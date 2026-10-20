# Atención y correo: implementación editable desde WordPress

Implementado en la copia local http://localhost:8082/wordpress/ después de pull fast-forward main7fed1d5→837216e. No se reimportó la base ni se reemplazó la configuración local. Núcleo, Blocksy y plugins de terceros intactos.

## Dónde editar a mano

| Necesidad | Pantalla del administrador |
|---|---|
| Texto de ¿Quiénes somos? | Páginas → ¿Quiénes somos? (ID10, URL /nosotros/) |
| Introducción e instrucciones del Libro | Páginas → Libro de Reclamaciones (ID143) |
| Formulario del Libro | Mantener el bloque Shortcode [minikids_libro_reclamaciones]; su lógica se carga desde mu-plugins |
| Política de privacidad | Páginas → Privacidad (ID3) |
| Enlace de ¿Quiénes somos? en cabecera | Apariencia → Menús → menú principal |
| Enlaces del Libro y Nosotros en pie | Apariencia → Menús → Atención y empresa, ubicación MiniKids: ayuda y empresa |
| Razón social, RUC, domicilio, correo y teléfono | Ajustes → MiniKids · Negocio |
| Activar/desactivar correo y cambiar API key | Ajustes → MiniKids · Negocio; la clave se guarda fuera de WordPress y no se muestra |
| Remitente, destinatarios, asunto, encabezado y texto adicional | WooCommerce → Ajustes → Correos electrónicos |
| Revisar y responder una hoja | WooCommerce → Reclamaciones → abrir hoja → respuesta / enviar / reenviar constancia |
| Reenviar correo de un pedido | WooCommerce → Pedidos → pedido → acciones de correo nativas |

No se requiere modificar PHP para estas tareas. La estructura funcional de formularios y transporte pertenece a mu-plugins; la edición de contenido y operación diaria permanece en el panel.

## Arquitectura y módulos

- minikids-negocio.php: ajustes administrativos, datos públicos del proveedor, acceso a credencial externa, menú de atención y assets.
- minikids-correo.php: filtro pre_wp_mail con Brevo API HTTPS; respeta HTML/texto, CC/BCC, Reply-To y adjuntos. Registra aceptación/fallo sin guardar cuerpos ni destinatarios en logs. Usa remitente nativo WooCommerce; debe estar verificado en Brevo. No duplica plantillas Woo.
- minikids-reclamaciones.php: shortcode, validación servidor, folio, impresión/PDF, registro privado, copia consumidor/proveedor, estado y respuesta administrativa.
- minikids-atencion.css/js: tokens, formularios accesibles, campos16px, objetivos táctiles y validación progresiva. Sin overflow en ancestros sticky/drawer.
- minikeds-dev2-local.php: sigue bloqueando cobros externos. El correo se suprime mientras mkp_mail_enabled no sea yes; habilitar correo no activa pagos.
- minikids-mcp-abilities.php: conserva8 abilities y añade backup=true al esquema de escritura; rechaza desactivar respaldo y escribir fuera de mu-plugins.

## Correo de compra y HPOS

El checkout usa los correos transaccionales nativos de WooCommerce. Transferencia pendiente de pago genera resumen En espera para el cliente y Nuevo pedido para el administrador. Procesando/completado mantienen sus correos normales. Asuntos, textos y destinatarios se conservan en los ajustes Woo editables; no se incrustan en código ni se envían dos confirmaciones para el mismo cambio de estado.

El pedido se crea por Store API/CRUD y se almacena en HPOS. El celular adicional minikids/celular se copia al teléfono de facturación. No se cambian estados para fingir cobros. La aceptación de cada correo queda en metadatos propios del pedido; la entrega real se comprueba en Brevo.

Brevo: credencial en local-private/brevo.json (o ruta definida en MINIKIDS_BREVO_CONFIG), nunca en opciones MCP, SQL versionado ni código. La conexión previamente facilitada por el propietario se reutilizó por canal privado. Remitente/administrador actual vektronova@gmail.com. La cuenta recibió dos resúmenes QA, señalados como validación sin compra real; eventos Brevo delivered para ambos.

## Libro de Reclamaciones

URL /libro-de-reclamaciones/, enlace permanente en el pie. Proveedor confirmado: MIMI KIDS STORE E.I.R.L., RUC20609651530, Av. Alfredo Mendiola3698, Independencia, Lima, Perú. Correo y teléfono editables en Negocio.

Solicita consumidor, documento, domicilio, correo, teléfono, representante cuando es menor, identificación del bien/servicio, tipo reclamo/queja, monto/pedido opcionales, detalle y petición. Validación de nonce, límites de longitud, honeypot, idempotencia por token y límite de envíos por conexión. La hoja se guarda privada antes de intentar correo: un fallo de envío conserva el registro y permite reenviar desde administración.

El consumidor obtiene folio y constancia imprimible/guardable como PDF; recibe copia por correo y el proveedor recibe aviso. No existe colección REST pública ni URL de consulta de datos personales. Los correos de atención no se usan para publicidad. El administrador puede responder por correo desde la hoja y conservar fecha/resultado de aceptación, o registrar su atención manual.

El plazo mostrado de15 días hábiles se contrastó con [Indecopi: Libro de Reclamaciones](https://consumidor.gob.pe/libro-de-reclamaciones/). El módulo es el registro del proveedor, no una integración de envío al sistema de Indecopi.

## Edición MCP real y continuidad humana

tools/mcp_local.py inicializa el protocolo MCP y conserva Mcp-Session-Id por conexión. Utiliza el adaptador principal y las abilities minikids mediante mcp-adapter-execute-ability. Autenticación en local-private/mcp-auth.json, fuera del código.

Las páginas se leyeron antes de modificarlas con get-content/update-content. Se conservan bloques nativos y shortcode; se validó la apertura en Gutenberg sin bloques inválidos. Los cambios al archivo de estética y al esquema MCP se realizaron con write-file y respaldo automático. Los respaldos .bak están protegidos del servidor local y Apache.

El proxy wordpress-mcp alternativo se desactivó mediante la API de plugins porque generaba warnings de esquemas; MCP Adapter sigue activo y probado con las8 abilities. No se editó código de terceros.

## Pruebas y reproducción

- tools/test-attention-http.py: dos pasadas HTTP/Store API con sandbox de correo local, registro/doble envío, permisos, compra HPOS y correos cliente/admin. No habilita sandbox desde una petición pública; usa temporalmente configuración privada y la restaura en finally.
- tools/test-claim-admin.php: respuesta en sandbox, usuario sin permisos y nonce inválido.
- tools/test-attention-browser.mjs: dos pasadas360/390/768/1024/1280/1440, editor Gutenberg, panel, búsqueda y drawer Ajax. Capturas en wp-content/qa-evidencia, bloqueadas por el router local.
- tools/test-mail-delivery.php: una única prueba de entrega real a la cuenta del propietario, con guarda para no repetir. tools/check-mail-delivery.php consulta delivered.
- brain/*checks.json contiene resultados sin credenciales. Baseline de13747 archivos protegido en local-private/baseline-before-attention.json y respaldo SQL previo en local-private/backups.

No ejecutar herramientas iniciales ni pruebas repetidamente sobre pedidos reales. Las hojas QA se eliminan y los pedidos QA se cancelan mediante APIs, restituyendo stock. Los archivos privados y evidencias no forman parte de un despliegue público.
