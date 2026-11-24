"""HTTP/DOM evidence in isolated sessions. No checkout submission, orders or mail."""
from pathlib import Path
import urllib.request,urllib.parse,http.cookiejar,socket,json,sys,hashlib
from lxml import html
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v11-backups'
resolve=socket.getaddrinfo;socket.getaddrinfo=lambda host,*a,**kw:resolve('127.0.0.1' if host=='localhost' else host,*a,**kw)
op=urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
base='http://localhost:8082/wordpress/';checks={};evidence={}
def req(path,data=None):
 with op.open(base+path,urllib.parse.urlencode(data).encode() if data is not None else None,timeout=45) as res:return res.status,res.headers,res.read().decode()
def check(k,v):
 checks[k]=bool(v);print(('PASS ' if v else 'FAIL ')+k,flush=True)
def config(s,name):return json.JSONDecoder().raw_decode(s.split('var '+name+' = ',1)[1])[0]
native=json.loads((B/'native-before.json').read_text('utf8'))
products=sorted([(int(k),v['data']) for k,v in native['products'].items() if v['data']['status']=='publish' and v['data']['stock_status']=='instock' and v['data']['price']],key=lambda x:len(x[1]['name']),reverse=True)[:5]
evidence['products']=[{'id':k,'name':v['name']} for k,v in products]
for n,(pid,p) in enumerate(products,1):
 _,_,s=req('?wc-ajax=add_to_cart',{'product_id':pid,'quantity':1});j=json.loads(s)
 check(f'add_{n}_native_fragments',bool(j.get('fragments')) and not j.get('error'))
 if n not in (1,2,5):continue
 fragment=j['fragments']['div.widget_shopping_cart_content'];d=html.fromstring(fragment)
 rows=d.xpath('//li[contains(@class,"woocommerce-mini-cart-item")]')
 check(f'bag_{n}_rows',len(rows)==n)
 check(f'bag_{n}_name_and_controls',all(len(x.xpath('./a[not(contains(@class,"remove"))]/img'))==1 and len(x.xpath('./a/span[@class="mk11-mini-name"]'))==1 and len(x.xpath('./div[@class="mk5-mini-quantity"]//input'))==1 and len(x.xpath('./a[contains(@class,"remove")]/svg'))==1 for x in rows))
 check(f'bag_{n}_single_footer',len(d.xpath('//div[@class="mk9-bag-footer"]'))==1 and 'Comprar' in fragment and 'Ver carrito' in fragment)
 (O/f'V11-bolsa-{n}.html').write_text(fragment,encoding='utf8')
 _,_,s=req('cart/');d=html.fromstring(s);(O/f'V11-carrito-{n}.html').write_text(s,encoding='utf8')
 rows=d.xpath('//tr[contains(concat(" ",normalize-space(@class)," ")," cart_item ")]')
 check(f'cart_{n}_one_trash_per_row',len(rows)==n and all(len(x.xpath('.//a[contains(@class,"remove")]/svg'))==1 and x.xpath('.//a[contains(@class,"remove")]/@aria-label')==['Eliminar'] for x in rows))
 form=d.xpath('//form[contains(@class,"woocommerce-cart-form")]')[0]
 check(f'cart_{n}_tools_directly_after_table',len(form.xpath('./table/following-sibling::*[1][@class="mk5-cart-tools"]'))==1 and len(d.xpath('//button[@class="mk5-empty-cart"]'))==1)
 check(f'cart_{n}_related_in_left_column',len(form.xpath('./section[contains(@class,"mk5-cart-related")]'))==1)
 labels=form.xpath('.//section[contains(@class,"mk5-cart-related")]//span[contains(@class,"onsale")]')
 check(f'cart_{n}_percentage_labels',all(x.text_content().strip().startswith('- ') and '%' in x.text_content() for x in labels))
 check(f'cart_{n}_compact_progress_structure',len(d.xpath('//div[contains(@class,"cart_totals")]//div[@class="mk9-progress"]/svg'))==1)
 check(f'cart_{n}_coupon_pair',len(d.xpath('//div[contains(@class,"mk6-coupon-controls")]/input'))==1 and d.xpath('//div[contains(@class,"mk6-coupon-controls")]/button/text()')==['Aplicar'])
 ship=d.xpath('//tr[contains(@class,"woocommerce-shipping-totals")]')
 check(f'cart_{n}_native_shipping_fullwidth',bool(ship) and all(x.xpath('./td/@colspan')==['2'] and not x.xpath('./th') and x.xpath('.//input[starts-with(@name,"shipping_method")]') for x in ship))
