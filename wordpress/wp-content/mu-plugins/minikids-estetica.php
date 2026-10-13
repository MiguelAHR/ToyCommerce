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

/**
 * Correcciones aditivas: guard de desbordamiento horizontal en la
 * portada y pulido del feedback "Ver carrito" de WooCommerce.
 * Se carga después de la hoja principal, nunca la sustituye.
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'minikids-bloques',
        plugins_url('minikids-bloques.css', __FILE__),
        array('minikids-estetica'),
        filemtime(dirname(__FILE__) . '/minikids-bloques.css')
    );
}, 30);
