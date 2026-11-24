from pathlib import Path
import runpy,json,urllib.request,sys
urllib.request.install_opener(urllib.request.build_opener(urllib.request.ProxyHandler({})))
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v5-backups/v18'
d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=B
receipts=[]
for ext in (sys.argv[1:] or ['css','js','php']):
 assert ext in ['css','js','php']
 source=R/'tools'/('v18-presentation.'+ext+('.txt' if ext=='php' else ''))
 receipts.append({'file':'minikids-commerce-v18.'+ext,'receipt':d['apply']('minikids-commerce-v18.'+ext,source.read_text('utf8'))})
f=O/'aplicacion-V18.json';data=json.loads(f.read_text('utf8'));data.setdefault('followup_writes',[]).extend(receipts);f.write_text(json.dumps(data,ensure_ascii=False,indent=2),encoding='utf8')
