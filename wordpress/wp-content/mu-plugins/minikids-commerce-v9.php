<?php
/** Plugin Name: MiniKids · Bolsa y compra segura V9 */
defined('ABSPATH') || exit;

function mk9_message(){return 'Tu sesión de compra segura venció. Vuelve al carrito para continuar.';}
function mk9_expire(){
    if(!WC()->session)return;
    WC()->session->__unset('mk_secure_start');
    WC()->session->__unset('mk_secure_token');
    WC()->session->set('mk_secure_expired',true);
}
function mk9_valid($token=''){
    $s=WC()->session;if(!$s)return false;
    $start=(int)$s->get('mk_secure_start');$ttl=(int)$s->get('mk_secure_ttl',600);
    if(!$start||$s->get('mk_secure_expired')||time()>=$start+$ttl){mk9_expire();return false;}
    return is_string($token)&&$token!==''&&hash_equals((string)$s->get('mk_secure_token'),$token);
}
function mk9_state(){
    $s=WC()->session;$now=time();$start=$s?(int)$s->get('mk_secure_start'):0;
    $end=$start+($s?(int)$s->get('mk_secure_ttl',600):600);
    return ['serverNow'=>$now,'start'=>$start,'end'=>$end,'remaining'=>max(0,$end-$now),'token'=>$s?(string)$s->get('mk_secure_token'):'','expired'=>!$start||($s&&$s->get('mk_secure_expired'))];
}
// Only the first HTML render starts a window. AJAX and submitted orders never do.
add_action('template_redirect',function(){
    if(is_cart()&&WC()->session){
        $s=WC()->session;$start=(int)$s->get('mk_secure_start');
        if($s->get('mk_secure_expired')||($start&&time()>=$start+(int)$s->get('mk_secure_ttl',600))){
            mk9_expire();$s->__unset('mk_secure_expired');
        }
    }
    if(!mk6_checkout())return;
    nocache_headers();header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0',true);header('Vary: Cookie',false);
    if($_SERVER['REQUEST_METHOD']!=='GET'||!WC()->session||WC()->cart->is_empty())return;
    $s=WC()->session;
    if($s->get('mk_secure_start')&&time()>=(int)$s->get('mk_secure_start')+(int)$s->get('mk_secure_ttl',600))mk9_expire();
    if(!$s->get('mk_secure_start')&&!$s->get('mk_secure_expired')){
        $s->set_customer_session_cookie(true);$s->set('mk_secure_start',time());
        $s->set('mk_secure_ttl',mk7_local_debug()&&isset($_GET['mk6_ttl'])&&$_GET['mk6_ttl']==='15'?15:600);
        $s->set('mk_secure_token',wp_generate_password(32,false));
    }
},1);
add_filter('body_class',function($c){if(mk6_checkout())$c[]='mk9-secure-checkout';return $c;});
add_action('wp_head',function(){if(!mk6_checkout())return;
    // This stays inline even when external styles or all JavaScript fail.
    echo '<style id="mk9-secure-critical">body.mk9-secure-checkout{background:#f3f4f6}body.mk9-secure-checkout :is(.mkr-header,.mkr-service,header.ct-header,.ct-footer,.mkr-footer,.hero-section,#minikids-cart-drawer,#mk6-loader){display:none!important}body.mk9-secure-checkout:not(.mk-secure-ready) :is(form.checkout,.mk6-native-checkout,.woocommerce-form-coupon-toggle,form.checkout_coupon,.mk6-steps){display:none!important}body.mk-secure-ready #mk9-failsafe{display:none!important}#mk9-failsafe{max-width:680px;margin:32px auto;padding:24px;background:white;border:1px solid #0756ad;border-radius:10px;color:#0b2b52}body.mk9-secure-checkout #mk9-failsafe .mk11-failure{visibility:hidden;animation:mk11-reveal 0s 4s forwards!important}body.mk9-secure-checkout.mk-secure-failed #mk9-failsafe .mk11-failure{visibility:visible;animation:none!important}body.mk-secure-failed #mk9-failsafe .mk11-loading{display:none}body.mk-secure-ready #mk11-failure-watch{display:none}body.mk9-secure-checkout #mk11-failure-watch{position:absolute;width:1px;height:1px;pointer-events:none;animation:mk11-watch 4s linear!important}#mk9-failsafe .mk11-loading{height:120px;background:linear-gradient(#edf0f4 0 0) 0 0/100% 48px no-repeat,linear-gradient(#edf0f4 0 0) 0 64px/100% 48px no-repeat;border-radius:8px}body.mk-secure-failed #mk9-failsafe .mk11-failure{min-height:0}@keyframes mk11-reveal{to{visibility:visible}}@keyframes mk11-watch{from{opacity:.99}to{opacity:1}}#mk9-failsafe a{color:#0756ad;text-decoration:underline}</style>';
},0);
add_action('wp_body_open',function(){if(mk6_checkout())echo '<span id="mk11-failure-watch" aria-hidden="true"></span><section id="mk9-failsafe" role="status"><div class="mk11-loading" aria-label="Preparando compra segura"></div><div class="mk11-failure"><p>No se ha podido iniciar la ventana de compra segura. Si el aviso continúa, vuelve al carrito para intentarlo de nuevo.</p><a href="'.esc_url(wc_get_cart_url()).'">Volver al carrito</a></div></section>';},2);
add_action('woocommerce_checkout_before_customer_details',function(){
    if(mk6_checkout())echo '<input type="hidden" name="mk_secure_token" value="'.esc_attr(mk9_state()['token']).'">';
});
function mk9_post_token(){return isset($_POST['mk_secure_token'])&&is_string($_POST['mk_secure_token'])?sanitize_text_field(wp_unslash($_POST['mk_secure_token'])):'';}
add_action('woocommerce_checkout_process',function(){if(!mk9_valid(mk9_post_token()))wc_add_notice(mk9_message(),'error');},0);
add_action('woocommerce_after_checkout_validation',function($data,$errors){if(!mk9_valid(mk9_post_token()))$errors->add('mk_secure_expired',mk9_message());},999,2);
// Run before the Store API route callback, which can create a draft order itself.
add_filter('rest_request_before_callbacks',function($response,$handler,$request){
    if(!preg_match('#^/wc/store/v[0-9]+/checkout(?:/|$)#',$request->get_route()))return $response;
    if(!WC()->session||!WC()->cart)wc_load_cart();
    if(!mk9_valid((string)$request->get_header('X-MK-Secure-Token')))return new WP_Error('mk_secure_expired',mk9_message(),['status'=>403]);
    return $response;
},0,3);
add_filter('rest_post_dispatch',function($response,$server,$request){
    if(str_starts_with($request->get_route(),'/wc/store/')&&str_contains($request->get_route(),'/checkout')){
        $response->header('Cache-Control','no-store');$response->header('Vary','Cookie');
    }return $response;
},999,3);
function mk9_clear(){if(WC()->session)foreach(['mk_secure_start','mk_secure_token','mk_secure_expired','mk_secure_ttl'] as $key)WC()->session->__unset($key);}
add_action('woocommerce_checkout_order_processed','mk9_clear',999);
add_action('woocommerce_store_api_checkout_order_processed','mk9_clear',999);
add_action('wc_ajax_mk9_secure_status',function(){
    nocache_headers();header('Cache-Control: no-store');header('Vary: Cookie',false);
    $token=isset($_POST['token'])&&is_string($_POST['token'])?sanitize_text_field(wp_unslash($_POST['token'])):'';
    if(!mk9_valid($token))wp_send_json_error(['message'=>mk9_message()],403);
    wp_send_json_success(mk9_state());
});

