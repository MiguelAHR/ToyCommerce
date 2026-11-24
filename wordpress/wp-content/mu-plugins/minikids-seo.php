<?php
/**
 * Plugin Name: MiniKids · SEO Técnico V19
 * Description: Metadatos, Open Graph, Twitter Cards, JSON-LD, robots, sitemaps, 404 útil, compartir y ajustes SEO.
 * Version: 1.0.0
 */
defined('ABSPATH') || exit;

/* -------------------------------------------------------------------------
 * 1. Ajustes en Ajustes > MiniKids · Negocio
 * ---------------------------------------------------------------------- */
add_action('admin_init', function () {
    register_setting('mkpn_business', 'seo_home_desc', ['sanitize_callback' => 'sanitize_textarea_field']);
    register_setting('mkpn_business', 'seo_shop_desc', ['sanitize_callback' => 'sanitize_textarea_field']);
    register_setting('mkpn_business', 'mk_ga4_id', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('mkpn_business', 'seo_gsc_verification', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('mkpn_business', 'seo_bing_verification', ['sanitize_callback' => 'sanitize_text_field']);

    add_settings_section('mk19-seo', 'SEO y Analítica', function () {
        echo '<p>Configuración de descripciones SEO, Google Analytics 4 y códigos de verificación de buscadores.</p>';
    }, 'mkpn-business');

    add_settings_field('mk19-seo-home-desc', 'Descripción SEO portada (120–158 car.)', function () {
        $val = (string) get_option('seo_home_desc', 'Descubre juguetes didácticos, peluches, juegos de rol y regalos para niños en MiniKids Perú. Compra en línea con envíos a todo el país, gratis desde S/ 149.');
        echo '<textarea rows="3" class="large-text" name="seo_home_desc">' . esc_textarea($val) . '</textarea>';
    }, 'mkpn-business', 'mk19-seo');

    add_settings_field('mk19-seo-shop-desc', 'Descripción SEO tienda (120–158 car.)', function () {
        $val = (string) get_option('seo_shop_desc', 'Explora el catálogo completo de juguetes en Lima y todo el Perú: didácticos, peluches, vehículos y arte en MiniKids Perú. Envío gratis desde S/ 149.');
        echo '<textarea rows="3" class="large-text" name="seo_shop_desc">' . esc_textarea($val) . '</textarea>';
    }, 'mkpn-business', 'mk19-seo');

    add_settings_field('mk19-ga4-id', 'ID de Google Analytics 4', function () {
        $val = (string) get_option('mk_ga4_id', 'G-EB0TZFNFK0');
        echo '<input type="text" class="regular-text" name="mk_ga4_id" value="' . esc_attr($val) . '" placeholder="G-EB0TZFNFK0"><p class="description">Déjalo vacío para desactivar GA4.</p>';
    }, 'mkpn-business', 'mk19-seo');

    add_settings_field('mk19-gsc', 'Google Search Console (google-site-verification)', function () {
        $val = (string) get_option('seo_gsc_verification', '');
        echo '<input type="text" class="regular-text" name="seo_gsc_verification" value="' . esc_attr($val) . '">';
    }, 'mkpn-business', 'mk19-seo');

    add_settings_field('mk19-bing', 'Bing Webmaster (msvalidate.01)', function () {
        $val = (string) get_option('seo_bing_verification', '');
        echo '<input type="text" class="regular-text" name="seo_bing_verification" value="' . esc_attr($val) . '">';
    }, 'mkpn-business', 'mk19-seo');
});

/* -------------------------------------------------------------------------
 * 2. Encolado de estilos y diferimiento de scripts propios
 * ---------------------------------------------------------------------- */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'minikids-seo',
        plugins_url('minikids-seo.css', __FILE__),
        ['minikids-retail'],
        filemtime(__DIR__ . '/minikids-seo.css')
    );
}, 260);

add_filter('script_loader_tag', function ($tag, $handle, $src) {
    $defer_handles = [
        'minikids-analytics',
        'minikids-campanas-v4',
        'minikids-atencion',
        'minikids-commerce-v8',
        'minikids-commerce-v16',
        'minikids-commerce-v18',
    ];
    if (in_array($handle, $defer_handles, true) && !str_contains($tag, ' defer')) {
        $tag = str_replace(' src=', ' defer src=', $tag);
    }
    return $tag;
}, 20, 3);

/* -------------------------------------------------------------------------
 * 3. Helpers de título, descripción, canonical e indexabilidad
 * ---------------------------------------------------------------------- */
