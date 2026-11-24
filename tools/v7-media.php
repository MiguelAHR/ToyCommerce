<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/')exit('Local only');
require_once ABSPATH.'wp-admin/includes/file.php';require_once ABSPATH.'wp-admin/includes/media.php';require_once ABSPATH.'wp-admin/includes/image.php';
$out=dirname(__DIR__,2).'/diseno_minikids/v5/';$back=dirname(__DIR__,2).'/local-private/v7-backups/';
$manifest=json_decode(file_get_contents($out.'imagenes-V7.json'),true);$results=[];
if(!file_exists($back.'native-before.json'))throw new RuntimeException('Snapshot required');
foreach($manifest as &$entry){
 if($entry['product']===41){$entry['status']='rejected';$entry['reason']='Image generator completed a new perimeter/base outside the original cropped model. Original miniature village has no visible complete object boundary; no faithful full cutout can be certified. Do not assign.';continue;}
 $p=wc_get_product($entry['product']);if(!$p)throw new RuntimeException('Missing product');
 if(hash_file('sha256',get_attached_file($entry['original_media']))!==$entry['original_sha256'])throw new RuntimeException('Original changed');
 $existing=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'fields'=>'ids','meta_key'=>'_mk_v7_product','meta_value'=>$p->get_id()]);
 if(($argv[1]??'')==='verify')$id=$p->get_image_id();elseif($existing)$id=$existing[0];else{
  $tmp=wp_tempnam(basename($entry['file']));copy($entry['file'],$tmp);
  $id=media_handle_sideload(['name'=>basename($entry['file']),'tmp_name'=>$tmp],0,$p->get_name().' · recorte transparente V7');
  if(is_wp_error($id))throw new RuntimeException($id->get_error_message());
  update_post_meta($id,'_mk_v7_product',$p->get_id());update_post_meta($id,'_mk_v7_original',$entry['original_media']);update_post_meta($id,'_wp_attachment_image_alt',$p->get_name().' sin fondo'.($p->get_id()===39?', solo bloques de madera, sin personas':''));
 }
 if(($argv[1]??'')!=='verify'){
  $gallery=$p->get_gallery_image_ids();$gallery=array_merge([$entry['original_media']],$gallery);
  if($p->get_image_id()!==$entry['original_media']&&$p->get_image_id()!=$id)$gallery[]=$p->get_image_id();
  $p->set_gallery_image_ids(array_values(array_unique(array_map('absint',$gallery))));$p->set_image_id($id);$p->save();
 }
 $entry['status']='assigned';$entry['review']='Compared original and generated object on white and dark composites; likeness accepted, not physical product certification.';
 $results[]=['product'=>$p->get_id(),'new_media'=>$id,'original_media'=>$entry['original_media'],'original_is_first_gallery_image'=>(int)($p->get_gallery_image_ids()[0]??0)===$entry['original_media'],'original_sha256_unchanged'=>hash_file('sha256',get_attached_file($entry['original_media']))===$entry['original_sha256'],'url'=>wp_get_attachment_url($id),'product_url'=>$p->get_permalink()];
 file_put_contents($out.'medios-V7.json',wp_json_encode($results,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));echo 'Product '.$p->get_id().' media '.$id.PHP_EOL;
}
unset($entry);file_put_contents($out.'imagenes-V7.json',wp_json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
