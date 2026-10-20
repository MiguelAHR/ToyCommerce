<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
if(DB_NAME!=='minikeds_dev2')throw new RuntimeException('Base inesperada');
$page=get_page_by_path('libro-de-reclamaciones');
$id=$page?$page->ID:wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'Libro de Reclamaciones','post_name'=>'libro-de-reclamaciones','post_content'=>'<!-- wp:shortcode -->[minikids_libro_reclamaciones]<!-- /wp:shortcode -->'],true);
if(is_wp_error($id))throw new RuntimeException('No se pudo crear la página');
update_option('mkp_claims_page_id',$id);update_option('mkp_about_page_id',10);
$menu=wp_get_nav_menu_object('Atención y empresa');$menuId=$menu?$menu->term_id:wp_create_nav_menu('Atención y empresa');
if(is_wp_error($menuId))throw new RuntimeException('No se pudo crear menú');
$locations=get_nav_menu_locations();$locations['minikids_atencion']=(int)$menuId;set_theme_mod('nav_menu_locations',$locations);
foreach([(int)$menuId=>[10,(int)$id],26=>[10]] as $menu_id=>$pageIds) {
    $items=wp_get_nav_menu_items($menu_id)?:[];
    foreach($pageIds as $pageId) {
        $exists=false;foreach($items as $item)if($item->object==='page'&&(int)$item->object_id===$pageId)$exists=true;
        if(!$exists)wp_update_nav_menu_item($menu_id,0,['menu-item-object-id'=>$pageId,'menu-item-object'=>'page','menu-item-type'=>'post_type','menu-item-status'=>'publish']);
    }
}
echo wp_json_encode(['claims_page'=>(int)$id,'about_page'=>10,'footer_menu'=>(int)$menuId]),PHP_EOL;
