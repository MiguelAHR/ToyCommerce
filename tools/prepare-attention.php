<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
if(DB_NAME!=='minikeds_dev2')throw new RuntimeException('Base inesperada');
$private=dirname(__DIR__).'/local-private';$backup=$private.'/backups';if(!is_dir($backup))mkdir($backup,0700,true);
global $wpdb;
$db=new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);$db->set_charset('utf8mb4');
$out=fopen($backup.'/before-attention-'.gmdate('Ymd-His').'.sql','xb');fwrite($out,"SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n");
$db->begin_transaction(MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT);
foreach($db->query('SHOW TABLES') as $record){$name=array_values($record)[0];if(!str_starts_with($name,$table_prefix))continue;$table='`'.$name.'`';fwrite($out,"DROP TABLE IF EXISTS $table;\n".$db->query('SHOW CREATE TABLE '.$table)->fetch_row()[1].";\n");foreach($db->query('SELECT * FROM '.$table) as $row){$values=array_map(fn($v)=>$v===null?'NULL':"'".$db->real_escape_string($v)."'",array_values($row));fwrite($out,'INSERT INTO '.$table.' VALUES('.implode(',',$values).");\n");}}
$db->commit();fwrite($out,"SET FOREIGN_KEY_CHECKS=1;\n");fclose($out);
$admin=get_user_by('login','admin');
$app=WP_Application_Passwords::create_new_application_password($admin->ID,['name'=>'MiniKids desarrollo local MCP']);if(is_wp_error($app))throw new RuntimeException('No se pudo crear acceso MCP');
file_put_contents($private.'/mcp-auth.json',wp_json_encode(['url'=>home_url('/wp-json/mcp/mcp-adapter-default-server'),'username'=>'admin','password'=>$app[0]],JSON_PRETTY_PRINT));
$pages=[];foreach(get_posts(['post_type'=>'page','posts_per_page'=>-1,'post_status'=>'any']) as $p)$pages[]=['id'=>$p->ID,'title'=>$p->post_title,'slug'=>$p->post_name];
echo wp_json_encode(['backup'=>true,'mcp_access_created'=>true,'hpos'=>get_option('woocommerce_custom_orders_table_enabled'),'pages'=>$pages,'menus'=>get_nav_menu_locations(),'plugins'=>get_option('active_plugins')]),PHP_EOL;
