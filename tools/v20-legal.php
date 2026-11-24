<?php
// CLI: aplica Términos, Privacidad y Métodos de pago. Idempotente; guarda copia del contenido anterior.
// Uso local: php tools/v20-legal.php   |  VPS: wp eval-file v20-legal.php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
if (!defined('ABSPATH')) {
    $_SERVER['HTTP_HOST'] = 'localhost:8082'; $_SERVER['REQUEST_URI'] = '/wordpress/';
    require dirname(__DIR__) . '/wordpress/wp-load.php';
}
function mk20_p($t) { return "<!-- wp:paragraph -->\n<p>$t</p>\n<!-- /wp:paragraph -->\n\n"; }
function mk20_h($t) { return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">$t</h2>\n<!-- /wp:heading -->\n\n"; }
function mk20_ul($items) {
    $li = ''; foreach ($items as $i) $li .= "<!-- wp:list-item -->\n<li>$i</li>\n<!-- /wp:list-item -->\n";
    return "<!-- wp:list -->\n<ul class=\"wp-block-list\">$li</ul>\n<!-- /wp:list -->\n\n";
}
function mk20_table($rows) {
    $h = array_shift($rows); $thead = '<thead><tr><th>' . implode('</th><th>', $h) . '</th></tr></thead>'; $body = '';
    foreach ($rows as $r) $body .= '<tr><td>' . implode('</td><td>', $r) . '</td></tr>';
    return "<!-- wp:table -->\n<figure class=\"wp-block-table\"><table>$thead<tbody>$body</tbody></table></figure>\n<!-- /wp:table -->\n\n";
}
function mk20_sc($c) { return "<!-- wp:shortcode -->\n$c\n<!-- /wp:shortcode -->\n\n"; }

$home = untrailingslashit(home_url());
$a = function ($path, $text) use ($home) { return '<a href="' . esc_url($home . $path) . '">' . $text . '</a>'; };
$terms_l = $a('/terminos-y-condiciones/', 'Términos y condiciones');
$priv_l = $a('/politica-privacidad/', 'Política de privacidad y protección de datos');
$pay_l = $a('/metodos-de-pago/', 'Métodos de pago');
$claim_l = $a('/libro-de-reclamaciones/', 'Libro de Reclamaciones');
$N = '[mk_negocio campo="name"]'; $RUC = '[mk_negocio campo="ruc"]'; $DIR = '[mk_negocio campo="address"]';
$MAIL = '[mk_negocio campo="email"]'; $TEL = '[mk_negocio campo="phone"]';

