"""HTTP/HTML/AJAX only. Isolated guest carts, no checkout submit/auth/mail."""
from pathlib import Path
import urllib.request,urllib.parse,urllib.error,http.cookiejar,json,sys,hashlib,runpy
from lxml import html
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v5-backups/v18';base='http://localhost:8082/wordpress/'
op=urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
checks={};case=sys.argv[1] if len(sys.argv)>1 else '1'
def req(path,data=None):
 body=urllib.parse.urlencode(data).encode() if data is not None else None
 try:r=op.open(base+path,body,timeout=45)
 except urllib.error.HTTPError as e:r=e
 return r.status,r.headers,r.read().decode()
def check(k,v):
 checks[k]=bool(v);print(('PASS ' if v else 'FAIL ')+k,flush=True)
def config(s,name):return json.JSONDecoder().raw_decode(s.split('var '+name+' = ',1)[1])[0]
def document(path,label):
 status,headers,s=req(path);d=html.fromstring(s)
 (B/(label+'-pass'+case+'.html')).write_text(s,encoding='utf8')
 check(label+'_http_200',status==200);check(label+'_v18_css_js',all('minikids-commerce-v18.'+ext in s for ext in ['css','js']))
 check(label+'_auth_3_views',set(d.xpath('//form[@data-mk12-view]/@data-mk12-view'))=={'login','register','lost'})
 return d,s
def cards(d,label):
 rows=d.xpath('//ul[contains(concat(" ",normalize-space(@class)," ")," products ")]/li[contains(concat(" ",normalize-space(@class)," ")," product ")]')
 check(label+'_cards_present',bool(rows))
 for n,c in enumerate(rows):
  rating=c.xpath('./div[@class="mk16-card-rating"]');space=c.xpath('./div[@class="mk18-rating-space"]')
  check(label+'_card_'+str(n)+'_one_rating_or_spacer',len(rating)+len(space)==1)
 check(label+'_no_duplicate_theme_rating',not d.xpath('//*[contains(@class,"ct-woo-card-rating")]'))
 check(label+'_stepper_add',all(c.xpath('.//*[@class="mk12-card-buy"]//*[@data-mk12-delta="-1"]') and c.xpath('.//*[@class="mk12-card-buy"]//*[@data-mk12-delta="1"]') and c.xpath('.//*[@class="mk12-card-buy"]/a[contains(@class,"ajax_add_to_cart")]') for c in rows))
 return rows
