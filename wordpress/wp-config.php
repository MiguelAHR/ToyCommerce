<?php
/**
 * Configuración de WordPress lista para XAMPP - MiniKids Perú
 *
 * Plug & Play: Preconfigurado para que el docente solo pegue la carpeta 'wordpress'
 * en su carpeta 'htdocs' de XAMPP e importe la base de datos sin necesidad de editar nada.
 */

// ** Ajustes de Base de Datos para XAMPP estándar ** //
define('DB_NAME',     'wordpress');
define('DB_USER',     'root');
define('DB_PASSWORD', '');
define('DB_HOST',     'localhost');
define('DB_CHARSET',  'utf8mb4');
define('DB_COLLATE',  '');

// ** Claves únicas de autenticación y salado ** //
define('AUTH_KEY',         'U:cJ=I;W{>B;6DyfC k(|nt31qQ0Dnu{mM~1}kE#+vac3:!,iSEW0p;2&6ApLXhL');
define('SECURE_AUTH_KEY',  '&Jb$Zs8|e-|OV}j1a[6Feh!-Qf=[r~s(nB+3<b:+nGw;dzY#W3JU}`^dONWoB-{|');
define('LOGGED_IN_KEY',    'm(prp1H#L7{PemVX?At$<s>5@R>~?,7(Zg|2ktTN-#-~x((g^[6d>9Hn5_|92sr=');
define('NONCE_KEY',        'w8pe};o?*|hK?ow+QAn1GLK|^Z!fOK2o#||[X):r8V`J4--f,$xR6E-^v|J}~wj<');
define('AUTH_SALT',        '<+<3ewR+[egX>l<Y$C%[of+KVZgvhiq.+t-n)ASN%hQjMr3wZ7M}?-@aC6jFoFPw');
define('SECURE_AUTH_SALT', '6eOEC9-/vOu_YJlpAIG9.s/+pC<sTmS<7 TzWsQ0P;C$^<H4>R#]I4!Q|l1bwuO^');
define('LOGGED_IN_SALT',   ')?v3%<&-$qxq9-sY`8N@1bY|ChrrS5iag8^&+~m:(0k>MR}qQxD|`<n9h}!uC(GY');
define('NONCE_SALT',       'Gpp|$^w6 <8GPo$b+gP>nYAk{=wM&~,o(B|)!|FCG)P}p+A)}5vry=Te/Uzgc5');

// ** Prefijo de tablas ** //
$table_prefix = 'wp_';

// ** Detección dinámica de puerto y dominio ** //
// Funciona automáticamente en puerto 80, 8080, 8088 o cualquier puerto de XAMPP sin tocar nada
if ( isset( $_SERVER['HTTP_HOST'] ) ) {
    $scheme = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ) ? 'https://' : 'http://';
    define( 'WP_HOME', $scheme . $_SERVER['HTTP_HOST'] . '/wordpress' );
    define( 'WP_SITEURL', $scheme . $_SERVER['HTTP_HOST'] . '/wordpress' );
} else {
    define( 'WP_HOME', 'http://localhost/wordpress' );
    define( 'WP_SITEURL', 'http://localhost/wordpress' );
}

// ** Entorno local ** //
define('WP_ENVIRONMENT_TYPE', 'local');
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

/* ¡Eso es todo, deja de editar! Feliz blogging. */

/** Ruta absoluta al directorio de WordPress. */
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

/** Establece las variables de WordPress y archivos incluidos. */
require_once ABSPATH . 'wp-settings.php';
