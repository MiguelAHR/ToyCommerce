<?php
/** Plugin Name: MiniKids · Páginas legales y métodos de pago */
if (!defined('ABSPATH')) exit;

// [mk_negocio campo="name|ruc|address|email|phone"] : dato editable en Ajustes > MiniKids · Negocio.
add_shortcode('mk_negocio', function ($atts) {
    $field = shortcode_atts(['campo' => 'name'], $atts)['campo'];
    $data = (array) get_option('mkp_business', []);
    $value = $data[$field] ?? '';
    if ($field === 'email' && is_email($value)) return '<a href="mailto:' . esc_attr($value) . '">' . esc_html($value) . '</a>';
    return esc_html($value);
});

// [mk_metodos_pago] : solo los medios disponibles en la tienda (misma fuente que el checkout).
add_shortcode('mk_metodos_pago', function () {
    if (!function_exists('WC') || !WC()->payment_gateways()) return '';
    $info = [
        'bacs' => ['Realiza el abono desde tu banco o aplicación bancaria e indica tu número de pedido como referencia. Te confirmamos los datos de la cuenta por nuestros canales oficiales y despachamos cuando se verifica el abono.', '<path d="m2 9 10-6 10 6ZM4 11v8m5-8v8m6-8v8m5-8v8M2 21h20"/>'],
        'cod'  => ['Pagas en efectivo cuando recibes tu pedido, según la disponibilidad de esta opción para tu dirección.', '<path d="M3 6h18v12H3z"/><circle cx="12" cy="12" r="3"/><path d="M6 9v0m12 6v0"/>'],
    ];
    $out = '';
    foreach (WC()->payment_gateways()->get_available_payment_gateways() as $id => $gateway) {
        $title = trim(preg_replace('/\s*\(prueba local\)\s*/iu', '', $gateway->get_title()));
        $text = $info[$id][0] ?? wp_strip_all_tags($gateway->get_description());
        $icon = $info[$id][1] ?? '<path d="M3 5h18v14H3zM3 10h18"/>';
        $out .= '<li class="mk-pay-card"><svg viewBox="0 0 24 24" aria-hidden="true">' . $icon . '</svg><h3>' . esc_html($title) . '</h3><p>' . esc_html($text) . '</p></li>';
    }
    if ($out === '') return '<p>Por ahora no hay medios de pago en línea habilitados. Escríbenos para coordinar tu compra.</p>';
    return '<ul class="mk-pay-list">' . $out . '</ul>';
});

add_action('wp_head', function () {
    if (!is_page(['terminos-y-condiciones', 'politica-privacidad', 'metodos-de-pago'])) return;
    echo '<style id="mk-legal-css">.mk-pay-list{list-style:none;margin:24px 0;padding:0;display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))}'
       . '.mk-pay-card{background:#fff;border:1px solid #e3e8ef;border-radius:14px;padding:22px;box-shadow:0 2px 12px #0b2b5214}'
       . '.mk-pay-card svg{width:40px;height:40px;fill:none;stroke:#0756ad;stroke-width:1.6;stroke-linecap:round;stroke-linejoin:round}'
       . '.mk-pay-card h3{margin:12px 0 8px;color:#0756ad;font-size:20px}.mk-pay-card p{margin:0}'
       . '.entry-content h2{margin-top:32px;font-size:24px;color:#172a41}.entry-content table{width:100%;border-collapse:collapse}'
       . '.entry-content td,.entry-content th{border:1px solid #e3e8ef;padding:10px;text-align:left;vertical-align:top}'
       . '.entry-content figure.wp-block-table{overflow-x:auto}</style>';
});
