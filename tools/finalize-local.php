<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
global $wpdb;
function mk_dev2_subdir($v){if(is_array($v)){foreach($v as &$x)$x=mk_dev2_subdir($x);unset($x);return $v;}if(!is_string($v))return $v;return preg_replace('~http:(?:\\\\/|/){2}localhost:8082(?!/wordpress|\\\\/wordpress)~','http://localhost:8082/wordpress',$v);}
foreach(['options'=>['option_id',['option_value']],'posts'=>['ID',['post_content','post_excerpt','guid']],'postmeta'=>['meta_id',['meta_value']],'termmeta'=>['meta_id',['meta_value']]] as $suffix=>[$id,$columns]){
 $table=$wpdb->prefix.$suffix;foreach($wpdb->get_results("SELECT * FROM `$table`",ARRAY_A) as $row){$changes=[];foreach($columns as $column){$v=maybe_unserialize($row[$column]);$new=mk_dev2_subdir($v);if($v!==$new)$changes[$column]=maybe_serialize($new);}if($changes)$wpdb->update($table,$changes,[$id=>$row[$id]]);}
}
update_option('home','http://localhost:8082/wordpress');update_option('siteurl','http://localhost:8082/wordpress');wp_cache_flush();
$root=dirname(__DIR__);$private=$root.'/local-private';if(!is_dir($private))mkdir($private,0700,true);
file_put_contents($private.'/.htaccess',"Require all denied\n");
$admin=get_user_by('login','admin');if(!$admin)throw new RuntimeException('No existe admin');
$adminPassword=wp_generate_password(24,true,false);wp_set_password($adminPassword,$admin->ID);
file_put_contents($private.'/access-local.txt',"URL: http://localhost:8082/wordpress/wp-admin/\nUsuario: admin\nContrasena: $adminPassword\n");
$config=[];foreach(['DB_NAME','DB_USER','DB_PASSWORD','DB_HOST','DB_CHARSET','DB_COLLATE','AUTH_KEY','SECURE_AUTH_KEY','LOGGED_IN_KEY','NONCE_KEY','AUTH_SALT','SECURE_AUTH_SALT','LOGGED_IN_SALT','NONCE_SALT'] as $key)$config[$key]=constant($key);
$config+=['WP_HOME'=>'http://localhost:8082/wordpress','WP_SITEURL'=>'http://localhost:8082/wordpress','WP_ENVIRONMENT_TYPE'=>'local','WP_DEBUG'=>false,'WP_MEMORY_LIMIT'=>'256M'];
file_put_contents($private.'/config.json',wp_json_encode($config,JSON_PRETTY_PRINT));
file_put_contents($root.'/wordpress/wp-config.php',"<?php\nforeach (json_decode(file_get_contents(dirname(__DIR__).'/local-private/config.json'), true) as \$name => \$value) define(\$name, \$value);\n\$table_prefix = 'wp_';\nif(!defined('ABSPATH'))define('ABSPATH',__DIR__.'/');\nrequire_once ABSPATH.'wp-settings.php';\n");
echo "URL local final configurada, administrador con contraseña nueva en local-private/access-local.txt\n";
