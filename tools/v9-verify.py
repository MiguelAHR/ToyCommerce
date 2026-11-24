"""HTTP/native evidence only. Deliberately incomplete/expired POSTs; no successful order."""
from pathlib import Path
import urllib.request,urllib.parse,urllib.error,http.cookiejar,socket,json,re,time,hashlib,sys
from lxml import html
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5';BACK=ROOT.parent/'local-private/v9-backups'
resolve=socket.getaddrinfo;socket.getaddrinfo=lambda host,*a,**kw:resolve('127.0.0.1' if host=='localhost' else host,*a,**kw)
jar=http.cookiejar.CookieJar();op=urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(jar));base='http://localhost:8082/wordpress/'
checks={};evidence={}
def req(path,data=None,headers=None):
    body=urllib.parse.urlencode(data).encode() if data is not None else None
    try:r=op.open(urllib.request.Request(base+path,data=body,headers=headers or {}),timeout=45)
    except urllib.error.HTTPError as e:r=e
    return r.status,r.headers,r.read().decode()
def check(key,value):
    checks[key]=bool(value)
    print(('PASS ' if value else 'FAIL ')+key,flush=True)
def config(s,name):return json.JSONDecoder().raw_decode(s.split('var '+name+' = ',1)[1])[0]
def state(s):return config(s,'mk9')['state']
def doc(s):return html.fromstring(s)
msg='Tu sesión de compra segura venció. Vuelve al carrito para continuar.'
_,_,s=req('?wc-ajax=add_to_cart',{'product_id':278,'quantity':1});check('add_native',bool(json.loads(s).get('fragments')))
_,_,s=req('cart/');d=doc(s)
check('cart_no_large_banner','minikids-shipping-progress-banner' not in s)
check('cart_one_progress_inside_totals',len(d.xpath('//div[contains(@class,"cart_totals")]//div[@class="mk9-progress"]'))==1 and len(d.xpath('//div[@class="mk9-progress"]'))==1)
check('cart_gap_actual','122.10' in d.xpath('//div[@class="mk9-progress"]')[0].text_content())
_,_,s=req('tienda/');d=doc(s)
check('drawer_native_fragment',len(d.xpath('//*[@id="minikids-cart-drawer"]//div[@class="widget_shopping_cart_content"]'))==1)
check('drawer_footer_in_fragment',bool(d.xpath('//*[@id="minikids-cart-drawer"]//div[@class="widget_shopping_cart_content"]//div[@class="mk9-bag-footer"]')))
check('drawer_old_footer_removed','class="minikids-cart-drawer-footer"' not in s and 'class="minikids-cart-drawer-shipping"' not in s)
check('drawer_buy','Comprar' in d.xpath('//div[@class="mk9-bag-footer"]')[0].text_content())
status,h,s=req('checkout/?mk6_ttl=15');d=doc(s);st=state(s);evidence['initial_state']=st.copy();evidence['initial_state'].pop('token')
check('checkout_http',status==200)
check('server_class','mk9-secure-checkout' in d.xpath('//body/@class')[0])
check('critical_before_body',s.index('id="mk9-secure-critical"')<s.index('<body'))
check('fail_closed_css','body.mk9-secure-checkout:not(.mk-secure-ready)' in s)
check('failsafe_cart_link',bool(d.xpath('//*[@id="mk9-failsafe"]//a[contains(@href,"/cart/")]')))
check('no_store','no-store' in h.get('Cache-Control',''))
check('vary_cookie','Cookie' in ','.join(h.get_all('Vary',[])))
check('server_ttl15',st['end']-st['start']==15 and 0<st['remaining']<=15)
check('native_single_place_order',len(d.xpath('//*[@id="place_order"]'))==1)
check('native_names',all(d.xpath(f'//*[@name="{name}"]') for name in ['billing_email','billing_first_name','billing_last_name','billing_phone','billing_document','payment_method','terms','mk_secure_token']))
check('products_quantities',bool(d.xpath('//div[@class="mk9-products-slot"]//input[@type="number"]')))
check('secure_footer',bool(d.xpath('//footer[@class="mk9-secure-footer"]')))
check('no_pickup_or_third_party_pay','Retiro en tienda' not in s and all(x not in d.xpath('//footer[@class="mk9-secure-footer"]')[0].text_content() for x in ['Yape','Visa','Culqi','PayPal']))
nonce=d.xpath('//input[@name="woocommerce-process-checkout-nonce"]/@value')[0]
post={'woocommerce-process-checkout-nonce':nonce,'mk_secure_token':st['token']}
# Missing all required identity/address/payment/terms is a second independent safety barrier.
_,_,s=req('?wc-ajax=checkout',post);j=json.loads(s)
check('active_incomplete_rejected',j.get('result')=='failure' and msg not in j.get('messages',''))
_,_,s=req('checkout/?mk6_ttl=1');st2=state(s)
check('reload_no_extension',st2['end']==st['end'] and st2['token']==st['token'])
_,_,s=req('?wc-ajax=mk9_secure_status',{'token':st['token']});check('status_active',json.loads(s).get('success'))
delay=max(0,st['end']-time.time()+1);print('Waiting actual expiry '+str(round(delay,2))+' s',flush=True);time.sleep(delay)
code,h,s=req('?wc-ajax=mk9_secure_status',{'token':st['token']});j=json.loads(s)
check('status_expired',code==403 and not j.get('success') and j['data']['message']==msg)
_,_,s=req('?wc-ajax=checkout',post);j=json.loads(s);evidence['expired_checkout_response']=j
check('expired_classic_post_rejected',j.get('result')=='failure' and msg in j.get('messages',''))
_,_,s=req('checkout/');expired=state(s)
check('expired_render_cannot_restart',expired['start']==0 and not expired['token'] and expired['expired'])
_,h,s=req('wp-json/wc/store/v1/cart');store=json.loads(s);nonce=h.get('Nonce')
check('cart_intact_after_expiry',len(store['items'])==1 and store['items'][0]['quantity']==1)
code,h,s=req('wp-json/wc/store/v1/checkout',{},headers={'Nonce':nonce,'X-MK-Secure-Token':st['token']});j=json.loads(s);evidence['expired_store_response']=j
check('expired_store_before_callback',code==403 and j.get('code')=='mk_secure_expired' and j.get('message')==msg)
req('cart/?mk-session-expired=1');_,_,s=req('checkout/');new=state(s)
check('cart_allows_new_600s_window',new['end']-new['start']==600 and new['token'] and new['token']!=st['token'])
_,_,s=req('?wc-ajax=checkout',{'woocommerce-process-checkout-nonce':nonce,'mk_secure_token':st['token']})
# Use the current checkout nonce, not the Store API nonce, for the stale-form check.
_,_,fresh=req('checkout/');freshdoc=doc(fresh);current_nonce=freshdoc.xpath('//input[@name="woocommerce-process-checkout-nonce"]/@value')[0]
_,_,s=req('?wc-ajax=checkout',{'woocommerce-process-checkout-nonce':current_nonce,'mk_secure_token':st['token']});j=json.loads(s)
check('stale_tab_token_rejected',j.get('result')=='failure' and msg in j.get('messages',''))
_,_,s=req('?wc-ajax=mk9_secure_status',{'token':new['token']});check('bad_token_does_not_extend_or_reset',json.loads(s).get('success'))
# Quantity endpoint plus native order-review fragments. Never submit a valid order.
_,_,page=req('tienda/');mk5=config(page,'mk5');key=store['items'][0]['key']
_,_,s=req('?wc-ajax=mk5_quantity',{'nonce':mk5['nonce'],'key':key,'quantity':2});j=json.loads(s)
check('quantity_native_fragments',bool(j.get('fragments')))
req('?wc-ajax=add_to_cart',{'product_id':38,'quantity':4})
_,_,s=req('cart/');check('free_threshold_complete','Tienes envío gratis' in s and 'aria-valuenow="149"' in s)
_,_,page=req('checkout/');params=config(page,'wc_checkout_params')
_,_,s=req('?wc-ajax=update_order_review',{'security':params['update_order_review_nonce'],'post_data':urllib.parse.urlencode({'billing_country':'PE','billing_state':'LMA','billing_city':'Lima','billing_address_1':'QA sin pedido','mk_secure_token':new['token']})});j=json.loads(s)
check('review_products_fragment','.mk9-products-slot' in j.get('fragments',{}))
check('review_progress_fragment','.mk9-summary > .mk9-progress' in j.get('fragments',{}))
req('?wc-ajax=mk5_quantity',{'nonce':mk5['nonce'],'key':key,'quantity':1})
_,_,s=req('?wc-ajax=apply_coupon',{'security':params['apply_coupon_nonce'],'coupon_code':'V9-NO-EXISTE'});check('coupon_invalid_native','cupón' in s.lower())
protected=json.loads((ROOT.parent/'local-private/v6-backups/protected.json').read_text('utf8'));diff=[p for p,digest in protected.items() if not (ROOT/p).is_file() or hashlib.sha256((ROOT/p).read_bytes()).hexdigest()!=digest]
check('protected_files_unchanged',not diff)
for name in ['minikids-commerce-v9.css','minikids-commerce-v9.js']:
    status,_,s=req('wp-content/mu-plugins/'+name);check(name+'_http',status==200)
js=(ROOT/'wordpress/wp-content/mu-plugins/minikids-commerce-v9.js').read_text('utf8')
check('no_reload_or_client_entry_redirect','location.reload' not in js and 'location.replace(cart.href)' in js)
check('pageshow_server_recheck',"window.addEventListener('pageshow'" in js and 'if(tick())verify()' in js)
result={'checks':checks,'passed':sum(checks.values()),'total':len(checks),'all_pass':all(checks.values()),'evidence':evidence,'protected_files':len(protected),'protected_differences':diff,'scope':'HTTP/DOM/source only; no browser, no valid checkout, no mail. POSTs omit identity/address/payment/terms even when expired.'}
number=sys.argv[1] if len(sys.argv)>1 else '1';(OUT/f'verificacion-V9-{number}.json').write_text(json.dumps(result,ensure_ascii=False,indent=2),encoding='utf8')
print(json.dumps({'passed':result['passed'],'total':result['total'],'failures':[k for k,v in checks.items() if not v]},ensure_ascii=True))
