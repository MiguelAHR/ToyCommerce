"""Apply only owned code through authenticated local MCP, with backups."""
from pathlib import Path
import sys,json,re,shutil,subprocess,datetime
ROOT=Path(__file__).resolve().parents[1]
sys.path.insert(0,str(ROOT.parent))
from diseno_mcp import client
c=client(); private=ROOT.parent/'local-private/v5-backups'
stamp=datetime.datetime.now().strftime('%Y%m%d-%H%M%S')
results=[]
def write(path,content):
    target=ROOT/'wordpress/wp-content'/path
    if target.exists():
        before=c.ability('minikids/read-file',{'path':path})
        (private/(target.name+'.'+stamp)).write_text(before['content'],encoding='utf8')
    result=c.ability('minikids/write-file',{'path':path,'content':content,'backup':True})
    after=c.ability('minikids/read-file',{'path':path})
    assert after['content']==content
    if path.endswith('.php'):subprocess.run(['C:/xampp/php/php.exe','-l',str(target)],check=True)
    results.append(result)
fonts=ROOT/'wordpress/wp-content/uploads/minikids/fonts'
fonts.mkdir(exist_ok=True,parents=True)
for source in (ROOT/'tools/v5-assets/fonts').glob('*.woff2'):
    dest=fonts/source.name
    if dest.exists():shutil.copy2(dest,private/(dest.name+'.'+stamp))
    shutil.copy2(source,dest)
css=c.ability('minikids/read-file',{'path':'mu-plugins/minikids-retail.css'})['content']
css=re.sub(r'@font-face\{font-family:MKRoboto;[^}]+}',"@font-face{font-family:MKRoboto;src:url('../uploads/minikids/fonts/montserrat-variable-latin.woff2') format('woff2');font-weight:100 900;font-display:swap}",css)
css=re.sub(r'@font-face\{font-family:MKRajdhani;[^}]+}',"@font-face{font-family:MKRajdhani;src:url('../uploads/minikids/fonts/barlowcondensed-bold-latin.woff2') format('woff2');font-weight:700;font-display:swap}",css)
css=re.sub(r'@font-face\{font-family:MKBarlow;[^}]+}',"@font-face{font-family:MKBarlow;src:url('../uploads/minikids/fonts/barlowcondensed-bold-latin.woff2') format('woff2');font-weight:700;font-display:swap}",css)
write('mu-plugins/minikids-retail.css',css)
write('mu-plugins/minikids-performance.php',(ROOT/'tools/v5-performance.php').read_text('utf8'))
php=c.ability('minikids/read-file',{'path':'mu-plugins/minikids-retail.php'})['content']
php=php.replace("$logo_url=$logo?wp_get_attachment_image_url($logo,'full'):content_url('/uploads/minikids/logo-minikids.png');","$logo_url=$logo?wp_get_attachment_image_url($logo,'full'):content_url('/uploads/minikids/logo-minikids.png');\n    if(function_exists('mkv5_webp'))$logo_url=mkv5_webp($logo_url);")
write('mu-plugins/minikids-retail.php',php)
(ROOT.parent/'diseno_minikids/v5/aplicacion-A.json').write_text(json.dumps(results,indent=2),encoding='utf8')