_,h,s=req('checkout/');d=html.fromstring(s);(O/'V11-checkout.html').write_text(s,encoding='utf8')
check('checkout_server_fail_closed', 'mk9-secure-checkout' in d.xpath('//body/@class')[0] and s.index('id="mk9-secure-critical"')<s.index('<body') and 'body.mk9-secure-checkout:not(.mk-secure-ready)' in s)
check('checkout_neutral_and_delayed_failure',bool(d.xpath('//*[@id="mk9-failsafe"]/div[@class="mk11-loading"]')) and bool(d.xpath('//*[@id="mk9-failsafe"]/div[@class="mk11-failure"]/a')) and 'mk11-reveal 0s 4s forwards!important' in s)
check('checkout_header_lock',len(d.xpath('//header[@class="mk6-secure-header"]/p[@class="mk11-secure-label"]/svg'))==1)
footer=d.xpath('//footer[@class="mk9-secure-footer"]')[0]
check('checkout_public_footer_no_test_suffix','(prueba local)' not in footer.text_content().lower())
check('checkout_public_payment_labels_no_test_suffix',all('(prueba local)' not in x.text_content().lower() for x in d.xpath('//li[contains(@class,"wc_payment_method")]/label')))
check('checkout_single_native_order_button',len(d.xpath('//*[@id="place_order"]'))==1)
check('checkout_cache_headers','no-store' in h.get('Cache-Control','') and 'Cookie' in ','.join(h.get_all('Vary',[])))
state=config(s,'mk9')['state'];_,_,s=req('?wc-ajax=mk9_secure_status',{'token':state['token']})
check('checkout_status_native',json.loads(s).get('success'))
_,_,s=req('tienda/');mk5=config(s,'mk5')
_,_,s=req('wp-json/wc/store/v1/cart');store=json.loads(s);key=store['items'][0]['key']
_,_,s=req('?wc-ajax=mk5_quantity',{'nonce':mk5['nonce'],'key':key,'quantity':2});j=json.loads(s)
check('bag_quantity_ajax_preserves_name_wrapper','mk11-mini-name' in j.get('fragments',{}).get('div.widget_shopping_cart_content',''))
for slug in ['abaco-pedagogico-y-laberinto-de-madera','auto-con-control-remoto','set-de-circuitos-inicial']:
 _,_,s=req('product/'+slug+'/');d=html.fromstring(s)
 check(slug+'_v10_structure',len(d.xpath('//button[@data-mk8-dialog]'))==4 and len(d.xpath('//dialog[@class="mk8-dialog"]'))==4 and len(d.xpath('//nav[contains(@class,"breadcrumb")]'))==1 and len(d.xpath('//h2[normalize-space(.)="Comentarios"]'))==1)
for name in ['minikids-commerce-v5.js','minikids-commerce-v6.js','minikids-commerce-v9.js','minikids-commerce-v9.css']:
 status,_,_=req('wp-content/mu-plugins/'+name);check(name+'_http',status==200)
protected=json.loads((B/'protected-before.json').read_text('utf8'));diff=[p for p,v in protected.items() if not (R/p).is_file() or hashlib.sha256((R/p).read_bytes()).hexdigest()!=v]
check('protected_files_unchanged',not diff)
out={'checks':checks,'passed':sum(checks.values()),'total':len(checks),'evidence':evidence,'protected_count':len(protected),'protected_differences':diff,'scope':'HTTP/DOM only; no browser, no rendering or height measurements, no checkout/order submission or mail.'}
(O/f'verificacion-V11-{sys.argv[1] if len(sys.argv)>1 else "1"}.json').write_text(json.dumps(out,ensure_ascii=False,indent=2),encoding='utf8')
print(json.dumps({'passed':out['passed'],'total':out['total'],'failed':[k for k,v in checks.items() if not v]}));sys.exit(0 if all(checks.values()) else 1)
