<?php
/** Plugin Name: MiniKids · Catálogo y compra V5
 * Description: Presentación B–E mediante hooks; WooCommerce conserva compra, sesión y pedidos.
 */
defined('ABSPATH') || exit;

// The existing shop shortcode was capped at 50; all local filters need the full catalog.
add_filter('shortcode_atts_products',function($atts){if(is_page('tienda'))$atts['limit']=-1;return $atts;},100);

add_action('wp_enqueue_scripts',function(){
    wp_enqueue_style('minikids-commerce-v5',plugins_url('minikids-commerce-v5.css',__FILE__),['minikids-retail'],filemtime(__DIR__.'/minikids-commerce-v5.css'));
    $deps=['jquery','minikids-retail','wc-cart-fragments'];
    if((is_cart()||is_checkout())&&wp_script_is('wc-blocks-checkout','registered'))$deps[]='wc-blocks-checkout';
    wp_enqueue_script('minikids-commerce-v5',plugins_url('minikids-commerce-v5.js',__FILE__),$deps,filemtime(__DIR__.'/minikids-commerce-v5.js'),true);
    $data=['shop'=>mkr_shop_url(),'cart'=>wc_get_cart_url(),'store'=>rest_url('wc/store/v1/cart'),'ajax'=>WC_AJAX::get_endpoint('mk5_quantity'),'nonce'=>wp_create_nonce('mk5_cart'),'products'=>[]];
    if(is_page('tienda'))foreach(wc_get_products(['status'=>'publish','limit'=>-1])as $p){
        $data['products'][$p->get_id()]=['price'=>(float)wc_get_price_to_display($p),'brand'=>$p->get_attribute('pa_marca')?:$p->get_attribute('Marca'),'max'=>$p->get_max_purchase_quantity()];
    }
    wp_localize_script('minikids-commerce-v5','mk5',$data);
},110);

add_filter('woocommerce_sale_flash',function($html,$post,$p){
    if(!$p||!$p->is_on_sale())return '';
    $r=(float)$p->get_regular_price();$s=(float)$p->get_sale_price();
    return $r>0&&$s>0&&$s<$r?'<span class="onsale">-'.esc_html((string)floor(100*($r-$s)/$r)).' %</span>':'';
},100,3);
add_filter('woocommerce_product_single_add_to_cart_text',fn()=>'Agregar a mi carrito',100);
add_filter('woocommerce_product_add_to_cart_text',function($text,$p){return $p&&$p->is_type('simple')&&$p->is_purchasable()&&$p->is_in_stock()?(is_product()||is_cart()?'A MI BOLSA':'AGREGAR'):$text;},100,2);
add_filter('woocommerce_product_related_products_heading',fn()=>'COMPLETA TU PEDIDO',100);
add_filter('woocommerce_output_related_products_args',function($a){$a['posts_per_page']=10;$a['columns']=5;return $a;},1100);
add_filter('woocommerce_related_products',function($ids,$product_id,$args){
    $same=array_slice($ids,0,7);
    $extra=wc_get_products(['status'=>'publish','stock_status'=>'instock','limit'=>3,'return'=>'ids','exclude'=>array_merge([$product_id],$ids),'category'=>['creatividad','juegos-familiares']]);
    return array_values(array_unique(array_merge($same,$extra)));
},100,3);
add_filter('woocommerce_product_tabs',function($tabs){if(isset($tabs['description']))$tabs['description']['title']='Descripción del producto';if(isset($tabs['additional_information']))$tabs['additional_information']['title']='Detalles';if(isset($tabs['reviews']))$tabs['reviews']['title']='Comentarios';return $tabs;},100);
add_action('woocommerce_single_product_summary',function(){
    global $product;if(!$product)return;
    echo '<p class="mk5-reference">Referencia: '.esc_html($product->get_sku()?:'—').'</p>';
    if(!$product->get_rating_count())echo '<p class="mk5-no-rating"><a href="#reviews"><span>Sin comentarios todavía</span></a></p>';
},6);
add_action('woocommerce_single_product_summary',function(){
    global $product;$n=$product?$product->get_stock_quantity():null;
    if($product&&$product->managing_stock()&&$product->is_in_stock()&&$n!==null&&$n>0&&$n<=10)echo '<p class="mk5-low-stock">¡Solo quedan '.esc_html($n).' piezas!</p>';
},11);
function mk5_info_link($path){$p=get_page_by_path($path);return $p&&$p->post_status==='publish'?get_permalink($p):home_url('/contacto/');}
add_action('woocommerce_single_product_summary',function(){
    echo '<div class="mk5-delivery"><a class="button mk5-delivery-button" href="'.esc_url(mk5_info_link('envios')).'">Ver disponibilidad y opciones de entrega</a><p>Consulta cobertura, costo y plazo de envío para tu dirección.</p><div class="mk5-help-links">';
    foreach(['Métodos de pago'=>'contacto','Opciones de envío'=>'envios','Devoluciones y garantías'=>'devoluciones']as $label=>$path)echo '<a href="'.esc_url(mk5_info_link($path)).'">'.esc_html($label).' <span aria-hidden="true">›</span></a>';
    echo '</div></div>';
},35);
// Review histogram is derived exclusively from approved WooCommerce ratings.
function mk5_review_overview(){
    global $product;if(!$product)return;$counts=$product->get_rating_counts();$total=$product->get_rating_count();
    echo '<section class="mk5-review-overview" aria-label="Resumen de comentarios"><h3>Resumen de valoraciones</h3><p><strong>'.esc_html($total?$product->get_average_rating():'0').' / 5</strong> · '.esc_html($total).' valoraciones</p>';
    for($i=5;$i>=1;$i--){$count=(int)($counts[$i]??0);echo '<div class="mk5-rating-row"><span>'.esc_html($i).' estrellas</span><meter min="0" max="'.esc_attr(max(1,$total)).'" value="'.esc_attr($count).'">'.esc_html($count).'</meter><span>'.esc_html($count).'</span></div>';}
    if(!$total)echo '<p>Aún no hay comentarios. Comparte tu experiencia con este producto.</p>';
    echo '</section>';
}

