<?php
/** Plugin Name: MiniKids · Ficha y atención V8 */
defined('ABSPATH') || exit;

function mk8_clean_text($text){return trim(preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}\x{200D}]/u','',(string)$text));}
function mk8_fields(){return [
 'availability_title'=>'Título de disponibilidad','availability_text'=>'Información de disponibilidad y envío','inventory_note'=>'Nota de inventario','continue_label'=>'Botón seguir comprando',
 'pickup_enabled'=>'Ofrecer recojo (yes/no; requiere método local_pickup activo)','pickup_name'=>'Nombre del punto de recojo','pickup_address'=>'Dirección del punto de recojo',
 'delivery_title'=>'Título de opciones de entrega','delivery_time'=>'Plazo de entrega (a validar por el dueño)','delivery_note'=>'Aclaraciones de entrega','delivery_final'=>'Texto final de entrega',
 'payment_title'=>'Título de opciones de pago','payment_bacs'=>'Descripción de transferencia','payment_cod'=>'Descripción de pago contra entrega',
 'returns_title'=>'Título de devoluciones y garantías','returns_text'=>'Devoluciones (a validar por el dueño)','warranty_text'=>'Garantías (a validar por el dueño)'];}
function mk8_defaults(){return [
 'availability_title'=>'Ver disponibilidad y opciones de entrega','availability_text'=>'Envío a domicilio según cobertura y métodos disponibles para tu dirección al finalizar la compra.','inventory_note'=>'*El inventario puede cambiar durante el día.','continue_label'=>'Seguir comprando','pickup_enabled'=>'no','pickup_name'=>'','pickup_address'=>'',
 'delivery_title'=>'Opciones de entrega','delivery_time'=>'Plazo a validar por el dueño.','delivery_note'=>'La disponibilidad y el costo final dependen de tu dirección y de las condiciones del método de envío seleccionado.','delivery_final'=>'En el momento de procesar tu compra te mostraremos los métodos de envío disponibles para tu dirección.',
 'payment_title'=>'Opciones de pago','payment_bacs'=>'Realiza una transferencia e indica el número de pedido como referencia. El envío se coordina al confirmar el abono. Consulta los datos bancarios con atención antes de transferir.','payment_cod'=>'Paga en efectivo al recibir tu pedido, cuando esta opción esté disponible para tu dirección.',
 'returns_title'=>'Devoluciones y garantías','returns_text'=>'A validar por el dueño: comunícate con atención indicando el número de pedido y el motivo de tu solicitud. Las condiciones y plazos de cambios o devoluciones deben confirmarse antes de comprar.','warranty_text'=>'A validar por el dueño: informa cualquier defecto con una descripción y comprobante de compra. Atención revisará el caso y comunicará la solución y las condiciones aplicables.'];}
function mk8_settings(){return wp_parse_args((array)get_option('mk8_product_help',[]),mk8_defaults());}
add_action('admin_init',function(){
 register_setting('mkpn_business','mk8_product_help',['sanitize_callback'=>function($data){$clean=[];foreach(mk8_fields() as $key=>$label)$clean[$key]=mk8_clean_text(sanitize_textarea_field($data[$key]??''));$clean['pickup_enabled']=($clean['pickup_enabled']??'no')==='yes'?'yes':'no';return $clean;}]);
 add_settings_section('mk8-help','Ficha de producto: disponibilidad, pagos, entrega y garantías',function(){echo '<p>Contenido público editable. Los importes/métodos vienen de WooCommerce. No publicar plazos ni políticas sin validación. El contacto viene de los datos del negocio.</p>';},'mkpn-business');
 foreach(mk8_fields() as $key=>$label)add_settings_field('mk8-'.$key,$label,function()use($key){$data=mk8_settings();echo '<textarea rows="3" class="large-text" name="mk8_product_help['.esc_attr($key).']">'.esc_textarea($data[$key]).'</textarea>';},'mkpn-business','mk8-help');
});
add_action('wp_enqueue_scripts',function(){
 if(!is_product()&&!mk6_checkout())return;
 wp_enqueue_style('minikids-commerce-v8',plugins_url('minikids-commerce-v8.css',__FILE__),['minikids-commerce-v6'],filemtime(__DIR__.'/minikids-commerce-v8.css'));
 wp_enqueue_script('minikids-commerce-v8',plugins_url('minikids-commerce-v8.js',__FILE__),['minikids-commerce-v6'],filemtime(__DIR__.'/minikids-commerce-v8.js'),true);
},130);

