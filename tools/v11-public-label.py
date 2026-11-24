from pathlib import Path
import runpy,json
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v11-backups'
d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=B
read,apply=d['read'],d['apply'];receipts=json.loads((O/'aplicacion-V11.json').read_text('utf8'))
s=read('minikids-commerce-v9.php')+'''
// Public checkout labels only: stored gateway configuration and behaviour stay native.
add_filter('woocommerce_gateway_title',function($title){
 return !is_admin()&&mk6_checkout()?preg_replace('/\\s*\\(prueba local\\)\\s*/iu','',$title):$title;
},999);
'''
receipts.append(apply('minikids-commerce-v9.php',s))
(O/'aplicacion-V11.json').write_text(json.dumps(receipts,ensure_ascii=False,indent=2),encoding='utf8')