// Mini-cart remains the native fragment and uses a nonce-protected session-only endpoint.
add_filter('woocommerce_widget_cart_item_quantity',function($html,$item,$key){
    $p=$item['data'];if(!$p||!$p->exists())return $html;
    $max=$p->get_max_purchase_quantity();
    return '<span class="mk5-mini-price">'.WC()->cart->get_product_price($p).'</span><div class="mk5-mini-quantity" data-key="'.esc_attr($key).'">'.woocommerce_quantity_input(['input_name'=>'mk5_qty_'.$key,'input_value'=>$item['quantity'],'min_value'=>1,'max_value'=>$max,'product_name'=>$p->get_name()],$p,false).'</div>';
},100,3);
add_action('wc_ajax_mk5_quantity',function(){
    if(!check_ajax_referer('mk5_cart','nonce',false))wp_send_json_error(['message'=>'Recarga la página para actualizar tu carrito.'],403);
    $key=wc_clean(wp_unslash($_POST['key']??''));$raw=wp_unslash($_POST['quantity']??'');$item=WC()->cart->get_cart_item($key);
    if(!$item||!is_scalar($raw)||!preg_match('/^\d+$/',(string)$raw))wp_send_json_error(['message'=>'Cantidad no válida.'],400);
    $q=(int)$raw;$p=$item['data'];$max=$p->get_max_purchase_quantity();
    if($q<1||($max>=0&&$q>$max)||($p->is_sold_individually()&&$q!==1)||!apply_filters('woocommerce_update_cart_validation',true,$key,$item,$q))wp_send_json_error(['message'=>'Revisa la cantidad y el stock disponible.'],400);
    WC()->cart->set_quantity($key,$q);WC_AJAX::get_refreshed_fragments();
});

// Keep the saved cart block intact; render Woo's own classic cart for the four-column table.
// No Woo template is copied or edited; its quantity/nonce/coupon handlers remain native.
add_filter('render_block_woocommerce/cart',function($html){
    if(!is_cart()||is_admin()||(defined('REST_REQUEST')&&REST_REQUEST))return $html;
    return '<div class="mk5-native-cart">'.do_shortcode('[woocommerce_cart]').'</div>';
},100);
// Select Woo's distributed native table instead of Blocksy's merged-price presentation.
// This changes template resolution only; neither template is edited or copied.
add_filter('woocommerce_locate_template',function($template,$name){
    if(is_cart()&&$name==='cart/cart.php')return WC()->plugin_path().'/templates/cart/cart.php';
    return $template;
},100,2);
add_action('wp',function(){if(is_cart())remove_action('woocommerce_cart_collaterals','woocommerce_cross_sell_display');});
add_filter('gettext',function($translated,$original,$domain){
    if($domain!=='woocommerce'||!function_exists('is_cart')||!is_cart()||is_admin())return $translated;
    $labels=['Cart totals'=>'Resumen','Proceed to checkout'=>'IR A PAGAR','Product'=>'Producto','Price'=>'Precio','Quantity'=>'Cantidad','Subtotal'=>'Subtotal','Update cart'=>'Actualizar carrito','Remove this item'=>'Retirar artículo','Coupon code'=>'Código de cupón','Apply coupon'=>'Aplicar cupón'];
    return $labels[$original]??$translated;
},100,3);
// V11: tools and recommendations follow the native table in its left column.
add_action('woocommerce_after_cart_table',function(){
    echo '<div class="mk5-cart-tools"><button type="button" class="mk5-empty-cart">Vaciar carrito de compra</button><span role="status" class="mk5-cart-status"></span></div>';
    $exclude=WC()->cart?array_column(WC()->cart->get_cart(),'product_id'):[];$ids=[];
    foreach($exclude as $id)$ids=array_merge($ids,wc_get_related_products($id,8,$exclude));
    $ids=array_slice(array_values(array_unique($ids)),0,10);
    if(!$ids)$ids=wc_get_products(['status'=>'publish','limit'=>10,'return'=>'ids','orderby'=>'popularity','exclude'=>$exclude]);
    if(function_exists('mk6_recommendations'))mk6_recommendations($ids,'Artículos relacionados','mk5-cart-related');
});
add_filter('woocommerce_order_button_text',fn()=>'Realizar pedido',100);
