"""Generate the P handover only from completed verification evidence."""
from pathlib import Path
import json,datetime,shutil
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5';PRIVATE=ROOT.parent/'local-private/v5-backups'
def read(name):return json.loads((OUT/name).read_text('utf8'))
v=read('verificacion-P.json');v2=read('verificacion-P-segunda.json');http=read('verificacion-http-P.json');images=read('imagenes_auditoria.json');admin=read('verificacion-admin-P.json');catalog=read('catalogo_demo.json')
orders=read('verificacion-pedidos-P.json')
details=read('verificacion-detalles-P.json');assert details['all_correct']
assert all(v['checks'].values())and all(v2['checks'].values())
assert images['found']==images['expected']==121 and images['alpha_all']and images['unique_sha256']==121
assert admin['correct_counts']and len(http['passes'])==2 and len(catalog)==81
assert len({p['id']for p in catalog})==81
assert orders['inventory_unchanged']and orders['hpos_active']
prompts=list((OUT/'imagenes').glob('*.png.json'))
assert len(prompts)==121 and all(json.loads(p.read_text('utf8')).get('prompt')for p in prompts)
assert all(p['currency']=='PEN'for p in catalog)
new_offers=[p for p in catalog if p['sale_price']]
discounts=[100*(1-float(p['sale_price'])/float(p['regular_price']))for p in new_offers]
effective=[float(p['sale_price']or p['regular_price'])for p in catalog]
performance=read('rendimiento.json');perf1=performance['P_despues']['pages'];perf2=performance['P_segunda']['pages']
perfrows='\n'.join(f"| {name} | {perf1[name]['median_html_seconds']:.3f} / {p['median_html_seconds']:.3f} s | {p['inventory_download_bytes']:,} B | {p['requests_over_1s']} |"for name,p in perf2.items())
catnames={'peluches':'Peluches','didacticos':'Didácticos y Madera','vehiculos':'Vehículos','juegos-de-rol':'Juegos de rol','creatividad':'Creatividad','juegos-familiares':'Juegos en familia','tecnologia':'Tecnología'}
rows='\n'.join(f"| {catnames[c]} | {sum(p['category']==c for p in catalog)} | {n} |"for c,n in v['categories'].items())
examples='\n'.join('- '+next(p['url']for p in catalog if p['category']==c)for c in catnames)
content=f'''# FASE P · Catálogo demo local

Entrega técnica de {datetime.datetime.now().strftime('%Y-%m-%d %H:%M')} (hora local del equipo). Solo local. Fases B–E no ejecutadas. La generación de imágenes y las pruebas HTTP no sustituyen la validación comercial ni la revisión visual de Claude.

Destino comprobado: base local **minikids_t3_revision_20261111**, host de DB loopback y home `http://localhost:8082/wordpress/`. La protección de los scripts usa esa identidad, verificada por wp-load; no se renombró ni migró una base. El nombre histórico minikeds_dev2 no es el de esta copia de revisión.

## Resultado comprobado

**98 productos publicados: 17 anteriores + 81 nuevos demo**, creados con `WC_Product_Simple` y guardados por CRUD WooCommerce. Los campos de los 17 publicados y el borrador previo ID 212 se conservaron (nombres, SKU, estado, precios, stock, imágenes asignadas, galerías, categorías y descripciones). La reparación de derivados de galería, sin sustituir fotos, está documentada en INFORME_A y reparacion-galerias-ampliada.json.

Todos los nuevos tienen `_mk_demo=1`, SKU único **MK-018 a MK-098**, atributo Marca **MiniKids**, descripciones corta/larga con viñetas, edad orientativa, contenido y advertencias, stock administrado, peso y dimensiones. No se crearon subcategorías porque las siete existentes bastan.

| Categoría existente | Nuevos demo | Publicados finales |
|---|---:|---:|
{rows}

- Precios efectivos de los nuevos: **S/ {min(effective):.2f} a S/ {max(effective):.2f}**, moneda PEN.
- Nuevas ofertas: **{len(new_offers)} de 81**; descuentos calculados con precios guardados, **{min(discounts):.1f}% a {max(discounts):.1f}%**. Catálogo completo: **{v['offers_total']} de 98 con oferta ({100*v['offers_total']/98:.1f}%)**. La oferta heredada del juego de memoria supera ligeramente 35%; se preservó por la orden de no cambiar precios existentes.
- Stock demo: {min(p['stock']for p in catalog)} a {max(p['stock']for p in catalog)}; {sum(p['stock']<=10 for p in catalog)} nuevos con 10 unidades o menos.
- Galerías: **20 nuevos con 2 fotos adicionales cada uno**; {v['gallery_total']} productos totales tienen al menos dos fotos de galería contando los 17 anteriores.

## Imágenes y hoja de contacto

**121 PNG generados individuales**: 81 principales + 40 vistas de galería. Herramienta integrada `image_gen`; las galerías usan la foto principal como referencia de edición para conservar el juguete. No se usaron fotos ajenas ni se reutilizó una imagen entre productos distintos. Las fuentes están en `imagenes/`; prompts y procedencia en los archivos contiguos `*.png.json` (el primer prompt también se registra allí).

Medios preparados con editor/API nativa WordPress: principal **1200×1200**, tarjeta **600×600**, otros tamaños registrados y sidecars **WebP calidad 82**, conservando PNG y originales generados. Alt descriptivo para todos. IDs y URLs en `catalogo_demo.json`. La ruta y metadatos del catálogo permanecen editables en Productos/Medios.

`catalogo_contacto.jpg` muestra los **81 nuevos** sobre blanco y azul oscuro; `catalogo_galerias_contacto.jpg` muestra las 40 vistas adicionales. `imagenes_auditoria.json` comprueba dimensiones, alfa y 121 SHA-256 distintos. Las imágenes servidas se decodificaron en dos pasadas HTTP y las demo conservaron transparencia. Estas pruebas no certifican bordes perfectos a cualquier escala ni correspondencia con un producto físico.

## Edición humana y reversión

En **Productos → Todos los productos**, filtro **Solo demo MiniKids**: http://localhost:8082/wordpress/wp-admin/edit.php?post_type=product&mk_demo_filter=demo

Cada ficha tiene el checkbox **Producto demo local** en Datos del producto → General. Precios, stock, Marca, contenido, imagen y galería se editan con WooCommerce nativo. El filtro inverso muestra los 17 publicados originales. Consulta nativa de integración comprobada: 81 demo / 17 sin marca demo. No se certifica navegación del panel con un navegador en esta entrega.

Para retirar la ampliación, el dueño puede filtrar demo y enviar esas fichas a la papelera desde el panel; no eliminar en bloque medios originales ni importar una DB sobre pedidos. No se ejecutó esa retirada. Respaldo previo y journal de creación/actualizaciones en `local-private/v5-backups/P-before.json` y `P-native-journal.jsonl`, fuera de WordPress. El importador tiene bloqueo exclusivo, respeta los demo ya publicados y solo publica nuevas fichas cuando sus imágenes requeridas están disponibles.

## Decisiones pendientes del dueño

1. **Precios y especificaciones comerciales:** son datos de demostración elegidos dentro del rango solicitado; no se consultó mercado externo por la restricción de trabajo local. No se certifica que sean cotizaciones reales ni medidas, pesos, edades o contenidos verificados del proveedor. Los metadatos `_mk_demo_price_status` y `_mk_demo_spec_status` y las descripciones lo identifican. Validar y reemplazar antes de vender o publicar fuera del local.
2. **Fotos heredadas:** la identidad de las siete fuentes de A sigue sin confirmación. No se sustituyeron por fotos de otros juguetes ni por nuevas imágenes sintéticas. Revisar INFORME_A.
3. **Licencias heredadas:** el producto previo ID 85 conserva su nombre/foto de personaje por la instrucción de no modificar los 17. Ningún nuevo demo usa marcas registradas ajenas ni personajes licenciados. Corresponde al dueño decidir la situación de ese artículo antes de su publicación comercial.
4. **Galerías generadas:** son vistas ilustrativas del mismo diseño, no evidencia de un objeto físico. Claude/dueño debe revisar formas, componentes, bordes y coherencia en las hojas de contacto.

## Verificación y archivos escritos

Dos pasadas nativas en `verificacion-P.json` / `verificacion-P-segunda.json`: 98 publicados, 81 demo, 14 por categoría, SKU únicos, ofertas válidas, 20 galerías nuevas, dimensiones y alt, y datos anteriores conservados. Dos pasadas de `verificacion-http-P.json`: Store API con los 98 IDs esperados; todas las imágenes inventariadas devuelven 200 y se decodifican; ficha de muestra por cada categoría; tienda con scripts/markup de fragmentos. Prueba Ajax aislada de agregar/retirar un demo y vaciar esa sesión, sin solicitar creación de pedidos ni envío de correos. HPOS activo e inventario de IDs de pedidos idéntico antes/después del chequeo final (`verificacion-pedidos-P.json`); no se inspeccionaron eventos de proveedor de correo ni se afirma entrega de mensajes.

Código de aplicación nuevo: `wordpress/wp-content/mu-plugins/minikids-demo-catalogo.php`, aplicado por MCP con backup=true, lectura posterior idéntica y `php -l` correcto. Herramientas `tools/v5-p-*.php/py`, `v5-plan-catalog.py`, fuentes de imagen, medios nuevos y manifiestos. Producto/Medios por CRUD/API nativa, sin SQL. No se editaron core, Blocksy, WooCommerce ni pasarelas. No hubo despliegue, Playwright ni Chrome.

Se archivaron mediante WP Filesystem las cuatro WOFF2 provisionales no referenciadas de uploads, comprobando copia/hash antes de retirarlas. Permanecen respaldadas fuera de WordPress (`fuentes-provisionales-archivadas.json`); los TTF originales y las dos WOFF2 definitivas del mu-plugin no cambiaron.

Auditoría de detalle posterior al carrito: Marca MiniKids, viñetas, stock y precios contra el plan, y metadatos de estado demo correctos en las 81 fichas (`verificacion-detalles-P.json`). STATE/bugs actualizados con respaldo y Graphify regenerado en modo secuencial; el grafo incluye el nuevo módulo demo. La herramienta advirtió diferencia entre paquete Graphify 0.9.57 y skill 0.9.111; no se actualizaron dependencias ni se tomó esa advertencia como validación de navegador.

## Rendimiento con los 98 publicados

Medición posterior al catálogo en `rendimiento.json`, etapas `P_despues` y `P_segunda`, separadas de las muestras A con 17 productos. No comparar el peso de tienda como si ambas tuvieran el mismo catálogo.

| Página | HTML, mediana de 3 GET por pasada | Inventario transferido, pasada 2 | Recursos estáticos >1 s, pasada 2 |
|---|---:|---:|---:|
{perfrows}

Se conserva el método documentado de curl/inventario HTTP (incluye imágenes lazy/ocultas, excluye cabeceras y ejecución JS); no son métricas de navegador. La meta global de cero solicitudes >1 s sigue sin certificarse cuando el HTML PHP supera 1 s. No se afirma LCP/CLS/INP ni ausencia de desbordes o bloques inválidos en el editor.

## URLs de revisión para Claude

- http://localhost:8082/wordpress/tienda/
- http://localhost:8082/wordpress/wp-json/wc/store/v1/products?per_page=100
{examples}

La lista completa de URLs e IDs, descripciones, precios, stock, medios y galerías está en `catalogo_demo.json`.
'''
dest=OUT/'INFORME_P.md'
if dest.exists():shutil.copy2(dest,PRIVATE/('INFORME_P-before-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S')+'.md'))
dest.write_text(content,encoding='utf8')
print('INFORME_P.md written from completed checks')
