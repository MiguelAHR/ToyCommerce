"""HTTP/image/font/CRUD regression checks, without browser or orders."""
import json,sys,urllib.request,urllib.parse,http.cookiejar,re,hashlib,io,concurrent.futures
from pathlib import Path
from PIL import Image,ImageFont
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5';BASE='http://localhost:8082/wordpress/'
sys.path.insert(0,str(ROOT/'tools'));from importlib.machinery import SourceFileLoader
measure=SourceFileLoader('v5_measure',str(ROOT/'tools/v5-measure.py')).load_module()
opener=urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
def get(url,data=None):
    assert url.startswith(BASE)
    # Use IPv4 transport with the canonical Host, avoiding Windows localhost connect fallback.
    transport=url.replace('http://localhost:8082/','http://127.0.0.1:8082/')
    with opener.open(urllib.request.Request(transport,data=data,headers={'Host':'localhost:8082'}),timeout=45) as r:return r.status,dict(r.headers),r.read(),r.url.replace('http://127.0.0.1:8082/','http://localhost:8082/')
def verify_image(url):
    status,headers,data,effective=get(url)
    with Image.open(io.BytesIO(data))as im:im.load();size=im.size;fmt=im.format
    assert status==200 and headers['Content-Type'].startswith('image/'),url
    return {'url':url,'effective_url':effective,'status':status,'content_type':headers['Content-Type'],'bytes':len(data),'dimensions':size,'format':fmt,'decoded':True,'redirected':effective!=url}
result={'browser_used':False,'passes':[]}
for number in (1,2):
    p={'number':number,'pages':{},'seven_images':[]}
    for route in ('','tienda/','product/bloques-creativos-60-piezas/'):
        status,h,raw,u=get(BASE+route);assert status==200
        doc=raw.decode('utf8');parser=measure.Assets();parser.feed(doc)
        assert all(a.get('src') for a in parser.images),(route,'Empty image src')
        images={a['src'] for a in parser.images if a.get('src','').startswith(BASE)}
        # Do not parallelize the cookie-bearing cart session.
        checked=[verify_image(u) for u in sorted(images)]
        no_dims=[a['src'] for a in parser.images if not a.get('width') or not a.get('height')]
        campaigns=[a for a in parser.images if 'minikids-v4-' in a.get('src','')]
        p['pages'][route or 'home']={'http_status':status,'images_decoded':len(checked),'images_missing_dimensions':no_dims,'campaign_images':len(campaigns),'campaign_images_with_srcset':sum(bool(a.get('srcset')) for a in campaigns),'campaign_lazy':sum(a.get('loading')=='lazy' for a in campaigns),'campaign_eager':sum(a.get('loading')=='eager' for a in campaigns),'preloaded_fonts':re.findall(r'<link rel="preload"[^>]+as="font"[^>]*>',doc),'cart_fragment_markup':'widget_shopping_cart_content' in doc,'cart_fragments_script':'frontend/cart-fragments.js' in doc,'remote_google_fonts':'fonts.googleapis.com' in doc,'v4_campaign_script':'minikids-campanas-v4.js' in doc}
        assert not no_dims,(route,no_dims)
        assert 'widget_shopping_cart_content' in doc and 'frontend/cart-fragments.js' in doc
        assert 'fonts.googleapis.com' not in doc
        if route=='tienda/':assert 'minikids-atencion.css' not in doc and 'minikids-campanas-v4.js' not in doc
        if not route:assert len(campaigns)==50 and all(a.get('srcset') for a in campaigns)
    for slug in ('bloques-60','memoria-animales','karaoke','pista-carreras','scooter','cocina-chef','tren-madera'):
        url=BASE+'wp-content/uploads/2026/10/prod-'+slug+'-hero-principal-300x300.jpg'
        p['seven_images'].append(verify_image(url));assert not p['seven_images'][-1]['redirected']
        p['seven_images'].append(verify_image(url[:-4]+'.webp'))
    store=json.loads(get(BASE+'wp-json/wc/store/v1/products?per_page=100')[2]);before=json.loads((OUT/'productos-antes.json').read_text('utf8'))
    assert {a['id'] for a in store}=={a['id'] for a in before}
    p['store_products']=len(store)
    result['passes'].append(p)
    print('Pass',number,'HTTP/images/dimensions/srcset/store/cart markup verified',flush=True)
# Check native Ajax fragments with an isolated cookie jar; no order is created.
ajax=BASE+'?wc-ajax='
added=json.loads(get(ajax+'add_to_cart',urllib.parse.urlencode({'product_id':38,'quantity':1}).encode())[2])
assert not added.get('error') and 'fragments' in added
fragments=''.join(added['fragments'].values())
key=re.search(r'data-cart_item_key="([a-f0-9]+)"',fragments)
assert key,'No cart item key in the returned Woo fragment'
removed=json.loads(get(ajax+'remove_from_cart',urllib.parse.urlencode({'cart_item_key':key[1]}).encode())[2])
assert 'fragments' in removed
refreshed=json.loads(get(ajax+'get_refreshed_fragments',b'')[2])
assert 'widget_shopping_cart_content' in ''.join(refreshed['fragments'])
assert key[1] not in ''.join(refreshed['fragments'].values())
result['ajax']={'added_product_id':38,'quantity':1,'add_fragments':True,'remove_fragments':True,'empty_session_after_cleanup':True,'orders_created':0,'browser_interaction_verified':False}
result['fonts']=[]
for name in ('montserrat-variable','barlowcondensed-bold'):
    source=ROOT.parent/'diseno_minikids/fuentes'/(name+'.ttf');dest=ROOT/'wordpress/wp-content/mu-plugins/minikids-v5-assets'/(name+'-latin.woff2')
    a,b=ImageFont.truetype(str(source),32),ImageFont.truetype(str(dest),32)
    text='¡Niñez Perú! Árbol pingüino — S/ 29.90'
    assert bytes(a.getmask(text))==bytes(b.getmask(text))
    weights=[]
    if name=='montserrat-variable':
        for weight in (100,400,700,900):
            a.set_variation_by_axes([weight]);b.set_variation_by_axes([weight]);assert bytes(a.getmask(text))==bytes(b.getmask(text));weights.append(weight)
    result['fonts'].append({'name':name,'freetype_decodes':True,'spanish_matches_original':True,'weights_tested':weights,'http_status':get(BASE+'wp-content/mu-plugins/minikids-v5-assets/'+dest.name)[0]})
# Original source files remain byte-identical to the pre-repair private copies.
result['original_sources_unchanged']=[]
for f in (ROOT.parent/'local-private/v5-backups').glob('repair-resume-*/prod-*-hero-principal.jpg'):
    original=ROOT/'wordpress/wp-content/uploads/2026/10'/f.name
    assert hashlib.sha256(original.read_bytes()).digest()==hashlib.sha256(f.read_bytes()).digest()
    result['original_sources_unchanged'].append(f.name)
target=OUT/'verificacion-final.json'
if target.exists():raise RuntimeError('Preserve existing verification')
target.write_text(json.dumps(result,indent=2,ensure_ascii=False),encoding='utf8')
print('Native Ajax add/remove and WOFF2 Spanish/variable weights verified; no orders',flush=True)