/* ---------- TÉRMINOS Y CONDICIONES ---------- */
$t  = mk20_p('<strong>Vigentes desde el 24 de noviembre de 2026.</strong>');
$t .= mk20_p("Estos términos regulan el uso de la tienda en línea MiniKids Perú y las compras que realices en ella. Al navegar o comprar aceptas estas condiciones y nuestra $priv_l. Nada de lo aquí escrito limita los derechos que te reconoce el Código de Protección y Defensa del Consumidor (Ley N.° 29571).");
$t .= mk20_h('1. Quiénes somos');
$t .= mk20_ul(["Razón social: $N", "RUC: $RUC", "Domicilio: $DIR", "Correo de atención: $MAIL", "Teléfono y WhatsApp: $TEL"]);
$t .= mk20_h('2. Quién puede comprar y cuentas de usuario');
$t .= mk20_p('Para comprar debes ser mayor de 18 años o actuar con la autorización de tu padre, madre o tutor. Puedes comprar como invitado o crear una cuenta. Eres responsable de que tus datos sean verdaderos y de mantener en reserva tu contraseña; avísanos si detectas un uso no autorizado. Podemos suspender cuentas usadas de forma fraudulenta o contraria a estos términos.');
$t .= mk20_h('3. Productos, precios y promociones');
$t .= mk20_ul(['Los precios se muestran en soles (S/) e incluyen los impuestos aplicables. El costo de envío se informa antes de confirmar la compra.', 'Las fotografías son referenciales; las características relevantes (edad recomendada, material, medidas y contenido) figuran en la ficha de cada producto.', 'Respeta la edad recomendada. Los juguetes con piezas pequeñas no son aptos para menores de 3 años y su uso requiere supervisión adulta.', 'Las ofertas rigen mientras estén publicadas y hasta agotar stock. El precio válido es el que figura al confirmar tu pedido.', 'Si detectamos un error evidente de precio o stock te avisaremos antes del despacho; podrás aceptar la corrección o anular el pedido con devolución íntegra de lo pagado.']);
$t .= mk20_h('4. Cómo se realiza una compra');
$t .= mk20_ul(['Agrega los productos a tu carrito y pulsa «Ir a pagar».', 'Completa tus datos, la dirección de envío y elige el medio de pago.', 'La ventana de compra segura dura 10 minutos. Si vence, vuelves al carrito, que se conserva, y puedes iniciarla otra vez.', 'Revisa el resumen, acepta estos términos y confirma con «Realizar pedido».', 'Recibirás la confirmación en tu correo con el número de pedido. El contrato queda sujeto a la verificación del pago (cuando corresponda) y a la disponibilidad del producto.']);
$t .= mk20_h('5. Medios de pago');
$t .= mk20_p("Estos son los medios disponibles actualmente. Más detalle en $pay_l.");
$t .= mk20_sc('[mk_metodos_pago]');
$t .= mk20_p('No te pedimos ni almacenamos números de tarjeta. Verifica siempre los datos de pago por nuestros canales oficiales. Podemos anular, avisándote previamente, los pedidos cuyo pago no se acredite.');
$t .= mk20_h('6. Comprobante de pago');
$t .= mk20_p('Emitimos el comprobante de pago conforme a las normas de SUNAT. Si necesitas factura, indica tu RUC y razón social en las notas del pedido antes de confirmarlo.');
$t .= mk20_h('7. Envíos y entregas');
$t .= mk20_table([['Concepto', 'Condición'], ['Cobertura', 'Envíos a todo el Perú'], ['Envío estándar', 'S/ 15.00'], ['Envío gratis', 'En compras desde S/ 149.00'], ['Plazo estimado', 'De 2 a 5 días hábiles, según el destino']]);
$t .= mk20_p('El plazo corre desde la confirmación del pedido y, en transferencias, desde que se verifica el abono. Zonas alejadas pueden requerir más tiempo; te informaremos de cualquier demora. Asegúrate de que la dirección y el teléfono sean correctos y de que haya alguien para recibir el pedido. Revisa el paquete al recibirlo y avísanos de inmediato si llega dañado o incompleto.');
$t .= mk20_h('8. Cambios, devoluciones y reembolsos');
$t .= mk20_ul(["<strong>Cambio por decisión del cliente:</strong> tienes 30 días calendario desde la entrega para solicitarlo, con el producto sin uso, completo, en su empaque original y con el comprobante. El costo del retorno corre por cuenta del cliente.", '<strong>Producto defectuoso, dañado o distinto al pedido:</strong> escríbenos apenas lo detectes. Según el caso y la ley, procede la reposición, la reparación o la devolución del dinero, sin costo de envío para ti.', "<strong>Cómo solicitarlo:</strong> envía tu número de pedido, una descripción y fotografías a $MAIL o por WhatsApp al $TEL.", '<strong>Reembolsos:</strong> aprobada la devolución, reembolsamos por transferencia a la cuenta que indiques dentro de los 15 días hábiles siguientes.', 'Estas condiciones se suman a las garantías legales; no las reemplazan ni las recortan.']);
$t .= mk20_h('9. Anulación de pedidos');
$t .= mk20_p('Puedes pedir la anulación antes del despacho escribiéndonos. Si ya pagaste, devolvemos el importe en el plazo indicado en el punto anterior. Si no podemos atender tu pedido por falta de stock u otra causa justificada, te lo comunicaremos y devolveremos lo pagado.');
$t .= mk20_h('10. Comentarios y valoraciones');
$t .= mk20_p('Solo los clientes que compraron un producto pueden valorarlo. Los comentarios deben ser veraces y respetuosos, sin datos personales de terceros, publicidad ni contenido ofensivo. Podemos moderar o retirar los que incumplan estas reglas.');
$t .= mk20_h('11. Propiedad intelectual y uso del sitio');
$t .= mk20_p('La marca, el diseño, los textos y las imágenes del sitio pertenecen a su titular o se usan con autorización. No está permitido copiarlos con fines comerciales, intentar vulnerar la seguridad, usar programas que sobrecarguen el servicio ni suplantar a otras personas. Las marcas de terceros pertenecen a sus respectivos dueños.');
$t .= mk20_h('12. Responsabilidad');
$t .= mk20_p('Procuramos que el sitio esté disponible y la información sea correcta, aunque pueden ocurrir interrupciones por mantenimiento o causas ajenas a nuestro control. No respondemos por demoras debidas a caso fortuito o fuerza mayor, sin perjuicio de tus derechos como consumidor.');
$t .= mk20_h('13. Reclamos y quejas');
$t .= mk20_p("Contamos con $claim_l virtual. Respondemos en un plazo máximo de 15 días hábiles. Presentar un reclamo no impide acudir a otras vías de solución de controversias ni denunciar ante el Indecopi.");
$t .= mk20_h('14. Ley aplicable y cambios');
$t .= mk20_p('Estos términos se rigen por las leyes de la República del Perú. Las controversias se atienden ante las autoridades competentes, respetando el fuero que corresponda al consumidor. Podemos actualizarlos; a cada pedido se aplica la versión publicada cuando se confirmó.');

