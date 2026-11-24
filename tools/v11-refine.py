from pathlib import Path
import runpy,json
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v11-backups'
d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=B
read,apply=d['read'],d['apply'];receipts=json.loads((O/'aplicacion-V11.json').read_text('utf8'))
s=read('minikids-commerce-v5.js').replace("  const empty=qs('.mk5-empty-cart');if(empty)empty.addEventListener('click',async()=>{", "  $(document).on('click','.mk5-empty-cart',async function(){\n   const empty=this;if(empty.disabled)return;")
receipts.append(apply('minikids-commerce-v5.js',s))
s=read('minikids-commerce-v6.js').replace('()=>{quantities();cart();});','()=>{quantities();cart();recommendations();});')
receipts.append(apply('minikids-commerce-v6.js',s))
s=read('minikids-commerce-v9.php')
s=s.replace('#mk9-failsafe .mk11-failure{visibility:hidden;animation:mk11-reveal 0s 4s forwards}', 'body.mk9-secure-checkout #mk9-failsafe .mk11-failure{visibility:hidden;animation:mk11-reveal 0s 4s forwards!important}')
s=s.replace('body.mk-secure-failed #mk9-failsafe .mk11-failure{visibility:visible;animation:none}', 'body.mk9-secure-checkout.mk-secure-failed #mk9-failsafe .mk11-failure{visibility:visible;animation:none!important}')
s=s.replace('#mk11-failure-watch{position:', 'body.mk9-secure-checkout #mk11-failure-watch{position:').replace('animation:mk11-watch 4s linear}', 'animation:mk11-watch 4s linear!important}')
s+='''
// Full-width shipping row, preserving Woo shipping controls and calculator markup.
add_action('woocommerce_cart_totals_before_shipping',function(){ob_start();},0);
add_action('woocommerce_cart_totals_after_shipping',function(){
 $html=ob_get_clean();
 $html=preg_replace('~<th\\b[^>]*>.*?</th>~s','',$html);
 echo preg_replace('~<td\\b~','<td colspan="2"',$html);
},999);
'''
receipts.append(apply('minikids-commerce-v9.php',s))
s=read('minikids-commerce-v9.js').replace('Math.max(0,4000-performance.now())', "Math.max(0,4000-(performance.now()-(performance.getEntriesByType('navigation')[0]?.responseStart||0)))")
receipts.append(apply('minikids-commerce-v9.js',s))
s=read('minikids-commerce-v9.css').replace('.woocommerce-shipping-totals{display:block}', '.woocommerce-shipping-totals{display:table-row}').replace('.woocommerce-shipping-totals>td{display:block!important;', '.woocommerce-shipping-totals>td{display:table-cell!important;')
receipts.append(apply('minikids-commerce-v9.css',s))
(O/'aplicacion-V11.json').write_text(json.dumps(receipts,ensure_ascii=False,indent=2),encoding='utf8')
