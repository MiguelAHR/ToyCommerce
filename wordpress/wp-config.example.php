<?php
/**
 * Plantilla de configuración de WordPress para MiniKids.
 *
 * Copia este archivo como `wp-config.php` en la raíz de la instalación y
 * completa los valores con los de tu entorno. El `wp-config.php` real no se
 * versiona porque contiene credenciales y claves de seguridad.
 *
 * Genera las claves de seguridad en: https://api.wordpress.org/secret-key/1.1/salt/
 */

/* ---------- Base de datos ---------- */
define('DB_NAME',     'wordpress');
define('DB_USER',     'tu_usuario_de_base_de_datos');
define('DB_PASSWORD', 'tu_contrasena');
define('DB_HOST',     '127.0.0.1');
define('DB_CHARSET',  'utf8mb4');
define('DB_COLLATE',  '');

/* ---------- Claves de seguridad ---------- */
/* Reemplaza cada valor por uno único generado en el enlace de arriba. */
define('AUTH_KEY',         'genera-una-clave-unica-aqui');
define('SECURE_AUTH_KEY',  'genera-una-clave-unica-aqui');
define('LOGGED_IN_KEY',    'genera-una-clave-unica-aqui');
define('NONCE_KEY',        'genera-una-clave-unica-aqui');
define('AUTH_SALT',        'genera-una-clave-unica-aqui');
define('SECURE_AUTH_SALT', 'genera-una-clave-unica-aqui');
define('LOGGED_IN_SALT',   'genera-una-clave-unica-aqui');
define('NONCE_SALT',       'genera-una-clave-unica-aqui');

/* ---------- Prefijo de tablas ---------- */
$table_prefix = 'wp_';

/* ---------- Depuración ---------- */
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

/* ---------- Entorno local ---------- */
/* Ajusta estas dos URLs a la dirección donde sirvas el sitio. */
define('WP_HOME',    'http://localhost:8088/wordpress');
define('WP_SITEURL', 'http://localhost:8088/wordpress');
define('WP_ENVIRONMENT_TYPE', 'local');

/* ---------- Rutas absolutas ---------- */
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
