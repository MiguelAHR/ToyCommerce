<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/')exit('Local only');
$journal=json_decode(file_get_contents(dirname(__DIR__,2).'/local-private/v8-order.json'),true);$o=wc_get_order($journal['orders'][0]);
$checks=['native_on_hold'=>$o->get_status()==='on-hold','bacs'=>$o->get_payment_method()==='bacs','owner_email'=>$o->get_billing_email()===mkpn_business()['email'],'billing_name'=>$o->get_billing_first_name()==='Prueba QA','document'=>$o->get_meta('_billing_document')==='QA-LOCAL-V8','phone'=>$o->get_billing_phone()==='999999999','address'=>$o->get_billing_address_1()==='Prueba QA local sin despacho 123','currency'=>$o->get_currency()==='PEN'];
$checks['hpos']=str_contains($o->get_data_store()->get_current_class_name(),'OrdersTable');
$pending=[];if(function_exists('as_get_scheduled_actions'))foreach(as_get_scheduled_actions(['status'=>'pending','per_page'=>1000]) as $id=>$action){$args=$action->get_args();if(in_array($o->get_id(),$args,true)||in_array((string)$o->get_id(),$args,true))$pending[]=['id'=>$id,'hook'=>$action->get_hook()];}
$result=['order_id'=>$o->get_id(),'status'=>$o->get_status(),'checks'=>$checks,'billing'=>$o->get_address('billing'),'shipping'=>$o->get_address('shipping'),'payment'=>$o->get_payment_method_title(),'document'=>$o->get_meta('_billing_document'),'subtotal'=>$o->get_subtotal(),'shipping_total'=>$o->get_shipping_total(),'total'=>$o->get_total(),'currency'=>$o->get_currency(),'pending_actions_for_order'=>$pending,'capture_only'=>true,'provider_delivered'=>false];
$out=dirname(__DIR__,2).'/diseno_minikids/v5/';file_put_contents($out.'pedido-auditoria-V8.json',wp_json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));echo wp_json_encode($result,JSON_UNESCAPED_UNICODE).PHP_EOL;
