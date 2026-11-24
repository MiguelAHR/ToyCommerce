from pathlib import Path
import runpy,json,urllib.request
urllib.request.install_opener(urllib.request.build_opener(urllib.request.ProxyHandler({})))
R=Path(__file__).resolve().parents[1];B=R.parent/'local-private/v13-backups'
d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=B
s=d['read']('minikids-commerce-v12.css')
s+='''
/* The progress bar spans the icon and text columns without changing bag markup. */
body.minikids-retail .mk5-native-cart .cart_totals table.shop_table .mk9-progress-row td .mk9-progress>div{display:contents!important}
body.minikids-retail .mk5-native-cart .cart_totals table.shop_table .mk9-progress-row td .mk9-progress>svg{grid-column:1;grid-row:1}
body.minikids-retail .mk5-native-cart .cart_totals table.shop_table .mk9-progress-row td .mk9-progress p{grid-column:2;grid-row:1;margin:0!important}
body.minikids-retail .mk5-native-cart .cart_totals table.shop_table .mk9-progress-row td .mk9-track{grid-column:1/-1;grid-row:2}
'''
receipt=d['apply']('minikids-commerce-v12.css',s)
(R.parent/'diseno_minikids/v5/aplicacion-V13-progreso.json').write_text(json.dumps(receipt,indent=2),encoding='utf8')