/* ---------- PRIVACIDAD ---------- */
$p  = mk20_p('<strong>Vigente desde el 24 de noviembre de 2026.</strong>');
$p .= mk20_p('Esta política explica qué datos personales tratamos, para qué, con quién los compartimos y cómo ejercer tus derechos, conforme a la Ley N.° 29733, Ley de Protección de Datos Personales, y su Reglamento (Decreto Supremo N.° 016-2024-JUS).');
$p .= mk20_h('1. Responsable del tratamiento');
$p .= mk20_ul(["$N, RUC $RUC", "Domicilio: $DIR", "Contacto para datos personales: $MAIL · $TEL"]);
$p .= mk20_h('2. Qué datos tratamos');
$p .= mk20_table([['Cuándo', 'Datos'], ['Cuenta de cliente', 'Nombre, apellido, correo electrónico y contraseña (guardada cifrada)'], ['Compra', 'Nombre, apellido, correo, teléfono, documento (si lo indicas), dirección de entrega, notas, productos, importes y medio de pago elegido'], ['Libro de Reclamaciones', 'Datos de identificación y contacto, datos del padre o apoderado cuando el consumidor es menor, y el detalle del reclamo o queja'], ['Comentarios', 'Nombre, correo (no se publica), valoración y texto'], ['Atención', 'Lo que nos envíes por correo, teléfono o WhatsApp'], ['Navegación', 'Dirección IP, tipo de dispositivo y navegador, páginas vistas, acciones en la tienda y cookies']]);
$p .= mk20_p('Los datos marcados como obligatorios en los formularios son necesarios para prestar el servicio; si no los entregas no podremos completar la operación. Los demás son opcionales. No solicitamos datos sensibles ni números de tarjeta.');
$p .= mk20_h('3. Para qué los usamos');
$p .= mk20_table([['Finalidad', 'Base'], ['Gestionar tu cuenta, pedidos, pagos, entregas, cambios y garantías', 'Ejecución de la relación de compra'], ['Enviar confirmaciones y avisos sobre tu pedido', 'Ejecución de la relación de compra'], ['Emitir comprobantes, atender reclamos y requerimientos de autoridades', 'Obligación legal'], ['Prevenir fraudes y proteger la seguridad del sitio', 'Seguridad del servicio y obligaciones legales'], ['Publicar tus comentarios', 'Tu consentimiento'], ['Medir el uso de la tienda y mejorarla (analítica)', 'Tu consentimiento, que puedes rechazar en el aviso de cookies']]);
$p .= mk20_p('No vendemos tus datos. No te enviamos publicidad sin tu consentimiento previo.');
$p .= mk20_h('4. Con quién los compartimos');
$p .= mk20_ul(['Proveedor de alojamiento del servidor y Cloudflare, para operar y proteger el sitio.', 'Brevo, para el envío de correos de pedidos y reclamaciones.', 'Google (Google Analytics 4), para estadísticas de uso.', 'Empresas de transporte, con los datos necesarios para la entrega.', 'WhatsApp (Meta), cuando decides contactarnos o compartir por ese medio.', 'Autoridades competentes, cuando la ley lo exija.']);
$p .= mk20_p('Algunos de estos proveedores tratan datos fuera del Perú (por ejemplo, en Estados Unidos o la Unión Europea), lo que implica un flujo transfronterizo de datos. Los complementos de pago instalados en la tienda pueden cargar recursos de sus proveedores, sujetos a sus propias políticas.');
$p .= mk20_h('5. Cookies y tecnologías similares');
$p .= mk20_table([['Tipo', 'Ejemplos', 'Para qué'], ['Necesarias', 'wp_woocommerce_session_*, woocommerce_cart_hash, woocommerce_items_in_cart, wordpress_logged_in_*', 'Carrito, sesión y compra segura'], ['Atribución de la tienda', 'sbjs_*', 'Saber desde qué página o enlace llegaste'], ['Analítica', '_ga, _ga_*', 'Estadísticas de Google Analytics'], ['Preferencias', 'Almacenamiento local del navegador', 'Recordar tu elección sobre cookies']]);
$p .= mk20_p('Puedes rechazar la analítica en el aviso de cookies y borrar o bloquear cookies desde tu navegador; sin las necesarias, el carrito y la compra pueden no funcionar.');
$p .= mk20_h('6. Cuánto tiempo los conservamos');
$p .= mk20_ul(['Cuenta: mientras la mantengas activa o hasta que pidas eliminarla.', 'Pedidos y comprobantes: durante los plazos exigidos por las normas tributarias y comerciales.', 'Libro de Reclamaciones: al menos dos años, según su reglamento.', 'Comentarios: mientras estén publicados o hasta que pidas retirarlos.', 'Analítica: según el periodo de retención configurado en Google Analytics.']);
$p .= mk20_h('7. Tus derechos');
$p .= mk20_p("Puedes ejercer los derechos de información, acceso, rectificación, cancelación y oposición (ARCO), revocar tu consentimiento y los demás que reconoce la ley. Envía tu solicitud a $MAIL o preséntala en $DIR indicando tu nombre, un medio de respuesta, el derecho que ejerces, lo que solicitas y copia de tu documento de identidad (o del representante). Es gratuito.");
$p .= mk20_p('Respondemos dentro de los plazos máximos del Reglamento, contados desde el día siguiente de la solicitud (entre ellos, 8 días para el derecho de información y 20 días para el de acceso), que pueden ampliarse una sola vez por un periodo igual cuando se justifique, salvo el de información. Si consideras que no atendimos tu derecho, puedes reclamar ante la Autoridad Nacional de Protección de Datos Personales del Ministerio de Justicia y Derechos Humanos.');
$p .= mk20_h('8. Seguridad');
$p .= mk20_p('Aplicamos medidas como conexión cifrada (HTTPS), contraseñas almacenadas de forma cifrada, acceso restringido a la administración, copias de respaldo y credenciales fuera de la carpeta pública del sitio. Ningún sistema es infalible; si ocurre un incidente que afecte tus datos, actuaremos y comunicaremos conforme a la ley.');
$p .= mk20_h('9. Niñas, niños y adolescentes');
$p .= mk20_p('La tienda está dirigida a personas adultas que compran juguetes. No creamos cuentas a sabiendas para menores. Si un padre, madre o tutor detecta datos de un menor entregados sin autorización, puede pedir su eliminación.');
$p .= mk20_h('10. Cambios');
$p .= mk20_p("Publicaremos en esta página cualquier actualización con su fecha de vigencia. Consulta también los $terms_l.");

