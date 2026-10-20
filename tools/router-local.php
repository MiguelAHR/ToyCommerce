<?php
$path=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)??'/');
if($path==='/'){header('Location: /wordpress/',true,302);return true;}
if(!str_starts_with($path,'/wordpress/')){http_response_code(403);exit('Acceso restringido');}
if(str_starts_with($path,'/wordpress/wp-content/qa-evidencia/')||preg_match('~\.bak(?:-|$)|/debug\.log$~i',$path)){http_response_code(403);exit('Acceso restringido');}
if(preg_match('~(?:^|/)(?:\.[^/]+|wp-config(?:\.example|-sample)?\.php|test_home\.html)(?:/|$)~i',$path)){http_response_code(403);exit('Acceso restringido');}
$root=realpath(dirname(__DIR__).'/wordpress');$target=realpath(dirname(__DIR__).$path);
if($target&&!str_starts_with($target,$root.DIRECTORY_SEPARATOR)&&$target!==$root){http_response_code(403);exit;}
if($path!=='/'&&$target&&is_file($target))return false;
if($target&&is_dir($target)&&is_file($target.'/index.php')){require $target.'/index.php';return true;}
require $root.'/index.php';
