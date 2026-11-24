<?php
// Read-only native state snapshot. No order setters, triggers or mail calls.
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/')throw new RuntimeException('Local only');
$products=[];foreach(wc_get_products(['status'=>'publish','limit'=>-1]) as $p)$products[$p->get_id()]=[$p->get_sku(),$p->get_regular_price(),$p->get_sale_price(),$p->get_price(),$p->get_stock_quantity(),$p->get_image_id(),$p->get_gallery_image_ids()];
$orders=[];foreach(wc_get_orders(['limit'=>-1,'status'=>array_keys(wc_get_order_statuses())]) as $o)$orders[$o->get_id()]=hash('sha256',wp_json_encode($o->get_data()));ksort($orders);
$data=['products'=>$products,'orders'=>$orders,'hpos'=>get_option('woocommerce_custom_orders_table_enabled'),'mail_enabled'=>get_option('mkp_mail_enabled'),'gateways'=>array_map(fn($g)=>[$g->enabled,$g->get_title()],WC()->payment_gateways()->payment_gateways())];
$dir=dirname(__DIR__,2).'/local-private/v9-backups/';
file_put_contents($dir.'native-'.($argv[1]??'before').'.json',wp_json_encode($data,JSON_PRETTY_PRINT));
echo wp_json_encode(['products'=>count($products),'orders'=>array_keys($orders),'hpos'=>$data['hpos'],'mail_enabled'=>$data['mail_enabled']]).PHP_EOL;
