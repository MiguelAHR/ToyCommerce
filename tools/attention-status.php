<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';require dirname(__DIR__).'/wordpress/wp-load.php';
$file=dirname(__DIR__).'/local-private/attention-qa.json';$ids=is_file($file)?json_decode(file_get_contents($file),true):[];
$out=['hpos'=>\Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled(),'mail'=>get_option('mkp_mail_status'),'orders'=>[],'claims'=>[]];
foreach($ids['orders']??[] as $id){$order=wc_get_order($id);if($order)$out['orders'][]=['id'=>$id,'status'=>$order->get_status(),'phone'=>$order->get_billing_phone(),'store'=>$order->get_data_store()->get_current_class_name(),'new_order'=>$order->get_meta('_mkpn_mail_new_order'),'customer'=>$order->get_meta('_mkpn_mail_customer_on_hold_order')];}
foreach($ids['claims']??[] as $id)$out['claims'][]=['id'=>$id,'private'=>get_post_status($id)==='private','mail'=>get_post_meta($id,'_mkpr_mail',true)];
echo wp_json_encode($out,JSON_PRETTY_PRINT),PHP_EOL;