// Keep Blocksy's priority-zero buffer flush; replace its presentation through hooks.
add_action('template_redirect',function(){
 if(!is_product())return;
 global $wp_filter;
 foreach(($wp_filter['woocommerce_single_product_summary']->callbacks??[]) as $priority=>$callbacks){if($priority===0)continue;foreach($callbacks as $callback)remove_action('woocommerce_single_product_summary',$callback['function'],$priority);}
 add_action('woocommerce_single_product_summary','mk8_purchase',100);
 remove_action('woocommerce_after_single_product_summary','woocommerce_output_product_data_tabs',10);
 add_action('woocommerce_before_single_product_summary',function(){echo '<div class="mk8-left">';},2);
 add_action('woocommerce_before_single_product_summary','mk8_editorial',99);
},PHP_INT_MAX);
add_filter('woocommerce_sale_flash',function($html){return is_product()&&!wc_get_loop_prop('name')?'':$html;},200);
function mk8_purchase(){
 global $product;
 woocommerce_breadcrumb(['wrap_before'=>'<nav class="woocommerce-breadcrumb mk8-breadcrumb" aria-label="Ruta del producto">','wrap_after'=>'</nav>','delimiter'=>' <span aria-hidden="true">›</span> ','home'=>'Inicio']);
 woocommerce_template_single_title();
 echo '<p class="mk5-reference">Referencia: '.esc_html($product->get_sku()).'</p>';
 if(function_exists('mk16_single_rating_trigger'))mk16_single_rating_trigger();
 $r=(float)$product->get_regular_price();$s=(float)$product->get_sale_price();
 if($product->is_on_sale()&&$s>0&&$s<$r){
  echo '<dl class="mk8-price"><dt>Precio especial:</dt><dd class="mk8-special">'.wc_price(wc_get_price_to_display($product,['price'=>$s])).'</dd><dt>Precio regular:</dt><dd><del>'.wc_price(wc_get_price_to_display($product,['price'=>$r])).'</del></dd><dt>Descuento:</dt><dd><span class="mk8-discount">- '.esc_html(floor(100*($r-$s)/$r)).' %</span></dd></dl>';
 }else echo '<div class="mk10-price-row"><p class="price">'.$product->get_price_html().'</p>';
 $n=$product->get_stock_quantity();
 ob_start();
 echo '<p class="mk5-low-stock">'.esc_html(!$product->is_in_stock()?'Agotado':($product->managing_stock()&&$n!==null&&$n>0&&$n<=10?'¡Solo quedan '.$n.' piezas!':'¡Llévatelo ahora!')).'</p>';
 $note=ob_get_clean();if(!($product->is_on_sale()&&$s>0&&$s<$r))echo $note.'</div>';
 woocommerce_template_single_add_to_cart();if($product->is_on_sale()&&$s>0&&$s<$r)echo '<div class="mk12-sale-note">'.$note.'</div>';
 $settings=mk8_settings();echo '<div class="mk8-help"><button type="button" class="button mk8-availability" data-mk8-dialog="availability" aria-haspopup="dialog" aria-controls="mk8-availability">'.esc_html($settings['availability_title']).'</button><div class="mk5-help-links">';
 foreach(['payment'=>'Métodos de pago','delivery'=>'Opciones de envío','returns'=>'Devoluciones y garantías'] as $id=>$label)echo '<button type="button" data-mk8-dialog="'.esc_attr($id).'" aria-haspopup="dialog" aria-controls="mk8-'.esc_attr($id).'">'.mk10_help_icon($id).'<span>'.esc_html($label).'</span></button>';
 echo '</div></div>';
}
function mk8_editorial(){
 global $product;
 echo '<div class="mk8-editorial"><section class="mk8-description"><h2>Descripción del producto</h2>'.wp_kses_post($product->get_description()).'</section><section class="mk8-details"><h2>Detalles del producto:</h2>';
 wc_display_product_attributes($product);
 $care=(array)$product->get_meta('_mk8_care');if($care){echo '<h3>Cuidados y recomendaciones</h3><ul>';foreach($care as $line)echo '<li>'.esc_html($line).'</li>';echo '</ul>';}
 echo '</section></div></div>';
}
add_action('woocommerce_product_options_general_product_data',function(){global $product_object;if(!$product_object)return;woocommerce_wp_textarea_input(['id'=>'_mk8_care_editor','label'=>'Cuidados y advertencias','description'=>'Una recomendación por línea. Aparece en Detalles del producto. Sin emojis.','value'=>implode("\n",(array)$product_object->get_meta('_mk8_care'))]);});
add_action('woocommerce_admin_process_product_object',function($product){if(!isset($_POST['_mk8_care_editor']))return;$lines=preg_split('/\r?\n/u',sanitize_textarea_field(wp_unslash($_POST['_mk8_care_editor'])));$lines=array_values(array_unique(array_filter(array_map('mk8_clean_text',$lines))));$product->update_meta_data('_mk8_care',$lines);});
function mk8_methods(){
 $zones=WC_Shipping_Zones::get_zones();$zones[0]=['zone_name'=>'Resto del mundo'];$rows=[];
 foreach($zones as $id=>$zone)foreach((new WC_Shipping_Zone($id))->get_shipping_methods(true) as $m)$rows[]=['zone'=>$zone['zone_name'],'method'=>$m];return $rows;
}
function mk8_dialog($id,$title,$body){
 $s=mk8_settings();echo '<dialog class="mk8-dialog" id="mk8-'.$id.'" role="dialog" tabindex="-1" aria-modal="true" aria-labelledby="mk8-'.$id.'-title"><button type="button" class="mk8-close" aria-label="Cerrar"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button><h2 id="mk8-'.$id.'-title">'.esc_html($title).'</h2>'.$body.'<button type="button" class="button mk8-dismiss">'.esc_html($s['continue_label']).'</button></dialog>';
}
add_action('wp_footer',function(){
 if(!is_product())return;$s=mk8_settings();$business=mkpn_business();$methods=mk8_methods();
 $body=mk12_shipping_public($methods,$s);
 $pickup=false;foreach($methods as $row)if($row['method']->id==='local_pickup')$pickup=true;
 if($pickup&&$s['pickup_enabled']==='yes'&&$s['pickup_name']&&$s['pickup_address'])$body.='<div class="mk8-pickup"><table><thead><tr><th>Punto de recojo</th><th>Dirección</th></tr></thead><tbody><tr><td>'.esc_html($s['pickup_name']).'</td><td>'.esc_html($s['pickup_address']).'</td></tr></tbody></table></div>';
 $body.='<p>'.esc_html($s['inventory_note']).'</p>';mk8_dialog('availability',$s['availability_title'],$body);
 $body='<div class="mk8-columns">';foreach(WC()->payment_gateways()->get_available_payment_gateways() as $id=>$gateway){
  $path=$id==='bacs'?'<path d="m2 9 10-6 10 6ZM4 11v8m5-8v8m6-8v8m5-8v8M2 21h20"/>':'<path d="M3 5h18v14H3zM3 10h18M7 15h4"/>';
  $body.='<section><svg class="mk8-payment-icon" viewBox="0 0 24 24" aria-hidden="true">'.$path.'</svg><h3>'.esc_html(preg_replace('/\s*\(prueba local\)\s*/iu','',$gateway->get_title())).'</h3><p>'.nl2br(esc_html($s['payment_'.$id]??wp_strip_all_tags($gateway->get_description()))).'</p></section>';
 }$body.='</div>';mk8_dialog('payment',$s['payment_title'],$body);
 $body='<table><thead><tr><th>Método de envío</th><th>Tiempo de entrega</th><th>Costo</th></tr></thead><tbody>';
 foreach($methods as $row){$m=$row['method'];
  if($m->id==='free_shipping'){$requires=$m->get_option('requires');$cost='Gratis';if(in_array($requires,['min_amount','either','both'],true))$cost.=' desde '.wp_strip_all_tags(wc_price((float)$m->get_option('min_amount')));if(in_array($requires,['coupon','both'],true))$cost.=' con cupón de envío gratis';}
  elseif($m->id==='flat_rate'){$raw=$m->get_option('cost');$cost=is_numeric($raw)?wc_price((float)$raw):'Se calcula al finalizar la compra';}
  elseif($m->id==='local_pickup'){$cost=wc_price((float)$m->get_option('cost'));}else $cost='Se calcula al finalizar la compra';
  $body.='<tr><td>'.esc_html($m->get_title().' · '.$row['zone']).'</td><td>'.esc_html(mk12_delivery_time($s)).'</td><td>'.$cost.'</td></tr>';
 }$body.='</tbody></table><p class="mk8-note">'.nl2br(esc_html($s['delivery_note'])).'</p><p>'.esc_html($s['delivery_final']).'</p>';mk8_dialog('delivery',$s['delivery_title'],$body);
 $body='<div class="mk8-columns"><section><h3>Devoluciones</h3><p>'.nl2br(esc_html($s['returns_text'])).'</p></section><section><h3>Garantías</h3><p>'.nl2br(esc_html($s['warranty_text'])).'</p></section></div><p>Contacto: <a href="mailto:'.esc_attr($business['email']??'').'">'.esc_html($business['email']??'').'</a></p>';mk8_dialog('returns',$s['returns_title'],$body);
},60);
add_filter('gettext',function($translated,$original,$domain){if(!in_array($domain,['woocommerce','blocksy'],true))return $translated;return ['SKU:'=>'Referencia:','SKU'=>'Referencia','Category:'=>'Categoría:','CATEGORY:'=>'Categoría:','Category'=>'Categoría','CATEGORY'=>'Categoría','Categories:'=>'Categorías:',
 'Add to cart: &ldquo;%s&rdquo;'=>'Agregar al carrito: «%s»','&ldquo;%s&rdquo; has been added to your cart'=>'Se agregó «%s» a tu carrito','Read more about &ldquo;%s&rdquo;'=>'Más información de «%s»',
 'Full screen image'=>'Imagen a pantalla completa','Zoom in/out'=>'Ampliar o reducir','Toggle fullscreen'=>'Alternar pantalla completa','Share'=>'Compartir','Close (Esc)'=>'Cerrar (Esc)','Previous (arrow left)'=>'Anterior (flecha izquierda)','Next (arrow right)'=>'Siguiente (flecha derecha)','Search'=>'Buscar','Search for...'=>'Buscar…','Search for:'=>'Buscar:','Search Form'=>'Formulario de búsqueda','My account'=>'Mi cuenta'][$original]??$translated;},300,3);
