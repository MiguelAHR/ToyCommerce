# Estado minikeds_dev2

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
