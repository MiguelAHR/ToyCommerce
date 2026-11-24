"""Local HTTP/DOM checks. Never submits registration, recovery, review or checkout."""
from pathlib import Path
import json,sys,socket,urllib.request,urllib.parse,urllib.error,http.cookiejar,re
from lxml import html
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5'
resolve=socket.getaddrinfo;socket.getaddrinfo=lambda host,*a,**kw:resolve('127.0.0.1' if host=='localhost' else host,*a,**kw)
op=urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()));base='http://localhost:8082/wordpress/'
checks={}
native=json.loads((R.parent/'local-private/v12-backups/native-before.json').read_text('utf8'))
def req(path,data=None):
 try:r=op.open(base+path,urllib.parse.urlencode(data).encode() if data is not None else None,timeout=40)
 except urllib.error.HTTPError as e:r=e
 with r:return r.status,r.headers,r.read().decode()
def check(name,value):checks[name]=bool(value);print(('PASS ' if value else 'FAIL ')+name,flush=True)
def cards(d,name):
 controls=d.xpath('//*[contains(concat(" ",normalize-space(@class)," ")," mk12-card-buy ")]')
 check(name+'_common_buy',bool(controls) and all(x.xpath('.//input[@min="1"]') and len(x.xpath('.//button[@data-mk12-delta]'))==2 and x.xpath('.//a[contains(@class,"ajax_add_to_cart")]/svg') and 'AGREGAR' in x.text_content() for x in controls))
 check(name+'_no_bag_label',all('A MI BOLSA' not in x.text_content() for x in d.xpath('//li[contains(@class,"product")]')))
 tags=d.xpath('//span[contains(@class,"mk12-tag")]');check(name+'_tags',bool(tags) and all(re.match(r'^- \d+ %',x.text_content()) and x.xpath('./i') for x in tags))
 check(name+'_no_sale_banner',all('OFERTA' not in x.text_content() for x in d.xpath('//span[contains(@class,"onsale")]')))
 valid=True
 for x in d.xpath('//li[contains(concat(" ",normalize-space(@class)," ")," product ")]'):
  pid=re.search(r'\bpost-(\d+)\b',x.get('class',''));tag=x.xpath('.//span[contains(@class,"mk12-tag")]')
  if not pid:continue
  p=native['products'][pid[1]]['data'];r=float(p['regular_price'] or 0);s=float(p['sale_price'] or 0)
  if tag:valid=valid and r>0 and s<r and int(re.search(r'\d+',tag[0].text_content())[0])==int(100*(r-s)/r+0.5)
 check(name+'_real_rounded_discount',valid)
_,_,s=req('tienda/');d=html.fromstring(s);(O/'V12-tienda.html').write_text(s,encoding='utf8');cards(d,'shop')
forms=d.xpath('//dialog[@id="mk12-auth"]/form');check('auth_dialog',len(forms)>=2 and d.xpath('//dialog[@id="mk12-auth"]/@aria-modal')==['true'])
login=d.xpath('//form[@data-mk12-view="login"]')[0];nonce=login.xpath('.//input[@name="woocommerce-login-nonce"]/@value')[0]
check('auth_native_nonce',bool(nonce) and login.xpath('.//input[@name="rememberme"]'))
check('auth_no_social',not d.xpath('//dialog[@id="mk12-auth"]//*[contains(text(),"Facebook") or contains(text(),"Google")]'))
check('auth_recovery_view',len(d.xpath('//form[@data-mk12-view="lost"]'))==1)
check('account_spanish_fallback',d.xpath('//a[@class="mkr-account"]/@href')==[base+'mi-cuenta/'])
check('auth_explicit_fallback',bool(d.xpath('//dialog[@id="mk12-auth"]/a[@class="mk12-account-fallback"]')))
for name,data,status,message in [
 ('csrf',{'mode':'login'},403,'caducado'),
 ('empty',{'mode':'login','woocommerce-login-nonce':nonce},400,'Completa'),
 ('bad_credentials',{'mode':'login','woocommerce-login-nonce':nonce,'username':'mk12-nonexistent-controlled','password':'invalid-test-only'},401,'Los datos no coinciden'),
 ('honeypot',{'mode':'login','woocommerce-login-nonce':nonce,'website':'filled'},400,'No pudimos'),
 ('lost_empty',{'mode':'lost','woocommerce-lost-password-nonce':d.xpath('//form[@data-mk12-view="lost"]//input[@name="woocommerce-lost-password-nonce"]/@value')[0]},400,'Completa')]:
 code,h,body=req('wp-admin/admin-ajax.php',dict(action='mk12_auth',**data));result=json.loads(body)
 check('auth_'+name,code==status and result['success']==False and message in result['data']['message']);check('auth_'+name+'_no_store','no-store' in h.get('Cache-Control',''))
