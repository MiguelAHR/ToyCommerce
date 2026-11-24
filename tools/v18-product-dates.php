<?php
// Read-only forensic check: reconstruct an earlier serialization, never save a product.
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/'||wp_get_environment_type()!=='local')throw new RuntimeException('Local only');
$back=dirname(__DIR__,2).'/local-private/v5-backups/v18/';
$before=json_decode(file_get_contents($back.'inventory-before.json'),true);$after=json_decode(file_get_contents($back.'inventory-after.json'),true);
$result=['verified'=>[],'errors'=>[]];
foreach($before['products'] as $id=>$expected){
 if($expected===$after['products'][$id])continue;
 $p=wc_get_product($id);$data=$p->get_data();foreach(['average_rating','rating_counts','review_count','meta_data'] as $key)unset($data[$key]);$data['attributes']=array_map(fn($a)=>$a->get_data(),$p->get_attributes());
 $encoded=wp_json_encode($data);$needle='"date_modified":'.wp_json_encode($data['date_modified']);$parts=explode($needle,$encoded);
 if(count($parts)!==2)throw new RuntimeException('Date field serialization');
 $current=$data['date_modified']->format('Y-m-d H:i:s');$dateToken=$current.'.000000';
 $upper=strtotime($current.' UTC');$found=null;
 // The pre-V18 V16 native save was on this local day. Search up to two days, backwards.
 for($n=$upper;$n>=$upper-172800;$n--){
  $candidate=gmdate('Y-m-d H:i:s',$n);
  $s=$parts[0].str_replace($dateToken,$candidate.'.000000',$needle).$parts[1];
  if(hash('sha256',$s)===$expected){$found=$candidate;break;}
 }
 if($found!==null){$result['verified'][(string)$id]=['before_date_modified'=>$found,'after_date_modified'=>$current,'all_other_serialized_fields_identical'=>true];echo 'MATCH '.$id." date_modified only\n";}
 else{$result['errors'][]=$id;echo 'UNRESOLVED '.$id."\n";}
}
file_put_contents($back.'product-date-reconstruction.json',wp_json_encode($result,JSON_PRETTY_PRINT));
echo wp_json_encode(['date_only'=>count($result['verified']),'errors'=>$result['errors']]);
if($result['errors'])exit(1);
