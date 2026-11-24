<?php
if(PHP_SAPI!=='cli')exit;
define('WP_DISABLE_FATAL_ERROR_HANDLER',true);$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
echo wp_json_encode(['home'=>home_url('/'),'database_name'=>DB_NAME,'database_host_is_loopback'=>(bool)preg_match('/^(?:localhost|127\.0\.0\.1)(?::\d+)?$/D',DB_HOST)],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
