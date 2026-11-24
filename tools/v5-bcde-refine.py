from pathlib import Path
import sys,socket,json,subprocess,datetime
ROOT=Path(__file__).resolve().parents[1];sys.path.insert(0,str(ROOT.parent))
resolver=socket.getaddrinfo;socket.getaddrinfo=lambda host,*a,**kw:resolver('127.0.0.1'if host=='localhost'else host,*a,**kw)
from diseno_mcp import client
c=client();back=ROOT.parent/'local-private/v5-backups'/('BCDE-refine-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S'));back.mkdir();out=ROOT.parent/'diseno_minikids/v5';results=[]
for ext in ['css','js','php']:
    name='minikids-commerce-v5.'+ext;path='mu-plugins/'+name;old=c.ability('minikids/read-file',{'path':path})['content'];(back/name).write_text(old,encoding='utf8')
    content=(ROOT/'tools'/('v5-commerce.'+ext)).read_text('utf8');(out/name).write_text(content,encoding='utf8')
    if ext=='php':subprocess.run([r'C:\xampp\php\php.exe','-l',str(out/name)],check=True)
    if ext=='js':subprocess.run(['node','--check',str(out/name)],check=True)
    assert c.ability('minikids/read-file',{'path':path})['content']==old
    results.append(c.ability('minikids/write-file',{'path':path,'content':content,'backup':True}))
    if ext=='php':subprocess.run([r'C:\xampp\php\php.exe','-l',str(ROOT/'wordpress/wp-content'/path)],check=True)
    assert c.ability('minikids/read-file',{'path':path})['content']==content;print('Refined '+name,flush=True)
for id,title in [(34,'Mi carrito'),(35,'Finalizar compra')]:
    old=c.ability('minikids/get-content',{'id':id});(back/f'page-{id}.json').write_text(json.dumps(old,ensure_ascii=False),encoding='utf8');results.append(c.ability('minikids/update-content',{'id':id,'title':title}));after=c.ability('minikids/get-content',{'id':id});assert after['content']==old['content'] and after['title']==title
(out/'ajuste-BCDE.json').write_text(json.dumps({'results':results,'backup':back.name},ensure_ascii=False,indent=2),encoding='utf8')
