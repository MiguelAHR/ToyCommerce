"""Local owned files through MCP; backup, lint, readback. No deployment."""
from pathlib import Path
import sys,socket,json,subprocess,datetime,hashlib,re
ROOT=Path(__file__).resolve().parents[1];sys.path.insert(0,str(ROOT.parent))
resolve=socket.getaddrinfo;socket.getaddrinfo=lambda host,*a,**kw:resolve('127.0.0.1' if host=='localhost' else host,*a,**kw)
from diseno_mcp import client
OUT=ROOT.parent/'diseno_minikids/v5';BACK=ROOT.parent/'local-private/v6-backups'
BACK.mkdir(exist_ok=True);c=client();assert c.url.startswith('http://localhost:8082/wordpress/')
def protected():
    names=subprocess.check_output(['rg','--files','--hidden','-g','!.git','-g','!*.bak*','wordpress/wp-admin','wordpress/wp-includes','wordpress/wp-content/themes','wordpress/wp-content/plugins'],cwd=ROOT,text=True).splitlines()
    return {name:hashlib.sha256((ROOT/name).read_bytes()).hexdigest()for name in names}
if not (BACK/'protected.json').exists():(BACK/'protected.json').write_text(json.dumps(protected()),encoding='utf8')
def read(name):
    data=c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']
    if not (BACK/name).exists():(BACK/name).write_text(data,encoding='utf8')
    return data
def write(name,content):
    try:read(name)
    except RuntimeError as e:
        if 'No existe el archivo indicado.' not in str(e)and'mkp_no_file' not in str(e):raise
    staged=OUT/name;staged.write_text(content,encoding='utf8')
    if name.endswith('.php'):subprocess.run(['C:/xampp/php/php.exe','-l',str(staged)],check=True)
    if name.endswith('.js'):subprocess.run(['node','--check',str(staged)],check=True)
    result=c.ability('minikids/write-file',{'path':'mu-plugins/'+name,'content':content,'backup':True})
    if name.endswith('.php'):subprocess.run(['C:/xampp/php/php.exe','-l',str(ROOT/'wordpress/wp-content/mu-plugins'/name)],check=True)
    assert c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']==content
    print('Applied/read back '+name,flush=True);return result
results=[]
# 9,10 first: remove the loose histogram; share the same renderer for both carousels.
php=read('minikids-commerce-v5.php')
php=php.replace("add_action('woocommerce_after_single_product_summary',function(){\n    global $product;if(!$product)return;$counts=", "function mk5_review_overview(){\n    global $product;if(!$product)return;$counts=")
php=php.replace("<h2>Comentarios</h2><p><strong>","<h3>Resumen de valoraciones</h3><p><strong>")
php=php.replace("    echo '</section>';\n},9);","    echo '</section>';\n}")
php=php.replace("    echo '<section class=\"mk5-cart-related\"><h2>Artículos relacionados</h2>';\n",'')
php=php.replace("    if($ids)echo do_shortcode('[products ids=\"'.implode(',',array_map('absint',$ids)).'\" columns=\"5\" orderby=\"post__in\"]');\n    echo '</section>';return $content.ob_get_clean();","    if(function_exists('mk6_recommendations'))mk6_recommendations($ids,'Artículos relacionados','mk5-cart-related');\n    return $content.ob_get_clean();")
php=php.replace("'<span class=\"onsale\">− '","'<span class=\"onsale\">-'")
php=php.replace('☆☆☆☆☆ <span>','<span>')
php=php.replace('IR A PAGAR →','IR A PAGAR')
(ROOT/'tools/v5-commerce.php').write_text(php,encoding='utf8');subprocess.run(['C:/xampp/php/php.exe','-l',str(ROOT/'tools/v5-commerce.php')],check=True)
results.append(write('minikids-commerce-v5.php',php))
# 11: remove decorative Unicode at its owned source; leave mail/admin handlers intact.
old=read('minikids-estetica.php');new=old.replace('<span class="shipping-icon">🚚</span>','')
# Only storefront UI; preserve the existing mail subject/body and admin handlers.
lines=new.splitlines(keepends=True)
for i,line in enumerate(lines):
    if i<660:
        for glyph in ['🎉','❤️','💬']:line=line.replace(glyph,'')
        lines[i]=line
