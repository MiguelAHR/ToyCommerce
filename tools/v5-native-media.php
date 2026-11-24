<?php
// CLI only. WordPress image APIs and Woo CRUD; no SQL and no HTTP write endpoint.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['SERVER_NAME']='localhost';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
require_once ABSPATH.'wp-admin/includes/image.php';
$root=dirname(__DIR__);$out=dirname($root).'/diseno_minikids/v5';$private=dirname($root).'/local-private/v5-backups';
wp_mkdir_p($private);wp_mkdir_p($out);
function v5_json($file,$value){if(file_exists($file))throw new RuntimeException('Output exists: '.$file);file_put_contents($file,wp_json_encode($value,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));}
$products=wc_get_products(['limit'=>100,'status'=>'publish']);
$slugs=['bloques-60','memoria-animales','karaoke','pista-carreras','scooter','cocina-chef','tren-madera'];
$attachments=get_posts(['post_type'=>'attachment','posts_per_page'=>-1,'post_status'=>'inherit']);
if(($argv[1]??'')==='backup'){
    $state=['products'=>[],'attachments'=>[]];
    foreach($products as $p)$state['products'][]=['id'=>$p->get_id(),'name'=>$p->get_name(),'image_id'=>$p->get_image_id(),'gallery'=>$p->get_gallery_image_ids(),'prices'=>[$p->get_regular_price(),$p->get_sale_price()],'stock'=>$p->get_stock_quantity()];
    foreach($attachments as $a){$id=$a->ID;$state['attachments'][]=['id'=>$id,'file'=>get_post_meta($id,'_wp_attached_file',true),'metadata'=>wp_get_attachment_metadata($id)];}
    v5_json($private.'/media-before.json',$state);v5_json($out.'/productos-antes.json',$state['products']);
    echo 'Native snapshot saved: '.count($attachments).' attachments, '.count($products).' products'.PHP_EOL;
}elseif(($argv[1]??'')==='repair'){
    if(!is_file($private.'/media-before.json'))throw new RuntimeException('Backup required');
    $result=[];
    foreach($attachments as $a){$id=$a->ID;$file=get_attached_file($id);$found=null;
        foreach($slugs as $s)if(preg_match('/^prod-'.preg_quote($s,'/').'-hero-principal(?:-scaled)?\.jpg$/',basename($file)))$found=$s;
        if(!$found)continue;
        $file=wp_upload_dir()['basedir'].'/2026/10/prod-'.$found.'-hero-principal.jpg';
        if(!is_file($file))throw new RuntimeException('Source absent '.$found);
        $copydir=$private.'/repair-resume-'.$found;wp_mkdir_p($copydir);
        foreach(glob(dirname($file).'/prod-'.$found.'-hero-principal*')as $existing){$copy=$copydir.'/'.basename($existing);if(!file_exists($copy))copy($existing,$copy);}
        // Preserve the existing source and assignment. This repairs missing derivatives,
        // not the catalogue's pre-existing photo/product identity mismatch.
        $meta=wp_get_attachment_metadata($id);
        if(!is_file(get_attached_file($id))||!is_file(dirname($file).'/'.($meta['sizes']['woocommerce_thumbnail']['file']??'missing')))$meta=wp_generate_attachment_metadata($id,$file);
        if(empty($meta['sizes']))throw new RuntimeException('No sizes generated '.$found);
        wp_update_attachment_metadata($id,$meta);
        $legacy=preg_replace('/\.jpg$/','-300x300.jpg',$file);
        if(!is_file($legacy)){ $legacy_size=image_make_intermediate_size($file,300,300,true);if(!$legacy_size||!is_file($legacy))throw new RuntimeException('Legacy thumbnail failed '.$found); }
        $product_ids=[];
        foreach($products as $p)if((int)$p->get_image_id()===$id){$p->set_image_id($id);$p->save();$product_ids[]=$p->get_id();}
        $result[]=['slug'=>$found,'attachment_id'=>$id,'product_ids'=>$product_ids,'source'=>str_replace(wp_upload_dir()['basedir'].'/','',$file),'metadata'=>$meta,'source_identity_verified'=>false];
        echo 'Native sizes regenerated: '.$found.' #'.$id.PHP_EOL;
    }
    if(count($result)!==7)throw new RuntimeException('Expected seven attachments');
    v5_json($out.'/reparacion-medios.json',$result);
}elseif(($argv[1]??'')==='webp'){
    $base=wp_upload_dir()['basedir'];$files=[];
    foreach(['2026/11/minikids-v4-*.png','minikids/prod-*.png','minikids/prod-*.jpg','minikids/*logo*.png','minikids/logo*.jpg']as $pattern)$files=array_merge($files,glob($base.'/'.$pattern));
    foreach($slugs as $s)$files=array_merge($files,glob($base.'/2026/10/prod-'.$s.'-hero-principal*.jpg'));
    $result=[];
    foreach(array_unique($files) as $file){$dest=preg_replace('/\.(png|jpg)$/i','.webp',$file);
        if(is_file($dest))continue;
        $editor=wp_get_image_editor($file);if(is_wp_error($editor))throw new RuntimeException($editor->get_error_message());
        $editor->set_quality(82);$saved=$editor->save($dest,'image/webp');if(is_wp_error($saved))throw new RuntimeException($saved->get_error_message());
        $size=getimagesize($dest);if(!$size||$size['mime']!=='image/webp')throw new RuntimeException('Invalid WebP');
        $result[]=['source'=>substr($file,strlen($base)+1),'webp'=>substr($dest,strlen($base)+1),'quality'=>82,'original_bytes'=>filesize($file),'webp_bytes'=>filesize($dest),'width'=>$size[0],'height'=>$size[1]];
    }
    $manifest=$out.'/conversion-webp.json';if(is_file($manifest)){copy($manifest,$private.'/conversion-webp-before-resume.json');$result=array_merge(json_decode(file_get_contents($manifest),true),$result);unlink($manifest);}
    v5_json($manifest,$result);echo 'WebP siblings generated (originals retained): '.count($result).PHP_EOL;
}elseif(($argv[1]??'')==='fonts'){
    require_once ABSPATH.'wp-admin/includes/file.php';WP_Filesystem();global $wp_filesystem;
    $dest=WPMU_PLUGIN_DIR.'/minikids-v5-assets';wp_mkdir_p($dest);
    foreach(['montserrat-variable-latin.woff2','barlowcondensed-bold-latin.woff2']as $name){
        if(is_file($dest.'/'.$name))copy($dest.'/'.$name,$private.'/'.$name.'.bak-'.gmdate('Ymd-His'));
        if(!$wp_filesystem->put_contents($dest.'/'.$name,file_get_contents(__DIR__.'/v5-assets/fonts-final/'.$name),FS_CHMOD_FILE))throw new RuntimeException('Font write failed');
    }
    foreach(['Montserrat-OFL.txt','BarlowCondensed-OFL.txt']as $name){
        if(is_file($dest.'/'.$name))copy($dest.'/'.$name,$private.'/'.$name.'.bak-'.gmdate('Ymd-His'));
        $wp_filesystem->put_contents($dest.'/'.$name,file_get_contents(dirname($root).'/diseno_minikids/fuentes/'.$name),FS_CHMOD_FILE);
    }
    echo 'Two validated WOFF2 fonts and licenses installed through native WP Filesystem'.PHP_EOL;
}elseif(($argv[1]??'')==='verify'){
    $before=json_decode(file_get_contents($private.'/media-before.json'),true);$check=[];
    foreach($before['products']as $b){$p=wc_get_product($b['id']);$check[]=['id'=>$p->get_id(),'prices_unchanged'=>[$p->get_regular_price(),$p->get_sale_price()]===$b['prices'],'stock_unchanged'=>$p->get_stock_quantity()===$b['stock'],'image_assignment_unchanged'=>$p->get_image_id()===$b['image_id']];}
    v5_json($out.'/crud-verificacion.json',$check);echo wp_json_encode($check).PHP_EOL;
}
