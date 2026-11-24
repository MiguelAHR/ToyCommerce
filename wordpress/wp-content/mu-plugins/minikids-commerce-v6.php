<?php
/** Plugin Name: MiniKids · Ajustes de compra V6 */
defined('ABSPATH') || exit;

// 9: one review panel, inside Woo's tabs; the theme's upper row remains gallery + summary.
add_filter('woocommerce_product_tabs',function($tabs){
    if(isset($tabs['reviews']))$tabs['reviews']['callback']=function(){comments_template();mk5_review_overview();};
    return $tabs;
},120);

// 10: both recommendation areas use this exact Woo card renderer and loop context.
function mk6_recommendations($ids,$title,$class=''){
    if(!$ids)return;
    echo '<section class="mk6-recommendations '.esc_attr($class).'" aria-label="'.esc_attr($title).'"><h2>'.esc_html($title).'</h2>';
    echo do_shortcode('[products ids="'.implode(',',array_map('absint',$ids)).'" columns="5" orderby="post__in"]');
    echo '</section>';
}
add_action('wp',function(){
    if(is_product()){
        remove_action('woocommerce_after_single_product_summary','woocommerce_output_related_products',20);
        add_action('woocommerce_after_single_product_summary',function(){global $product;if($product)mk6_recommendations(wc_get_related_products($product->get_id(),10),'COMPLETA TU PEDIDO');},20);
    }
});
add_filter('theme_mod_woo_has_related_upsells',function($value){return is_product()?'no':$value;});

// 12: native coupon controls use the native cart form even from the summary column.
add_action('woocommerce_cart_totals_before_order_total',function(){
    if(!wc_coupons_enabled())return;
    echo '<tr class="mk6-coupon-row"><td colspan="2"><label for="coupon_code">Código de cupón</label><div class="coupon mk6-coupon-controls"><input id="coupon_code" name="coupon_code" form="mk6-cart-form" autocomplete="off" type="text"><button type="submit" form="mk6-cart-form" name="apply_coupon" value="Aplicar cupón">Aplicar</button></div></td></tr>';
});
add_action('woocommerce_proceed_to_checkout',function(){
    echo '<p class="mk6-cart-consent"><label><input type="checkbox" id="mk6-cart-consent"> Acepto los <a href="'.esc_url(home_url('/terminos-y-condiciones/')).'">Términos y condiciones</a> y la <a href="'.esc_url(get_privacy_policy_url()).'">Política de privacidad</a>.</label></p><p class="mk6-consent-status" role="status"></p>';
},5);

