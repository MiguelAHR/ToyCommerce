<?php
/** Plugin Name: MiniKids · Libro de Reclamaciones */
defined('ABSPATH') || exit;

add_action('init',function() {
    register_post_type('mkp_claim',['label'=>'Reclamaciones','public'=>false,'publicly_queryable'=>false,'exclude_from_search'=>true,'show_ui'=>true,'show_in_rest'=>false,'show_in_menu'=>'woocommerce','supports'=>['title'],
        'capabilities'=>['edit_posts'=>'manage_woocommerce','edit_post'=>'manage_woocommerce','read_post'=>'manage_woocommerce','delete_post'=>'manage_options','create_posts'=>'do_not_allow'],'map_meta_cap'=>false]);
});
add_action('template_redirect',function() {
    if(is_page((int)get_option('mkp_claims_page_id'))){nocache_headers();header('X-Robots-Tag: noindex, nofollow');}
});
function mkpr_fields() {
    return ['name'=>'Nombres y apellidos','document'=>'Documento de identidad','address'=>'Domicilio','email'=>'Correo electrónico','phone'=>'Teléfono',
        'guardian'=>'Padre, madre o representante (si es menor de edad)','order'=>'Número de pedido (opcional)','amount'=>'Monto reclamado en S/ (opcional)',
        'product'=>'Producto o servicio contratado','detail'=>'Detalle del reclamo o queja','request'=>'Pedido del consumidor'];
}
function mkpr_mail_body($id) {
    $data=(array)get_post_meta($id,'_mkpr_data',true);$business=(array)get_post_meta($id,'_mkpr_business',true);
    $body='HOJA DE RECLAMACIÓN '.get_post_meta($id,'_mkpr_folio',true)."\nFecha: ".get_the_date('d/m/Y H:i',$id)."\nProveedor: ".($business['name']??'')."\nRUC: ".($business['ruc']??'')."\nDomicilio: ".($business['address']??'')."\nTipo: ".($data['type']??'')."\nContratación: ".($data['kind']??'')."\n";
    foreach(mkpr_fields() as $key=>$label)$body.=$label.': '.($data[$key]??'')."\n";
    return $body."\nEl plazo máximo de respuesta es de 15 días hábiles. La presentación de esta hoja no impide acudir a otras vías de solución.\n";
}
function mkpr_send_receipt($id) {
    $data=(array)get_post_meta($id,'_mkpr_data',true);$business=mkpn_business();$body=mkpr_mail_body($id);$folio=get_post_meta($id,'_mkpr_folio',true);
    $customer=wp_mail($data['email'],'Constancia de reclamación '.$folio,$body);
    $provider=wp_mail($business['email']??get_option('admin_email'),'Nueva reclamación '.$folio,$body);
    update_post_meta($id,'_mkpr_mail',['customer'=>(bool)$customer,'provider'=>(bool)$provider,'time'=>time()]);
    return $customer&&$provider;
}
add_shortcode('minikids_libro_reclamaciones',function() {
    $business=mkpn_business();$values=[];$errors=[];$receipt=0;
    $required=['name','document','address','email','phone','product','detail','request'];
    if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['mkpr_submit'])) {
        if(!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mkpr_nonce']??'')),'mkpr_submit'))$errors['form']='La sesión venció. Recarga la página e inténtalo de nuevo.';
        foreach(mkpr_fields() as $key=>$label) {
            $raw=wp_unslash($_POST['mkpr_'.$key]??'');if(!is_string($raw))$raw='';
            $values[$key]=in_array($key,['product','detail','request'],true)?sanitize_textarea_field($raw):sanitize_text_field($raw);
            $max=in_array($key,['product','detail','request'],true)?8000:500;
            if(mb_strlen($values[$key])>$max)$errors[$key]='El campo '.$label.' es demasiado extenso.';
        }
        foreach($required as $key)if($values[$key]==='')$errors[$key]='Completa: '.mkpr_fields()[$key].'.';
        if(!is_email($values['email']))$errors['email']='Ingresa un correo electrónico válido.';
        if($values['amount']!==''&&!preg_match('/^\d{1,9}(?:[.,]\d{1,2})?$/',$values['amount']))$errors['amount']='Ingresa un monto válido, por ejemplo 59.90.';
        $values['type']=sanitize_key($_POST['mkpr_type']??'');$values['kind']=sanitize_key($_POST['mkpr_kind']??'');
        if(!in_array($values['type'],['reclamo','queja'],true))$errors['type']='Selecciona reclamo o queja.';
        if(!in_array($values['kind'],['bien','servicio'],true))$errors['kind']='Selecciona bien o servicio.';
        $values['minor']=!empty($_POST['mkpr_minor']);
        if($values['minor']&&$values['guardian']==='')$errors['guardian']='Indica al padre, madre o representante del menor.';
        if(empty($_POST['mkpr_accept']))$errors['accept']='Confirma la información y el uso de datos para atender tu solicitud.';
        if(!empty($_POST['mkpr_website']))$errors['form']='No se pudo procesar el formulario.';
        foreach(['name','ruc','address','email'] as $field)if(empty($business[$field]))$errors['business']='El proveedor debe completar sus datos. Contacta al negocio para recibir atención.';
        $token=sanitize_key($_POST['mkpr_token']??'');if(!preg_match('/^[a-f0-9]{32}$/',$token))$errors['form']='Recarga el formulario antes de enviarlo.';
        $rate='mkpr_rate_'.hash_hmac('sha256',$_SERVER['REMOTE_ADDR']??'local',wp_salt());$count=(int)get_transient($rate);
        if($count>=5)$errors['form']='Se recibieron varias solicitudes desde esta conexión. Intenta más tarde o contacta al proveedor.';
        if(!$errors) {
            $lock='mkpr_once_'.hash('sha256',$token);
            if(!add_option($lock,'pending','','no'))$errors['form']='La hoja ya fue enviada. Conserva la constancia del primer envío.';
            else {
                $receipt=wp_insert_post(['post_type'=>'mkp_claim','post_status'=>'private','post_title'=>'Hoja de reclamación · '.wp_date('Y-m-d H:i')],true);
                if(is_wp_error($receipt)){delete_option($lock);$receipt=0;$errors['form']='No se pudo registrar la hoja. Intenta de nuevo.';}
                else {
                    $folio='MK-'.wp_date('Y').'-'.str_pad((string)$receipt,6,'0',STR_PAD_LEFT);
                    update_post_meta($receipt,'_mkpr_data',$values);update_post_meta($receipt,'_mkpr_business',$business);update_post_meta($receipt,'_mkpr_folio',$folio);update_post_meta($receipt,'_mkpr_status','recibido');
                    update_option($lock,(string)$receipt,false);set_transient($rate,$count+1,HOUR_IN_SECONDS);mkpr_send_receipt($receipt);
                }
            }
        }
    }
    ob_start();
    echo '<div class="mkpr-provider"><strong>'.esc_html($business['name']??'MiniKids Perú').'</strong><span>RUC: '.esc_html($business['ruc']??'').'</span><span>'.esc_html($business['address']??'').'</span><span>'.esc_html($business['email']??'').'</span></div>';
    if($receipt) {
        echo '<section class="mkpr-receipt" aria-live="polite"><h2>Tu hoja ha sido registrada</h2><p class="mkpr-folio">'.esc_html(get_post_meta($receipt,'_mkpr_folio',true)).'</p><p>Fecha: '.esc_html(wp_date('d/m/Y H:i')).'</p><dl>';
        foreach(array_merge(['type'=>'Tipo','kind'=>'Contratación'],mkpr_fields()) as $key=>$label)if(!empty($values[$key]))echo '<dt>'.esc_html($label).'</dt><dd>'.nl2br(esc_html($values[$key])).'</dd>';
        $mail=get_post_meta($receipt,'_mkpr_mail',true);
        echo '</dl><p>Respuesta en un plazo máximo de 15 días hábiles.</p><p>'.($mail['customer']?'La constancia fue aceptada por el servicio de correo. Revisa también la carpeta de spam.':'No se pudo enviar la copia por correo. Conserva esta constancia; el proveedor mantiene tu registro.').'</p><button class="mkpr-button" type="button" data-mkpr-print>Imprimir / Guardar PDF</button></section>';
    } else {
        if($errors){echo '<div class="mkpr-errors" role="alert" tabindex="-1"><h2>Revisa la información</h2><ul>';foreach($errors as $key=>$message)echo '<li>'.(isset(mkpr_fields()[$key])?'<a href="#mkpr-'.$key.'">'.esc_html($message).'</a>':esc_html($message)).'</li>';echo '</ul></div>';}
        echo '<form class="mkpr-form" action="'.esc_url(get_permalink()).'" method="post">';wp_nonce_field('mkpr_submit','mkpr_nonce');
        echo '<input type="hidden" name="mkpr_token" value="'.esc_attr(bin2hex(random_bytes(16))).'"><div class="mkpr-honeypot" aria-hidden="true"><label>Dejar vacío<input name="mkpr_website" tabindex="-1" autocomplete="off"></label></div><p>Los campos con * son obligatorios.</p><fieldset><legend>Datos del consumidor</legend>';
        foreach(mkpr_fields() as $key=>$label) {
            if($key==='product') {
                echo '</fieldset><fieldset><legend>Tu solicitud</legend><div class="mkpr-choices"><fieldset><legend>Tipo de solicitud *</legend>';
                foreach(['reclamo'=>'Reclamo: producto o servicio','queja'=>'Queja: atención recibida'] as $value=>$text)echo '<label><input type="radio" name="mkpr_type" value="'.$value.'" required '.checked($values['type']??'',$value,false).'> '.esc_html($text).'</label>';
                echo '</fieldset><fieldset><legend>Identificación de la contratación *</legend>';
                foreach(['bien'=>'Bien / producto','servicio'=>'Servicio'] as $value=>$text)echo '<label><input type="radio" name="mkpr_kind" value="'.$value.'" required '.checked($values['kind']??'',$value,false).'> '.esc_html($text).'</label>';
                echo '</fieldset></div>';
            }
            $area=in_array($key,['product','detail','request'],true);$required_attr=in_array($key,$required,true)?' required':'';
            echo '<div class="mkpr-field"><label for="mkpr-'.$key.'">'.esc_html($label).($required_attr?' *':'').'</label>';
            $invalid=isset($errors[$key])?' aria-invalid="true" aria-describedby="mkpr-error-'.$key.'"':'';
            if($area)echo '<textarea id="mkpr-'.$key.'" name="mkpr_'.$key.'" rows="4" maxlength="8000"'.$required_attr.$invalid.'>'.esc_textarea($values[$key]??'').'</textarea>';
            else echo '<input id="mkpr-'.$key.'" name="mkpr_'.$key.'" type="'.(['email'=>'email','phone'=>'tel'][$key]??'text').'" maxlength="500" value="'.esc_attr($values[$key]??'').'"'.$required_attr.$invalid.' autocomplete="'.(['name'=>'name','address'=>'street-address','email'=>'email','phone'=>'tel'][$key]??'off').'"'.($key==='amount'?' inputmode="decimal"':'').'>';
            if(isset($errors[$key]))echo '<p class="mkpr-field-error" id="mkpr-error-'.$key.'">'.esc_html($errors[$key]).'</p>';
            echo '</div>';
            if($key==='guardian')echo '<label class="mkpr-check"><input type="checkbox" name="mkpr_minor" value="1" '.checked($values['minor']??false,true,false).'> El consumidor es menor de edad.</label>';
        }
        echo '</fieldset><p>Los datos se usan para registrar y atender esta solicitud. <a href="'.esc_url(get_privacy_policy_url()?:home_url('/')).'">Consultar privacidad</a>.</p><label class="mkpr-check"><input type="checkbox" name="mkpr_accept" value="1" required> Declaro que la información es correcta y leí el uso de mis datos para atender la solicitud.</label><p>El plazo máximo de respuesta es de 15 días hábiles. Presentar esta hoja no impide acudir a otras vías de solución.</p><button class="mkpr-button" name="mkpr_submit" value="1" type="submit">Registrar hoja de reclamación</button></form>';
    }
    return ob_get_clean();
});
add_action('add_meta_boxes',function() {
    add_meta_box('mkpr-details','Hoja de Reclamación',function($post) {
        if(!current_user_can('manage_woocommerce'))return;
        echo '<p><strong>Folio: '.esc_html(get_post_meta($post->ID,'_mkpr_folio',true)).'</strong></p><pre style="white-space:pre-wrap">'.esc_html(mkpr_mail_body($post->ID)).'</pre>';
        $mail=(array)get_post_meta($post->ID,'_mkpr_mail',true);
        echo '<p>Constancia cliente: '.(!empty($mail['customer'])?'Aceptada por correo':'Pendiente / falló').'. Notificación proveedor: '.(!empty($mail['provider'])?'Aceptada por correo':'Pendiente / falló').'.</p>';
        wp_nonce_field('mkpr_admin','mkpr_admin_nonce');
        echo '<p><label>Estado <select name="mkpr_status">';foreach(['recibido'=>'Recibido','en-atencion'=>'En atención','respondido'=>'Respondido'] as $value=>$label)echo '<option value="'.$value.'" '.selected(get_post_meta($post->ID,'_mkpr_status',true),$value,false).'>'.$label.'</option>';echo '</select></label></p>';
        echo '<p><label for="mkpr-response">Respuesta al consumidor</label><textarea id="mkpr-response" name="mkpr_response" class="widefat" rows="7">'.esc_textarea(get_post_meta($post->ID,'_mkpr_response',true)).'</textarea></p>';
        submit_button('Guardar y enviar respuesta al consumidor','secondary','mkpr_send_reply',false);echo ' ';submit_button('Reenviar constancia y aviso al proveedor','secondary','mkpr_resend',false);
        echo '<p>Enviar respuesta la remite por correo y registra la fecha si el servicio la acepta. Guardar el registro normalmente conserva las notas sin enviar.</p>';
    },'mkp_claim','normal','high');
});
add_action('save_post_mkp_claim',function($id) {
    if(wp_is_post_revision($id)||(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)||!current_user_can('manage_woocommerce')||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mkpr_admin_nonce']??'')),'mkpr_admin'))return;
    $status=sanitize_key($_POST['mkpr_status']??'');if(in_array($status,['recibido','en-atencion','respondido'],true))update_post_meta($id,'_mkpr_status',$status);
    $reply=sanitize_textarea_field(wp_unslash($_POST['mkpr_response']??''));update_post_meta($id,'_mkpr_response',$reply);
    if(isset($_POST['mkpr_resend']))mkpr_send_receipt($id);
    if(isset($_POST['mkpr_send_reply'])&&$reply!=='') {
        $data=get_post_meta($id,'_mkpr_data',true);$business=mkpn_business();
        $sent=wp_mail($data['email'],'Respuesta a tu reclamación '.get_post_meta($id,'_mkpr_folio',true),$reply."\n\n".($business['name']??'MiniKids Perú'),['Reply-To: '.($business['email']??get_option('admin_email'))]);
        update_post_meta($id,'_mkpr_reply_mail',(bool)$sent);if($sent){update_post_meta($id,'_mkpr_status','respondido');update_post_meta($id,'_mkpr_replied_at',time());}
    }
});
