from pathlib import Path
import runpy,json,urllib.request
urllib.request.install_opener(urllib.request.build_opener(urllib.request.ProxyHandler({})))
R=Path(__file__).resolve().parents[1];B=R.parent/'local-private/v12-backups';O=R.parent/'diseno_minikids/v5'
d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=B;read,apply=d['read'],d['apply'];receipts=[]
s=read('minikids-retail.php').replace("esc_url(wc_get_page_permalink('myaccount'));?>\" class=\"mkr-account\"", "esc_url(mk12_account_url());?>\" class=\"mkr-account\"");receipts.append(apply('minikids-retail.php',s))
s=read('minikids-commerce-v9.php').replace('Ver carrito</a></div>', 'Ver carrito</a><a class="mk12-bag-account" href="\'.esc_url(mk12_account_url()).\'">Mi cuenta</a></div>');receipts.append(apply('minikids-commerce-v9.php',s))
for ext in ['php','css','js']:receipts.append(apply('minikids-commerce-v12.'+ext,(R/'tools'/('v12-source.'+ext)).read_text('utf8')))
(O/'aplicacion-V12-refinamiento.json').write_text(json.dumps(receipts,indent=2),encoding='utf8')
