<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
$root=dirname(__DIR__,2);$out=$root.'/diseno_minikids/v5';$before=json_decode(file_get_contents($out.'/BCDE-native.json'),true);
$changed=[];foreach($before['products']as $d){$p=wc_get_product($d['id']);foreach(['name'=>'get_name','price'=>'get_price','regular'=>'get_regular_price','sale'=>'get_sale_price','stock'=>'get_stock_quantity','sku'=>'get_sku']as $field=>$method)if(!$p||$p->$method()!==$d[$field])$changed[]=$d['id'].':'.$field;}
$footer=get_post_field('post_content',219);$blocks=parse_blocks($footer);$unknown=[];$count=0;
$walk=function($items)use(&$walk,&$unknown,&$count){foreach($items as $b){if($b['blockName']){$count++;if(!WP_Block_Type_Registry::get_instance()->is_registered($b['blockName']))$unknown[]=$b['blockName'];}$walk($b['innerBlocks']);}};$walk($blocks);
$g=[];foreach(WC()->payment_gateways()->get_available_payment_gateways()as $id=>$gateway)$g[]=$id;
$data=['checks'=>['products_unchanged'=>!$changed,'published_98'=>count(wc_get_products(['status'=>'publish','limit'=>-1,'return'=>'ids']))===98,'hpos_yes'=>get_option('woocommerce_custom_orders_table_enabled')==='yes','footer_parse_roundtrip_structure'=>parse_blocks(serialize_blocks($blocks))===$blocks,'footer_known_blocks'=>!$unknown,'only_bacs_cod'=>!array_diff($g,['bacs','cod'])],'changed_product_fields'=>$changed,'footer_blocks'=>$count,'gateways'=>$g,'orders_read_only'=>wc_get_orders(['limit'=>-1,'return'=>'ids']),'note'=>'Parse/serialize structure ignores JSON whitespace; not Gutenberg editor validation; no order mutation.' ];
$name=$argv[1]??'native-checks-BCDE';file_put_contents($out.'/'.$name.'.json',wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));echo wp_json_encode($data['checks'],JSON_PRETTY_PRINT).PHP_EOL;
