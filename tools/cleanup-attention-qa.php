<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';require dirname(__DIR__).'/wordpress/wp-load.php';
if(DB_NAME!=='minikeds_dev2')throw new RuntimeException('Base inesperada');
$file=dirname(__DIR__).'/local-private/attention-qa.json';$ids=json_decode(file_get_contents($file),true);
// Cancelar la QA nunca debe notificar como si fuera una cancelación comercial.
add_filter('pre_wp_mail','__return_true',-100);
foreach($ids['orders'] as $id) {
    $order=wc_get_order($id);if(!$order)continue;
    if($order->get_billing_email()!=='vektronova@gmail.com'||$order->get_billing_first_name()!=='Validación')throw new RuntimeException('Pedido fuera del ámbito QA');
    if($order->get_status()!=='cancelled')$order->update_status('cancelled','Validación local concluida; sin compra real, stock restituido.');
    echo 'Pedido QA '.$id.': '.$order->get_status().PHP_EOL;
}
foreach($ids['claims'] as $id) {
    if(!get_post($id))continue;$data=get_post_meta($id,'_mkpr_data',true);
    if(get_post_type($id)!=='mkp_claim'||($data['name']??'')!=='Validación técnica MiniKids')throw new RuntimeException('Hoja fuera de QA');
    wp_delete_post($id,true);echo 'Hoja QA '.$id.' eliminada'.PHP_EOL;
}
foreach(['::1','127.0.0.1','local'] as $ip)delete_transient('mkpr_rate_'.hash_hmac('sha256',$ip,wp_salt()));
