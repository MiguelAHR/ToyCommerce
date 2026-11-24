## V18 · 2026-11-24 · reseñas, tarjetas y responsive; Chrome pendiente

- 135 comentarios `_mk_demo=1` reescritos individualmente con wp_update_comment, previo respaldo en local-private/v5-backups/v18 y comentarios_antes.json. Verificador11/11: textos9–42palabras, sin duplicados ni secuencias de7palabras en más de2reseñas; IDs/autores/fechas/rating/verified/metadatos y promedios/recuentos35productos idénticos. Woo actualizó automáticamente date_modified de esos35productos; reconstrucción SHA-256 demuestra que es la única diferencia de los campos comerciales auditados, sin guardar productos en el diagnóstico.
- Solo tres mu-plugins V18 nuevos por MCP backup=true/relectura: reserva de valoración18px incluso sin reseñas, precio44px/dos líneas y acciones al fondo; compra apilada en tarjetas menores de240px para objetivos44px. Aviso checkout44px previsto, separación18/16px, icono18/texto14/borde3. Correcciones de cabecera, galería horizontal, carrito apilado/barra fija, bolsa viewport-48, modales/teclado visualViewport y pie. Medidas y overflow reales no verificados.
- Dos pasadas HTTP501/501 con1y3productos,135textos servidos y limpieza de carritos; regresión ventana segura29/29 dos veces, Node10/10 viewport y16aserciones pasos; php-l/node-check correctos.13732protegidos y138mu previos idénticos;14pedidos/2usuarios/opciones auditadas conservados. No navegador, pedidos nuevos, correos ni despliegue. INFORME_V18.md incluye10antes/después,20reseñas y matriz360/390/430/768/1024 pendiente de Claude. Sin cierre P0–P2.

## V17 · 2026-11-24 · diseño del checkout; Chrome pendiente

- Checkout seguro: aviso azul con usuario SVG hacia modal existente; borrador de un uso tras éxito de login. Correo primero y revelado de datos; candados/aria-disabled, checks y Editar con invalidación dependiente. Cabecera en pastilla y resumen agrupado en productos/cupón y totales; sticky escritorio, acordeón con total en móvil hasta768.
- MCP read/write backup=true y relectura; PHP lint/node check OK. Core/terceros13732 y minikids-commerce-v16.* sin diferencias SHA-256. Sin pedidos, correos, navegador ni despliegue. Dos pasadas HTTP29/29, en verificacion-V17-1.json y verificacion-V17-2.json; transiciones Node OK. INFORME_V17.md detalla alcance y reproducción; Chrome/login real/restauración de campos/medidas pendientes, sin cierre P0–P2.

## V14 · 2026-11-24 · retoques locales; Chrome pendiente

- Retiradas estrellas doradas del pseudo de estado vacío en origen; promedio vacío conserva estrellas vacías y renderer nativo para promedio real. Títulos Comentarios/Escribe tu comentario comparten Barlow28, interlínea/márgenes y reinicio de posición del formulario; línea base pendiente de Chrome.
- Causa de barra ausente: dependencia V9 de V8 no registrada en carrito; ahora depende de V6 y carga su CSS. Mismo mk9_progress y CSS compartido bolsa/Resumen, pista6px gris, azul proporcional subtotal149 y completa al alcanzar umbral. Retirados overrides V13 de4px.
- Un filtro final de eliminación devuelve exactamente un enlace nativo por resultado; retirado filtro redundante. HTTP cuenta toda cart_item y prueba nonce/eliminar/total/progreso restante. Duplicado previo no reproducido en muestra local; no se oculta control adicional.
- Dos pasadas136/136 HTTP/HTML/declaraciones y dos regresiones51/51.13732 protegidos,14pedidos/hash,99productos y opciones auditadas iguales. MCP backup=true/relectura y PHP lint tras escritura; backups privados v14. Registro Woo sigue no, enlace oculto; NO login exitoso. Sin navegador/pedidos nuevos/correos/despliegue ni cierre P0–P2. INFORME_V14.md contiene reproducción y límites; modern-web-guidance offline ENOTCACHED.

## V13 · 2026-11-24 · pulido de tarjetas, comentarios, acceso y carrito; Chrome pendiente

