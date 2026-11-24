<?php
/**
 * Plugin Name: MiniKids · Google Analytics 4 V19
 * Description: Snippet gtag.js GA4, consentimiento Ley 29733 y eventos de comercio electrónico.
 * Version: 1.0.0
 */
defined('ABSPATH') || exit;

function mk19_ga_id() {
    return trim((string) get_option('mk_ga4_id', 'G-EB0TZFNFK0'));
}

function mk19_ga_is_local() {
    $host = wp_parse_url(home_url('/'), PHP_URL_HOST);
    return wp_get_environment_type() === 'local' || in_array($host, ['localhost', '127.0.0.1', '::1'], true);
}

function mk19_ga_debug_requested() {
    return isset($_GET['mk_ga_debug']) && (string) $_GET['mk_ga_debug'] === '1';
}

function mk19_ga_should_send() {
    if (is_admin() || mk19_ga_id() === '' || current_user_can('manage_options')) {
        return false;
    }
    if (mk19_ga_is_local() && !mk19_ga_debug_requested()) {
        return false;
    }
    return true;
}

add_action('wp_head', function () {
    $id = mk19_ga_id();
    if (is_admin() || $id === '') {
        return;
    }
    if (current_user_can('manage_options')) {
        echo "<!-- MiniKids GA4: omitido para usuarios administradores -->\n";
        return;
    }
    $debug = mk19_ga_debug_requested();
    $active = mk19_ga_should_send();
    $snippet = '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr($id) . '"></script>' . "\n"
        . "<script>\n"
        . "window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}\n"
        . "(function(){\n"
        . "  try{var p=new URLSearchParams(location.search);if(p.get('utm_medium')==='test'||p.get('mk_test')==='1'){sessionStorage.setItem('mk_test','1');}}catch(e){}\n"
        . "  var consent='granted';try{if(localStorage.getItem('mk_ga_consent')==='denied')consent='denied';}catch(e){}\n"
        . "  gtag('consent','default',{analytics_storage:consent,ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied'});\n"
        . "  gtag('js',new Date());\n"
        . "  var cfg={send_page_view:true,currency:'PEN',language:'es-PE'" . ($debug ? ",debug_mode:true" : "") . "};\n"
        . "  try{if(sessionStorage.getItem('mk_test')==='1')cfg.traffic_type='test';}catch(e){}\n"
        . "  gtag('config','" . esc_js($id) . "',cfg);\n"
        . "})();\n"
        . "</script>\n";

    if (!$active) {
        $safe = str_replace('-->', '-- >', $snippet);
        echo "<!-- MiniKids GA4 (desactivado en entorno local; usar ?mk_ga_debug=1 para activar):\n" . $safe . "-->\n";
        return;
    }
    echo $snippet;
}, 1);

function mk19_ga_item($product, $qty = 1) {
    if (!$product instanceof WC_Product) {
        return null;
    }
    $sku = $product->get_sku() ?: (string) $product->get_id();
    $brand = $product->get_attribute('pa_marca') ?: $product->get_attribute('Marca') ?: 'MiniKids';
    $cats = get_the_terms($product->get_id(), 'product_cat');
    $cat = ($cats && !is_wp_error($cats)) ? $cats[0]->name : '';
    $price = round((float) wc_get_price_to_display($product), 2);
    $item = [
        'item_id'       => $sku,
        'item_name'     => $product->get_name(),
        'item_brand'    => $brand,
        'item_category' => $cat,
        'price'         => $price,
        'quantity'      => max(1, (int) $qty),
    ];
    if ($product->is_on_sale()) {
        $reg = (float) $product->get_regular_price();
        $sale = (float) $product->get_sale_price();
        if ($reg > $sale && $sale > 0) {
            $item['discount'] = round($reg - $sale, 2);
        }
    }
    return $item;
}

