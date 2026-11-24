<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
$out=['categories'=>[],'products'=>[]];
foreach(get_terms(['taxonomy'=>'product_cat','hide_empty'=>false])as $t)$out['categories'][]=['id'=>$t->term_id,'slug'=>$t->slug,'name'=>$t->name,'count'=>$t->count];
foreach(wc_get_products(['limit'=>-1,'status'=>'publish'])as $p)$out['products'][]=['id'=>$p->get_id(),'name'=>$p->get_name(),'sku'=>$p->get_sku(),'categories'=>$p->get_category_ids()];
echo wp_json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
