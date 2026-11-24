"""Local HTTP inventory, deliberately not a browser or CWV measurement."""
import concurrent.futures, datetime, html, json, re, statistics, subprocess, sys
from pathlib import Path
from urllib.parse import urljoin, urlparse
from html.parser import HTMLParser
ROOT=Path(__file__).resolve().parents[1]
OUT=ROOT.parent/'diseno_minikids/v5'
BASE='http://localhost:8082'
class Assets(HTMLParser):
    def __init__(self):super().__init__();self.urls=set();self.images=[]
    def handle_starttag(self,tag,attrs):
        a=dict(attrs)
        if tag=='img':self.images.append(a)
        if tag in ('img','script') and a.get('src'):self.urls.add(a['src'])
        if tag=='link' and a.get('rel') in ('stylesheet','preload'):self.urls.add(a.get('href',''))
def curl(url):
    p=subprocess.run(['curl.exe','-sS','-L','--max-time','90','-o','NUL','-w','%{json}',url],capture_output=True,text=True)
    try:d=json.loads(p.stdout);return {k:d.get(k) for k in ('url_effective','http_code','content_type','time_starttransfer','time_total','size_download','num_redirects')}|{'url':url,'error':p.stderr.strip()}
    except ValueError:return {'url':url,'error':p.stderr,'http_code':0,'time_total':90,'size_download':0}
def body(url):return subprocess.check_output(['curl.exe','-sS','-L','--max-time','90',url]).decode('utf8','replace')
def local(url):return urlparse(url).hostname in ('localhost','127.0.0.1') and urlparse(url).port==8082
def main(stage):
    OUT.mkdir(exist_ok=True,parents=True)
    target=OUT/'rendimiento.json'
    data=json.loads(target.read_text('utf8')) if target.exists() else {}
    if stage in data:raise SystemExit('Stage exists; preserve original evidence')
    result={'utc':datetime.datetime.now(datetime.timezone.utc).isoformat(),'method':'curl.exe GET, redirects followed; three sequential HTML samples. Inventory: unique img src + script src + stylesheet/preload + local CSS url(), all fetched (includes lazy/hidden images); no JS execution, srcset selection, external fetches, browser cache or CWV. Download bytes exclude headers. Six parallel resource requests.','pages':{}}
    for route in ('/','/wordpress/tienda/','/wordpress/product/bloques-creativos-60-piezas/'):
        samples=[curl(BASE+route) for _ in range(3)]
        doc=body(BASE+route);parser=Assets();parser.feed(doc)
        name={'/':'home','/wordpress/tienda/':'tienda'}.get(route,'ficha')
        (OUT/f'{stage}-{name}.html').write_text(doc,encoding='utf8')
        urls={urljoin(BASE+'/wordpress/',html.unescape(u)) for u in parser.urls if u and not u.startswith('data:')}
        external=sorted(u for u in urls if not local(u));urls={u for u in urls if local(u)}
        # CSS URLs are read from local files, avoiding an extra HTTP fetch outside the inventory.
        for u in list(urls):
            f=ROOT/urlparse(u).path.lstrip('/')
            if f.suffix=='.css' and f.is_file():
                for v in re.findall(r'url\(\s*[\"\']?([^\)\"\']+)',f.read_text('utf8',errors='replace')):
                    if not v.startswith('data:'):
                        full=urljoin(u,v)
                        if local(full):urls.add(full)
                        else:external.append(full)
        with concurrent.futures.ThreadPoolExecutor(max_workers=6) as pool:resources=list(pool.map(curl,sorted(urls)))
        result['pages'][name]={'route':route,'samples':samples,'median_html_seconds':statistics.median(s['time_total'] for s in samples),'resources':resources,'external_not_fetched':external,'inventory_download_bytes':samples[-1]['size_download']+sum(r.get('size_download',0) for r in resources),'requests_over_1s':sum(r['time_total']>1 for r in resources),'images':parser.images}
        print(stage,name,result['pages'][name]['median_html_seconds'],result['pages'][name]['inventory_download_bytes'],flush=True)
    slugs=['bloques-60','memoria-animales','karaoke','pista-carreras','scooter','cocina-chef','tren-madera']
    result['seven_legacy_images']=[curl(BASE+'/wordpress/wp-content/uploads/2026/10/prod-'+s+'-hero-principal-300x300.jpg') for s in slugs]
    data[stage]=result
    if target.exists():
        import shutil
        backup=ROOT.parent/'local-private/v5-backups';backup.mkdir(exist_ok=True,parents=True)
        shutil.copy2(target,backup/f'rendimiento-{datetime.datetime.now().strftime("%Y%m%d-%H%M%S")}.json')
    target.write_text(json.dumps(data,indent=2,ensure_ascii=False),encoding='utf8')
if __name__=='__main__':main(sys.argv[1])
