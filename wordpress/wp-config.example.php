<?php
/**
 * Copy this file as wp-config.php in the deployment environment.
 * Never commit a real wp-config.php with credentials or WordPress salts.
 */

define('DB_NAME', getenv('WORDPRESS_DB_NAME') ?: 'wordpress');
define('DB_USER', getenv('WORDPRESS_DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD') ?: '');
define('DB_HOST', getenv('WORDPRESS_DB_HOST') ?: '127.0.0.1:3306');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

define('AUTH_KEY',         'replace-with-a-unique-secret');
define('SECURE_AUTH_KEY',  'replace-with-a-unique-secret');
define('LOGGED_IN_KEY',    'replace-with-a-unique-secret');
define('NONCE_KEY',        'replace-with-a-unique-secret');
define('AUTH_SALT',        'replace-with-a-unique-secret');
define('SECURE_AUTH_SALT', 'replace-with-a-unique-secret');
define('LOGGED_IN_SALT',   'replace-with-a-unique-secret');
define('NONCE_SALT',       'replace-with-a-unique-secret');

$table_prefix = 'wp_';
define('WP_DEBUG', false);

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
require_once ABSPATH . 'wp-settings.php';
