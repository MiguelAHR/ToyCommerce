<?php
if(PHP_SAPI!=='cli')exit;
// This CLI run alone may download the explicitly requested official language pack.
define('WP_ACCESSIBLE_HOSTS','api.wordpress.org,downloads.wordpress.org');
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';require_once ABSPATH.'wp-admin/includes/class-wp-upgrader.php';
$url='https://downloads.wordpress.org/translation/plugin/woocommerce/'.WC_VERSION.'/es_ES.zip';
$probe=wp_remote_get($url,['timeout'=>20]);$result=['package'=>$url];
if(is_wp_error($probe))$result['error']=$probe->get_error_message();
elseif(wp_remote_retrieve_response_code($probe)!==200)$result['http_status']=wp_remote_retrieve_response_code($probe);
else{
    $offer=(object)['type'=>'plugin','slug'=>'woocommerce','language'=>'es_ES','version'=>WC_VERSION,'package'=>$url];
    ob_start();$u=new Language_Pack_Upgrader(new Automatic_Upgrader_Skin());$r=$u->upgrade($offer,['clear_update_cache'=>false]);ob_end_clean();
    $result['result']=is_wp_error($r)?$r->get_error_message():(bool)$r;$result['installed']=file_exists(WP_LANG_DIR.'/plugins/woocommerce-es_ES.mo');
}
file_put_contents(dirname(__DIR__,2).'/diseno_minikids/v5/language-V6.json',wp_json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));echo wp_json_encode($result,JSON_UNESCAPED_UNICODE).PHP_EOL;
