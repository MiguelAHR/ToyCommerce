"""An unexpired timer must preserve earlier Woo/V6 validation handler results."""
from pathlib import Path
import sys,socket,subprocess
ROOT=Path(__file__).resolve().parents[1];sys.path.insert(0,str(ROOT.parent))
resolve=socket.getaddrinfo;socket.getaddrinfo=lambda host,*a,**kw:resolve('127.0.0.1' if host=='localhost' else host,*a,**kw)
from diseno_mcp import client
c=client();assert c.url.startswith('http://localhost:8082/wordpress/')
name='minikids-commerce-v6.js';old=c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']
data=old.replace("$(form).on('checkout_place_order',()=>{tick();return !expired;});","$(form).on('checkout_place_order',()=>{tick();if(expired)return false;});");assert data!=old
stage=ROOT.parent/'diseno_minikids/v5'/name;stage.write_text(data,encoding='utf8');subprocess.run(['node','--check',str(stage)],check=True)
c.ability('minikids/write-file',{'path':'mu-plugins/'+name,'content':data,'backup':True})
assert c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']==data
print('Applied/read back deadline guard without overriding earlier validation results.')
