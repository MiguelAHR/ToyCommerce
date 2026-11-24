from pathlib import Path
import runpy,json,urllib.request
urllib.request.install_opener(urllib.request.build_opener(urllib.request.ProxyHandler({})))
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v15-backups'
d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=B
receipts=[]
for name in ['minikids-commerce-v15.php','minikids-commerce-v15.css','minikids-commerce-v12.js']:
 receipts.append(d['apply'](name,(R/'tools/v15-stage'/name).read_text('utf8')))
(O/'aplicacion-V15-ajuste.json').write_text(json.dumps(receipts,indent=2))
