from pathlib import Path
import sys,datetime,subprocess,json
root=Path(__file__).resolve().parents[1];sys.path.insert(0,str(root.parent))
from diseno_mcp import client
c=client();assert c.url.startswith('http://localhost:8082/wordpress/')
path='mu-plugins/minikids-estetica.php';before=c.ability('minikids/read-file',{'path':path})['content']
backup=root.parent/'local-private/v5-backups'/('estetica-before-logo-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S')+'.php');backup.write_text(before,encoding='utf8')
old='<img src="/wordpress/wp-content/uploads/minikids/logo-minikids.png" alt="Mini Kids" class="footer-logo-img"/>'
new='<img src="<?php echo esc_url(function_exists(\'mka_webp_url\') ? mka_webp_url(content_url(\'/uploads/minikids/logo-minikids.png\')) : content_url(\'/uploads/minikids/logo-minikids.png\')); ?>" alt="Mini Kids" class="footer-logo-img" width="1024" height="341" loading="lazy" decoding="async"/>'
assert before.count(old)==1
after=before.replace(old,new);staged=root/'tools/v5-staged-estetica.php';staged.write_text(after,encoding='utf8')
subprocess.run([r'C:\xampp\php\php.exe','-l',str(staged)],check=True)
assert c.ability('minikids/read-file',{'path':path})['content']==before
r=c.ability('minikids/write-file',{'path':path,'content':after,'backup':True})
assert c.ability('minikids/read-file',{'path':path})['content']==after
subprocess.run([r'C:\xampp\php\php.exe','-l',str(root/'wordpress/wp-content'/path)],check=True)
(root.parent/'diseno_minikids/v5/aplicacion-logo.json').write_text(json.dumps(r,indent=2),encoding='utf8')
