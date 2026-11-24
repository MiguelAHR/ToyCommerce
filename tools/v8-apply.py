"""Owned files only: MCP read, private backup, backup=true write, lint and exact readback."""
from pathlib import Path
import sys,socket,subprocess,json
ROOT=Path(__file__).resolve().parents[1];sys.path.insert(0,str(ROOT.parent))
resolve=socket.getaddrinfo;socket.getaddrinfo=lambda host,*a,**kw:resolve('127.0.0.1' if host=='localhost' else host,*a,**kw)
from diseno_mcp import client
OUT=ROOT.parent/'diseno_minikids/v5';BACK=ROOT.parent/'local-private/v8-backups';BACK.mkdir(exist_ok=True)
c=client();assert c.url.startswith('http://localhost:8082/wordpress/')
def apply(name,data):
 try:
  old=c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']
  if not (BACK/name).exists():(BACK/name).write_text(old,encoding='utf8')
 except RuntimeError as e:
  if 'mkp_no_file' not in str(e) and 'No existe el archivo indicado.' not in str(e):raise
 stage=OUT/name;stage.write_text(data,encoding='utf8')
 if name.endswith('.php'):subprocess.run(['C:/xampp/php/php.exe','-l',str(stage)],check=True)
 if name.endswith('.js'):subprocess.run(['node','--check',str(stage)],check=True)
 receipt=c.ability('minikids/write-file',{'path':'mu-plugins/'+name,'content':data,'backup':True})
 if name.endswith('.php'):subprocess.run(['C:/xampp/php/php.exe','-l',str(ROOT/'wordpress/wp-content/mu-plugins'/name)],check=True)
 assert c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']==data
 print('Applied/readback '+name,flush=True);return receipt
if __name__=='__main__':
 receipts=[]
 for name in ['minikids-commerce-v8.php','minikids-commerce-v8.css','minikids-commerce-v8.js']:receipts.append(apply(name,(OUT/name).read_text('utf8')))
 name='minikids-negocio.php';old=c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']
 if "do_settings_sections('mkpn-business')" not in old:
  receipts.append(apply(name,old.replace("submit_button('Guardar datos');", "do_settings_sections('mkpn-business');submit_button('Guardar datos');")))
 (OUT/'aplicacion-V8.json').write_text(json.dumps(receipts,indent=2,ensure_ascii=False),encoding='utf8')
