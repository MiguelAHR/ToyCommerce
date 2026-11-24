<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/')exit('Local only');
$out=dirname(__DIR__,2).'/diseno_minikids/v5/';$back=dirname(__DIR__,2).'/local-private/v8-backups/';
if(is_file($out.'productos-corregidos-V8.json')&&($argv[1]??'')!=='--rebuild')exit('Already applied; inspect before rerunning');
$original=json_decode(file_get_contents($back.'native-before.json'),true);$original=array_column($original['products'],null,'id');
function mk8_lines($html){
 $html=preg_replace('/<h[1-6]\b[^>]*>.*?<\/h[1-6]>/isu','',$html);
 $html=preg_replace('/<\/(?:p|li|div)>|<br\s*\/?>/iu',"\n",$html);
 $lines=preg_split('/\n+/u',html_entity_decode(wp_strip_all_tags($html),ENT_QUOTES|ENT_HTML5,'UTF-8'));
 $result=[];$seen=[];foreach($lines as $line){$line=mk8_clean_text(preg_replace('/\s+/u',' ',$line));$key=mb_strtolower(rtrim($line," .;:"));if($line!==''&&!isset($seen[$key])){$result[]=$line;$seen[$key]=true;}}return $result;
}
$result=['audited'=>0,'changed_ids'=>[],'samples'=>[],'changes'=>[]];
foreach(wc_get_products(['status'=>'publish','limit'=>-1]) as $p){
 $result['audited']++;$source=$original[$p->get_id()];$before=['description'=>$source['description'],'short'=>$source['short'],'care'=>$source['care']];
 $short=mk8_lines($before['short']);$long=mk8_lines($before['description']);$care=[];$description=[];$seen=[];
 foreach($p->get_meta('_mk_demo')?$long:array_merge($short,$long) as $line){$key=mb_strtolower(rtrim($line," .;:"));if(isset($seen[$key]))continue;$seen[$key]=true;
  if(preg_match('/limpi|lavar|paño|supervisi|advertencia|piezas pequeñas|sumerg|almacenar|secar|revisar costuras|asegurar el ensamblaje|ficha de demostración|artículo de demostración|confirmar especificaciones/iu',$line))$care[]=$line;
  elseif(!preg_match('/^Edad (orientativa|recomendada)/iu',$line))$description[]=$line;
 }
 if(!$description)$description=[mk8_clean_text($p->get_name())];
 $html='<ul>';foreach($description as $line)$html.='<li>'.esc_html($line).'</li>';$html.='</ul>';
 $short_html='<ul>';foreach($short as $line)$short_html.='<li>'.esc_html($line).'</li>';$short_html.='</ul>';
 $after=['description'=>$html,'short'=>$short_html,'care'=>$care];
 if($before!==$after){$p->set_description($html);$p->set_short_description($short_html);$p->update_meta_data('_mk8_care',$care);$p->save();$result['changed_ids'][]=$p->get_id();$result['changes'][]=['id'=>$p->get_id(),'name'=>$p->get_name(),'before'=>$before,'after'=>$after];}
}
foreach([135,136,41,39,278] as $id)foreach($result['changes'] as $change)if($change['id']===$id)$result['samples'][]=$change;
file_put_contents($out.'productos-corregidos-V8.json',wp_json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));echo wp_json_encode(['audited'=>$result['audited'],'changed_ids'=>$result['changed_ids']]).PHP_EOL;
