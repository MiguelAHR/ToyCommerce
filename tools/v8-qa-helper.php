<?php
/** Temporary, private-file gated QA capture. No public switch. */
defined('ABSPATH')||exit;
$mk8qa=dirname(ABSPATH,2).'/local-private/v8-qa-active.json';
if(wp_get_environment_type()!=='local'||!is_readable($mk8qa))return;
$mk8qa_data=json_decode(file_get_contents($mk8qa),true);
if(($mk8qa_data['enabled']??false)!==true)return;
function mk8_qa_order($order){return $order instanceof WC_Order&&$order->get_billing_first_name()==='Prueba QA'&&$order->get_billing_email()===(mkpn_business()['email']??'');}
add_action('woocommerce_checkout_create_order',function($order){
 if(!mk8_qa_order($order))return;
 if(wc_get_orders(['limit'=>1,'meta_key'=>'_mk8_qa','meta_value'=>'v8']))throw new Exception('La prueba V8 ya existe; no repetir.');
 $order->update_meta_data('_mk8_qa','v8');$order->update_meta_data('_billing_document','QA-LOCAL-V8');
},10);
foreach(['new_order','customer_on_hold_order'] as $type)add_filter('woocommerce_email_subject_'.$type,function($subject,$order){return mk8_qa_order($order)?'[Prueba QA V8 sin compra real] '.$subject:$subject;},50,2);
add_action('woocommerce_checkout_order_created',function($order){if(mk8_qa_order($order))file_put_contents(dirname(ABSPATH,2).'/local-private/v8-order.json',wp_json_encode(['orders'=>[$order->get_id()],'claims'=>[]]));});
add_filter('pre_wp_mail',function($previous,$atts){
 if(!str_contains($atts['subject'],'[Prueba QA V8 sin compra real]'))return $previous;
 $owner=mkpn_business()['email']??'';foreach(mkpn_mail_addresses($atts['to']) as $to)if($to['email']!==$owner)throw new RuntimeException('QA destinatario no autorizado');
 $out=dirname(ABSPATH,2).'/diseno_minikids/v5/correos/';if(!is_dir($out))mkdir($out,0700,true);
 $index=is_file($out.'capturas.json')?json_decode(file_get_contents($out.'capturas.json'),true):[];
 $name=str_contains($atts['subject'],'Nuevo pedido')?'admin':'cliente';
 file_put_contents($out.$name.'.html',$atts['message']);
 $index[]=['subject'=>$atts['subject'],'to'=>$atts['to'],'from'=>get_option('woocommerce_email_from_address'),'file'=>$name.'.html','transport'=>'captured_only_no_brevo_credentials','delivered'=>false];
 file_put_contents($out.'capturas.json',wp_json_encode($index,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
 return true; // Explicit offline capture; never presented as provider acceptance or delivery.
},-100,2);
