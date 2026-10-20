<?php
if(PHP_SAPI!=='cli')exit;
require dirname(__DIR__).'/wordpress/wp-load.php';
if(DB_NAME!=='minikeds_dev2')throw new RuntimeException('Base inesperada');
$db=new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);$db->set_charset('utf8mb4');
$out=fopen(dirname(__DIR__).'/local-private/deploy/database.sql','wb');
fwrite($out,"SET NAMES utf8mb4;\nSET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO';\nSET FOREIGN_KEY_CHECKS=0;\n");
$db->begin_transaction(MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT);
foreach($db->query('SHOW TABLES') as $record){$name=array_values($record)[0];if(!str_starts_with($name,$table_prefix))continue;$table='`'.$name.'`';fwrite($out,"DROP TABLE IF EXISTS $table;\n".$db->query('SHOW CREATE TABLE '.$table)->fetch_row()[1].";\n");foreach($db->query('SELECT * FROM '.$table) as $row){$values=array_map(fn($v)=>$v===null?'NULL':"'".$db->real_escape_string($v)."'",array_values($row));fwrite($out,'INSERT INTO '.$table.' VALUES('.implode(',',$values).");\n");}}
$db->commit();fwrite($out,"SET FOREIGN_KEY_CHECKS=1;\n");fclose($out);echo "Exportacion privada completada\n";
