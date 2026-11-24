<?php
/** Plugin Name: MiniKids · Catálogo demo editable */
defined('ABSPATH')||exit;
add_action('restrict_manage_posts',function($type){
    if($type!=='product')return;$selected=sanitize_key(wp_unslash($_GET['mk_demo_filter']??''));
    echo '<select name="mk_demo_filter" aria-label="Filtrar productos demo"><option value="">Todo el catálogo</option>';
    foreach(['demo'=>'Solo demo MiniKids','original'=>'Sin marca demo']as $value=>$label)echo '<option value="'.esc_attr($value).'" '.selected($selected,$value,false).'>'.esc_html($label).'</option>';
    echo '</select>';
});
add_action('pre_get_posts',function($query){
    if(!is_admin()||!$query->is_main_query()||$query->get('post_type')!=='product')return;
    $value=sanitize_key(wp_unslash($_GET['mk_demo_filter']??''));if(!in_array($value,['demo','original'],true))return;
    $meta=(array)$query->get('meta_query');
    $meta[]=$value==='demo'?['key'=>'_mk_demo','value'=>'1','compare'=>'=']:['relation'=>'OR',['key'=>'_mk_demo','compare'=>'NOT EXISTS'],['key'=>'_mk_demo','value'=>'1','compare'=>'!=']];
    $query->set('meta_query',$meta);
});
add_filter('display_post_states',function($states,$post){if($post->post_type==='product'&&(string)get_post_meta($post->ID,'_mk_demo',true)==='1')$states['mk_demo']='DEMO local';return $states;},10,2);
add_action('woocommerce_product_options_general_product_data',function(){
    global $product_object;if(!$product_object)return;
    woocommerce_wp_checkbox(['id'=>'_mk_demo','label'=>'Producto demo local','value'=>(string)$product_object->get_meta('_mk_demo')==='1'?'yes':'no','description'=>'Identifica fichas de demostración. Reemplaza y valida datos e imágenes antes de retirar esta marca.']);
});
add_action('woocommerce_admin_process_product_object',function($product){
    // WooCommerce verifies capabilities and the product editor nonce before this hook.
    if(isset($_POST['_mk_demo']))$product->update_meta_data('_mk_demo',1);else $product->delete_meta_data('_mk_demo');
});
