<?php
/**
 * Plugin Name: MiniKids · Redirección de tienda
 * Description: Redirige la tienda de WooCommerce (/shop/) al catálogo /tienda/,
 *              que sí renderiza los productos.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) { exit; }

add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || is_404()) {
        return;
    }
    // La pagina de tienda de WooCommerce se muestra vacia: llevamos al catalogo real.
    if (function_exists('is_shop') && is_shop() && !is_search() && !is_filtered()) {
        wp_safe_redirect(home_url('/tienda/'), 301);
        exit;
    }
}, 5);
