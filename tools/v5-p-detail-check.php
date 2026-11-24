<?php
if(PHP_SAPI!=='cli')exit;
define('WP_DISABLE_FATAL_ERROR_HANDLER',true);$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
$out=dirname(__DIR__,2).'/diseno_minikids/v5';$plan=json_decode(file_get_contents($out.'/catalogo_plan.json'),true);$rows=[];
foreach($plan as $s){$p=wc_get_product(wc_get_product_id_by_sku($s['sku']));
    $rows[]=['id'=>$p->get_id(),'sku'=>$p->get_sku(),'brand_correct'=>$p->get_attribute('Marca')==='MiniKids','short_bullets'=>str_contains($p->get_short_description(),'<ul>')&&str_contains($p->get_short_description(),'<li>'),'long_bullets'=>str_contains($p->get_description(),'<ul>')&&str_contains($p->get_description(),'<li>'),'stock_matches_after_cart'=>$p->get_stock_quantity()===$s['stock'],'prices_match_plan'=>(float)$p->get_regular_price()===(float)$s['regular_price']&&($p->get_sale_price()===''?$s['sale_price']==='':(float)$p->get_sale_price()===(float)$s['sale_price']),'price_demo_flag'=>$p->get_meta('_mk_demo_price_status')===$s['price_status'],'spec_demo_flag'=>$p->get_meta('_mk_demo_spec_status')===$s['spec_status']];
}
$correct=count($rows)===81;foreach($rows as $r)foreach($r as $k=>$v)if(!in_array($k,['id','sku'],true)&&$v!==true)$correct=false;
$result=['utc'=>gmdate('c'),'products'=>$rows,'all_correct'=>$correct];$dest=$out.'/verificacion-detalles-P.json';if(file_exists($dest))throw new RuntimeException('Preserve prior audit');file_put_contents($dest,wp_json_encode($result,JSON_PRETTY_PRINT));echo wp_json_encode(['products'=>count($rows),'all_correct'=>$correct]).PHP_EOL;
