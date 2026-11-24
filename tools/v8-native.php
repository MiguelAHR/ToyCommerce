<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/')throw new RuntimeException('Local only');
$out=dirname(__DIR__,2).'/diseno_minikids/v5/';
$back=dirname(__DIR__,2).'/local-private/v8-backups/';if(!is_dir($back))mkdir($back,0700,true);
$products=[];
foreach(wc_get_products(['status'=>'publish','limit'=>-1]) as $p)$products[]=['id'=>$p->get_id(),'name'=>$p->get_name(),'description'=>$p->get_description(),'short'=>$p->get_short_description(),'care'=>$p->get_meta('_mk8_care'),'sku'=>$p->get_sku(),'regular'=>$p->get_regular_price(),'sale'=>$p->get_sale_price(),'price'=>$p->get_price(),'stock'=>$p->get_stock_quantity(),'image'=>$p->get_image_id(),'gallery'=>$p->get_gallery_image_ids(),'attributes'=>array_map(fn($a)=>$a->get_data(),$p->get_attributes()),'url'=>$p->get_permalink(),'source'=>get_attached_file($p->get_image_id())];
$zones=WC_Shipping_Zones::get_zones();$zones[0]=['zone_name'=>'Resto del mundo'];$shipping=[];
foreach($zones as $id=>$zone)foreach((new WC_Shipping_Zone($id))->get_shipping_methods(true) as $m)$shipping[]=['zone'=>$zone['zone_name'],'id'=>$m->id,'instance'=>$m->instance_id,'title'=>$m->get_title(),'settings'=>$m->instance_settings];
$gateways=[];foreach(WC()->payment_gateways()->payment_gateways() as $id=>$g)if($g->enabled==='yes')$gateways[$id]=['title'=>$g->get_title(),'description'=>$g->get_description(),'instructions'=>$g->get_option('instructions')];
$orders=wc_get_orders(['limit'=>-1,'return'=>'ids','status'=>array_keys(wc_get_order_statuses())]);sort($orders);
$data=['products'=>$products,'shipping'=>$shipping,'gateways'=>$gateways,'business'=>mkpn_business(),'orders'=>$orders,'hpos'=>get_option('woocommerce_custom_orders_table_enabled'),'email_from'=>get_option('woocommerce_email_from_address'),'email_name'=>get_option('woocommerce_email_from_name'),'admin_email'=>get_option('woocommerce_new_order_settings'),'mail_enabled'=>get_option('mkp_mail_enabled')];
$mode=$argv[1]??'before';
file_put_contents($back.'native-'.$mode.'.json',wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
echo wp_json_encode(['products'=>count($products),'orders'=>$orders,'shipping'=>$shipping,'gateways'=>$gateways,'business'=>$data['business'],'mail_enabled'=>$data['mail_enabled']],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).PHP_EOL;
