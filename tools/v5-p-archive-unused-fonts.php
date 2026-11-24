<?php
if(PHP_SAPI!=='cli')exit;
define('WP_DISABLE_FATAL_ERROR_HANDLER',true);
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';require_once ABSPATH.'wp-admin/includes/file.php';
set_exception_handler(function($e){fwrite(STDERR,get_class($e).': '.$e->getMessage().PHP_EOL);exit(1);});
if(DB_NAME!=='minikids_t3_revision_20261111'||!str_starts_with(home_url('/'),'http://localhost:8082/wordpress/'))throw new RuntimeException('Local site required');
WP_Filesystem();global $wp_filesystem;
$workspace=realpath(dirname(__DIR__,2));$base=WP_CONTENT_DIR.'/uploads/minikids/fonts';$dest=$workspace.'/local-private/v5-backups/unused-provisional-woff2-'.gmdate('Ymd-His');wp_mkdir_p($dest);$records=[];
foreach(['montserrat-variable-latin.woff2','barlowcondensed-bold-latin.woff2','roboto-variable-latin.woff2','rajdhani-bold-latin.woff2']as $name){
    $source=$base.'/'.$name;if(!is_file($source))continue;
    foreach(array_merge(glob(WPMU_PLUGIN_DIR.'/*.php'),glob(WPMU_PLUGIN_DIR.'/*.css'),glob(WPMU_PLUGIN_DIR.'/*.js'))as $code){$text=file_get_contents($code);if(str_contains($text,'/uploads/minikids/fonts/'.$name))throw new RuntimeException('Provisional asset still referenced; preserve it');}
    $resolved=realpath($source);if(!str_starts_with($resolved,$workspace.DIRECTORY_SEPARATOR))throw new RuntimeException('Outside workspace');
    $backup=$dest.'/'.$name;$bytes=$wp_filesystem->get_contents($source);
    if(!$wp_filesystem->put_contents($backup,$bytes,FS_CHMOD_FILE)||hash_file('sha256',$backup)!==hash_file('sha256',$source))throw new RuntimeException('Backup mismatch');
    if(!$wp_filesystem->delete($source,false,'f'))throw new RuntimeException('Archive cleanup failed');
    $records[]=['name'=>$name,'bytes'=>strlen($bytes),'sha256'=>hash_file('sha256',$backup),'backup_relative'=>substr(str_replace('\\','/',$backup),strlen(str_replace('\\','/',$workspace))+1),'unreferenced_by_application'=>true];
}
$output=$workspace.'/diseno_minikids/v5/fuentes-provisionales-archivadas.json';if(file_exists($output))throw new RuntimeException('Preserve prior evidence');file_put_contents($output,wp_json_encode($records,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));echo 'Archived with native WP Filesystem: '.count($records).' unused provisional fonts; TTF and final mu-plugin fonts preserved'.PHP_EOL;
