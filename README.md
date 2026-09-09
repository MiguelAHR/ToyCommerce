# ToyCommerce

Implementación de la tienda MiniKids como páginas administrables de WordPress.

## Contenido versionado

- `wordpress/wp-content/`: contenido desplegable de WordPress: plugins, temas, idiomas y recursos del proyecto.
- `wordpress/wp-content/plugins/minikids-pages/`: plugin que aporta el diseño público, estilos para el editor y el carrito local.
- `wordpress/wp-config.example.php`: plantilla de configuración sin secretos.
- `database/wordpress-sanitized.sql`: estructura y contenido de la base de datos, excluyendo usuarios, hashes y claves.
- `tools/install-minikids-pages.php`: crea o actualiza las páginas Inicio, Tienda, Nosotros y Contacto usando bloques nativos de Gutenberg.

No se incluye el core de WordPress ni un `wp-config.php` real. Las credenciales, salts y el contenido privado deben existir solo en el entorno de despliegue. El SQL incluido está sanitizado y no contiene cuentas ni secretos.

## Instalación

1. Crear la base de datos y cargar `database/wordpress-sanitized.sql`.
2. Copiar `wordpress/wp-config.example.php` como `wp-config.php` y definir las credenciales y salts del entorno.
3. Copiar `wordpress/wp-content/` a la instalación de WordPress.
4. Activar **MiniKids Pages** desde WordPress → Plugins.
5. Ejecutar `tools/install-minikids-pages.php` mediante WP-CLI, PHP con WordPress cargado o adaptar su ruta de carga a la instalación destino.
6. Abrir WordPress → Páginas. Cada página queda compuesta por bloques editables de Grupo, Columnas, Encabezado, Párrafo, Imagen y Botón; no usa shortcodes ni un bloque dinámico que guarde el contenido en PHP.

El frontend conserva la página completa y su CSS público. Los textos, imágenes, botones y grupos se pueden editar directamente desde Gutenberg.
