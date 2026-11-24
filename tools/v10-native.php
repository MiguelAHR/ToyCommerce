<?php
// Development helper: local CLI only, native read/CRUD. Never sends orders or mail.
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/')throw new RuntimeException('Local only');
$back=dirname(__DIR__,2).'/local-private/v10-backups/';
$out=dirname(__DIR__,2).'/diseno_minikids/v5/';
$mode=$argv[1]??'before';
$products=wc_get_products(['status'=>['publish','draft','pending','private','future'],'limit'=>-1]);
function mk10_normalize_age($s){return preg_replace_callback('/\b(\d+)\s*a\s*\+\s*años\b/iu',fn($m)=>$m[1].((int)$m[1]===1?' año':' años').' en adelante',$s);}
if($mode==='normalize'){
 if(!is_file($back.'native-before.json'))throw new RuntimeException('Missing backup');
 $changes=[];
 foreach($products as $p){
  $rows=[];
  foreach(['description','short_description'] as $field){$get='get_'.$field;$set='set_'.$field;$old=$p->$get();$new=mk10_normalize_age($old);if($old!==$new){$p->$set($new);$rows[]=['field'=>$field,'before'=>$old,'after'=>$new];}}
  // Clone attribute value objects so Woo detects the change against its original data.
  $attrs=array_map(fn($a)=>clone $a,$p->get_attributes());$attr_changed=false;
  foreach($attrs as $attr){
   $options=$attr->get_options();$next=[];
   foreach($options as $option){
    if($attr->is_taxonomy()){
     $term=get_term((int)$option,$attr->get_name());if(!$term||is_wp_error($term)){ $next[]=$option;continue; }
     $new=mk10_normalize_age($term->name);
     if($new===$term->name){$next[]=$option;continue;}
     $existing=term_exists($new,$attr->get_name());
     if(!$existing)$existing=wp_insert_term($new,$attr->get_name());
     if(is_wp_error($existing))throw new RuntimeException($existing->get_error_message());
     $next[]=(int)$existing['term_id'];$rows[]=['field'=>$attr->get_name(),'before'=>$term->name,'after'=>$new];$attr_changed=true;
    }else{
     $new=mk10_normalize_age((string)$option);$next[]=$new;
     if($new!==$option){$rows[]=['field'=>$attr->get_name(),'before'=>$option,'after'=>$new];$attr_changed=true;}
    }
   }
   $attr->set_options($next);
  }
  if($attr_changed)$p->set_attributes($attrs);
  $care=$p->get_meta('_mk8_care');if(is_array($care)){$new=array_map('mk10_normalize_age',$care);if($new!==$care){$p->update_meta_data('_mk8_care',$new);$rows[]=['field'=>'_mk8_care','before'=>$care,'after'=>$new];}}
  if($rows){$p->save();$changes[]=['id'=>$p->get_id(),'sku'=>$p->get_sku(),'changes'=>$rows];}
 }
 file_put_contents($out.'edades-V10.json',wp_json_encode(['scanned'=>count($products),'corrected'=>$changes],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
 echo wp_json_encode(['scanned'=>count($products),'corrected_ids'=>array_column($changes,'id')]).PHP_EOL;exit;
}
$data=['products'=>[],'orders'=>[],'options'=>[]];
foreach($products as $p)$data['products'][$p->get_id()]=['data'=>$p->get_data(),'attributes'=>array_map(fn($a)=>$a->get_data(),$p->get_attributes()),'care'=>$p->get_meta('_mk8_care')];
foreach(wc_get_orders(['limit'=>-1,'status'=>array_keys(wc_get_order_statuses())]) as $order)$data['orders'][$order->get_id()]=hash('sha256',wp_json_encode($order->get_data()));
foreach(['woocommerce_custom_orders_table_enabled','mkp_mail_enabled','woocommerce_bacs_settings','woocommerce_cod_settings','woocommerce_email_from_address','woocommerce_email_from_name','woocommerce_new_order_settings'] as $key)$data['options'][$key]=get_option($key);
file_put_contents($back.'native-'.$mode.'.json',wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
$bad=[];foreach($products as $p){$texts=[$p->get_description(),$p->get_short_description(),implode(' ',(array)$p->get_meta('_mk8_care'))];foreach($p->get_attributes() as $a)$texts[]=$p->get_attribute($a->get_name());foreach($texts as $s)if(mk10_normalize_age($s)!==$s){$bad[]=$p->get_id();break;}}
echo wp_json_encode(['products'=>count($products),'orders'=>count($data['orders']),'malformed_age_ids'=>$bad,'sale_MK095'=>wc_get_product_id_by_sku('MK-095')]).PHP_EOL;