// 13: progressive presentation of Woo's distributed checkout. No stored block changes.
function mk6_checkout(){return function_exists('is_checkout')&&is_checkout()&&!is_order_received_page()&&!is_wc_endpoint_url('order-pay');}
add_filter('blocksy:woocommerce:checkout:has-custom-markup',function($enabled){return mk6_checkout()?false:$enabled;});
add_action('wp',function(){if(mk6_checkout()&&!has_action('woocommerce_before_checkout_form','woocommerce_checkout_coupon_form'))add_action('woocommerce_before_checkout_form','woocommerce_checkout_coupon_form',10);},200);
add_filter('render_block_woocommerce/checkout',function($html){
    if(!mk6_checkout()||is_admin()||(defined('REST_REQUEST')&&REST_REQUEST))return $html;
    return '<div class="mk6-native-checkout">'.do_shortcode('[woocommerce_checkout]').'</div>';
},120);
add_filter('woocommerce_locate_template',function($path,$name){
    if(mk6_checkout()&&str_starts_with($name,'checkout/')){
        $native=WC()->plugin_path().'/templates/'.$name;if(is_file($native))return $native;
    }
    return $path;
},120,2);
add_action('wp_enqueue_scripts',function(){
    wp_enqueue_style('minikids-commerce-v6',plugins_url('minikids-commerce-v6.css',__FILE__),['minikids-commerce-v5'],filemtime(__DIR__.'/minikids-commerce-v6.css'));
    $deps=['jquery','minikids-commerce-v5'];
    if(is_cart()){wp_enqueue_script('wc-cart');$deps[]='wc-cart';}
    if(mk6_checkout()){wp_enqueue_script('wc-checkout');$deps[]='wc-checkout';}
    wp_enqueue_script('minikids-commerce-v6',plugins_url('minikids-commerce-v6.js',__FILE__),$deps,filemtime(__DIR__.'/minikids-commerce-v6.js'),true);
    wp_localize_script('minikids-commerce-v6','mk6',['cart'=>wc_get_cart_url(),'shop'=>mkr_shop_url(),'checkout'=>mk6_checkout(),'ttl'=>(mk7_local_debug() && isset($_GET['mk6_ttl']) && (string)$_GET['mk6_ttl']==='15')?15:600]);
},120);
function mk6_logo(){
    $id=get_theme_mod('custom_logo');$url=$id?wp_get_attachment_image_url($id,'full'):content_url('/uploads/minikids/logo-minikids.png');
    return function_exists('mka_webp_url')?mka_webp_url($url):$url;
}
add_action('wp_body_open',function(){
    if(!mk6_checkout())return;
    echo '<header class="mk6-secure-header"><a href="'.esc_url(home_url('/')).'"><img src="'.esc_url(mk6_logo()).'" alt="MiniKids" width="164" height="74"></a><p class="mk17-secure-clock">'.mk9_icon('lock').'<span>Ventana segura</span><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> <span id="mk6-timer" role="timer" aria-label="Tiempo restante">10:00</span></p></header>';
},1);
add_filter('body_class',function($classes){if(mk6_checkout())$classes[]='mk6-secure-checkout';return $classes;});
add_action('woocommerce_before_checkout_form',function(){
    if(mk6_checkout())echo '<nav class="mk6-steps" aria-label="Pasos de la compra" hidden><button type="button" data-step="0">Datos</button><button type="button" data-step="1" disabled>Entrega</button><button type="button" data-step="2" disabled>Pago</button></nav>';
},5);
add_action('woocommerce_checkout_before_order_review_heading',function(){echo '<div class="mk6-checkout-navigation" hidden><p class="mk6-step-error" role="alert" tabindex="-1"></p><button type="button" class="mk6-back">Atrás</button><button type="button" class="mk6-next">Siguiente</button></div>';});
add_action('woocommerce_checkout_after_order_review',function(){echo '<a class="mk6-continue" href="'.esc_url(mkr_shop_url()).'">Seguir comprando</a>';});
add_action('woocommerce_before_cart',function(){if(isset($_GET['mk-session-expired']))wc_print_notice('Tu sesión de compra segura venció. Tu carrito se conserva.','notice');});

// V7: debug hooks are limited to a loopback site and local/debug environment.
function mk7_local_debug(){
    $host=wp_parse_url(home_url('/'),PHP_URL_HOST);
    return in_array($host,['localhost','127.0.0.1','::1'],true) && (wp_get_environment_type()==='local' || (defined('WP_DEBUG') && WP_DEBUG));
}
function mk7_loader_enabled(){return !is_admin()&&!is_customize_preview()&&!isset($_GET['elementor-preview']);}
add_action('wp_head',function(){
    if(!mk7_loader_enabled())return;
    $debug=mk7_local_debug()&&isset($_GET['mk6_loader_debug'])&&(string)$_GET['mk6_loader_debug']==='1';
    echo '<style id="mk6-loader-critical">'.file_get_contents(__DIR__.'/minikids-loader-v7.css').'</style>';
    echo '<script>window.mk7LoaderDebug='.($debug?'true':'false').';'.file_get_contents(__DIR__.'/minikids-loader-v7.js').'</script>';
},1);
add_action('wp_body_open',function(){
    if(!mk7_loader_enabled())return;
    echo '<div id="mk6-loader" aria-hidden="true"><img src="'.esc_url(mk6_logo()).'" alt="" width="200" height="90" fetchpriority="high"><div class="mk6-dots"><i></i><i></i><i></i></div></div>';
},0);

