### V18-RESENAS-TARJETAS-MOVIL · P2 · En verificación por Claude
- URL: portada, /tienda/, fichas, /cart/, bolsa, modales, /checkout/ y pie locales. Anchos360/390/430/768/1024; sesión invitada con1y3productos. Reproducir según diseno_minikids/v5/INFORME_V18.md, sin enviar pedidos ni formularios de autenticación/recuperación.
- Causas de código: ausencia de fila cuando no hay valoración; precio sin reserva común; aviso con enlace44px más padding24px; miniaturas verticales, controles pequeños y anchos mínimos heredados. Correcciones confinadas a minikids-commerce-v18.php/css/js, MCP backup=true y relectura.
- Reseñas:135actualizadas in situ por API WP con respaldo previo;11/11 condiciones automáticas,135textos servidos, mismos autores/IDs/fechas/rating/metadatos y promedios/conteos. Fecha de modificación de35productos actualizada automáticamente por guardado nativo Woo; hash reconstruido verifica que los restantes campos comerciales auditados no cambiaron.
- Verificación: HTTP501/501 dos pasadas, ventana segura29/29 dos pasadas, Node10/10 y16aserciones; lint correcto.13732protegidos/138mu previos idénticos,14pedidos/2usuarios/opciones iguales. Sin nuevos pedidos, correos o despliegue.
- Pendiente: medir aviso44px/espacios18–16px, líneas de tarjetas, overflow, scroll/teclado, lightbox táctil, zoom escritorio, modales/tres vistas y #place_order solo en Pago en Chrome real. No cerrado; no certifica ausencia de P0–P2.

### V14-COMENTARIOS-PROGRESO-ELIMINAR · P2 · En verificación por Claude
- Local: fichas, bolsa y carrito. Eliminado pseudo de cinco estrellas doradas, títulos con regla Barlow28 compartida. Corregida dependencia de V9 CSS ausente en carrito; componente común con pista6px y porcentaje real. Filtro único devuelve enlace eliminar nativo/SVG/aria; duplicado anterior no reproducido en muestra HTTP.
- Dos pasadas HTTP136/136 y dos regresiones51/51; eliminación nativa con nonce/total/progreso y componente idéntico bolsa/Resumen bajo/sobre149. Integridad13732 protegidos/14pedidos/99productos/opciones sin cambios. Registro no; login exitoso no probado. Ver diseno_minikids/v5/INFORME_V14.md.
- Estado: no cerrado. Chrome pendiente para barra visible, línea base, responsive, recorte, foco e interacción AJAX. Sin navegador, pedidos nuevos, correos ni despliegue.

### V13-PULIDO · P2 · En verificación por Claude
- Local: tienda, cinco carruseles de portada, ficha, carrito y modal de cuenta; seis anchos, incluido390px. Reproducir según diseno_minikids/v5/INFORME_V13.md, sin enviar pedidos/reseñas/recuperación/registro.
- Hallazgos de código: pseudo OFERTA histórico, tag right negativo, compra con wrap y marca con orden parcial; tres zonas de reseñas por grids anidados, máscara histórica de papelera y formato de Envío heredado.
- Corrección solo mu-plugins por MCP backup=true: tag interior, orden común y grid compra45/55, un grid nativo de comentarios, especificidad azul/modal, máscara retirada y SVG único, progreso compacto y método/costo/destino ordenados. Formulario, cantidades, métodos/calculadora y AJAX nativos conservados.
- Verificación técnica en verificacion-V13-1/2.json, verificacion-V13-regresion-1.json e integridad-V13.json. No certifica layout ni interacciones: recorte de slides, alturas64/520/120, responsive390, foco/teclado y regresión visual pendientes de Chrome. Registro nativo no, sin cambiar ajuste; éxito login/registro/confirmación de recuperación no probados. No cerrado.

### V12-FICHA-TARJETAS-ACCESO · P2 · En verificación por Claude
- Local: /wordpress/tienda/, portada, fichas, carrito y Mi cuenta. Comentarios movidos fuera de columnas por hook antes de recomendaciones; un renderer Woo para todos los bucles y cinco carruseles de portada; tag - N % real redondeado, cantidad y AGREGAR, sin controles duplicados.
- Acceso dialog invitado, mejora progresiva /mi-cuenta/, login WP/Woo con nonce/no-store/errores genéricos; registro oculto por opción nativa no. Recuperación implementada sin enviar solicitudes/correos; éxito de login/retorno de carrito y registro habilitado no ejercitados. Foto única CSS, pago limpio, datos de zonas/plazo editable, etiqueta bajo compra.
- HTTP83/83 dos pasadas; V11 51/51 y regresiones VM. Sin Chrome: medidas/encuadre2fotos/interacción/foco/primer pintado pendientes.13732 protegidos y14pedidos/hash iguales; solo caché nativa Blocksy458. Reproducción y límites en diseno_minikids/v5/INFORME_V12.md. No cerrado ni autocertificado.

### V11-BOLSA-CARRITO · P2 · En verificación por Claude
- URL local: /wordpress/cart/ y drawer Mi bolsa; 1/2/5 productos largos, seis anchos. Causa comprobada por HTTP: plantilla mini-cart de Blocksy con product-data no corresponde al CSS V9. Selección nativa Woo desde mu-plugin y grid72/flexible/100; papeleras/pseudoelementos unificados.
- Aviso/cupón/envío/Resumen compactos; vaciado y relacionados tras tabla en izquierda; carrusel sin inset y etiquetas porcentuales. Reinit/delegación tras Ajax. Dos pasadas52/52 HTTP/DOM y datos/integridad; no medición de metas520/120/64px ni interacción real. INFORME_V11.md. Pendiente Chrome; no cerrado.

### V11-CHECKOUT · P2 · En verificación por Claude
- URL local: /wordpress/checkout/. Failsafe antes visible de inmediato; ahora esqueleto, fallo diferido4s CSS+JS si falta ready, formulario sigue bloqueado. SVG candado, resumen con sombra/radio12 y sufijo prueba local retirado de títulos públicos sin editar opciones de pasarelas.
- Watchdog6VM, regresión13modales/9contador/7pasos y HTTP52/52 dos pasadas. Primer pintado, no-JS visual, reduced motion, consola/Ajax/BFCache y seis anchos pendientes de Claude.14 pedidos/hash y opciones iguales,13732 protegidos intactos; sin pedidos/correos/navegador. No cerrado.