function mk19_clamp_title($prefix, $suffix = ' | MiniKids Perú', $max = 60) {
    $prefix = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags(html_entity_decode((string) $prefix, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
    if ($prefix === '') {
        return 'MiniKids Perú';
    }
    if (mb_strlen($prefix . $suffix, 'UTF-8') <= $max) {
        return $prefix . $suffix;
    }
    $avail = $max - mb_strlen($suffix, 'UTF-8') - 1;
    if ($avail < 12) {
        return mb_substr($prefix, 0, $max, 'UTF-8');
    }
    $cut = mb_substr($prefix, 0, $avail, 'UTF-8');
    $sp = mb_strrpos($cut, ' ', 0, 'UTF-8');
    if ($sp !== false && $sp >= 12) {
        $cut = mb_substr($cut, 0, $sp, 'UTF-8');
    }
    return rtrim($cut, " ,.;:-·") . '…' . $suffix;
}

function mk19_clamp_desc($raw, $tail = ' Envío a todo el Perú, gratis desde S/ 149.') {
    $text = html_entity_decode((string) $raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = wp_strip_all_tags($text);
    $text = str_replace(["\r", "\n", "\t", '•', '·', '★', '▪', '✓', '✔'], ' ', $text);
    $text = preg_replace('/(?:^|\s)[-–—]\s+/u', ' ', $text);
    $text = trim(preg_replace('/\s+/u', ' ', $text));
    if ($text === '') {
        $text = 'Descubre juguetes, peluches y regalos para niños en MiniKids Perú.';
    }
    if (mb_strlen($text, 'UTF-8') < 120) {
        $text = rtrim($text, '.') . '.' . $tail;
    }
    if (mb_strlen($text, 'UTF-8') < 120) {
        $text = rtrim($text, '.') . '. Compra seguro en línea en MiniKids Perú.';
    }
    if (mb_strlen($text, 'UTF-8') > 158) {
        $cut = mb_substr($text, 0, 155, 'UTF-8');
        $sp = mb_strrpos($cut, ' ', 0, 'UTF-8');
        if ($sp !== false && $sp >= 120) {
            $cut = mb_substr($cut, 0, $sp, 'UTF-8');
        }
        $text = rtrim($cut, " ,.;:-") . '...';
    }
    return $text;
}

function mk19_is_shop_view() {
    return (function_exists('is_shop') && is_shop()) || is_page('tienda');
}

function mk19_is_search_view() {
    return is_search() || (is_page('tienda') && (!empty($_GET['q']) || !empty($_GET['s']) || !empty($_GET['buscar'])));
}

function mk19_is_noindex() {
    if (function_exists('is_cart') && is_cart()) return true;
    if (function_exists('is_checkout') && is_checkout()) return true;
    if (function_exists('is_order_received_page') && is_order_received_page()) return true;
    if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url()) return true;
    if (function_exists('is_account_page') && is_account_page()) return true;
    if (function_exists('mk15_account_alias') && mk15_account_alias()) return true;
    if (mk19_is_search_view() || is_404() || is_page('test-hero')) return true;
    $blocked_params = ['orderby', 'min_price', 'max_price', 's', 'add-to-cart', 'q', 'buscar'];
    foreach ($_GET as $key => $val) {
        $k = (string) $key;
        if (in_array($k, $blocked_params, true) || str_starts_with($k, 'filter_')) {
            return true;
        }
    }
    return false;
}

function mk19_document_title() {
    if (is_front_page()) {
        return 'MiniKids Perú | Juguetes, peluches y regalos para niños';
    }
    if (function_exists('is_product') && is_product()) {
        return mk19_clamp_title(get_the_title());
    }
    if (function_exists('is_product_category') && is_product_category()) {
        return mk19_clamp_title(single_term_title('', false) . ' para niños');
    }
    if (mk19_is_search_view()) {
        $q = sanitize_text_field(wp_unslash($_GET['s'] ?? $_GET['q'] ?? $_GET['buscar'] ?? get_search_query()));
        return mk19_clamp_title($q !== '' ? 'Buscar: ' . $q : 'Buscar juguetes');
    }
    if (mk19_is_shop_view()) {
        return 'Tienda de juguetes en Lima | MiniKids Perú';
    }
    if (is_404()) {
        return 'Página no encontrada | MiniKids Perú';
    }
    return mk19_clamp_title(get_the_title());
}
add_filter('document_title_separator', fn() => '|', 99);
add_filter('pre_get_document_title', 'mk19_document_title', 99);

function mk19_meta_description() {
    if (is_front_page()) {
        $d = (string) get_option('seo_home_desc', 'Descubre juguetes didácticos, peluches, juegos de rol y regalos para niños en MiniKids Perú. Compra en línea con envíos a todo el país, gratis desde S/ 149.');
        return mk19_clamp_desc($d);
    }
    if (function_exists('is_product') && is_product()) {
        $p = wc_get_product(get_queried_object_id());
        if ($p) {
            $short = trim(wp_strip_all_tags($p->get_short_description()));
            if ($short !== '') {
                return mk19_clamp_desc($short);
            }
            $price = wc_format_decimal(wc_get_price_to_display($p), 2);
            return mk19_clamp_desc($p->get_name() . '. Desde S/ ' . $price . '. Envío a todo el Perú.');
        }
    }
    if (function_exists('is_product_category') && is_product_category()) {
        $term = get_queried_object();
        if ($term && !empty($term->description)) {
            return mk19_clamp_desc($term->description);
        }
        $name = $term ? $term->name : 'juguetes';
        return mk19_clamp_desc('Compra ' . $name . ' en MiniKids Perú. Envío a todo el país, gratis desde S/ 149.');
    }
    if (mk19_is_shop_view()) {
        $d = (string) get_option('seo_shop_desc', 'Explora el catálogo completo de juguetes en Lima y todo el Perú: didácticos, peluches, vehículos y arte en MiniKids Perú. Envío gratis desde S/ 149.');
        return mk19_clamp_desc($d);
    }
    if (is_404()) {
        return 'No encontramos la página que buscas en MiniKids Perú. Usa el buscador o explora nuestras categorías de juguetes y peluches con envíos a todo el país.';
    }
    $custom = [
        'nosotros' => 'Conoce MiniKids Perú, tienda online de juguetes, peluches y juegos educativos en Lima con envíos a todo el país y atención cercana por WhatsApp.',
        'contacto' => 'Contáctanos en MiniKids Perú para resolver dudas sobre juguetes, pedidos o envíos. Atención por WhatsApp +51 991 117 308 y correo electrónico.',
        'libro-de-reclamaciones' => 'Accede al Libro de Reclamaciones virtual de MIMI KIDS STORE E.I.R.L. (MiniKids Perú) y registra tu queja o reclamo con constancia inmediata.',
        'politica-privacidad' => 'Consulta la Política de Privacidad de MiniKids Perú sobre el tratamiento y protección de datos personales en nuestra tienda de juguetes en línea.',
        'terminos-y-condiciones' => 'Revisa los Términos y Condiciones de compra, envíos, cambios y garantías aplicables a tus pedidos en la tienda en línea de MiniKids Perú.',
        'cart' => 'Revisa los juguetes seleccionados en tu carrito de compras en MiniKids Perú. Envío gratis a todo el país en compras desde S/ 149.',
        'checkout' => 'Completa los datos de entrega y elige tu método de pago para finalizar tu compra segura de juguetes en MiniKids Perú.',
        'my-account' => 'Ingresa a tu cuenta de MiniKids Perú para consultar tus pedidos recientes, direcciones de envío y datos personales de compra.',
    ];
    if (is_page()) {
        $slug = get_post_field('post_name', get_queried_object_id());
        if (isset($custom[$slug])) {
            return mk19_clamp_desc($custom[$slug]);
        }
    }
    if (function_exists('mk15_account_alias') && mk15_account_alias()) {
        return mk19_clamp_desc($custom['my-account']);
    }
    $post = get_queried_object();
    if ($post instanceof WP_Post) {
        return mk19_clamp_desc($post->post_excerpt ?: $post->post_content);
    }
    return mk19_clamp_desc('');
}

function mk19_canonical_url() {
    if (is_404()) {
        return '';
    }
    $paged = max(1, (int) get_query_var('paged'));
    if (is_front_page()) {
        return home_url('/');
    }
    if (function_exists('is_product_category') && is_product_category()) {
        $term = get_queried_object();
        $link = $term ? get_term_link($term) : '';
        if (is_wp_error($link) || !$link) return '';
        return $paged > 1 ? trailingslashit($link) . 'page/' . $paged . '/' : $link;
    }
    if (mk19_is_shop_view()) {
        $tienda = get_page_by_path('tienda');
        $link = $tienda ? get_permalink($tienda) : home_url('/tienda/');
        return $paged > 1 ? trailingslashit($link) . 'page/' . $paged . '/' : $link;
    }
    if (function_exists('mk15_account_alias') && mk15_account_alias()) {
        return wc_get_page_permalink('myaccount');
    }
    if (is_singular()) {
        return get_permalink(get_queried_object_id());
    }
    return home_url(wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
}

/* -------------------------------------------------------------------------
 * 4. Metas <head>, Canonical, hreflang, OG/Twitter, Preload y Preconnect
 * ---------------------------------------------------------------------- */
add_action('init', function () {
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_shortlink_wp_head', 10);
    remove_action('template_redirect', 'wp_shortlink_header', 11);
    remove_action('wp_head', 'rel_canonical');
    add_filter('the_generator', '__return_empty_string');
});

add_filter('wp_robots', function ($robots) {
    if (mk19_is_noindex()) {
        return ['noindex' => true, 'follow' => true];
    }
    return $robots;
}, 99);

function mk19_og_image_data() {
    if (function_exists('is_product') && is_product()) {
        $product = wc_get_product(get_queried_object_id());
        $img_id = $product ? (int) $product->get_image_id() : 0;
        if ($img_id) {
            $file = get_attached_file($img_id);
            $meta = wp_get_attachment_metadata($img_id);
            $upload = wp_upload_dir();
            if ($file && is_file($file) && !empty($meta['file'])) {
                // Use non-WebP original when present for maximum social crawler compatibility.
                $url = trailingslashit($upload['baseurl']) . ltrim((string) $meta['file'], '/');
                $w = (int) ($meta['width'] ?? 1200);
                $h = (int) ($meta['height'] ?? 1200);
                $alt = get_post_meta($img_id, '_wp_attachment_image_alt', true) ?: $product->get_name();
                return ['url' => $url, 'width' => $w, 'height' => $h, 'alt' => $alt];
            }
        }
    }
    $default_id = (int) get_option('seo_og_default_id', 0);
    if ($default_id) {
        $file = get_attached_file($default_id);
        $meta = wp_get_attachment_metadata($default_id);
        $upload = wp_upload_dir();
        if ($file && is_file($file) && !empty($meta['file'])) {
            return [
                'url'    => trailingslashit($upload['baseurl']) . ltrim((string) $meta['file'], '/'),
                'width'  => (int) ($meta['width'] ?? 1200),
                'height' => (int) ($meta['height'] ?? 630),
                'alt'    => 'MiniKids Perú — Juguetes, peluches y regalos',
            ];
        }
    }
    return [
        'url'    => content_url('/uploads/minikids/og-default-1200x630.jpg'),
        'width'  => 1200,
        'height' => 630,
        'alt'    => 'MiniKids Perú — Juguetes, peluches y regalos',
    ];
}

add_action('wp_head', function () {
    $desc = mk19_meta_description();
    $canonical = mk19_canonical_url();
    $title = mk19_document_title();
    $og_img = mk19_og_image_data();
    $og_type = (function_exists('is_product') && is_product()) ? 'product' : 'website';

    echo '<meta name="theme-color" content="#ffd60a">' . "\n";
    echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";

    $gsc = trim((string) get_option('seo_gsc_verification', ''));
    if ($gsc !== '') {
        echo '<meta name="google-site-verification" content="' . esc_attr($gsc) . '">' . "\n";
    }
    $bing = trim((string) get_option('seo_bing_verification', ''));
    if ($bing !== '') {
        echo '<meta name="msvalidate.01" content="' . esc_attr($bing) . '">' . "\n";
    }

    if ($canonical !== '') {
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
        echo '<link rel="alternate" hreflang="es-pe" href="' . esc_url($canonical) . '">' . "\n";
        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($canonical) . '">' . "\n";
    }

    echo '<link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>' . "\n";
    echo '<link rel="preconnect" href="https://www.google-analytics.com" crossorigin>' . "\n";

    // LCP image preload on homepage and single product page
    if (is_front_page()) {
        $lcp = wp_get_attachment_image_url(246, 'full');
        if ($lcp) {
            if (function_exists('mka_webp_url')) $lcp = mka_webp_url($lcp);
            echo '<link rel="preload" as="image" href="' . esc_url($lcp) . '" fetchpriority="high">' . "\n";
        }
    } elseif (function_exists('is_product') && is_product()) {
        $p = wc_get_product(get_queried_object_id());
        $img_id = $p ? (int) $p->get_image_id() : 0;
        if ($img_id) {
            $lcp = wp_get_attachment_image_url($img_id, 'woocommerce_single') ?: wp_get_attachment_image_url($img_id, 'full');
            if ($lcp) {
                if (function_exists('mka_webp_url')) $lcp = mka_webp_url($lcp);
                echo '<link rel="preload" as="image" href="' . esc_url($lcp) . '" fetchpriority="high">' . "\n";
            }
        }
    }

    // Open Graph & Twitter Card
    echo '<meta property="og:type" content="' . esc_attr($og_type) . '">' . "\n";
    echo '<meta property="og:site_name" content="MiniKids Perú">' . "\n";
    echo '<meta property="og:locale" content="es_PE">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
    if ($canonical !== '') {
        echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
    }
    echo '<meta property="og:image" content="' . esc_url($og_img['url']) . '">' . "\n";
    echo '<meta property="og:image:width" content="' . esc_attr((string) $og_img['width']) . '">' . "\n";
    echo '<meta property="og:image:height" content="' . esc_attr((string) $og_img['height']) . '">' . "\n";
    echo '<meta property="og:image:alt" content="' . esc_attr($og_img['alt']) . '">' . "\n";

    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url($og_img['url']) . '">' . "\n";
    echo '<meta name="twitter:image:alt" content="' . esc_attr($og_img['alt']) . '">' . "\n";
}, 5);

// Ensure first LCP image on homepage & single product gets fetchpriority=high and loading=eager
add_filter('render_block_core/image', function ($html, $block) {
    if (!is_front_page()) return $html;
    static $first_done = false;
    if (!$first_done && str_contains($block['attrs']['className'] ?? '', 'mk4-image')) {
        $first_done = true;
        $proc = new WP_HTML_Tag_Processor($html);
        if ($proc->next_tag('IMG')) {
            $proc->set_attribute('fetchpriority', 'high');
            $proc->set_attribute('loading', 'eager');
            return $proc->get_updated_html();
        }
    }
    return $html;
}, 40, 2);

add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment) {
    if (function_exists('is_product') && is_product() && $attachment) {
        $p = wc_get_product(get_queried_object_id());
        if ($p && (int) $p->get_image_id() === (int) $attachment->ID) {
            $attr['fetchpriority'] = 'high';
            $attr['loading'] = 'eager';
        }
    }
    return $attr;
}, 40, 2);

/* -------------------------------------------------------------------------
 * 5. JSON-LD único por página (sin reseñas _mk_demo y sin duplicados Woo)
 * ---------------------------------------------------------------------- */
add_filter('woocommerce_structured_data_product', '__return_empty_array', 999);
add_filter('woocommerce_structured_data_breadcrumblist', '__return_empty_array', 999);
add_filter('woocommerce_structured_data_website', '__return_empty_array', 999);
add_action('wp_footer', function () {
    if (function_exists('WC') && isset(WC()->structured_data)) {
        remove_action('wp_footer', [WC()->structured_data, 'output_structured_data'], 10);
    }
}, 1);

function mk19_real_product_reviews($product_id) {
    $comments = get_comments([
        'post_id'    => $product_id,
        'type'       => 'review',
        'status'     => 'approve',
        'number'     => 0,
        'meta_query' => [
            'relation' => 'OR',
            ['key' => '_mk_demo', 'compare' => 'NOT EXISTS'],
            ['key' => '_mk_demo', 'value' => '1', 'compare' => '!='],
        ],
    ]);
    $clean = [];
    foreach ($comments as $c) {
        if (get_comment_meta($c->comment_ID, '_mk_demo', true)) {
            continue;
        }
        $rating = (int) get_comment_meta($c->comment_ID, 'rating', true);
        if ($rating >= 1 && $rating <= 5) {
            $clean[] = ['comment' => $c, 'rating' => $rating];
        }
    }
    return $clean;
}

add_action('wp_footer', function () {
    if (is_admin()) return;
    $business = (array) get_option('mkp_business', []);
    $logo_url = content_url('/uploads/minikids/logo-minikids.png');
    $og_img = mk19_og_image_data()['url'];
    $org_id = home_url('/#organization');
    $web_id = home_url('/#website');

    $org = [
        '@type' => 'Organization',
        '@id'   => $org_id,
        'name'  => 'MiniKids Perú',
        'url'   => home_url('/'),
        'logo'  => [
            '@type' => 'ImageObject',
            'url'   => $logo_url,
        ],
    ];
    if (!empty($business['name'])) {
        $org['legalName'] = (string) $business['name'];
    }
    $phone = trim((string) ($business['phone'] ?? ''));
    $email = trim((string) ($business['email'] ?? ''));
    if ($phone !== '' || $email !== '') {
        $contact = [
            '@type'             => 'ContactPoint',
            'contactType'       => 'customer service',
            'areaServed'        => 'PE',
            'availableLanguage' => 'es',
        ];
        if ($phone !== '') $contact['telephone'] = $phone;
        if ($email !== '') $contact['email'] = $email;
        $org['contactPoint'] = [$contact];
    }

    $website = [
        '@type'           => 'WebSite',
        '@id'             => $web_id,
        'url'             => home_url('/'),
        'name'            => 'MiniKids Perú',
        'publisher'       => ['@id' => $org_id],
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => home_url('/?s={search_term_string}'),
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];

    $graph = [$org, $website];

    if (is_front_page() || is_page('contacto')) {
        $store = [
            '@type' => 'Store',
            '@id'   => home_url('/#store'),
            'name'  => 'MiniKids Perú',
            'url'   => home_url('/'),
            'image' => $og_img,
        ];
        if ($phone !== '') $store['telephone'] = $phone;
        if ($email !== '') $store['email'] = $email;
        $addr = trim((string) ($business['address'] ?? ''));
        if ($addr !== '') {
            $store['address'] = [
                '@type'           => 'PostalAddress',
                'streetAddress'   => 'Av. Alfredo Mendiola 3698',
                'addressLocality' => 'Independencia',
                'addressRegion'   => 'Lima',
                'addressCountry'  => 'PE',
            ];
            if (!str_contains($addr, '3698')) {
                $store['address']['streetAddress'] = $addr;
            }
        }
        $graph[] = $store;
    }

    if (function_exists('is_product') && is_product()) {
        $p = wc_get_product(get_queried_object_id());
        if ($p) {
            $purl = get_permalink($p->get_id());
            $images = [];
            $main_id = (int) $p->get_image_id();
            if ($main_id) {
                $u = wp_get_attachment_image_url($main_id, 'full');
                if ($u) $images[] = $u;
            }
            foreach ($p->get_gallery_image_ids() as $gid) {
                $u = wp_get_attachment_image_url($gid, 'full');
                if ($u && !in_array($u, $images, true)) $images[] = $u;
            }
            if (!$images) $images[] = $og_img;

            $brand = $p->get_attribute('pa_marca') ?: $p->get_attribute('Marca') ?: 'MiniKids';
            $desc_raw = $p->get_short_description() ?: $p->get_description();
            $desc_clean = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags(html_entity_decode((string) $desc_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));

            $price = wc_format_decimal(wc_get_price_to_display($p), 2);
            $offer = [
                '@type'         => 'Offer',
                'url'           => $purl,
                'priceCurrency' => 'PEN',
                'price'         => $price,
                'availability'  => $p->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'seller'        => ['@id' => $org_id],
            ];
            if ($p->is_on_sale()) {
                $to = $p->get_date_on_sale_to();
                if ($to) {
                    $offer['priceValidUntil'] = $to->date('Y-m-d');
                }
                $reg = (float) $p->get_regular_price();
                $sale = (float) $p->get_sale_price();
                if ($reg > $sale && $sale > 0) {
                    $offer['priceSpecification'] = [
                        '@type'         => 'UnitPriceSpecification',
                        'priceType'     => 'https://schema.org/StrikethroughPrice',
                        'price'         => wc_format_decimal(wc_get_price_to_display($p, ['price' => $reg]), 2),
                        'priceCurrency' => 'PEN',
                    ];
                }
            }

            $prod_schema = [
                '@type'       => 'Product',
                '@id'         => $purl . '#product',
                'name'        => $p->get_name(),
                'image'       => $images,
                'description' => $desc_clean ?: $p->get_name(),
                'brand'       => ['@type' => 'Brand', 'name' => $brand],
                'offers'      => $offer,
            ];
            if ($p->get_sku()) {
                $prod_schema['sku'] = $p->get_sku();
            }
            $mpn = $p->get_meta('_mpn') ?: $p->get_meta('mpn') ?: $p->get_attribute('pa_mpn');
            if ($mpn) {
                $prod_schema['mpn'] = (string) $mpn;
            }

            // ONLY real reviews (without _mk_demo=1). Never mark seeded demo reviews.
            $real_reviews = mk19_real_product_reviews($p->get_id());
            if (!empty($real_reviews)) {
                $sum = array_sum(array_column($real_reviews, 'rating'));
                $cnt = count($real_reviews);
                $prod_schema['aggregateRating'] = [
                    '@type'       => 'AggregateRating',
                    'ratingValue' => number_format($sum / $cnt, 1, '.', ''),
                    'reviewCount' => $cnt,
                ];
                $prod_schema['review'] = [];
                foreach (array_slice($real_reviews, 0, 10) as $row) {
                    $c = $row['comment'];
                    $prod_schema['review'][] = [
                        '@type'        => 'Review',
                        'author'       => ['@type' => 'Person', 'name' => $c->comment_author],
                        'datePublished'=> mysql2date('c', $c->comment_date_gmt),
                        'reviewBody'   => wp_strip_all_tags($c->comment_content),
                        'reviewRating' => [
                            '@type'       => 'Rating',
                            'ratingValue' => $row['rating'],
                            'bestRating'  => 5,
                            'worstRating' => 1,
                        ],
                    ];
                }
            }

            $graph[] = $prod_schema;

            $crumbs = [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => home_url('/')],
            ];
            $cats = get_the_terms($p->get_id(), 'product_cat');
            if ($cats && !is_wp_error($cats)) {
                $cat = $cats[0];
                $cat_link = get_term_link($cat);
                if (!is_wp_error($cat_link)) {
                    $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $cat->name, 'item' => $cat_link];
                }
            }
            $crumbs[] = ['@type' => 'ListItem', 'position' => count($crumbs) + 1, 'name' => $p->get_name(), 'item' => $purl];
            $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => $crumbs];
        }
    } elseif ((function_exists('is_product_category') && is_product_category()) || mk19_is_shop_view()) {
        $canonical = mk19_canonical_url();
        $is_cat = function_exists('is_product_category') && is_product_category();
        $term = $is_cat ? get_queried_object() : null;
        $col_name = $term ? $term->name : 'Tienda de juguetes en Lima';
        $args = ['status' => 'publish', 'limit' => 24, 'orderby' => 'menu_order', 'order' => 'ASC'];
        if ($term) {
            $args['category'] = [$term->slug];
        }
        $list_items = [];
        $pos = 1;
        foreach (wc_get_products($args) as $prod) {
            $img_url = $prod->get_image_id() ? wp_get_attachment_image_url($prod->get_image_id(), 'woocommerce_thumbnail') : $og_img;
            $list_items[] = [
                '@type'    => 'ListItem',
                'position' => $pos++,
                'url'      => get_permalink($prod->get_id()),
                'name'     => $prod->get_name(),
                'image'    => $img_url,
            ];
        }
        $graph[] = [
            '@type'       => 'CollectionPage',
            '@id'         => $canonical . '#collection',
            'name'        => $col_name,
            'description' => mk19_meta_description(),
            'url'         => $canonical,
            'mainEntity'  => ['@id' => $canonical . '#itemlist'],
        ];
        $graph[] = [
            '@type'           => 'ItemList',
            '@id'             => $canonical . '#itemlist',
            'numberOfItems'   => count($list_items),
            'itemListElement' => $list_items,
        ];
        if ($is_cat && $term) {
            $tienda_url = get_permalink(get_page_by_path('tienda')) ?: home_url('/tienda/');
            $graph[] = [
                '@type'           => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => home_url('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Tienda', 'item' => $tienda_url],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $term->name, 'item' => $canonical],
                ],
            ];
        }
    }

    $payload = ['@context' => 'https://schema.org', '@graph' => $graph];
    echo '<script type="application/ld+json">' . wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}, 90);

