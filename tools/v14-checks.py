# Supplemental assertions inserted into the existing HTTP verification pass.
from urllib.parse import urlsplit
_,_,s=req('product/abaco-pedagogico-y-laberinto-de-madera/');d=html.fromstring(s)
check('v14_empty_text_without_star_nodes',bool(d.xpath('//p[@class="woocommerce-noreviews"]')) and all(not x.xpath('.//*') and '★' not in x.text_content() for x in d.xpath('//p[@class="woocommerce-noreviews"]')))
check('v14_zero_average_empty_stars',len(d.xpath('//p[@class="mk13-zero-stars"]/span[.="☆"]'))==5 and not d.xpath('//*[@id="comments"]//div[contains(@class,"star-rating")]'))
css=(R/'wordpress/wp-content/mu-plugins/minikids-estetica.css').read_text('utf8')
rule=css.split('.woocommerce-noreviews::before {',1)[1].split('}',1)[0]
check('v14_empty_pseudo_removed_at_source','content: none !important' in rule and 'display: none !important' in rule)
css=(R/'wordpress/wp-content/mu-plugins/minikids-commerce-v12.css').read_text('utf8')
check('v14_shared_heading_rule','#mk12-comments-title,\nbody.minikids-retail .mk12-comments #review_form #reply-title{' in css and 'font-size:28px!important' in css and 'font-family:MKBarlow' in css)
css=(R/'wordpress/wp-content/mu-plugins/minikids-commerce-v9.css').read_text('utf8')
check('v14_shared_track_6px','grid-template-rows:auto 6px!important' in css and '.mk9-track>span{display:block!important;height:100%!important;background:var(--mk9-blue,#0756ad)!important' in css)

def cart_rows(d):return d.xpath('//tr[contains(concat(" ",normalize-space(@class)," ")," cart_item ")]')
def single_remove(rows):
 return bool(rows) and all(len(x.xpath('.//a[contains(@href,"remove_item=")]|.//button[@data-remove]|.//*[@aria-label="Eliminar producto"]'))==1 and len(x.xpath('.//td[@class="product-remove"]/a[@class="remove"][@aria-label="Eliminar producto"]/svg'))==1 for x in rows)
def store():return json.loads(req('wp-json/wc/store/v1/cart')[2])
def progress(d,total):
 bars=d.xpath('//tr[@class="mk9-progress-row"]//*[@role="progressbar"]')
 return len(bars)==1 and abs(float(bars[0].get('aria-valuenow'))-min(149,total))<0.001 and abs(float(bars[0].xpath('./span/@style')[0].split(':')[1].strip('%'))-min(100,total/149*100))<0.001
req('?wc-ajax=add_to_cart',{'product_id':40,'quantity':1})
_,_,s=req('cart/');d=html.fromstring(s)
check('v14_v9_stylesheet_actually_enqueued_cart',len(d.xpath('//link[@id="minikids-commerce-v9-css"]'))==1)
check('v14_all_row_remove_controls',single_remove(cart_rows(d)))
before=store();total=float(before['totals']['total_price']);first=cart_rows(d)[0];href=first.xpath('.//a[@class="remove"]/@href')[0]
check('v14_remove_native_nonce','_wpnonce=' in href and href.startswith(base+'cart/'))
_,_,s=req(href.removeprefix(base));d=html.fromstring(s);after=store()
check('v14_native_removal_one_less',len(after['items'])==len(before['items'])-1)
check('v14_native_removal_total_recalculated',float(after['totals']['total_price'])<total)
check('v14_remaining_rows_single_control',single_remove(cart_rows(d)))
(O/'V14-eliminar-despues.html').write_text(s,encoding='utf8')
check('v14_progress_recalculated_after_remove',progress(d,sum(int(x['totals']['line_subtotal'])/100 for x in after['items'])))

# Dedicated guest session: exact example 29.90, then 149.50 above the threshold.
op=urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
for qty,label in [(1,'below'),(4,'above')]:
 _,_,s=req('?wc-ajax=add_to_cart',{'product_id':39,'quantity':qty});j=json.loads(s)
 fragment=j['fragments']['div.widget_shopping_cart_content'];bag=html.fromstring(fragment)
 _,_,s=req('cart/');d=html.fromstring(s);cart=store();subtotal=sum(int(x['totals']['line_subtotal'])/100 for x in cart['items'])
 check('v14_'+label+'_proportional_progress',progress(d,subtotal))
 check('v14_'+label+'_same_bag_component',html.tostring(d.xpath('//tr[@class="mk9-progress-row"]/td/div')[0])==html.tostring(bag.xpath('//div[@class="mk9-progress"]')[0]))
 check('v14_'+label+'_one_remove_per_row',single_remove(cart_rows(d)))
 check('v14_'+label+'_threshold_text',('Tienes envío gratis' in d.xpath('//tr[@class="mk9-progress-row"]')[0].text_content())==(subtotal>=149))
 (O/('V14-progreso-'+label+'.html')).write_text(s,encoding='utf8')
_,_,s=req('checkout/');d=html.fromstring(s)
check('v14_v9_stylesheet_actually_enqueued_checkout',len(d.xpath('//link[@id="minikids-commerce-v9-css"]'))==1)
check('v14_checkout_fail_closed_kept','body.mk9-secure-checkout:not(.mk-secure-ready)' in s and len(d.xpath('//*[@id="place_order"]'))==1)
