"""Two HTTP passes; no browser, recovery submission, registration or order."""
from pathlib import Path
R=Path(__file__).resolve().parents[1]
source=(R/'tools/v12-verify.py').read_text('utf8').replace('V12','V13').replace('v12-backups','v13-backups')
extra='''
css=(R/'wordpress/wp-content/mu-plugins/minikids-commerce-v12.css').read_text('utf8')
check('css_tag_inset','right:-5px' not in css and 'top:10px!important;right:8px!important' in css)
check('css_purchase_grid','grid-template-columns:minmax(0,45fr) minmax(0,55fr)!important' in css and 'height:40px!important' in css)
check('css_auth_brand_override','body.minikids-retail #mk12-auth .mk12-auth-submit{background:#0756ad!important' in css)
check('css_no_offer_pseudo','content: "OFERTA"' not in (R/'wordpress/wp-content/mu-plugins/minikids-estetica.css').read_text('utf8'))
check('css_no_trash_mask_source','mask:url' not in (R/'wordpress/wp-content/mu-plugins/minikids-commerce-v5.css').read_text('utf8'))
for path,label in [('','home_v13'),('tienda/','shop_v13'),('product/abaco-pedagogico-y-laberinto-de-madera/','product_v13'),('cart/','cart_v13')]:
 code,_,s=req(path);d=html.fromstring(s)
 check(label+'_http',code==200)
 items=d.xpath('//li[contains(concat(" ",normalize-space(@class)," ")," product ")]')
 check(label+'_brand_dom_before_title',bool(items) and all(x.xpath('./span[@class="mk5-card-brand"]/following-sibling::h2[contains(@class,"woocommerce-loop-product__title")]') for x in items))
 check(label+'_purchase_siblings',all(len(x.xpath('./div[@class="mkr-quantity"]/following-sibling::a[contains(@class,"ajax_add_to_cart")]'))==1 for x in d.xpath('//div[@class="mk12-card-buy"]')))
 if label=='home_v13':check('five_live_carousels',len(d.xpath('//div[contains(@class,"mk12-home-track")]'))==5)
 if label=='product_v13':
  check('two_review_siblings',len(d.xpath('//*[@id="reviews"]/*[@id="comments"]'))==1 and len(d.xpath('//*[@id="reviews"]/*[@id="review_form_wrapper"]'))==1)
  check('overview_and_list_same_column',len(d.xpath('//*[@id="comments"]/section[@class="mk5-review-overview"]'))==1 and bool(d.xpath('//*[@id="comments"]//p[@class="woocommerce-noreviews"][contains(.,"Sé el primero en opinar")]')))
  check('native_form_title',d.xpath('//*[@id="reply-title"]')[0].text_content().startswith('Escribe tu comentario'))
  check('single_comments_title_left',len(d.xpath('//*[@id="comments"]/h2[.="Comentarios"]'))==1)
  check('auth_subtitle',d.xpath('//dialog[@id="mk12-auth"]/p[@class="mk13-auth-subtitle"]/text()')==['Accede a tu cuenta'])
  check('auth_fallback_label',d.xpath('//a[@class="mk12-account-fallback"]/text()')==['Ir a Mi cuenta'])
  facts=json.loads((O/'datos-V13.json').read_text('utf8'))
  enabled=facts['woocommerce_enable_myaccount_registration']=='yes'
  check('register_matches_native_setting',bool(d.xpath('//form[@data-mk12-view="register"]'))==enabled and bool(d.xpath('//a[@data-mk12-switch="register"]'))==enabled)
 if label=='cart_v13':
  rows=d.xpath('//td[@class="product-remove"]')
  check('single_native_remove_svg',bool(rows) and all(len(x.xpath('./a[@aria-label="Eliminar producto"]/svg'))==1 and len(x.xpath('./a'))==1 for x in rows))
  check('shipping_label_name_cost',bool(d.xpath('//tr[contains(@class,"woocommerce-shipping-totals")]//label/span[@class="mk13-shipping-name"]')) and bool(d.xpath('//span[@class="mk13-shipping-cost"]')))
  check('shipping_native_calculator_kept',bool(d.xpath('//form[@class="woocommerce-shipping-calculator"]//input[@name="woocommerce-shipping-calculator-nonce"]')))
  check('shipping_destination_prefix',bool(d.xpath('//p[@class="woocommerce-shipping-destination"][starts-with(.,"Enviar a ")]')))
  check('shipping_heading_removed',not d.xpath('//div[@class="ct-shipping-heading"]'))
  check('progress_uses_bag_component',bool(d.xpath('//tr[@class="mk9-progress-row"]//div[@class="mk9-progress"]/svg')) and bool(d.xpath('//tr[@class="mk9-progress-row"]//div[@class="mk9-track"]')))
 (O/('V13-'+label+'.html')).write_text(s,encoding='utf8')
'''
source=source.replace("(O/('verificacion-V13-",extra+"\n(O/('verificacion-V13-")
exec(compile(source,str(R/'tools/v12-verify.py'),'exec'),{'__file__':str(R/'tools/v12-verify.py'),'__name__':'__main__'})
