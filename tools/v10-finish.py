from pathlib import Path
import json,shutil,hashlib
from lxml import html
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v10-backups'
before=json.loads((B/'native-before.json').read_text('utf8'));after=json.loads((B/'native-after.json').read_text('utf8'))
changes=[]
for i,old in before['products'].items():
    new=after['products'][i]
    diff=[k for k,v in old['data'].items() if v!=new['data'][k]]
    attr=old['attributes']!=new['attributes']
    if diff or attr or old['care']!=new['care']:changes.append({'id':int(i),'data_fields':diff,'attributes_changed':attr,'care_changed':old['care']!=new['care']})
assert before['orders']==after['orders'];assert before['options']==after['options']
assert {c['id'] for c in changes if c['attributes_changed']}=={135,133,95,89,42}
cache_changes=[]
for c in changes:
 if 'meta_data' in c['data_fields']:
  i=str(c['id']);a=before['products'][i]['data']['meta_data'];b=after['products'][i]['data']['meta_data']
  added=[m for m in b if m not in a]
  assert all(m['key']=='blocksy_post_meta_options' and set(m['value'])=={'styles_descriptor'} for m in added)
  assert all(m in b for m in a)
  cache_changes.append(c['id'])
 assert set(c['data_fields'])<={'attributes','date_modified','date_modified_gmt','meta_data'},c
integrity={'orders_identical':True,'orders_count':len(after['orders']),'options_hpos_mail_gateways_identical':True,'products_scanned':len(after['products']),'changes':changes,'generated_blocksy_style_cache_on_http_get_ids':cache_changes,'protected_files':json.loads((O/'verificacion-V10-2.json').read_text('utf8'))['protected_files']}
(O/'integridad-V10.json').write_text(json.dumps(integrity,ensure_ascii=False,indent=2),encoding='utf8')
oldhtml=(O/'V10-ficha-antes.html').read_text('utf8');doc=html.fromstring(oldhtml)
script=doc.xpath('//script[contains(@src,"minikids-commerce-v8.js")]')[0]
cause={'script_attributes':dict(script.attrib),'script_position':oldhtml.index('minikids-commerce-v8.js'),'dialog_position':oldhtml.index('id="mk8-availability"'),'original_script_bytes':len((B/'minikids-commerce-v8.js').read_bytes()),'script_hook_priority':20,'dialog_hook_priority':60,'old_script_skips_binding_if_dialog_missing':True,'v5_v6_help_replacement_found':False,'scope':'Source/HTTP order and synthetic VM reproduction, no browser trace.'}
(O/'causa-modales-V10.json').write_text(json.dumps(cause,ensure_ascii=False,indent=2),encoding='utf8')
for receipt in json.loads((O/'aplicacion-V10.json').read_text('utf8')):
    name=receipt['backup'];src=R/'wordpress/wp-content/mu-plugins'/name
    assert src.parent.resolve()==(R/'wordpress/wp-content/mu-plugins').resolve() and '.bak-' in name
    if src.exists():shutil.move(str(src),str(B/name))
for relative,prefix in [
 ('brain/STATE.md','''## V10 · 2026-11-24 · ficha y modales; Chrome pendiente

- Cambios solo commerce-v8.php/css/js por MCP backup=true, relectura y php -l tras PHP. Causa modal comprobada por código/HTML: JS en footer20 antes de dialogs footer60; getElementById null impide registrar clics. Sin evidencia de reemplazo de enlaces por V5/V6. Delegación document, resolución tardía, showModal try/catch y overlay fallback, Esc/X/backdrop, ciclo de foco/scroll y pagehide/pageshow.
- Una miga derecha por filtro nativo del hero; una cabecera Comentarios con resumen y reseñas Woo; precio normal y etiqueta en fila, oferta tres filas; disponibilidad amarillo#ffd60a y compra azul#0756ad; caja2 columnas con3 SVG; cuidados separados28px. Visual pendiente de Chrome.
- CRUD99 productos (98 publicados+1 borrador): edades135/133/95/89/42 normalizadas y relectura sin patrón malformado. Precios/stock/SKU/fotos y14 pedidos/hash intactos; HPOS/pasarelas/correo iguales.13.732 protegidos sin diferencias. No pedidos/correos/navegador/despliegue. INFORME_V10.md y evidencias técnicas, sin cierre P0–P2; Claude debe probar interacción y diseño en Chrome real.

'''),
 ('docs/bugs.md','''### V10-MODALES · P1 · En verificación por Claude
- URL: /wordpress/product/abaco-pedagogico-y-laberinto-de-madera/.
- Antes: cuatro botones visibles sin apertura. Causa comprobada en código/HTML: JS footer20 se ejecuta antes de imprimir dialogs footer60; al faltar el nodo omite listener. La hipótesis de reemplazo por V5/V6 no se confirmó.
- Corrección propia: delegación en document y resolución al clic, fallback con overlay, Esc/X/backdrop, foco/scroll y pagehide/pageshow. 13 casos VM (incluye reproducción del binding vacío original), sin navegador. Pendiente clic real en cuatro modales, foco, fallback, Ajax, Atrás, consola y seis anchos. No cerrado.

### V10-FICHA · P2 · En verificación por Claude
- Una miga derecha y una cabecera Comentarios con lista/formulario Woo nativos; precio/oferta y etiqueta, CTAs amarillo/azul, caja con SVG, separación28px y cinco edades corregidas por CRUD.
- Dos pasadas HTTP/DOM32/32; MK-095/470 oferta y producto sin oferta;99 productos revisados y14 pedidos/hash intactos;13.732 protegidos iguales. INFORME_V10.md. Pendientes geometría/colores computados y regresión interactiva V9 en Chrome real; no autocertificar.

''')]:
    p=R/relative
    if not (B/(p.name+'.before')).exists():shutil.copy2(p,B/(p.name+'.before'))
    old=p.read_text('utf8')
    if prefix.splitlines()[0] not in old:p.write_text(prefix+old,encoding='utf8')
print(json.dumps({'integrity':integrity,'cause':cause},ensure_ascii=True))
