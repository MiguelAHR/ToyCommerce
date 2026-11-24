"""Apply owned B–E files and editable footer through the local MCP only."""
from pathlib import Path
import sys,json,socket,subprocess,datetime,hashlib
ROOT=Path(__file__).resolve().parents[1];sys.path.insert(0,str(ROOT.parent))
# Local PHP listener is IPv4. Avoid repeated unavailable IPv6 connection attempts.
original_resolve=socket.getaddrinfo
socket.getaddrinfo=lambda host,*a,**kw:original_resolve('127.0.0.1' if host=='localhost' else host,*a,**kw)
from diseno_mcp import client
out=ROOT.parent/'diseno_minikids/v5';back=ROOT.parent/'local-private/v5-backups'/(sys.argv[1]if len(sys.argv)>1 else 'BCDE-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S'));back.mkdir(exist_ok=True)
def protected():
    result={}
    names=subprocess.check_output(['rg','--files','--hidden','-g','!.git','-g','!*.bak*','wordpress/wp-admin','wordpress/wp-includes','wordpress/wp-content/themes','wordpress/wp-content/plugins'],cwd=ROOT,text=True).splitlines()
    print('Checking protected files: '+str(len(names)),flush=True)
    for i,name in enumerate(names):
        p=ROOT/name;result[p.relative_to(ROOT).as_posix()]=hashlib.sha256(p.read_bytes()).hexdigest()
        if i%2000==0:print('Hashed '+str(i),flush=True)
    return result
if not (back/'protected.json').exists():(back/'protected.json').write_text(json.dumps(protected()),encoding='utf8')
c=client();assert c.url.startswith('http://localhost:8082/wordpress/')
footer=c.ability('minikids/get-content',{'id':219});(back/'footer.json').write_text(json.dumps(footer,ensure_ascii=False),encoding='utf8')
old=c.ability('minikids/read-file',{'path':'mu-plugins/minikids-retail.js'})['content'];(back/'minikids-retail.js').write_text(old,encoding='utf8')
js=old
start=js.index('  let pending=0;new MutationObserver(');end=js.index('\n  for(const item of items)',start)
js=js[:start]+'  // V5 catalog observer lives in minikids-commerce-v5.js.\n'+js[end:]
js=js.replace('Math.min(99,Math.floor(Number(n)||1))','Math.min(Number(input.max)||99,Math.floor(Number(n)||1))')
files={'minikids-commerce-v5.css':(ROOT/'tools/v5-commerce.css').read_text('utf8'),'minikids-commerce-v5.js':(ROOT/'tools/v5-commerce.js').read_text('utf8'),'minikids-commerce-v5.php':(ROOT/'tools/v5-commerce.php').read_text('utf8'),'minikids-retail.js':js}
results=[]
for name,content in files.items():
    try:prior=c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']
    except RuntimeError as e:
        if 'mkp_no_file' not in str(e) and 'No existe el archivo indicado.' not in str(e):raise
        prior=None
    if prior is not None:(back/name).write_text(prior,encoding='utf8')
    staged=out/name;staged.write_text(content,encoding='utf8')
    if name.endswith('.php'):subprocess.run([r'C:\xampp\php\php.exe','-l',str(staged)],check=True)
    if name.endswith('.js'):subprocess.run(['node','--check',str(staged)],check=True)
    result=c.ability('minikids/write-file',{'path':'mu-plugins/'+name,'content':content,'backup':True})
    if name.endswith('.php'):subprocess.run([r'C:\xampp\php\php.exe','-l',str(ROOT/'wordpress/wp-content/mu-plugins'/name)],check=True)
    assert c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']==content
    results.append(result);print('MCP applied '+name,flush=True)
from html import escape
base='http://localhost:8082/wordpress/'
def para(text,cls=''):
    attrs={'className':cls} if cls else {};attr=' '+json.dumps(attrs,ensure_ascii=False) if attrs else ''
    return '<!-- wp:paragraph'+attr+' -->\n<p'+(' class="'+cls+'"' if cls else '')+'>'+text+'</p>\n<!-- /wp:paragraph -->'
def heading(text):return '<!-- wp:heading {"level":3} -->\n<h3 class="wp-block-heading">'+text+'</h3>\n<!-- /wp:heading -->'
def group(content,cls):return '<!-- wp:group '+json.dumps({'className':cls},ensure_ascii=False)+' -->\n<div class="wp-block-group '+cls+'">'+content+'</div>\n<!-- /wp:group -->'
def columns(items,cls=''):
    return '<!-- wp:columns'+(' '+json.dumps({'className':cls}) if cls else '')+' -->\n<div class="wp-block-columns'+(' '+cls if cls else '')+'">'+''.join('<!-- wp:column -->\n<div class="wp-block-column">'+i+'</div>\n<!-- /wp:column -->' for i in items)+'</div>\n<!-- /wp:columns -->'
def link(label,path):return '<a href="'+escape(base+path,quote=True)+'">'+escape(label)+'</a>'
native=json.loads((out/'BCDE-native.json').read_text('utf8'));assert set(native['gateways'])=={'bacs','cod'}
benefits=group(columns([heading(link('↗ Envíos a tu dirección','contacto/'))+para('Consulta cobertura y plazos'),heading('<a href="https://wa.me/51991117308">☏ Atención por WhatsApp</a>')+para('Te ayudamos a elegir'),heading(link('☷ Libro de Reclamaciones','libro-de-reclamaciones/'))+para('Un canal para escucharte'),heading(link('✓ Opciones de pago','contacto/'))+para('Transferencia y contra entrega')]),'mk5-benefits')
cols=[heading('Secciones')+''.join(para(link(t['name'],'tienda/?cat='+t['slug']))for t in native['categories']),heading('Soporte')+para(link('Contacto','contacto/'))+para('<a href="https://wa.me/51991117308">Atención por WhatsApp</a>')+para(link('Libro de Reclamaciones','libro-de-reclamaciones/')),heading('Servicios')+para(link('Mi cuenta','my-account/'))+para(link('Mis pedidos','my-account/orders/'))+para(link('Mi carrito','cart/'))+para(link('Consulta de envíos','contacto/')),heading('Nosotros')+para(link('Conoce MiniKids','nosotros/'))+para(link('Política de privacidad','politica-privacidad/'))+para(link('Consulta condiciones de compra','contacto/')),heading('Métodos de pago')+para('Transferencia bancaria')+para('Pago contra entrega')+para('Consulta disponibilidad para tu dirección.')]
legal=group(para('MiniKids Perú. Todos los derechos reservados © 2026')+para(link('Condiciones de compra: consúltanos','contacto/')+' · '+link('Privacidad','politica-privacidad/')+' · '+link('Libro de Reclamaciones','libro-de-reclamaciones/')),'mk5-footer-legal')
content=benefits+'\n'+columns(cols,'mk5-footer-columns')+'\n'+legal
(out/'footer-219-bcde.html').write_text(content,encoding='utf8')
assert c.ability('minikids/get-content',{'id':219})['content']==footer['content']
r=c.ability('minikids/update-content',{'id':219,'content':content});assert c.ability('minikids/get-content',{'id':219})['content']==content
(out/'aplicacion-BCDE.json').write_text(json.dumps({'files':results,'footer':r,'backup':back.name},ensure_ascii=False,indent=2),encoding='utf8');print('Footer 219 updated and read back',flush=True)
after=protected();before=json.loads((back/'protected.json').read_text('utf8'));assert before==after
(out/'integridad-BCDE.json').write_text(json.dumps({'protected_checked':len(before),'differences':[]},indent=2),encoding='utf8')
