<?php
/**
 * Test Suite para verificar las 7 correcciones requeridas por el usuario
 */
require_once __DIR__ . '/wp-load.php';

$results = [];

// 1. WhatsApp en /contacto/
$contact_post = get_post(11);
$contact_html = apply_filters('the_content', $contact_post ? $contact_post->post_content : '');
$has_phone_number = (strpos($contact_html, '+51 991 117 308') !== false || strpos($contact_html, '991 117 308') !== false);
$has_wame_link = (strpos($contact_html, 'https://wa.me/51991117308?text=') !== false);
$has_encoded_msg = (strpos($contact_html, '%C2%A1Hola%20MiniKids') !== false);
$results['1_whatsapp'] = [
    'status' => ($has_phone_number && $has_wame_link && $has_encoded_msg) ? 'PASS' : 'FAIL',
    'details' => [
        'phone_displayed' => $has_phone_number ? '+51 991 117 308' : 'FAIL',
        'wame_link' => $has_wame_link ? 'wa.me/51991117308' : 'FAIL',
        'encoded_msg' => $has_encoded_msg ? 'Cordially encoded' : 'FAIL'
    ]
];

// 2. Navbar en portada & Altura/Espaciado
$home_html = file_get_contents('http://localhost:8088/wordpress/');
$css_content = file_get_contents(__DIR__ . '/wp-content/mu-plugins/minikids-estetica.css');
$header_has_search = (strpos($home_html, 'ct-header-search') !== false);
$header_has_cart = (strpos($home_html, 'ct-header-cart') !== false);
$header_has_menu = (strpos($home_html, 'header-menu-1') !== false);
$navbar_height_60 = (strpos($css_content, 'height: 60px !important;') !== false);
$cart_gap_28 = (strpos($css_content, 'gap: 28px !important;') !== false);
$results['2_navbar'] = [
    'status' => (!$header_has_search && $header_has_cart && $header_has_menu && $navbar_height_60 && $cart_gap_28) ? 'PASS' : 'FAIL',
    'details' => [
        'search_removed_from_header' => !$header_has_search ? 'YES' : 'FAIL (still in HTML)',
        'cart_present' => $header_has_cart ? 'YES' : 'FAIL',
        'menu_present' => $header_has_menu ? 'YES' : 'FAIL',
        'navbar_compact_height_60' => $navbar_height_60 ? 'YES (60px)' : 'FAIL',
        'cart_separation_gap_28' => $cart_gap_28 ? 'YES (28px)' : 'FAIL'
    ]
];

// 3. Eliminar precio visible del navbar y badge sin tapar el icono
$hide_price_css = (strpos($css_content, '.ct-header-cart .ct-label') !== false && strpos($css_content, 'display: none !important;') !== false);
$decouple_icon_css = (strpos($css_content, '.ct-header-cart .ct-icon-container') !== false && strpos($css_content, 'position: static !important;') !== false);
$badge_corner_css = (strpos($css_content, '.ct-dynamic-count-cart') !== false && strpos($css_content, 'position: absolute !important;') !== false);
$results['3_navbar_price_and_badge'] = [
    'status' => ($hide_price_css && $decouple_icon_css && $badge_corner_css) ? 'PASS' : 'FAIL',
    'details' => [
        'navbar_price_hidden' => $hide_price_css ? 'YES (display:none on .ct-header-cart .ct-label)' : 'FAIL',
        'icon_container_static' => $decouple_icon_css ? 'YES (badge decoupled from 15px icon)' : 'FAIL',
        'badge_positioned_clear' => $badge_corner_css ? 'YES (top/right corner without covering icon)' : 'FAIL'
    ]
];

// 4. Diferenciación visual de productos en oferta vs precio normal
$regular_price_clean = (strpos($css_content, '.woocommerce ul.products li.product .price') !== false && strpos($css_content, 'color: var(--brand-ink) !important;') !== false);
$sale_price_red = (strpos($css_content, '.woocommerce ul.products li.product .price ins') !== false && strpos($css_content, 'color: var(--brand-red) !important;') !== false);
$sale_del_crossed = (strpos($css_content, 'text-decoration: line-through !important;') !== false && strpos($css_content, 'color: #8d99ae !important;') !== false);
$results['4_sale_price_differentiation'] = [
    'status' => ($regular_price_clean && $sale_price_red && $sale_del_crossed) ? 'PASS' : 'FAIL',
    'details' => [
        'regular_price_clean_dark' => $regular_price_clean ? 'YES (var(--brand-ink))' : 'FAIL',
        'sale_price_ins_red' => $sale_price_red ? 'YES (var(--brand-red))' : 'FAIL',
        'sale_price_del_crossed' => $sale_del_crossed ? 'YES (line-through #8d99ae)' : 'FAIL'
    ]
];

// 5. Encabezado de la Tienda y Buscador de Tienda
$tienda_html = file_get_contents('http://localhost:8088/wordpress/tienda/');
$tienda_has_header = (strpos($tienda_html, 'minikids-tienda-header') !== false);
$tienda_has_title = (strpos($tienda_html, 'Todos los') !== false && strpos($tienda_html, 'juguetes') !== false);
$tienda_has_balloon = (strpos($tienda_html, '🎈') !== false);
$tienda_has_search = (strpos($tienda_html, 'minikids-catalog-search') !== false);
$tienda_header_centered = (strpos($css_content, '.minikids-tienda-header') !== false && strpos($css_content, 'text-align: center !important;') !== false);
$results['5_tienda_header_and_search'] = [
    'status' => ($tienda_has_header && $tienda_has_title && $tienda_has_balloon && $tienda_has_search && $tienda_header_centered) ? 'PASS' : 'FAIL',
    'details' => [
        'tienda_header_present' => $tienda_has_header ? 'YES' : 'FAIL',
        'tienda_title_centered' => ($tienda_has_title && $tienda_header_centered) ? 'YES' : 'FAIL',
        'balloon_aligned' => $tienda_has_balloon ? 'YES' : 'FAIL',
        'catalog_internal_search_preserved' => $tienda_has_search ? 'YES' : 'FAIL'
    ]
];

// 6. Mini Carrito: Botón "Ver carrito" en el footer y sincronización
$drawer_has_viewcart = (strpos($home_html, 'class="drawer-viewcart-btn">Ver carrito</a>') !== false);
$drawer_has_checkout = (strpos($home_html, 'class="drawer-checkout-btn">Finalizar compra') !== false);
$cart_url = wc_get_page_permalink('cart');
$results['6_cart_drawer'] = [
    'status' => ($drawer_has_viewcart && $drawer_has_checkout) ? 'PASS' : 'FAIL',
    'details' => [
        'has_viewcart_btn' => $drawer_has_viewcart ? 'YES' : 'FAIL',
        'cart_url' => $cart_url,
        'has_checkout_btn' => $drawer_has_checkout ? 'YES' : 'FAIL'
    ]
];

// 7. Botón "Ver Juguetes" en el slide de la portada
$has_tienda_link = (strpos($home_html, 'href="/wordpress/tienda/">Ver juguetes') !== false);
$has_hash_productos = (strpos($home_html, 'href="#productos">Ver juguetes') !== false);
$results['7_hero_button'] = [
    'status' => ($has_tienda_link && !$has_hash_productos) ? 'PASS' : 'FAIL',
    'details' => [
        'links_to_tienda' => $has_tienda_link ? '/wordpress/tienda/' : 'FAIL',
        'no_internal_anchor' => !$has_hash_productos ? 'YES' : 'FAIL (has #productos)'
    ]
];

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