- Solo localhost8082 y mu-plugins, MCP backup=true/relectura y php -l tras cada PHP. Sin navegador, pedidos, correos ni despliegue. INFORME_V13.md detalla archivos, reproducción y límites; verificacion-V13-* e integridad-V13.json contienen evidencia técnica.
- Retirado pseudo OFERTA y tag dentro right8/top10; orden común de tarjeta y grid45/55 de stepper/AGREGAR40px. Comentarios conserva sección fuera de columnas y usa #reviews como único grid45/55, resumen/lista izquierda y formulario nativo derecha, apilado móvil.
- Login con azul#0756ad específico, subtítulos por vista, fallback Ir a Mi cuenta y límites de viewport; registro nativo sigue no, sin cambiar opciones. Recuperación/registro existentes conservados, no enviados; éxito de login y registro habilitado no probados.
- Papelera: retirada máscara adicional en origen, enlace/SVG nativo y aria Eliminar producto; progreso de bolsa presentado en grid compacto64px y barra completa en Resumen; Envío con nombre/costo y destino/calculadora nativos ordenados. Medidas/recorte/foco/Ajax visual y regresión real pendientes de Claude; no cierre P0–P2.
- Dos pasadas HTTP114/114, regresión V11 51/51, PHP lint/node check correctos.13732 protegidos,14 pedidos/hash, opciones y99productos sin diferencias. Respaldos privados archivados; Graphify regenerado con wrapper secuencial, salida0.

## V12 · 2026-11-24 · comentarios, tarjeta común y acceso; Chrome pendiente

- Solo localhost8082; aplicación mu-plugins por MCP backup=true/relectura, php -l por PHP y node --check. Comentarios fuera de columnas antes de recomendaciones, 40/60; Descripción/Detalles a izquierda. Tarjeta Woo común con tag rojo redondeado - N %, stepper/AGREGAR/SVG y AJAX; portada renderiza los enlaces editables de sus cinco carruseles como tarjetas Woo sin cambiar bloques guardados.
- Dialog invitado de acceso con fallback /mi-cuenta/ hacia página nativa /my-account/, vistas/login WP nonce+wp_signon, hooks Woo/errores genéricos/no-store/honeypot; registro nativo deshabilitado (no se cambió). Recuperación native retrieve_password implementada, no enviada ni probada con identificador; no usuario creado/login correcto probado. Sin botones sociales.
- Foto única de Blocksy cubierta por CSS contain/480/aspect-ratio, variantes Flexy; títulos de pago limpios, costos reales de zonas15/gratis149, plazo editable sin provisional público, nota de oferta tras botón. No certificar geometría ni foto2: no publicado con exactamente2 imágenes declaradas.
- HTTP83/83 dos pasadas; regresión V11 51/51 y VM13modales/9contador/7pasos/6watchdog.13732 protegidos idénticos,14pedidos/hash y opciones iguales;99 productos comerciales no editados, solo caché nativa Blocksy styles_descriptor vacía v8 generada al consultar458. INFORME_V12.md y datos/integridad-V12.json detallan límites. Sin navegador/pedidos/correo/despliegue ni cierre P0–P2; Claude verifica Chrome y éxito de login controlado.

## V11 · 2026-11-24 · bolsa, carrito y primer estado de checkout; Chrome pendiente

- Solo mu-plugins propios por MCP backup=true/relectura y php -l tras cada PHP. Bolsa usa selección de plantilla nativa Woo (Blocksy añadía product-data y rompía el grid esperado), fila72/texto flexible/controles100, nombre2 líneas. Papelera única accesible y pie conservado.
- Carrito: envío/progreso compactos, cupón en fila, resumen con menos padding; vaciado/relacionados tras tabla en columna izquierda para ocupar el hueco, porcentajes pequeños; vaciado delegado y carrusel reinicializado tras Ajax. Metas520/120/64px implementadas, no medidas.
- Checkout: esqueleto neutro, fallo CSS tras4s y clase failed por watchdog JS si falta ready; CSS crítico sigue cerrado. Candado, sombra/radio12 del resumen, sufijo prueba local retirado solo de presentación pública, sin opciones/pasarelas editadas.
- Dos pasadas HTTP52/52, watchdogVM6, regresiones VM13modales/9contador/7pasos.99 productos comerciales y14 pedidos/hash intactos, salvo caché nativa Blocksy de estilos del430 al consultar HTTP;13732 protegidos sin diferencias. INFORME_V11.md contiene reproducción y límites. Sin navegador/pedidos/correo/despliegue ni cierre P0–P2; Claude debe verificar en Chrome.

## V10 · 2026-11-24 · ficha y modales; Chrome pendiente