add_action('woocommerce_after_shop_loop_item', function () {
    global $product;
    if (!$product instanceof WC_Product) return;
    $item = mk19_ga_item($product, 1);
    if ($item) {
        echo '<span class="mk-ga-item" hidden data-mk-item="' . esc_attr(wp_json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '"></span>';
    }
}, 99);

function mk19_ga_order_is_sandbox($order) {
    if (!$order instanceof WC_Order) return true;
    if ($order->get_meta('_mk_sandbox') || $order->get_meta('_mk_demo')) return true;
    foreach ($order->get_meta_data() as $meta) {
        if (str_starts_with((string) $meta->key, '_mkpn_mail_') && is_array($meta->value) && !empty($meta->value['sandbox'])) {
            return true;
        }
    }
    return false;
}

add_action('wp_enqueue_scripts', function () {
    $id = mk19_ga_id();
    if ($id === '' || current_user_can('manage_options')) {
        return;
    }
    wp_enqueue_script(
        'minikids-analytics',
        plugins_url('minikids-analytics.js', __FILE__),
        ['jquery'],
        filemtime(__DIR__ . '/minikids-analytics.js'),
        true
    );

    $cart_items = [];
    $cart_map = [];
    $cart_value = 0.0;
    $cart_coupon = '';
    if (function_exists('WC') && WC()->cart) {
        foreach (WC()->cart->get_cart() as $key => $row) {
            if (empty($row['data']) || !$row['data'] instanceof WC_Product) continue;
            $item = mk19_ga_item($row['data'], (int) ($row['quantity'] ?? 1));
            if ($item) {
                $cart_items[] = $item;
                $cart_map[$key] = $item;
                $cart_map['id_' . $row['product_id']] = $item;
            }
        }
        $cart_value = round((float) WC()->cart->get_subtotal(), 2);
        $coupons = WC()->cart->get_applied_coupons();
        $cart_coupon = implode(',', $coupons);
    }

    $config = [
        'id'         => $id,
        'active'     => mk19_ga_should_send(),
        'debug'      => mk19_ga_debug_requested(),
        'currency'   => 'PEN',
        'privacyUrl' => get_privacy_policy_url() ?: home_url('/politica-privacidad/'),
        'isFront'    => is_front_page(),
        'isShop'     => function_exists('mk19_is_shop_view') ? mk19_is_shop_view() : is_page('tienda'),
        'isCategory' => function_exists('is_product_category') && is_product_category(),
        'isProduct'  => function_exists('is_product') && is_product(),
        'isCart'     => function_exists('is_cart') && is_cart(),
        'isCheckout' => function_exists('mk6_checkout') ? mk6_checkout() : (function_exists('is_checkout') && is_checkout() && !is_order_received_page()),
        'cartMap'    => $cart_map,
    ];

    if (function_exists('is_product') && is_product()) {
        $p = wc_get_product(get_queried_object_id());
        if ($p) {
            $config['product'] = mk19_ga_item($p, 1);
        }
    }
    if ($config['isCart']) {
        $config['cart'] = ['currency' => 'PEN', 'value' => $cart_value, 'items' => $cart_items];
    }
    if ($config['isCheckout']) {
        $config['checkout'] = ['currency' => 'PEN', 'value' => $cart_value, 'coupon' => $cart_coupon, 'items' => $cart_items];
    }
    $search_term = sanitize_text_field(wp_unslash($_GET['s'] ?? $_GET['q'] ?? $_GET['buscar'] ?? get_search_query()));
    if ($search_term !== '' && (is_search() || is_page('tienda'))) {
        $config['searchTerm'] = $search_term;
    }
    if (function_exists('is_order_received_page') && is_order_received_page()) {
        $order_id = absint(get_query_var('order-received'));
        $order = $order_id ? wc_get_order($order_id) : null;
        $key = sanitize_text_field(wp_unslash($_GET['key'] ?? ''));
        if ($order && $key !== '' && hash_equals($order->get_order_key(), $key) && !mk19_ga_order_is_sandbox($order) && $order->get_meta('_mk_ga_sent') !== '1') {
            $items = [];
            foreach ($order->get_items() as $line) {
                $prod = $line->get_product();
                if ($prod) {
                    $it = mk19_ga_item($prod, $line->get_quantity());
                    if ($it) $items[] = $it;
                }
            }
            $config['purchase'] = [
                'transaction_id' => (string) $order->get_order_number(),
                'value'          => round((float) $order->get_total(), 2),
                'tax'            => round((float) $order->get_total_tax(), 2),
                'shipping'       => round((float) $order->get_shipping_total(), 2),
                'currency'       => $order->get_currency() ?: 'PEN',
                'coupon'         => implode(',', $order->get_coupon_codes()),
                'items'          => $items,
            ];
            $order->update_meta_data('_mk_ga_sent', '1');
            $order->save();
        }
    }

    wp_localize_script('minikids-analytics', 'mkGaConfig', $config);
}, 270);

add_action('wp_footer', function () {
    if (is_admin() || mk19_ga_id() === '' || current_user_can('manage_options')) {
        return;
    }
    $privacy = get_privacy_policy_url() ?: home_url('/politica-privacidad/');
    ?>
    <aside id="mk19-consent" class="mk19-consent-bar" role="region" aria-label="Aviso de cookies" hidden>
        <p>Usamos cookies de analítica para mejorar la tienda. <a href="<?php echo esc_url($privacy); ?>">Más información</a></p>
        <div class="mk19-consent-actions">
            <button type="button" class="mk19-consent-accept" data-mk19-consent="granted">Entendido</button>
            <button type="button" class="mk19-consent-reject" data-mk19-consent="denied">Rechazar</button>
        </div>
    </aside>
    <?php
}, 95);
