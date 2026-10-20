# Registro Central de Bugs y QA · MiniKids (`bugs.md`)

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
