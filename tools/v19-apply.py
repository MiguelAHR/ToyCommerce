"""V19 application through authenticated local MCP; backup=true, syntax checks and exact readback."""
from pathlib import Path
from datetime import datetime
import sys, socket, subprocess, json, hashlib, re, shutil

resolve = socket.getaddrinfo
socket.getaddrinfo = lambda host, *a, **kw: resolve('127.0.0.1' if host == 'localhost' else host, *a, **kw)

ROOT = Path(__file__).resolve().parents[1]
WORK = ROOT.parent
sys.path.insert(0, str(WORK))
from diseno_mcp import client

STAGE = WORK / 'diseno_minikids/v5'
BACK = WORK / 'local-private/v5-backups/v19'
BACK.mkdir(parents=True, exist_ok=True)
MU = ROOT / 'wordpress/wp-content/mu-plugins'

c = client()
assert c.url.startswith('http://localhost:8082/wordpress/'), f'Unexpected URL {c.url}'

if not (BACK / 'mu-before.json').exists():
    existing_mu = {p.name: hashlib.sha256(p.read_bytes()).hexdigest() for p in MU.iterdir() if p.is_file()}
    (BACK / 'mu-before.json').write_text(json.dumps(existing_mu, indent=2), encoding='utf8')


def mcp_read(name):
    data = c.ability('minikids/read-file', {'path': 'mu-plugins/' + name})['content']
    if not (BACK / name).exists():
        (BACK / name).write_text(data, encoding='utf8')
    return data


def mcp_apply(name, data):
    try:
        mcp_read(name)
    except RuntimeError as e:
        if 'mkp_no_file' not in str(e) and 'No existe el archivo indicado.' not in str(e):
            raise
    stage_file = STAGE / name
    stage_file.write_text(data, encoding='utf8')
    if name.endswith('.php'):
        subprocess.run(['C:/xampp/php/php.exe', '-l', str(stage_file)], check=True)
    if name.endswith('.js'):
        subprocess.run(['node', '--check', str(stage_file)], check=True)
    receipt = c.ability('minikids/write-file', {'path': 'mu-plugins/' + name, 'content': data, 'backup': True})
    if name.endswith('.php'):
        subprocess.run(['C:/xampp/php/php.exe', '-l', str(MU / name)], check=True)
    if name.endswith('.js'):
        subprocess.run(['node', '--check', str(MU / name)], check=True)
    assert c.ability('minikids/read-file', {'path': 'mu-plugins/' + name})['content'] == data, f'Readback mismatch for {name}'
    print('Applied & verified:', name)
    return receipt


receipts = []

# 1. Apply 4 new V19 mu-plugin files
for name in ['minikids-seo.css', 'minikids-seo.php', 'minikids-analytics.php', 'minikids-analytics.js']:
    receipts.append({'file': name, 'receipt': mcp_apply(name, (STAGE / name).read_text('utf8'))})

# 2. Patch minikids-estetica.php (remove is_product_category 301 redirect so category archives render natively)
est = mcp_read('minikids-estetica.php')
cat_redir_pattern = r"\r?\n\s*if \(is_product_category\(\)\) \{\r?\n\s*\$cat = get_queried_object\(\);\r?\n\s*if \(\$cat && isset\(\$cat->slug\)\) \{\r?\n\s*wp_safe_redirect\(home_url\('/tienda/\?cat=' \. rawurlencode\(\$cat->slug\)\), 301\);\r?\n\s*exit;\r?\n\s*\}\r?\n\s*\}"
if re.search(cat_redir_pattern, est):
    est_new = re.sub(cat_redir_pattern, '', est, count=1)
    receipts.append({'file': 'minikids-estetica.php', 'receipt': mcp_apply('minikids-estetica.php', est_new)})
else:
    print('minikids-estetica.php: category redirect already removed or pattern not found')

# 3. Patch minikids-commerce-v6.php (add descriptive alt on #mk6-loader img)
v6php = mcp_read('minikids-commerce-v6.php')
target_loader = 'id="mk6-loader" aria-hidden="true"><img src="\'.esc_url(mk6_logo()).\'" alt="" width="200" height="90"'
repl_loader = 'id="mk6-loader" aria-hidden="true"><img src="\'.esc_url(mk6_logo()).\'" alt="MiniKids Perú" width="200" height="90"'
if target_loader in v6php:
    receipts.append({'file': 'minikids-commerce-v6.php', 'receipt': mcp_apply('minikids-commerce-v6.php', v6php.replace(target_loader, repl_loader, 1))})
else:
    print('minikids-commerce-v6.php: loader alt already updated or pattern not found')

# 4. Patch minikids-commerce-v5.js and minikids-retail.js so Tienda H1 stays "Tienda de juguetes"
v5js = mcp_read('minikids-commerce-v5.js')
if "name==='Todos'?'Tienda':name" in v5js:
    receipts.append({'file': 'minikids-commerce-v5.js', 'receipt': mcp_apply('minikids-commerce-v5.js', v5js.replace("name==='Todos'?'Tienda':name", "name==='Todos'?'Tienda de juguetes':name", 1))})

retjs = mcp_read('minikids-retail.js')
if "category==='all'?'Juguetes':active.textContent.trim()" in retjs:
    receipts.append({'file': 'minikids-retail.js', 'receipt': mcp_apply('minikids-retail.js', retjs.replace("category==='all'?'Juguetes':active.textContent.trim()", "category==='all'?'Tienda de juguetes':active.textContent.trim()", 1))})

# 5. Update Page 8 (Inicio) and Page 9 (Tienda) via MCP get-content / update-content
page_updates = []
p8 = c.ability('minikids/get-content', {'id': 8})
if not (BACK / 'page-8-before.json').exists():
    (BACK / 'page-8-before.json').write_text(json.dumps(p8, ensure_ascii=False, indent=2), encoding='utf8')