- Cambios solo commerce-v8.php/css/js por MCP backup=true, relectura y php -l tras PHP. Causa modal comprobada por código/HTML: JS en footer20 antes de dialogs footer60; getElementById null impide registrar clics. Sin evidencia de reemplazo de enlaces por V5/V6. Delegación document, resolución tardía, showModal try/catch y overlay fallback, Esc/X/backdrop, ciclo de foco/scroll y pagehide/pageshow.
- Una miga derecha por filtro nativo del hero; una cabecera Comentarios con resumen y reseñas Woo; precio normal y etiqueta en fila, oferta tres filas; disponibilidad amarillo#ffd60a y compra azul#0756ad; caja2 columnas con3 SVG; cuidados separados28px. Visual pendiente de Chrome.
- CRUD99 productos (98 publicados+1 borrador): edades135/133/95/89/42 normalizadas y relectura sin patrón malformado. Precios/stock/SKU/fotos y14 pedidos/hash intactos; HPOS/pasarelas/correo iguales.13.732 protegidos sin diferencias. No pedidos/correos/navegador/despliegue. INFORME_V10.md y evidencias técnicas, sin cierre P0–P2; Claude debe probar interacción y diseño en Chrome real.

## V9 · 2026-11-24 · compra segura de servidor y bolsa; Chrome pendiente

- Puntos 1–5 implementados solo local por MCP backup=true/relectura/lint. Nuevos commerce-v9.php/css/js; ajustes propios estetica.php y commerce-v6.php/js. Carrito sin caja superior, barra compacta en Resumen; drawer Mi bolsa con pie dentro del fragmento.
- Checkout bloqueado desde HTML/CSS crítico hasta ready tras verificación. WC session mk_secure_start/ttl/token, 600 s o hook local15 para ventana nueva; validación checkout_process + after_checkout_validation y guard Store API antes de callback. Token antiguo/vencimiento rechazan, recarga no renueva; pageshow revalida y falla cerrado. No-store/Cookie. Tarjetas Datos/Envío/Pago y resumen/cupón/cantidades nativos; responsive y primer pintado pendientes de Chrome.
- Dos pasadas42/42 HTTP/DOM/código,10 hooks nativos en memoria,9 VM reloj,7 VM pasos;13730 protegidos sin diferencias.98 productos comerciales y14 pedidos/hash iguales, HPOS/pasarelas/mail intactos. No pedidos/correos/navegador/despliegue. INFORME_V9.md contiene reproducción cURL vencida sin compra válida y guía de Chrome. No cierre global P0–P2 ni de pendientes V8.

## V8 · 2026-11-24 · ficha completa, atención y correo pendientes de cierre real

- Ficha con izquierda galería+descripción+detalles+comentarios y derecha compra, sin sticky/pestañas/chips/promesas; modales nativos dialog y precio especial/regular/descuento. CSS móvil <900. HTTP/DOM sí; visual/foco real de Chrome pendiente.
- 98 productos limpiados por CRUD; cuidados en meta con editor Woo General; edad/contenido de81 demo a atributos nativos. Precios/SKU/stock/imágenes/galerías intactos.12 páginas auditadas, emojis retirados en10/11 y tienda; Mi cuenta titulada en español. Sin certificar datos demo físicos.
-17 campos en Ajustes > MiniKids · Negocio; métodos efectivos BACS/COD, zona Perú estándar15/gratis149, sin recojo; plazos/políticas a validar. Contacto vigente leído de Negocio, distinto al histórico. No tocar pasarelas.
- Idioma Woo oficial sigue bloqueado cURL7 proxy127.0.0.1:9; fallback sin inglés muestreado en8 pantallas HTTP.41 nuevo intento image_gen rechazado por reconstrucción; original conservada.
- Un pedido499 por checkout nativo BACS quedó on-hold; capturas offline cliente/admin a propietario configurado. Mail está desactivado y no hay credencial Brevo legible: NO enviado, NO delivered. Faltó teléfono en captura original; corregido por filtro de checkout clásico, parse/setter nativos sin segundo pedido. Vistas corregidas en memoria no equivalen a envío. Retirado disparo heredado de Procesando y overrides rígidos; opciones/plantillas Woo nativas y estilos propios.
-499 cancelado/eliminado por cleanup-attention-qa.php --v8; mismos14 pedidos, stock98 idéntico, cero acciones email pendientes. Helper QA desactivado. No repetir pedido automáticamente.
- Dos pasadas92/92 HTTP/DOM/API/estáticas,13 VM reloj/loader,6 VM pasos, teléfono y17 campos nativos.13730 protegidos sin diferencias. No Chrome/Playwright/despliegue. INFORME_V8.md y correos/ en diseno_minikids/v5. Pendientes reales: Brevo/foto41/Chrome; no cierre P0–P2.

