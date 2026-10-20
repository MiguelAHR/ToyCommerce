<?php
/** Plugin Name: MiniKids · Datos del negocio y atención */
defined('ABSPATH') || exit;

add_action('after_setup_theme',function(){register_nav_menu('minikids_atencion','MiniKids: ayuda y empresa');});
add_action('wp_enqueue_scripts',function() {
    wp_enqueue_style('minikids-atencion',plugins_url('minikids-atencion.css',__FILE__),['minikids-bloques'],filemtime(__DIR__.'/minikids-atencion.css'));
    if(is_page((int)get_option('mkp_claims_page_id')))wp_enqueue_script('minikids-atencion',plugins_url('minikids-atencion.js',__FILE__),[],filemtime(__DIR__.'/minikids-atencion.js'),true);
},40);

function mkpn_business() { return (array)get_option('mkp_business', []); }
function mkpn_brevo_path() {
    return defined('MINIKIDS_BREVO_CONFIG') ? MINIKIDS_BREVO_CONFIG : dirname(ABSPATH).'/local-private/brevo.json';
}
add_action('admin_init', function() {
    register_setting('mkpn_business', 'mkp_business', ['sanitize_callback'=>function($data) {
        $clean=[];foreach(['name','ruc','address','phone'] as $key)$clean[$key]=sanitize_text_field($data[$key]??'');
        $clean['email']=sanitize_email($data['email']??'');return $clean;
    }]);
    register_setting('mkpn_business', 'mkp_mail_enabled', ['sanitize_callback'=>fn($v)=>$v==='yes'?'yes':'no']);
});
add_action('admin_menu', function() {
    add_options_page('MiniKids · Negocio y correo','MiniKids · Negocio','manage_options','mkpn-business',function() {
        if(!current_user_can('manage_options'))return;
        $data=mkpn_business();
        echo '<div class="wrap"><h1>MiniKids · Negocio y correo</h1><p>Estos datos identifican al proveedor en el Libro de Reclamaciones. El contenido editorial se cambia en Páginas.</p>';
        if(isset($_GET['key_saved']))echo '<div class="notice notice-success"><p>Credencial privada actualizada.</p></div>';
        echo '<form action="options.php" method="post">';settings_fields('mkpn_business');
        echo '<table class="form-table">';
        foreach(['name'=>'Razón social','ruc'=>'RUC','address'=>'Domicilio del establecimiento','email'=>'Correo del administrador y atención','phone'=>'Teléfono / WhatsApp'] as $key=>$label)echo '<tr><th><label for="mkpn-'.$key.'">'.esc_html($label).'</label></th><td><input class="regular-text" id="mkpn-'.$key.'" name="mkp_business['.$key.']" type="'.($key==='email'?'email':'text').'" value="'.esc_attr($data[$key]??'').'"></td></tr>';
        echo '<tr><th>Correo transaccional</th><td><input type="hidden" name="mkp_mail_enabled" value="no"><label><input type="checkbox" name="mkp_mail_enabled" value="yes" '.checked(get_option('mkp_mail_enabled'),'yes',false).'> Enviar correos de pedidos y reclamaciones mediante Brevo</label><p class="description">Al activarlo también se permite el envío desde esta instalación local. No activa pagos ni campañas.</p></td></tr></table>';
        submit_button('Guardar datos');echo '</form><hr><h2>Conexión de correo</h2>';
        echo '<p>Credencial: <strong>'.(is_readable(mkpn_brevo_path())?'Configurada fuera de WordPress':'Pendiente').'</strong>. La clave no se muestra ni se guarda en opciones accesibles por MCP.</p>';
        echo '<form action="'.esc_url(admin_url('admin-post.php')).'" method="post"><input type="hidden" name="action" value="mkpn_save_key">';wp_nonce_field('mkpn_save_key');
        echo '<p><label>Nueva API key de Brevo <input type="password" name="brevo_key" class="regular-text" autocomplete="new-password" required></label></p>';
        submit_button('Actualizar credencial privada','secondary');echo '</form>';
        echo '<p><a href="'.esc_url(admin_url('admin.php?page=wc-settings&tab=email')).'">Editar asuntos, remitente, destinatarios y textos de correos WooCommerce</a></p><p>El remitente que elijas debe estar verificado en Brevo. El correo de atención identifica al proveedor y recibe sus reclamaciones; el destinatario de nuevos pedidos se cambia en el correo Nuevo pedido de WooCommerce.</p>';
        foreach(['mkp_about_page_id'=>'Editar ¿Quiénes somos?','mkp_claims_page_id'=>'Editar introducción del Libro de Reclamaciones'] as $option=>$label)if($id=(int)get_option($option))echo '<p><a href="'.esc_url(get_edit_post_link($id)).'">'.esc_html($label).'</a></p>';
        echo '<p><a href="'.esc_url(admin_url('edit.php?post_type=mkp_claim')).'">Atender reclamaciones</a></p></div>';
    });
});
add_action('admin_post_mkpn_save_key', function() {
    if(!current_user_can('manage_options'))wp_die('Sin permisos',403);
    check_admin_referer('mkpn_save_key');
    $key=trim(wp_unslash($_POST['brevo_key']??''));
    if(!preg_match('/^xkeysib-[a-zA-Z0-9-]{30,}$/',$key))wp_die('La clave no tiene el formato esperado.');
    $path=mkpn_brevo_path();$dir=dirname($path);
    if(!is_dir($dir)||!is_writable($dir))wp_die('El administrador del servidor debe habilitar el directorio privado de configuración.');
    $business=mkpn_business();$data=['api_key'=>$key,'sender'=>$business['email']??'','name'=>get_bloginfo('name')];
    $tmp=tempnam($dir,'brevo-');chmod($tmp,0600);
    if(file_put_contents($tmp,wp_json_encode($data))===false||!rename($tmp,$path))wp_die('No se pudo guardar la configuración privada.');
    wp_safe_redirect(add_query_arg('key_saved',1,admin_url('options-general.php?page=mkpn-business')));exit;
});