for slug in ['abaco-pedagogico-y-laberinto-de-madera','auto-con-control-remoto','set-de-circuitos-inicial']:
 _,_,s=req('product/'+slug+'/');d=html.fromstring(s);(O/('V12-'+slug+'.html')).write_text(s,encoding='utf8')
 comments=d.xpath('//section[@class="mk12-comments"]');check(slug+'_comments_once',len(comments)==1 and len(d.xpath('//h2[normalize-space(.)="Comentarios"]'))==1)
 check(slug+'_comments_outside_columns',bool(comments) and not comments[0].xpath('ancestor::*[contains(@class,"mk8-left") or contains(@class,"product-entry-wrapper")]'))
 check(slug+'_reviews_native',bool(comments) and comments[0].xpath('.//*[@id="commentform"]') and len(comments[0].xpath('.//meter'))==5)
 check(slug+'_order',bool(comments) and comments[0].xpath('preceding::*[contains(@class,"product-entry-wrapper")]') and comments[0].xpath('following::section[contains(@class,"mk6-recommendations")]'))
 check(slug+'_description_left',len(d.xpath('//div[@class="mk8-left"]//section[@class="mk8-description"]'))==1)
 check(slug+'_four_help_dialogs',len(d.xpath('//dialog[contains(@class,"mk8-dialog")]'))==4 and len(d.xpath('//button[@data-mk8-dialog]'))==4)
 availability=d.xpath('//dialog[@id="mk8-availability"]')[0].text_content();check(slug+'_shipping_real','15.00' in availability and '149.00' in availability and 'a validar' not in availability.lower())
 check(slug+'_payment_clean','(prueba local)' not in d.xpath('//dialog[@id="mk8-payment"]')[0].text_content().lower())
 check(slug+'_one_breadcrumb',len(d.xpath('//nav[contains(@class,"breadcrumb")]'))==1)
 if slug=='auto-con-control-remoto':check('sale_note_after_button',len(d.xpath('//form[contains(@class,"cart")]/following-sibling::*[1][@class="mk12-sale-note"]'))==1)
 cards(d,slug)
_,_,s=req('');d=html.fromstring(s);cards(d,'home');(O/'V12-inicio.html').write_text(s,encoding='utf8')
_,_,s=req('?wc-ajax=add_to_cart',{'product_id':38,'quantity':2});result=json.loads(s);check('native_add_fragments',bool(result.get('fragments')))
fragment=result.get('fragments',{}).get('div.widget_shopping_cart_content','');check('bag_v11_structure','mk11-mini-name' in fragment and 'mk5-mini-quantity' in fragment and 'mk9-bag-footer' in fragment)
_,_,s=req('cart/');d=html.fromstring(s);cards(d,'cart');(O/'V12-carrito.html').write_text(s,encoding='utf8')
check('cart_v11_tools',bool(d.xpath('//form[contains(@class,"woocommerce-cart-form")]/table/following-sibling::*[1][contains(@class,"mk5-cart-tools")]')))
_,_,s=req('checkout/');d=html.fromstring(s);check('checkout_v11_failsafe',bool(d.xpath('//*[@id="mk9-failsafe"]')) and 'mk11-reveal 0s 4s' in s)
check('checkout_clean_payments',all('(prueba local)' not in x.text_content() for x in d.xpath('//li[contains(@class,"wc_payment_method")]/label')))
_,_,s=req('my-account/');check('native_account_available','woocommerce-form-login' in s)
_,_,s=req('mi-cuenta/');check('spanish_alias_reaches_native_account','woocommerce-form-login' in s)
for n in [1,2,3]:
 matches=[(pid,p['data']) for pid,p in native['products'].items() if p['data']['status']=='publish' and 1+len(p['data']['gallery_image_ids'])==n]
 if not matches:print('No native product with '+str(n)+' images; no data modified',flush=True);continue
 pid,p=matches[0];_,_,s=req('product/'+p['slug']+'/');d=html.fromstring(s)
 check('gallery_'+str(n)+'_native_structure',bool(d.xpath('//div[@class="mk8-left"]//div[contains(@class,"woocommerce-product-gallery")]//img')) and ('ct-product-gallery-container' in s))
(O/('verificacion-V12-'+(sys.argv[1] if len(sys.argv)>1 else '1')+'.json')).write_text(json.dumps(checks,indent=2),encoding='utf8');print(f'{sum(checks.values())}/{len(checks)}');sys.exit(0 if all(checks.values()) else 1)
