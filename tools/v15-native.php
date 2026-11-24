<?php
// Auxiliary native CLI only. No SQL writes, orders, real email or external destination.
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/'||wp_get_environment_type()!=='local'||get_option('mkp_mail_enabled')==='yes')throw new RuntimeException('Local, mail-disabled environment required');
$out=dirname(__DIR__,2).'/diseno_minikids/v5/';$back=dirname(__DIR__,2).'/local-private/v15-backups/';
$mode=$argv[1]??'inspect';
if($mode==='reset-qa-limits'){
 foreach(['127.0.0.1','::1'] as $ip)foreach(['login','register','lost'] as $auth_mode)delete_transient('mk15_auth_'.hash_hmac('sha256',$ip.'|'.$auth_mode,wp_salt('nonce')));
 echo '{"reset":true}';exit;
}
function mk15_inventory(){
 $data=['products'=>[],'orders'=>[],'users'=>[],'options'=>[]];
 foreach(wc_get_products(['limit'=>-1,'status'=>['publish','draft','pending','private','future']]) as $p){
  $row=$p->get_data();foreach(['average_rating','rating_counts','review_count','meta_data'] as $key)unset($row[$key]);
  $row['attributes']=array_map(fn($a)=>$a->get_data(),$p->get_attributes());
  $data['products'][$p->get_id()]=hash('sha256',wp_json_encode($row));
 }
 foreach(wc_get_orders(['limit'=>-1,'status'=>array_keys(wc_get_order_statuses())]) as $p)$data['orders'][$p->get_id()]=hash('sha256',wp_json_encode($p->get_data()));
 foreach(get_users(['orderby'=>'ID','order'=>'ASC']) as $u)$data['users'][]=['id'=>$u->ID,'email'=>$u->user_email,'roles'=>$u->roles,'login'=>$u->user_login];
 foreach(['woocommerce_custom_orders_table_enabled','mkp_mail_enabled','woocommerce_bacs_settings','woocommerce_cod_settings','woocommerce_email_from_address','woocommerce_email_from_name','woocommerce_new_order_settings'] as $key)$data['options'][$key]=get_option($key);
 return $data;
}
if($mode==='before'||$mode==='after'){
 $data=mk15_inventory();$file=$back.'native-'.$mode.'.json';
 if($mode==='before'&&is_file($file))throw new RuntimeException('Baseline already exists');
 file_put_contents($file,wp_json_encode($data,JSON_PRETTY_PRINT));
 echo wp_json_encode(['mode'=>$mode,'products'=>count($data['products']),'orders'=>count($data['orders']),'users'=>count($data['users']),'qa_exists'=>(bool)get_user_by('email','qa-registro@example.invalid')]);exit;
}
if($mode==='qa-inspect'){
 $u=get_user_by('email','qa-registro@example.invalid');echo wp_json_encode(['id'=>$u?$u->ID:null,'roles'=>$u?$u->roles:[],'first'=>$u?$u->first_name:null,'last'=>$u?$u->last_name:null]);exit;
}
if($mode==='qa-cleanup'){
 $baseline=json_decode(file_get_contents($back.'native-before.json'),true);
 foreach($baseline['users'] as $row)if($row['email']==='qa-registro@example.invalid')throw new RuntimeException('QA existed before');
 $u=get_user_by('email','qa-registro@example.invalid');
 if($u){if($u->roles!==['customer'])throw new RuntimeException('Unexpected QA role');require_once ABSPATH.'wp-admin/includes/user.php';wp_delete_user($u->ID);}
 echo wp_json_encode(['qa_absent'=>!get_user_by('email','qa-registro@example.invalid'),'inventory_identical'=>mk15_inventory()['users']===$baseline['users']]);exit;
}
if($mode==='seed'){
 $ids=wc_get_products(['limit'=>-1,'status'=>'publish','orderby'=>'ID','order'=>'ASC','return'=>'ids']);
 $patterns=[[5],[4,5],[4,4,5],[3,4,5,5],[2,3,4,4,4],[4,4,4,5,5,5],[3,4,4,4,5,5,5],[3,4,4,4,5,5,5,5],[5,5],[4],[3,4,4,5,5]];
 $names=['Lucía','Mateo','Valeria','Diego','Camila','José','Mariana','Luis','Sofía','Andrés'];
 $rows=[];$products=[];$serial=0;
 for($i=0;$i<35;$i++){
  $id=$ids[(int)floor($i*count($ids)/35)];$p=wc_get_product($id);$products[]=$id;
  $existing=get_comments(['post_id'=>$id,'type'=>'review','status'=>'approve','meta_key'=>'_mk_demo','meta_value'=>'1']);
  if(!$existing){
   foreach($patterns[$i%count($patterns)] as $j=>$rating){
    $serial++;$age=$p->get_attribute('Edad recomendada')?:$p->get_attribute('pa_edad-recomendada');
    $snippets=[
     'Elegimos '.$p->get_name().($age?' para la edad recomendada de '.$age:' siguiendo la edad indicada').'. Fue entretenido durante el juego en familia.',
     'Probamos '.$p->get_name().'. La calidad nos pareció adecuada y fue fácil empezar a jugar.',
     'El paquete de '.$p->get_name().' llegó bien protegido. Lo usamos con acompañamiento de un adulto.',
     'Con '.$p->get_name().' pasamos una tarde divertida. Nos hubiera gustado una guía más detallada.',
     'A '.$p->get_name().' le dimos varios usos en casa. Hay detalles de acabado que podrían mejorar.'
    ];
    $text='[DEMOSTRACIÓN] '.$snippets[($i+$j)%count($snippets)];
    $timestamp=time()-((($i*11+$j*7)%70)*DAY_IN_SECONDS)-($j+1)*HOUR_IN_SECONDS;
    $cid=wp_insert_comment(['comment_post_ID'=>$id,'comment_author'=>$names[($i+$j)%count($names)].' Demo '.$serial,'comment_author_email'=>'demo+'.$serial.'@example.invalid','comment_content'=>$text,'comment_type'=>'review','comment_approved'=>1,'user_id'=>0,'comment_date'=>wp_date('Y-m-d H:i:s',$timestamp),'comment_date_gmt'=>gmdate('Y-m-d H:i:s',$timestamp),'comment_meta'=>['rating'=>$rating,'verified'=>1,'_mk_demo'=>1]]);
    if(!$cid)throw new RuntimeException('Comment insert failed');
    $existing[]=get_comment($cid);
    // Persist each creation so an interrupted run is still reversible.
    $rows[]=['comment_id'=>$cid,'product_id'=>$id,'rating'=>$rating];
    file_put_contents($out.'comentarios_demo.json',wp_json_encode(['warning'=>'Comentarios de demostración: eliminar antes de publicar','comments'=>$rows,'products'=>$products],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
   }
  }else foreach($existing as $c)$rows[]=['comment_id'=>(int)$c->comment_ID,'product_id'=>$id,'rating'=>(int)get_comment_meta($c->comment_ID,'rating',true)];
  WC_Comments::clear_transients($id);
 }
 file_put_contents($out.'comentarios_demo.json',wp_json_encode(['warning'=>'Comentarios de demostración: eliminar antes de publicar','delete'=>'C:/xampp/php/php.exe tools/v15-native.php delete-demo','products'=>$products,'comments'=>$rows],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
 echo wp_json_encode(['products'=>count($products),'comments'=>count($rows)]);exit;
}
if($mode==='delete-demo'){
 $products=[];$deleted=[];
 foreach(get_comments(['type'=>'review','status'=>'all','meta_key'=>'_mk_demo','meta_value'=>'1','number'=>0]) as $c){$products[]=(int)$c->comment_post_ID;if(wp_delete_comment($c->comment_ID,true))$deleted[]=(int)$c->comment_ID;}
 foreach(array_unique($products) as $id)WC_Comments::clear_transients($id);
 file_put_contents($out.'comentarios_demo_eliminados.json',wp_json_encode(['deleted'=>$deleted,'products'=>array_values(array_unique($products))],JSON_PRETTY_PRINT));
 echo wp_json_encode(['deleted'=>count($deleted),'remaining'=>get_comments(['type'=>'review','meta_key'=>'_mk_demo','meta_value'=>'1','count'=>true])]);exit;
}
if($mode==='inspect'){
 $result=['ratings'=>[],'errors'=>[],'eligible_users'=>[],'terms'=>wc_get_page_permalink('terms'),'privacy'=>get_privacy_policy_url(),'environment'=>wp_get_environment_type(),'mail_enabled'=>get_option('mkp_mail_enabled')];
 foreach(wc_get_products(['limit'=>-1,'status'=>'publish']) as $p){
  $counts=[];$sum=0;$reviews=get_comments(['post_id'=>$p->get_id(),'type'=>'review','status'=>'approve']);
  foreach($reviews as $c){$r=(int)get_comment_meta($c->comment_ID,'rating',true);if($r>0){$counts[$r]=($counts[$r]??0)+1;$sum+=$r;}}
  $n=array_sum($counts);$mean=$n?round($sum/$n,2):0;
  $actual=$p->get_rating_counts();ksort($counts);ksort($actual);
  $ok=$counts==$actual&&abs((float)$p->get_average_rating()-$mean)<0.011&&$p->get_review_count()===count($reviews);
  if(!$ok)$result['errors'][]=$p->get_id();
  $result['ratings'][]=['id'=>$p->get_id(),'url'=>$p->get_permalink(),'name'=>$p->get_name(),'average'=>$p->get_average_rating(),'count'=>$p->get_review_count(),'histogram'=>$counts,'consistent'=>$ok];
 }
 // Read existing completed orders only; no fabricated buyer or order.
 foreach(wc_get_orders(['limit'=>-1,'status'=>'completed']) as $order){$uid=$order->get_customer_id();if(!$uid)continue;foreach($order->get_items() as $item){$pid=$item->get_product_id();if(wc_customer_bought_product('', $uid,$pid))$result['eligible_users'][]=['user_id'=>$uid,'product_id'=>$pid];}}
 file_put_contents($out.'datos-V15.json',wp_json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
 echo wp_json_encode(['products'=>count($result['ratings']),'rated'=>count(array_filter($result['ratings'],fn($r)=>$r['count']>0)),'errors'=>$result['errors'],'eligible_existing_buyers'=>count($result['eligible_users']),'terms'=>$result['terms'],'privacy'=>$result['privacy'],'mail_enabled'=>$result['mail_enabled']]);exit;
}
throw new RuntimeException('Unknown operation');