/* -------------------------------------------------------------------------
 * 6. Un solo H1, categorías nativas con intro SEO y botones de compartir
 * ---------------------------------------------------------------------- */
add_filter('blocksy:hero:custom-source', function ($source) {
    if (is_front_page() || is_page('tienda') || is_page('contacto') || (function_exists('is_product_category') && is_product_category())) {
        return false;
    }
    return $source;
}, 20);

add_filter('body_class', function ($classes) {
    if (function_exists('is_product_category') && is_product_category()) {
        $classes[] = 'mk19-category-page';
    }
    return $classes;
}, 20);

add_action('woocommerce_before_shop_loop', function () {
    if (!function_exists('is_product_category') || !is_product_category()) return;
    $term = get_queried_object();
    if (!$term) return;
    $tienda_url = get_permalink(get_page_by_path('tienda')) ?: home_url('/tienda/');
    echo '<header class="mk19-cat-header">';
    echo '<nav class="mkr-breadcrumb" aria-label="Ruta de categoría"><a href="' . esc_url(home_url('/')) . '">Inicio</a> <span aria-hidden="true">/</span> <a href="' . esc_url($tienda_url) . '">Tienda</a> <span aria-hidden="true">/</span> <span>' . esc_html($term->name) . '</span></nav>';
    echo '<h1>' . esc_html($term->name) . '</h1>';
    $desc = trim((string) term_description($term));
    if ($desc !== '') {
        echo '<div class="mk19-cat-intro">' . wp_kses_post($desc) . '</div>';
    }
    echo '</header>';
}, 5);

