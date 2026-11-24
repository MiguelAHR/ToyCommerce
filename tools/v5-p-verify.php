<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
$root=dirname(__DIR__);$out=dirname($root).'/diseno_minikids/v5';$private=dirname($root).'/local-private/v5-backups';
$before=json_decode(file_get_contents($private.'/P-before.json'),true);
$legacy=[];
foreach($before['products']as $b){$p=wc_get_product($b['id']);$d=$p?$p->get_data():[];$fields=['id','name','sku','status','regular_price','sale_price','stock_quantity','image_id','gallery_image_ids','category_ids','description','short_description'];$delta=[];foreach($fields as $k)if(($d[$k]??null)!==($b[$k]??null))$delta[]=$k;$legacy[]=['id'=>$b['id'],'unchanged'=>!$delta,'changed_fields'=>$delta];}
$items=[];$categories=[];$offers=0;$galleries=0;$urls=[];
foreach(wc_get_products(['status'=>'publish','limit'=>-1])as $p){
    if($p->is_on_sale())$offers++;if(count($p->get_gallery_image_ids())>=2)$galleries++;
    foreach($p->get_category_ids()as $cat){$t=get_term($cat,'product_cat');$categories[$t->slug]=($categories[$t->slug]??0)+1;}
    $media=[];foreach(array_merge([$p->get_image_id()],$p->get_gallery_image_ids())as $id){$f=get_attached_file($id);$m=wp_get_attachment_metadata($id);$src=wp_get_attachment_image_src($id,'woocommerce_thumbnail');$full=wp_get_attachment_image_src($id,'full');$card=wp_get_attachment_image_src($id,'mk_demo_card');$card_meta=$m['sizes']['mk_demo_card']??[];
        $card_ok=($card_meta['width']??0)===600&&($card_meta['height']??0)===600&&is_file(dirname($f).'/'.($card_meta['file']??'missing'));
        $media[]=['id'=>$id,'file_exists'=>is_file($f),'full_size'=>[$m['width']??0,$m['height']??0],'alt_present'=>get_post_meta($id,'_wp_attachment_image_alt',true)!=='','card_600'=>$card_ok,'full_url'=>$full[0]??null,'thumbnail_url'=>$src[0]??null,'card_url'=>$card[0]??null];
        if($full)$urls[]=$full[0];if($src)$urls[]=$src[0];if($card)$urls[]=$card[0];
    }
    $items[]=['id'=>$p->get_id(),'sku'=>$p->get_sku(),'demo'=>(string)$p->get_meta('_mk_demo')==='1','regular'=>(float)$p->get_regular_price(),'sale'=>$p->get_sale_price()!==''?(float)$p->get_sale_price():null,'gallery_count'=>count($p->get_gallery_image_ids()),'has_weight_dimensions'=>$p->get_weight()!==''&&$p->get_length()!==''&&$p->get_width()!==''&&$p->get_height()!=='','media'=>$media];
}
$demo=array_values(array_filter($items,fn($x)=>$x['demo']));
$legacy_published=count(array_filter($before['products'],fn($x)=>$x['status']==='publish'));
$checks=['published_98'=>count($items)===98,'demo_81'=>count($demo)===81,'legacy_17_and_existing_draft_unchanged'=>$legacy_published===17&&!array_filter($legacy,fn($x)=>!$x['unchanged']),'category_14_each'=>count($categories)===7&&!array_filter($categories,fn($n)=>$n!==14),'new_gallery_20'=>count(array_filter($demo,fn($x)=>$x['gallery_count']===2))===20,'skus_unique'=>count(array_unique(array_column($items,'sku')))===count($items),'sales_valid'=>!array_filter($items,fn($x)=>$x['sale']!==null&&($x['sale']<=0||$x['sale']>=$x['regular'])),'dimensions_present'=>!array_filter($demo,fn($x)=>!$x['has_weight_dimensions'])];
foreach($demo as $p)foreach($p['media']as $m){if(!$m['file_exists']||!$m['alt_present']||!$m['card_600']||$m['full_size']!==[1200,1200])$checks['all_demo_media_1200_600_alt']=false;}
$checks['all_demo_media_1200_600_alt']=$checks['all_demo_media_1200_600_alt']??true;
$data=['utc'=>gmdate('c'),'checks'=>$checks,'published'=>count($items),'new_demo'=>count($demo),'offers_total'=>$offers,'gallery_total'=>$galleries,'categories'=>$categories,'legacy'=>$legacy,'products'=>$items,'image_urls'=>array_values(array_unique($urls))];
$name=$argv[1]??'verificacion-P';$path=$out.'/'.$name.'.json';if(file_exists($path))throw new RuntimeException('Preserve prior verification');
file_put_contents($path,wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
echo wp_json_encode(['checks'=>$checks,'published'=>count($items),'new_demo'=>count($demo),'categories'=>$categories,'offers_total'=>$offers,'gallery_total'=>$galleries],JSON_PRETTY_PRINT);
