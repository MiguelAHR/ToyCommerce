<?php
if(!defined('WP_CLI')||!WP_CLI)exit;
if(DB_NAME!=='minikeds_dev2_prod')throw new RuntimeException('Base inesperada');
// Configuración propia mediante APIs nativas, nunca tablas de pedidos manualmente.
foreach(WC()->payment_gateways()->payment_gateways() as $id=>$gateway){
    $settings=(array)get_option('woocommerce_'.$id.'_settings',[]);
    $settings['enabled']=in_array($id,['bacs','cod'],true)?'yes':'no';
    update_option('woocommerce_'.$id.'_settings',$settings);
}
update_option('woocommerce_coming_soon','no');update_option('blog_public',1);
foreach(get_users() as $user){
    WP_Application_Passwords::delete_all_application_passwords($user->ID);
    WP_Session_Tokens::get_instance($user->ID)->destroy_all();
}
$admin=get_user_by('login','admin');$password=wp_generate_password(32,true,false);wp_set_password($password,$admin->ID);
$private='/opt/apps/projects/minikids/private';
file_put_contents($private.'/access-dev2.txt',"URL: https://minikids.vitorstream.shop/wp-admin/\nUsuario: admin\nContrasena: ".$password."\n");chmod($private.'/access-dev2.txt',0600);
$app=WP_Application_Passwords::create_new_application_password($admin->ID,['name'=>'MiniKids producción MCP']);
if(is_wp_error($app))throw new RuntimeException('Error creando credencial MCP');
file_put_contents($private.'/mcp-dev2.json',wp_json_encode(['url'=>home_url('/wp-json/mcp/mcp-adapter-default-server'),'username'=>'admin','password'=>$app[0]]));chmod($private.'/mcp-dev2.json',0600);
echo wp_json_encode(['products'=>count(wc_get_products(['status'=>'publish','limit'=>-1])),'hpos'=>get_option('woocommerce_custom_orders_table_enabled'),'theme'=>get_stylesheet(),'business_configured'=>(bool)get_option('mkp_business'),'mail_enabled'=>get_option('mkp_mail_enabled'),'gutenberg_about'=>has_blocks(get_post(10)->post_content),'gutenberg_claims'=>has_blocks(get_post(143)->post_content)]),PHP_EOL;