// 4: native es_ES Woo pack supplements es_PE; explicit residual strings only.
add_filter('load_textdomain_mofile',function($file,$domain){
    if($domain==='woocommerce'&&get_locale()==='es_PE'&&is_file(WP_LANG_DIR.'/plugins/woocommerce-es_ES.mo'))return WP_LANG_DIR.'/plugins/woocommerce-es_ES.mo';
    return $file;
},20,2);
add_filter('gettext',function($translation,$original,$domain){
    if(!in_array($domain,['woocommerce','blocksy'],true))return $translation;
    $map=['Shipment'=>'Envío','Shipping'=>'Envío','Shipping to %s.'=>'Envío a %s.','Change address'=>'Cambiar dirección','Contact information'=>'Datos de contacto','Email address'=>'Correo electrónico','You are currently checking out as a guest.'=>'Estás comprando como invitado.','Shipping address'=>'Dirección de entrega','Order summary'=>'Resumen del pedido','Your order'=>'Resumen','Add coupons'=>'Añadir cupón','Save %s'=>'Ahorra %s','Shopping cart'=>'Carrito de compras','There are no reviews yet.'=>'Aún no hay comentarios.','Be the first to review &ldquo;%s&rdquo;'=>'Sé la primera persona en comentar sobre «%s»','%s in stock'=>'%s disponibles','Original price was: %s.'=>'Precio anterior: %s.','Current price is: %s.'=>'Precio actual: %s.'];
    return $map[$original]??$translation;
},150,3);

