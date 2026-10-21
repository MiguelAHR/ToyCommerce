<?php
/** Plugin Name: MiniKids · Correo transaccional Brevo */
defined('ABSPATH') || exit;

function mkpn_mail_addresses($addresses) {
    $result=[];
    foreach((array)$addresses as $address)foreach(str_getcsv($address,',','"','\\') as $entry) {
        $entry=trim($entry);if(preg_match('/[\r\n]/',$entry))continue;
        $name='';$email=$entry;
        if(preg_match('/^(.*?)<([^<>]+)>$/u',$entry,$matches)){$name=trim($matches[1]," \t\"'");$email=trim($matches[2]);}
        if(!is_email($email))continue;
        $item=['email'=>$email];if($name!=='')$item['name']=$name;$result[]=$item;
    }
    return $result;
}
add_filter('pre_wp_mail', function($previous,$atts) {
    if(get_option('mkp_mail_enabled')!=='yes')return $previous;
    $error=function($status) {
        update_option('mkp_mail_status',['ok'=>false,'status'=>(int)$status,'time'=>time()],false);
        do_action('wp_mail_failed',new WP_Error('mkpn_mail_failed','El servicio no aceptó el correo.',['status'=>(int)$status]));return false;
    };
    if($previous!==null)return $previous;
    if(!is_readable(mkpn_brevo_path()))return $previous;
    $config=json_decode(file_get_contents(mkpn_brevo_path()),true);
    if(empty($config['api_key'])||empty($config['sender']))return $previous;
    $sender=sanitize_email(get_option('woocommerce_email_from_address',$config['sender']));
    if(!$sender)return $error(0);
    $payload=['sender'=>['email'=>$sender,'name'=>get_option('woocommerce_email_from_name',get_bloginfo('name'))],
        'to'=>mkpn_mail_addresses($atts['to']),'subject'=>$atts['subject']];
    $headers=$atts['headers']??[];if(!is_array($headers))$headers=explode("\n",str_replace("\r\n","\n",$headers));
    $type=apply_filters('wp_mail_content_type','text/plain');
    foreach($headers as $header) {
        if(!str_contains($header,':'))continue;[$name,$value]=array_map('trim',explode(':',$header,2));$name=strtolower($name);
        if($name==='content-type')$type=trim(explode(';',$value)[0]);
        if(in_array($name,['cc','bcc'],true))$payload[$name]=array_merge($payload[$name]??[],mkpn_mail_addresses($value));
        if($name==='reply-to'&&($parsed=mkpn_mail_addresses($value)))$payload['replyTo']=$parsed[0];
    }
    $payload[$type==='text/html'?'htmlContent':'textContent']=$atts['message'];
    $attachments=$atts['attachments']??[];if(!is_array($attachments))$attachments=explode("\n",str_replace("\r\n","\n",$attachments));
    foreach($attachments as $file) {
        if(!$file)continue;if(!is_readable($file)||!is_file($file))return $error(0);
        $payload['attachment'][]=['name'=>basename($file),'content'=>base64_encode(file_get_contents($file))];
    }
    // Sandbox solo mediante configuración privada local; nunca por GET/POST.
    $sandbox=wp_get_environment_type()==='local'&&defined('MKPN_MAIL_SANDBOX')&&MKPN_MAIL_SANDBOX;
    if($sandbox)$payload['headers']['X-Sib-Sandbox']='drop';
    $response=wp_remote_post('https://api.brevo.com/v3/smtp/email',['timeout'=>25,'redirection'=>0,
        'headers'=>['api-key'=>$config['api_key'],'accept'=>'application/json','content-type'=>'application/json'],'body'=>wp_json_encode($payload)]);
    $status=is_wp_error($response)?0:wp_remote_retrieve_response_code($response);
    if($status!==201)return $error($status);
    $data=json_decode(wp_remote_retrieve_body($response),true);
    update_option('mkp_mail_status',['ok'=>true,'status'=>201,'time'=>time(),'sandbox'=>$sandbox],false);
    do_action('mkpn_mail_accepted',$atts,$data['messageId']??'');do_action('wp_mail_succeeded',$atts);return true;
},20,2);
add_action('woocommerce_email_sent',function($sent,$id,$email) {
    if(!isset($email->object)||!$email->object instanceof WC_Order)return;
    $status=get_option('mkp_mail_status',[]);
    $email->object->update_meta_data('_mkpn_mail_'.$id,['accepted'=>(bool)$sent,'time'=>time(),'sandbox'=>!empty($status['sandbox'])]);
    $email->object->save_meta_data();
},10,3);
add_action('admin_notices',function() {
    if(!current_user_can('manage_woocommerce')||get_option('mkp_mail_enabled')!=='yes')return;
    $last=get_option('mkp_mail_status',[]);
    if(isset($last['ok'])&&!$last['ok'])echo '<div class="notice notice-error"><p>MiniKids: el último correo no fue aceptado. Revisa Ajustes → MiniKids · Negocio y la conexión Brevo antes de reenviar desde el pedido o reclamación.</p></div>';
});
