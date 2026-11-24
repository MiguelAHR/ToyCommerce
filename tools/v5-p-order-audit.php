<?php
// Read-only Woo order inventory. No order creation/update or raw SQL.
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
$ids=wc_get_orders(['limit'=>-1,'return'=>'ids']);sort($ids,SORT_NUMERIC);
$data=['utc'=>gmdate('c'),'count'=>count($ids),'id_set_sha256'=>hash('sha256',wp_json_encode($ids)),'hpos_active'=>Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()];
$private=dirname(__DIR__,2).'/local-private/v5-backups/P-orders-before-verification.json';
if(($argv[1]??'')==='before'){
    if(file_exists($private))throw new RuntimeException('Preserve order baseline');file_put_contents($private,wp_json_encode($data,JSON_PRETTY_PRINT));
    echo 'Read-only order inventory baseline saved; HPOS '.($data['hpos_active']?'active':'inactive').PHP_EOL;
}elseif(($argv[1]??'')==='after'){
    $before=json_decode(file_get_contents($private),true);$data['inventory_unchanged']=$before['id_set_sha256']===$data['id_set_sha256'];$data['count_before']=$before['count'];
    $dest=dirname(__DIR__,2).'/diseno_minikids/v5/verificacion-pedidos-P.json';if(file_exists($dest))throw new RuntimeException('Preserve prior verification');file_put_contents($dest,wp_json_encode($data,JSON_PRETTY_PRINT));echo wp_json_encode($data).PHP_EOL;
}
