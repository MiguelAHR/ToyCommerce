<?php
if(PHP_SAPI!=='cli')exit;
define('WP_ADMIN',true);$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/wp-admin/edit.php';
require dirname(__DIR__).'/wordpress/wp-load.php';
$result=[];
foreach(['demo','original']as $filter){
    $_GET['mk_demo_filter']=$filter;$q=new WP_Query();$GLOBALS['wp_the_query']=$q;
    $ids=$q->query(['post_type'=>'product','post_status'=>'publish','posts_per_page'=>-1,'fields'=>'ids']);
    $result[$filter]=['count'=>count($ids),'ids'=>$ids];
}
$result['correct_counts']=$result['demo']['count']===81&&$result['original']['count']===17;
$dest=dirname(__DIR__,2).'/diseno_minikids/v5/verificacion-admin-P.json';if(file_exists($dest))throw new RuntimeException('Preserve prior output');
file_put_contents($dest,wp_json_encode($result,JSON_PRETTY_PRINT));echo wp_json_encode($result);
