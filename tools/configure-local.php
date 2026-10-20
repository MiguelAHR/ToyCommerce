<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/';
require dirname(__DIR__).'/wordpress/wp-load.php';
global $wpdb;
function mk_dev2_urls($value){
 if(is_array($value)){foreach($value as &$entry)$entry=mk_dev2_urls($entry);unset($entry);return $value;}
 if(!is_string($value))return $value;
 foreach(['http://localhost:8088/wordpress','http://localhost/wordpress','http://localhost:8080/wordpress'] as $old){$value=str_replace([$old,str_replace('/','\\/',$old)],['http://localhost:8082',str_replace('/','\\/','http://localhost:8082')],$value);}
 return $value;
}
$changed=0;
foreach(['options'=>['option_id',['option_value']],'posts'=>['ID',['post_content','post_excerpt','guid']],'postmeta'=>['meta_id',['meta_value']],'termmeta'=>['meta_id',['meta_value']]] as $suffix=>[$id,$columns]){
 $table=$wpdb->prefix.$suffix;
 foreach($wpdb->get_results("SELECT * FROM `$table`",ARRAY_A) as $row){
  $updates=[];foreach($columns as $column){$old=maybe_unserialize($row[$column]);$new=mk_dev2_urls($old);if($new!==$old)$updates[$column]=maybe_serialize($new);}
  if($updates){if($wpdb->update($table,$updates,[$id=>$row[$id]])===false)throw new RuntimeException('No se actualizó '.$table);$changed++;}
 }
}
update_option('home','http://localhost:8082');update_option('siteurl','http://localhost:8082');update_option('blog_public',0);update_option('woocommerce_coming_soon','no');
wp_cache_flush();flush_rewrite_rules(false);
$admin=get_user_by('login','admin');
echo wp_json_encode(['changed_records'=>$changed,'products'=>count(wc_get_products(['status'=>'publish','limit'=>-1])),'theme'=>get_stylesheet(),'admin_exists'=>(bool)$admin,'documented_demo_password_matches'=>$admin?wp_check_password('MiniKids2026!',$admin->user_pass,$admin->ID):false]),PHP_EOL;
