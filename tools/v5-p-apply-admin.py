from pathlib import Path
import sys,json,datetime,subprocess
ROOT=Path(__file__).resolve().parents[1];sys.path.insert(0,str(ROOT.parent))
from diseno_mcp import client
c=client();assert c.url.startswith('http://localhost:8082/wordpress/')
name='mu-plugins/minikids-demo-catalogo.php';target=ROOT/'wordpress/wp-content'/name
before=None
if target.exists():
    before=c.ability('minikids/read-file',{'path':name})['content']
    (ROOT.parent/'local-private/v5-backups'/('demo-admin-before-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S')+'.php')).write_text(before,encoding='utf8')
source=ROOT/'tools/v5-p-admin.php';subprocess.run(['C:/xampp/php/php.exe','-l',str(source)],check=True)
content=source.read_text('utf8');r=c.ability('minikids/write-file',{'path':name,'content':content,'backup':True})
assert c.ability('minikids/read-file',{'path':name})['content']==content
subprocess.run(['C:/xampp/php/php.exe','-l',str(target)],check=True)
(ROOT.parent/'diseno_minikids/v5/aplicacion-admin-P.json').write_text(json.dumps({'write':r,'existed_before':before is not None,'reread_matches':True},indent=2),encoding='utf8')
