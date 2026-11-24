<?php
if(PHP_SAPI!=='cli')exit;
define('WP_ACCESSIBLE_HOSTS','api.brevo.com');
$_SERVER['HTTP_HOST']='localhost:8082';require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/')exit('Local only');
$path=mkpn_brevo_path();$readable=is_readable($path);$report=['mail_enabled'=>get_option('mkp_mail_enabled'),'credential_readable'=>$readable,'owner'=>mkpn_business()['email']??'','provider_events_checked'=>false,'delivered'=>false];
if($readable){$c=json_decode(file_get_contents($path),true);$r=wp_remote_get('https://api.brevo.com/v3/account',['timeout'=>12,'headers'=>['api-key'=>$c['api_key'],'accept'=>'application/json']]);$report['provider_http']=is_wp_error($r)?0:wp_remote_retrieve_response_code($r);if(is_wp_error($r))$report['provider_error']=$r->get_error_message();}
else $report['blocker']='No hay credencial Brevo legible en la ruta configurada de esta copia. No es posible autenticar consulta de eventos ni confirmar delivered.';
$out=dirname(__DIR__,2).'/diseno_minikids/v5/';file_put_contents($out.'correo-preflight-V8.json',wp_json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));echo wp_json_encode($report,JSON_UNESCAPED_UNICODE).PHP_EOL;
