"""Local MCP writes, backup=true, lint after each PHP and exact readback."""
from pathlib import Path
import runpy,json,subprocess,urllib.request
urllib.request.install_opener(urllib.request.build_opener(urllib.request.ProxyHandler({})))
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v14-backups';B.mkdir(exist_ok=True)
d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=B
read,apply=d['read'],d['apply'];receipts=[]
protected=runpy.run_path(str(R/'tools/v10-preflight.py'))['protected']
(B/'protected-before.json').write_text(json.dumps(protected()))
native=(R.parent/'local-private/v13-backups/native-snapshot.php').read_text('utf8').replace('v13-backups','v14-backups')
helper=B/'native-snapshot.php';helper.write_text(native,encoding='utf8')
subprocess.run(['C:/xampp/php/php.exe','-l',str(helper)],check=True)
subprocess.run(['C:/xampp/php/php.exe',str(helper),'before'],check=True)
s=read('minikids-estetica.css')
start=s.index('.woocommerce-noreviews::before {');end=s.index('}',start)+1
s=s[:start]+'.woocommerce-noreviews::before {\n  content: none !important;\n  display: none !important;\n}'+s[end:]
receipts.append(apply('minikids-estetica.css',s))
s=read('minikids-commerce-v9.php')
# V8 is registered only on product pages; it prevented V9 CSS from printing elsewhere.
assert "['minikids-commerce-v8']" in s
s=s.replace("['minikids-commerce-v8']","['minikids-commerce-v6']")
old="add_filter('woocommerce_cart_item_remove_link',function($html){return preg_replace('/(&times;|×)(<\\/a>)/u',mk9_icon('trash').'$2',$html);},150);"
assert old in s;s=s.replace(old,'// Removal markup is normalized once below, after other presentation filters.')
old="""add_filter('woocommerce_cart_item_remove_link',function($html){
 $html=preg_replace('/aria-label="[^"]*"/','aria-label="Eliminar producto"',$html,1);
 return preg_replace('~(<a\\b[^>]*>).*?(</a>)~s','$1'.mk9_icon('trash').'$2',$html,1);
},999);"""
new="""add_filter('woocommerce_cart_item_remove_link',function($html,$key){
 // Return one native link for the entire filter value, never an added control.
 $item=WC()->cart?WC()->cart->get_cart_item($key):null;
 if(!$item||empty($item['data']))return $html;
 $p=$item['data'];$mini=!empty($GLOBALS['mk11_mini_render']);
 return '<a role="button" href="'.esc_url(wc_get_cart_remove_url($key)).'" class="remove'.($mini?' remove_from_cart_button':'').'" aria-label="Eliminar producto" data-product_id="'.esc_attr($item['product_id']).'" data-product_sku="'.esc_attr($p->get_sku()).'" data-cart_item_key="'.esc_attr($key).'">'.mk9_icon('trash').'</a>';
},999,2);"""
assert old in s;s=s.replace(old,new)
receipts.append(apply('minikids-commerce-v9.php',s))
s=read('minikids-commerce-v12.css')
start=s.index('/* Reset inherited Woo/table alignment');end=s.index('body.minikids-retail .mk5-native-cart .cart_totals .woocommerce-shipping-totals',start)
s=s[:start]+s[end:]
start=s.index('/* The progress bar spans');s=s[:start]
s+='''/* V14: the native heading elements share one type scale and top line. */
body.minikids-retail .mk12-comments #mk12-comments-title,
body.minikids-retail .mk12-comments #review_form #reply-title{display:block!important;font-family:MKBarlow,'Barlow Condensed',sans-serif!important;font-size:28px!important;font-weight:700!important;line-height:1.2!important;letter-spacing:normal!important;text-transform:none!important;color:var(--brand-blue,#0756ad)!important;margin:0 0 20px!important;padding:0!important}
body.minikids-retail .mk12-comments #review_form,
body.minikids-retail .mk12-comments #respond{margin:0!important;padding:0!important}
'''
receipts.append(apply('minikids-commerce-v12.css',s))
s=read('minikids-commerce-v9.css')
s+='''
/* V14: one shared shipping component for bag and cart, including a full-width track. */
body.minikids-retail :is(.cart_totals,#minikids-cart-drawer) .mk9-progress{display:grid!important;grid-template-columns:24px minmax(0,1fr)!important;grid-template-rows:auto 6px!important;column-gap:8px!important;row-gap:6px!important;height:auto!important;min-height:64px!important;align-content:center!important}
body.minikids-retail :is(.cart_totals,#minikids-cart-drawer) .mk9-progress>svg{grid-column:1;grid-row:1}
body.minikids-retail :is(.cart_totals,#minikids-cart-drawer) .mk9-progress>div{display:contents!important}
body.minikids-retail :is(.cart_totals,#minikids-cart-drawer) .mk9-progress p{grid-column:2;grid-row:1;margin:0!important}
body.minikids-retail :is(.cart_totals,#minikids-cart-drawer) .mk9-track{display:block!important;grid-column:1/-1;grid-row:2;width:100%!important;height:6px!important;min-height:6px!important;background:#d7dfe7!important;border-radius:8px;overflow:hidden}
body.minikids-retail :is(.cart_totals,#minikids-cart-drawer) .mk9-track>span{display:block!important;height:100%!important;background:var(--mk9-blue,#0756ad)!important;border-radius:8px}
'''
receipts.append(apply('minikids-commerce-v9.css',s))
facts={k:d['c'].ability('minikids/get-option',{'name':k})['value'] for k in ['woocommerce_enable_myaccount_registration','woocommerce_registration_generate_username','woocommerce_registration_generate_password','mkp_mail_enabled']}
(O/'datos-V14.json').write_text(json.dumps(facts,indent=2),encoding='utf8')
(O/'aplicacion-V14.json').write_text(json.dumps(receipts,indent=2),encoding='utf8')
