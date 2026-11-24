<?php
/** Local CLI only: native media APIs + WooCommerce CRUD. Never SQL. */
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['SERVER_NAME']='localhost';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
if(DB_NAME!=='minikids_t3_revision_20261111'||!preg_match('/^(?:localhost|127\.0\.0\.1)(?::\d+)?$/D',DB_HOST)||!str_starts_with(home_url('/'),'http://localhost:8082/wordpress/'))throw new RuntimeException('This importer is restricted to the isolated local V5 database/site');
require_once ABSPATH.'wp-admin/includes/image.php';
require_once ABSPATH.'wp-admin/includes/file.php';
$root=dirname(__DIR__);$out=dirname($root).'/diseno_minikids/v5';$private=dirname($root).'/local-private/v5-backups';
wp_mkdir_p($private);
$plan=json_decode(file_get_contents($out.'/catalogo_plan.json'),true,512,JSON_THROW_ON_ERROR);
function mkp_json($path,$data){
    global $private;
    if(is_file($path))copy($path,$private.'/'.basename($path).'.'.gmdate('Ymd-His').'-'.uniqid());
    $tmp=$path.'.tmp';file_put_contents($tmp,wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    if(!rename($tmp,$path))throw new RuntimeException('Manifest replace failed');
}
function mkp_journal($event){global $private;file_put_contents($private.'/P-native-journal.jsonl',wp_json_encode(['utc'=>gmdate('c')]+$event)."\n",FILE_APPEND|LOCK_EX);}
if(($argv[1]??'')==='backup'){
    $dest=$private.'/P-before.json';if(file_exists($dest))throw new RuntimeException('Preserve existing backup');
    $data=['utc'=>gmdate('c'),'products'=>[],'attachments'=>[],'categories'=>[]];
    foreach(wc_get_products(['limit'=>-1,'status'=>['publish','draft','private','pending']])as $p)$data['products'][]=$p->get_data();
    foreach(get_posts(['post_type'=>'attachment','post_status'=>'inherit','posts_per_page'=>-1])as $a){$f=get_attached_file($a->ID);$data['attachments'][]=['id'=>$a->ID,'file'=>$f,'sha256'=>is_file($f)?hash_file('sha256',$f):null,'metadata'=>wp_get_attachment_metadata($a->ID)];}
    foreach(get_terms(['taxonomy'=>'product_cat','hide_empty'=>false])as $t)$data['categories'][]=(array)$t;
    mkp_json($dest,$data);echo 'Native complete product/media snapshot saved'.PHP_EOL;exit;
}
if(!is_file($private.'/P-before.json'))throw new RuntimeException('Run backup first');
if(!is_file($out.'/INFORME_A.md'))throw new RuntimeException('FASE A report required before importing P');
$lock=fopen($private.'/P-import.lock','c');
if(!$lock||!flock($lock,LOCK_EX|LOCK_NB))throw new RuntimeException('Another P import is active; do not duplicate it');
add_image_size('mk_demo_card',600,600,true);
function mkp_media($sku,$view,$parent,$name){
    global $out;
    $key=$sku.'-'.$view;
    $existing=get_posts(['post_type'=>'attachment','post_status'=>'inherit','posts_per_page'=>2,'meta_key'=>'_mk_demo_media_key','meta_value'=>$key,'fields'=>'ids']);
    if(count($existing)>1)throw new RuntimeException('Duplicate media key '.$key);
    if($existing)return (int)$existing[0];
    $source=$out.'/imagenes/'.$sku.($view==='main'?'':'-'.$view).'.png';
    if(!is_file($source))throw new RuntimeException('Image missing '.$source);
    $size=wp_getimagesize($source);if(!$size||$size['mime']!=='image/png')throw new RuntimeException('Invalid PNG');
    $editor=wp_get_image_editor($source);if(is_wp_error($editor))throw new RuntimeException($editor->get_error_message());
    $upload=wp_upload_dir();if($upload['error'])throw new RuntimeException($upload['error']);
    $filename=wp_unique_filename($upload['path'],'minikids-demo-'.strtolower($key).'.png');$file=$upload['path'].'/'.$filename;
    // Sources remain intact outside WordPress. Native editor prepares the 1200px version.
    $r=$editor->resize(1200,1200,true);if(is_wp_error($r))throw new RuntimeException($r->get_error_message());
    $saved=$editor->save($file,'image/png');if(is_wp_error($saved))throw new RuntimeException($saved->get_error_message());
    mkp_journal(['action'=>'insert_attachment','sku'=>$sku,'view'=>$view,'file'=>$file]);
    $id=wp_insert_attachment(['post_title'=>$name.($view==='side'?' · vista lateral':($view==='detail'?' · detalle':'')),'post_mime_type'=>'image/png','post_status'=>'inherit','post_parent'=>$parent],$file,$parent,true);
    if(is_wp_error($id))throw new RuntimeException($id->get_error_message());
    update_post_meta($id,'_mk_demo_media_key',$key);update_post_meta($id,'_mk_demo',1);
    update_post_meta($id,'_wp_attachment_image_alt',$name.' — '.(['main'=>'vista principal','side'=>'vista lateral','detail'=>'detalle de materiales'][$view]));
    $meta=wp_generate_attachment_metadata($id,$file);wp_update_attachment_metadata($id,$meta);
    if(empty($meta['sizes']['mk_demo_card']))throw new RuntimeException('600px card missing '.$key);
    $files=[$file];foreach($meta['sizes']as $s)$files[]=dirname($file).'/'.$s['file'];
    foreach(array_unique($files)as $png){$webp=preg_replace('/\.png$/i','.webp',$png);if(is_file($webp))continue;$e=wp_get_image_editor($png);if(is_wp_error($e))throw new RuntimeException($e->get_error_message());$e->set_quality(82);$saved=$e->save($webp,'image/webp');if(is_wp_error($saved))throw new RuntimeException($saved->get_error_message());}
    mkp_journal(['action'=>'attachment_saved','sku'=>$sku,'view'=>$view,'id'=>$id]);return $id;
}
$catalog=[];$wanted=$argv[2]??null;
foreach($plan as $i=>$s){
    if($wanted&&$s['sku']!==$wanted)continue;
    $existing=wc_get_product_id_by_sku($s['sku']);$p=$existing?wc_get_product($existing):new WC_Product_Simple();
    if($existing&&(string)$p->get_meta('_mk_demo')!=='1')throw new RuntimeException('SKU belongs to non-demo product');
    if($existing&&$p->get_status()==='publish'){
        echo 'Preserve already published demo '.$s['sku'].PHP_EOL;continue;
    }
    if($existing&&$p->get_status()!=='draft')throw new RuntimeException('Preserve owner-managed product status for '.$s['sku']);
    $views=$s['gallery_required']?['main','side','detail']:['main'];$ready=true;
    foreach($views as $v)if(!is_file($out.'/imagenes/'.$s['sku'].($v==='main'?'':'-'.$v).'.png'))$ready=false;
    if(!$ready){echo 'Awaiting images '.$s['sku'].PHP_EOL;continue;}
    if(!$existing){
        mkp_journal(['action'=>'create_product_intent','sku'=>$s['sku']]);
        $p->set_name($s['name']);$p->set_sku($s['sku']);$p->set_status('draft');$p->update_meta_data('_mk_demo',1);$p->save();
    }
    mkp_journal(['action'=>'before_update','sku'=>$s['sku'],'state'=>$p->get_data()]);
    $p->set_name($s['name']);$p->set_regular_price($s['regular_price']);$p->set_sale_price($s['sale_price']);
    $p->set_price($s['sale_price']!==''?$s['sale_price']:$s['regular_price']);
    $p->set_manage_stock(true);$p->set_stock_quantity($s['stock']);$p->set_stock_status('instock');$p->set_backorders('no');
    $p->set_category_ids([$s['category_id']]);$p->set_weight($s['weight']);$p->set_length($s['dimensions']['length']);$p->set_width($s['dimensions']['width']);$p->set_height($s['dimensions']['height']);
    $a=new WC_Product_Attribute();$a->set_name('Marca');$a->set_options(['MiniKids']);$a->set_visible(true);$p->set_attributes([$a]);
    $warning=$s['category_slug']==='peluches'?'Usar bajo supervisión de una persona adulta. Revisar costuras y retirar el juguete si se desprenden componentes.':'Piezas pequeñas: no apto para menores de 3 años. Usar bajo supervisión de una persona adulta.';
    $p->set_short_description('<ul><li>Edad orientativa: '.$s['age'].' años a más.</li><li>Incluye el juguete o set mostrado en la imagen.</li><li>Artículo de demostración; confirmar especificaciones antes de su venta.</li></ul>');
    $p->set_description('<p>'.esc_html($s['name']).' de MiniKids, una propuesta para el juego y la exploración.</p><ul><li>Edad recomendada orientativa: '.$s['age'].' años a más.</li><li>Contenido: juguete o conjunto ilustrado; no incluye accesorios de ambientación.</li><li>'.esc_html($warning).'</li><li>Limpieza: paño suave; evitar inmersión en componentes electrónicos.</li><li>Ficha de demostración: edad, contenido, medidas, peso y precio deben validarse con el producto físico antes de ofrecerlo en venta.</li></ul>');
    $p->update_meta_data('_mk_demo',1);$p->update_meta_data('_mk_demo_price_status',$s['price_status']);$p->update_meta_data('_mk_demo_spec_status',$s['spec_status']);
    $main=mkp_media($s['sku'],'main',$p->get_id(),$s['name']);$gallery=[];
    if($s['gallery_required'])foreach(['side','detail']as $v)$gallery[]=mkp_media($s['sku'],$v,$p->get_id(),$s['name']);
    $p->set_image_id($main);$p->set_gallery_image_ids($gallery);$p->set_status('publish');$p->save();
    mkp_journal(['action'=>'published','sku'=>$s['sku'],'id'=>$p->get_id(),'image_id'=>$main,'gallery'=>$gallery]);
    echo 'Published '.$s['sku'].' #'.$p->get_id().PHP_EOL;
}
// Full authoritative list, including previously imported products on a resumed run.
foreach($plan as $s){$id=wc_get_product_id_by_sku($s['sku']);if(!$id)continue;$p=wc_get_product($id);if((string)$p->get_meta('_mk_demo')!=='1')continue;
    $catalog[]=['id'=>$id,'sku'=>$p->get_sku(),'name'=>$p->get_name(),'status'=>$p->get_status(),'url'=>get_permalink($id),'category'=>$s['category_slug'],'currency'=>get_woocommerce_currency(),'regular_price'=>$p->get_regular_price(),'sale_price'=>$p->get_sale_price(),'stock'=>$p->get_stock_quantity(),'weight_kg'=>$p->get_weight(),'dimensions_cm'=>$p->get_dimensions(false),'age_recommended_demo'=>$s['age'],'short_description'=>$p->get_short_description(),'description'=>$p->get_description(),'brand'=>'MiniKids','_mk_demo'=>$p->get_meta('_mk_demo'),'image_id'=>$p->get_image_id(),'image_url'=>wp_get_attachment_url($p->get_image_id()),'gallery_ids'=>$p->get_gallery_image_ids(),'gallery_urls'=>array_map('wp_get_attachment_url',$p->get_gallery_image_ids()),'generated_source'=>'imagenes/'.$s['sku'].'.png','price_status'=>$s['price_status'],'spec_status'=>$s['spec_status']];
}
mkp_json($out.'/catalogo_demo.json',$catalog);echo 'Manifest products: '.count($catalog).PHP_EOL;