## V7 · 2026-11-24 · correcciones locales; Chrome y dos pendientes materiales

- Loader V7 con rebote/pulso de opacidad en reduced motion, excepción específica a animation:none!important global, salida por load/pageshow/visibilitychange y transición/failsafe CSS 2.5 s; navegación interna rápida sin velo. Debug ?mk6_loader_debug=1 limitado a loopback + local/WP_DEBUG. No getAnimations ni capturas ejecutadas aquí.
- Ventana segura con límite absoluto de 600000 ms; actividad/recarga no extiende end. Clave mk6-secure-window JSON {version:7,start,end,activity}, epoch ms. Hook local ?mk6_ttl=15, coerción de escalar localizado a número, comprobación al volver a pestaña, aviso antes de retorno al carrito intacto. Lógica verificada en 13 casos VM; reloj/UI real pendiente.
- Ocho ediciones image_gen con originales; siete asignadas por CRUD Woo, medios 492–498, originales intactas como primera imagen de galería; 39/Bloques solo juguete en principal. 41 rechazado porque completa base/perímetro fuera de fuente recortada: requiere foto fiel. Fuentes 44/40 no certifican un juguete infantil, discrepancia documentada.
- Galería sticky top24 desde1024; apilado sin sticky debajo. Carrito oculta fila actions visual vacía conservando controles/nonces y reinicia ancho/inset de relacionados. Layouts pendientes de medición por Claude.
- Core es_PE/es_ES ya instalado, WPLANG es_PE confirmado por API nativa. Woo oficial bloqueado: cURL7 contra proxy local127.0.0.1:9. Fallback propio de291 entradas/contexto/plurales; auditoría de literales simples en cart/checkout/myaccount/emails sin pendientes en su alcance y asuntos predeterminados en español. Sin envío de correos ni creación de pedido; no garantía de todos los estados dinámicos.
- Aplicación solo mu-plugins mediante MCP backup=true, relectura y php -l; siete fotos vía API de medios/CRUD. Datos comerciales, HPOS/BACS/COD y14 pedidos sin cambios; inventario protegido13730 sin diferencias. Fuentes/evidencias y reproducción en diseno_minikids/v5/INFORME_V7.md. No Chrome/Playwright ni despliegue. No declarar cierre visual P0–P2.

## V6 · 2026-11-24 · correcciones locales; revisión de Claude pendiente

- Aplicados primero 9–14 y después correcciones/diagnóstico 1–8: ficha superior de dos columnas, histograma dentro de Comentarios, recomendaciones compartidas ficha/carrito, cupón en Resumen y cantidad automática nativa, checkout clásico nativo progresivo por pasos con reloj de 600 s y loader amarillo con logo WebP.
- Código propio nuevo minikids-commerce-v6.php/css/js y ajustes V5/estetica/drawer/atencion por MCP read/write, backup=true, relectura y php -l; core/terceros 13.730 sin diferencias. Bloques guardados de carrito/checkout conservados. Pie 219 actualizado por MCP; Términos 482 por API nativa, marcado borrador local a validar por el dueño, opción/casillas/enlaces configurados.
- Nueve extracciones V3 revisadas sobre blanco/oscuro, nuevas versiones WebP Medios 483–491 por API de medios/CRUD Woo; originales en galería y SHA-256 conservado. Camión 91 rechazado por restos claros. Ocho fotos pendientes: 136/134/91/44/43/41/40/39; rembg/modelo no disponibles offline.
- Dos pasadas finales HTTP/API de 52/52 comprueban catálogo 98, cantidades/rechazos/retirada, cupón inválido en español, recálculo nativo de checkout y retorno de vencimiento con carrito intacto. Sin envío de acción de crear pedido ni correo; 14 pedidos sin cambios, HPOS activo y BACS/COD conservados. La comprobación GET de retorno no ejecuta el reloj JS.
- API/descarga oficial de idioma bloqueada por red: fallback gettext/contexto e Intl. Traducción exhaustiva de errores/correos pendiente. Se conservaron los handlers y ajustes de correo existentes.
- INFORME_V6.md contiene archivos, evidencias, URLs y pendientes. Sin Chrome/Playwright; Claude debe comprobar layouts 360/390/768/1024/1280/1440, pasos/validación, reloj/recarga, loader, cantidades/cupón, drawer y editor Gutenberg. No declarar cierre visual/funcional ni desplegar.