add_filter('woocommerce_get_stock_html',function($html){return is_product()?'':$html;},300);
add_filter('gettext_with_context',function($translated,$original,$context,$domain){return $domain==='blocksy'&&$original==='Search'?'Buscar':$translated;},300,4);
add_filter('gettext',function($translated,$original,$domain){return $domain==='blocksy'?(['Search button'=>'Botón de búsqueda','Search in category'=>'Buscar en categoría','Oops! That page can&rsquo;t be found.'=>'No encontramos esta página.','It looks like nothing was found at this location. Maybe try to search for something else?'=>'No encontramos contenido en esta dirección. Puedes buscar otro juguete.'][$original]??$translated):$translated;},310,3);
add_filter('woocommerce_email_styles',function($css){return $css.' #template_header{background:#ffd60a!important;color:#0756ad!important} #template_header h1{color:#0756ad!important;font-family:Arial,sans-serif!important} #template_container{border-radius:20px!important;border:1px solid #dce4ef!important} #body_content a{color:#0756ad!important} #template_footer{color:#0756ad!important}';},100);
// Optional fiscal identifier, editable on the native order and rendered by native email meta.
add_filter('woocommerce_checkout_fields',function($fields){$fields['billing']['billing_document']=['type'=>'text','label'=>'Número de documento','required'=>false,'class'=>['form-row-wide'],'priority'=>25,'maxlength'=>20];return $fields;});
add_action('woocommerce_checkout_create_order',function($order,$data){if(!empty($data['billing_document']))$order->update_meta_data('_billing_document',sanitize_text_field($data['billing_document']));},10,2);
add_filter('woocommerce_email_order_meta_fields',function($fields,$sent_to_admin,$order){$document=$order->get_meta('_billing_document');if($document)$fields['billing_document']=['label'=>'Número de documento','value'=>$document];return $fields;},10,3);
// The previous Blocks configuration hides Woo's phone globally. Restore it only
// for the progressive classic checkout and its native AJAX validation requests.
function mk8_classic_checkout_request(){return mk6_checkout()||(defined('WC_DOING_AJAX')&&WC_DOING_AJAX&&in_array((string)($_GET['wc-ajax']??''),['checkout','update_order_review'],true));}
add_filter('pre_option_woocommerce_checkout_phone_field',function($value){return mk8_classic_checkout_request()?'required':$value;});
add_action('woocommerce_email_before_order_table',function($order,$sent_to_admin,$plain_text){
 if($sent_to_admin||$order->get_payment_method()!=='bacs')return;
 $gateway=WC()->payment_gateways()->payment_gateways()['bacs']??null;
 if(!$gateway||$gateway->get_option('instructions')||get_option('woocommerce_bacs_accounts'))return;
 $business=mkpn_business();$text='Para coordinar la transferencia, solicita los datos bancarios a '.($business['email']??'').'. Indica el número de pedido como referencia. El envío se coordina al confirmar el abono.';
 echo $plain_text?esc_html($text)."\n":'<p>'.esc_html($text).'</p>';
},20,3);

// V10: keep the native review list/form; only remove its redundant section heading.
function mk10_native_reviews(){
 ob_start();comments_template();$html=ob_get_clean();
 echo preg_replace('~<h2\b[^>]*class="woocommerce-Reviews-title"[^>]*>.*?</h2>~s','',$html,1);
}
function mk10_help_icon($id){
 $paths=['payment'=>'M3 5h18v14H3zM3 10h18M7 15h4','delivery'=>'M2 5h12v12H2zM14 9h4l4 4v4h-8M6 17a2 2 0 1 0 0 4 2 2 0 0 0 0-4M18 17a2 2 0 1 0 0 4 2 2 0 0 0 0-4','returns'=>'M12 2 3 6v6c0 5 9 10 9 10s9-5 9-10V6zM8 12l3 3 5-6'];
 return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="'.esc_attr($paths[$id]).'"/></svg>';
}
// Native customizer read filter, scoped to product views; no theme/options edits.
add_filter('theme_mod_product_hero_elements',function($elements){
 if(!is_product())return $elements;
 return array_values(array_filter((array)$elements,fn($element)=>($element['id']??'')!=='breadcrumbs'));
});
