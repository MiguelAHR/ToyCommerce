"""V9 application through authenticated local MCP; backup=true and exact readback."""
from pathlib import Path
import sys,socket,subprocess,json,re
ROOT=Path(__file__).resolve().parents[1];sys.path.insert(0,str(ROOT.parent))
resolve=socket.getaddrinfo;socket.getaddrinfo=lambda host,*a,**kw:resolve('127.0.0.1' if host=='localhost' else host,*a,**kw)
from diseno_mcp import client
OUT=ROOT.parent/'diseno_minikids/v5';BACK=ROOT.parent/'local-private/v9-backups';BACK.mkdir(exist_ok=True)
c=client();assert c.url.startswith('http://localhost:8082/wordpress/')
def read(name):
    data=c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']
    if not (BACK/name).exists():(BACK/name).write_text(data,encoding='utf8')
    return data
def apply(name,data):
    try:read(name)
    except RuntimeError as e:
        if 'mkp_no_file' not in str(e) and 'No existe el archivo indicado.' not in str(e):raise
    stage=OUT/name;stage.write_text(data,encoding='utf8')
    if name.endswith('.php'):subprocess.run(['C:/xampp/php/php.exe','-l',str(stage)],check=True)
    if name.endswith('.js'):subprocess.run(['node','--check',str(stage)],check=True)
    result=c.ability('minikids/write-file',{'path':'mu-plugins/'+name,'content':data,'backup':True})
    if name.endswith('.php'):subprocess.run(['C:/xampp/php/php.exe','-l',str(ROOT/'wordpress/wp-content/mu-plugins'/name)],check=True)
    assert c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']==data
    print('Applied/readback '+name,flush=True);return result
if __name__=='__main__':
    receipts=[]
    # Source render removal, rather than leaving an invisible large banner in the DOM.
    s=read('minikids-estetica.php');start=s.index('// 9. Barra de Envío Gratis');end=s.index('// 9.1 ',start)
    s=s[:start]+'// V9: compact free-shipping notice lives in cart totals and native mini-cart fragment.\n\n'+s[end:]
    s=re.sub(r'\s*<div class="minikids-cart-drawer-shipping">.*?</div>','',s,flags=re.S)
    s=re.sub(r'\s*<footer class="minikids-cart-drawer-footer">.*?</footer>','',s,flags=re.S)
    s=s.replace('Tu carrito <span','Mi bolsa <span');receipts.append(apply('minikids-estetica.php',s))
    s=read('minikids-commerce-v6.js')
    # V6 still owns native cart/quantities; V9 exclusively owns secure checkout.
    s=s.replace('  const form=one(\'form.checkout\'),details=',"  if(window.mk9?.checkout)return;\n  const form=one('form.checkout'),details=")
    receipts.append(apply('minikids-commerce-v6.js',s))
    s=read('minikids-commerce-v6.php')
    s=s.replace('<p>Tu ventana de compra: <span','<p>Ventana segura <span')
    receipts.append(apply('minikids-commerce-v6.php',s))
    for name in ['minikids-commerce-v9.css','minikids-commerce-v9.js','minikids-commerce-v9.php']:
        receipts.append(apply(name,(OUT/name).read_text('utf8')))
    (OUT/'aplicacion-V9.json').write_text(json.dumps(receipts,ensure_ascii=False,indent=2),encoding='utf8')