## V5 B–E · 2026-11-23 · implementación local; revisión de Claude pendiente

- Catálogo 98, precios/SKU/stock conservados; cinco columnas >1280, filtros izquierda con datos reales; ficha Flexy vertical, zoom de respaldo, SKU/stock/ayuda/valoraciones y COMPLETA TU PEDIDO. Shortcode de tienda sin el límite heredado de 50.
- minikids-commerce-v5.php/css/js y ajuste de minikids-retail.js por MCP read/write con backup=true, relectura y php -l. Drawer conserva fragmentos y eventos; endpoint propio de cantidad con nonce/stock validado.
- Carrito guardado como bloque y presentado con shortcode Woo nativo mediante filtro propio (Producto/Precio/Cantidad/Subtotal), seleccionando la plantilla distribuida de Woo sin editarla; checkout de bloques, relacionados debajo del carrito, vaciado por Store API, etiquetas mediante extensión, navegación Datos/Entrega/Pago sin ocultar campos ni alterar pedidos/pagos. Pie 219 actualizado por MCP tras lectura y respaldo; títulos 34/35 en español sin cambiar bloques.
- HTTP/API comprobó añadir/cantidad/rechazos/retirada, productos y enlaces; core/terceros 13.730 sin diferencias. HPOS activo, BACS/COD disponibles por filtro local; no POST de checkout/pedidos/correo/despliegue. No afirmar navegador, editor ni responsive.
- Pendiente de D: no casilla contractual porque no hay página de Términos configurada. Modal email y compartir omitidos. Claude debe verificar seis anchos, Flexy/zoom, filtros, drawer y React checkout. INFORME_BCDE.md contiene archivos, evidencias, URLs y límites.

## FASE A V5 · 2026-11-23 · entrega HTTP; QA visual/identidad pendiente

## V5 · Fases A y P locales · catálogo demo de 98 publicados

- Destino actual verificado por wp-load: base local `minikids_t3_revision_20261111`, conexión loopback, home `http://localhost:8082/wordpress/`. El nombre minikeds_dev2 de las entradas históricas no identifica esta copia de revisión. No se renombró ni migró ninguna base.
- A medida por curl/inventario, con límites explícitos: tienda HTML <3 s, menor peso de recursos, estáticos rápidos; PHP todavía >1 s. Router rápido activo; Apache multihilo preparado pero sin sustituir el listener por restricciones del entorno. Sin Chrome/Playwright, sin certificación visual/editor/CWV.
- P: 81 nuevos mediante CRUD WooCommerce, SKU MK-018 a MK-098 y meta _mk_demo=1. Total 98 publicados, 14 por cada categoría existente, 35 ofertas totales, 20 nuevas galerías de 2 imágenes. Los 17 publicados anteriores y el borrador 212 conservan sus campos. Otros 22 medios de galería recuperaron derivados ausentes, con originales SHA-256 intactos.
- 121 imágenes demo seleccionadas (81 principales + 40 galerías), PNG con alfa y variantes 1200/600, WebP calidad 82; hojas de contacto sobre blanco/oscuro. Prompts, fuentes y lista de IDs/URLs en diseno_minikids/v5. Producto físico, edades, peso, medidas y cotizaciones no certificados: datos demo señalados, pendientes del dueño. No sustituir las siete fotos heredadas incongruentes sin fuente confirmada; personaje heredado ID 85 conservado por la orden vigente.
- Nuevo filtro/checkbox de Productos en minikids-demo-catalogo.php, aplicado/releído por MCP con backup=true. Consulta nativa de 81 demo / 17 originales; dos pasadas CRUD/HTTP e imágenes decodificadas; Ajax agregar/retirar en sesión aislada. No se solicitó creación de pedidos ni envío de correo. No SQL ni cambios de terceros/pasarelas.
- INFORME_A.md, INFORME_P.md, catalogo_demo.json y catalogo_contacto.jpg son la entrega local. Respaldos y journal fuera de WordPress en local-private/v5-backups. No despliegue. B–E no iniciadas.