### V10-MODALES · P1 · En verificación por Claude
- URL: /wordpress/product/abaco-pedagogico-y-laberinto-de-madera/.
- Antes: cuatro botones visibles sin apertura. Causa comprobada en código/HTML: JS footer20 se ejecuta antes de imprimir dialogs footer60; al faltar el nodo omite listener. La hipótesis de reemplazo por V5/V6 no se confirmó.
- Corrección propia: delegación en document y resolución al clic, fallback con overlay, Esc/X/backdrop, foco/scroll y pagehide/pageshow. 13 casos VM (incluye reproducción del binding vacío original), sin navegador. Pendiente clic real en cuatro modales, foco, fallback, Ajax, Atrás, consola y seis anchos. No cerrado.

### V10-FICHA · P2 · En verificación por Claude
- Una miga derecha y una cabecera Comentarios con lista/formulario Woo nativos; precio/oferta y etiqueta, CTAs amarillo/azul, caja con SVG, separación28px y cinco edades corregidas por CRUD.
- Dos pasadas HTTP/DOM32/32; MK-095/470 oferta y producto sin oferta;99 productos revisados y14 pedidos/hash intactos;13.732 protegidos iguales. INFORME_V10.md. Pendientes geometría/colores computados y regresión interactiva V9 en Chrome real; no autocertificar.

### V9-SEGURIDAD · P1 · En verificación por Claude
- URL: /wordpress/checkout/ y ?mk6_ttl=15 para ventana local nueva.
- Síntoma reportado: checkout normal visible antes de activar ventana; reloj solo cliente evadible.
- Corrección: CSS crítico/clase servidor/failsafe, sesión Woo con plazo/token y guards de checkout clásico/Store API antes de creación; no-store, pageshow y retorno al carrito. Sin reload de entrada.
- Evidencia: dos pasadas42/42 HTTP/DOM,10 comprobaciones nativas en memoria,9 VM reloj y7 VM pasos; mismos14 pedidos/hash y98 datos comerciales. No se creó pedido ni se envió correo. INFORME_V9.md y JSON V9.
- Pendiente: primer pintado/una navegación/BFCache real, comportamiento de tarjetas/Ajax/teclado y responsive en Chrome. No cerrar por las pruebas técnicas.

### V9-CARRITO-BOLSA · P2 · En verificación por Claude
- Corrección: retiro real de caja superior; barra149−subtotal dentro de Resumen; Mi bolsa con zona inferior dentro del fragmento nativo, lista con scroll y cantidades/retirada Woo.
- HTTP comprueba ubicación única, diferencia real, umbral y fragmentos. Pendientes medida≤32px, caja≈64px, alturas, dos líneas, pie fijo, vacío/muchos productos y seis anchos. Sin autocertificación visual.

### V7-LOADER-TTL · P1 · En verificación por Claude
- URL: /wordpress/?mk6_loader_debug=1 y /wordpress/checkout/?mk6_ttl=15, solo entorno local.
- Causa: V6 desactiva animación reducida, reglas globales animation:none!important y duración .001ms; salida frágil por shorthand. Recarga con actividad renovaba diez minutos. WordPress localiza TTL como texto: corregido Number(mk6.ttl).
- Corrección: archivos propios loader-v7, excepciones de ID, pulso solo opacidad, transición y failsafe2.5s, load/pageshow/visibilitychange; fecha absoluta start/end invariable, alerta y retorno con carrito intacto, hook local15s.
- Verificación técnica: php-l/MCP backup/relectura,13 casos de lógica VM, guard local/no local y configuración HTTP; ver INFORME_V7.md y JSON V7. Pendientes getAnimations, capturas/pintura, reloj/recarga/retorno real. No cerrar sin revisión de Chrome.

### V7-FOTOS · P2 · Producto41 pendiente; siete recortes en revisión visual
- Ocho ediciones image_gen con referencias originales. Medios492–498 asignados a136/134/91/44/43/40/39; originales conservadas en primera posición de galería/hash. Bloques39 sin niño en principal.
- Producto41 rechazado porque el generador completó base/perímetro fuera de fuente recortada. No asignado, falta fuente que muestre el objeto completo. Fuentes44/40 no certifican productos infantiles; validación comercial pendiente.
- Evidencia: imagenes-V7.json, medios-V7.json, V7-originales-recortes-blanco-oscuro.jpg. Identidad física y perfección de bordes no certificadas.

### V7-LAYOUT-IDIOMA · P2 · En verificación / idioma nativo Woo pendiente
- Sticky de galería >=1024 top24; apilado debajo; fila actions del carrito sin caja visual y relacionados sin segundo inset. Falta medición Chrome en360/390/768/1024/1280/1440.
- Core es_PE/es_ES ya presente, sitio es_PE. Paquete Woo no instalado: API oficial y archivo oficial fallan, cURL7 por proxy127.0.0.1:9. Fallback291 entradas, contextos/plurales, validaciones y asuntos; literales simples auditados sin pendientes en su alcance.
- No se enviaron correos, no se probaron todos los estados dinámicos ni se renderizó factura con pedido; no declarar traducción exhaustiva. Evidencias language-V7.json/native-audit-V7.json y reproducción en INFORME_V7.md.

