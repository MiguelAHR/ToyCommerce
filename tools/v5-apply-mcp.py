"""Read before write + private backups + MCP backup=true for every application file."""
import sys,re,json,subprocess,datetime
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1];sys.path.insert(0,str(ROOT.parent))
from diseno_mcp import client
OUT=ROOT.parent/'diseno_minikids/v5';PRIVATE=ROOT.parent/'local-private/v5-backups'
c=client();assert c.url.startswith('http://localhost:8082/wordpress/')
stamp=datetime.datetime.now().strftime('%Y%m%d-%H%M%S');backup=PRIVATE/('mcp-'+stamp);backup.mkdir()
files={}
for ext in ('php','css','js'):
    name='minikids-retail.'+ext
    files[name]=c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']
    (backup/name).write_text(files[name],encoding='utf8')
for id in (8,219):
    (backup/f'page-{id}.json').write_text(json.dumps(c.ability('minikids/get-content',{'id':id}),ensure_ascii=False),encoding='utf8')
css=files['minikids-retail.css']
css=re.sub(r'@font-face\{font-family:MKRoboto;[^}]+\}',"@font-face{font-family:MKMontserrat;src:url('minikids-v5-assets/montserrat-variable-latin.woff2') format('woff2');font-weight:100 900;font-display:swap}",css)
css=re.sub(r'@font-face\{font-family:MKRajdhani;[^}]+\}', '',css)
css=re.sub(r'@font-face\{font-family:MKBarlow;[^}]+\}',"@font-face{font-family:MKBarlow;src:url('minikids-v5-assets/barlowcondensed-bold-latin.woff2') format('woff2');font-weight:700;font-display:swap}",css)
css=css.replace('MKRoboto','MKMontserrat').replace('MKRajdhani','MKBarlow')
assert 'base64,' not in css and '.ttf' not in css
files['minikids-retail.css']=css
js=files['minikids-retail.js'];base,marker,campaign=js.partition('/* BEGIN MINIKIDS V4 */');assert marker
files['minikids-retail.js']=base
files['minikids-campanas-v4.js']=marker+campaign
php=files['minikids-retail.php']
php=php.replace('Version: 4.0.0','Version: 5.0.0-a')
php=php.replace("    ?>\n    <div class=\"mkr-service\">","    if(function_exists('mka_webp_url'))$logo_url=mka_webp_url($logo_url);\n    ?>\n    <div class=\"mkr-service\">")
php=php.replace('height="74" fetchpriority="high"','height="74" decoding="async" fetchpriority="high"')
assert 'mka_webp_url($logo_url)' in php
files['minikids-retail.php']=php
files['minikids-performance.php']=(ROOT/'tools/v5-performance.php').read_text('utf8')
results=[]
for name,content in files.items():
    # Re-read immediately before replacement and compare to the captured original.
    if (backup/name).exists():assert c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']==(backup/name).read_text('utf8')
    staged=ROOT/'tools'/('v5-staged-'+name)
    if staged.exists():raise RuntimeError('Staging already exists')
    staged.write_text(content,encoding='utf8')
    if name.endswith('.php'):subprocess.run([r'C:\xampp\php\php.exe','-l',str(staged)],check=True)
    if name.endswith('.js'):subprocess.run(['node','--check',str(staged)],check=True)
    r=c.ability('minikids/write-file',{'path':'mu-plugins/'+name,'content':content,'backup':True})
    assert c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']==content
    if name.endswith('.php'):subprocess.run([r'C:\xampp\php\php.exe','-l',str(ROOT/'wordpress/wp-content/mu-plugins'/name)],check=True)
    results.append(r);print('MCP written and re-read:',name,flush=True)
for id in (8,219):assert c.ability('minikids/get-content',{'id':id})['content']==json.loads((backup/f'page-{id}.json').read_text('utf8'))['content']
(OUT/'aplicacion-mcp.json').write_text(json.dumps({'writes':results,'content_8_219_unchanged':True,'backup_directory':backup.name},indent=2,ensure_ascii=False),encoding='utf8')
