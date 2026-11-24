"""Write the phase-A handoff from measured evidence; preserve earlier evidence."""
import json,shutil,datetime,hashlib
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5';PRIVATE=ROOT.parent/'local-private/v5-backups'
def read(name):return json.loads((OUT/name).read_text('utf8'))
data=read('rendimiento.json');before=data['antes'];after=data['valido'];second=data['valido_segunda'];verify=read('verificacion-final.json')
assert len(verify['passes'])==2
baseline=json.loads((OUT.parent/'v4/respaldo/terceros-sha256.json').read_text('utf8'))
changed=[name for name,sha in baseline.items() if not (ROOT/'wordpress'/name).is_file() or hashlib.sha256((ROOT/'wordpress'/name).read_bytes()).hexdigest()!=sha]
assert not changed,changed
evaluation={'authoritative_before':'antes','authoritative_after':['valido','valido_segunda'],'intermediate_measurements':'Other after stages are retained as intermediate evidence, including a src-empty regression later fixed; do not use them as final results.','static_resources_over_1s':{name:p['requests_over_1s'] for name,p in after['pages'].items()},'all_requests_under_1s':False,'html_over_1s':True,'shop_under_3s_both_final_passes':all(stage['pages']['tienda']['median_html_seconds']<3 for stage in (after,second)),'seven_legacy_images_direct_200':all(r['http_code']==200 and r['num_redirects']==0 and r['content_type']=='image/jpeg' for r in after['seven_legacy_images']),'source_identity_and_background_cutouts_complete':False,'font_glyph_outline_subset_complete':False,'browser_CWV_measured':False,'protected_files_compared':len(baseline),'protected_files_changed':changed,'server':'existing PHP single-thread process; fast static router applied without restart; Apache configuration validated but takeover prevented by denied Stop-Process'}
stamp=datetime.datetime.now().strftime('%Y%m%d-%H%M%S')
shutil.copy2(OUT/'rendimiento.json',PRIVATE/f'rendimiento-before-evaluation-{stamp}.json');data['evaluacion_fase_A']=evaluation
(OUT/'rendimiento.json').write_text(json.dumps(data,ensure_ascii=False,indent=2),encoding='utf8')
rows=[]
for name,label in [('home','Portada `/` → `/wordpress/`'),('tienda','`/wordpress/tienda/`'),('ficha','Ficha bloques 60 piezas')]:
    b,a,s=before['pages'][name],after['pages'][name],second['pages'][name]
    reduction=100*(1-a['inventory_download_bytes']/b['inventory_download_bytes'])
    rows.append(f"| {label} | {b['median_html_seconds']:.3f} s | {a['median_html_seconds']:.3f} / {s['median_html_seconds']:.3f} s | {b['inventory_download_bytes']:,} → {a['inventory_download_bytes']:,} B ({reduction:.1f}% menos) | {b['requests_over_1s']} → {a['requests_over_1s']} |")