try:
 d,s=document('','home');cards(d,'home')
 check('home_hero_promos_sections',all(d.xpath('//*[contains(@class,"'+c+'")]') for c in ['mk4-hero','mk4-promos','mk4-section']))
 check('home_native_footer',bool(d.xpath('//footer[contains(@class,"minikids-site-footer")]')))
 d,s=document('tienda/','shop');rows=cards(d,'shop');check('shop_98_products',len(rows)==98)
 check('shop_offer_and_regular',any(c.xpath('.//del') for c in rows) and any(not c.xpath('.//del') for c in rows))
 check('shop_rated_and_unrated',any(c.xpath('./div[@class="mk16-card-rating"]') for c in rows) and any(c.xpath('./div[@class="mk18-rating-space"]') for c in rows))
 texts=json.loads((O/'comentarios_v18.json').read_text('utf8'));before=json.loads((O/'comentarios_antes.json').read_text('utf8'))
 for pid,p in before['products'].items():
  d,s=document('?p='+pid,'product_'+pid)
  check('product_'+pid+'_review_dialog',len(d.xpath('//*[@id="mk16-reviews-dialog"]'))==1)
  for c in [c for c in before['comments'] if str(c['product_id'])==pid]:
   nodes=d.xpath('//*[@id="comment-'+str(c['id'])+'"]//*[contains(@class,"mk16-review-body")]')
   check('comment_'+str(c['id'])+'_served',bool(nodes) and ' '.join(nodes[0].text_content().split())==texts[str(c['id'])])
  if pid=='136':
   cards(d,'product_related');check('gallery_thumbs_exist',bool(d.xpath('//*[@class="flexy-pills" and @data-type="thumbs"]/ol/li')))
   check('single_one_rating',len(d.xpath('//*[@class="mk16-single-rating"]'))==1 and not d.xpath('//*[@class="woocommerce-product-rating"]'))
   check('gallery_no_jquery_zoom','jquery.zoom' not in s and '/zoom.min.js' not in s)
   check('lightbox_exists',bool(d.xpath('//*[contains(@class,"pswp")]')))
 d,s=document('?p=476','product_empty');check('zero_review_dialog',bool(d.xpath('//*[@id="mk16-reviews-dialog"]')))
 for count in [1,3]:
  ids=[278] if count==1 else [136,348]
  for pid in ids:
   _,_,s=req('?wc-ajax=add_to_cart',{'product_id':pid,'quantity':1});check('add_'+str(pid),bool(json.loads(s).get('fragments')))
  d,s=document('cart/','cart_'+str(count));cards(d,'cart_related_'+str(count))
  check('cart_'+str(count)+'_native_rows',len(d.xpath('//tr[contains(@class,"cart_item")]'))==count)
  check('cart_'+str(count)+'_single_checkout_link',len(d.xpath('//*[contains(concat(" ",normalize-space(@class)," ")," checkout-button ")]'))==1)
  d,s=document('checkout/','checkout_'+str(count));state=config(s,'mk9')['state'];params=config(s,'wc_checkout_params')
  check('checkout_'+str(count)+'_notice',len(d.xpath('//*[@class="mk17-account-notice"]/a[@href="#mk12-auth"]'))==1)
  check('checkout_'+str(count)+'_timer',len(d.xpath('//*[@id="mk6-timer"]'))==1)
  check('checkout_'+str(count)+'_one_order_button',len(d.xpath('//*[@id="place_order"]'))==1)
  check('checkout_'+str(count)+'_summary_products',len(d.xpath('//*[@class="mk9-product"]'))==count)
  _,_,s=req('?wc-ajax=mk9_secure_status',{'token':state['token']});check('checkout_'+str(count)+'_active',json.loads(s).get('success'))
  _,_,s=req('?wc-ajax=update_order_review',{'security':params['update_order_review_nonce'],'post_data':urllib.parse.urlencode({'billing_country':'PE','billing_state':'LMA','billing_city':'Lima','mk_secure_token':state['token']})});fragments=json.loads(s).get('fragments',{})
  check('checkout_'+str(count)+'_native_fragments',all(k in fragments for k in ['.woocommerce-checkout-review-order-table','.woocommerce-checkout-payment','.mk9-products-slot','.mk9-summary .mk9-progress']))
  _,_,s=req('tienda/');d=html.fromstring(s)
  check('bag_'+str(count)+'_items',len(d.xpath('//*[@id="minikids-cart-drawer"]//li[contains(@class,"woocommerce-mini-cart-item")]'))==count)
  check('bag_'+str(count)+'_fragment_container',bool(d.xpath('//*[@id="minikids-cart-drawer"]//*[@class="widget_shopping_cart_content"]')))
finally:
 _,headers,s=req('wp-json/wc/store/v1/cart');cart=json.loads(s)
 for item in cart.get('items',[]):
  _,h,_=req('wp-json/wc/store/v1/cart');request=urllib.request.Request(base+'wp-json/wc/store/v1/cart/remove-item',data=json.dumps({'key':item['key']}).encode(),headers={'Content-Type':'application/json','Nonce':h['Nonce']})
  with op.open(request,timeout=45) as response:json.load(response)
 _,_,s=req('wp-json/wc/store/v1/cart');check('isolated_cart_cleaned',not json.loads(s)['items'])
result={'checks':checks,'passed':sum(checks.values()),'total':len(checks),'scope':'HTTP/HTML/AJAX only; no rendering at any width, auth submit, order submit or mail.'}
(O/('verificacion-V18-'+case+'.json')).write_text(json.dumps(result,ensure_ascii=False,indent=2),encoding='utf8')
print(str(result['passed'])+'/'+str(result['total']),flush=True)
assert all(checks.values()),[k for k,v in checks.items() if not v]
