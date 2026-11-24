<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/')exit('Local only');
require_once ABSPATH.'wp-admin/includes/template.php';
// Invoke only the owned registration callbacks; unrelated admin_init handlers
// depend on the full authenticated admin bootstrap, which this CLI does not use.
global $wp_filter;
foreach($wp_filter['admin_init']->callbacks as $callbacks)foreach($callbacks as $callback){$fn=$callback['function'];if(!$fn instanceof Closure)continue;$reflection=new ReflectionFunction($fn);if(in_array(basename($reflection->getFileName()),['minikids-commerce-v8.php','minikids-negocio.php'],true))$fn();}
$registered=get_registered_settings();$fields=$GLOBALS['wp_settings_fields']['mkpn-business']['mk8-help']??[];
ob_start();do_settings_sections('mkpn-business');$html=ob_get_clean();
$sample=call_user_func($registered['mk8_product_help']['sanitize_callback'],['availability_text'=>'Texto editable '.json_decode('"\ud83d\udca1"'),'pickup_enabled'=>'yes']);
$checks=['registered_in_existing_group'=>($registered['mk8_product_help']['group']??'')==='mkpn_business','all_fields_rendered'=>count($fields)===count(mk8_fields()),'editable_textareas'=>substr_count($html,'name="mk8_product_help[')===count(mk8_fields()),'sanitize_removes_emoji'=>$sample['availability_text']==='Texto editable','pickup_explicit_optin'=>$sample['pickup_enabled']==='yes','care_product_editor'=>has_action('woocommerce_product_options_general_product_data')!==false];
$content=[];$back=dirname(__DIR__,2).'/local-private/v8-backups/';foreach([9,10,11,36] as $id){$before=json_decode(file_get_contents($back.'page-'.$id.'.json'),true);$post=get_post($id);$names=function($text){$walk=function($blocks)use(&$walk){$result=[];foreach($blocks as $block){$result[]=$block['blockName'];$result=array_merge($result,$walk($block['innerBlocks']));}return $result;};return $walk(parse_blocks($text));};$content[$id]=$names($before['content'])===$names($post->post_content);}
$result=['checks'=>$checks,'fields'=>count($fields),'block_structure_preserved'=>$content,'scope'=>'Native Settings API registration/render/sanitize and Gutenberg parser block-name structure. Does not open the editor or save a real admin form.'];
file_put_contents(dirname(__DIR__,2).'/diseno_minikids/v5/ajustes-native-V8.json',wp_json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));echo wp_json_encode($result).PHP_EOL;
