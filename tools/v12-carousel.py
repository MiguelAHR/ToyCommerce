from pathlib import Path
import runpy,urllib.request,json
urllib.request.install_opener(urllib.request.build_opener(urllib.request.ProxyHandler({})))
R=Path(__file__).resolve().parents[1];d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=R.parent/'local-private/v12-backups'
s=d['read']('minikids-campanas-v4.js').replace("root.querySelector('.mk4-card-track')", "root.querySelector('.mk12-home-track ul.products')||root.querySelector('.mk4-card-track')")
receipt=d['apply']('minikids-campanas-v4.js',s)
(R.parent/'diseno_minikids/v5/aplicacion-V12-carrusel.json').write_text(json.dumps(receipt),encoding='utf8')
