<?php
$path=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)??'/');
if($path==='/'){header('Location: /wordpress/',true,302);return true;}
if(!str_starts_with($path,'/wordpress/')){http_response_code(403);exit('Acceso restringido');}
if(str_starts_with($path,'/wordpress/wp-content/qa-evidencia/')||preg_match('~\.bak(?:-|$)|/debug\.log$~i',$path)){http_response_code(403);exit('Acceso restringido');}
if(preg_match('~(?:^|/)(?:\.[^/]+|wp-config(?:\.example|-sample)?\.php|test_home\.html)(?:/|$)~i',$path)){http_response_code(403);exit('Acceso restringido');}
$root=realpath(dirname(__DIR__).'/wordpress');$target=realpath(dirname(__DIR__).$path);
if($target&&!str_starts_with($target,$root.DIRECTORY_SEPARATOR)&&$target!==$root){http_response_code(403);exit;}
if($target&&is_file($target)&&strtolower(pathinfo($target,PATHINFO_EXTENSION))!=='php'){
    $mime=['css'=>'text/css; charset=utf-8','js'=>'application/javascript; charset=utf-8','png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','gif'=>'image/gif','webp'=>'image/webp','svg'=>'image/svg+xml','ico'=>'image/x-icon','woff'=>'font/woff','woff2'=>'font/woff2','ttf'=>'font/ttf'];
    $ext=strtolower(pathinfo($target,PATHINFO_EXTENSION));
    if(!isset($mime[$ext]))return false;
    $etag='"'.dechex(filemtime($target)).'-'.dechex(filesize($target)).'"';
    header('Content-Type: '.$mime[$ext]);header('Cache-Control: public, max-age=604800');
    header('ETag: '.$etag);header('Last-Modified: '.gmdate('D, d M Y H:i:s',filemtime($target)).' GMT');
    header('X-Content-Type-Options: nosniff');
    if(($_SERVER['HTTP_IF_NONE_MATCH']??'')===$etag){http_response_code(304);return true;}
    header('Content-Length: '.filesize($target));
    if($_SERVER['REQUEST_METHOD']!=='HEAD')readfile($target);
    return true;
}
// A missing asset must be a real 404, never WordPress's canonical redirect to home.
if(!$target&&preg_match('~\.(?:png|jpe?g|gif|webp|svg|ico|woff2?|ttf|css|js)$~i',$path)){http_response_code(404);header('Content-Type: text/plain; charset=utf-8');exit('Archivo no encontrado');}
if($path!=='/'&&$target&&is_file($target))return false;
if($target&&is_dir($target)&&is_file($target.'/index.php')){require $target.'/index.php';return true;}
require $root.'/index.php';
