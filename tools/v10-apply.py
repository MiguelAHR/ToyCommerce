from pathlib import Path
import runpy,json
R=Path(__file__).resolve().parents[1]; O=R.parent/'diseno_minikids/v5'
d=runpy.run_path(str(R/'tools/v9-apply.py'))
d['read'].__globals__['BACK']=R.parent/'local-private/v10-backups'
read,apply=d['read'],d['apply']; receipts=[]
s=read('minikids-commerce-v8.php')
s=s.replace(" }else echo '<p class=\"price\">'.$product->get_price_html().'</p>';", " }else echo '<div class=\"mk10-price-row\"><p class=\"price\">'.$product->get_price_html().'</p>';")
s=s.replace(" woocommerce_template_single_add_to_cart();", " if(!($product->is_on_sale()&&$s>0&&$s<$r))echo '</div>';\n woocommerce_template_single_add_to_cart();")
s=s.replace("foreach(['payment'=>'Métodos de pago','delivery'=>'Opciones de envío','returns'=>'Devoluciones y garantías'] as $id=>$label)echo '<button type=\"button\" data-mk8-dialog=\"'.esc_attr($id).'\">'.esc_html($label).'</button>';", "foreach(['payment'=>'Métodos de pago','delivery'=>'Opciones de envío','returns'=>'Devoluciones y garantías'] as $id=>$label)echo '<button type=\"button\" data-mk8-dialog=\"'.esc_attr($id).'\" aria-haspopup=\"dialog\" aria-controls=\"mk8-'.esc_attr($id).'\">'.mk10_help_icon($id).'<span>'.esc_html($label).'</span></button>';")
s=s.replace('class="button mk8-availability" data-mk8-dialog="availability"','class="button mk8-availability" data-mk8-dialog="availability" aria-haspopup="dialog" aria-controls="mk8-availability"')
s=s.replace('role="dialog" aria-modal="true"','role="dialog" tabindex="-1" aria-modal="true"')
s=s.replace("mk5_review_overview();comments_template();", "mk5_review_overview();mk10_native_reviews();")
s+='''
// V10: keep the native review list/form; only remove its redundant section heading.
function mk10_native_reviews(){
 ob_start();comments_template();$html=ob_get_clean();
 echo preg_replace('~<h2\\b[^>]*class="woocommerce-Reviews-title"[^>]*>.*?</h2>~s','',$html,1);
}
function mk10_help_icon($id){
 $paths=['payment'=>'M3 5h18v14H3zM3 10h18M7 15h4','delivery'=>'M2 5h12v12H2zM14 9h4l4 4v4h-8M6 17a2 2 0 1 0 0 4 2 2 0 0 0 0-4M18 17a2 2 0 1 0 0 4 2 2 0 0 0 0-4','returns'=>'M12 2 3 6v6c0 5 9 10 9 10s9-5 9-10V6zM8 12l3 3 5-6'];
 return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="'.esc_attr($paths[$id]).'"/></svg>';
}
// Native customizer read filter, scoped to product views; no theme/options edits.
add_filter('theme_mod_product_hero_elements',function($elements){
 if(!is_product())return $elements;
 return array_values(array_filter((array)$elements,fn($element)=>($element['id']??'')!=='breadcrumbs'));
});
'''
receipts.append(apply('minikids-commerce-v8.php',s))
receipts.append(apply('minikids-commerce-v8.js',(O/'minikids-commerce-v8.js').read_text('utf8')))
s=read('minikids-commerce-v8.css')
s+='''
/* V10: scoped product corrections; V9 cart/checkout rules stay unchanged. */
body.minikids-retail.single-product .summary .mk8-breadcrumb{color:#68717d;text-transform:none;letter-spacing:normal;margin:0 0 16px;font-size:12px;line-height:1.6}
body.minikids-retail.single-product .summary .mk8-breadcrumb a{color:inherit;text-transform:none}
body.minikids-retail.single-product .summary .mk10-price-row{display:flex;align-items:center;justify-content:flex-start;gap:12px;flex-wrap:wrap;margin:24px 0}
body.minikids-retail.single-product .summary .mk10-price-row>.price{display:block!important;text-align:left!important;margin:0!important;flex:0 0 auto;width:auto!important}
body.minikids-retail.single-product .summary .mk5-low-stock{display:inline-block;align-self:center;width:auto;margin:0;padding:6px 10px;background:#ffd60a;color:#172a41;border-radius:6px;font-size:12px;line-height:1.5}
body.minikids-retail.single-product .summary .mk8-price{display:grid!important;justify-content:start;text-align:left;grid-template-columns:max-content minmax(0,1fr);width:100%;margin:24px 0 12px}
body.minikids-retail.single-product .summary .mk8-price+ .mk5-low-stock{margin:0 0 20px}
body.minikids-retail.single-product .summary .mk8-price dd{text-align:left}
body.minikids-retail.single-product .summary .mk8-help .mk8-availability{display:flex!important;align-items:center;justify-content:center;width:100%!important;min-height:52px;background:#ffd60a!important;color:#172a41!important;border:0!important;border-radius:6px!important;padding:12px 16px;text-align:center;line-height:1.5}
body.minikids-retail.single-product .summary .mk8-help .mk8-availability:hover{background:#eac400!important}
body.minikids-retail.single-product .summary .single_add_to_cart_button{background:#0756ad!important;color:#fff!important;border-radius:6px!important}
body.minikids-retail.single-product .summary .single_add_to_cart_button:hover{background:#06458c!important}
body.minikids-retail.single-product .summary .mk8-help .mk5-help-links{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px 16px;border:1px solid #dce1e6;border-radius:8px;padding:12px;margin-top:20px}
body.minikids-retail.single-product .summary .mk8-help .mk5-help-links button{display:flex;align-items:center;justify-content:flex-start;gap:10px;background:transparent!important;color:#172a41!important;border:0!important;padding:8px 4px;font-size:13px;line-height:1.5;text-align:left;white-space:normal;min-width:0}
.mk8-help .mk5-help-links button:last-child{grid-column:1/-1}
.mk8-help .mk5-help-links svg{width:24px;height:24px;flex:0 0 24px;fill:none;stroke:#0756ad;stroke-width:1.6;stroke-linecap:round;stroke-linejoin:round}
body.minikids-retail.single-product .mk8-details table{margin-bottom:28px!important}
body.minikids-retail.single-product .mk8-details h3{font:700 28px/1.2 MKBarlow,sans-serif;color:#0756ad;margin:28px 0 20px!important}
body.minikids-retail.single-product .mk8-editorial>section{margin-bottom:28px}
.mk10-modal-overlay{position:fixed;inset:0;background:#10284099;z-index:1000000}
.mk8-dialog.is-open{display:block;position:fixed;inset:50% auto auto 50%;transform:translate(-50%,-50%);margin:0;z-index:1000001}
body.minikids-retail.single-product .summary :is(.mk8-help button,.single_add_to_cart_button):focus-visible{outline:3px solid #0756ad!important;outline-offset:4px;box-shadow:0 0 0 2px #fff}
'''
receipts.append(apply('minikids-commerce-v8.css',s))
(O/'aplicacion-V10.json').write_text(json.dumps(receipts,ensure_ascii=False,indent=2),encoding='utf8')