function mk9_icon($name){
    $paths=['truck'=>'<path d="M2 5h12v12H2zM14 9h4l4 4v4h-8"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>','user'=>'<circle cx="12" cy="7" r="4"/><path d="M4 22v-3a8 8 0 0 1 16 0v3"/>','lock'=>'<rect x="4" y="10" width="16" height="12" rx="2"/><path d="M8 10V6a4 4 0 0 1 8 0v4M12 15v3"/>','check'=>'<path d="m5 12 4 4L20 5"/>','trash'=>'<path d="M3 6h18M9 6V3h6v3M6 6l1 15h10l1-15M10 10v7M14 10v7"/>'];
    return '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">'.($paths[$name]??$paths['lock']).'</svg>';
}
function mk9_progress(){
    $subtotal=WC()->cart?(float)WC()->cart->get_subtotal():0;$gap=max(0,149-$subtotal);$pct=min(100,max(0,$subtotal/149*100));
    return '<div class="mk9-progress">'.mk9_icon('truck').'<div><p>'.($gap>0?'Solo te falta <strong>'.wp_kses_post(wc_price($gap)).'</strong> para envío gratis':'Tienes envío gratis').'</p><div class="mk9-track" role="progressbar" aria-label="Avance para envío gratis" aria-valuemin="0" aria-valuemax="149" aria-valuenow="'.esc_attr(min(149,$subtotal)).'"><span style="width:'.esc_attr($pct).'%"></span></div></div></div>';
}
add_action('woocommerce_cart_totals_before_order_total',function(){echo '<tr class="mk9-progress-row"><td colspan="2">'.mk9_progress().'</td></tr>';},5);
add_action('woocommerce_after_mini_cart',function(){
    if(!WC()->cart||WC()->cart->is_empty())return;
    echo '<div class="mk9-bag-footer">'.mk9_progress().'<p class="mk9-bag-subtotal"><strong>Subtotal</strong><strong>'.WC()->cart->get_cart_subtotal().'</strong></p><p class="mk9-bag-note">Los gastos de envío se calcularán antes de finalizar tu compra.</p><a class="button mk9-buy" href="'.esc_url(wc_get_checkout_url()).'">Comprar</a><a class="mk9-view-cart" href="'.esc_url(wc_get_cart_url()).'">Ver carrito</a><a class="mk12-bag-account" href="'.esc_url(mk12_account_url()).'">Mi cuenta</a></div>';
});
// The quantity renderer and nonce-protected mk5 endpoint stay in use.
// Removal markup is normalized once below, after other presentation filters.
function mk9_products(){
    $html='<div class="mk9-products">';
    foreach(WC()->cart->get_cart() as $key=>$item){$p=$item['data'];if(!$p||!$p->exists())continue;
        $html.='<div class="mk9-product">'.$p->get_image('woocommerce_thumbnail').'<div><a href="'.esc_url($p->get_permalink()).'">'.esc_html($p->get_name()).'</a><p>'.WC()->cart->get_product_subtotal($p,$item['quantity']).'</p><div class="mk9-product-actions" data-key="'.esc_attr($key).'"><button type="button" data-delta="-1" aria-label="Reducir cantidad">−</button><input type="number" min="1" max="'.esc_attr($p->get_max_purchase_quantity()>0?$p->get_max_purchase_quantity():'').'" value="'.esc_attr($item['quantity']).'" aria-label="Cantidad de '.esc_attr($p->get_name()).'"><button type="button" data-delta="1" aria-label="Aumentar cantidad">+</button><button type="button" data-remove aria-label="Retirar '.esc_attr($p->get_name()).'">'.mk9_icon('trash').'</button></div></div></div>';
    }return $html.'</div>';
}
add_action('woocommerce_checkout_before_order_review_heading',function(){
    echo '<details class="mk9-summary" open><summary><span>Resumen de tu bolsa</span><span class="mk17-summary-total">'.wp_kses_post(WC()->cart->get_total()).'</span></summary><section class="mk17-products-card" aria-labelledby="mk17-summary-title"><h2 id="mk17-summary-title">Resumen de tu bolsa</h2><div class="mk9-products-slot">'.mk9_products().'</div>'.mk9_progress().'<section class="mk9-coupon-slot"></section><div class="mk9-summary-status" role="status"></div></section></details>';
},5);
add_filter('woocommerce_update_order_review_fragments',function($fragments){$fragments['.mk9-products-slot']='<div class="mk9-products-slot">'.mk9_products().'</div>';$fragments['.mk9-summary .mk9-progress']=mk9_progress();return $fragments;});
add_action('wp_footer',function(){if(!mk6_checkout())return;
    echo '<footer class="mk9-secure-footer"><strong>'.mk9_icon('lock').' Pago seguro</strong><p>';
    foreach(WC()->payment_gateways()->get_available_payment_gateways() as $g)echo '<span>'.esc_html(preg_replace('/\s*\(prueba local\)\s*/iu','',$g->get_title())).'</span> ';
    echo '</p><p>Copyright © 2026 MiniKids. Todos los derechos reservados.</p></footer>';
},10);
add_action('wp_enqueue_scripts',function(){
    wp_enqueue_style('minikids-commerce-v9',plugins_url('minikids-commerce-v9.css',__FILE__),['minikids-commerce-v6'],filemtime(__DIR__.'/minikids-commerce-v9.css'));
    wp_enqueue_script('minikids-commerce-v9',plugins_url('minikids-commerce-v9.js',__FILE__),['jquery','minikids-commerce-v6'],filemtime(__DIR__.'/minikids-commerce-v9.js'),true);
    wp_localize_script('minikids-commerce-v9','mk9',['checkout'=>mk6_checkout(),'state'=>mk6_checkout()?mk9_state():null,'needsShipping'=>WC()->cart?WC()->cart->needs_shipping():false,'cart'=>wc_get_cart_url(),'status'=>WC_AJAX::get_endpoint('mk9_secure_status'),'store'=>rest_url('wc/store/v1/cart'),'icons'=>['user'=>mk9_icon('user'),'truck'=>mk9_icon('truck'),'lock'=>mk9_icon('lock'),'check'=>mk9_icon('check')]]);
},140);