c8 = p8['content']
c8_new = c8.replace(
    '<h1 class="wp-block-heading screen-reader-text">MiniKids · Juguetes para imaginar, aprender y compartir</h1>',
    '<h1 class="wp-block-heading screen-reader-text">Juguetes, peluches y regalos para niños en Perú</h1>',
    1,
)
seo_block = (
    '\n\n<!-- wp:group {"className":"mk19-home-seo","layout":{"type":"default"}} -->\n'
    '<div class="wp-block-group mk19-home-seo"><!-- wp:heading {"level":2} -->\n'
    '<h2 class="wp-block-heading">Por qué comprar en MiniKids Perú</h2>\n'
    '<!-- /wp:heading -->\n\n'
    '<!-- wp:paragraph -->\n'
    '<p>En MiniKids Perú reunimos juguetes pensados para cada etapa de la infancia con envíos a Lima y todo el país, gratis en pedidos desde S/ 149. Si necesitas orientación para elegir un regalo, nuestro equipo te atiende por <a href="https://wa.me/51991117308" target="_blank" rel="noopener">WhatsApp al +51 991 117 308</a> y ponemos a tu disposición nuestro <a href="http://localhost:8082/wordpress/libro-de-reclamaciones/">Libro de Reclamaciones</a> y canales de <a href="http://localhost:8082/wordpress/contacto/">contacto directo</a>.</p>\n'
    '<!-- /wp:paragraph -->\n\n'
    '<!-- wp:list {"className":"mk19-home-seo-links"} -->\n'
    '<ul class="wp-block-list mk19-home-seo-links">'
    '<!-- wp:list-item -->\n<li><a href="http://localhost:8082/wordpress/product-category/peluches/">Peluches para niños</a></li>\n<!-- /wp:list-item -->'
    '<!-- wp:list-item -->\n<li><a href="http://localhost:8082/wordpress/product-category/didacticos/">Juguetes didácticos</a></li>\n<!-- /wp:list-item -->'
    '<!-- wp:list-item -->\n<li><a href="http://localhost:8082/wordpress/product-category/creatividad/">Arte y creatividad</a></li>\n<!-- /wp:list-item -->'
    '<!-- wp:list-item -->\n<li><a href="http://localhost:8082/wordpress/product-category/juegos-de-rol/">Juegos de rol</a></li>\n<!-- /wp:list-item -->'
    '<!-- wp:list-item -->\n<li><a href="http://localhost:8082/wordpress/product-category/juegos-familiares/">Juegos familiares</a></li>\n<!-- /wp:list-item -->'
    '<!-- wp:list-item -->\n<li><a href="http://localhost:8082/wordpress/product-category/vehiculos/">Vehículos de juguete</a></li>\n<!-- /wp:list-item -->'
    '<!-- wp:list-item -->\n<li><a href="http://localhost:8082/wordpress/product-category/tecnologia/">Tecnología infantil</a></li>\n<!-- /wp:list-item -->'
    '</ul>\n<!-- /wp:list --></div>\n<!-- /wp:group -->'
)
if 'mk19-home-seo' not in c8_new:
    end_marker = '</div>\n<!-- /wp:group -->'
    idx = c8_new.rfind(end_marker)
    assert idx != -1, 'Could not find closing wp:group in page 8'
    c8_new = c8_new[:idx] + seo_block + c8_new[idx:]

if c8_new != c8:
    res8 = c.ability('minikids/update-content', {'id': 8, 'content': c8_new})
    page_updates.append({'id': 8, 'result': res8})
    print('Updated page 8 (Inicio)')

p9 = c.ability('minikids/get-content', {'id': 9})
if not (BACK / 'page-9-before.json').exists():
    (BACK / 'page-9-before.json').write_text(json.dumps(p9, ensure_ascii=False, indent=2), encoding='utf8')
c9 = p9['content']
c9_new = re.sub(
    r'(<h1\b[^>]*>).*?(</h1>)',
    r'\1Tienda de juguetes\2',
    c9,
    count=1,
    flags=re.S,
)
if c9_new != c9:
    res9 = c.ability('minikids/update-content', {'id': 9, 'content': c9_new})
    page_updates.append({'id': 9, 'result': res9})
    print('Updated page 9 (Tienda)')

# 6. Patch tools/router-local.php (with .bak-YYYYMMDD-HHMMSS) so /wordpress/favicon.ico passes to WordPress
router = ROOT / 'tools/router-local.php'
r_txt = router.read_text('utf8')
old_r = "if(!$target&&preg_match('~\\.(?:png|jpe?g|gif|webp|svg|ico|woff2?|ttf|css|js)$~i',$path))"
new_r = "if(!$target&&$path!=='/wordpress/favicon.ico'&&preg_match('~\\.(?:png|jpe?g|gif|webp|svg|ico|woff2?|ttf|css|js)$~i',$path))"
if old_r in r_txt:
    bak = router.with_name(router.name + '.bak-' + datetime.now().strftime('%Y%m%d-%H%M%S'))
    shutil.copy2(router, bak)
    router.write_text(r_txt.replace(old_r, new_r, 1), encoding='utf8')
    subprocess.run(['C:/xampp/php/php.exe', '-l', str(router)], check=True)
    receipts.append({'file': 'tools/router-local.php', 'backup': bak.name})
    print('Updated router-local.php with backup:', bak.name)

(STAGE / 'aplicacion-V19.json').write_text(
    json.dumps({'writes': receipts, 'page_updates': page_updates}, ensure_ascii=False, indent=2),
    encoding='utf8',
)
print('ALL V19 WRITES COMPLETED')