// Botones de compartir en ficha de producto (zona de compra, tras la caja de enlaces .mk8-help)
add_action('woocommerce_single_product_summary', function () {
    global $product;
    if (!$product) return;
    $url = get_permalink($product->get_id());
    $name = $product->get_name();
    $sku = $product->get_sku() ?: (string) $product->get_id();
    $wa_url = 'https://wa.me/?text=' . rawurlencode($name . ' — ' . $url);
    $fb_url = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($url);
    ?>
    <div class="mk19-share" aria-label="Compartir producto">
        <div class="mk19-share-head">
            <p class="mk19-share-title">Compartir</p>
            <span class="mk19-share-status" role="status" aria-live="polite"></span>
        </div>
        <div class="mk19-share-actions">
            <a class="mk19-share-btn" href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="noopener" aria-label="Compartir <?php echo esc_attr($name); ?> por WhatsApp" data-mk-share="whatsapp" data-mk-item-id="<?php echo esc_attr($sku); ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-12.6 7.4L3 20.5l1.6-5.2A8.5 8.5 0 1 1 21 11.5Z"/><path d="M9.5 8.8c0 3.2 2.5 5.7 5.7 5.7l1.1-1.9-1.9-.9-.7.7a5 5 0 0 1-2.4-2.4l.7-.7-.9-1.9-1.6.4Z"/></svg>
                <span>WhatsApp</span>
            </a>
            <a class="mk19-share-btn" href="<?php echo esc_url($fb_url); ?>" target="_blank" rel="noopener" aria-label="Compartir <?php echo esc_attr($name); ?> en Facebook" data-mk-share="facebook" data-mk-item-id="<?php echo esc_attr($sku); ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3Z"/></svg>
                <span>Facebook</span>
            </a>
            <button type="button" class="mk19-share-btn" aria-label="Copiar enlace de <?php echo esc_attr($name); ?>" data-mk-share="copy" data-mk-share-url="<?php echo esc_url($url); ?>" data-mk-share-title="<?php echo esc_attr($name); ?>" data-mk-item-id="<?php echo esc_attr($sku); ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.1 0l2.8-2.8a5 5 0 0 0-7.1-7.1L11.2 4.7"/><path d="M14 11a5 5 0 0 0-7.1 0l-2.8 2.8a5 5 0 0 0 7.1 7.1l1.6-1.6"/></svg>
                <span>Copiar enlace</span>
            </button>
        </div>
    </div>
    <?php
}, 110);

