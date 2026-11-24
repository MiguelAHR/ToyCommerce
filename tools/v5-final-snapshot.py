from pathlib import Path
import sys,json,hashlib,subprocess
root=Path(__file__).resolve().parents[1];sys.path.insert(0,str(root.parent))
from diseno_mcp import client
c=client();assert c.url.startswith('http://localhost:8082/wordpress/')
backup=sorted((root.parent/'local-private/v5-backups').glob('mcp-*'))[0]
result={'unchanged_content':{},'owned_files':[]}
for id in (8,219):
    prior=json.loads((backup/f'page-{id}.json').read_text('utf8'))
    current=c.ability('minikids/get-content',{'id':id})
    result['unchanged_content'][str(id)]=current['content']==prior['content']
    assert result['unchanged_content'][str(id)]
for name in ('minikids-retail.php','minikids-retail.css','minikids-retail.js','minikids-performance.php','minikids-estetica.php','minikids-campanas-v4.js'):
    read=c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']
    path=root/'wordpress/wp-content/mu-plugins'/name
    assert path.read_bytes().decode('utf8')==read,name
    if name.endswith('.php'):subprocess.run([r'C:\xampp\php\php.exe','-l',str(path)],check=True)
    if name.endswith('.js'):subprocess.run(['node','--check',str(path)],check=True)
    result['owned_files'].append({'name':name,'sha256':hashlib.sha256(path.read_bytes()).hexdigest()})
result['router_lint']=subprocess.check_output([r'C:\xampp\php\php.exe','-l',str(root/'tools/router-local.php')]).decode().strip()
(root.parent/'diseno_minikids/v5/estado-final.json').write_text(json.dumps(result,indent=2),encoding='utf8')
print('Content 8/219 unchanged, owned code read back and linted')
