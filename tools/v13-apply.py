"""Scoped V13 corrections through local MCP with backup and lint/readback."""
from pathlib import Path
import runpy,json,subprocess,urllib.request
urllib.request.install_opener(urllib.request.build_opener(urllib.request.ProxyHandler({})))
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v13-backups';B.mkdir(exist_ok=True)
d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=B
read,apply=d['read'],d['apply'];receipts=[]
protected=runpy.run_path(str(R/'tools/v10-preflight.py'))['protected']
(B/'protected-before.json').write_text(json.dumps(protected()))
native=(R.parent/'local-private/v12-backups/native-snapshot.php').read_text('utf8').replace('v12-backups','v13-backups')
helper=B/'native-snapshot.php';helper.write_text(native,encoding='utf8')
subprocess.run(['C:/xampp/php/php.exe','-l',str(helper)],check=True)
subprocess.run(['C:/xampp/php/php.exe',str(helper),'before'],check=True)
s=read('minikids-commerce-v12.php')
old=''' echo '<section class="mk12-comments" aria-labelledby="mk12-comments-title"><h2 id="mk12-comments-title">Comentarios</h2><div class="mk12-review-grid">';
 mk5_review_overview();echo '<div class="mk12-native-reviews">';mk10_native_reviews();echo '</div></div></section>';'''
new=''' // Keep the native list and form siblings: overview/list left, form right.
 ob_start();mk5_review_overview();$overview=ob_get_clean();
 $overview=preg_replace('~<p>Aún no hay comentarios\\..*?</p>~s','',$overview);
 global $product;
 $overview.='<div class="mk13-review-stars">'.wc_get_rating_html((float)$product->get_average_rating(),$product->get_rating_count()).'</div>';
 if(!$product->get_rating_count())$overview.='<p class="mk13-zero-stars" aria-label="Sin valoraciones">'.str_repeat('<span aria-hidden="true">☆</span>',5).'</p>';
 ob_start();mk10_native_reviews();$reviews=ob_get_clean();
 $reviews=str_replace('<div id="comments">','<div id="comments"><h2 id="mk12-comments-title">Comentarios</h2>'.$overview,$reviews);
 $reviews=preg_replace('~(<p class="woocommerce-noreviews">).*?(</p>)~s','$1Aún no hay comentarios. Sé el primero en opinar.$2',$reviews);
 echo '<section class="mk12-comments" aria-labelledby="mk12-comments-title"><div class="mk12-native-reviews">'.$reviews.'</div></section>';'''
assert old in s;s=s.replace(old,new)
s=s.replace('</h2>\';\n $views=', '</h2><p class="mk13-auth-subtitle">Accede a tu cuenta</p>\';\n $views=')
s=s.replace('¿No tienes una cuenta?', '¿No tienes cuenta?').replace('Abrir Mi cuenta</a>', 'Ir a Mi cuenta</a>')
s+='''
// The review form remains WooCommerce's own renderer and validation flow.
add_filter('woocommerce_product_review_comment_form_args',function($args){
 $args['title_reply']='Escribe tu comentario';return $args;
});
'''
receipts.append(apply('minikids-commerce-v12.php',s))
s=read('minikids-commerce-v12.css')
s=s.replace('top:12px!important;right:-5px!important','top:10px!important;right:8px!important')
s+='\n'+(R/'tools/v13-style.css').read_text('utf8')
receipts.append(apply('minikids-commerce-v12.css',s))
s=read('minikids-commerce-v12.js').replace("  one('#mk12-auth-title',active).textContent=", "  one('.mk13-auth-subtitle',active).textContent={login:'Accede a tu cuenta',lost:'Recupera el acceso a tu cuenta',register:'Únete a MiniKids'}[name];\n  one('#mk12-auth-title',active).textContent=")
receipts.append(apply('minikids-commerce-v12.js',s))
s=read('minikids-commerce-v9.php').replace('aria-label="Eliminar"','aria-label="Eliminar producto"')
old=" $html=preg_replace('~<th\\b[^>]*>.*?</th>~s','',$html);"
new=old+'''
 $html=preg_replace('~<div class="ct-shipping-heading">.*?</div>~s','',$html);
 // Preserve native inputs, prices/taxes and calculator; only arrange their labels.
 $html=preg_replace_callback('~(<label\\b[^>]*>)(.*?)(</label>)~s',function($m){
  $parts=preg_split('~:\\s*(?=<span)~',$m[2],2);
  return $m[1].'<span class="mk13-shipping-name">'.$parts[0].'</span>'.(isset($parts[1])?'<span class="mk13-shipping-cost">'.$parts[1].'</span>':'').$m[3];
 },$html);
'''
assert old in s;s=s.replace(old,new)
s=s.replace("$m[1].$destination[0].$m[3]", "$m[1].'Enviar a '.$destination[0].$m[3]")
receipts.append(apply('minikids-commerce-v9.php',s))
# Remove the obsolete decorative trash at its source, rather than layering over it.
s=read('minikids-commerce-v5.css');start=s.index('.minikids-retail :is(.widget_shopping_cart_content,.mk5-native-cart) a.remove::before{');end=s.index('}',start)+1
s=s[:start]+'.minikids-retail :is(.widget_shopping_cart_content,.mk5-native-cart) a.remove::before{content:none!important;display:none!important}'+s[end:]
receipts.append(apply('minikids-commerce-v5.css',s))
# The older global pseudo-element must never prepend OFERTA to percentage tags.
s=read('minikids-estetica.css').replace('content: "OFERTA" !important;', 'content: none !important;')
receipts.append(apply('minikids-estetica.css',s))
facts={k:d['c'].ability('minikids/get-option',{'name':k})['value'] for k in ['woocommerce_enable_myaccount_registration','woocommerce_registration_generate_username','woocommerce_registration_generate_password','mkp_mail_enabled']}
(O/'datos-V13.json').write_text(json.dumps(facts,indent=2),encoding='utf8')
(O/'aplicacion-V13.json').write_text(json.dumps(receipts,indent=2),encoding='utf8')
