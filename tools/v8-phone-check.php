<?php
if(PHP_SAPI!=='cli')exit;
define('WC_DOING_AJAX',true);$_GET['wc-ajax']='checkout';$_SERVER['HTTP_HOST']='localhost:8082';require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/')exit('Local only');
$checkout=WC()->checkout();$fields=$checkout->get_checkout_fields('billing');
$_POST=['billing_first_name'=>'Prueba QA','billing_last_name'=>'MiniKids','billing_phone'=>'999999999','billing_email'=>mkpn_business()['email'],'billing_country'=>'PE','billing_address_1'=>'Prueba local sin despacho','billing_city'=>'Lima','billing_state'=>'LMA','billing_postcode'=>'15001','billing_document'=>'QA-LOCAL-V8'];
$method=new ReflectionMethod($checkout,'get_posted_data');$method->setAccessible(true);$data=$method->invoke($checkout);
$order=new WC_Order();$order->set_billing_phone($data['billing_phone']??'');
$result=['phone_field_present'=>isset($fields['billing_phone']),'phone_required'=>!empty($fields['billing_phone']['required']),'posted_phone_parsed'=>($data['billing_phone']??'')==='999999999','native_unsaved_order_phone'=>$order->get_billing_phone()==='999999999','no_order_persisted'=>$order->get_id()===0,'scope'=>'Native checkout data parsing and unsaved WC_Order setter; no second order, no send, no paid status.'];
file_put_contents(dirname(__DIR__,2).'/diseno_minikids/v5/telefono-corregido-V8.json',wp_json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));echo wp_json_encode($result).PHP_EOL;