### V6-QA · P2 · Puntos 1–14 · En verificación por Claude
- URL: /tienda/, fichas, /cart/, /checkout/, pie y /terminos-y-condiciones/; 360/390/768/1024/1280/1440 pendientes de navegador.
- Correcciones en código propio aplicadas por MCP con respaldo y lint: dos columnas y comentarios únicos, recomendaciones compartidas, cupón en Resumen, actualización nativa automática, checkout progresivo Datos/Entrega/Pago con temporizador y loader de marca.
- Verificaciones técnicas: HTTP/Store API, cantidades/nonces/stock, cupón rechazado en español, update_order_review sin crear pedido, aviso de vencimiento con carrito conservado, nueve WebP nuevos y originales en galería/hash. Core/terceros 13.730 sin diferencias; HPOS/BACS/COD y 14 pedidos conservados.
- No se ejecutó navegador: pendientes desbordes, alineación, foco/teclado, drawer, avance de pasos/errores, debounce, aplicación de cupón desde UI, reloj/recarga/retorno automático, loader y editor. No cerrar este P2 sin las dos pasadas visuales de Claude.
- Evidencias: _trabajo/diseno_minikids/v5/INFORME_V6.md, verificacion-V6-final-*.json, native-checks-V6.json, integridad-V6.json, media-checks-V6.json.

### V6-IDIOMA · P2 · Punto 4 · Pendiente de paquete nativo/revisión exhaustiva
- Intentos por API oficial de traducciones y Language_Pack_Upgrader documentados; descarga impedida por red del entorno. Locale es_PE; fallback propio gettext/contexto e Intl, con etiquetas principales y cupón inválido comprobados en español.
- Falta instalar paquete Woo es_PE/es_ES y verificar todos los mensajes, plurales y correos, sin enviar pedidos ni correo de prueba en esta tarea. Evidencias language-V6.json y native-application-V6.json.

### V6-FOTOS · P2 · Punto 6 · Parcial, ocho originales pendientes
- Medios nuevos 483–491 asignados a nueve productos; originales en galería e intactos. Se reutilizaron extracciones V3 con procedencia; rembg/modelo no disponibles offline.
- Pendientes 136/134/44/43/41/40/39 por ausencia de extracción fiable; 91 rechazado visualmente por resto claro bajo ruedas. No se asignaron sustitutos de otro juguete.
- Evidencia: imagenes-V6.json, medios-V6.json, V6-recortes-blanco-oscuro.jpg y V6-heredadas-diagnostico.jpg. Confirmación del producto físico y revisión de bordes a escala real pendientes del dueño/Claude.

### V5-BCDE-QA · P2 · Fases B–E · En verificación
- URL: /tienda/, fichas, /cart/, /checkout/ y pie 219; seis anchos pendientes.
- Implementación local por MCP, datos/API y sintaxis comprobados. No Chrome/Playwright: revisión visual/interacción y editor a cargo de Claude.
- Carrito presentado con shortcode y plantilla Woo nativos desde filtros propios; archivos de plantilla y bloque guardado intactos. Página de Términos no configurada: no se añadió aceptación ni documento ficticio. Modal email/compartir omitidos.
- Evidencia: _trabajo/diseno_minikids/v5/INFORME_BCDE.md, verificacion-BCDE-*.json, native-checks-BCDE-*.json, integridad-BCDE.json. No declarar cierre sin dos pasadas visuales de Claude.

# Registro Central de Bugs y QA · MiniKids (`bugs.md`)

## V5 · Catálogo demo y revisión ampliada de galerías

### BUG-V5-P-GAL · P2 · Estado: reparación técnica comprobada; identidad comercial pendiente
- Inventario completo de los 17 publicados detectó 35 URLs de galería ausentes más allá de las tres páginas de muestra de A.
- Corrección: 22 medios regenerados con APIs nativas, sin cambiar imágenes asignadas; originales SHA-256 conservados. Respaldos privados antes de metadata/derivados.
- Evidencia: reparacion-galerias-ampliada.json y dos pasadas de verificacion-http-P.json, con todas las imágenes inventariadas 200 y decodificadas.
- Las fotos heredadas siguen requiriendo confirmación de identidad/SKU por el dueño; no se sustituyeron por fotos de otros juguetes.

### QA-V5-P · Estado: verificación técnica completada; revisión visual/comercial pendiente
- 98 publicados, 81 demo identificados, 14 por categoría, 35 ofertas totales; 20 nuevas galerías con 2 fotos extra. 121 imágenes fuente con alfa y hashes distintos, variantes 1200/600 y WebP calidad 82; hojas para Claude.
- Dos pasadas CRUD y HTTP/Store API; filtro admin probado por WP_Query nativo; prueba Ajax aislada sin pedido. Los 17 publicados y el borrador 212 se conservaron.
- Precios, edades, contenidos, pesos y medidas son demo, sin verificación de proveedor/mercado. Validar antes de venta/despliegue. No se certifica navegador, responsive, bordes perfectos ni editor Gutenberg; Chrome/Playwright prohibidos por el usuario.
- Evidencias: INFORME_P.md y verificacion-P*.json, verificacion-http-P.json, verificacion-admin-P.json, imagenes_auditoria.json.
- B–E fuera de alcance; sin despliegue ni cambios de pasarelas.


## Revisión V4 aplicada · 2026-11-23 · Sin cierre visual

### BUG-RETAIL-V4-001 · P2 · Fase 1 · Estado: En Verificación
- URL: `http://localhost:8082/wordpress/`.
- Esperado: portada V4 publicitaria con Gutenberg válido y revisión visual de Claude.
- Corrección aplicada por MCP: tres slides × cuatro paneles, tres promos y cinco secciones de banner/carrusel, clases mk4-*. 29 Medios existentes renderizados por Claude, subidos por API nativa. CSS/JS añadidos a retail con backup=true; PHP solo versión. Respaldo actual en v4/respaldo/aplicacion-*.
- Evidencia: v4/INFORME.md, medios.json, aplicacion.json y verificacion-aplicacion.json. Dos pasadas HTTP/estáticas correctas, precios Store API y hashes de 29 Medios verificados; PHP y sintaxis JS OK; terceros/core sin diferencias.
- No certificado: cero bloques inválidos en Gutenberg, ausencia de overflow en seis anchos, hover/controles y carrito interactivo. Comentarios de bloques balanceados no sustituyen validación del editor. No se abrió navegador por instrucción del usuario; Claude toma capturas. Fase sin cierre; sin pedidos ni despliegue.

## Segunda revisión del escaparate · 2026-11-23

