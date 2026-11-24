"""Two read-only HTTP/DOM/native passes; no browser and no order submission."""
from pathlib import Path
import urllib.request,urllib.parse,urllib.error,http.cookiejar,socket,json,re,hashlib,subprocess,sys
from lxml import html
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5';BACK=ROOT.parent/'local-private/v8-backups'
resolve=socket.getaddrinfo;socket.getaddrinfo=lambda host,*a,**kw:resolve('127.0.0.1' if host=='localhost' else host,*a,**kw)
op=urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()));base='http://localhost:8082/wordpress/'
checks={};remaining={};pages={}
def request(path,data=None):
 body=None if data is None else urllib.parse.urlencode(data).encode()
 try:r=op.open(urllib.request.Request(base+path,data=body),timeout=45)
 except urllib.error.HTTPError as e:r=e
 return r.status,r.read().decode()
def check(key,value):checks[key]=bool(value)
paths={'home':'','lista':'tienda/','ficha':'product/osito-miel-con-lazo/','abaco':'product/abaco-pedagogico-y-laberinto-de-madera/','carrito':'cart/','cuenta':'my-account/','404':'v8-no-existe/'}
request('?wc-ajax=add_to_cart',{'product_id':278,'quantity':1});paths['checkout']='checkout/'
for name,path in paths.items():
 status,s=request(path);pages[name]=s;d=html.fromstring(s)
 (OUT/f'V8-{name}.html').write_text(s,encoding='utf8')
 check(name+'_http',status==(404 if name=='404' else 200));check(name+'_no_php_error',not re.search(r'(Fatal error|Parse error|Uncaught Error|Warning:)',s))
 for node in d.xpath('//script|//style'):node.drop_tree()
 visible=' '.join(d.xpath('//body//text()'));attributes=' '.join(d.xpath('//@aria-label|//@title|//@placeholder|//@data-success_message'))
 check(name+'_no_emoji',not re.search('[\U0001F000-\U0001FAFF\u2600-\u27BF\ufe0f]',visible))
 found=[]
 for pattern in [r'\bSKU\s*:',r'\bCATEGORY\s*:',r'Add to cart',r'has been added to your cart',r'\bSearch\b',r'\bRead more\b',r'\bNext\b',r'\bPrevious\b',r'\bRelated products\b',r'\bShipping\b',r'\bBilling\b',r'\bYour order\b',r'\bLost your password\b',r'\bPage not found\b']:
  found+=re.findall(pattern,visible+' '+attributes,re.I)
 remaining[name]=sorted(set(found))
 print(name,status,remaining[name],flush=True)
 if name in ['ficha','abaco'] and status==200:
  left=d.xpath('//div[@class="mk8-left"]');summary=d.xpath('//div[contains(concat(" ",normalize-space(@class)," ")," entry-summary ")]')
  check(name+'_two_columns_dom',len(left)==1 and len(summary)==1 and left[0].getparent()==summary[0].getparent())
  check(name+'_editorial_in_left',len(d.xpath('//div[@class="mk8-left"]//div[@class="mk8-editorial"]'))==1)
  check(name+'_gallery_in_left',len(d.xpath('//div[@class="mk8-left"]/div[@class="woocommerce-product-gallery"]'))==1)
  check(name+'_sections_order',[x.text_content() for x in d.xpath('//div[@class="mk8-editorial"]/section/h2')]==['Descripción del producto','Detalles del producto:','Comentarios'])
  check(name+'_no_tabs',not d.xpath('//*[contains(@class,"woocommerce-tabs")]'))
  check(name+'_no_legacy',all(x not in s for x in ['minikids-product-meta-badges','minikids-product-trust-badges','minikids-short-desc-subtitle','class="product_meta"']))
  check(name+'_review_once',s.count('class="mk5-review-overview"')==1)
  dialogs=d.xpath('//dialog');check(name+'_four_modals',len(dialogs)==4 and all(x.get('aria-modal')=='true' for x in dialogs))
  pay=d.get_element_by_id('mk8-payment');paytext=pay.text_content();check(name+'_payments_bacs_cod',all(x in paytext for x in ['Transferencia bancaria','Pago contra entrega']) and not any(x in paytext for x in ['Visa','Mastercard','Culqi','Yape','Mercado Pago']))
  availability=d.get_element_by_id('mk8-availability');check(name+'_no_pickup',not availability.xpath('.//table'))
  delivery=d.get_element_by_id('mk8-delivery').text_content();check(name+'_shipping_real',all(x in delivery for x in ['149','15.00','a validar']))
  check(name+'_no_gallery_sale_flash',not d.xpath('//div[@class="mk8-left"]//*[contains(@class,"onsale")]'))
  recommendations=d.xpath('//section[contains(@class,"mk6-recommendations")]');check(name+'_recommendations_outside_columns',len(recommendations)==1 and recommendations[0].getparent()==left[0].getparent().getparent())
 if name=='checkout':
  check('native_checkout_button_once',len(d.xpath('//*[@id="place_order"]'))==1)
  check('native_button_inside_payment',bool(d.xpath('//*[@id="payment"]//*[@id="place_order"]')))
  check('native_terms_required',bool(d.xpath('//input[@name="terms"]')))
  check('native_document_field',bool(d.xpath('//input[@name="billing_document"]')))
