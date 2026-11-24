"""Local MCP writes only; inherited writer enforces backup, readback and PHP lint."""
from pathlib import Path
import runpy,json,subprocess
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v11-backups';B.mkdir(exist_ok=True)
d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=B
read,apply=d['read'],d['apply'];receipts=[]
# Read-only baseline: native Woo data and protected files, never normalize products.
native=(R/'tools/v10-native.php').read_text('utf8').replace('v10-backups/','v11-backups/')
helper=B/'native-snapshot.php';helper.write_text(native.replace("require dirname(__DIR__).'/wordpress/wp-load.php';", "require '"+str(R/'wordpress/wp-load.php').replace('\\','/')+"';").replace("dirname(__DIR__,2).'/local-private/v11-backups/'", "'"+str(B).replace('\\','/')+"/'"),encoding='utf8')
subprocess.run(['C:/xampp/php/php.exe','-l',str(helper)],check=True)
subprocess.run(['C:/xampp/php/php.exe',str(helper),'before'],check=True)
protected=runpy.run_path(str(R/'tools/v10-preflight.py'))['protected']
(B/'protected-before.json').write_text(json.dumps(protected()))
s=read('minikids-commerce-v5.php')
start=s.index('// Presentation below the complete cart;');end=s.index("add_filter('woocommerce_order_button_text'",start)
s=s[:start]+'''// V11: tools and recommendations follow the native table in its left column.
add_action('woocommerce_after_cart_table',function(){
    echo '<div class="mk5-cart-tools"><button type="button" class="mk5-empty-cart">Vaciar carrito de compra</button><span role="status" class="mk5-cart-status"></span></div>';
    $exclude=WC()->cart?array_column(WC()->cart->get_cart(),'product_id'):[];$ids=[];
    foreach($exclude as $id)$ids=array_merge($ids,wc_get_related_products($id,8,$exclude));
    $ids=array_slice(array_values(array_unique($ids)),0,10);
    if(!$ids)$ids=wc_get_products(['status'=>'publish','limit'=>10,'return'=>'ids','orderby'=>'popularity','exclude'=>$exclude]);
    if(function_exists('mk6_recommendations'))mk6_recommendations($ids,'Artículos relacionados','mk5-cart-related');
});
'''+s[end:]
receipts.append(apply('minikids-commerce-v5.php',s))
s=read('minikids-commerce-v6.php').replace('<p>Compra segura</p>', '<p class="mk11-secure-label">\'.mk9_icon(\'lock\').\' Compra segura</p>')
s=s.replace('>Aplicar cupón</button>','>Aplicar</button>')
receipts.append(apply('minikids-commerce-v6.php',s))
s=read('minikids-commerce-v9.php')
s=s.replace('#mk9-failsafe a{', '''#mk9-failsafe .mk11-failure{visibility:hidden;animation:mk11-reveal 0s 4s forwards}body.mk-secure-failed #mk9-failsafe .mk11-failure{visibility:visible;animation:none}body.mk-secure-failed #mk9-failsafe .mk11-loading{display:none}body.mk-secure-ready #mk11-failure-watch{display:none}#mk11-failure-watch{position:absolute;width:1px;height:1px;pointer-events:none;animation:mk11-watch 4s linear}#mk9-failsafe .mk11-loading{height:120px;background:linear-gradient(#edf0f4 0 0) 0 0/100% 48px no-repeat,linear-gradient(#edf0f4 0 0) 0 64px/100% 48px no-repeat;border-radius:8px}body.mk-secure-failed #mk9-failsafe .mk11-failure{min-height:0}@keyframes mk11-reveal{to{visibility:visible}}@keyframes mk11-watch{from{opacity:.99}to{opacity:1}}#mk9-failsafe a{''')
s=s.replace('<section id="mk9-failsafe" role="status"><p>', '<span id="mk11-failure-watch" aria-hidden="true"></span><section id="mk9-failsafe" role="status"><div class="mk11-loading" aria-label="Preparando compra segura"></div><div class="mk11-failure"><p>')
s=s.replace('>Volver al carrito</a></section>', '>Volver al carrito</a></div></section>')
s=s.replace("esc_html($g->get_title())", "esc_html(preg_replace('/\\s*\\(prueba local\\)\\s*/iu','',$g->get_title()))")
s+='''
// V11: scope name wrapping to native mini-cart renders (including Ajax fragments).
add_action('woocommerce_before_mini_cart',function(){$GLOBALS['mk11_mini_render']=true;},0);
add_action('woocommerce_after_mini_cart',function(){$GLOBALS['mk11_mini_render']=false;},999);
add_filter('woocommerce_cart_item_name',function($name){
 return !empty($GLOBALS['mk11_mini_render'])?'<span class="mk11-mini-name">'.$name.'</span>':$name;
},999);
// Keep native href/nonces/data attributes; render exactly one decorative trash SVG.
add_filter('woocommerce_cart_item_remove_link',function($html){
 $html=preg_replace('/aria-label="[^"]*"/','aria-label="Eliminar"',$html,1);
 return preg_replace('~(<a\\b[^>]*>).*?(</a>)~s','$1'.mk9_icon('trash').'$2',$html,1);
},999);
// Same percentage label in cart recommendations as in catalogue cards.
add_filter('woocommerce_sale_flash',function($html,$post,$p){
 if(!is_cart())return $html;
 $r=(float)$p->get_regular_price();$s=(float)$p->get_sale_price();
 return $r>0&&$s>0&&$s<$r?'<span class="onsale">- '.esc_html((string)floor(100*($r-$s)/$r)).' %</span>':'';
},999,3);
'''
receipts.append(apply('minikids-commerce-v9.php',s))
s=read('minikids-commerce-v9.js')
s=s.replace("  if(!mk9.checkout)return;", """  if(!mk9.checkout)return;
  // The CSS animation starts with the parsed body, before this footer module.
  function failIfUnready(){if(!document.body.classList.contains('mk-secure-ready'))document.body.classList.add('mk-secure-failed');}
  const watch=one('#mk11-failure-watch');
  watch?.addEventListener('animationend',e=>{if(e.animationName==='mk11-watch')failIfUnready();});
  setTimeout(failIfUnready,Math.max(0,4000-performance.now()));""")
s=s.replace("if(mk9.state?.expired){", "if(mk9.state?.expired){\n   document.body.classList.add('mk-secure-failed');")
s=s.replace("if(expired)return;expired=true;", "if(expired)return;document.body.classList.add('mk-secure-failed');expired=true;")
s=s.replace("document.body.classList.add('mk-secure-ready');", "document.body.classList.remove('mk-secure-failed');document.body.classList.add('mk-secure-ready');")
receipts.append(apply('minikids-commerce-v9.js',s))
s=read('minikids-commerce-v9.css')+'\n'+(O/'v11-corrections.css').read_text('utf8')
receipts.append(apply('minikids-commerce-v9.css',s))
(O/'aplicacion-V11.json').write_text(json.dumps(receipts,ensure_ascii=False,indent=2),encoding='utf8')
