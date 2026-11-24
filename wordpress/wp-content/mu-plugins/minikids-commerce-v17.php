<?php
/** Plugin Name: MiniKids · Diseño del checkout V17 */
defined('ABSPATH') || exit;
add_action('wp',function(){
 if(!mk6_checkout())return;
 remove_action('woocommerce_before_checkout_form','woocommerce_checkout_login_form',10);
 add_action('woocommerce_before_checkout_form',function(){
  if(is_user_logged_in())return;
  echo '<aside class="mk17-account-notice" aria-label="Acceso a tu cuenta">'.mk9_icon('user').'<span>¿Ya tienes cuenta?</span><a class="showlogin" href="#mk12-auth" aria-haspopup="dialog" aria-controls="mk12-auth">Iniciar sesión</a></aside>';
 },10);
},210);
add_action('wp_enqueue_scripts',function(){
 if(!mk6_checkout())return;
 wp_enqueue_style('minikids-commerce-v17',plugins_url('minikids-commerce-v17.css',__FILE__),['minikids-commerce-v9','minikids-commerce-v12','minikids-commerce-v15'],filemtime(__DIR__.'/minikids-commerce-v17.css'));
},200);
