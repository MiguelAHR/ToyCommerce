<?php
if(!defined('WP_CLI')||!WP_CLI)exit;
$business=mkpn_business();$mail=json_decode(file_get_contents(mkpn_brevo_path()),true);
$response=wp_remote_get('https://api.brevo.com/v3/account',['headers'=>['api-key'=>$mail['api_key']],'timeout'=>20]);
$gateways=[];foreach(WC()->payment_gateways()->payment_gateways() as $id=>$gateway)if($gateway->enabled==='yes')$gateways[]=$id;
$users=[];foreach(get_users(['role'=>'administrator']) as $user)$users[]=$user->user_login;
echo wp_json_encode(['environment'=>wp_get_environment_type(),'hpos'=>get_option('woocommerce_custom_orders_table_enabled'),'products'=>count(wc_get_products(['status'=>'publish','limit'=>-1])),'gateways'=>$gateways,'administrators'=>$users,'mail_provider_account_status'=>wp_remote_retrieve_response_code($response),'private_key_writable'=>is_writable(mkpn_brevo_path()),'mail_enabled'=>get_option('mkp_mail_enabled'),'claims_page'=>get_post_status(get_option('mkp_claims_page_id')),'business_complete'=>!empty($business['ruc'])&&!empty($business['address']),'legacy_mcp_disabled'=>!is_plugin_active('wordpress-mcp/wordpress-mcp.php')]),PHP_EOL;
