<?php
if(PHP_SAPI!=='cli')exit;
define('MKPN_MAIL_SANDBOX',true);
$_SERVER['HTTP_HOST']='localhost:8082';require dirname(__DIR__).'/wordpress/wp-load.php';
$ids=json_decode(file_get_contents(dirname(__DIR__).'/local-private/attention-qa.json'),true);$id=$ids['claims'][0];
if(get_post_type($id)!=='mkp_claim')throw new RuntimeException('No es una hoja QA');
$admin=get_user_by('login','admin');wp_set_current_user($admin->ID);
$_POST=['mkpr_admin_nonce'=>wp_create_nonce('mkpr_admin'),'mkpr_status'=>'en-atencion','mkpr_response'=>'Respuesta técnica de validación local, sin reclamación comercial.','mkpr_send_reply'=>'1'];
wp_update_post(['ID'=>$id]);
if(get_post_meta($id,'_mkpr_status',true)!=='respondido'||!get_post_meta($id,'_mkpr_reply_mail',true))throw new RuntimeException('No se envió/registró la respuesta');
echo "OK Respuesta administrativa aceptada en sandbox y fecha registrada\n";
$before=get_post_meta($id,'_mkpr_response',true);wp_set_current_user(0);$_POST['mkpr_response']='Cambio no autorizado';do_action('save_post_mkp_claim',$id);
if(get_post_meta($id,'_mkpr_response',true)!==$before)throw new RuntimeException('Se permitió escribir sin permisos');
echo "OK Registro protegido frente a usuario sin permisos\n";
wp_set_current_user($admin->ID);$_POST['mkpr_admin_nonce']='invalido';do_action('save_post_mkp_claim',$id);
if(get_post_meta($id,'_mkpr_response',true)!==$before)throw new RuntimeException('Se permitió escribir sin nonce');
echo "OK Registro protegido frente a nonce inválido\n";