- Router estático rápido, WebP 82, WOFF2 Montserrat/Barlow locales, dimensiones/lazy/srcset y carga condicional de carrusel/CSS propio. Apache aislado validado en sintaxis; no se pudo detener el listener 8082 por permisos, por lo que sigue PHP de un hilo sin reinicio.
- Siete miniaturas JPG/WebP recuperadas por API nativa, 200 directo/MIME/decodificación en dos pasadas; medios 166/169/172/175/178/181/184. Derivados de galería 173/174 también reparados. Fuentes originales conservadas; no nuevas fotos ni cambios de SKU/precio/stock.
- Persisten identidad fotográfica sin confirmar y recortes: karaoke/memoria/pista presentan incongruencias. Subset de cmap latino; contornos completos. No certificar cierre visual/Gutenberg/CWV.
- Mediciones autoritativas: `_trabajo/diseno_minikids/v5/rendimiento.json`, etapas valido/valido_segunda; las etapas intermedias conservan una regresión src vacío ya corregida. Informe y verificación en v5/INFORME_A.md y verificacion-final.json. Recursos estáticos <1 s, HTML ~1.4 s. Ajax add/remove verificado sin pedidos; terceros/core 13732 sin diferencias. Solo A; B–E no ejecutadas.

# Estado minikeds_dev2

## V4 aplicada localmente · 2026-11-23 · QA visual/editor pendiente

- 29 PNG existentes subidos por REST nativa, alt del manifiesto y hashes/dimensiones comprobados. IDs 246–274; listado en `_trabajo/diseno_minikids/v4/medios.json` e INFORME.md.
- Portada 8 aplicada por MCP con bloques Grupo/Imagen/Encabezado: tres slides de cuatro paneles, tres promos y cinco secciones con banner y seis tarjetas. 50 imágenes enlazadas; pie 219 conservado.
- CSS/JS V4 añadidos a retail por MCP con backup=true, fuente Barlow local embebida y ancho raíz explícito. PHP solo cambia versión a 4.0.0; php -l OK. Respaldo actualizado y respuestas en v4/aplicacion.json.
- Dos pasadas HTTP/estáticas OK: anuncios/recursos/destinos y 17 productos con precios reales. Integridad core/terceros sin diferencias. Evidencia en v4/verificacion-aplicacion.json.
- Sin navegador ni nuevos renders. No se certifican cero bloques inválidos Gutenberg, responsive, hover ni interacción del carrito. Claude toma capturas y verifica editor/QA. Aprobación de imágenes comunicada por el usuario. Fase 1 sin cierre visual; Fase 2 no iniciada; sin despliegue ni pedidos.

## Ajuste de proporciones y tipografía del escaparate · 2026-11-23

- Segunda versión local aplicada por MCP: cabecera en una fila, carrusel panorámico de tres campañas y tres promociones compactas. Colores y logo originales conservados; portada/pie continúan siendo bloques Gutenberg válidos.
- Comparación real a 1440 px: aviso 46 px, cabecera 75 px, hero 1440 × 371.52 px; promociones x=112, y=562.52, ancho 1216 y alto 152 px. La referencia tiene hero de 371.36 px y primer título a y=758.25; el nuevo título está a y=758.52. Roboto y Rajdhani alojadas localmente con sus licencias.
- Nueva composición promocional transparente en Medios, ID 224. Textos y botones separados de las imágenes y editables manualmente. Navegación con botones, indicadores, teclado y desplazamiento táctil, sin avance automático.
- Corregidas limitación heredada de ancho del carrusel, tipografía heredada de tarjetas, contraste de envío y desaparición de imágenes por composición con mezcla de colores. Dos pasadas finales en seis anchos: 12 vistas sin desbordes ni imágenes rotas; 8 comprobaciones funcionales correctas. Editor de portada/pie sin bloques inválidos; sintaxis PHP y Store API correctas. No se enviaron pedidos.
- Entrega actualizada en MINIKIDS_REDISENO y su ZIP, fuera del repositorio. Continúa solo en local, puerto 8082.

## Rediseño comercial local · 2026-11-23

