"""Dos pasadas reales HTTP/Store API, solo sandbox controlado por config privada."""
import html,http.cookiejar,json,re,subprocess,urllib.request,urllib.error
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1];BASE='http://localhost:8082/wordpress'
config_path=ROOT/'local-private/config.json'
config=json.loads(config_path.read_text());assert config['DB_NAME']=='minikeds_dev2'
original=config_path.read_text();config['MKPN_MAIL_SANDBOX']=True;config_path.write_text(json.dumps(config),encoding='utf-8')
results=[];ids={'orders':[],'claims':[]}
ids_path=ROOT/'local-private/attention-qa.json'
if ids_path.exists():ids=json.loads(ids_path.read_text())
def check(name,ok):
 results.append({'name':name,'passed':bool(ok)});print(('OK ' if ok else 'FAIL ')+name,flush=True)
 if not ok:raise AssertionError(name)
def save_ids():ids_path.write_text(json.dumps(ids))
try:
 for iteration in [1,2]:
  opener=urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
  def request(path,data=None,json_body=False,headers=None):
   body=None if data is None else (json.dumps(data).encode() if json_body else urllib.parse.urlencode(data).encode())
   h={'User-Agent':'MiniKids-local-QA'};h.update(headers or {})
   if data is not None:h['Content-Type']='application/json' if json_body else 'application/x-www-form-urlencoded'
   with opener.open(urllib.request.Request(BASE+path,data=body,headers=h),timeout=60) as r:return r.read().decode(),r.headers
  for path in ['/nosotros/','/libro-de-reclamaciones/','/tienda/']:
   page,_=request(path);check(f'Pasada{iteration}: HTTP {path}','<html' in page)
  page,headers=request('/libro-de-reclamaciones/')
  check(f'Pasada{iteration}: datos y formulario','20609651530' in page and 'mkpr-form' in page and 'noindex' in headers.get('X-Robots-Tag',''))
  def hidden(name):return html.unescape(re.search(r'name="'+name+r'"[^>]*value="([^"]*)"',page)[1])
  form={'mkpr_submit':'1','mkpr_nonce':hidden('mkpr_nonce'),'mkpr_token':hidden('mkpr_token'),
        'mkpr_name':'Validación técnica MiniKids','mkpr_document':'QA-LOCAL','mkpr_address':'Validación local, sin reclamación real',
        'mkpr_email':'vektronova@gmail.com','mkpr_phone':'999999999','mkpr_guardian':'','mkpr_order':'','mkpr_amount':'49.90',
        'mkpr_product':'Validación técnica del formulario','mkpr_detail':'Prueba local de integración, sin reclamación comercial.',
        'mkpr_request':'Validar constancia privada y correo sandbox.','mkpr_type':'reclamo','mkpr_kind':'bien','mkpr_accept':'1'}
  invalid=dict(form,mkpr_nonce='invalido');response,_=request('/libro-de-reclamaciones/',invalid)
  check(f'Pasada{iteration}: nonce inválido rechazado','mkpr-errors' in response and 'Tu hoja ha sido registrada' not in response)
  response,_=request('/libro-de-reclamaciones/',form)
  check(f'Pasada{iteration}: registro y constancia','Tu hoja ha sido registrada' in response)
  folio=re.search(r'MK-\d{4}-(\d{6})',response)[1];ids['claims'].append(int(folio));save_ids()
  response,_=request('/libro-de-reclamaciones/',form)
  check(f'Pasada{iteration}: duplicado rechazado','La hoja ya fue enviada' in response)
  try:request('/wp-json/wp/v2/mkp_claim');private=False
  except urllib.error.HTTPError as e:private=e.code in (401,403,404)
  check(f'Pasada{iteration}: reclamaciones sin REST público',private)
  raw,headers=request('/wp-json/wc/store/v1/cart');nonce=headers.get('Nonce');cart=json.loads(raw)
  check(f'Pasada{iteration}: Store API disponible',nonce is not None)
  # Producto publicado con stock, seleccionado por la Store API nativa.
  raw,_=request('/wp-json/wc/store/v1/products?stock_status=instock&per_page=1');product=json.loads(raw)[0]
  api_headers={'Nonce':nonce}
  raw,_=request('/wp-json/wc/store/v1/cart/add-item',{'id':product['id'],'quantity':1},True,api_headers)
  check(f'Pasada{iteration}: carrito Store API',len(json.loads(raw)['items'])==1)
  address={'first_name':'Validación','last_name':'MiniKids','company':'','address_1':'Validación técnica local','address_2':'','city':'Lima','state':'LMA','postcode':'15001','country':'PE','phone':'999999999'}
  billing=dict(address,email='vektronova@gmail.com')
  raw,_=request('/wp-json/wc/store/v1/cart/update-customer',{'billing_address':billing,'shipping_address':address},True,api_headers)
  cart=json.loads(raw)
  if cart.get('shipping_rates'):
   package=cart['shipping_rates'][0];rate=package['shipping_rates'][0]['rate_id']
   request('/wp-json/wc/store/v1/cart/select-shipping-rate',{'package_id':package['package_id'],'rate_id':rate},True,api_headers)
  raw,_=request('/wp-json/wc/store/v1/checkout',{'billing_address':billing,'shipping_address':address,'payment_method':'bacs','payment_data':[],'additional_fields':{'minikids/celular':'999999999'},'create_account':False},True,api_headers)
  order=json.loads(raw);ids['orders'].append(order['order_id']);save_ids()
  check(f'Pasada{iteration}: compra real sin cobro',order['status']=='on-hold')
  raw=subprocess.check_output([r'C:\xampp\php\php.exe',str(ROOT/'tools/attention-status.php')],cwd=ROOT)
  status=json.loads(raw)
  check(f'Pasada{iteration}: pedido HPOS y celular',status['hpos'] and status['orders'][-1]['phone']=='999999999' and 'OrdersTableDataStore' in status['orders'][-1]['store'])
  row=status['orders'][-1]
  check(f'Pasada{iteration}: resumen cliente y admin',row['customer'].get('accepted') and row['new_order'].get('accepted') and row['customer'].get('sandbox'))
  claim=status['claims'][-1]
  check(f'Pasada{iteration}: hoja privada y dos constancias',claim['private'] and claim['mail'].get('customer') and claim['mail'].get('provider'))
finally:
 config_path.write_text(original,encoding='utf-8')
 (ROOT/'brain/attention-http-checks.json').write_text(json.dumps(results,indent=2,ensure_ascii=False),encoding='utf-8')