/* ---------- MÉTODOS DE PAGO ---------- */
$m  = mk20_p('Estos son los medios de pago disponibles hoy en MiniKids Perú. Eliges el tuyo en el último paso de la compra segura.');
$m .= mk20_sc('[mk_metodos_pago]');
$m .= mk20_h('Cómo pagar');
$m .= mk20_ul(['Agrega tus juguetes al carrito y pulsa «Ir a pagar».', 'Completa tus datos y la dirección de envío.', 'Elige el medio de pago y confirma el pedido.', 'Guarda tu número de pedido: te sirve de referencia para el pago y la atención.']);
$m .= mk20_h('Compra con seguridad');
$m .= mk20_ul(['No pedimos números de tarjeta, claves ni códigos de verificación.', "Confirma los datos de pago solo por nuestros canales oficiales: $MAIL o WhatsApp $TEL.", 'Los precios están en soles. Envío estándar S/ 15.00 y gratis desde S/ 149.00.']);
$m .= mk20_p("Revisa nuestros $terms_l, la $priv_l y el $claim_l.");

/* ---------- APLICAR ---------- */
$backup = [];
$save = function ($slug, $title, $content, $id = 0) use (&$backup) {
    $page = $id ? get_post($id) : null;
    if (!$page || $page->post_type !== 'page') $page = get_page_by_path($slug);
    if ($page) $backup[$slug] = ['id' => $page->ID, 'title' => $page->post_title, 'content' => $page->post_content, 'status' => $page->post_status];
    $data = ['post_type' => 'page', 'post_title' => $title, 'post_name' => $slug, 'post_content' => wp_slash($content), 'post_status' => 'publish', 'comment_status' => 'closed'];
    if ($page) $data['ID'] = $page->ID;
    $result = $page ? wp_update_post($data, true) : wp_insert_post($data, true);
    if (is_wp_error($result)) { fwrite(STDERR, "ERROR $slug: " . $result->get_error_message() . "\n"); exit(1); }
    echo "pagina $slug => #$result\n";
    return (int) $result;
};
$admins = get_users(['role' => 'administrator', 'number' => 1, 'fields' => 'ID']); if ($admins) wp_set_current_user((int) $admins[0]);
$tid = $save('terminos-y-condiciones', 'Términos y condiciones', $t, (int) get_option('woocommerce_terms_page_id'));
$pid = $save('politica-privacidad', 'Política de privacidad y protección de datos', $p, (int) get_option('wp_page_for_privacy_policy'));
$mid = $save('metodos-de-pago', 'Métodos de pago', $m);
delete_post_meta($tid, '_mk_legal_draft');
update_option('woocommerce_terms_page_id', $tid);
update_option('wp_page_for_privacy_policy', $pid);