- Nueva presentación inspirada en la organización comercial de Tai Loy, conservando logo y paleta MiniKids. Solo local, en la base aislada de revisión y puerto 8082; no publicada en el VPS.
- MCP local real conectado con autenticación nueva fuera de la raíz web. Portada ID 8 y bloque de pie ID 219 leídos/actualizados por abilities; archivos propios `minikids-retail.php/css/js` escritos y releídos mediante MCP, con respaldo obligatorio. Medios creados por API nativa.
- Tres imágenes promocionales nuevas, fondos sin texto, en Medios. Títulos, párrafos, enlaces y botones editables como bloques nativos. Acceso Apariencia → MiniKids · Diseño; menú original conservado y cabecera configurable desde Personalizar.
- Reparada la variable de configuración del buscador y su enlace al catálogo canónico `/tienda/`. Fragmentos WooCommerce conservados para contador/carrito. No se tocaron terceros.
- Dos pasadas en seis anchos 360/390/768/1024/1280/1440: sin desbordes ni imágenes rotas en la nueva portada. Búsqueda, categorías, añadido, carrito y paso al checkout comprobados; sin crear pedidos. Gutenberg abrió portada/pie con cero bloques inválidos.
- Evidencias y exportaciones en `_trabajo/diseno_minikids` del espacio de trabajo padre. Manual en docs/REDISENO-TIENDA-EDITABLE.md. La foto ausente del juego de memoria no se reemplazó por una incorrecta; ese artículo se excluye de la nueva selección de portada.
- Para producción: respaldar y transferir únicamente medios, bloques y código propio; nunca importar la base local ni copiar credenciales. Historial publicado anterior a continuación.

## Publicación completada: 2026-10-20

- Push inicial completado en MiguelAHR/ToyCommerce main, commit7e3a3aa. Publicación expresa del usuario reemplaza la restricción histórica de solo local para este despliegue.
- Dominio vigente de esta versión: https://minikids.vitorstream.shop/; admin /wp-admin/. Local conserva http://localhost:8082/wordpress/ y su DB minikeds_dev2.
- VPS: current apunta a releases/toycommerce-20261020; DB nueva minikeds_dev2_prod; versión anterior previous-20261020 y DB minikids_dev intactas. Respaldos completos antes/después y cron actualizados. Nunca reimportar la copia local sobre producción con pedidos nuevos.
- Credenciales de producción y MCP nuevas y privadas, acceso local sin cambios. Copia de acceso solo local-private/access-production.txt. Configuración y Brevo fuera de raíz web; credencial Brevo editable desde Negocio. Solo BACS/COD habilitados, HPOS activo,17 productos, páginas nuevas y correo activado.
- Portabilidad corregida vía MCP con backup: pie usa home_url y buscador recibe shopUrl localizado. Migración DB serializada con WP-CLI; no cambios en terceros. Primer cambio detectó permiso del directorio privado y restauró automáticamente el sitio anterior; corregido chmod750 antes de activación definitiva.
- Dos pasadas HTTP públicas48/48; navegador68/68, más14/14 comprobaciones de logo/fotos y ficha móvil tras corregir recursos CSS. MCP real autenticado y Gutenberg de ambas páginas probados; proveedor Brevo cuenta200 (sin enviar correos adicionales). Sitios vecinos conservan respuestas200/307/200. Resultados en brain/production-*-checks.json; evidencias solo privadas. Integridad13729 archivos terceros/core sin diferencias; tres respaldos/metadatos Git excluidos del paquete.

## Actualización vigente: MCP y atención (2026-10-19)

