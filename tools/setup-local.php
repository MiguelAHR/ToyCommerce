<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
$root=dirname(__DIR__);$dbName='minikeds_dev2';
$password=getenv('MINIKEDS_DB_PASSWORD');if($password===false)exit("Falta contraseña MySQL en el entorno.\n");
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
$db=new mysqli('127.0.0.1','root',$password);$db->set_charset('utf8mb4');
$db->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");$db->select_db($dbName);
if($db->query('SHOW TABLES')->num_rows)exit("La base minikeds_dev2 ya tiene tablas; se detiene sin sobrescribir.\n");
$db->query("SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO'");
$db->multi_query(file_get_contents($root.'/database/wordpress-sanitized.sql'));
do{if($result=$db->store_result())$result->free();}while($db->more_results()&&$db->next_result());
$count=$db->query('SHOW TABLES')->num_rows;$db->close();
$values=['DB_NAME'=>$dbName,'DB_USER'=>'root','DB_PASSWORD'=>$password,'DB_HOST'=>'127.0.0.1','DB_CHARSET'=>'utf8mb4','DB_COLLATE'=>'','WP_HOME'=>'http://localhost:8082','WP_SITEURL'=>'http://localhost:8082','WP_ENVIRONMENT_TYPE'=>'local','WP_DEBUG'=>false,'WP_MEMORY_LIMIT'=>'256M'];
foreach(['AUTH_KEY','SECURE_AUTH_KEY','LOGGED_IN_KEY','NONCE_KEY','AUTH_SALT','SECURE_AUTH_SALT','LOGGED_IN_SALT','NONCE_SALT'] as $key)$values[$key]=base64_encode(random_bytes(48));
$config="<?php\n";foreach($values as $key=>$value)$config.='define('.var_export($key,true).', '.var_export($value,true).");\n";
$config.="\$table_prefix = 'wp_';\nif(!defined('ABSPATH'))define('ABSPATH',__DIR__.'/');\nrequire_once ABSPATH.'wp-settings.php';\n";
file_put_contents($root.'/wordpress/wp-config.php',$config);
echo "Importadas $count tablas en base aislada minikeds_dev2; configuración local creada.\n";
