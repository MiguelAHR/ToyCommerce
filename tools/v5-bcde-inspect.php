<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
$data=['home'=>home_url('/'),'gateways'=>[],'categories'=>[],'hpos'=>get_option('woocommerce_custom_orders_table_enabled'),'products'=>[]];
foreach(WC()->payment_gateways()->get_available_payment_gateways()as $id=>$g)if($g->enabled==='yes')$data['gateways'][$id]=$g->get_title();
foreach(get_terms(['taxonomy'=>'product_cat','hide_empty'=>true])as $t)$data['categories'][]=['name'=>$t->name,'slug'=>$t->slug,'count'=>$t->count];
foreach(wc_get_products(['status'=>'publish','limit'=>-1])as $p)$data['products'][]=['id'=>$p->get_id(),'name'=>$p->get_name(),'price'=>$p->get_price(),'regular'=>$p->get_regular_price(),'sale'=>$p->get_sale_price(),'stock'=>$p->get_stock_quantity(),'sku'=>$p->get_sku(),'brand'=>$p->get_attribute('pa_marca')?:$p->get_attribute('Marca'),'url'=>$p->get_permalink()];
file_put_contents(dirname(__DIR__,2).'/diseno_minikids/v5/BCDE-native.json',wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
echo 'Published '.count($data['products']).'; HPOS '.$data['hpos'].'; gateways '.implode(',',array_keys($data['gateways'])).PHP_EOL;