// Offline fallback while the official language archive cannot be downloaded locally.
// Does not change email settings, gateway settings, order state or validation logic.
function mk6_translate($translation,$original,$domain){
    if(!in_array($domain,['woocommerce','blocksy'],true))return $translation;
    $map=[
        'Coupon "%s" cannot be applied because it does not exist.'=>'El cupón «%s» no se puede aplicar porque no existe.','Coupon "%s" cannot be applied because it is not valid.'=>'El cupón «%s» no se puede aplicar porque no es válido.','Coupon code "%s" already applied!'=>'El cupón «%s» ya está aplicado.','Coupon "%s" has expired.'=>'El cupón «%s» ha vencido.','Coupon code removed successfully.'=>'Cupón retirado correctamente.','Usage limit for coupon "%s" has been reached.'=>'Se alcanzó el límite de uso del cupón «%s».','The minimum spend for coupon "%1$s" is %2$s.'=>'La compra mínima para el cupón «%1$s» es %2$s.','The maximum spend for coupon "%1$s" is %2$s.'=>'La compra máxima para el cupón «%1$s» es %2$s.','Please enter a valid email to use coupon code "%s".'=>'Ingresa un correo electrónico válido para usar el cupón «%s».','Please enter a valid email at checkout to use coupon code "%s".'=>'Ingresa un correo electrónico válido al finalizar la compra para usar el cupón «%s».','Sorry, coupon "%s" is not applicable to your cart contents.'=>'El cupón «%s» no se aplica a los artículos de tu carrito.','Sorry, coupon "%s" has already been applied and cannot be used in conjunction with other coupons.'=>'El cupón «%s» ya está aplicado y no se puede combinar con otros cupones.','Sorry, it seems the coupon "%s" is invalid - it has now been removed from your order.'=>'El cupón «%s» no es válido y se ha retirado de tu pedido.','Sorry, coupon "%1$s" is not applicable to the products: %2$s.'=>'El cupón «%1$s» no se aplica a estos productos: %2$s.','Sorry, coupon "%1$s" is not applicable to the categories: %2$s.'=>'El cupón «%1$s» no se aplica a estas categorías: %2$s.','Coupon does not exist.'=>'El cupón no existe.','Invalid coupon'=>'Cupón no válido',
        'Shipment'=>'Envío','Shipment %d'=>'Envío %d','optional'=>'opcional','required'=>'obligatorio','Home'=>'Inicio','Cancel Reply'=>'Cancelar respuesta','Category'=>'Categoría','Category: '=>'Categoría: ','Categories: '=>'Categorías: ','Product quantity'=>'Cantidad de producto','%s quantity'=>'Cantidad de %s','Remove item'=>'Retirar artículo','Thumbnail image'=>'Imagen del producto','Country / region'=>'País o región','City:'=>'Ciudad:','Postcode / ZIP:'=>'Código postal:','Update'=>'Actualizar',
        'Product'=>'Producto','Price'=>'Precio','Quantity'=>'Cantidad','Total'=>'Total','Subtotal'=>'Subtotal','Billing details'=>'Datos de facturación','First name'=>'Nombres','Last name'=>'Apellidos','Company name'=>'Empresa','Country / Region'=>'País o región','Select a country / region…'=>'Selecciona un país o región…','Update country / region'=>'Actualizar país o región','Street address'=>'Dirección','House number and street name'=>'Número y nombre de la calle','Apartment, suite, unit, etc.'=>'Departamento, interior, etc.','Apartment, suite, unit, etc. (optional)'=>'Departamento, interior, etc. (opcional)','Town / City'=>'Ciudad o distrito','State / County'=>'Departamento','State'=>'Departamento','County'=>'Provincia','Region'=>'Región','Postcode / ZIP'=>'Código postal','Select an option…'=>'Selecciona una opción…','Phone'=>'Teléfono','(optional)'=>'(opcional)','Ship to a different address?'=>'¿Enviar a otra dirección?','Additional information'=>'Información adicional','Order notes'=>'Notas del pedido','Notes about your order, e.g. special notes for delivery.'=>'Indicaciones adicionales para entregar tu pedido.','Your order'=>'Resumen','Payment'=>'Pago','Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our %s.'=>'Tus datos personales se utilizarán para procesar tu pedido, atenderte y para los fines descritos en nuestra %s.','privacy policy'=>'Política de privacidad','terms and conditions'=>'Términos y condiciones','I have read and agree to the website %s'=>'He leído y acepto los %s','Please read and accept the terms and conditions to proceed with your order.'=>'Lee y acepta los términos y condiciones para continuar con tu pedido.',
        'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.'=>'Realiza una transferencia a nuestra cuenta bancaria e indica el número de pedido como referencia. El envío se coordina al confirmar el abono.','Pay with cash upon delivery.'=>'Paga en efectivo al recibir tu pedido.','Update totals'=>'Actualizar totales','Update Totals'=>'Actualizar totales','Place order'=>'Realizar pedido','Have a coupon?'=>'¿Tienes un cupón?','Click here to enter your code'=>'Ingresa tu código','If you have a coupon code, please apply it below.'=>'Si tienes un código de cupón, aplícalo aquí.','Coupon code'=>'Código de cupón','Apply coupon'=>'Aplicar cupón','Coupon:'=>'Cupón:','Coupon "%s" does not exist!'=>'El cupón «%s» no existe.','Coupon "%s" does not exist.'=>'El cupón «%s» no existe.','Please enter a coupon code.'=>'Ingresa un código de cupón.','Coupon code applied successfully.'=>'Cupón aplicado correctamente.','Coupon has been removed.'=>'El cupón se ha retirado.','This coupon has expired.'=>'Este cupón ha vencido.','Sorry, this coupon is not applicable to your cart contents.'=>'Este cupón no se aplica a los artículos de tu carrito.','This coupon is not valid.'=>'Este cupón no es válido.',
        '%s is a required field.'=>'%s es un campo obligatorio.','%s is not a valid email address.'=>'%s no es un correo electrónico válido.','%s is not a valid phone number.'=>'%s no es un teléfono válido.','Please enter a valid postcode / ZIP.'=>'Ingresa un código postal válido.','Invalid payment method.'=>'Selecciona un método de pago válido.','Please select a payment method.'=>'Selecciona un método de pago.','Please double-check your payment details and try again.'=>'Revisa los datos de pago y vuelve a intentarlo.','Error processing checkout. Please try again.'=>'No pudimos procesar la compra. Vuelve a intentarlo.','Sorry, your session has expired. <a href="%s" class="wc-backward">Return to shop</a>'=>'Tu sesión venció. <a href="%s" class="wc-backward">Volver a la tienda</a>','Please check your details before proceeding.'=>'Revisa tus datos antes de continuar.','No shipping method has been selected. Please double check your address, or contact us if you need any help.'=>'Selecciona un método de envío. Revisa tu dirección o contáctanos si necesitas ayuda.',
        'There are no reviews yet.'=>'Aún no hay comentarios.','Reviews'=>'Comentarios','Your review'=>'Tu comentario','Your rating'=>'Tu valoración','Rate…'=>'Valorar…','Perfect'=>'Excelente','Good'=>'Bueno','Average'=>'Regular','Not that bad'=>'Podría mejorar','Very poor'=>'Malo','Name'=>'Nombre','Email'=>'Correo electrónico','Submit'=>'Enviar','Be the first to review &ldquo;%s&rdquo;'=>'Sé la primera persona en comentar sobre «%s»','Save my name, email, and website in this browser for the next time I comment.'=>'Guardar mi nombre, correo electrónico y web en este navegador para mi próximo comentario.','Description'=>'Descripción','Additional information'=>'Detalles','Category:'=>'Categoría:','Categories:'=>'Categorías:','Weight'=>'Peso','Dimensions'=>'Dimensiones','Add to cart'=>'Agregar al carrito','View cart'=>'Ver carrito','Return to shop'=>'Volver a la tienda','Your cart is currently empty.'=>'Tu carrito está vacío.','Cart updated.'=>'Carrito actualizado.','Remove this item'=>'Retirar artículo','Available on backorder'=>'Disponible por encargo','Out of stock'=>'Agotado','In stock'=>'Disponible','Shipping to %s.'=>'Envío a %s.','Shipping options will be updated during checkout.'=>'Las opciones de envío se actualizarán al finalizar la compra.','Calculate shipping'=>'Calcular envío','Shipping costs are calculated during checkout.'=>'Los costos de envío se calculan al finalizar la compra.','Country'=>'País','City'=>'Ciudad','Address'=>'Dirección','Proceed to checkout'=>'IR A PAGAR','Skip to content'=>'Ir al contenido','Menu'=>'Menú',
        'Your {site_title} order is now on hold'=>'Tu pedido de {site_title} está en espera','Order on-hold'=>'Pedido en espera','Order details'=>'Detalles del pedido','Billing address'=>'Dirección de facturación','Shipping address'=>'Dirección de entrega','Thanks for your order. It’s on-hold until we confirm that payment has been received.'=>'Gracias por tu pedido. Quedará en espera hasta que confirmemos la recepción del pago.','Thank you. Your order has been received.'=>'Gracias. Hemos recibido tu pedido.','Order number:'=>'Número de pedido:','Date:'=>'Fecha:','Email:'=>'Correo electrónico:','Payment method:'=>'Método de pago:','Order #%s'=>'Pedido #%s',
    ];
    return $map[$original]??$translation;
}
add_filter('gettext','mk6_translate',160,3);
add_filter('gettext_with_context',function($translated,$original,$context,$domain){return mk6_translate($translated,$original,$domain);},160,4);
add_filter('woocommerce_countries',function($countries){
    if(class_exists('Locale'))foreach($countries as $code=>$label){$localized=Locale::getDisplayRegion('und_'.$code,'es_PE');if($localized)$countries[$code]=$localized;}
    return $countries;
});
add_filter('woocommerce_get_privacy_policy_text',function($text,$type){
    if($type==='checkout'&&str_contains($text,'Your personal data will be used'))return 'Tus datos personales se utilizarán para procesar tu pedido, atenderte y para los fines descritos en nuestra [privacy_policy].';
    return $text;
},20,2);