// V11: scope name wrapping to native mini-cart renders (including Ajax fragments).
add_action('woocommerce_before_mini_cart',function(){$GLOBALS['mk11_mini_render']=true;},0);
add_action('woocommerce_after_mini_cart',function(){$GLOBALS['mk11_mini_render']=false;},999);
add_filter('woocommerce_cart_item_name',function($name){
 return !empty($GLOBALS['mk11_mini_render'])?'<span class="mk11-mini-name">'.$name.'</span>':$name;
},999);
// Keep native href/nonces/data attributes; render exactly one decorative trash SVG.
add_filter('woocommerce_cart_item_remove_link',function($html,$key){
 // Return one native link for the entire filter value, never an added control.
 $item=WC()->cart?WC()->cart->get_cart_item($key):null;
 if(!$item||empty($item['data']))return $html;
 $p=$item['data'];$mini=!empty($GLOBALS['mk11_mini_render']);
 return '<a role="button" href="'.esc_url(wc_get_cart_remove_url($key)).'" class="remove'.($mini?' remove_from_cart_button':'').'" aria-label="Eliminar producto" data-product_id="'.esc_attr($item['product_id']).'" data-product_sku="'.esc_attr($p->get_sku()).'" data-cart_item_key="'.esc_attr($key).'">'.mk9_icon('trash').'</a>';
},999,2);
// Same percentage label in cart recommendations as in catalogue cards.
add_filter('woocommerce_sale_flash',function($html,$post,$p){
 if(!is_cart())return $html;
 $r=(float)$p->get_regular_price();$s=(float)$p->get_sale_price();
 return $r>0&&$s>0&&$s<$r?'<span class="onsale">&#45; '.esc_html((string)floor(100*($r-$s)/$r)).' %</span>':'';
},999,3);

