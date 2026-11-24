from pathlib import Path
import sys,socket,json,re,subprocess
ROOT=Path(__file__).resolve().parents[1];sys.path.insert(0,str(ROOT.parent));OUT=ROOT.parent/'diseno_minikids/v5';BACK=ROOT.parent/'local-private/v7-backups'
resolve=socket.getaddrinfo;socket.getaddrinfo=lambda host,*a,**kw:resolve('127.0.0.1' if host=='localhost' else host,*a,**kw)
from diseno_mcp import client
c=client();assert c.url.startswith('http://localhost:8082/wordpress/')
for name in ['minikids-language-v7.json','minikids-language-v7.php']:
 data=c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']
 if not(BACK/name).exists():(BACK/name).write_text(data,encoding='utf8')
 if name.endswith('.json'):
  mapping=json.loads(data);mapping.update({
   "'%s' is not a valid country code.":'«%s» no es un código de país válido.',
   '%1$s is not valid. Please enter one of the following: %2$s':'%1$s no es válido. Ingresa una de estas opciones: %2$s',
   'Please enter an address to continue.':'Ingresa una dirección para continuar.',
   'Unfortunately, <strong>we do not ship %1$s %2$s</strong>. Please enter an alternative shipping address.':'<strong>No realizamos envíos %1$s %2$s</strong>. Ingresa otra dirección de entrega.',
   'Unable to create order.':'No se pudo crear el pedido.',
   'Unable to create order. Please try again.':'No se pudo crear el pedido. Vuelve a intentarlo.',
   '%s download remaining':'%s descarga restante','%s downloads remaining':'%s descargas restantes',
   '%1$s for %2$s item':'%1$s por %2$s artículo','%1$s for %2$s items':'%1$s por %2$s artículos',
   'click here':'haz clic aquí',
   'Billing %s':'Facturación: %s','Shipping %s':'Envío: %s',
  })
  for a,b in mapping.items():
   assert sorted(re.findall(r'%(?:\d+\$)?[sd]',a))==sorted(re.findall(r'%(?:\d+\$)?[sd]',b)),(a,b)
   assert sorted(re.findall(r'\{\w+\}',a))==sorted(re.findall(r'\{\w+\}',b)),(a,b)
  data=json.dumps(mapping,ensure_ascii=False,indent=2)
 else:
  data+='''
add_filter('ngettext',function($translated,$single,$plural,$number,$domain){
    return mk7_translate_residual($translated,$number==1?$single:$plural,$domain);
},180,5);
add_filter('ngettext_with_context',function($translated,$single,$plural,$number,$context,$domain){
    return mk7_translate_residual($translated,$number==1?$single:$plural,$domain);
},180,6);
'''
 (OUT/name).write_text(data,encoding='utf8')
 if name.endswith('.php'):subprocess.run(['C:/xampp/php/php.exe','-l',str(OUT/name)],check=True)
 c.ability('minikids/write-file',{'path':'mu-plugins/'+name,'content':data,'backup':True})
 if name.endswith('.php'):subprocess.run(['C:/xampp/php/php.exe','-l',str(ROOT/'wordpress/wp-content/mu-plugins'/name)],check=True)
 assert c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']==data
 print('Applied/read back '+name,flush=True)