- Pull fast-forward 7fed1d5→837216e, conservando configuración privada, administrador y DB local. Leídas guía MCP, guía del proyecto y11 skills; AGENTS.md las establece como norma permanente. No se reimportó SQL. Puerto vigente8082/subcarpeta wordpress, aunque docs históricas dicen8088.
- Proyecto anterior minkids_dev: únicamente docs/IMPLEMENTACION-MCP-LOGICA-Y-DISENO.md y memoria actualizadas. No se implementó esta arquitectura allí ni se cambió su web/VPS.
- MCP Adapter real probado con credencial de aplicación local fuera de código, handshake/Mcp-Session-Id y8 abilities. tools/mcp_local.py es el cliente. Páginas/ajustes modificados mediante abilities y archivos propios mediante write-file con backup=true. Escritura MCP ahora rechaza terceros y desactivar backup. Proxy legacy wordpress-mcp desactivado por warnings; adapter principal activo.
- Nuevos módulos propios en mu-plugins: minikids-negocio.php, minikids-correo.php, minikids-reclamaciones.php y minikids-atencion.css/js. Negocio/correo editables en Ajustes > MiniKids · Negocio; correo usa plantillas nativas Woo editables en Ajustes > Correos. Cobros externos siguen bloqueados. Excepción al historial: el correo local ya está activado explícitamente con Brevo.
- Proveedor confirmado previamente: MIMI KIDS STORE E.I.R.L., RUC20609651530, Av. Alfredo Mendiola3698, Independencia, Lima, Perú; vektronova@gmail.com y WhatsApp +51991117308. Clave Brevo reutilizada por canal privado, solo local-private/brevo.json. Nunca registrar su valor en documentos/grafo ni opciones accesibles por MCP.
- Quiénes somos: página10 /nosotros/, bloques nativos, enlace en menú principal. Libro: página143 /libro-de-reclamaciones/, introducción en bloques y shortcode dinámico. Privacidad: página3 publicada. Menú nativo Atención y empresa (ID31) en pie. Todo contenido editorial editable por humanos en Páginas/Menús.
- Reclamaciones: CPT mkp_claim privado/no REST público, nonce, idempotencia, validaciones, menor/representante, folio, constancia imprimible/PDF, copia cliente/proveedor y respuesta/reenvío desde WooCommerce > Reclamaciones. No es integración de reporte automático a Indecopi. Plazo15 días hábiles verificado en fuente oficial.
- Resumen compra: correos nativos customer_on_hold_order/procesando/completado y new_order para admin; aceptación guardada en metadatos propios del pedido. Dos mensajes reales QA a la cuenta del propietario: Brevo confirmó delivered para ambos. Los tests funcionales usan sandbox temporal privado, nunca parámetro HTTP.
- Dos pasadas HTTP/Store API completas aprobadas, incluidas compra HPOS, celular y correos cliente/admin. Dos pasadas visuales360/390/768/1024/1280/1440 sin desborde en Nosotros/Libro; Gutenberg válido, panel/credencial oculta y respuesta humana comprobados. Evidencias wp-content/qa-evidencia (bloqueadas por router), resultados brain/*checks.json. QA se cancela/elimina con tools/cleanup-attention-qa.php para restituir stock.
- Detectados y corregidos en código propio: atributos no admitidos del celular y estado vacío del buscador que no se inyectaba después de do_blocks. Drawer Ajax y fragmentos conservados. Sin cambios en núcleo/Blocksy/Woo ni demás terceros. Baseline13747 archivos y SQL previo privados. Docs/ATENCION-CORREO-Y-EDICION.md contiene flujo y pruebas.
- Cierre comprobado:28/28 pruebas HTTP,90/90 de navegador (incluye regresiones), respuesta administrativa en sandbox y2/2 correos reales delivered. Cinco pedidos QA151/153/154/156/158 cancelados; cuatro hojas QA150/152/155/157 eliminadas, límites de envío de QA restablecidos. Graphify60 nodos/52 relaciones, regeneración Windows corregida con guarda main. No hubo push ni despliegue de estos cambios.

## Historial previo (sustituido por el estado vigente donde corresponda)

2026-10-18. ToyCommerce clonado de MiguelAHR/ToyCommerce a C:/xampp/htdocs/minikeds_dev2. Proyecto separado y solo local; no tocar Mini Kids anterior ni VPS.

Servidor PHP XAMPP8.2 en localhost8082, documento raíz del proyecto y tools/router-local.php. Sitio http://localhost:8082/wordpress/; admin /wordpress/wp-admin/, usuario admin. Contraseña nueva y configuración MySQL exclusivamente en local-private; nunca documentar claves.

Base minikeds_dev2, 52 tablas de database/wordpress-sanitized.sql, 17 productos publicados, tema Blocksy. URLs migradas de localhost8088/wordpress a localhost8082/wordpress respetando serialización. Se conserva subcarpeta por enlaces originales codificados. Código base/terceros intacto; wp-config.php solo configuración y mu-plugin nuevo minikeds-dev2-local.php suprime correo y cobros externos.

Portada,16 imágenes sin errores, catálogo, ficha y login administrativo comprobados. Carrito de bloques comprobado en Chrome con un artículo y totales/envío; checkout HTTP200. No se creó pedido ni se cobró. El carrito se renderiza con JavaScript: buscar cart_item solo en HTML inicial no sirve como validación. Router protege SQL/configuración privada. Arrancar solo con tools/start-local.ps1 y MySQL activo; no reimportar DB ni repetir preparación sobre cambios. Docs/EJECUCION-LOCAL.md contiene el flujo. No se publicaron cambios en ningún repositorio ni servidor.

Grafo solo código propio en brain/graphify-out/graph.json. Regenerar con C:/Python314/python.exe tools/update-brain.py. No indexar SQL, local-private, wp-config, código base ni terceros.

Credencial del administrador local actualizada y verificada por indicación del usuario; valor solo en local-private/access-local.txt. No se modificó la cuenta del proyecto anterior ni producción.
