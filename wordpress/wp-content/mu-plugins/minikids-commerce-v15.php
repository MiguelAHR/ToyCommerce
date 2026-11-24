<?php
/** Plugin Name: MiniKids · Bolsa y valoraciones V15 */
defined('ABSPATH') || exit;
function mk15_account_alias(){return untrailingslashit(wp_parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH))===untrailingslashit(wp_parse_url(home_url('/mi-cuenta/'),PHP_URL_PATH));}
function mk15_auto_auth(){return !is_user_logged_in()&&(is_account_page()||mk15_account_alias()||isset($_GET['login']))&&!(is_wc_endpoint_url('lost-password')&&(isset($_GET['key'])||isset($_GET['show-reset-form'])));}
add_action('template_redirect',function(){if(!is_user_logged_in()&&(is_account_page()||isset($_GET['login']))){nocache_headers();header('Cache-Control: no-store, private');}},1);
add_action('wp_head',function(){if(mk15_auto_auth())echo '<script>if(typeof HTMLDialogElement!=="undefined"&&HTMLDialogElement.prototype.showModal)document.documentElement.classList.add("mk15-auto-auth");</script>';},1);
add_filter('body_class',function($classes){if(mk15_auto_auth()&&is_account_page())$classes[]='mk15-account-background';return $classes;});
add_action('wp_enqueue_scripts',function(){
 wp_enqueue_style('minikids-commerce-v15',plugins_url('minikids-commerce-v15.css',__FILE__),['minikids-commerce-v12','minikids-commerce-v9'],filemtime(__DIR__.'/minikids-commerce-v15.css'));
 wp_localize_script('minikids-commerce-v12','mk15',['auto'=>mk15_auto_auth(),'view'=>is_wc_endpoint_url('lost-password')?'lost':'login','home'=>home_url('/'),'lost'=>wc_lostpassword_url()]);
},160);
add_filter('woocommerce_locate_template',function($template,$name){return $name==='cart/mini-cart.php'?__DIR__.'/minikids-mini-cart-v15.php':$template;},1100,2);
add_action('woocommerce_after_shop_loop_item_title',function(){if(function_exists('mk16_card_rating'))mk16_card_rating();},11);
add_action('wp',function(){remove_action('woocommerce_after_shop_loop_item_title','woocommerce_template_loop_rating',5);remove_action('woocommerce_single_product_summary','woocommerce_template_single_rating',10);});
add_filter('views_edit-comments',function($views){$views['mk_demo']='<a href="'.esc_url(admin_url('edit-comments.php?mk_demo=1')).'">Solo demo</a>';return $views;});
add_action('pre_get_comments',function($query){if(is_admin()&&current_user_can('moderate_comments')&&!empty($_GET['mk_demo'])){$query->query_vars['meta_key']='_mk_demo';$query->query_vars['meta_value']='1';}});
