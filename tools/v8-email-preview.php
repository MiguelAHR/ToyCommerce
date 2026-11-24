<?php
// Re-render the deleted QA snapshot in memory; never save, trigger or mail.
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/')exit('Local only');
$out=dirname(__DIR__,2).'/diseno_minikids/v5/';$data=json_decode(file_get_contents($out.'pedido-auditoria-V8.json'),true);
$o=new WC_Order();$o->set_address($data['billing'],'billing');$o->set_address($data['shipping'],'shipping');$o->set_billing_phone('999999999');$o->set_shipping_phone('999999999');$o->set_payment_method('bacs');$o->set_payment_method_title($data['payment']);$o->set_date_created(time());$o->set_currency('PEN');$o->set_status('on-hold');$o->set_shipping_total($data['shipping_total']);$o->set_total($data['total']);$o->update_meta_data('_billing_document','QA-LOCAL-V8');
$item=new WC_Order_Item_Product();$item->set_name('Osito miel con lazo');$item->set_product_id(278);$item->set_quantity(1);$item->set_subtotal($data['subtotal']);$item->set_total($data['subtotal']);$o->add_item($item);
$ship=new WC_Order_Item_Shipping();$ship->set_method_title('Envío estándar');$ship->set_method_id('flat_rate');$ship->set_total($data['shipping_total']);$o->add_item($ship);
// Historic number for visual comparison only. This object is never persisted.
$o->set_id($data['order_id']);
$emails=WC()->mailer()->get_emails();$report=[];
// BACS's callback re-fetches the deleted order by ID. Resolve that exact
// historic ID to the in-memory snapshot only during this no-send preview.
$bacs=WC()->payment_gateways()->payment_gateways()['bacs'];remove_action('woocommerce_email_before_order_table',[$bacs,'email_instructions'],10);
add_action('woocommerce_email_before_order_table',function($order,$admin,$plain)use($bacs){
 if($admin||$order->get_payment_method()!=='bacs')return;
 $instructions=$bacs->get_option('instructions');if($instructions)echo wp_kses_post(wpautop($instructions));
 $accounts=(array)get_option('woocommerce_bacs_accounts');if(!$accounts)return;
 echo '<h2>Datos para la transferencia</h2><ul>';
 foreach($accounts as $account){foreach(['account_name'=>'Titular','bank_name'=>'Banco','account_number'=>'Número de cuenta','iban'=>'IBAN','bic'=>'BIC','sort_code'=>'Código bancario'] as $key=>$label)if(!empty($account[$key]))echo '<li>'.esc_html($label.': '.$account[$key]).'</li>';}
 echo '</ul>';
},10,3);
foreach(['admin'=>'WC_Email_New_Order','cliente'=>'WC_Email_Customer_On_Hold_Order'] as $file=>$key){
 $email=$emails[$key];$email->object=$o;$email->recipient=mkpn_business()['email'];$email->placeholders['{order_number}']=(string)$data['order_id'];$email->placeholders['{order_date}']=wc_format_datetime($o->get_date_created());
 $content=$email->style_inline($email->get_content_html());file_put_contents($out.'correos/'.$file.'-vista-corregida.html',$content);
 $report[]=['file'=>$file.'-vista-corregida.html','subject'=>$email->get_subject(),'from'=>get_option('woocommerce_email_from_address'),'recipient'=>$email->recipient,'mode'=>'Unsaved in-memory re-render of deleted order snapshot with corrected phone; NOT a second checkout, NOT sent','order_id'=>$data['order_id']];
}
file_put_contents($out.'correos/vistas-corregidas.json',wp_json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));echo wp_json_encode($report,JSON_UNESCAPED_UNICODE).PHP_EOL;
