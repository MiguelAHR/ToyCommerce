<?php
if(PHP_SAPI!=='cli')exit;
define('WP_ACCESSIBLE_HOSTS','api.wordpress.org,downloads.wordpress.org');
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/')exit('Local only');
$out=dirname(__DIR__,2).'/diseno_minikids/v5/';$back=dirname(__DIR__,2).'/local-private/v7-backups/';
if(($argv[1]??'')==='hooks'){
 $local=mk7_local_debug();
 $remote=function(){return 'https://example.test/';};add_filter('home_url',$remote);
 $outside=mk7_local_debug();remove_filter('home_url',$remote);
 $result=['loopback_debug_allowed'=>$local,'nonloopback_debug_rejected'=>!$outside,'environment'=>wp_get_environment_type(),'wp_debug'=>defined('WP_DEBUG')&&WP_DEBUG];
 file_put_contents($out.'debug-guard-V7.json',wp_json_encode($result,JSON_PRETTY_PRINT));echo wp_json_encode($result).PHP_EOL;exit;
}
if(($argv[1]??'')==='language'){
 require_once ABSPATH.'wp-admin/includes/translation-install.php';
 require_once ABSPATH.'wp-admin/includes/class-wp-upgrader.php';
 add_filter('http_request_timeout',fn()=>12);
 $result=['locale_before'=>get_locale(),'languages_before'=>get_available_languages(),'attempts'=>[]];
 if(!file_exists($back.'language-option.json'))file_put_contents($back.'language-option.json',wp_json_encode(['WPLANG'=>get_option('WPLANG')]));
 foreach(['es_PE','es_ES'] as $locale){
  $r=wp_download_language_pack($locale);$result['attempts'][]=['type'=>'core','locale'=>$locale,'result'=>$r,'installed'=>in_array($locale,get_available_languages(),true)];
  if(in_array($locale,get_available_languages(),true)){update_option('WPLANG',$locale);break;}
 }
 // Official translation updater only; never update WooCommerce itself.
 $api=translations_api('plugins',['slug'=>'woocommerce','version'=>WC_VERSION]);
 if(is_wp_error($api)){
  $result['plugin_api_error']=$api->get_error_message();
  $url='https://downloads.wordpress.org/translation/plugin/woocommerce/'.WC_VERSION.'/es_ES.zip';
  $probe=wp_remote_get($url,['timeout'=>12]);
  $entry=['type'=>'plugin-direct-official','package'=>$url];
  if(is_wp_error($probe)){$entry['error_code']=$probe->get_error_code();$entry['error']=$probe->get_error_message();}
  elseif(wp_remote_retrieve_response_code($probe)!==200)$entry['http_status']=wp_remote_retrieve_response_code($probe);
  else{
   ob_start();$u=new Language_Pack_Upgrader(new Automatic_Upgrader_Skin());$r=$u->upgrade((object)['type'=>'plugin','slug'=>'woocommerce','language'=>'es_ES','version'=>WC_VERSION,'package'=>$url],['clear_update_cache'=>false]);ob_end_clean();
   $entry['result']=is_wp_error($r)?$r->get_error_message():$r;
  }
  $result['attempts'][]=$entry;
 }
 else{
  foreach(['es_PE','es_ES'] as $locale){
   $offer=null;foreach($api['translations']??[] as $o)if($o['language']===$locale){$offer=$o;break;}
   if(!$offer){$result['attempts'][]=['type'=>'plugin','locale'=>$locale,'error'=>'No official offer'];continue;}
   ob_start();$u=new Language_Pack_Upgrader(new Automatic_Upgrader_Skin());$r=$u->upgrade((object)array_merge($offer,['type'=>'plugin','slug'=>'woocommerce','version'=>WC_VERSION]),['clear_update_cache'=>false]);$log=ob_get_clean();
   $installed=is_file(WP_LANG_DIR.'/plugins/woocommerce-'.$locale.'.mo');
   $result['attempts'][]=['type'=>'plugin','locale'=>$locale,'result'=>is_wp_error($r)?$r->get_error_message():$r,'installed'=>$installed,'log'=>strip_tags($log)];
   if($installed)break;
  }
 }
 $result['locale_after']=get_locale();$result['languages_after']=get_available_languages();
 $result['woo_es_PE_installed']=is_file(WP_LANG_DIR.'/plugins/woocommerce-es_PE.mo');$result['woo_es_ES_installed']=is_file(WP_LANG_DIR.'/plugins/woocommerce-es_ES.mo');
 file_put_contents($out.'language-V7.json',wp_json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));echo wp_json_encode($result,JSON_UNESCAPED_UNICODE).PHP_EOL;exit;
}
if(($argv[1]??'')==='audit'){
 try{
 $data=['home'=>home_url('/'),'locale'=>get_locale(),'hpos'=>get_option('woocommerce_custom_orders_table_enabled'),'orders_total'=>wc_get_orders(['limit'=>1,'paginate'=>true])->total,'products'=>[],'gateways'=>[],'untranslated'=>[],'email_subjects'=>[]];
 foreach(WC()->payment_gateways()->get_available_payment_gateways() as $id=>$g)$data['gateways'][$id]=$g->enabled;
 foreach(wc_get_products(['status'=>'publish','limit'=>-1]) as $p)$data['products'][]=['id'=>$p->get_id(),'sku'=>$p->get_sku(),'price'=>$p->get_price(),'regular'=>$p->get_regular_price(),'sale'=>$p->get_sale_price(),'stock'=>$p->get_stock_quantity(),'image'=>$p->get_image_id(),'gallery'=>$p->get_gallery_image_ids(),'source'=>get_attached_file($p->get_image_id()),'url'=>$p->get_permalink(),'demo'=>$p->get_meta('_mk_demo')];
 foreach(['cart','checkout','myaccount','emails'] as $dir){
  $files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator(WC()->plugin_path().'/templates/'.$dir,FilesystemIterator::SKIP_DOTS));
  foreach($files as $f){if($f->getExtension()!=='php')continue;$text=file_get_contents($f->getPathname());
   preg_match_all('/\b(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*\x27((?:[^\x27\\\\]|\\\\.)*)\x27\s*,\s*\x27woocommerce\x27/',$text,$matches);
   foreach($matches[1] as $s){$s=str_replace(["\\'","\\\\"],["'","\\"],$s);$translated=__($s,'woocommerce');if($translated===$s&&preg_match('/[a-zA-Z]{3}/',$s)&&!in_array($s,['SKU:','Total','Total:','Subtotal'],true))$data['untranslated'][$dir][$s]=str_replace(WC()->plugin_path().'/','',$f->getPathname());}
  }
 }
 foreach(WC()->mailer()->get_emails() as $email){
  $entry=['default'=>$email->get_default_subject(),'configured'=>$email->get_option('subject')];
  try{$entry['effective_without_order']=$email->get_subject();}catch(Throwable $e){$entry['effective_without_order']='Requires order context; not rendered';}
  $data['email_subjects'][$email->id]=$entry;
 }
 file_put_contents($out.'native-audit-V7.json',wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
 if(!file_exists($back.'native-before.json'))file_put_contents($back.'native-before.json',wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
 echo wp_json_encode(['locale'=>$data['locale'],'products'=>count($data['products']),'untranslated'=>array_map('count',$data['untranslated']),'email_subjects'=>$data['email_subjects']],JSON_UNESCAPED_UNICODE).PHP_EOL;
 }catch(Throwable $e){echo get_class($e).': '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine().PHP_EOL;exit(1);}
}
