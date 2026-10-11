<?php
/**
 * Plugin Name: MiniKids · Estética
 * Description: Carga la paleta, tipografías y estilos de tienda de MiniKids
 *              sobre la plantilla de WooCommerce.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) { exit; }

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'minikids-estetica',
        plugins_url('minikids-estetica.css', __FILE__),
        array(),
        filemtime(dirname(__FILE__) . '/minikids-estetica.css')
    );
}, 20);