/* -------------------------------------------------------------------------
 * 7. Página 404 útil, robots.txt, sitemaps y favicon.ico
 * ---------------------------------------------------------------------- */
add_filter('blocksy:404:custom-output', function () {
    $cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true]);
    ob_start();
    ?>
    <div class="ct-container">
        <section class="mk19-404" aria-labelledby="mk19-404-title">
            <h1 id="mk19-404-title">No encontramos esta página</h1>
            <p>La dirección que buscas no existe o cambió de lugar. Puedes buscar un juguete o explorar nuestras categorías:</p>
            <form class="mk19-404-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <label class="screen-reader-text" for="mk19-404-input">Buscar juguetes</label>
                <input id="mk19-404-input" type="search" name="s" placeholder="Buscar juguetes, peluches o juegos..." required>
                <button type="submit">Buscar</button>
            </form>
            <?php if ($cats && !is_wp_error($cats)): ?>
                <ul class="mk19-404-cats">
                    <?php foreach ($cats as $cat): ?>
                        <li><a href="<?php echo esc_url(get_term_link($cat)); ?>"><?php echo esc_html($cat->name); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <a class="mk19-404-home" href="<?php echo esc_url(home_url('/')); ?>">Volver al inicio</a>
        </section>
    </div>
    <?php
    return ob_get_clean();
});

