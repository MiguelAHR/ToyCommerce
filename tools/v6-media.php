<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/')exit('Local only');
require_once ABSPATH.'wp-admin/includes/file.php';require_once ABSPATH.'wp-admin/includes/media.php';require_once ABSPATH.'wp-admin/includes/image.php';
$out=dirname(__DIR__,2).'/diseno_minikids/v5/';$manifest=json_decode(file_get_contents($out.'imagenes-V6.json'),true);$results=[];
foreach($manifest as &$entry){
    if($entry['product']===91){$entry['status']='rejected';$entry['reason']='Light patch beneath wheels visible on dark contact sheet; not assigned.';continue;}
    if($entry['status']!=='prepared')continue;
    $p=wc_get_product($entry['product']);if(!$p||$p->get_meta('_mk_demo'))throw new RuntimeException('Unexpected product');
    if(hash_file('sha256',get_attached_file($entry['original_media']))!==$entry['original_sha256'])throw new RuntimeException('Original changed');
    $existing=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'fields'=>'ids','meta_key'=>'_mk_v6_product','meta_value'=>$p->get_id()]);
    if(($argv[1]??'')==='verify')$id=$p->get_image_id();elseif($existing)$id=$existing[0];else{
        $tmp=wp_tempnam(basename($entry['file']));copy($entry['file'],$tmp);
        $id=media_handle_sideload(['name'=>basename($entry['file']),'tmp_name'=>$tmp],0,$p->get_name().' · fondo transparente V6');
        if(is_wp_error($id))throw new RuntimeException($id->get_error_message());
        update_post_meta($id,'_mk_v6_product',$p->get_id());update_post_meta($id,'_mk_v6_original',$entry['original_media']);update_post_meta($id,'_wp_attachment_image_alt',$p->get_name().' sin fondo');
    }
    if(($argv[1]??'')!=='verify'){$gallery=$p->get_gallery_image_ids();array_unshift($gallery,$entry['original_media']);$p->set_gallery_image_ids(array_values(array_unique($gallery)));$p->set_image_id($id);$p->save();}
    $results[]=['product'=>$p->get_id(),'new_media'=>$id,'original_media'=>absint($entry['original_media']),'original_in_gallery'=>in_array(absint($entry['original_media']),array_map('absint',$p->get_gallery_image_ids()),true),'original_sha256_unchanged'=>hash_file('sha256',get_attached_file($entry['original_media']))===$entry['original_sha256'],'url'=>wp_get_attachment_url($id),'product_url'=>$p->get_permalink()];
    file_put_contents($out.'medios-V6.json',wp_json_encode($results,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));echo 'Product '.$p->get_id().' media '.$id.PHP_EOL;
}
unset($entry);file_put_contents($out.'imagenes-V6.json',wp_json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
