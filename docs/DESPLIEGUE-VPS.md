# Publicación de minikeds_dev2

Destino autorizado: https://minikids.vitorstream.shop/. El código se conserva en main de MiguelAHR/ToyCommerce. Publicar código en Git no publica los cambios de contenido de WordPress: páginas, productos, opciones y pedidos viven en la base de datos.

El runtime se prepara desde wordpress/, excluyendo configuración, SQL, documentos, herramientas, logs, evidencias y respaldos. La exportación SQL se guarda exclusivamente en local-private/deploy. No volver a importar esta copia local sobre una tienda con pedidos nuevos.

La versión ToyCommerce utiliza una base separada y una release bajo /opt/apps/projects/minikids/releases/. current apunta a la versión publicada. La versión anterior se conserva en previous-20261020 y su base minikids_dev permanece intacta. El respaldo previo y posterior está bajo backups, fuera del acceso web.

Configuración y credenciales de producción están fuera del runtime. El acceso administrativo y MCP son nuevos; la contraseña local no se publica. Ajustes > MiniKids · Negocio permite editar datos y credencial de correo; WooCommerce > Ajustes > Correos administra destinatarios y plantillas. Solo transferencia y contraentrega quedan habilitadas; las pasarelas externas requieren configuración real.

Las páginas siguen editándose en Gutenberg y los productos en WooCommerce. Pedidos usan HPOS. No se modifican archivos de WordPress, Blocksy ni plugins de terceros. Las URLs propias del pie y buscador se calculan desde home_url para admitir la subcarpeta local y el dominio raíz.

Verificación del despliegue:48/48 HTTP,68/68 navegador y14/14 de recursos/ficha móvil, con dos pasadas y seis anchos. Comparación de13729 archivos protegidos sin diferencias. Brevo reconoce la cuenta y MCP autentica correctamente; este despliegue no envió mensajes adicionales ni creó pedidos de compra.

Herramientas: package-production.py y export-production.php preparan archivos privados; vps-stage.sh crea una instalación aislada; production-configure.php usa APIs nativas para configurar accesos y pagos; vps-activate.sh respalda y cambia el enlace current con comprobación HTTP. Son procedimientos iniciales fechados, no una importación repetible sobre producción.

Restauración: detener temporalmente las escrituras, conservar la versión nueva y su DB, volver a apuntar current a previous-20261020, restaurar la configuración Nginx y el script de backup guardados en private, validar nginx -t y recargar Nginx/FPM. La configuración anterior conserva su propia base. Evaluar por separado cualquier pedido recibido después del cambio.
