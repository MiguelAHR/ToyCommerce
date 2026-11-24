<?php
/** Plugin Name: MiniKids · Recursos eficientes V5 (FASE A) */
defined('ABSPATH') || exit;

// Use sidecar WebP only when it exists; originals and media IDs stay editable.
function mka_webp_url($url){
    if(!is_string($url))return $url;
    $prefix=content_url('/uploads/');
    $relative_prefix=parse_url($prefix,PHP_URL_PATH);
    if(str_starts_with($url,$relative_prefix))$url=$prefix.substr($url,strlen($relative_prefix));
    if(!str_starts_with($url,$prefix)||!preg_match('/\.(?:png|jpe?g)(?:\?.*)?$/i',$url))return $url;
    $path=rawurldecode(parse_url($url,PHP_URL_PATH)??'');$upload_path=parse_url($prefix,PHP_URL_PATH);
    $relative=substr($path,strlen($upload_path));if(str_contains($relative,'..'))return $url;
    $webp=preg_replace('/\.(?:png|jpe?g)$/i','.webp',$relative);
    return is_file(WP_CONTENT_DIR.'/uploads/'.$webp)?$prefix.$webp:$url;
}
add_filter('wp_get_attachment_url','mka_webp_url',30);
add_filter('wp_get_attachment_image_src',function($image){if($image)$image[0]=mka_webp_url($image[0]);return $image;},30);
add_filter('wp_calculate_image_srcset_meta',function($meta,$size,$src){
    if(!is_array($meta)||!str_ends_with(parse_url($src,PHP_URL_PATH)??'','.webp'))return $meta;
    $meta['file']=preg_replace('/\.(?:png|jpe?g)$/i','.webp',$meta['file']);
    $folder=dirname($meta['file']);
    foreach($meta['sizes']??[] as $name=>$entry){
        $file=preg_replace('/\.(?:png|jpe?g)$/i','.webp',$entry['file']);
        if(is_file(WP_CONTENT_DIR.'/uploads/'.$folder.'/'.$file)){$meta['sizes'][$name]['file']=$file;$meta['sizes'][$name]['mime-type']='image/webp';}
        else unset($meta['sizes'][$name]);
    }
    return $meta;
},30,3);
add_filter('wp_calculate_image_srcset',function($sources){if(is_array($sources))foreach($sources as &$source)$source['url']=mka_webp_url($source['url']);return $sources;},30);
add_filter('wp_get_attachment_image_attributes',function($attr){
    // Blocksy filters attributes before merging its separately computed src.
    // Adding an empty src here would override that URL (even when srcset exists).
    if(isset($attr['src']))$attr['src']=mka_webp_url($attr['src']);
    $attr['decoding']='async';
    if(empty($attr['fetchpriority'])||$attr['fetchpriority']!=='high')$attr['loading']=$attr['loading']??'lazy';
    return $attr;
},30);

function mka_image_markup($markup,$attachment_id=0,$hero=false){
    $html=new WP_HTML_Tag_Processor($markup);
    while($html->next_tag('IMG')){
        $src=$html->get_attribute('src');if(!$src)continue;
        $src=mka_webp_url($src);$html->set_attribute('src',$src);$html->set_attribute('decoding','async');
        if(!$html->get_attribute('width')||!$html->get_attribute('height')){
            $prefix=content_url('/uploads/');
            if(str_starts_with($src,$prefix)){
                $relative=rawurldecode(substr(parse_url($src,PHP_URL_PATH),strlen(parse_url($prefix,PHP_URL_PATH))));
                if(!str_contains($relative,'..')){
                    static $dimensions=[];$file=WP_CONTENT_DIR.'/uploads/'.$relative;
                    if(!isset($dimensions[$file]))$dimensions[$file]=is_file($file)?wp_getimagesize($file):false;
                    if($dimensions[$file]){$html->set_attribute('width',$dimensions[$file][0]);$html->set_attribute('height',$dimensions[$file][1]);}
                }
            }
        }
        if($hero){$html->set_attribute('loading','eager');}
        elseif($html->get_attribute('fetchpriority')!=='high'&&$html->get_attribute('loading')!=='eager'){$html->set_attribute('loading','lazy');}
        if($attachment_id&&!$html->get_attribute('srcset')){
            $set=wp_get_attachment_image_srcset($attachment_id,'full');
            if($set){$html->set_attribute('srcset',$set);$html->set_attribute('sizes',wp_get_attachment_image_sizes($attachment_id,'full'));}
        }
    }
    return $html->get_updated_html();
}
add_filter('render_block_core/image',function($markup,$block){
    static $campaign_image=0;$campaign=str_contains($block['attrs']['className']??'','mk4-image');
    $hero=$campaign&&++$campaign_image<=4;
    return mka_image_markup($markup,(int)($block['attrs']['id']??0),$hero);
},30,2);
add_filter('wp_content_img_tag',function($html,$context,$id){return mka_image_markup($html,(int)$id);},30,3);
add_filter('render_block_core/html','mka_image_markup',30);
add_filter('woocommerce_product_get_image',function($html){return mka_image_markup($html);},30);

add_action('wp_enqueue_scripts',function(){
    wp_dequeue_style('minikids-fonts'); // Replaced with the local brand fonts.
    if(is_page('tienda')||is_shop())wp_dequeue_style('minikids-atencion'); // Claims/about selectors only.
    if(is_front_page())wp_enqueue_script('minikids-campanas-v4',plugins_url('minikids-campanas-v4.js',__FILE__),['minikids-retail'],filemtime(__DIR__.'/minikids-campanas-v4.js'),true);
},100);
add_action('wp_head',function(){
    echo '<link rel="preload" href="'.esc_url(plugins_url('minikids-v5-assets/montserrat-variable-latin.woff2',__FILE__)).'" as="font" type="font/woff2" crossorigin>';
},2);
