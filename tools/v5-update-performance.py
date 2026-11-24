import sys,json,datetime,subprocess
from pathlib import Path
root=Path(__file__).resolve().parents[1];sys.path.insert(0,str(root.parent))
from diseno_mcp import client
c=client();assert c.url.startswith('http://localhost:8082/wordpress/')
path='mu-plugins/minikids-performance.php'
before=c.ability('minikids/read-file',{'path':path})['content']
backup=root.parent/'local-private/v5-backups'/('performance-before-srcset-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S')+'.php');backup.write_text(before,encoding='utf8')
after=(root/'tools/v5-performance.php').read_text('utf8')
subprocess.run([r'C:\xampp\php\php.exe','-l',str(root/'tools/v5-performance.php')],check=True)
assert c.ability('minikids/read-file',{'path':path})['content']==before
r=c.ability('minikids/write-file',{'path':path,'content':after,'backup':True})
assert c.ability('minikids/read-file',{'path':path})['content']==after
subprocess.run([r'C:\xampp\php\php.exe','-l',str(root/'wordpress/wp-content'/path)],check=True)
(root.parent/'diseno_minikids/v5/aplicacion-srcset.json').write_text(json.dumps(r,indent=2),encoding='utf8')
