<?php
/** Plugin Name: MiniKids · Tarjetas y acceso V12 */
defined('ABSPATH') || exit;
function mk12_account_url(){return is_user_logged_in()?wc_get_page_permalink('myaccount'):home_url('/mi-cuenta/');}
// Spanish public entry keeps the configured native account page and endpoints intact.
add_action('template_redirect',function(){
 if(untrailingslashit(wp_parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH))===untrailingslashit(wp_parse_url(home_url('/mi-cuenta/'),PHP_URL_PATH))){wp_safe_redirect(get_permalink(wc_get_page_id('myaccount')),302);exit;}
},0);
add_filter('render_block',function($html){
 if(is_user_logged_in())return $html;
 return str_replace('href="'.esc_url(wc_get_page_permalink('myaccount')).'"','href="'.esc_url(mk12_account_url()).'"',$html);
});
// Campaign choices stay editable as Gutenberg image links; render live Woo cards.
add_filter('render_block_core/group',function($html,$block){
 if(!is_front_page()||!in_array('mk4-card-track',explode(' ',$block['attrs']['className']??''),true))return $html;
 $ids=[];$processor=new WP_HTML_Tag_Processor($html);
 while($processor->next_tag('A')){
  $url=$processor->get_attribute('href');if(!$url)continue;$id=url_to_postid($url);$p=wc_get_product($id);
  if($p&&$p->get_status()==='publish')$ids[]=$id;
 }
 if(!$ids)return $html;
 return '<div class="mk4-card-track mk12-home-track">'.do_shortcode('[products ids="'.implode(',',array_unique($ids)).'" orderby="post__in" columns="4"]').'</div>';
},20,2);
add_action('wp_enqueue_scripts',function(){
 wp_enqueue_style('minikids-commerce-v12',plugins_url('minikids-commerce-v12.css',__FILE__),['minikids-retail','minikids-commerce-v5'],filemtime(__DIR__.'/minikids-commerce-v12.css'));
 wp_enqueue_script('minikids-commerce-v12',plugins_url('minikids-commerce-v12.js',__FILE__),['jquery','wc-add-to-cart','minikids-commerce-v5'],filemtime(__DIR__.'/minikids-commerce-v12.js'),true);
 wp_localize_script('minikids-commerce-v12','mk12',['ajax'=>admin_url('admin-ajax.php'),'account'=>wc_get_page_permalink('myaccount'),'alias'=>home_url('/mi-cuenta/'),'guest'=>!is_user_logged_in()]);
},150);
add_action('woocommerce_after_single_product_summary',function(){
 if(!is_product())return;
 echo '<section class="mk12-comments" aria-labelledby="mk12-comments-title"><h2 id="mk12-comments-title">Comentarios</h2><div class="mk12-review-grid">';
 mk5_review_overview();echo '<div class="mk12-native-reviews">';mk10_native_reviews();echo '</div></div></section>';
},9);
function mk12_delivery_time($s){
 $text=trim($s['delivery_time']);return preg_match('/a validar por el due[ñn]o/iu',$text)?'':$text;
}
function mk12_shipping_public($methods,$settings){
 $html='<div class="mk8-callout"><ul>';
 foreach($methods as $row){$m=$row['method'];$cost='';
  if($m->id==='flat_rate'&&is_numeric($m->get_option('cost')))$cost=wc_price((float)$m->get_option('cost'));
  if($m->id==='free_shipping'){
   $requires=$m->get_option('requires');$cost='Gratis';
   if(in_array($requires,['min_amount','either','both'],true))$cost.=' desde '.wc_price((float)$m->get_option('min_amount'));
   if($requires==='either')$cost.=' o con cupón de envío gratis';
   if(in_array($requires,['coupon','both'],true))$cost.=' con cupón de envío gratis';
  }
  if($cost)$html.='<li>'.esc_html($m->get_title().' · '.$row['zone']).': '.$cost.'</li>';
 }
 $html.='</ul>';if(mk12_delivery_time($settings))$html.='<p>'.esc_html(mk12_delivery_time($settings)).'</p>';
 return $html.'<p>El costo final y la cobertura se confirman con tu dirección al finalizar la compra.</p></div>';
}
// One renderer for every native Woo loop, including shortcodes and recommendations.
add_filter('woocommerce_sale_flash',function($html,$post,$p){
 if(!$p||!$p->is_on_sale())return '';
 $r=(float)$p->get_regular_price();$s=(float)$p->get_sale_price();
 if($r<=0||$s<0||$s>=$r)return '';
 return '<span class="onsale mk12-tag">&#45; '.esc_html((string)round(100*($r-$s)/$r)).' %<i aria-hidden="true"></i></span>';
},2000,3);
add_filter('woocommerce_product_add_to_cart_text',function($text,$p){return $p&&$p->is_type('simple')&&$p->is_purchasable()&&$p->is_in_stock()?'AGREGAR':$text;},2000,2);
add_action('woocommerce_shop_loop_item_title',function(){
 global $product;if(!$product)return;
 $brand=$product->get_attribute('pa_marca')?:$product->get_attribute('Marca');
 if(!$brand){$terms=get_the_terms($product->get_id(),'product_cat');$brand=$terms&&!is_wp_error($terms)?$terms[0]->name:'';}
 echo '<span class="mk5-card-brand">'.esc_html($brand).'</span>';
},1);
add_filter('woocommerce_loop_add_to_cart_link',function($html,$p){
 if(!$p||!$p->is_type('simple')||!$p->is_purchasable()||!$p->is_in_stock())return $html;
 $html=preg_replace('~(<a\b[^>]*>).*?(</a>)~s','$1'.mkr_icon('cart').'AGREGAR$2',$html,1);
 return '<div class="mk12-card-buy"><div class="mkr-quantity"><button type="button" data-mk12-delta="-1" aria-label="Reducir cantidad">−</button><input type="number" min="1" max="'.esc_attr($p->get_max_purchase_quantity()>0?$p->get_max_purchase_quantity():'').'" step="1" value="1" aria-label="Cantidad de '.esc_attr($p->get_name()).'"><button type="button" data-mk12-delta="1" aria-label="Aumentar cantidad">+</button></div>'.$html.'</div>';
},2000,2);
function mk12_register_enabled(){return 'yes'===get_option('woocommerce_enable_myaccount_registration');}
function mk12_auth_fields($view){
 $fields=$view==='login'?['username'=>'Correo electrónico o usuario','password'=>'Contraseña']:($view==='register'?['email'=>'Correo electrónico']:['user_login'=>'Correo electrónico o usuario']);
 if($view==='register'){
  if('no'===get_option('woocommerce_registration_generate_username'))$fields=['username'=>'Usuario']+$fields;
  if('no'===get_option('woocommerce_registration_generate_password'))$fields['password']='Contraseña';
 }
 foreach($fields as $key=>$label){$id='mk12-'.$view.'-'.$key;$password=$key==='password';
  echo '<div class="mk12-field"><input placeholder=" " required id="'.esc_attr($id).'" name="'.esc_attr($key).'" type="'.($password?'password':($key==='email'?'email':'text')).'" autocomplete="'.($password?($view==='register'?'new-password':'current-password'):($key==='email'?'email':'username')).'"><label for="'.esc_attr($id).'">'.esc_html($label).'</label>';
  if($password)echo '<button type="button" class="mk12-show" aria-label="Mostrar contraseña" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button>';
  echo '</div>';
 }
}
add_action('wp_footer',function(){
 if(is_user_logged_in())return;
 echo '<dialog id="mk12-auth" class="mk12-auth" aria-modal="true" aria-labelledby="mk12-auth-title"><button type="button" class="mk12-auth-close" aria-label="Cerrar">'.'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button><h2 id="mk12-auth-title">Iniciar sesión</h2>';
 $views=['login'=>'Iniciar sesión','lost'=>'Recuperar contraseña'];if(mk12_register_enabled())$views['register']='Crear cuenta';
 foreach($views as $view=>$title){
  $nonce=$view==='lost'?'lost-password':$view;
  echo '<form method="post" action="'.esc_url($view==='lost'?wc_lostpassword_url():wc_get_page_permalink('myaccount')).'" data-mk12-view="'.esc_attr($view).'"'.($view==='login'?'':' hidden').'><input type="hidden" name="mode" value="'.esc_attr($view).'">';
  wp_nonce_field('woocommerce-'.$nonce,'woocommerce-'.$nonce.'-nonce');
  echo '<div class="mk12-trap" aria-hidden="true"><label>Dejar vacío<input name="website" tabindex="-1" autocomplete="off"></label></div>';
  if($view==='register')do_action('woocommerce_register_form_start');
  mk12_auth_fields($view);
  if($view==='login')echo '<label class="mk12-remember"><input type="checkbox" name="rememberme" value="forever"> Mantenerme conectado</label>';
  if($view==='register'){do_action('woocommerce_register_form');if('yes'===get_option('woocommerce_registration_generate_password'))echo '<p>Recibirás un enlace para establecer tu contraseña.</p>';}
  if($view==='lost')echo '<p>Te enviaremos las instrucciones si existe una cuenta asociada.</p>';
  echo '<p class="mk12-auth-message" role="status" aria-live="polite"></p><button class="mk12-auth-submit" type="submit" name="'.esc_attr($view==='lost'?'wc_reset_password':$view).'" value="1">'.esc_html($title).'</button>';
  if($view==='login'){
   echo '<a href="'.esc_url(wc_lostpassword_url()).'" data-mk12-switch="lost">¿Olvidaste tu contraseña?</a>';
   if(mk12_register_enabled())echo '<p>¿No tienes una cuenta? <a href="'.esc_url(wc_get_page_permalink('myaccount')).'" data-mk12-switch="register">Regístrate</a></p>';
  }else echo '<a href="'.esc_url(wc_get_page_permalink('myaccount')).'" data-mk12-switch="login">Volver a iniciar sesión</a>';
  if($view==='register')do_action('woocommerce_register_form_end');echo '</form>';
 }
 echo '<a class="mk12-account-fallback" href="'.esc_url(mk12_account_url()).'">Abrir Mi cuenta</a></dialog>';
},70);
// Native WP/Woo APIs retain authentication/registration/recovery filters.
add_action('wp_ajax_nopriv_mk12_auth','mk12_auth_request');
add_action('wp_ajax_mk12_auth','mk12_auth_request');
function mk12_auth_request(){
 nocache_headers();header('Cache-Control: no-store, private');
 foreach(['mode','website','username','password','email','user_login','rememberme','woocommerce-login-nonce','woocommerce-register-nonce','woocommerce-lost-password-nonce'] as $key)if(isset($_POST[$key])&&!is_scalar($_POST[$key]))wp_send_json_error(['message'=>'No pudimos completar la solicitud.'],400);
 $mode=sanitize_key(wp_unslash($_POST['mode']??''));$nonce=$mode==='lost'?'lost-password':$mode;
 if(!in_array($mode,['login','register','lost'],true)||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['woocommerce-'.$nonce.'-nonce']??'')),'woocommerce-'.$nonce))wp_send_json_error(['message'=>'Tu sesión ha caducado. Recarga la página e inténtalo de nuevo.'],403);
 if(!empty($_POST['website']))wp_send_json_error(['message'=>'No pudimos completar la solicitud.'],400);
 if(is_user_logged_in())wp_send_json_success(['reload'=>true]);
 $user=trim((string)wp_unslash($_POST['username']??''));$pass=(string)wp_unslash($_POST['password']??'');
 if($mode==='login'){
  if($user===''||$pass==='')wp_send_json_error(['message'=>'Completa el usuario y la contraseña.'],400);
  $errors=apply_filters('woocommerce_process_login_errors',new WP_Error(),$user,$pass);
  if(is_wp_error($errors)&&$errors->has_errors())wp_send_json_error(['message'=>'Los datos no coinciden.'],401);
  $credentials=apply_filters('woocommerce_login_credentials',['user_login'=>$user,'user_password'=>$pass,'remember'=>!empty($_POST['rememberme'])]);
  $result=wp_signon($credentials,is_ssl());
  if(is_wp_error($result))wp_send_json_error(['message'=>'Los datos no coinciden.'],401);
  wp_send_json_success(['reload'=>true]);
 }
 if($mode==='register'){
  if(!mk12_register_enabled())wp_send_json_error(['message'=>'El registro no está disponible.'],403);
  $email=sanitize_email(wp_unslash($_POST['email']??''));
  if(!is_email($email)||('no'===get_option('woocommerce_registration_generate_username')&&$user==='')||('no'===get_option('woocommerce_registration_generate_password')&&$pass===''))wp_send_json_error(['message'=>'Completa los campos con datos válidos.'],400);
  $errors=apply_filters('woocommerce_process_registration_errors',new WP_Error(),$user,$pass,$email);
  if(is_wp_error($errors)&&$errors->has_errors())wp_send_json_error(['message'=>'No pudimos crear la cuenta. Revisa los datos o inicia sesión.'],400);
  $id=wc_create_new_customer($email,wc_clean($user),$pass);
  if(is_wp_error($id))wp_send_json_error(['message'=>'No pudimos crear la cuenta. Revisa los datos o inicia sesión.'],400);
  if(apply_filters('woocommerce_registration_auth_new_customer',true,$id)){wc_set_customer_auth_cookie($id);wp_send_json_success(['reload'=>true]);}
  wp_send_json_success(['message'=>'Cuenta creada. Revisa tu correo para continuar.']);
 }
 $login=trim((string)wp_unslash($_POST['user_login']??''));
 if($login==='')wp_send_json_error(['message'=>'Completa tu correo electrónico o usuario.'],400);
 retrieve_password($login);
 wp_send_json_success(['message'=>'Si existe una cuenta asociada, recibirás las instrucciones para recuperar tu contraseña.']);
}
