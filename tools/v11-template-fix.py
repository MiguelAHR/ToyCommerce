from pathlib import Path
import runpy,json
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v11-backups'
d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=B
read,apply=d['read'],d['apply'];receipts=json.loads((O/'aplicacion-V11.json').read_text('utf8'))
s=read('minikids-commerce-v9.php').replace('<span class="onsale">- ', '<span class="onsale">&#45; ')
s+='''
// Native Woo markup matches this drawer's grid; keep templates of both vendors intact.
add_filter('woocommerce_locate_template',function($template,$name){
 if($name==='cart/mini-cart.php')return WC()->plugin_path().'/templates/cart/mini-cart.php';
 return $template;
},1000,2);
'''
receipts.append(apply('minikids-commerce-v9.php',s))
(O/'aplicacion-V11.json').write_text(json.dumps(receipts,ensure_ascii=False,indent=2),encoding='utf8')
