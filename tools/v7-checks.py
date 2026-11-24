"""Read-only V7 checks, HTTP snapshots and native integrity comparison."""
from pathlib import Path
import urllib.request,json,socket,hashlib,subprocess,re
from PIL import Image
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5';BACK=ROOT.parent/'local-private/v7-backups'
resolve=socket.getaddrinfo;socket.getaddrinfo=lambda host,*a,**kw:resolve('127.0.0.1' if host=='localhost' else host,*a,**kw)
op=urllib.request.build_opener(urllib.request.ProxyHandler({}));base='http://localhost:8082/wordpress/';checks={}
for name,url in [('home',''),('debug','?mk6_loader_debug=1'),('account','my-account/')]:
    with op.open(base+url,timeout=35) as r:html=r.read().decode();checks[name+'_200']=r.status==200
    (OUT/f'V7-{name}-extra.html').write_text(html,encoding='utf8')
    if name=='home':
        checks['default_debug_false']='window.mk7LoaderDebug=false' in html
        checks['legacy_loader_removed']='animation:none}}' not in html and '2750' not in html
        checks['reduced_motion_explicit_opacity_animation']='animation-name:mk7-pulse!important' in html and 'animation-iteration-count:infinite!important' in html
        checks['loader_aria_hidden']='id="mk6-loader" aria-hidden="true"' in html
    if name=='debug':checks['local_debug_enabled']='window.mk7LoaderDebug=true' in html
    if name=='account':
        checks['account_login_spanish']=all(x in html for x in ['Iniciar sesión','Contraseña','¿Olvidaste tu contraseña?'])
        checks['account_no_english_sample']=not re.search(r'>\s*(Login|Username or email address|Remember me|Lost your password\?|Log in)\s*<',html)
manifest=json.loads((OUT/'medios-V7.json').read_text('utf8'))
for e in manifest:
    pid=e['product'];checks[f'{pid}_original_second_photo']=e['original_is_first_gallery_image'];checks[f'{pid}_original_unchanged']=e['original_sha256_unchanged']
    with op.open(e['url'],timeout=35) as r:data=r.read();checks[f'{pid}_webp_200']=r.status==200
    from io import BytesIO
    im=Image.open(BytesIO(data));checks[f'{pid}_webp_size_alpha']=im.size==(1200,1200)and im.convert('RGBA').getchannel('A').getextrema()==(0,255)
before=json.loads((BACK/'native-before.json').read_text('utf8'));after=json.loads((OUT/'native-audit-V7.json').read_text('utf8'))
commercial=lambda products:[{k:v for k,v in p.items()if k not in ['image','gallery','source']}for p in products]
checks['commercial_fields_unchanged']=commercial(before['products'])==commercial(after['products'])
for key in ['orders_total','hpos','gateways']:checks[key+'_unchanged']=before[key]==after[key]
checks['product41_unassigned']=next(p for p in before['products']if p['id']==41)==next(p for p in after['products']if p['id']==41)
checks['locale_es_PE']=after['locale']=='es_PE'
checks['audited_single_literal_templates_translated']=not after['untranslated']
# Reuse the established protected-file baseline, read-only hashes only.
protected=json.loads((ROOT.parent/'local-private/v6-backups/protected.json').read_text('utf8'))
differences=[name for name,digest in protected.items()if not(ROOT/name).is_file()or hashlib.sha256((ROOT/name).read_bytes()).hexdigest()!=digest]
checks['protected_files_unchanged']=not differences
result={'checks':checks,'all_pass':all(checks.values()),'protected_checked':len(protected),'protected_differences':differences,'scope':'Read-only HTTP/native snapshot/image decoding/static CSS assertions. No browser, computed styles, physical toy certification or email delivery.'}
(OUT/'checks-V7.json').write_text(json.dumps(result,ensure_ascii=False,indent=2),encoding='utf8');print(json.dumps(result,ensure_ascii=True,indent=2))
raise SystemExit(0 if result['all_pass']else 1)
