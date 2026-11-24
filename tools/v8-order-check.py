"""One native classic-checkout order. Private offline mail capture; always cleanup."""
from pathlib import Path
import sys,runpy,json,urllib.request,urllib.parse,http.cookiejar,re,html,subprocess,socket
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5';PRIVATE=ROOT.parent/'local-private';BACK=PRIVATE/'v8-backups'
resolve=socket.getaddrinfo;socket.getaddrinfo=lambda host,*a,**kw:resolve('127.0.0.1' if host=='localhost' else host,*a,**kw)
d=runpy.run_path(str(ROOT/'tools/v8-apply.py'));c=d['c']
baseline=json.loads((BACK/'native-before.json').read_text('utf8'));owner=baseline['business']['email']
assert baseline['admin_email']['recipient']==owner
assert not (PRIVATE/'v8-order-started.json').exists(),'Single-order guard: inspect the journal; never repeat blindly'
# Native editable Woo options only; preserve each prior value.
old=json.loads((BACK/'email-options.json').read_text('utf8'))
from lxml import html as lh
op=urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
BASE='http://localhost:8082/wordpress/'
def req(path,data=None):
 body=None if data is None else urllib.parse.urlencode(data).encode()
 with op.open(urllib.request.Request(BASE+path,data=body,headers={'User-Agent':'MiniKids-V8-Controlled-QA'}),timeout=60) as r:return r.read().decode(),dict(r.headers)
home,_=req('');doc=lh.fromstring(home);logos=doc.xpath('//div[@id="mk6-loader"]/img/@src');assert logos
options={'woocommerce_email_header_image':logos[0],'woocommerce_email_base_color':'#0756ad','woocommerce_email_background_color':'#f3f6fa','woocommerce_email_body_background_color':'#ffffff','woocommerce_email_text_color':'#172a41','woocommerce_email_footer_text':'MiniKids · '+baseline['business']['name']+' | '+owner}
for key,heading in [('woocommerce_customer_on_hold_order_settings','Tu pedido está en espera de transferencia'),('woocommerce_new_order_settings','Nuevo pedido')]:
 value=dict(old[key] or {});value['heading']=heading;value['enabled']='yes';options[key]=value
for key,value in options.items():
 c.ability('minikids/update-option',{'name':key,'value':value});assert c.ability('minikids/get-option',{'name':key})['value']==value
(OUT/'opciones-correo-V8.json').write_text(json.dumps(options,ensure_ascii=False,indent=2),encoding='utf8')
report={'checks':{},'provider_delivery':False,'mode':'offline native capture; credential missing'}
active=PRIVATE/'v8-qa-active.json';active.write_text(json.dumps({'enabled':True}),encoding='utf8')
try:
 req('?wc-ajax=add_to_cart',{'product_id':278,'quantity':1})
 page,_=req('checkout/');(OUT/'V8-checkout-before.html').write_text(page,encoding='utf8');doc=lh.fromstring(page)
 nonce=doc.xpath('//input[@name="woocommerce-process-checkout-nonce"]/@value')[0]
 data={'woocommerce-process-checkout-nonce':nonce,'_wp_http_referer':'/wordpress/checkout/','billing_first_name':'Prueba QA','billing_last_name':'MiniKids V8','billing_company':'QA LOCAL SIN DESPACHO','billing_country':'PE','billing_address_1':'Prueba QA local sin despacho 123','billing_address_2':'No representa una compra real','billing_city':'Lima','billing_state':'LMA','billing_postcode':'15001','billing_phone':'999999999','billing_email':owner,'billing_document':'QA-LOCAL-V8','ship_to_different_address':'0','payment_method':'bacs','shipping_method[0]':'flat_rate:2','terms-field':'1','order_comments':'Prueba QA V8 sin compra real. No cobrar ni despachar.'}
 raw,_=req('?wc-ajax=checkout',dict(data,billing_email='',terms='1'));r=json.loads(raw);report['checks']['incomplete_rejected']=r['result']=='failure';(OUT/'V8-checkout-invalid-fields.json').write_text(raw,encoding='utf8')
 raw,_=req('?wc-ajax=checkout',data);r=json.loads(raw);report['checks']['terms_missing_rejected']=r['result']=='failure' and ('términos' in r.get('messages','').lower());(OUT/'V8-checkout-invalid-terms.json').write_text(raw,encoding='utf8')
 assert all(report['checks'].values()),report
 (PRIVATE/'v8-order-started.json').write_text(json.dumps({'single_attempt':True}),encoding='utf8')
 raw,_=req('?wc-ajax=checkout',dict(data,terms='1'));r=json.loads(raw);(OUT/'V8-checkout-order-response.json').write_text(raw,encoding='utf8');report['checks']['native_checkout_success']=r['result']=='success'
 assert report['checks']['native_checkout_success'],r
 print('One native BACS checkout succeeded; capture and cleanup follow.',flush=True)
 subprocess.run(['C:/xampp/php/php.exe',str(ROOT/'tools/v8-order-audit.php')],cwd=ROOT,check=True)
finally:
 active.write_text(json.dumps({'enabled':False}),encoding='utf8')
 if (PRIVATE/'v8-order.json').exists():
  p=subprocess.run(['C:/xampp/php/php.exe',str(ROOT/'tools/cleanup-attention-qa.php'),'--v8'],cwd=ROOT,capture_output=True,text=True,encoding='utf8');print(p.stdout);report['cleanup_exit']=p.returncode
 d['apply']('minikids-qa-v8.php',"<?php\n/** V8 QA capture disabled after controlled test. */\n")
 (OUT/'pedido-controlado-V8.json').write_text(json.dumps(report,ensure_ascii=False,indent=2),encoding='utf8')
