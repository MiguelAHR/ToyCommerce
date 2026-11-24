from pathlib import Path
import json,sys,urllib.request,socket
R=Path(__file__).resolve().parents[1];sys.path.insert(0,str(R.parent));urllib.request.install_opener(urllib.request.build_opener(urllib.request.ProxyHandler({})))
resolve=socket.getaddrinfo;socket.getaddrinfo=lambda host,*a,**kw:resolve('127.0.0.1' if host=='localhost' else host,*a,**kw)
from diseno_mcp import client
c=client();facts={}
for k in ['woocommerce_enable_myaccount_registration','woocommerce_registration_generate_username','woocommerce_registration_generate_password','mkp_mail_enabled']:facts[k]=c.ability('minikids/get-option',{'name':k})['value']
B=R.parent/'local-private/v12-backups';a=json.loads((B/'native-before.json').read_text('utf8'));b=json.loads((B/'native-after.json').read_text('utf8'))
facts['product_changes']={}
for pid in a['products']:
 if a['products'][pid]!=b['products'][pid]:
  facts['product_changes'][pid]={k:{'before':v,'after':b['products'][pid]['data'][k]} for k,v in a['products'][pid]['data'].items() if v!=b['products'][pid]['data'][k]}
(R.parent/'diseno_minikids/v5/datos-V12.json').write_text(json.dumps(facts,ensure_ascii=False,indent=2),encoding='utf8');print(json.dumps(facts,ensure_ascii=True),flush=True)
