from pathlib import Path
import json,shutil
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5';BACK=ROOT.parent/'local-private/v9-backups'
before=json.loads((BACK/'native-before.json').read_text('utf8'));after=json.loads((BACK/'native-after.json').read_text('utf8'))
integrity={k:before[k]==after[k] for k in before};assert all(integrity.values()),integrity
(OUT/'integridad-V9.json').write_text(json.dumps({'checks':integrity,'products':len(after['products']),'orders':len(after['orders']),'scope':'Native CRUD read-only snapshots; order data hashed. Private source snapshots outside WordPress.'},indent=2),encoding='utf8')
passes=[json.loads((OUT/f'verificacion-V9-{i}.json').read_text('utf8')) for i in [1,2]]
assert all(p['all_pass'] for p in passes)
report='''# MiniKids V9 · Implementación local y revisión pendiente de Chrome

24 de noviembre de 2026. Destino exclusivo: http://localhost:8082/wordpress/. Se implementaron los puntos 1–5 de ORDEN_CODEX_V9. **No se autocertifica el resultado visual ni el cierre P0–P2**: Claude debe verificarlo en Chrome real. No se usó Chrome, Playwright, otro navegador ni despliegue. No se creó ningún pedido ni se envió correo.

## 1. Carrito compacto

Se retiró del generador PHP propio la caja grande superior, no solo su visibilidad. La barra está en la tabla de Resumen, antes del cupón/Total: fondo gris, camión SVG, diferencia calculada como max(0, 149 − subtotal), importe destacado y progreso de 6 px. Al llegar a 149 muestra «Tienes envío gratis». Woo conserva el cálculo final de cobertura y tarifa; la barra no sustituye sus condiciones de envío.

CSS elimina la fila vacía de avisos, reduce el margen entre título y contenido y conserva la alineación superior del resumen con la tabla. La caja tiene mínimo de 64 px; texto largo puede aumentar su altura. **No se midieron los 32 px ni alturas/ancho computados**.

HTTP comprobó una sola barra dentro de cart_totals y ninguna caja superior. Con osito 278 × 1 (26.90) mostró diferencia 122.10; también se comprobó el estado completo al superar el umbral con productos reales del catálogo en una sesión QA aislada. No se cambiaron precios ni stock.

## 2. Mi bolsa

Se conserva #minikids-cart-drawer, .widget_shopping_cart_content, los eventos Woo y el endpoint de cantidad existente con nonce/validación de stock. Cabecera «Mi bolsa», X a la izquierda; miniatura, nombre limitado a dos líneas, precio, papelera SVG y cantidad. Se retiraron las antiguas franjas y el pie duplicado del PHP propio.

El nuevo pie vive dentro del fragmento nativo: barra compacta, subtotal nativo, nota de cálculo de envío, Comprar al checkout y Ver carrito. El panel usa 100dvh; solo la lista desplaza si desborda y el pie permanece abajo. Los fragmentos actualizan su contenido junto al carrito. HTTP comprobó estructura y enlaces; falta comprobar la posición fija, el scroll y la operación real del drawer, incluidos vacío y muchas líneas.

## 3. Ventana segura y validación de servidor

La causa en código anterior era una presentación progresiva con formulario nativo visible antes del JS y plazo conservado únicamente en sessionStorage. V9 añade mk9-secure-checkout en body_class y CSS crítico en wp_head, antes del body: oculta cabecera/pie/menú/loader general y el formulario hasta mk-secure-ready. Existe aviso estático con Volver al carrito. El formulario se habilita solo tras construir la interfaz y comprobar la sesión contra servidor; con error de red permanece bloqueado. V6 deja de inicializar su checkout cuando V9 está presente, manteniendo sus funciones de carrito/cantidades.

No hay reload ni redirección para entrar en modo seguro. La única sustitución de documento desde V9 ocurre al vencer; al retirar el último producto se vuelve al carrito. La llamada Ajax de estado no es una segunda navegación de documento. **La ausencia del parpadeo se deduce del HTML/CSS y requiere captura de primer pintado para confirmarla**.

El primer GET del checkout con carrito lleno crea mk_secure_start, mk_secure_ttl y mk_secure_token en WC()->session. El plazo normal es 600 s. ?mk6_ttl=15 solo se acepta para una ventana nueva en loopback y local/WP_DEBUG, mediante el guard local existente. Cambiar el parámetro después no cambia el plazo. No se usa sessionStorage como autoridad, ni se necesita su caché visual.

woocommerce_checkout_process añade el aviso exacto «Tu sesión de compra segura venció. Vuelve al carrito para continuar.»; woocommerce_after_checkout_validation vuelve a comprobarlo antes de crear el pedido. Se rechazan inicio ausente, plazo alcanzado y token incorrecto/antiguo. Al vencer se limpian inicio/token y se marca la sesión vencida: recargar checkout no inicia otros diez minutos. Visitar carrito permite una ventana nueva. La creación nativa completada limpia las claves propias por hooks de Woo, sin editar pedidos ni estados.

El filtro nativo rest_request_before_callbacks protege /wc/store/v*/checkout antes del callback que podría crear un borrador; exige sesión vigente y X-MK-Secure-Token. La interfaz vigente usa checkout clásico. Un cliente Store API alternativo necesita ese identificador; sin él recibe 403. No se ejecutó un checkout Store válido ni se creó un borrador.

Checkout responde Cache-Control: no-store, no-cache, must-revalidate, max-age=0 y Vary: Cookie; las respuestas de checkout Store API también reciben no-store/Cookie. Esto no permite afirmar que todos los navegadores excluyan BFCache: pagehide bloquea el formulario y pageshow comprueba plazo y servidor antes de habilitarlo. Al vencer hay aviso y retorno a /cart/?mk-session-expired=1 con carrito intacto. La recarga de un HTML ya vencido también conduce al aviso/retorno.

### Reproducción segura por cURL, sin crear pedido

Estos comandos son para PowerShell, exclusivamente contra localhost. Usan cookies aisladas y dejan deliberadamente vacíos identidad, dirección, pago y Términos. Aunque fallara el guard, no constituyen una compra válida. No completar esos datos ni reenviar un checkout válido.

```powershell
$base = 'http://localhost:8082/wordpress/'
$qaDir = Join-Path $env:TEMP ('minikids-v9-' + [guid]::NewGuid())
New-Item -ItemType Directory -Path $qaDir | Out-Null
$cookies = Join-Path $qaDir 'cookies.txt'
$htmlFile = Join-Path $qaDir 'checkout.html'
curl.exe --noproxy '*' --max-time 30 -sS -c $cookies -d 'product_id=278&quantity=1' ($base + '?wc-ajax=add_to_cart') | Out-Null
curl.exe --noproxy '*' --max-time 30 -sS -b $cookies -c $cookies -o $htmlFile ($base + 'checkout/?mk6_ttl=15')
$checkoutHtml = Get-Content -Raw -Encoding UTF8 $htmlFile
$checkoutNonce = [regex]::Match($checkoutHtml, 'name="woocommerce-process-checkout-nonce" value="([^"]+)"').Groups[1].Value
$secureToken = [regex]::Match($checkoutHtml, 'name="mk_secure_token" value="([^"]+)"').Groups[1].Value
if (!$checkoutNonce -or !$secureToken) { throw 'No se extrajo nonce/token; detener la prueba.' }
Start-Sleep -Seconds 16
curl.exe --noproxy '*' --max-time 30 -sS -b $cookies --data-urlencode "woocommerce-process-checkout-nonce=$checkoutNonce" --data-urlencode "mk_secure_token=$secureToken" ($base + '?wc-ajax=checkout')
```

Esperado: JSON result=failure con el aviso exacto de vencimiento. GET posterior al checkout no debe iniciar otra ventana; GET al carrito conserva productos y muestra aviso. Al volver desde carrito se inicia una ventana nueva de 600 s. Para probar quince segundos otra vez usar otra sesión/cookie jar. tools/v9-verify.py automatiza este equivalente HTTP y prueba además el rechazo 403 Store API con Nonce nativo y token vencido. No registra credenciales. Las respuestas de error quedan en verificacion-V9-1.json/-2.json.

## 4. Diseño de compra segura

Fondo #f3f4f6; logo MiniKids, Compra segura y reloj; columnas 58/42 desde 900 px. Tus datos / Envío / Pago son tarjetas blancas con radio 10, sombra suave, círculo numerado azul, título Barlow y SVG. Una tarjeta abierta; las completadas muestran resumen y Editar. El correo va primero, con explicación y checks SVG; Continuar muestra nombres, apellidos, documento y teléfono en la misma tarjeta. Valida campos obligatorios y correo en español.

Envío contiene dirección, ciudad/distrito, departamento, notas y métodos reales de Woo. No se introduce retiro. Pago conserva los medios disponibles y sus instrucciones nativas, casilla contractual y el único #place_order. El resumen derecho contiene miniaturas/nombres/precio/cantidad/papelera, cupón nativo y totales; las cantidades usan mk5_quantity y la retirada usa Store API de carrito con Nonce, sin inventar pedidos. Los fragmentos update_order_review actualizan productos/progreso/totales y vuelven a situar el pago/métodos en sus tarjetas. El resumen muestra el método seleccionado, sin duplicar controles.

Pie azul marino #0b2b52 con candado, Pago seguro, títulos de medios efectivamente disponibles y copyright 2026. Azul #0756ad, amarillo #ffd60a, Montserrat/Barlow locales. No se copiaron logos ni datos de otra tienda. Por debajo de 900 px hay una columna y un resumen nativo details colapsado arriba. **La apariencia, el flujo real de campos y el comportamiento Ajax interactivo no están certificados sin navegador**.

## 5. Guía de verificación para Claude

1. Preparar carrito lleno; probar 360/390/768/1024/1280/1440 px. En Network conservar log y filtrar Document: una entrada inicial a /checkout/, sin reload ni redirección para activar la ventana. Grabar primer pintado con red/JS lentos: jamás deben verse cabecera normal ni formulario sin modo seguro. Bloquear el JS V9 y luego todo JS: aviso estático y enlace al carrito, nunca campos utilizables.
2. Ventana nueva con ?mk6_ttl=15: editar/recargar/cambiar pestaña no aumenta end. Esperar vencimiento, comprobar aviso y carrito intacto; Atrás y restauración de pestaña no deben reabrir el formulario sin revalidación. Probar también pérdida de conexión del endpoint de estado y una ventana normal de diez minutos. Modificar/borrar sessionStorage no cambia la decisión del servidor. No crear pedido.
3. Ejecutar la prueba cURL anterior; comprobar result=failure/aviso. Comparar pedidos antes/después con CRUD de solo lectura. No enviar un pedido válido ni disparar correo. Store API vencida: 403 mk_secure_expired antes de ejecutar el callback de checkout.
4. Carrito: medir ≤32 px entre título y tabla y alineación superior de Resumen, comprobar caja cercana a 64 px y barra 6 px; sin caja grande arriba. Cantidad/cupón inválido/retiro deben recalcular con Woo; comprobar casos bajo/sobre 149 y vacío. Drawer en tienda: X a izquierda, dos líneas de nombre, cantidades, papelera, pie siempre visible con uno/muchos productos, sin segundo subtotal/pie ni huecos causados por bloques vacíos.
5. Checkout: correo vacío/mal formado bloquea Continuar; segundo Continuar exige datos. Validar dirección y método; una tarjeta abierta y Editar conserva valores. Revisar países/departamentos/autofill/documento/teléfono, términos y pago tras update_checkout, cupón inválido, cantidades y retirada. #place_order único y visible únicamente en Pago abierto. Revisar teclado/foco, zoom, contraste, scroll móvil y resumen colapsable. Detenerse antes de realizar un pedido válido.

## Archivos, respaldo y evidencias

Aplicación exclusivamente en wordpress/wp-content/mu-plugins, por MCP read-file/write-file con backup=true y relectura exacta. Nuevos: minikids-commerce-v9.php/css/js. Modificados: minikids-estetica.php (retirar caja superior y zonas duplicadas del drawer), minikids-commerce-v6.js (ceder checkout a V9) y minikids-commerce-v6.php (etiqueta Ventana segura). No se tocaron core, Blocksy, WooCommerce, pasarelas, plantillas distribuidas, pedidos, estados ni wp-config.

Fuentes espejo en diseno_minikids/v5; originales previos y snapshots privados en _trabajo/local-private/v9-backups, fuera de WordPress/Git/grafo. aplicacion-V9.json conserva recibos MCP. php -l pasó tras cada escritura PHP de aplicación y en auxiliares; JS pasó node --check. Herramientas auxiliares tools/v9-* no son código de aplicación instalado.

Dos pasadas finales HTTP/DOM/código: 42/42 cada una, con vencimiento real de 15 s. server-checks-V9.json: 10 comprobaciones de hooks nativos/sesión en memoria, incluidos límites 600/15 y limpieza. timer-checks-V9.json: 9 casos de funciones reales en VM. pasos-checks-V9.json: 7 casos sintéticos de validación/apertura de tarjetas. **VM no equivale a navegación ni prueba visual**. El ensayo inicial de cantidad 6 excedía el stock 3 del osito: rechazo correcto de Woo; el harness se corrigió a cantidad 2 antes de las pasadas finales. El primer parser auxiliar tampoco aceptaba comentarios sourceURL de WordPress y se corrigió; no se presenta como fallo del sitio.

integridad-V9.json confirma los mismos 14 pedidos (hash de datos nativos sin cambios), datos comerciales de 98 productos, HPOS, ajustes de pasarelas y correo desactivado. Las dos pasadas comparan 13.730 archivos protegidos con el inventario previo sin diferencias. No se enviaron mensajes; no hay credencial Brevo y no se intentó sustituirla. No se modificaron páginas/bloques, medios ni opciones comerciales.

Se actualizan brain/STATE.md y docs/bugs.md y se regenera Graphify con el wrapper secuencial del actualizador existente, sin privados/SQL/terceros. La guía npm modern-web-guidance no estaba disponible en caché offline (ENOTCACHED); no se descargó ni se invocó navegador. Se siguieron las reglas del repositorio y la skill frontend-design con las instrucciones V9 como prioridad. Permanecen pendientes las comprobaciones de Chrome anteriores; V8 conserva sus pendientes de Brevo/foto 41, fuera del alcance de V9.
'''
(OUT/'INFORME_V9.md').write_text(report,encoding='utf8')
state='''## V9 · 2026-11-24 · compra segura de servidor y bolsa; Chrome pendiente

- Puntos 1–5 implementados solo local por MCP backup=true/relectura/lint. Nuevos commerce-v9.php/css/js; ajustes propios estetica.php y commerce-v6.php/js. Carrito sin caja superior, barra compacta en Resumen; drawer Mi bolsa con pie dentro del fragmento.
- Checkout bloqueado desde HTML/CSS crítico hasta ready tras verificación. WC session mk_secure_start/ttl/token, 600 s o hook local15 para ventana nueva; validación checkout_process + after_checkout_validation y guard Store API antes de callback. Token antiguo/vencimiento rechazan, recarga no renueva; pageshow revalida y falla cerrado. No-store/Cookie. Tarjetas Datos/Envío/Pago y resumen/cupón/cantidades nativos; responsive y primer pintado pendientes de Chrome.
- Dos pasadas42/42 HTTP/DOM/código,10 hooks nativos en memoria,9 VM reloj,7 VM pasos;13730 protegidos sin diferencias.98 productos comerciales y14 pedidos/hash iguales, HPOS/pasarelas/mail intactos. No pedidos/correos/navegador/despliegue. INFORME_V9.md contiene reproducción cURL vencida sin compra válida y guía de Chrome. No cierre global P0–P2 ni de pendientes V8.

'''
bugs='''### V9-SEGURIDAD · P1 · En verificación por Claude
- URL: /wordpress/checkout/ y ?mk6_ttl=15 para ventana local nueva.
- Síntoma reportado: checkout normal visible antes de activar ventana; reloj solo cliente evadible.
- Corrección: CSS crítico/clase servidor/failsafe, sesión Woo con plazo/token y guards de checkout clásico/Store API antes de creación; no-store, pageshow y retorno al carrito. Sin reload de entrada.
- Evidencia: dos pasadas42/42 HTTP/DOM,10 comprobaciones nativas en memoria,9 VM reloj y7 VM pasos; mismos14 pedidos/hash y98 datos comerciales. No se creó pedido ni se envió correo. INFORME_V9.md y JSON V9.
- Pendiente: primer pintado/una navegación/BFCache real, comportamiento de tarjetas/Ajax/teclado y responsive en Chrome. No cerrar por las pruebas técnicas.

### V9-CARRITO-BOLSA · P2 · En verificación por Claude
- Corrección: retiro real de caja superior; barra149−subtotal dentro de Resumen; Mi bolsa con zona inferior dentro del fragmento nativo, lista con scroll y cantidades/retirada Woo.
- HTTP comprueba ubicación única, diferencia real, umbral y fragmentos. Pendientes medida≤32px, caja≈64px, alturas, dos líneas, pie fijo, vacío/muchos productos y seis anchos. Sin autocertificación visual.

'''
for relative,prefix in [('brain/STATE.md',state),('docs/bugs.md',bugs)]:
    p=ROOT/relative;backup=BACK/(p.name+'.before');
    if not backup.exists():shutil.copy2(p,backup)
    old=p.read_text('utf8')
    if prefix.splitlines()[0] not in old:p.write_text(prefix+old,encoding='utf8')
print('INFORME_V9.md, STATE, bugs and integrity written. Native snapshots identical.')
