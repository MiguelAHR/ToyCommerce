<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/')exit('Local only');
$out=dirname(__DIR__,2).'/diseno_minikids/v5/';$initial=json_decode(file_get_contents(dirname(__DIR__,2).'/local-private/v8-backups/native-before.json'),true);$report=[];
foreach($initial['products'] as $original){
 $p=wc_get_product($original['id']);$attrs=$p->get_attributes();$added=[];
 $source=html_entity_decode(wp_strip_all_tags(preg_replace('/<\/(?:p|li|div)>/iu',"\n",$original['description']."\n".$original['short'])),ENT_QUOTES|ENT_HTML5,'UTF-8');
 foreach(['Edad recomendada'=>'/^Edad (?:recomendada orientativa|orientativa|recomendada):\s*(.+)$/imu','Contenido'=>'/^Contenido:\s*(.+)$/imu'] as $name=>$pattern){
  if($p->get_attribute($name)||!preg_match($pattern,$source,$m))continue;
  $value=mk8_clean_text($m[1]);$attribute=new WC_Product_Attribute();$attribute->set_name($name);$attribute->set_options([$value]);$attribute->set_visible(true);$attribute->set_position(count($attrs));$attrs[sanitize_title($name)]=$attribute;$added[$name]=$value;
 }
 $description=preg_replace('/<li>Contenido:.*?<\/li>/isu','',$p->get_description());
 if($added||$description!==$p->get_description()){$p->set_attributes($attrs);$p->set_description($description);$p->save();$report[]=['id'=>$p->get_id(),'native_attributes_added'=>$added];}
}
file_put_contents($out.'detalles-migrados-V8.json',wp_json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
$changes=json_decode(file_get_contents($out.'productos-corregidos-V8.json'),true);
foreach(['changes','samples'] as $key)foreach($changes[$key] as &$row){$p=wc_get_product($row['id']);$row['after']['description']=$p->get_description();$row['after']['native_attributes']=array_map(fn($a)=>$a->get_data(),$p->get_attributes());}unset($row);
file_put_contents($out.'productos-corregidos-V8.json',wp_json_encode($changes,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));echo wp_json_encode(['native_details_migrated'=>count($report)]).PHP_EOL;
