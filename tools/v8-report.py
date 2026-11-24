from pathlib import Path
import json,re,html
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5'
changes=json.loads((OUT/'productos-corregidos-V8.json').read_text('utf8'))
passes=[json.loads((OUT/f'verificacion-V8-{i}.json').read_text('utf8')) for i in [1,2]]
assert all(x['all_pass'] for x in passes)
def plain(s):return re.sub(r'\s+',' ',html.unescape(re.sub('<[^>]+>',' ',re.sub('[\U0001F000-\U0001FAFF\u2600-\u27BF\ufe0f]','',s)))).strip()
samples=''
for row in changes['samples']:
 samples+=f"\n### {row['id']} · {row['name']}\n\nAntes, descripción larga: {plain(row['before']['description'])[:470]}\n\nDespués, descripción: {plain(row['after']['description'])[:470]}\n\nCuidados en Detalles: {' '.join(row['after']['care'])[:520]}\n"
report='''# MiniKids V8 · Implementación local y verificación acotada

24 de noviembre de 2026. Destino: `http://localhost:8082/wordpress/`, copia `minikids_t3_revision_20261111`. No se desplegó ni se usó Chrome/Playwright. **No hay cierre global de V8**: no se pudo confirmar entrega Brevo; la foto 41 se conserva; falta revisión real de navegador. Las pasadas técnicas aprobadas no equivalen a cero P0–P2 visuales.

## 1–2. Dos columnas completas y compra limpia

El HTML contiene un contenedor izquierdo con galería y, debajo, Descripción del producto → Detalles del producto: → Comentarios. Histograma 5→1 y formulario nativo aparecen una vez. La compra es su hermano derecho; «COMPLETA TU PEDIDO» queda fuera de ambas columnas, debajo. Se retiraron las pestañas mediante hooks; no se movieron secciones con JS. CSS define 52/48, divisor gris y, por debajo de 900 px, galería → compra → descripción/detalles/comentarios. Galería con posición relativa para anclar sus controles; sin sticky.

Compra: migas, título, referencia, valoraciones o estado vacío, precio, etiqueta amarilla según stock real, formulario nativo y enlaces de ayuda. No se renderizan Características, chips de edad/stock, fila SKU/CATEGORY/Información de envío ni tira de promesas. Se conserva la validación nativa de stock y el formulario de añadir.

**Comprobado:** estructura y orden por lxml sobre HTTP de osito y ábaco; una sola reseña/histograma, cuatro modales y ausencia del HTML heredado. **Pendiente:** medir anchos, divisor, alturas, zoom, miniaturas/flechas/puntos y orden visual en 360/390/768/1024/1280/1440. No se midieron estilos computados ni píxeles.

Reproducir: `/product/osito-miel-con-lazo/` y `/product/abaco-pedagogico-y-laberinto-de-madera/`. En móvil verificar el orden anterior y en escritorio que el texto permanece a la izquierda, sin sticky.

## 3. Los 98 productos: limpieza y ubicación de datos

Se recorrieron y modificaron los 98 publicados con `WC_Product`, sin SQL. Se eliminaron pictogramas, encabezados decorativos y párrafos/líneas exactos repetidos. Para los 17 heredados, la descripción comercial se recuperó de la descripción corta existente; sus cuidados pasaron a `_mk8_care`. En los demo se preservó el texto de demostración y sus advertencias; no se inventaron materiales, certificaciones ni contenido físico.

Edad y Contenido de los 81 demo se trasladaron desde el texto existente a atributos nativos visibles en Detalles; peso, medidas y atributos anteriores se conservan. **Los datos demo siguen sujetos a validación física/comercial del dueño.** La descripción corta sigue editable en su campo nativo; no se imprime bajo el precio. El editor de producto incorpora «Cuidados y advertencias» en Datos del producto → General, una línea por recomendación. El resto de detalles se edita en Atributos/Envío de Woo.

`productos-corregidos-V8.json` registra todos los cambios y cinco muestras completas antes/después; `detalles-migrados-V8.json` identifica los 81 traslados. Verificación CRUD: no variaron precios normales/de oferta/actuales, SKU, cantidades, imágenes principales ni galerías. Los duplicados comprobados son textuales; no se certifica una revisión editorial humana de las 98 fichas.

IDs corregidos: **__IDS__**.

__SAMPLES__

## 4. Oferta en el precio

Se suprime sale flash en la galería; los bucles mantienen su badge pequeño porcentual. Si hay oferta vigente real y precio menor al regular, se muestran Precio especial (rojo #e5384d), Precio regular tachado y Descuento porcentual. Sin oferta se usa precio nativo normal. El descuento se calcula, no se escribe en el catálogo.

Comprobado por HTTP: la galería no contiene badge y los datos comerciales permanecen idénticos. La apariencia/alineación queda para Claude. Revisar osito (26.90/29.90) y una ficha sin oferta desde tienda; en recomendaciones el badge debe seguir siendo pequeño y porcentual.

## 5–7. Cuatro modales y datos editables

Se usa `<dialog>` nativo con `showModal`, `role=dialog`, `aria-modal`, título asociado, backdrop, tarjeta blanca de 28 px y cierre SVG circular azul. X, clic fuera de la tarjeta y botón final llaman a close; Escape y bloqueo del fondo/foco dependen del comportamiento nativo de dialog. Se devuelve explícitamente el foco al disparador. **No se ejecutó interacción de teclado, lector de pantalla ni foco en navegador.**

Ajustes → MiniKids · Negocio tiene 17 campos nuevos en el mismo formulario: títulos, texto de disponibilidad, nota de inventario, botón final, recojo/nombre/dirección, plazo, aclaraciones, texto final, explicaciones BACS/COD y políticas. Verificación de Settings API: registro en el grupo existente, 17 textareas renderizadas y saneamiento sin emojis. Esto no prueba un guardado desde el panel real.

- Disponibilidad: envío a domicilio; no tabla de recojo porque ningún método local_pickup está activo. Recojo requiere método Woo activo y opt-in explícito, nombre y dirección informados.
- Pago: consulta `get_available_payment_gateways`, por tanto solo BACS/COD efectivos en esta instalación local. Sus títulos reales incluyen «(prueba local)». Otras pasarelas conservan opciones enabled en la base, pero el filtro local las excluye; no se muestran logos de tarjetas/Yape/Culqi/Mercado Pago ni se alteraron pasarelas. Se usan SVG propios de banco/efectivo.
- Entrega: la zona nativa se llama **Perú**, no exclusivamente Lima. Se refleja ese nombre: envío estándar S/ 15.00 y gratis desde S/ 149, según configuración. No se inventó un plazo: figura «a validar por el dueño». La tabla es informativa; Woo calcula cobertura/condiciones/costo final en checkout.
- Devoluciones/garantías: dos secciones, textos provisionales explícitos a validar; contacto vigente `miguel16hr@gmail.com`, leído de Negocio. El histórico mencionaba otro correo: no se reutilizó. No se copió contenido ni identidad de Miniso/Tai Loy.

La fuente actual de negocio es MIMI KIDS STORE E.I.R.L., RUC 20609651530, Av. Alfredo Mendiola 3698, Independencia, Lima, Perú; teléfono/WhatsApp y correo siguen editables en Negocio. No se afirma que esta dirección sea un punto de recojo.

Reproducir: abrir los cuatro disparadores en una ficha. En cada modal probar Tab/Shift+Tab, Esc, clic fuera, cierre y vuelta al botón original; probar con viewport móvil y texto largo. En Ajustes cambiar un texto de prueba, guardar, comprobar y restituir. Claude debe hacerlo en Chrome real.

## 8. Pendientes anteriores

**Idioma:** core es_PE/es_ES ya estaba disponible; se confirmó es_PE. Nuevo intento oficial de Woo falló con cURL7 contra 127.0.0.1:9. No se instaló paquete Woo. `language-V8.json` contiene la respuesta. Fallback propio incluye Referencia/Categoría, accesibilidad de tarjetas, Photoswipe, búsqueda y 404. Dos pasadas HTTP de `/`, `/tienda/`, ficha, ábaco, carrito, checkout, mi cuenta y 404 dejaron vacía la lista de **cadenas inglesas muestreadas** por pantalla; ver `english_remaining_by_screen`. No es prueba exhaustiva de toda cadena dinámica/pantalla autenticada. Mi cuenta se tituló en español por API nativa.

**Sin emojis:** limpieza de contenido de los 98 productos; retiro de pictogramas en código propio y del carrito generado por CSS. De 12 páginas publicadas auditadas, se limpiaron 10/Nosotros y 11/Contacto; tienda ya se había limpiado. Se conservaron nombres/orden de bloques según parser nativo en 9/10/11/36; no se abrió Gutenberg. Solo quedan pictogramas en comentarios de JS, sin salida al sitio. Las capturas históricas de correo son evidencia previa y conservan sus asuntos originales.

**Producto 41:** se probó image_gen integrado, con referencia original y prompt restrictivo: conservar únicamente pista/coches visibles, sin completar bordes ni partes ocultas. Resultado `imagenes-v8/producto-41-intento-v8-rechazado.png`, generado desde la foto `prod-pista-carreras-hero-principal.jpg`. La inspección visual detectó reconstrucción del coche blanco/azul, geometría/textura alteradas y fondo no apto; se rechazó. No se asignó ningún medio ni se cambió su original/galería. Fuente muestra maqueta parcial y no permite certificar un producto completo. Prompt completo en `producto-41-V8.json`; herramienta: built-in image_gen, sin CLI alternativo. OpenCV no está disponible en este Python.

**Diez minutos:** el código productivo define 600000 ms y usa límite absoluto que no aumenta por actividad/recarga. Se ejecutó su bloque real en VM: 13/13 casos (incluye loader, reloj de 600 s, 15 s, recarga, retorno de pestaña y bloqueo vencido). HTTP verificó TTL 600, `?mk6_ttl=15` → 15, y valor 1 → 600. No se esperaron diez minutos ni se fingió una navegación real. Reproducir en Chrome con la clave `sessionStorage['mk6-secure-window']`, JSON `{version:7,start,end,activity}`, timestamps ms. Para nueva prueba borrar la clave; entrar con `?mk6_ttl=15`, editar/recargar y volver de otra pestaña: end no aumenta, aviso, retorno al carrito intacto. Sin parámetro end-start debe ser 600000.

**Realizar pedido:** HTTP confirma un solo botón nativo dentro de `#payment`. JS lo sitúa en el panel Pago y CSS lo hace visible solo dentro de ese panel activo; 6/6 casos de VM sobre las funciones productivas de pasos comprueban Datos/Entrega/Pago, campos requeridos, bloqueo del evento en pasos previos y volver atrás. El servidor nativo rechazó por HTTP datos incompletos y Términos ausentes **antes** del único pedido válido. Falta comprobar la visibilidad/operación real y refresco Ajax en navegador.

## 9. Pedido único y correos: resultados y bloqueo real

Se creó **un solo pedido #499** por POST del checkout clásico nativo, con BACS y destinatario leído de Ajustes: `miguel16hr@gmail.com`; el destinatario de Nuevo pedido coincidía. Datos ficticios «Prueba QA», dirección «Prueba QA local sin despacho 123», documento QA-LOCAL-V8. Transición nativa BACS a **on-hold**. No se marcó pagado, no hubo cobro ni despacho. Total nativo: subtotal S/ 26.90 + envío S/ 15.00 = S/ 41.90, moneda PEN. Direcciones de facturación/envío y documento registrados; HPOS confirmado.

**Brevo NO verificado:** `mkp_mail_enabled=no`, no hay credencial legible en la ruta configurada de esta copia. No se pudieron autenticar eventos ni confirmar delivered. No se habilitó otro transporte, no se inventó credencial/correo y no se enviaron mensajes. Se usó captura temporal privada y de un solo intento para guardar el HTML generado por la transición nativa, sin remitirlo al proveedor. El guard temporal quedó deshabilitado. Un valor interno accepted del sandbox/captura no representa aceptación ni entrega de Brevo.

Capturas originales `correos/admin.html` y `correos/cliente.html`; asuntos y remitente en `correos/capturas.json` (asuntos heredados tenían emojis). Remitente configurado: **noreply@minikids.pe**; su verificación en Brevo no pudo comprobarse. Los originales incluyeron logo, importes, producto, documento y direcciones, pero **faltó teléfono**: la opción heredada de Blocks lo ocultaba también al checkout clásico. Esto es un fallo observado, no un correo final certificado.

Se corrigió el teléfono por filtro de opción limitado al checkout clásico y sus endpoints Ajax nativos. `telefono-corregido-V8.json` comprueba campo requerido, lectura nativa de 999999999, setter en WC_Order sin guardar y ausencia de segundo pedido. No se repitió el checkout exitoso para probar el correo.

Se retiraron de código propio los asuntos/encabezados rígidos, el postprocesado global de HTML y el disparo forzado de Procesando antes de resolver BACS. Ahora mandan las transiciones y ajustes nativos. Se corrigieron opciones de logo/azul/amarillo/fondos/pie/encabezados, con respaldo; editables en WooCommerce → Ajustes → Correos. No se editó ninguna plantilla distribuida de Woo ni pasarela. Se corrigieron además atributos no admitidos del registro heredado del celular.

`correos/admin-vista-corregida.html` y `cliente-vista-corregida.html` son **vistas nuevas en memoria de la instantánea eliminada**, con teléfono corregido. No se guardó un pedido ni se llamó a trigger/wp_mail. El callback BACS que vuelve a buscar el pedido borrado no puede renderizarlo; solo para estas vistas se mostraron instrucciones/cuentas de la configuración mediante un callback auxiliar. Por ello estas vistas no sustituyen una prueba de envío nativo end-to-end. Los tests de contenido comprueban estas vistas, con número histórico #499, nombre/documento/teléfono/dirección/producto/pago/importes/logo/colores y sin emojis.

Asuntos actuales de las vistas nativas: administrador «[MiniKids Peru]: Nuevo pedido: #499»; cliente «¡Recibimos tu pedido de MiniKids Peru!». Remitente noreply@minikids.pe, destinatario propietario. `vistas-corregidas.json` distingue expresamente que no se enviaron. Claude puede capturar estos cuatro HTML; los dos originales sirven para comparar el defecto.

Limpieza por `tools/cleanup-attention-qa.php --v8`, adaptando la herramienta existente al manifiesto y base actuales sin alterar su modo histórico: cancelación nativa, restitución de stock y eliminación del #499. Se conservaron privadamente los logs de QA y se retiraron sus entradas del logger heredado para que no aparezcan como enviados. `limpieza-pedido-V8.json`: mismos 14 IDs iniciales, stock idéntico de los 98 y **cero acciones de correo pendientes** en Action Scheduler. Sin nuevos pedidos de prueba pendientes.

Para completar entrega real hacen falta la credencial/conectividad Brevo de esta copia y autorización de otra prueba si se necesita recrear pedido: **no ejecutar automáticamente el script de pedido**, protegido por journal de intento único. La credencial se configura desde Negocio, fuera de WordPress. No se da V8 por cerrado por este bloqueo.

## Archivos y evidencias

Aplicación escrita únicamente por MCP read-file/write-file con backup=true, relectura exacta, PHP lint antes/después y comprobación JS:

- Nuevos: `mu-plugins/minikids-commerce-v8.php/css/js`.
- Modificados: `minikids-negocio.php` (sección editable), `minikids-commerce-v6.js` (documento en Datos), `minikids-estetica.php` (sin emojis, correo nativo, atributos de celular válidos), `minikids-bloques.css` (sin carrito pictográfico).
- `minikids-qa-v8.php`: helper temporal ahora inerte; no flag público, sin credenciales. Los flags/journals/backups están en `_trabajo/local-private/v8-backups/` y privados adyacentes.

Auxiliares `ToyCommerce/tools/v8-*` documentan aplicación, limpieza CRUD, traslado de atributos, captura, vistas, auditorías y reproducción. Se modificó `tools/cleanup-attention-qa.php` solo para el modo V8 local. Sin tocar core/Blocksy/WooCommerce/pasarelas ni configuración de red/wp-config. Sin despliegue, commit ni publicación.

Verificaciones finales: **dos pasadas 92/92 HTTP/DOM/API/estáticas**, 13/13 VM reloj/loader, 6/6 VM pasos, comprobaciones nativas de teléfono y Settings API. **13.730 archivos protegidos sin diferencias** contra el inventario previo. Las pasadas de correo comprueban vistas corregidas sin envío. `verificacion-V8-1.json`/`-2.json` incluyen límites materiales y lista de inglés muestreado vacía por pantalla. `ajustes-native-V8.json`, `pedido-auditoria-V8.json`, `pedido-controlado-V8.json`, `correo-preflight-V8.json`, `limpieza-pedido-V8.json` conservan resultados separados.

Los intentos auxiliares de vista de un pedido ya eliminado y del bootstrap admin completo por CLI fallaron inicialmente; se acotaron los harnesses y se volvieron a ejecutar. No se presentan esos fallos auxiliares como fallos HTTP del sitio ni se ocultan como verificaciones exitosas. Logs privados preservados.

`brain/STATE.md` y `docs/bugs.md` registran estado abierto/en verificación. Grafo regenerado con el actualizador existente y wrapper secuencial; solo fuentes propias/docs, sin privados ni SQL.

## Lo que queda para Claude y el dueño

Chrome real en seis anchos; fidelidad de layout, foco/teclado de modales, zoom/galería, pasos/refresco Ajax, reloj/retorno reales, editor/guardado de ajustes y cero bloques inválidos. Validación comercial de catálogo y de textos de entrega/devoluciones/garantías. Foto completa/fiel para 41. Instalar idioma oficial cuando vuelva conectividad. Credencial Brevo y prueba real de entrega/correo corregido con autorización adicional al único pedido ya consumido. No hay capturas nuevas de navegador, certificación de accesibilidad ni de rendimiento/CWV.
'''
report=report.replace('__IDS__',', '.join(map(str,sorted(changes['changed_ids'])))).replace('__SAMPLES__',samples)
(OUT/'INFORME_V8.md').write_text(report,encoding='utf8')
state='''## V8 · 2026-11-24 · ficha completa, atención y correo pendientes de cierre real

- Ficha con izquierda galería+descripción+detalles+comentarios y derecha compra, sin sticky/pestañas/chips/promesas; modales nativos dialog y precio especial/regular/descuento. CSS móvil <900. HTTP/DOM sí; visual/foco real de Chrome pendiente.
- 98 productos limpiados por CRUD; cuidados en meta con editor Woo General; edad/contenido de81 demo a atributos nativos. Precios/SKU/stock/imágenes/galerías intactos.12 páginas auditadas, emojis retirados en10/11 y tienda; Mi cuenta titulada en español. Sin certificar datos demo físicos.
-17 campos en Ajustes > MiniKids · Negocio; métodos efectivos BACS/COD, zona Perú estándar15/gratis149, sin recojo; plazos/políticas a validar. Contacto vigente leído de Negocio, distinto al histórico. No tocar pasarelas.
- Idioma Woo oficial sigue bloqueado cURL7 proxy127.0.0.1:9; fallback sin inglés muestreado en8 pantallas HTTP.41 nuevo intento image_gen rechazado por reconstrucción; original conservada.
- Un pedido499 por checkout nativo BACS quedó on-hold; capturas offline cliente/admin a propietario configurado. Mail está desactivado y no hay credencial Brevo legible: NO enviado, NO delivered. Faltó teléfono en captura original; corregido por filtro de checkout clásico, parse/setter nativos sin segundo pedido. Vistas corregidas en memoria no equivalen a envío. Retirado disparo heredado de Procesando y overrides rígidos; opciones/plantillas Woo nativas y estilos propios.
-499 cancelado/eliminado por cleanup-attention-qa.php --v8; mismos14 pedidos, stock98 idéntico, cero acciones email pendientes. Helper QA desactivado. No repetir pedido automáticamente.
- Dos pasadas92/92 HTTP/DOM/API/estáticas,13 VM reloj/loader,6 VM pasos, teléfono y17 campos nativos.13730 protegidos sin diferencias. No Chrome/Playwright/despliegue. INFORME_V8.md y correos/ en diseno_minikids/v5. Pendientes reales: Brevo/foto41/Chrome; no cierre P0–P2.

'''
p=ROOT/'brain/STATE.md';s=p.read_text('utf8');
if not s.startswith('## V8 ·'):p.write_text(state+s,encoding='utf8')
bugs='''

### BUG-V8-LOCAL · P2 · Fase V8 · Estado: En Verificación
- URL: ficha, checkout y Ajustes MiniKids Negocio, localhost8082.
- Corrección: dos columnas completas, modales y contenido nativo, limpieza98, correo nativo editable y teléfono clásico restaurado.
- Verificación: dos pasadas92/92 HTTP/DOM/API;13 reloj/loader y6 pasos VM; PHP lint y13730 protegidos. Informe V8 describe alcance exacto.
- Pendiente: Chrome360/390/768/1024/1280/1440, foco real/galería/layout/flujo/editor. No cerrar por pruebas estáticas.

### BUG-V8-MAIL · P2 · Fase V8 · Estado: Abierto
- Obtenido: mail_enabled=no y sin credencial Brevo de esta copia; ninguna entrega real comprobada. Captura única #499 no incluyó teléfono por opción Blocks heredada.
- Corrección: teléfono clásico restaurado y parse/setter nativos verificados; asuntos/plantillas propios rígidos y procesamiento forzado retirados; vistas corregidas sin guardar/enviar.
- Evidencia: correo-preflight-V8.json, pedido-auditoria-V8.json, telefono-corregido-V8.json, correos/.499 cancelado/eliminado, stock/pedidos restituidos y cero acciones mail pendientes.
- Pendiente: configurar transporte y autorizar nueva prueba si corresponde. No afirmar delivered ni end-to-end de teléfono corregido.

### BUG-V8-FOTO41 · P2 · Fase V8 · Estado: Abierto
- Obtenido: image_gen estricto sigue alterando coche/geométría de maqueta parcial. Intento rechazado; original conservada.
- Evidencia: imagenes-v8/producto-41-intento-v8-rechazado.png.
- Pendiente: fuente que muestre el producto fiel/completo.
'''
p=ROOT/'docs/bugs.md';s=p.read_text('utf8');
if '### BUG-V8-LOCAL' not in s:p.write_text(s+bugs,encoding='utf8')
prompt='Precise conservative background removal EDIT of supplied photo, no generation of replacement objects. Isolate ONLY the visible gray slotted model race track and the exact tiny red car and tiny white/blue car on it. Remove houses, roofs, people, landscape, trees, ground and sky. Preserve the exact camera perspective, geometry, track slots, curves, car shape and wheels and pixels as far as possible. The track is cropped by the photograph edges: preserve those incomplete cut edges, do NOT invent hidden track, perimeter base, complete loop, or any missing parts. Preserve only track actually visible; transparent gaps where houses occlude it, no reconstruction. Center the visible segmented cutout with generous transparent margin. No text, no new toys, no new pieces. Genuine alpha transparency. If a faithful full product cannot be isolated from this cropped source, prefer an obviously incomplete but faithful cutout over invented completion.'
(OUT/'producto-41-V8.json').write_text(json.dumps({'product':41,'tool':'built-in image_gen edit','source':'prod-pista-carreras-hero-principal.jpg','prompt':prompt,'transparent_background_requested':True,'result':'rejected','reason':'Reconstructed white/blue car and road geometry/texture; background unsuitable. Original not reassigned.','file':'imagenes-v8/producto-41-intento-v8-rechazado.png'},ensure_ascii=False,indent=2),encoding='utf8')
print('INFORME_V8.md, STATE and bugs updated with verified scope and unresolved blockers.')