new=''.join(lines)
(ROOT/'tools/v6-estetica-source.php').write_text(new,encoding='utf8');subprocess.run(['C:/xampp/php/php.exe','-l',str(ROOT/'tools/v6-estetica-source.php')],check=True)
results.append(write('minikids-estetica.php',new))
drawer=read('minikids-cart-drawer.js').replace(' 🎉','').replace(' ✔','')
results.append(write('minikids-cart-drawer.js',drawer))
attention=read('minikids-atencion.css').replace("content:'↗'","content:none")
results.append(write('minikids-atencion.css',attention))
# 12-14 then 1-8 live in v6. Remove V5's previous display-only checkout navigation.
js=read('minikids-commerce-v5.js');start=js.find('  // Navigation aid only:');end=js.rfind('\n });')
if start>=0:js=js[:start]+js[end:]
js='\n'.join(line for line in js.split('\n')if not line.startswith("  all('.related.products,.mk5-cart-related').forEach"))
js=js.replace('IR A PAGAR →','IR A PAGAR')
(ROOT/'tools/v5-commerce.js').write_text(js,encoding='utf8');results.append(write('minikids-commerce-v5.js',js))
for ext in ['css','js','php']:results.append(write('minikids-commerce-v6.'+ext,(ROOT/f'tools/v6-commerce.{ext}').read_text('utf8')))
# Native Gutenberg headings, inline SVG injected only at render time.
footer=c.ability('minikids/get-content',{'id':219})
if not (BACK/'footer-219.json').exists():(BACK/'footer-219.json').write_text(json.dumps(footer,ensure_ascii=False),encoding='utf8')
content=footer['content']
for symbol,label,icon in [('↗','Envíos a tu dirección','truck'),('☏','Atención por WhatsApp','whatsapp'),('☷','Libro de Reclamaciones','book'),('✓','Opciones de pago','shield')]:
    content=content.replace(symbol+' ','')
    pattern=r'<!-- wp:heading (\{"level":\s*3\}) -->\s*<h3 class="wp-block-heading">(.*?'+re.escape(label)+r'.*?)</h3>'
    content=re.sub(pattern,lambda m:'<!-- wp:heading '+json.dumps({'level':3,'className':'mk6-benefit-'+icon})+' -->\n<h3 class="wp-block-heading mk6-benefit-'+icon+'">'+m[2]+'</h3>',content,count=1)
content=content.replace('Consulta condiciones de compra','Términos y condiciones').replace('Condiciones de compra: consúltanos','Términos y condiciones')
content=content.replace('href="http://localhost:8082/wordpress/contacto/">Términos y condiciones','href="http://localhost:8082/wordpress/terminos-y-condiciones/">Términos y condiciones')
assert c.ability('minikids/get-content',{'id':219})['content']==footer['content']
results.append(c.ability('minikids/update-content',{'id':219,'content':content}));assert c.ability('minikids/get-content',{'id':219})['content']==content
(OUT/'footer-219-v6.html').write_text(content,encoding='utf8')
(OUT/'aplicacion-V6.json').write_text(json.dumps({'results':results,'backup':'local-private/v6-backups','time':datetime.datetime.now().isoformat()},ensure_ascii=False,indent=2),encoding='utf8')
before=json.loads((BACK/'protected.json').read_text('utf8'));after=protected();diff=[name for name in before if before[name]!=after.get(name)]
(OUT/'integridad-V6.json').write_text(json.dumps({'protected_checked':len(before),'differences':diff},indent=2),encoding='utf8');assert not diff
print('Protected files unchanged; footer read back.',flush=True)
