"""Isolated cart HTTP/DOM regression. No order endpoint, account creation or mail."""
from pathlib import Path
import urllib.request,urllib.parse,urllib.error,http.cookiejar,json,time,sys
from lxml import html
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5'
op=urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
base='http://localhost:8082/wordpress/'
checks={}
def req(path,data=None):
 b=urllib.parse.urlencode(data).encode() if data is not None else None
 try:r=op.open(base+path,b,timeout=45)
 except urllib.error.HTTPError as e:r=e
 return r.status,r.headers,r.read().decode()
def check(k,v):
 checks[k]=bool(v);print(('PASS ' if v else 'FAIL ')+k,flush=True)
def config(s,name):return json.JSONDecoder().raw_decode(s.split('var '+name+' = ',1)[1])[0]
_,_,s=req('?wc-ajax=add_to_cart',{'product_id':278,'quantity':1});check('native_add_one_product',bool(json.loads(s).get('fragments')))
status,headers,s=req('checkout/?mk6_ttl=15');page=html.fromstring(s);state=config(s,'mk9')['state'];params=config(s,'wc_checkout_params');mk5=config(s,'mk5')
check('checkout_200',status==200)
check('checkout_no_store','no-store' in headers.get('Cache-Control',''))
check('v17_styles_loaded','minikids-commerce-v17.css' in s)
check('one_account_notice_with_user_svg',len(page.xpath('//*[@class="mk17-account-notice"]/svg'))==1)
check('notice_modal_target',page.xpath('//*[@class="mk17-account-notice"]/a/@href')==['#mk12-auth'])
check('modal_exists',len(page.xpath('//*[@id="mk12-auth"]'))==1)
check('no_old_login_notice',not page.xpath('//*[contains(@class,"woocommerce-form-login-toggle")]'))
check('clock_lock_and_clock_svg',len(page.xpath('//*[@class="mk17-secure-clock"]/svg'))==2)
check('no_redundant_header_label',not page.xpath('//*[@class="mk11-secure-label"]'))
check('mobile_summary_total',bool(page.xpath('//details[@class="mk9-summary"]/summary/span[@class="mk17-summary-total"]')))
check('products_card_grouped',all(page.xpath('//section[@class="mk17-products-card"]//*[contains(@class,"'+c+'")]') for c in ['mk9-products-slot','mk9-progress','mk9-coupon-slot']))
check('totals_native_single',len(page.xpath('//*[@id="order_review"]'))==1)
check('place_order_single',len(page.xpath('//*[@id="place_order"]'))==1)
check('native_field_ids',all(page.xpath('//*[@id="'+n+'"]') for n in ['billing_email','billing_first_name','billing_last_name','billing_phone']))
check('first_paint_guard',s.index('id="mk9-secure-critical"')<s.index('<body') and 'body.mk9-secure-checkout:not(.mk-secure-ready)' in s)
check('secure_footer',bool(page.xpath('//footer[@class="mk9-secure-footer"]')))
_,_,s=req('?wc-ajax=mk9_secure_status',{'token':state['token']});check('active_secure_status',json.loads(s).get('success'))
_,_,s=req('checkout/');again=config(s,'mk9')['state'];check('reload_does_not_extend_window',state['end']==again['end'] and state['token']==again['token'])
_,_,s=req('wp-json/wc/store/v1/cart');cart=json.loads(s);key=cart['items'][0]['key'];check('one_product_in_cart',len(cart['items'])==1 and cart['items'][0]['quantity']==1)
_,_,s=req('?wc-ajax=mk5_quantity',{'nonce':mk5['nonce'],'key':key,'quantity':2});check('quantity_fragments',bool(json.loads(s).get('fragments')))
_,_,s=req('?wc-ajax=update_order_review',{'security':params['update_order_review_nonce'],'post_data':urllib.parse.urlencode({'billing_country':'PE','billing_state':'LMA','billing_city':'Lima','mk_secure_token':state['token']})});frags=json.loads(s).get('fragments',{})
check('products_fragment', '.mk9-products-slot' in frags)
check('nested_progress_fragment','.mk9-summary .mk9-progress' in frags)
check('native_totals_fragment','.woocommerce-checkout-review-order-table' in frags)
check('native_payment_fragment','.woocommerce-checkout-payment' in frags)
_,_,s=req('?wc-ajax=apply_coupon',{'security':params['apply_coupon_nonce'],'coupon_code':'V17-NO-EXISTE'});check('invalid_coupon_native','cupón' in s.lower())
# Allow the actual 15-second test window to expire; never call the order route.
delay=max(0,state['end']-time.time()+1)
if delay<20:time.sleep(delay)
status,_,s=req('?wc-ajax=mk9_secure_status',{'token':state['token']});check('expired_status_403',status==403 and not json.loads(s).get('success'))
_,_,s=req('wp-json/wc/store/v1/cart');check('cart_preserved_after_expiry',json.loads(s)['items'][0]['quantity']==2)
# Existing quantity controls use the shared endpoint; native nonce-protected removal through Store API.
_,h,s=req('wp-json/wc/store/v1/cart');nonce=h.get('Nonce')
request=urllib.request.Request(base+'wp-json/wc/store/v1/cart/remove-item',data=json.dumps({'key':key}).encode(),headers={'Content-Type':'application/json','Nonce':nonce})
with op.open(request,timeout=45) as response:removed=json.load(response)
check('native_remove_and_cleanup',not removed['items'])
result={'checks':checks,'passed':sum(checks.values()),'total':len(checks),'scope':'HTTP/HTML/AJAX only. No browser, no order requests, no auth submission or mail.'}
(O/('verificacion-V17-'+(sys.argv[1] if len(sys.argv)>1 else '1')+'.json')).write_text(json.dumps(result,ensure_ascii=False,indent=2),encoding='utf8')
assert all(checks.values()),[k for k,v in checks.items() if not v]
