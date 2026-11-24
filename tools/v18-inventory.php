<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/'||wp_get_environment_type()!=='local')throw new RuntimeException('Local only');
$r=['products'=>[],'orders'=>[],'options'=>[],'users'=>[]];
foreach(wc_get_products(['limit'=>-1,'status'=>['publish','draft','pending','private','future']]) as $p){$data=$p->get_data();foreach(['average_rating','rating_counts','review_count','meta_data'] as $key)unset($data[$key]);$data['attributes']=array_map(fn($a)=>$a->get_data(),$p->get_attributes());$r['products'][$p->get_id()]=hash('sha256',wp_json_encode($data));}
foreach(wc_get_orders(['limit'=>-1,'status'=>array_keys(wc_get_order_statuses())]) as $o)$r['orders'][$o->get_id()]=hash('sha256',wp_json_encode($o->get_data()));
foreach(get_users(['orderby'=>'ID','order'=>'ASC']) as $u)$r['users'][$u->ID]=hash('sha256',wp_json_encode($u->data));
foreach(['woocommerce_custom_orders_table_enabled','mkp_mail_enabled','woocommerce_bacs_settings','woocommerce_cod_settings','woocommerce_email_from_address','woocommerce_email_from_name','woocommerce_new_order_settings','woocommerce_enable_myaccount_registration','woocommerce_registration_generate_password'] as $key)$r['options'][$key]=hash('sha256',wp_json_encode(get_option($key)));
$mode=$argv[1]??'';if(!in_array($mode,['before','after'],true))throw new RuntimeException('Mode');
$file=dirname(__DIR__,2).'/local-private/v5-backups/v18/inventory-'.$mode.'.json';if($mode==='before'&&is_file($file))throw new RuntimeException('Baseline exists');
file_put_contents($file,wp_json_encode($r,JSON_PRETTY_PRINT));echo wp_json_encode(['mode'=>$mode,'products'=>count($r['products']),'orders'=>count($r['orders']),'users'=>count($r['users'])]);
