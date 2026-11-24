"""Apply reviewed V7 supplements via MCP only."""
from pathlib import Path
import sys,socket,json,subprocess
ROOT=Path(__file__).resolve().parents[1];sys.path.insert(0,str(ROOT.parent));OUT=ROOT.parent/'diseno_minikids/v5';BACK=ROOT.parent/'local-private/v7-backups'
resolve=socket.getaddrinfo;socket.getaddrinfo=lambda host,*a,**kw:resolve('127.0.0.1' if host=='localhost' else host,*a,**kw)
from diseno_mcp import client
c=client();assert c.url.startswith('http://localhost:8082/wordpress/')
def read(name):
    d=c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']
    if not(BACK/name).exists():(BACK/name).write_text(d,encoding='utf8')
    return d
def write(name,data):
    try:read(name)
    except RuntimeError as e:
        if 'mkp_no_file' not in str(e)and 'No existe el archivo indicado.' not in str(e):raise
    p=OUT/name;p.write_text(data,encoding='utf8')
    if name.endswith('.php'):subprocess.run(['C:/xampp/php/php.exe','-l',str(p)],check=True)
    if name.endswith('.js'):subprocess.run(['node','--check',str(p)],check=True)
    r=c.ability('minikids/write-file',{'path':'mu-plugins/'+name,'content':data,'backup':True})
    if name.endswith('.php'):subprocess.run(['C:/xampp/php/php.exe','-l',str(ROOT/'wordpress/wp-content/mu-plugins'/name)],check=True)
    assert c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']==data
    print('Applied/read back '+name,flush=True);return r
css=read('minikids-loader-v7.css')
css=css.replace('animation-duration:.25s;animation-delay:2.25s;animation-fill-mode:forwards','animation-duration:.25s!important;animation-delay:2.25s!important;animation-fill-mode:forwards!important;animation-iteration-count:1!important')
css=css.replace('animation-name:mk7-loader-out;','animation-name:mk7-loader-out!important;')
css=css.replace('transition:opacity .25s,visibility 0s .25s;','transition:opacity .25s,visibility 0s .25s!important;')
css=css.replace('animation-name:mk7-dot;animation-duration:.8s;animation-iteration-count:infinite;animation-direction:alternate','animation-name:mk7-dot!important;animation-duration:.8s!important;animation-iteration-count:infinite!important;animation-direction:alternate!important;animation-delay:0s!important')
css=css.replace('animation-delay:.2s','animation-delay:.2s!important').replace('animation-delay:.4s','animation-delay:.4s!important')
css=css.replace('animation-name:none','animation-name:none!important').replace('transition:none','transition:none!important')
css=css.replace('animation-name:mk7-pulse;','animation-name:mk7-pulse!important;')
write('minikids-loader-v7.css',css)
css=read('minikids-commerce-v6.css')
css+='\nbody.minikids-retail .mk6-recommendations>.woocommerce{width:100%!important;max-width:none!important;padding:0!important;margin:0!important}\n'
write('minikids-commerce-v6.css',css)
mapping=json.loads((OUT/'minikids-language-v7.json').read_text('utf8'))
mapping.update({'Your in-store purchase #%1$s at %2$s':'Tu compra en tienda #%1$s en %2$s','Your %1$s order #%2$s has been refunded':'Tu pedido #%2$s de %1$s se ha reembolsado','Thank you for your in-store purchase':'Gracias por tu compra en tienda'})
write('minikids-language-v7.json',json.dumps(mapping,ensure_ascii=False,indent=2))
write('minikids-language-v7.php','''<?php
/** MiniKids V7: residual native Woo strings. Human editable JSON, no mail handlers. */
defined('ABSPATH') || exit;
function mk7_translate_residual($translation,$original,$domain){
    if(!in_array($domain,['woocommerce','blocksy'],true)||!str_starts_with(get_locale(),'es_'))return $translation;
    // Prefer the installed official catalog and existing explicit business translations.
    if($translation!==$original)return $translation;
    static $map=null;
    if($map===null)$map=json_decode(file_get_contents(__DIR__.'/minikids-language-v7.json'),true)?:[];
    return $map[$original]??$translation;
}
add_filter('gettext','mk7_translate_residual',180,3);
add_filter('gettext_with_context',function($translated,$original,$context,$domain){return mk7_translate_residual($translated,$original,$domain);},180,4);
''')
