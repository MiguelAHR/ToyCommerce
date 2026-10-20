<?php
foreach (json_decode(file_get_contents(dirname(__DIR__).'/local-private/config.json'), true) as $name => $value) define($name, $value);
$table_prefix = 'wp_';
if(!defined('ABSPATH'))define('ABSPATH',__DIR__.'/');
require_once ABSPATH.'wp-settings.php';