add_action('parse_request', function ($wp) {
    $req_path = trim((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    $base_path = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');
    $rel = ($base_path !== '' && str_starts_with($req_path, $base_path)) ? ltrim(substr($req_path, strlen($base_path)), '/') : $req_path;
    if ($rel === 'robots.txt') {
        $wp->query_vars = ['robots' => '1'];
    } elseif ($rel === 'favicon.ico') {
        $icon = get_site_icon_url(32);
        if ($icon) {
            wp_safe_redirect($icon, 302);
            exit;
        }
    }
}, 1);

add_action('do_faviconico', function () {
    $icon = get_site_icon_url(32);
    if ($icon) {
        wp_safe_redirect($icon, 302);
        exit;
    }
}, 1);

add_filter('robots_txt', function ($output, $public) {
    if (!$public) return $output;
    $rules = [
        'Disallow: /cart/',
        'Disallow: /checkout/',
        'Disallow: /my-account/',
        'Disallow: /mi-cuenta/',
        'Disallow: /*?orderby=',
        'Disallow: /*?min_price=',
        'Disallow: /*?filter_',
    ];
    $add = '';
    foreach ($rules as $rule) {
        if (!str_contains($output, $rule)) {
            $add .= $rule . "\n";
        }
    }
    if ($add !== '') {
        if (str_contains($output, "Sitemap:")) {
            $output = preg_replace('/(?=Sitemap:)/', $add . "\n", $output, 1);
        } else {
            $output = rtrim($output) . "\n" . $add . "\nSitemap: " . home_url('/wp-sitemap.xml') . "\n";
        }
    }
    return $output;
}, 99, 2);

add_filter('wp_sitemaps_posts_query_args', function ($args, $post_type) {
    if ($post_type === 'page') {
        $exclude = array_filter([
            function_exists('wc_get_page_id') ? (int) wc_get_page_id('cart') : 0,
            function_exists('wc_get_page_id') ? (int) wc_get_page_id('checkout') : 0,
            function_exists('wc_get_page_id') ? (int) wc_get_page_id('myaccount') : 0,
            function_exists('wc_get_page_id') ? (int) wc_get_page_id('shop') : 0,
            ($th = get_page_by_path('test-hero')) ? (int) $th->ID : 0,
        ]);
        $args['post__not_in'] = array_values(array_unique(array_merge((array) ($args['post__not_in'] ?? []), $exclude)));
    }
    return $args;
}, 20, 2);

add_filter('wp_sitemaps_taxonomies_entry', function ($entry, $term_id, $taxonomy, $term) {
    if ($taxonomy === 'product_cat' && $term_id) {
        $latest = get_posts([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'orderby'        => 'modified',
            'order'          => 'DESC',
            'tax_query'      => [[
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => (int) $term_id,
            ]],
        ]);
        if (!empty($latest[0])) {
            $entry['lastmod'] = wp_date(DATE_W3C, strtotime($latest[0]->post_modified_gmt));
        }
    }
    return $entry;
}, 20, 4);