foreach (['bacs', 'cod'] as $gw) {
    $s = get_option("woocommerce_{$gw}_settings");
    if (is_array($s) && !empty($s['title'])) {
        $backup["gateway_$gw"] = $s['title'];
        $s['title'] = trim(preg_replace('/\s*\(prueba local\)\s*/iu', '', $s['title']));
        update_option("woocommerce_{$gw}_settings", $s);
        echo "titulo $gw => {$s['title']}\n";
    }
}

// Pie: el título «Métodos de pago» enlaza a su página.
$footer = get_post(219);
if ($footer && strpos($footer->post_content, '/metodos-de-pago/') === false) {
    $new = preg_replace('~(<h([2-6])[^>]*>)\s*Métodos de pago\s*(</h\2>)~u', '$1<a href="' . $home . '/metodos-de-pago/">Métodos de pago</a>$3', $footer->post_content, 1, $count);
    if ($count) { $backup['footer'] = $footer->post_content; wp_update_post(['ID' => 219, 'post_content' => wp_slash($new)]); echo "pie actualizado\n"; }
    else echo "pie: no se encontro el titulo, sin cambios\n";
}
$file = rtrim(getenv('MK20_BACKUP_DIR') ?: dirname(__DIR__, 2) . '/local-private', '/\\') . '/v20-legal-backup-' . gmdate('Ymd-His') . '.json';
echo (file_put_contents($file, wp_json_encode($backup, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ? "respaldo: $file\n" : "AVISO: no se pudo guardar el respaldo\n");
wp_cache_flush();
