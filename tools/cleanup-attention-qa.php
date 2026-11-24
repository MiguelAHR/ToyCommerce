<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';require dirname(__DIR__).'/wordpress/wp-load.php';
// V8: isolated current-copy journal; preserve the historical QA manifest and database guard.
if(($argv[1]??'')==='--v8'){
    if(home_url('/')!=='http://localhost:8082/wordpress/'||wp_get_environment_type()!=='local')throw new RuntimeException('Local only');
    $base=dirname(__DIR__,2).'/local-private/';$file=$base.'v8-order.json';
    $ids=is_file($file)?json_decode(file_get_contents($file),true):['orders'=>[]];
    $before=json_decode(file_get_contents($base.'v8-backups/native-before.json'),true);
    add_filter('pre_wp_mail','__return_true',-200);
    foreach($ids['orders'] as $id){
        if(in_array($id,$before['orders'],true))throw new RuntimeException('Existing order protected');
        $order=wc_get_order($id);if(!$order)continue;
        if($order->get_meta('_mk8_qa')!=='v8'||$order->get_billing_first_name()!=='Prueba QA'||$order->get_billing_email()!==$before['business']['email'])throw new RuntimeException('Outside V8 QA');
        if($order->get_status()!=='cancelled')$order->update_status('cancelled','Prueba QA V8 concluida, sin compra ni despacho.');
        wc_maybe_increase_stock_levels($id);
        $order->delete(true);
    }
    // The legacy owned logger records wp_mail attempts as sent before transport.
    // Preserve a private copy and remove only this QA order's captured attempts.
    $logs=get_option('minikids_email_logs',[]);$backup=$base.'v8-backups/email-logs-pre-cleanup.json';if(!file_exists($backup))file_put_contents($backup,wp_json_encode($logs));
    foreach($logs as $key=>$log)foreach($ids['orders'] as $id)if(preg_match('/#'.preg_quote((string)$id,'/').'\b/u',$log['subject']??''))unset($logs[$key]);
    update_option('minikids_email_logs',$logs);
    $pending_mail=[];if(function_exists('as_get_scheduled_actions'))foreach(as_get_scheduled_actions(['status'=>'pending','per_page'=>1000]) as $action_id=>$action)if(preg_match('/mail|email/i',$action->get_hook()))$pending_mail[]=['id'=>$action_id,'hook'=>$action->get_hook()];
    $now=wc_get_orders(['limit'=>-1,'return'=>'ids','status'=>array_keys(wc_get_order_statuses())]);sort($now);$original=$before['orders'];sort($original);
    $stock=[];foreach($before['products'] as $p)$stock[$p['id']]=wc_get_product($p['id'])->get_stock_quantity()===$p['stock'];
    $report=['orders_identical'=>$now===$original,'stock_identical'=>!in_array(false,$stock,true),'orders'=>$now,'qa_deleted'=>$ids['orders'],'pending_email_actions'=>$pending_mail,'qa_mail_logs_removed'=>true];
    file_put_contents(dirname(__DIR__,2).'/diseno_minikids/v5/limpieza-pedido-V8.json',wp_json_encode($report,JSON_PRETTY_PRINT));echo wp_json_encode($report).PHP_EOL;exit;
}
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
