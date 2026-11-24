<?php
// Own template: native Woo cart/filter/fragment APIs; no third-party template edited.
defined('ABSPATH') || exit;
if(!did_action('wp_loaded'))return;
do_action('woocommerce_before_mini_cart');
if(WC()->cart&&!WC()->cart->is_empty()): ?>
<ul class="woocommerce-mini-cart cart_list product_list_widget mk15-mini-list">
<?php
do_action('woocommerce_before_mini_cart_contents');
foreach(WC()->cart->get_cart() as $key=>$item){
 $p=apply_filters('woocommerce_cart_item_product',$item['data'],$item,$key);
 if(!$p||!$p->exists()||$item['quantity']<=0||!apply_filters('woocommerce_widget_cart_item_visible',true,$item,$key))continue;
 $name=apply_filters('woocommerce_cart_item_name',WC()->cart->get_item_product_name($item,$p),$item,$key);
 $url=apply_filters('woocommerce_cart_item_permalink',$p->is_visible()?$p->get_permalink($item):'',$item,$key);
 $price=apply_filters('woocommerce_cart_item_price',WC()->cart->get_product_price($p),$item,$key);
 echo '<li class="woocommerce-mini-cart-item '.esc_attr(apply_filters('woocommerce_mini_cart_item_class','mini_cart_item',$item,$key)).'">';
 echo '<div class="mk15-thumb">'.apply_filters('woocommerce_cart_item_thumbnail',$p->get_image('woocommerce_thumbnail'),$item,$key).'</div>';
 echo $url?'<a class="mk15-name" href="'.esc_url($url).'">'.wp_kses_post($name).'</a>':'<span class="mk15-name">'.wp_kses_post($name).'</span>';
 echo '<div class="mk15-line">'.apply_filters('woocommerce_widget_cart_item_quantity','<span class="quantity">'.esc_html($item['quantity']).' × '.$price.'</span>',$item,$key);
 echo apply_filters('woocommerce_cart_item_remove_link','<a class="remove remove_from_cart_button" href="'.esc_url(wc_get_cart_remove_url($key)).'" data-cart_item_key="'.esc_attr($key).'" aria-label="Eliminar producto">&times;</a>',$key).'</div>';
 echo wc_get_formatted_cart_item_data($item).'</li>';
}
do_action('woocommerce_mini_cart_contents');
?></ul>
<div class="mk15-native-mini-hooks" hidden><?php do_action('woocommerce_widget_shopping_cart_total');do_action('woocommerce_widget_shopping_cart_before_buttons');do_action('woocommerce_widget_shopping_cart_buttons');do_action('woocommerce_widget_shopping_cart_after_buttons'); ?></div>
<?php else: ?>
<p class="woocommerce-mini-cart__empty-message">Tu bolsa está vacía.</p>
<?php endif;
do_action('woocommerce_after_mini_cart');
