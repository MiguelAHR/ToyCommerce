<?php
// Related missing derivatives found while verifying the requested product page.
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';require_once ABSPATH.'wp-admin/includes/image.php';
$private=dirname(__DIR__,2).'/local-private/v5-backups/galleries-'.gmdate('Ymd-His');wp_mkdir_p($private);$result=[];
foreach([173,174]as $id){
    $file=get_attached_file($id);$source=preg_replace('/-scaled(?=\.jpg$)/','',$file);
    if(!is_file($source))throw new RuntimeException('No original gallery source');
    file_put_contents($private.'/'.$id.'.json',wp_json_encode(['file'=>get_post_meta($id,'_wp_attached_file',true),'metadata'=>wp_get_attachment_metadata($id)]));
    $stem=pathinfo($source,PATHINFO_FILENAME);
    foreach(glob(dirname($source).'/'.$stem.'*')as $f)copy($f,$private.'/'.basename($f));
    $meta=wp_generate_attachment_metadata($id,$source);wp_update_attachment_metadata($id,$meta);
    $webps=[];
    foreach(glob(dirname($source).'/'.$stem.'*.jpg')as $f){
        $dest=substr($f,0,-4).'.webp';if(is_file($dest))continue;
        $editor=wp_get_image_editor($f);if(is_wp_error($editor))throw new RuntimeException('Invalid gallery image');
        $editor->set_quality(82);$saved=$editor->save($dest,'image/webp');if(is_wp_error($saved))throw new RuntimeException('WebP failed');$webps[]=basename($dest);
    }
    $result[]=['id'=>$id,'metadata'=>$meta,'webp_files'=>$webps,'source_identity_verified'=>false];echo 'Gallery derivatives restored: '.$id.PHP_EOL;
}
$out=dirname(__DIR__,2).'/diseno_minikids/v5/reparacion-galeria.json';if(file_exists($out))throw new RuntimeException('Evidence exists');file_put_contents($out,wp_json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
