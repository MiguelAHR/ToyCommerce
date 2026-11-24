<?php
// CLI only. Demo products only (_mk_demo=1): sale price -> ends in .90, via WooCommerce CRUD.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['SERVER_NAME']='localhost';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
$apply=in_array('apply',$argv,true);$log=[];
foreach(wc_get_products(['limit'=>200,'status'=>'publish','meta_key'=>'_mk_demo','meta_value'=>'1']) as $p){
  $reg=(float)$p->get_regular_price();$sale=$p->get_sale_price();if($sale===''||!$reg)continue;
  $new=round((float)$sale-0.9)+0.90; if($new>=$reg)$new=$reg-1; if($new<1)$new=0.9;
  $new=number_format($new,2,'.','');
  $log[]=[$p->get_id(),$p->get_sku(),$reg,(float)$sale,(float)$new];
  if($apply&&$new!==number_format((float)$sale,2,'.','')){$p->set_sale_price($new);$p->save();}
}
$out=dirname(__DIR__,2).'/_trabajo/diseno_minikids/v5/precios-redondeo-'.($apply?'aplicado':'simulacion').'.json';
echo count($log)," productos con oferta\n";foreach(array_slice($log,0,8) as $l)echo implode(' | ',$l),"\n";
@file_put_contents(dirname(__DIR__).'/../diseno_minikids/v5/precios-redondeo-'.($apply?'aplicado':'simulacion').'.json',json_encode($log));
