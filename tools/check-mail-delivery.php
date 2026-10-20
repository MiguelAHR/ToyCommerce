<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';require dirname(__DIR__).'/wordpress/wp-load.php';
$file=dirname(__DIR__).'/local-private/mail-delivery-qa.json';$report=json_decode(file_get_contents($file),true);$config=json_decode(file_get_contents(mkpn_brevo_path()),true);
$results=[];
foreach($report['accepted'] as $mail) {
    $url=add_query_arg(['messageId'=>$mail['message_id'],'days'=>1,'limit'=>50],'https://api.brevo.com/v3/smtp/statistics/events');
    $response=wp_remote_get($url,['timeout'=>25,'headers'=>['api-key'=>$config['api_key'],'accept'=>'application/json']]);
    $status=is_wp_error($response)?0:wp_remote_retrieve_response_code($response);
    $data=is_wp_error($response)?[]:json_decode(wp_remote_retrieve_body($response),true);
    $events=array_values(array_unique(array_column($data['events']??[],'event')));
    $results[]=['http'=>$status,'events'=>$events,'delivered'=>in_array('delivered',$events,true)];
}
file_put_contents(dirname(__DIR__).'/brain/mail-delivery-checks.json',wp_json_encode($results,JSON_PRETTY_PRINT));
echo wp_json_encode($results),PHP_EOL;
