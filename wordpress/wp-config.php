<?php
/** WordPress configuration for the local MiniKids store. */
define('DB_NAME', 'wordpress');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
// Fuerza la conexión TCP al MySQL incluido en XAMPP (puerto 3306).
define('DB_HOST', '127.0.0.1:3306');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

define('AUTH_KEY',         'minikids-local-auth-key-2026-9a7f');
define('SECURE_AUTH_KEY',  'minikids-local-secure-auth-key-2026-4b2d');
define('LOGGED_IN_KEY',    'minikids-local-logged-in-key-2026-8c1e');
define('NONCE_KEY',        'minikids-local-nonce-key-2026-6d3a');
define('AUTH_SALT',        'minikids-local-auth-salt-2026-1f9b');
define('SECURE_AUTH_SALT', 'minikids-local-secure-auth-salt-2026-7e4c');
define('LOGGED_IN_SALT',   'minikids-local-logged-in-salt-2026-2a8d');
define('NONCE_SALT',       'minikids-local-nonce-salt-2026-5c6f');

$table_prefix = 'wp_';
define('WP_DEBUG', false);


define( 'WP_HOME', 'http://localhost:8088/wordpress' );
define( 'WP_SITEURL', 'http://localhost:8088/wordpress' );

// Entorno local: habilita los Application Passwords (API REST / MCP) sobre HTTP.
define( 'WP_ENVIRONMENT_TYPE', 'local' );


if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
require_once ABSPATH . 'wp-settings.php';