// Full-width shipping row, preserving Woo shipping controls and calculator markup.
add_action('woocommerce_cart_totals_before_shipping',function(){ob_start();},0);
add_action('woocommerce_cart_totals_after_shipping',function(){
 $html=ob_get_clean();
 $html=preg_replace('~<th\b[^>]*>.*?</th>~s','',$html);
 $html=preg_replace('~<div class="ct-shipping-heading">.*?</div>~s','',$html);
 // Preserve native inputs, prices/taxes and calculator; only arrange their labels.
 $html=preg_replace_callback('~(<label\b[^>]*>)(.*?)(</label>)~s',function($m){
  $parts=preg_split('~:\s*(?=<span)~',$m[2],2);
  return $m[1].'<span class="mk13-shipping-name">'.$parts[0].'</span>'.(isset($parts[1])?'<span class="mk13-shipping-cost">'.$parts[1].'</span>':'').$m[3];
 },$html);

 $html=preg_replace_callback('~(<p class="woocommerce-shipping-destination">)(.*?)(</p>)~s',function($m){
  return preg_match('~<strong>.*?</strong>~s',$m[2],$destination)?$m[1].'Enviar a '.$destination[0].$m[3]:$m[0];
 },$html);
 echo preg_replace('~<td\b~','<td colspan="2"',$html);
},999);

// Native Woo markup matches this drawer's grid; keep templates of both vendors intact.
add_filter('woocommerce_locate_template',function($template,$name){
 if($name==='cart/mini-cart.php')return WC()->plugin_path().'/templates/cart/mini-cart.php';
 return $template;
},1000,2);

// Public checkout labels only: stored gateway configuration and behaviour stay native.
add_filter('woocommerce_gateway_title',function($title){
 return !is_admin()&&mk6_checkout()?preg_replace('/\s*\(prueba local\)\s*/iu','',$title):$title;
},999);
