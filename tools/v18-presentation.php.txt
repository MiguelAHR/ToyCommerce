<?php
/** Plugin Name: MiniKids · Alineación y responsive V18 */
defined('ABSPATH') || exit;
add_action('wp_enqueue_scripts',function(){
 $deps=['minikids-commerce-v16'];
 if(function_exists('mk6_checkout') && mk6_checkout())$deps[]='minikids-commerce-v17';
 wp_enqueue_style('minikids-commerce-v18',plugins_url('minikids-commerce-v18.css',__FILE__),$deps,filemtime(__DIR__.'/minikids-commerce-v18.css'));
 wp_enqueue_script('minikids-commerce-v18',plugins_url('minikids-commerce-v18.js',__FILE__),['jquery','minikids-commerce-v16'],filemtime(__DIR__.'/minikids-commerce-v18.js'),true);
},250);
// Same slot even when the native product has no ratings. No fake stars/counts.
add_action('woocommerce_after_shop_loop_item_title',function(){
 global $product;
 if($product && !$product->get_rating_count())echo '<div class="mk18-rating-space" aria-hidden="true"></div>';
},12);
