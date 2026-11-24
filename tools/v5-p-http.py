"""Local image decode, Store API and isolated native Ajax checks; no browser/orders."""
import json,urllib.request,urllib.parse,http.cookiejar,concurrent.futures,io,re,datetime,sys
from pathlib import Path
from PIL import Image
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5';BASE='http://localhost:8082/wordpress/'
jar=http.cookiejar.CookieJar();opener=urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(jar))
def request(url,data=None,cookies=False):
    assert url.startswith(BASE)
    req=urllib.request.Request(url.replace('http://localhost:8082/','http://127.0.0.1:8082/'),headers={'Host':'localhost:8082'},data=data)
    local_opener=opener if cookies else urllib.request.build_opener(urllib.request.ProxyHandler({}))
    with local_opener.open(req,timeout=60)as r:return r.status,r.headers,r.read()
def picture(url):
    try:
        status,headers,raw=request(url)
        with Image.open(io.BytesIO(raw))as im:
            im.load();size=im.size;mode=im.mode;alpha=im.getchannel('A').histogram()[0]/(im.width*im.height)if im.mode=='RGBA'else 0
        return {'url':url,'status':status,'mime':headers.get('Content-Type'),'dimensions':list(size),'mode':mode,'transparent_fraction':alpha,'decoded':True,'bytes':len(raw)}
    except Exception as e:return {'url':url,'decoded':False,'error':str(e)}
native=json.loads((OUT/'verificacion-P.json').read_text('utf8'));catalog=json.loads((OUT/'catalogo_demo.json').read_text('utf8'))
data={'utc':datetime.datetime.now(datetime.timezone.utc).isoformat(),'browser_used':False,'passes':[]}
for n in [1,2]:
    products=json.loads(request(BASE+'wp-json/wc/store/v1/products?per_page=100')[2]);assert len(products)==98
    assert {p['id']for p in products}=={p['id']for p in native['products']}
    with concurrent.futures.ThreadPoolExecutor(max_workers=6)as pool:images=list(pool.map(picture,native['image_urls']))
    assert all(i['decoded']and i['status']==200 and (i.get('mime')or'').startswith('image/')for i in images),[i for i in images if not i['decoded']]
    demo=[i for i in images if '/minikids-demo-'in i['url']];assert all(i['transparent_fraction']>.02 for i in demo)
    sample={}
    for p in catalog:sample.setdefault(p['category'],p['url'])
    pages=[]
    for category,url in sample.items():
        status,headers,raw=request(url);assert status==200 and '<!doctype html>'in raw.decode('utf8').lower()
        pages.append({'category':category,'url':url,'status':status,'bytes':len(raw)})
    status,headers,raw=request(BASE+'tienda/');doc=raw.decode('utf8');assert status==200
    assert 'widget_shopping_cart_content'in doc and 'cart-fragments.js'in doc
    data['passes'].append({'number':n,'store_products':len(products),'images':images,'sample_pages':pages,'shop_status':status,'cart_markup_scripts_present':True})
    print('HTTP/decoded image pass',n,'images',len(images),flush=True)
pid=catalog[-1]['id'];added=json.loads(request(BASE+'?wc-ajax=add_to_cart',urllib.parse.urlencode({'product_id':pid,'quantity':1}).encode(),True)[2]);assert not added.get('error')and 'fragments'in added
fragments=''.join(added['fragments'].values());match=re.search(r'data-cart_item_key="([a-f0-9]+)"',fragments);assert match
removed=json.loads(request(BASE+'?wc-ajax=remove_from_cart',urllib.parse.urlencode({'cart_item_key':match[1]}).encode(),True)[2]);assert 'fragments'in removed
refreshed=json.loads(request(BASE+'?wc-ajax=get_refreshed_fragments',b'',True)[2]);assert match[1]not in ''.join(refreshed['fragments'].values())
data['ajax']={'product_id':pid,'add_remove_fragments':True,'isolated_session_empty_after':True,'order_creation_requested':False,'email_sending_requested':False}
dest=OUT/'verificacion-http-P.json';assert not dest.exists(),'Preserve previous evidence'
dest.write_text(json.dumps(data,ensure_ascii=False,indent=2),encoding='utf8')
print('Native Ajax add/remove OK; no order creation or email sending requested')
