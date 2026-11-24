<?php
// Supplemental native repair of absent gallery derivatives; preserves photo identities.
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';require_once ABSPATH.'wp-admin/includes/image.php';
if(DB_NAME!=='minikids_t3_revision_20261111'||!str_starts_with(home_url('/'),'http://localhost:8082/wordpress/'))throw new RuntimeException('Local V5 site required');
$root=dirname(__DIR__);$out=dirname($root).'/diseno_minikids/v5';$private=dirname($root).'/local-private/v5-backups/P-legacy-derivatives-'.gmdate('Ymd-His');wp_mkdir_p($private);
$before=json_decode(file_get_contents($out.'/productos-antes.json'),true);$ids=[];foreach($before as $p)$ids=array_merge($ids,$p['gallery']);$result=[];
foreach(array_unique($ids)as $id){
    $file=get_attached_file($id);$meta=wp_get_attachment_metadata($id);$missing=!is_file($file);
    foreach(($meta['sizes']??[])as $size)if(!is_file(dirname($file).'/'.$size['file']))$missing=true;
    if(!$missing)continue;
    $source=is_file($file)?$file:preg_replace('/-scaled(?=\.[^.]+$)/','',$file);
    if(!is_file($source)){$result[]=['id'=>$id,'status'=>'original_missing_no_substitution'];continue;}
    file_put_contents($private.'/'.$id.'.json',wp_json_encode(['file'=>get_post_meta($id,'_wp_attached_file',true),'metadata'=>$meta,'source_sha256'=>hash_file('sha256',$source)]));
    $stem=preg_replace('/-scaled$/','',pathinfo($source,PATHINFO_FILENAME));
    foreach(glob(dirname($source).'/'.$stem.'*')as $f)if(basename($f)!==basename($source)&&is_file($f))copy($f,$private.'/'.basename($f));
    $hash=hash_file('sha256',$source);$new=wp_generate_attachment_metadata($id,$source);wp_update_attachment_metadata($id,$new);
    if($hash!==hash_file('sha256',$source))throw new RuntimeException('Original changed');
    $generated=[];foreach(array_merge([get_attached_file($id)],array_map(fn($s)=>dirname($source).'/'.$s['file'],$new['sizes']??[]))as $f){
        if(!is_file($f))throw new RuntimeException('Native derivative missing');$dest=preg_replace('/\.(?:jpe?g|png)$/i','.webp',$f);
        if($dest===$f||is_file($dest))continue;$e=wp_get_image_editor($f);if(is_wp_error($e))throw new RuntimeException('Image editor failed');$e->set_quality(82);$saved=$e->save($dest,'image/webp');if(is_wp_error($saved))throw new RuntimeException($saved->get_error_message());$generated[]=basename($dest);
    }
    $result[]=['id'=>$id,'status'=>'derivatives_repaired','source_unchanged'=>true,'generated_webp'=>$generated,'metadata'=>$new];echo 'Gallery derivative repair #'.$id.PHP_EOL;
}
$dest=$out.'/reparacion-galerias-ampliada.json';if(file_exists($dest))throw new RuntimeException('Preserve prior evidence');file_put_contents($dest,wp_json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
