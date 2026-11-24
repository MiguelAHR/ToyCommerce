"""HTTP/Store API checks in an isolated cookie jar. Never calls checkout POST."""
import urllib.request,urllib.parse,urllib.error,http.cookiejar,socket,json,re,time,sys
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5'
resolve=socket.getaddrinfo;socket.getaddrinfo=lambda host,*a,**kw:resolve('127.0.0.1' if host=='localhost' else host,*a,**kw)
base='http://localhost:8082/wordpress/';jar=http.cookiejar.CookieJar();op=urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(jar))
checks={};pages={};timings={}
def request(url,data=None,headers=None):
    req=urllib.request.Request(urllib.parse.urljoin(base,url),data=data,headers=headers or {});start=time.monotonic()
    try:r=op.open(req,timeout=40)
    except urllib.error.HTTPError as e:r=e
    body=r.read().decode();return r.status,dict(r.headers),body,round(time.monotonic()-start,3)
def get(path):return request(base+path)
for name,path in [('home',''),('lista','tienda/'),('categoria','tienda/?cat=didacticos'),('ficha','product/osito-miel-con-lazo/'),('cart_empty','cart/')]:
    code,headers,body,elapsed=get(path);checks[name+'_200']=code==200;timings[name]=elapsed;pages[name]=body
    checks[name+'_no_php_error']=not re.search(r'(Fatal error|Parse error|Warning:|Uncaught Error)',body)
    (OUT/f'BCDE-{name}.html').write_text(body,encoding='utf8');print(name,code,elapsed,flush=True)
checks['commerce_assets']=all('minikids-commerce-v5.'+ext in pages['lista']for ext in ['css','js'])
checks['native_fragment']= 'widget_shopping_cart_content' in pages['lista'] and 'wc-cart-fragments' in pages['lista']
checks['detail_hook_content']=all(text in pages['ficha']for text in ['Referencia: MK-018','¡Solo quedan 3 piezas!','Ver disponibilidad y opciones de entrega','COMPLETA TU PEDIDO','mk5-rating-row'])
checks['related_below_cart']='mk5-cart-related' in pages['cart_empty'] and 'Artículos relacionados' in pages['cart_empty']
checks['footer_content']=all(text in pages['home']for text in ['mk5-benefits','mk5-footer-columns','mk5-footer-legal','Transferencia bancaria','Pago contra entrega'])
checks['footer_not_card_promises']=not any(x in pages['home'][pages['home'].find('<footer class="mkr-footer"'):]for x in ['Visa','Mastercard','Yape'])
code,headers,text,_=get('wp-json/wc/store/v1/products?per_page=100');products=json.loads(text);checks['store_98']=code==200 and len(products)==98
config=json.loads(re.search(r'var mk5 = (\{[^\n]+\});',pages['lista']).group(1));checks['catalog_data_98']=len(config['products'])==98
checks['catalog_prices_real']=all(abs(config['products'][str(p['id'])]['price']-int(p['prices']['price'])/100)<.001 for p in products)
checks['catalog_cards_98']=len(re.findall(r'<li class="product type-product ',pages['lista']))==98
# Add, adjust, reject excessive stock, remove; no order endpoint involved.
code,headers,text,_=request(base+'?wc-ajax=add_to_cart',urllib.parse.urlencode({'product_id':278,'quantity':1}).encode(),{'Content-Type':'application/x-www-form-urlencoded'})
data=json.loads(text);checks['ajax_add_native_fragments']=code==200 and 'div.widget_shopping_cart_content' in data.get('fragments',{})
code,h,text,_=get('wp-json/wc/store/v1/cart');cart=json.loads(text);checks['added_quantity_1']=len(cart['items'])==1 and cart['items'][0]['quantity']==1;key=cart['items'][0]['key']
def quantity(q,nonce=config['nonce']):return request(config['ajax'],urllib.parse.urlencode({'nonce':nonce,'key':key,'quantity':q}).encode(),{'Content-Type':'application/x-www-form-urlencoded'})
code,h,text,_=quantity(2);data=json.loads(text);checks['mini_quantity_fragments']=code==200 and 'div.widget_shopping_cart_content' in data.get('fragments',{}) and 'mk5-mini-quantity' in text
code,h,text,_=quantity(999);checks['stock_rejected']=code==400
code,h,text,_=quantity(1,'bad');checks['bad_nonce_rejected']=code==403
code,h,text,_=get('wp-json/wc/store/v1/cart');cart=json.loads(text);checks['quantity_still_2']=cart['items'][0]['quantity']==2
for name,path in [('cart_filled','cart/'),('checkout','checkout/')]:
    code,h,body,elapsed=get(path);pages[name]=body;timings[name]=elapsed;checks[name+'_200']=code==200
    checks[name+'_native_presentation']=('woocommerce-cart-form' in body and 'mk5-native-cart' in body)if name=='cart_filled'else 'wp-block-woocommerce-checkout' in body
    (OUT/f'BCDE-{name}.html').write_text(body,encoding='utf8')
checks['checkout_native_coupon']='checkout-order-summary-coupon-form' in pages['checkout']
checks['cart_four_headers']=all('class="'+cls+'"' in pages['cart_filled']for cls in ['product-name','product-price','product-quantity','product-subtotal'])
checks['cart_checkout_label']='IR A PAGAR' in pages['cart_filled']
nonce_cart=re.search(r'name="woocommerce-cart-nonce" value="([^"]+)"',pages['cart_filled']).group(1)
code,h,text,_=request(base+'cart/',urllib.parse.urlencode({'woocommerce-cart-nonce':nonce_cart,'cart['+key+'][qty]':1,'update_cart':'Actualizar carrito'}).encode(),{'Content-Type':'application/x-www-form-urlencoded'})
code,h,text,_=get('wp-json/wc/store/v1/cart');checks['classic_cart_update_native']=json.loads(text)['items'][0]['quantity']==1
code,h,text,_=get('wp-json/wc/store/v1/cart');nonce=h.get('Nonce')or h.get('nonce');checks['store_cart_nonce']=bool(nonce)
code,h,text,_=request(base+'wp-json/wc/store/v1/cart/remove-item',json.dumps({'key':key}).encode(),{'Content-Type':'application/json','Nonce':nonce});checks['native_remove_empty']=code==200 and not json.loads(text)['items']
# HTTP status of every footer destination; no external WhatsApp request.
footer=pages['home'][pages['home'].find('<footer class="mkr-footer"'):];links=sorted(set(re.findall(r'href="('+re.escape(base)+r'[^"#]*)"',footer)))
from html import unescape
dest=[]
for url in links:
    code,h,text,elapsed=request(unescape(url));dest.append({'url':unescape(url),'status':code});
checks['footer_local_links_200']=all(x['status']==200 for x in dest)
result={'checks':checks,'all_pass':all(checks.values()),'html_seconds':timings,'footer_links':dest,'scope':'HTTP/native API only; no browser, checkout POST, orders, email or gateway changes.'}
name=sys.argv[1]if len(sys.argv)>1 else 'verificacion-BCDE';(OUT/(name+'.json')).write_text(json.dumps(result,ensure_ascii=False,indent=2),encoding='utf8')
print(json.dumps(result,ensure_ascii=True,indent=2));sys.exit(0 if result['all_pass']else 1)