// Editorial headings remain ordinary Gutenberg headings; decorative SVG is render-only.
add_filter('render_block_core/heading',function($html,$block){
    $class=$block['attrs']['className']??'';if(!str_contains($class,'mk6-benefit'))return $html;
    $paths=[
        'truck'=>'<path d="M1 5h13v12H1zM14 9h4l4 4v4h-8"/><circle cx="5" cy="18" r="3"/><circle cx="18" cy="18" r="3"/>',
        'whatsapp'=>'<path d="M5 19l-3 3 1-6a10 10 0 1 1 4 4z"/><path d="M8 7c0 5 4 9 8 9l1-3-3-1-1 1-3-3 1-1-1-3z"/>',
        'book'=>'<path d="M12 5C8 3 5 3 2 4v15c3-1 6-1 10 1 4-2 7-2 10-1V4c-3-1-6-1-10 1v15"/>',
        'shield'=>'<path d="M12 2l9 4v6c0 5-5 9-9 10-4-1-9-5-9-10V6zM8 12l3 3 5-6"/>',
    ];
    foreach($paths as $name=>$path)if(str_contains($class,'mk6-benefit-'.$name))return preg_replace('/(<h[1-6][^>]*>)/','$1<span class="mk6-benefit-icon" aria-hidden="true"><svg viewBox="0 0 24 24">'.$path.'</svg></span>',$html,1);
    return $html;
},20,2);
