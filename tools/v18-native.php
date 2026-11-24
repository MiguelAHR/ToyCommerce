<?php
// Development CLI: native APIs only, loopback local site, mail disabled.
if (PHP_SAPI !== 'cli') exit;
$_SERVER['HTTP_HOST']='localhost:8082'; $_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/' || wp_get_environment_type()!=='local' || get_option('mkp_mail_enabled')==='yes') throw new RuntimeException('Local/mail guard');
$root=dirname(__DIR__,2); $out=$root.'/diseno_minikids/v5/'; $back=$root.'/local-private/v5-backups/v18/';
if(!is_dir($back))mkdir($back,0777,true);
function mk18_snapshot(){
 $r=['comments'=>[],'products'=>[]];
 foreach(get_comments(['type'=>'review','status'=>'all','meta_key'=>'_mk_demo','meta_value'=>'1','number'=>0,'orderby'=>'comment_ID','order'=>'ASC']) as $c){
  $id=(int)$c->comment_post_ID; $p=wc_get_product($id);
  $r['comments'][]=['id'=>(int)$c->comment_ID,'product_id'=>$id,'text'=>$c->comment_content,'author'=>$c->comment_author,'date'=>$c->comment_date,'date_gmt'=>$c->comment_date_gmt,'approved'=>$c->comment_approved,'user_id'=>$c->user_id,'type'=>$c->comment_type,'parent'=>$c->comment_parent,'rating'=>get_comment_meta($c->comment_ID,'rating',true),'verified'=>get_comment_meta($c->comment_ID,'verified',true),'demo'=>get_comment_meta($c->comment_ID,'_mk_demo',true),'meta'=>get_comment_meta($c->comment_ID)];
  if(!isset($r['products'][$id]))$r['products'][$id]=['name'=>$p->get_name(),'category'=>wp_get_post_terms($id,'product_cat',['fields'=>'names']),'description'=>wp_strip_all_tags($p->get_description()),'short'=>wp_strip_all_tags($p->get_short_description()),'age'=>$p->get_attribute('Edad recomendada'),'average'=>get_post_meta($id,'_wc_average_rating',true),'count'=>get_post_meta($id,'_wc_review_count',true),'rating_counts'=>get_post_meta($id,'_wc_rating_count',true),'actual_count'=>get_comments(['post_id'=>$id,'type'=>'review','status'=>'approve','count'=>true])];
 }
 return $r;
}
$mode=$argv[1]??'snapshot';
if($mode==='snapshot'){
 if(is_file($back.'before.json'))throw new RuntimeException('Baseline already exists');
 $data=mk18_snapshot(); if(count($data['comments'])!==135)throw new RuntimeException('Expected 135');
 file_put_contents($back.'before.json',wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
 file_put_contents($out.'comentarios_antes.json',wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
 // Full restoration record stays private, including comment fields and metadata.
 $full=[];foreach($data['comments'] as $row)$full[]=['comment'=>(array)get_comment($row['id']),'meta'=>get_comment_meta($row['id'])];
 file_put_contents($back.'full-comments.json',wp_json_encode($full,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
 echo wp_json_encode(['backed_up'=>count($data['comments']),'products'=>count($data['products'])]);exit;
}
if($mode==='apply'){
 $before=json_decode(file_get_contents($back.'before.json'),true);$current=mk18_snapshot();
 if($before!==$current)throw new RuntimeException('Baseline drift');
 $texts=json_decode(file_get_contents($out.'comentarios_v18.json'),true);
 if(count($texts)!==135)throw new RuntimeException('Expected 135 authored texts');
 foreach($before['comments'] as $c){
  $text=$texts[(string)$c['id']]??'';if(!$text)throw new RuntimeException('Missing text');
  $result=wp_update_comment(['comment_ID'=>$c['id'],'comment_content'=>wp_slash($text)],true);
  if(is_wp_error($result)||!$result)throw new RuntimeException('Comment update failed '.$c['id']);
 }
 $after=mk18_snapshot(); file_put_contents($back.'after.json',wp_json_encode($after,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
 echo wp_json_encode(['updated'=>135]);exit;
}
if($mode==='after'){
 file_put_contents($back.'after.json',wp_json_encode(mk18_snapshot(),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
 $full=json_decode(file_get_contents($back.'full-comments.json'),true);$errors=[];
 foreach($full as $row){$old=$row['comment'];$new=(array)get_comment($old['comment_ID']);unset($old['comment_content'],$new['comment_content']);if($old!==$new||$row['meta']!==get_comment_meta($new['comment_ID']))$errors[]=$new['comment_ID'];}
 file_put_contents($out.'comentarios_campos_nativos.json',wp_json_encode(['unchanged_except_content'=>!$errors,'checked'=>count($full),'errors'=>$errors],JSON_PRETTY_PRINT));
 $all=[];foreach(get_comments(['type'=>'review','status'=>'all','number'=>0]) as $c)$all[]=['id'=>(int)$c->comment_ID,'text'=>$c->comment_content];
 file_put_contents($back.'all-site-reviews.json',wp_json_encode($all,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
 if($errors)throw new RuntimeException('Immutable fields changed');echo '{"snapshot":true,"all_native_fields_and_meta_preserved":true}';exit;
}
throw new RuntimeException('Unknown mode');