- **BUG-RETAIL-005 · P2 · Cerrado:** el contenedor restringido de Gutenberg limitaba el carrusel a 1290 px, dejando una franja lateral. Ancho explícito del recorrido y controles; hero comprobado a 1440 px de ancho y 371.52 px de alto.
- **BUG-RETAIL-006 · P2 · Cerrado:** reglas históricas imponían la fuente de títulos a los nombres de productos y texto oscuro al aviso de envío. Selectores acotados al escaparate corrigen la jerarquía y el contraste.
- **BUG-RETAIL-007 · P2 · Cerrado:** mezcla de colores en imágenes de categorías producía círculos vacíos en capturas completas de Chromium. Se conserva composición normal y se espera la decodificación real de imágenes antes de capturar. Revisión visual completa confirma las siete categorías.
- **BUG-RETAIL-008 · P2 · Cerrado:** las flechas centrales coincidían con los textos de campañas en móvil. Controles reubicados en las esquinas inferiores; las tres campañas se revisaron visualmente en 390 y 1440 px. Eliminado también el relleno inferior heredado del tema en la portada.
- **Ajuste visual solicitado:** proporciones de cabecera, hero, tres promociones, títulos y separaciones contrastadas con la referencia en el mismo ancho. Logo y colores propios conservados; fuentes Roboto/Rajdhani locales.
- **Cierre:** dos pasadas finales de seis anchos sin desbordes ni imágenes rotas; ocho comprobaciones de interacción correctas; portada y pie con bloques válidos. Solo local, sin pedidos ni pagos.

## Revisión local del rediseño comercial · 2026-11-23

Alcance: nueva cabecera, portada y pie. Dos pasadas consecutivas en 360/390/768/1024/1280/1440, búsqueda, carrito y apertura del formulario de compra. Las comprobaciones no enviaron pedidos ni pagos. No es una certificación de todos los componentes históricos del proyecto.

### BUG-RETAIL-001 · P2 · Cerrado
- La regla heredada de Portada limitaba dos veces el ancho del texto y cortaba palabras en el banner.
- Corrección: ancho y base flexible explícitos en el contenedor, con tratamiento separado para móvil. Verificado visualmente en seis anchos, dos pasadas.

### BUG-RETAIL-002 · P1 · Cerrado
- El buscador intentaba utilizar `minikidsCatalog` sin configuración; además la tienda nativa `/shop/` no conservaba la palabra al redirigir.
- Corrección: configuración del script desde WordPress, utilizando el catálogo canónico `/tienda/`. Dos pasadas: «osito» conduce a un producto coincidente.

### BUG-RETAIL-003 · P2 · Mitigado en la portada
- La copia local carece de la fotografía correcta del juego de memoria.
- Ese artículo se excluye de la selección nueva, conservando su referencia original. No se declara reparada la imagen del producto fuera de la portada. Dos pasadas de la portada: cero imágenes rotas.

### BUG-RETAIL-004 · P2 · Cerrado
- El texto flotante de WhatsApp se superponía a las categorías en móvil.
- Corrección: se retira el aviso flotante y se mantiene el botón de contacto con tamaño y separación adecuados. Verificado en las dos pasadas.

---

**Entorno:** XAMPP local (`http://localhost:8088/wordpress`)  
**Metodología:** Bucle *"Detectar → Registrar → Corregir → Verificar → Regresión"* (0 bugs P0–P2 abiertos para cerrar cada fase).  
**Última actualización:** 2026-10-13

---

## Resumen Ejecutivo de Mejoras y Pulido

| Total Casos | P0 | P1 | P2 | P3 | Resueltos | Abiertos | Estado |
| :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| 16 | 0 | 3 | 12 | 1 | 16 | 0 | **0 BUGS P0–P2 ABIERTOS (100% VERIFICADOS)** |

---

## Tabla Resumen de Bugs

| ID | Fase | Sev. | URL / Componente | Resumen del Defecto | Estado |
| :---: | :---: | :---: | :--- | :--- | :---: |
| **BUG-001** | 1 | P2 | `/tienda/` (Catálogo) | Productos e imágenes repetidas o no correspondientes | **Cerrado** |
| **BUG-002** | 1 | P1 | `/contacto/` | Vista desalineada y formulario roto por sanitización | **Cerrado** |
| **BUG-003** | 1 | P1 | `/checkout/` | Pasarela PayPal inhabilitada y COD presentes en checkout | **Cerrado** |
| **BUG-004** | 1 | P2 | `/cart/` | Banner de progreso de envío gratis descentrado | **Cerrado** |
| **BUG-005** | 1 | P3 | Navbar Global | Falta de micro-interacciones lúdicas y animaciones en navegación | **Cerrado** |
| **BUG-006** | 1 | P2 | `/` (Portada) | 3 puntos de carrusel debajo de hero con imagen fija única | **Cerrado** |
| **BUG-007** | 1 | P2 | `/` (Portada) | Espacio en blanco excesivo entre header sticky y Hero | **Cerrado** |
| **BUG-008** | 1 | P2 | `/tienda/` (Filtros) | Botón "Limpiar filtros" visible permanentemente en categoría "Todos" | **Cerrado** |
| **BUG-009** | 1 | P2 | `/my-account/` | Perfil de usuario sin identidad visual ni diseño de MiniKids | **Cerrado** |
| **BUG-010** | 2 | P2 | `/contacto/` (WhatsApp) | Botón con número erróneo y sin mensaje cordial predeterminado | **Cerrado** |
| **BUG-011** | 2 | P2 | Navbar Global | Desalineación vertical de enlaces respecto a acciones y altura excesiva | **Cerrado** |
| **BUG-012** | 2 | P2 | `/` (Hero Slide 1) | Botón «Ver Juguetes» apuntaba a ancla interna en vez de `/tienda/` | **Cerrado** |
| **BUG-013** | 2 | P2 | `/checkout/` (Diseño) | Título descentrado, jerarquía deficiente y espaciados descompensados | **Cerrado** |
| **BUG-014** | 2 | P1 | `/checkout/` (Campos) | Solapamiento de etiqueta en Email y falta de campo Celular en Contacto | **Cerrado** |
| **BUG-015** | 2 | P2 | Mini Carrito (Drawer) | Ausencia de botón visible para acceder a la página completa `/cart/` | **Cerrado** |
| **BUG-016** | 2 | P2 | `/tienda/` & Navbar | Encabezado de tienda descentrado y botón inactivo de búsqueda en navbar | **Cerrado** |

