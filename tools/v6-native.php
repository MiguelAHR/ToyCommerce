<?php
// Development-only CLI: native API maintenance; no order writes or email calls.
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/')throw new RuntimeException('Local only');
$out=dirname(__DIR__,2).'/diseno_minikids/v5/';$back=dirname(__DIR__,2).'/local-private/v6-backups/';
$snapshot=function(){
    $data=['home'=>home_url('/'),'locale'=>get_locale(),'hpos'=>get_option('woocommerce_custom_orders_table_enabled'),'gateways'=>[],'products'=>[],'orders_total'=>wc_get_orders(['limit'=>1,'paginate'=>true])->total];
    foreach(WC()->payment_gateways()->get_available_payment_gateways()as $id=>$g)$data['gateways'][$id]=$g->enabled;
    foreach(wc_get_products(['status'=>'publish','limit'=>-1])as $p)$data['products'][]=['id'=>$p->get_id(),'sku'=>$p->get_sku(),'price'=>$p->get_price(),'regular'=>$p->get_regular_price(),'sale'=>$p->get_sale_price(),'stock'=>$p->get_stock_quantity(),'image'=>$p->get_image_id(),'gallery'=>$p->get_gallery_image_ids(),'demo'=>$p->get_meta('_mk_demo'),'source'=>get_attached_file($p->get_image_id()),'url'=>$p->get_permalink()];
    return $data;
};
if(!file_exists($back.'native-before.json'))file_put_contents($back.'native-before.json',wp_json_encode($snapshot(),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
if(($argv[1]??'')==='verify'){
    $data=$snapshot();$before=json_decode(file_get_contents($back.'native-before.json'),true);
    $data['catalog_unchanged']=$data['products']===$before['products'];
    $commercial=function($items){foreach($items as &$item)unset($item['image'],$item['gallery'],$item['source']);return $items;};
    $data['commercial_fields_unchanged']=$commercial($data['products'])===$commercial($before['products']);
    $data['orders_unchanged']=$data['orders_total']===$before['orders_total'];$data['hpos_unchanged']=$data['hpos']===$before['hpos'];$data['gateways_unchanged']=$data['gateways']===$before['gateways'];
    $page=get_post(219);$blocks=parse_blocks($page->post_content);$data['footer_roundtrip']=parse_blocks(serialize_blocks($blocks))===$blocks;
    $data['language_samples']=['shipment'=>__('Shipment','woocommerce'),'change_address'=>__('Change address','woocommerce'),'required'=>__('%s is a required field.','woocommerce'),'email_subject'=>__('Your {site_title} order is now on hold','woocommerce')];
    file_put_contents($out.'native-checks-V6.json',wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));echo wp_json_encode(array_diff_key($data,['products'=>1]),JSON_UNESCAPED_UNICODE).PHP_EOL;exit;
}
// 5: editable local legal draft, clearly marked; no claim that the owner approved it.
$page=get_page_by_path('terminos-y-condiciones');$created=false;
if(!$page){
    $content='<!-- wp:heading --><h2 class="wp-block-heading">Borrador a validar por el dueño</h2><!-- /wp:heading -->'."\n";
    foreach(['Documento de demostración local pendiente de revisión y aprobación del dueño. El contenido puede editarse desde Páginas → Términos y condiciones.','Antes de confirmar tu compra, revisa los productos, cantidades, importes y datos de contacto. Consulta al negocio la disponibilidad y las condiciones aplicables a tu dirección.','El costo, la cobertura y el plazo de entrega se muestran o se coordinan antes de completar la compra. Para consultas sobre pagos, cambios, devoluciones y garantías, contacta al negocio.','Los precios y descuentos mostrados corresponden al catálogo local. Las promociones se sujetan al stock disponible.','Para ayuda, utiliza la página de Contacto. Puedes consultar la Política de privacidad y presentar una reclamación en el Libro de Reclamaciones.']as $text)$content.='<!-- wp:paragraph --><p>'.esc_html($text).'</p><!-- /wp:paragraph -->'."\n";
    $id=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'Términos y condiciones','post_name'=>'terminos-y-condiciones','post_content'=>$content],true);
    if(is_wp_error($id))throw new RuntimeException($id->get_error_message());update_post_meta($id,'_mk_legal_draft','Borrador a validar por el dueño');$created=true;
}else $id=$page->ID;
if(!file_exists($back.'terms-option.json'))file_put_contents($back.'terms-option.json',wp_json_encode(['woocommerce_terms_page_id'=>get_option('woocommerce_terms_page_id')]));
update_option('woocommerce_terms_page_id',$id);
$result=['terms_id'=>$id,'terms_created'=>$created,'terms_url'=>get_permalink($id),'terms_review'=>'Borrador a validar por el dueño','language'=>[]];
// 4: official WordPress translations API + language upgrader; never plugin updates.
require_once ABSPATH.'wp-admin/includes/translation-install.php';require_once ABSPATH.'wp-admin/includes/class-wp-upgrader.php';
$api=translations_api('plugins',['slug'=>'woocommerce','version'=>WC_VERSION]);
if(is_wp_error($api))$result['language']['error']=$api->get_error_message();
else {
    $offers=$api['translations']??[];$offer=null;
    foreach(['es_PE','es_ES']as $locale){foreach($offers as $o){if($o['language']===$locale){$offer=$o;break;}}if($offer)break;}
    if($offer){
        $lang=$offer['language'];$result['language']['locale']=$lang;
        // Prefer es_ES, a complete native Woo fallback for the es_PE storefront.
        if($lang==='es_PE')foreach($offers as $o)if($o['language']==='es_ES'){$offer=$o;$lang='es_ES';break;}
        if(!file_exists(WP_LANG_DIR.'/plugins/woocommerce-'.$lang.'.mo')){
            $upgrade=(object)array_merge($offer,['type'=>'plugin','slug'=>'woocommerce','version'=>WC_VERSION]);
            ob_start();$u=new Language_Pack_Upgrader(new Automatic_Upgrader_Skin());$r=$u->upgrade($upgrade,['clear_update_cache'=>false]);ob_end_clean();
            $result['language']['result']=is_wp_error($r)?$r->get_error_message():(bool)$r;
        }
        $result['language']['installed']=file_exists(WP_LANG_DIR.'/plugins/woocommerce-'.$lang.'.mo');$result['language']['locale']=$lang;
    }else $result['language']['error']='No language offer for installed Woo version';
}
file_put_contents($out.'native-application-V6.json',wp_json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));echo wp_json_encode($result,JSON_UNESCAPED_UNICODE).PHP_EOL;