for suffix,expected in [('',600),('?mk6_ttl=15',15),('?mk6_ttl=1',600)]:
 status,s=request('checkout/'+suffix);check('ttl_'+str(expected)+suffix,bool(re.search(r'"ttl":"?'+str(expected)+r'"?[,}]',s)))
css=(ROOT/'wordpress/wp-content/mu-plugins/minikids-commerce-v8.css').read_text('utf8')
check('responsive_900_defined','@media(max-width:899px)' in css and '52fr' in css and '48fr' in css)
check('pay_visibility_rule','#mk6-step-2:not([hidden]) #payment{display:block!important}' in css)
before=json.loads((BACK/'native-before.json').read_text('utf8'));after=json.loads((BACK/'native-final.json').read_text('utf8'))
commercial=['sku','regular','sale','price','stock','image','gallery']
check('98_products_preserved',len(after['products'])==98)
check('commercial_unchanged',all({k:p[k] for k in commercial}=={k:next(x for x in after['products'] if x['id']==p['id'])[k] for k in commercial} for p in before['products']))
check('orders_identical',before['orders']==after['orders']);check('hpos_preserved',after['hpos']=='yes')
check('shipping_gateways_business_preserved',all(before[k]==after[k] for k in ['shipping','gateways','business']))
check('product_descriptions_no_emoji',not any(re.search('[\U0001F000-\U0001FAFF\u2600-\u27BF\ufe0f]',p['description']+p['short']+' '.join(p['care'] or [])) for p in after['products']))
check('product_description_no_exact_duplicates',all(len(lines:=re.findall(r'<li>(.*?)</li>',p['description']))==len(set(lines)) for p in after['products']))
protected=json.loads((ROOT.parent/'local-private/v6-backups/protected.json').read_text('utf8'));diff=[p for p,digest in protected.items() if not (ROOT/p).is_file() or hashlib.sha256((ROOT/p).read_bytes()).hexdigest()!=digest]
check('protected_files_unchanged',not diff)
for name in ['cliente','admin']:
 p=OUT/'correos'/f'{name}-vista-corregida.html'
 if p.exists():
  s=p.read_text('utf8');d=html.fromstring(s);text=' '.join(d.xpath('//text()'))
  for label,needle in [('name','Prueba QA'),('document','QA-LOCAL-V8'),('phone','999999999'),('billing_address','Prueba QA local sin despacho 123'),('product','Osito miel con lazo'),('payment','Transferencia bancaria'),('currency','S/'),('subtotal','26.90'),('shipping','15.00'),('total','41.90')]:check(name+'_preview_'+label,needle in text)
  check(name+'_preview_brand',bool(d.xpath('//img[contains(@src,"logo")]')) and '#0756ad' in s.lower())
  check(name+'_preview_no_emoji',not re.search('[\U0001F000-\U0001FAFF\u2600-\u27BF\ufe0f]',text))
check('sampled_english_strings_absent',not any(remaining.values()))
number=sys.argv[1] if len(sys.argv)>1 else '1'
result={'checks':checks,'passed':sum(checks.values()),'total':len(checks),'all_pass':all(checks.values()),'english_remaining_by_screen':remaining,'protected_checked':len(protected),'protected_differences':diff,'material_unverified':['Brevo credential absent; no delivered event','Original QA checkout email lacked phone; corrected preview is unsent','Product 41 image attempt rejected','Browser responsive/layout/modal keyboard/real countdown pending Claude'],'scope':'HTTP/lxml DOM, native CRUD snapshots and static CSS. Mail content checks use corrected in-memory previews, not the original checkout capture. No browser layout, painting, dialog keyboard or actual 10min navigation; mail capture is not delivery.'}
(OUT/f'verificacion-V8-{number}.json').write_text(json.dumps(result,ensure_ascii=False,indent=2),encoding='utf8')
print(json.dumps({'passed':result['passed'],'total':result['total'],'failed':[k for k,v in checks.items() if not v],'remaining':remaining},ensure_ascii=False,indent=2))