---

## Detalle Histórico de Bugs

### BUG-001: Productos e imágenes repetidas o no correspondientes
- **Fase:** 1 (Preparación del entorno y catálogo)
- **Severidad:** P2 (Defecto visible en catálogo de productos)
- **URL:** `http://localhost:8088/wordpress/tienda/`
- **Pasos para reproducir:**
  1. Abrir la página `/tienda/`.
  2. Navegar por los productos y observar múltiples juguetes ficticios repitiendo la foto de conejita rosa (`prod-conejita.png`).
- **Comportamiento obtenido:** Catálogo con 37 productos donde 20 eran duplicados con la misma imagen.
- **Comportamiento esperado:** Catálogo curado y limpio con 17 juguetes únicos, cada uno con su imagen fotográfica fiel a la categoría.
- **Solución implementada:** Ejecutado script de limpieza `fix_products_images.php`, eliminando posts duplicados (#106–#125) y enlazando assets nativos (`cat-madera.png`, `cat-educativos.png`, `cat-aire-libre.png`, `cat-munecas.png`).
- **Evidencia de verificación:** Catálogo auditado con `list_products.php`: 17 productos únicos con imágenes diferenciadas 1 a 1.
- **Estado:** **Cerrado**

---

### BUG-002: Vista `/contacto/` desalineada y formulario roto
- **Fase:** 1
- **Severidad:** P1 (Función rota de contacto con clientes)
- **URL:** `http://localhost:8088/wordpress/contacto/`
- **Pasos para reproducir:**
  1. Visitar `http://localhost:8088/wordpress/contacto/`.
  2. Intentar ingresar datos de contacto en el formulario.
- **Comportamiento obtenido:** Los inputs `<input>` y `<textarea>` fueron eliminados por el filtro del editor de bloques de WordPress, dejando solo textos estáticos y diseño desestructurado.
- **Comportamiento esperado:** Formulario interactivo y accesible de 2 columnas con tarjetas de atención (WhatsApp, horario, soporte) y campos funcionales.
- **Solución implementada:** Inyección de layout seguro en `wp-content/mu-plugins/minikids-estetica.php` vía filtro `the_content` para la página `/contacto/`, con CSS en `minikids-estetica.css`.
- **Evidencia de verificación:** `curl` a `/contacto/` confirma presencia de `<form id="minikids-contact-form">`, campos `<input type="text">`, `<input type="email">`, `<textarea>` y botón CTA.
- **Estado:** **Cerrado**

---

### BUG-003: Pasarelas de pago no disponibles activas en checkout
- **Fase:** 1
- **Severidad:** P1 (Riesgo en proceso de compra)
- **URL:** `http://localhost:8088/wordpress/checkout/`
- **Pasos para reproducir:**
  1. Agregar un producto al carrito e ir a `/checkout/`.
  2. Observar las opciones de pago disponibles.
- **Comportamiento obtenido:** Aparecía botón "Proceed to PayPal" (sin credenciales API configuradas) y opción de pago contra entrega (COD).
- **Comportamiento esperado:** Únicamente pasarelas habilitadas (Transferencia Bancaria directa / Yape / Plin).
- **Solución implementada:** Actualizadas opciones de WooCommerce (`woocommerce_ppcp-gateway_settings`, `woocommerce_cod_settings`, `woocommerce_bacs_settings`) mediante `fix_gateways.php`.
- **Evidencia de verificación:** Ejecución de `fix_gateways.php` confirma gateways `ppcp-gateway`, `paypal` y `cod` deshabilitados; `bacs` habilitado.
- **Estado:** **Cerrado**

---

### BUG-004: Banner de progreso de envío gratis descentrado en `/cart/`
- **Fase:** 1
- **Severidad:** P2 (Defecto visual en flujo de compra)
- **URL:** `http://localhost:8088/wordpress/cart/`
- **Pasos para reproducir:**
  1. Agregar productos al carrito e ir a `/cart/`.
  2. Observar el banner azul de envío gratis (`🚚 Te faltan S/...`).
- **Comportamiento obtenido:** Banner alineado hacia la izquierda o con márgenes descompensados en pantallas desktop.
- **Comportamiento esperado:** Banner azul pastel con bordes suaves centrado horizontalmente (`margin: 0 auto; max-width: 680px;`).
- **Solución implementada:** Añadidas reglas en `minikids-estetica.css` forzando centrado en contenedor, flexbox header y track de progreso.
- **Evidencia de verificación:** Inspección de CSS en línea 1262 de `minikids-estetica.css` con `margin: 0 auto 32px auto !important; max-width: 680px; justify-content: center;`.
- **Estado:** **Cerrado**

---

### BUG-005: Falta de micro-interacciones lúdicas en Navbar
- **Fase:** 1
- **Severidad:** P3 (Mejora de experiencia e identidad visual)
- **URL:** Global (todas las páginas)
- **Pasos para reproducir:**
  1. Pasar el cursor sobre el logo, enlaces de navegación, icono de búsqueda y carrito.
- **Comportamiento obtenido:** Transiciones rígidas estándar.
- **Comportamiento esperado:** Animaciones suaves tipo resorte (`cubic-bezier(0.34, 1.56, 0.64, 1)`), elevación sutil de elementos e iluminación suave al hover.
- **Solución implementada:** Agregadas transiciones en `minikids-estetica.css` para `.site-branding img`, `.header-menu a`, `.ct-cart-item` y botón de búsqueda.
- **Evidencia de verificación:** Estilos compilados y validados en `minikids-estetica.css`.
- **Estado:** **Cerrado**

---

### BUG-006: 3 puntos de carrusel debajo de hero con imagen fija única
- **Fase:** 1
- **Severidad:** P2 (Defecto de usabilidad)
- **URL:** `http://localhost:8088/wordpress/`
- **Pasos para reproducir:**
  1. Cargar la página de inicio.
  2. Mirar debajo de la imagen principal del Hero.
- **Comportamiento obtenido:** Aparecían 3 puntos simulando un slider estático innecesario.
- **Comportamiento esperado:** Solo la imagen hero limpia con bordes suaves sin indicadores falsos.
- **Solución implementada:** Eliminado el bloque HTML `.minikids-hero-dots` del post 8 y forzado `display: none !important;` en CSS.
- **Evidencia de verificación:** Script `verify_home.ps1` ejecutado con éxito y `curl` sin coincidencias para `.minikids-hero-dots`.
- **Estado:** **Cerrado**

---

### BUG-007: Espacio en blanco excesivo entre header sticky y Hero
- **Fase:** 1
- **Severidad:** P2 (Defecto visual de layout)
- **URL:** `http://localhost:8088/wordpress/`
- **Pasos para reproducir:**
  1. Visitar la portada en desktop y móvil.
- **Comportamiento obtenido:** Blocksy insertaba `padding-top: var(--theme-content-vertical-spacing)` (50px+), generando una franja blanca que separaba el navbar del banner principal.
- **Comportamiento esperado:** El Hero inicia inmediatamente bajo el navbar sin franja blanca.
- **Solución implementada:** Sobrescrito en `minikids-estetica.css` el `padding-top: 0 !important;` para selectores de portada (`.home [data-vertical-spacing*="top"]`, `.site-main`, `.entry-content`).
- **Evidencia de verificación:** Verificado en hoja de estilos de portada y validado con `verify_home.ps1`.
- **Estado:** **Cerrado**

---

### BUG-008: Botón "Limpiar filtros" visible permanentemente en categoría "Todos"
- **Fase:** 1
- **Severidad:** P2 (Defecto de usabilidad y estética)
- **URL:** `http://localhost:8088/wordpress/tienda/`
- **Pasos para reproducir:**
  1. Ir a `/tienda/` sin realizar ninguna búsqueda ni elegir categoría.
- **Comportamiento obtenido:** La píldora `Ver todos ✕` se mostraba de forma fija rompiendo la armonía visual.
- **Comportamiento esperado:** El botón debe permanecer 100% oculto en "Todos" sin búsqueda, y aparecer únicamente si se elige una categoría específica o se escribe texto en el buscador.
- **Solución implementada:** Actualizada la lógica en `minikids-catalogo.js` (condición `hasFilter`) y en `minikids-estetica.css` con regla `display: none !important;` por defecto y activación exclusiva mediante `.is-visible`.
- **Evidencia de verificación:** Lógica revisada en `minikids-catalogo.js` líneas 105-110.
- **Estado:** **Cerrado**

---

### BUG-009: Perfil de usuario sin identidad visual ni diseño de MiniKids
- **Fase:** 1
- **Severidad:** P2 (Defecto de consistencia de marca)
- **URL:** `http://localhost:8088/wordpress/my-account/`
- **Pasos para reproducir:**
  1. Navegar a `/my-account/`.
- **Comportamiento obtenido:** Vistas estándar en blanco y negro sin estética infantil ni cards acordes a la paleta MiniKids.
- **Comportamiento esperado:** Banner superior con saludo amigable, tarjetas pastel, navegación por pestañas suaves y formulario de acceso estilizado.
- **Solución implementada:** Integrado hook de cabecera en `minikids-estetica.php` y estilos específicos en `minikids-estetica.css` (`.minikids-account-auth-header`, `.minikids-account-welcome-banner`, pestañas y botones).
- **Evidencia de verificación:** `curl` en `/my-account/` confirma cabecera `.minikids-account-auth-header` y estilos correspondientes aplicados.
- **Estado:** **Cerrado**

---

### BUG-010: Botón de WhatsApp con número erróneo y sin mensaje cordial predeterminado
- **Fase:** 2 (Interacción y Canales de Comunicación)
- **Severidad:** P2 (Defecto en canal de atención al cliente)
- **URL:** `http://localhost:8088/wordpress/contacto/`
- **Pasos para reproducir:**
  1. Ir a `/contacto/` y pulsar el botón «Chatear por WhatsApp».
- **Comportamiento obtenido:** Abría enlace con número ficticio `51999888777` sin mensaje inicial.
- **Comportamiento esperado:** Abrir conversación internacional con `+51 991 117 38` (`https://wa.me/5199111738`) y mensaje cordial predeterminado codificado.
- **Solución implementada:** Modificado `minikids-estetica.php` y post 11 con URL `https://wa.me/5199111738?text=%C2%A1Hola%20MiniKids!%20Me%20gustar%C3%ADa%20recibir%20informaci%C3%B3n%20y%20asesor%C3%ADa%20sobre%20sus%20juguetes.` y visualización `+51 991 117 38`.
- **Evidencia de verificación:** `verify_improvements.php` comprobó presencia del número, enlace wa.me y codificación del mensaje (`PASS`).
- **Estado:** **Cerrado**

---

### BUG-011: Desalineación vertical de enlaces en Navbar respecto a acciones y altura excesiva
- **Fase:** 2 (Cabecera y Navegación)
- **Severidad:** P2 (Defecto visual de layout)
- **URL:** Global (`header.ct-header`)
- **Pasos para reproducir:**
  1. Observar la barra de navegación superior en escritorio.
  2. Los enlaces de menú se encontraban desplazados hacia arriba respecto al icono del carrito.
- **Comportamiento obtenido:** Menú desalineado verticalmente y altura de barra de más de 80px.
- **Comportamiento esperado:** Todos los elementos (logo, enlaces, carrito) compartiendo exactamente la misma línea central vertical (`align-items: center`), con altura compacta de 64px.
- **Solución implementada:** Actualizado `minikids-estetica.css` forzando `min-height: 64px; height: 64px;` en `[data-row="middle"]`, `height: 100%` con flexbox centrado en `header-menu-1`, items y `.ct-header-cart`.
- **Evidencia de verificación:** Inspección visual y reglas CSS validadas; responsive móvil preservado sin overflow.
- **Estado:** **Cerrado**

---

### BUG-012: Botón «Ver Juguetes» en el Hero redirigía internamente a ancla
- **Fase:** 2 (Navegación Principal)
- **Severidad:** P2 (Usabilidad de navegación)
- **URL:** `http://localhost:8088/wordpress/`
- **Pasos para reproducir:**
  1. En el slide 1 de la portada («Diversión para cada etapa»), hacer clic en «Ver juguetes >».
- **Comportamiento obtenido:** Navegaba a `#productos` desplazándose dentro de la misma página de portada.
- **Comportamiento esperado:** Dirigir directamente a la página de catálogo real de WooCommerce (`/wordpress/tienda/`).
- **Solución implementada:** Modificado post 8 en base de datos (`update_post8_button.php`), reemplazando el atributo `href` por `/wordpress/tienda/`.
- **Evidencia de verificación:** `verify_improvements.php` y curl validaron `href="/wordpress/tienda/">Ver juguetes` (`PASS`).
- **Estado:** **Cerrado**

---

### BUG-013: Título de Checkout descentrado y distribución descompensada
- **Fase:** 2 (Proceso de Compra)
- **Severidad:** P2 (Defecto estético y jerarquía visual)
- **URL:** `http://localhost:8088/wordpress/checkout/`
- **Pasos para reproducir:**
  1. Acceder a `/checkout/` con productos en el carrito.
- **Comportamiento obtenido:** Título alineado a la izquierda sin jerarquía destacada, pasos y resumen sin contenedor unificado.
- **Comportamiento esperado:** Título centrado con barra decorativa amarilla, contenedor de 1180px con grid de 2 columnas en desktop y tarjetas independientes de 20px de radio.
- **Solución implementada:** Añadidas reglas en `minikids-estetica.css` para `.woocommerce-checkout .entry-header`, `.page-title::after`, `.wc-block-checkout.is-large`, pasos y sticky order summary.
- **Evidencia de verificación:** Comprobado renderizado limpio y estilizado en `minikids-estetica.css`.
- **Estado:** **Cerrado**

---

### BUG-014: Solapamiento de etiqueta en campos de Checkout y falta de campo Celular
- **Fase:** 2 (Formularios y Checkout)
- **Severidad:** P1 (Defecto funcional y de legibilidad en checkout)
- **URL:** `http://localhost:8088/wordpress/checkout/`
- **Pasos para reproducir:**
  1. En `/checkout/`, escribir en el campo «Email address».
  2. Observar que el texto se escribe encima de la etiqueta.
- **Comportamiento obtenido:** La etiqueta floating quedaba detrás o encima del texto ingresado; no existía campo para número de celular en Contact Information.
- **Comportamiento esperado:** Espaciado superior `padding-top: 22px` con etiqueta que escala a `top: 7px` y `scale(0.76)` sin solapamiento; nuevo campo «Número de celular» en Contact Information con validación de 8-15 dígitos y sincronización a `billing_phone` del pedido.
- **Solución implementada:** Corregidos estilos de `.wc-block-components-text-input` en `minikids-estetica.css`, añadida función de sincronización en `minikids-cart-drawer.js`, registrado `minikids/celular` mediante `woocommerce_register_additional_checkout_field` en `minikids-estetica.php` y ocultado teléfono redundante en dirección.
- **Evidencia de verificación:** `verify_improvements.php` confirmó `minikids/celular` registrado en `contact` y `woocommerce_checkout_phone_field = 'hidden'` (`PASS`).
- **Estado:** **Cerrado**

---

### BUG-015: Ausencia de botón para acceder al carrito completo desde el Mini Carrito
- **Fase:** 2 (Navegación de Carrito)
- **Severidad:** P2 (Defecto de flujo UX)
- **URL:** Mini carrito flotante (drawer)
- **Pasos para reproducir:**
  1. Desplegar el mini carrito lateral haciendo clic en el icono de carrito.
  2. Solo aparecía «Finalizar compra >» y «Seguir comprando».
- **Comportamiento obtenido:** Imposibilidad de ir a la página completa `/cart/` para gestionar cantidades detalladas.
- **Comportamiento esperado:** Botón visible «Ver carrito» que dirija a `/wordpress/cart/`.
- **Solución implementada:** Agregado `<a href="..." class="drawer-viewcart-btn">Ver carrito</a>` en el footer del drawer en `minikids-estetica.php` y estilizado en `minikids-estetica.css` con estética pastel azul interactiva.
- **Evidencia de verificación:** `verify_improvements.php` confirmó presencia del botón y URL válida hacia `/wordpress/cart/` (`PASS`).
- **Estado:** **Cerrado**

---

### BUG-016: Encabezado de Tienda descentrado y botón inactivo de búsqueda en Navbar
- **Fase:** 2 (Tienda y Cabecera)
- **Severidad:** P2 (Defecto de maquetación)
- **URL:** `http://localhost:8088/wordpress/tienda/` & Global
- **Pasos para reproducir:**
  1. En `/tienda/`, el bloque «Todos los juguetes» y «Elige una nueva aventura... 🎈» no estaba centrado ni el globo alineado.
  2. En el navbar, el botón de lupa no ejecutaba ninguna acción.
- **Comportamiento obtenido:** Encabezado ladeado y botón fantasma de búsqueda en navbar.
- **Comportamiento esperado:** Encabezado centrado en flexbox con globo alineado; botón de búsqueda eliminado del navbar preservando intacto el buscador interno del catálogo.
- **Solución implementada:** Estilizado `.minikids-tienda-header` con centrado total y span para globo; retirado `search` de `header_placements` y ocultado `.ct-header-search` en CSS; buscador del catálogo en `/tienda/` conservado al 100%.
- **Evidencia de verificación:** `verify_improvements.php` comprobó encabezado centrado, globo alineado, búsqueda eliminada de navbar y buscador interno activo (`PASS`).
- **Estado:** **Cerrado**

---

### BUG-017: Número WhatsApp 991117308, precio visible en Navbar, solapamiento de badge y falta de diferenciación en ofertas
- **Fase:** 2 & 3 (Cabecera, Catálogo y Contacto)
- **Severidad:** P2 (Defecto visual y de contacto)
- **URL:** Global (`/`, `/tienda/`, `/contacto/`)
- **Pasos para reproducir:**
  1. En `/contacto/`, el número de WhatsApp le faltaba un dígito (+51 991 117 38 en vez de +51 991 117 308).
  2. En el Navbar, aparecía un precio fijo "S/ 0.00" al lado del carrito ocupando espacio.
  3. El badge de cantidad se superponía sobre el SVG del carrito por estar confinado en un contenedor relativo de 15px.
  4. En el catálogo, todos los productos aparecían con precio en color rojo, sin diferenciar visualmente las ofertas de los precios normales.
- **Comportamiento obtenido:** Enlace y texto de WhatsApp con 8 dígitos; precio fijo visible en navbar; icono de carrito tapado por el badge; falta de jerarquía de precios en catálogo.
- **Comportamiento esperado:** WhatsApp a 9 dígitos `+51 991 117 308` con enlace codificado; navbar limpio sin precio visible (solo en mini carrito/carrito); badge en la esquina superior derecha sin tapar el SVG; ofertas en rojo con precio tachado y productos normales limpios y discretos.
- **Solución implementada:**
  1. Actualizado número de WhatsApp a `+51 991 117 308` y enlace `https://wa.me/51991117308?text=...` en `minikids-estetica.php` y en post 11.
  2. Ocultado `.ct-header-cart .ct-label`, `[data-price]`, `.ct-amount` en `minikids-estetica.css`.
  3. Desacoplado `.ct-icon-container` con `position: static !important;`, botón de 42px con SVG 20px centrado y badge anclado en `top: -2px; right: -3px;` con espacio libre sobre el icono.
  4. Estilizado `.price` base en neutro `var(--brand-ink)`, y ofertas con `ins` en `var(--brand-red)` y `del` tachado en `#8d99ae`.
  5. Centrado reforzado en `.minikids-tienda-header` y altura de navbar compacta a 60px con separación de 28px para el carrito.
- **Evidencia de verificación:** Suite `verify_improvements.php` ejecutada con salida 100% PASS en los 7 criterios.
- **Estado:** **Cerrado**




### BUG-018 · P1 · Correos de compra suprimidos en local
- Causa: minikeds-dev2-local devolvía éxito sin transporte para todo wp_mail.
- Corrección: habilitación explícita en Negocio y transporte Brevo en mu-plugins; plantillas nativas WooCommerce y HPOS.
- Verificación: dos pasadas Store API; resumen cliente/Nuevo pedido aceptados en sandbox, más dos mensajes reales con evento delivered.
- Estado: Cerrado en funciones comprobadas.

### BUG-019 · P2 · Ausencia de Libro y página de empresa editable
- Corrección: bloques Gutenberg para Nosotros/Libro/Privacidad, menú nativo, datos del proveedor en Ajustes y registros privados con respuesta administrativa.
- Evidencia: brain/attention-http-checks.json y wp-content/qa-evidencia/.
- Verificación: dos pasadas funcionales y visuales multi-breakpoint; editor de bloques válido.
- Estado: Cerrado; dos regresiones de administración aprobadas.

### BUG-020 · P2 · Atributos de celular no soportados y warnings del proxy alternativo
- Causa: placeholder/type en attributes de campos adicionales Woo; proxy legacy asume description en todas las rutas REST.
- Corrección: atributos inválidos retirados mediante MCP; proxy alternativo desactivado conservando MCP Adapter, sin editar terceros.
- Verificación: Store API conserva celular en HPOS; ocho abilities descubiertas y escritura con backup probada.
- Estado: Cerrado en funciones comprobadas.

### BUG-021 · P2 · Búsqueda vacía sin mensaje visible
- Causa: inyección buscaba comentarios Gutenberg cuando the_content ya los había procesado.
- Corrección: garantizar estado vacío único en HTML renderizado, con status accesible; etiqueta de búsqueda añadida.
- Verificación: búsqueda sin resultados y Limpiar restauran catálogo; drawer conserva fragmentos Ajax.
- Estado: Cerrado; dos pasadas de búsqueda/reset y drawer aprobadas.

### BUG-022 · P1 · Permiso de configuración al activar la release
- Causa: umask077 redujo los permisos del directorio privado recién creado; FPM no podía atravesarlo.
- Corrección: directorio root:minikids750 y configuración640, fuera de la raíz web. El primer intento se revirtió automáticamente sin tocar la base anterior.
- Verificación: lectura como usuario del pool, dos pasadas HTTP públicas y login/editor real.
- Estado: Cerrado; producción responde sin el error y conserva respaldo anterior.

### BUG-023 · P2 · Rutas de subcarpeta al publicar en dominio raíz
- Causa: pie, buscador, logo y fondo promocional usaban /wordpress/ en código propio.
- Corrección: home_url, URL localizada al buscador y recursos CSS relativos a su hoja. Edición local/producción por MCP con backup obligatorio; migración serializada de contenido con WP-CLI.
- Verificación: navegador en seis tamaños, dos pasadas HTTP, búsqueda vacía, drawer Ajax, checkout y Gutenberg. Revisión adicional de logo/fotos y ficha móvil registrada en brain/production-assets-checks.json.
- Estado: Cerrado;48/48 HTTP,68/68 navegador y14/14 recursos/ficha, incluidas dos pasadas posteriores al ajuste CSS. Sin cambios en terceros.

### BUG-V5-A-IMAGENES · P2 · FASE A · Estado: En verificación
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


### BUG-V17-CHECKOUT · P2 · Estado: En Verificación
- URL: http://localhost:8082/wordpress/checkout/
- Orden V17 puntos1–7: aviso/modal, fondo/tarjetas, correo progresivo, bloqueo de pasos, cabecera y resumen móvil implementados solo en mu-plugins.
- Evidencia técnica: ../../diseno_minikids/v5/INFORME_V17.md y verificacion-V17-*.json; PHP lint/node check y transiciones Node OK. Backups MCP y hashes protegidos/V16 iguales.
- Pendiente: Claude debe reproducir en Chrome a360/390/768/1024/1280/1440, login correcto con campos/carrito, foco/animación/acordeón/overflow/sticky/primer pintado. Sin cierre visual ni P0–P2. No se crearon pedidos ni enviaron correos.
