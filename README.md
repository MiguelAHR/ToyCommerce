# ToyCommerce

Implementación de la tienda MiniKids como páginas administrables de WordPress.

## Contenido versionado

- `wordpress-plugin/minikids-pages/`: plugin que aporta el diseño público, estilos para el editor y el carrito local.
- `tools/install-minikids-pages.php`: crea o actualiza las páginas Inicio, Tienda, Nosotros y Contacto usando bloques nativos de Gutenberg.

No se incluye el core de WordPress, `wp-config.php`, la base de datos ni `uploads/`. Esos elementos dependen de cada servidor y no deben subirse al repositorio.

## Instalación

1. Copiar `wordpress-plugin/minikids-pages` a `wp-content/plugins/`.
2. Activar **MiniKids Pages** desde WordPress → Plugins.
3. Ejecutar `tools/install-minikids-pages.php` mediante WP-CLI, PHP con WordPress cargado o adaptar su ruta de carga a la instalación destino.
4. Abrir WordPress → Páginas. Cada página queda compuesta por bloques editables de Grupo, Columnas, Encabezado, Párrafo, Imagen y Botón; no usa shortcodes ni un bloque dinámico que guarde el contenido en PHP.

El frontend conserva la página completa y su CSS público. Los textos, imágenes, botones y grupos se pueden editar directamente desde Gutenberg.