media=read('reparacion-medios.json');gallery=read('reparacion-galeria.json');webp=read('conversion-webp.json')
idrows='\n'.join(f"| {r['slug']} | {', '.join(map(str,r['product_ids']))} | {r['attachment_id']} | 200 JPG + 200 WebP, decodificadas |"for r in media)
report=f'''# FASE A · rendimiento y reparación técnica de imágenes

Solo local, 23 de noviembre de 2026 (America/Lima). No se ejecutaron fases B–E. Entrega técnica medida; **no se certifica cierre visual ni identidad comercial de las fotografías**.

## Mediciones reales

Fuentes: `rendimiento.json` (`antes`, `valido`, `valido_segunda`) y `verificacion-final.json`. Se conservan las mediciones intermedias por trazabilidad; varias corresponden a un fallo de `src` vacío ya corregido y **no son resultados finales**.

| Página | HTML antes, mediana de 3 GET | HTML después, pasada 1 / 2 | Inventario transferido antes → después | Recursos >1 s antes → después |
|---|---:|---:|---:|---:|
{chr(10).join(rows)}

Comando de medición: `C:\\Python314\\python.exe tools\\v5-measure.py antes` y, para el estado final, `... valido` / `... valido_segunda`, desde ToyCommerce. Cada petición ejecuta `curl.exe -sS -L --max-time 90 -o NUL -w %{{json}} URL`. Tres muestras HTML secuenciales; después inventario de URLs locales únicas de `img src`, scripts, hojas de estilo/preload y `url()` de CSS, con seis GET concurrentes. Incluye imágenes lazy/ocultas; no ejecuta JS ni selecciona `srcset` según viewport. Bytes descargados sin cabeceras; **no equivale al tráfico inicial de un navegador**. Las URLs rotas iniciales respondían 302→portada 200 HTML: ese contenido también está contado en el antes. No se presenta como descarga de fotos válida. Recursos externos no se descargaron.

Tienda <3 s en ambas pasadas finales; ya estaba por debajo de 3 s en esta medición inicial. No se afirma aceleración del HTML. Cero recursos estáticos >1 s en el inventario final; **la meta global de cero solicitudes >1 s no se alcanza**, pues las páginas PHP superan 1 s. No se midieron LCP/CLS/INP ni colas de estáticos mientras otra petición PHP ocupa el servidor.

## Servidor local

`tools/router-local.php` ahora entrega estáticos por `readfile`, con MIME, `Content-Length`, caché 7 días, ETag/304, Last-Modified y HEAD. Un estático ausente devuelve 404 real sin arrancar WordPress ni redirigir a portada. Se conservaron las restricciones de acceso del router. `php -l` correcto. **No se reinició el servidor**: el archivo del router entra en vigor en las siguientes peticiones.

Se evaluó Apache XAMPP con configuración aislada: `tools/apache-local-v5.conf.in` + `tools/start-local-v5.ps1`; `httpd.exe -t` devuelve `Syntax OK`. Configuración privada fuera de raíz web, 64 hilos, solo loopback IPv4/IPv6, raíz/rutas WordPress, permisos equivalentes, MIME/caché/gzip y 404 de estáticos. No se editó la configuración global de XAMPP. El intento exacto `Stop-Process -Id 18740` fue rechazado con `Access is denied`; se mantuvo PHP en 8082. Apache **no está activo en 8082** y su funcionamiento HTTP no se certifica. El script incluye una guarda que rechaza arrancar si el puerto está ocupado. `PHP_CLI_SERVER_WORKERS` no se utilizó.

Arranque PHP reproducible (desde la raíz de ToyCommerce):

```powershell
& 'C:\\xampp\\php\\php.exe' -S 127.0.0.1:8082 -t . tools/router-local.php
```

Alternativa Apache preparada, solo cuando el listener de 8082 se haya detenido legítimamente:

```powershell
& .\\tools\\start-local-v5.ps1
# Ejecutable usado por el script:
# C:\\xampp\\apache\\bin\\httpd.exe -f C:\\Users\\Cristopher\\orca\\workspaces\\minikids\\MINIKIS\\_trabajo\\local-private\\apache-local-v5.conf
```

## Imágenes y fuentes

- {len(webp)} WebP (calidad 82) en `conversion-webp.json`, más los derivados de las dos imágenes de galería en `reparacion-galeria.json`. Conversión con editor nativo WordPress; PNG/JPG originales conservados. Medios/metadata regenerados mediante APIs nativas; conservación de imagen principal mediante CRUD WooCommerce. No se subieron fotos externas ni se generaron juguetes nuevos.
- `minikids-performance.php` sirve WebP solo si existe, adapta metadata de `srcset` en memoria, añade dimensiones y `decoding="async"`. Portada: 50 imágenes de campaña con `srcset`, 46 lazy y 4 eager en ambas pasadas. También se corrigieron dimensiones/formato del logo heredado, sin cambiar estructura del pie. Contenido editorial de portada 8 y pie 219 conservado; sin actualización de bloques en DB.
- Montserrat variable y Barlow Condensed WOFF2 locales, licencias incluidas en `mu-plugins/minikids-v5-assets`. `font-display:swap` y un solo preload (Montserrat crítica). Se retiró la petición a Google Fonts y las referencias TTF/base64 del CSS comercial. WOFF2: 228348 B + 44604 B. Decodificación FreeType y texto español idéntico a las TTF originales; Montserrat verificada en pesos 100/400/700/900.
- **Subset limitado**: cmap latino/puntuación; se conservaron contornos, variantes y tablas originales para evitar fuentes inválidas. No se presenta como reducción completa de glifos. Los intentos inválidos se conservaron privados y no son las fuentes referenciadas por el CSS final.
- Se separó el JS del carrusel V4 para cargarlo solo en portada, dependiente de `minikids-retail`. La tienda deja de cargar el CSS de reclamaciones/nosotros. Se mantuvieron los scripts WooCommerce, fragmentos, drawer y dependencias. No se eliminó CSS global sin prueba de que estuviera muerto.

## Las siete imágenes

Cada JPG original de 300×300 y su WebP: **HTTP 200 directo, MIME imagen y decodificación correcta en dos pasadas**. No basta el código 200: antes las siete rutas redirigían a HTML.

| Fuente `prod-…-hero-principal` | Producto ID | Medio ID | Comprobación |
|---|---:|---:|---|
{idrows}

Las tres referencias `-scaled.jpg` ausentes se recuperaron desde el original del proyecto. La ficha de bloques reveló además cuatro URLs rotas de galería: se regeneraron sus medios **173 y 174**, con respaldos de archivos/metadata; ver manifiesto de galería.

**Pendiente para Claude/dueño**: estos archivos recuperan las fotos previamente asignadas, no acreditan que sean fotos reales de cada SKU. La fuente de karaoke muestra piano/micrófono antiguo; memoria muestra niños en un juego de tablero, sin evidencia de animalitos; pista muestra una maqueta con vías. Las alternativas locales también presentan incongruencias. No se reemplazaron por fotos de otros productos. Bloques, cocina, scooter y tren tienen fuentes visualmente plausibles pero tampoco identidad/SKU confirmados. No se aplicó recorte sin fondo ni se reasignó una foto distinta sin una fuente verificable. **La recuperación comercial y los recortes pedidos quedan pendientes**; las asignaciones existentes, precios y stock de los 17 productos se conservaron (`crud-verificacion.json`).

## Archivos, respaldo y comprobaciones

Código propio aplicado por MCP, con lectura previa y `backup=true`: `mu-plugins/minikids-retail.php`, `.css`, `.js`; `minikids-performance.php`; `minikids-campanas-v4.js`; cambio puntual del logo en `minikids-estetica.php`. Fuentes/licencias mediante WP Filesystem nativo en `mu-plugins/minikids-v5-assets/`. Derivados en uploads mediante editor/API nativa. No hubo SQL, cambios de terceros, despliegue, pedidos ni cobros.

Herramientas propias: `v5-native-media.php`, `v5-repair-gallery.php`, `v5-subset-font.py`, `v5-woff2.cjs`, `v5-apply-mcp.py`, `v5-update-performance.py`, `v5-fix-legacy-logo.py`, `v5-measure.py`, `v5-verify.py`, `v5-find-missing.py`, `v5-update-brain.py`, `v5-report.py`, plantilla/script Apache. No ejecutar de nuevo las mutaciones sobre outputs existentes sin respaldarlos. Los manifiestos permiten identificar los nuevos derivados; los originales permanecen disponibles.

Respaldos fuera de WordPress, en `_trabajo/local-private/v5-backups/`: estado nativo de 115 medios/17 productos, fuentes originales de las siete imágenes y derivados previos, galerías, lecturas MCP, copias automáticas `.bak`, JSON anteriores, experimentos y memoria/grafo anteriores. Sin credenciales en esta entrega. Se detectaron escrituras concurrentes de otro proceso en V5; se preservaron los estados observados y se releyó antes de las escrituras finales. Los artefactos de planificación/ampliación de catálogo detectados no se ejecutaron ni forman parte de esta FASE A.

Dos pasadas finales HTTP/decodificación/dimensiones/srcset, Store API 17 productos, `.widget_shopping_cart_content` y JS de fragmentos presentes. Prueba nativa Ajax aislada: agregar 1 unidad del producto 38, comprobar fragmentos, retirarla y confirmar sesión vacía; sin pedido. PHP/JS propios validados. Comparación de **{len(baseline)} archivos protegidos: 0 diferencias** respecto a respaldo V4. Graphify actualizado en modo secuencial: el modo con procesos fue bloqueado por el sandbox. No se certifican interacción visual, editor Gutenberg, responsive, zoom, hover ni CWV: no se usaron Playwright/Chrome.

## URLs para que Claude revise/capture

- http://localhost:8082/wordpress/
- http://localhost:8082/wordpress/tienda/
- http://localhost:8082/wordpress/product/bloques-creativos-60-piezas/
- Fichas de IDs 40, 41, 43, 44, 134 y 136: consultar sus permalinks reales en Store API (`/wordpress/wp-json/wc/store/v1/products?per_page=100`).
- Siete recursos JPG: `/wordpress/wp-content/uploads/2026/10/prod-{{bloques-60,memoria-animales,karaoke,pista-carreras,scooter,cocina-chef,tren-madera}}-hero-principal-300x300.jpg`, más la misma ruta `.webp`.

Tras esta entrega se pausa. B–E no se iniciaron. No se autocertifica cierre de FASE A: quedan identidad/recortes, subset completo y QA de navegador, además del límite del servidor de un hilo.
'''
target=OUT/'INFORME_A.md'
if target.exists():shutil.copy2(target,PRIVATE/f'INFORME_A-before-{stamp}.md')
target.write_text(report,encoding='utf8')
state=ROOT/'brain/STATE.md';bugs=ROOT/'docs/bugs.md'
for f in (state,bugs):shutil.copy2(f,PRIVATE/(f.name+'.before-A-'+stamp))
entry='''## FASE A V5 · 2026-11-23 · entrega HTTP; QA visual/identidad pendiente

- Router estático rápido, WebP 82, WOFF2 Montserrat/Barlow locales, dimensiones/lazy/srcset y carga condicional de carrusel/CSS propio. Apache aislado validado en sintaxis; no se pudo detener el listener 8082 por permisos, por lo que sigue PHP de un hilo sin reinicio.
- Siete miniaturas JPG/WebP recuperadas por API nativa, 200 directo/MIME/decodificación en dos pasadas; medios 166/169/172/175/178/181/184. Derivados de galería 173/174 también reparados. Fuentes originales conservadas; no nuevas fotos ni cambios de SKU/precio/stock.
- Persisten identidad fotográfica sin confirmar y recortes: karaoke/memoria/pista presentan incongruencias. Subset de cmap latino; contornos completos. No certificar cierre visual/Gutenberg/CWV.
- Mediciones autoritativas: `_trabajo/diseno_minikids/v5/rendimiento.json`, etapas valido/valido_segunda; las etapas intermedias conservan una regresión src vacío ya corregida. Informe y verificación en v5/INFORME_A.md y verificacion-final.json. Recursos estáticos <1 s, HTML ~1.4 s. Ajax add/remove verificado sin pedidos; terceros/core 13732 sin diferencias. Solo A; B–E no ejecutadas.

'''
state.write_text(entry+state.read_text('utf8'),encoding='utf8')
bugentry='''\n### BUG-V5-A-IMAGENES · P2 · FASE A · Estado: En verificación
- URL: /wordpress/tienda/ y ficha bloques 60 piezas.
- Causa: tamaños de imagen y tres archivos scaled ausentes; el router devolvía portada HTML tras redirección para un recurso inexistente.
- Corrección: APIs nativas de metadata/editor, CRUD conservando imagen del producto, WebP, dimensiones/srcset y 404 estático real; galerías 173/174 incluidas.
- Verificación: dos pasadas HTTP/MIME/decodificación, siete JPG + siete WebP 200 directo; cero src vacíos. Se corrigió durante QA una regresión propia que añadía src vacío en atributos Blocksy antes del merge.
- Evidencia: _trabajo/diseno_minikids/v5/verificacion-final.json y rendimiento.json (valido/valido_segunda).
- Pendiente: identidad real de fotos, recortes sin fondo y QA visual de Claude. No cerrar como 0 P0–P2 visuales.

### BUG-V5-A-SERVIDOR · P2 · FASE A · Estado: Mitigado; pendiente
- Router rápido aplicado sin reinicio. Apache dedicado pasa sintaxis, pero Stop-Process del listener 8082 fue rechazado por el sandbox.
- Recursos estáticos medidos <1 s; HTML >1 s. No se alcanza la meta global de cero solicitudes >1 s ni se verificó concurrencia con PHP ocupado.
- Informe: _trabajo/diseno_minikids/v5/INFORME_A.md. Sin otras fases.
'''
bugs.write_text(bugs.read_text('utf8')+bugentry,encoding='utf8')
print('INFORME_A.md, performance evaluation, STATE and bugs written from final measured evidence')
