from pathlib import Path
import urllib.request,hashlib,json,subprocess
from lxml import html
R=Path(__file__).resolve().parents[1]; O=R.parent/'diseno_minikids/v5'; B=R.parent/'local-private/v10-backups'; B.mkdir(exist_ok=True)
def protected():
    return {str(p.relative_to(R)):hashlib.sha256(p.read_bytes()).hexdigest() for area in ['wp-admin','wp-includes','wp-content/themes','wp-content/plugins'] for p in (R/'wordpress'/area).rglob('*') if p.is_file()}
if __name__=='__main__':
    (B/'protected-before.json').write_text(json.dumps(protected()))
    s=urllib.request.build_opener(urllib.request.ProxyHandler({})).open('http://127.0.0.1:8082/wordpress/product/abaco-pedagogico-y-laberinto-de-madera/').read().decode()
    (O/'V10-ficha-antes.html').write_text(s,encoding='utf8');d=html.fromstring(s)
    print([(x.tag,x.get('class'),x.getparent().get('class')) for x in d.xpath('//*[contains(@class,"breadcrumb")]')])
    print('script/dialog positions',s.find('minikids-commerce-v8.js'),s.find('id="mk8-availability"'))
