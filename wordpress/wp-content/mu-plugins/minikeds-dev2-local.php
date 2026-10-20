<?php
/** Plugin Name: Mini Keds dev2 · entorno local aislado */
defined('ABSPATH')||exit;
if(wp_get_environment_type()!=='local')return;
add_filter('pre_wp_mail',function($previous){return get_option('mkp_mail_enabled')==='yes'?$previous:true;});
add_filter('woocommerce_available_payment_gateways',function($gateways){return array_intersect_key($gateways,array_flip(['bacs','cod']));},100);
