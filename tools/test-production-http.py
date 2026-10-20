"""Dos pasadas de lectura HTTP y Store API, sin crear ventas ni enviar correo."""
import urllib.request,urllib.error,json,re
from pathlib import Path
base='https://minikids.vitorstream.shop';checks=[]
def get(path):
    try:
        request=urllib.request.Request(base+path,headers={'User-Agent':'MiniKids-Deployment-QA/1.0'})
        with urllib.request.urlopen(request,timeout=40) as r:return r.status,r.read(),dict(r.headers)
    except urllib.error.HTTPError as e:return e.code,e.read(),dict(e.headers)
def check(name,ok):checks.append({'name':name,'passed':bool(ok)});print(('OK ' if ok else 'FAIL ')+name,flush=True)
for run in [1,2]:
    for path in ['/','/tienda/','/nosotros/','/libro-de-reclamaciones/','/cart/','/checkout/','/my-account/','/wp-login.php']:
        status,body,h=get(path);check(f'P{run} {path}',status==200 and b'Fatal error' not in body)
        check(f'P{run} URLs {path}',not re.search(rb'(?:href|src)=["\'][^"\']*(?:localhost|/wordpress/)',body))
    status,body,h=get('/wp-json/wc/store/v1/products?per_page=100');products=json.loads(body);check(f'P{run} productos',status==200 and len(products)==17)
    check(f'P{run} imágenes',all(p['images'] and p['images'][0]['src'].startswith(base) for p in products))
    status,body,h=get('/wp-json/wc/store/v1/cart');check(f'P{run} Store API carrito',status==200 and isinstance(json.loads(body).get('items'),list))
    for path in ['/wp-config.php','/local-private/config.json','/database/wordpress-sanitized.sql','/wp-content/mu-plugins/minikids-estetica.php.bak-20261020','/wp-content/qa-evidencia/test.png']:
        status,body,h=get(path);check(f'P{run} privado {path}',status in [403,404])
Path('brain/production-http-checks.json').write_text(json.dumps(checks,indent=2))
if any(not c['passed'] for c in checks):raise SystemExit(1)
