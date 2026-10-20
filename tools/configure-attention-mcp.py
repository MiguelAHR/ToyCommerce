"""Publica bloques y ajustes a través de las ocho abilities, no por SQL."""
import json
from pathlib import Path
from mcp_local import MCP,ROOT

m=MCP()
pages=m.ability('minikids/list-content',{'post_type':'page','limit':100})['items']
claim=next(p['id'] for p in pages if p['slug']=='libro-de-reclamaciones')
about='''<!-- wp:group {"className":"mkpn-about","layout":{"type":"constrained"}} -->
<div class="wp-block-group mkpn-about"><!-- wp:heading -->
<h2 class="wp-block-heading">Jugar hoy, un mejor mañana</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Somos MiniKids Perú, una tienda de juguetes para acompañar la imaginación, la curiosidad y los momentos de juego en familia.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>Queremos ayudarte a encontrar su próxima aventura: un regalo, un juego para compartir o una nueva forma de descubrir el mundo. Puedes explorar las categorías y revisar la información de cada juguete antes de elegir.</p>
<!-- /wp:paragraph -->
<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Imaginación para cada etapa</h3>
<!-- /wp:heading -->
<!-- wp:paragraph --><p>Peluches, construcción, creatividad y juegos para descubrir nuevas historias a tu manera.</p><!-- /wp:paragraph --></div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Estamos para escucharte</h3>
<!-- /wp:heading -->
<!-- wp:paragraph --><p>Consulta la información de los productos y contáctanos si necesitas ayuda con tu pedido o con la atención recibida.</p><!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="http://localhost:8082/wordpress/tienda/">Explorar juguetes</a></div>
<!-- /wp:button -->
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="http://localhost:8082/wordpress/contacto/">Conversemos</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->'''
book='''<!-- wp:paragraph -->
<p>Queremos escucharte. Registra un reclamo sobre un producto o servicio, o una queja sobre la atención recibida. Al terminar obtendrás una constancia que puedes imprimir o guardar como PDF.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>El plazo máximo de respuesta es de 15 días hábiles. Conserva el número de tu hoja para dar seguimiento a la solicitud.</p>
<!-- /wp:paragraph -->
<!-- wp:shortcode -->
[minikids_libro_reclamaciones]
<!-- /wp:shortcode -->'''
privacy='''<!-- wp:heading --><h2 class="wp-block-heading">Datos para atenderte</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>MIMI KIDS STORE E.I.R.L., RUC 20609651530, utiliza los datos proporcionados en la tienda para gestionar pedidos, cuentas y solicitudes de atención. Domicilio: Av. Alfredo Mendiola 3698, Independencia, Lima, Perú. Contacto: vektronova@gmail.com.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Libro de Reclamaciones</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Los datos del formulario se guardan en un registro privado para documentar y atender la solicitud. La constancia y las comunicaciones se remiten al correo indicado por el consumidor y al proveedor mediante el servicio de correo Brevo. El personal autorizado del negocio puede revisar el registro y responder. No usamos la información de una reclamación para publicidad.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Puedes contactar al proveedor para consultar el tratamiento de tus datos y solicitar el ejercicio de los derechos que correspondan. Esta página se puede actualizar desde el administrador para reflejar cambios en la operación del negocio.</p><!-- /wp:paragraph -->'''
for id,title,content in [(10,'¿Quiénes somos?',about),(claim,'Libro de Reclamaciones',book),(3,'Privacidad',privacy)]:
    old=m.ability('minikids/get-content',{'id':id})
    (ROOT/f'local-private/backups/page-{id}-mcp.json').write_text(json.dumps(old,ensure_ascii=False),encoding='utf-8')
    m.ability('minikids/update-content',{'id':id,'title':title,'content':content,'status':'publish'})
    print('Página editable publicada mediante MCP:',id)
options={
 'mkp_business':{'name':'MIMI KIDS STORE E.I.R.L.','ruc':'20609651530','address':'Av. Alfredo Mendiola 3698, Independencia, Lima, Perú','email':'vektronova@gmail.com','phone':'+51 991 117 308'},
 'mkp_mail_enabled':'yes','admin_email':'vektronova@gmail.com',
 'woocommerce_email_from_address':'vektronova@gmail.com','woocommerce_email_from_name':'MiniKids Perú',
 'wp_page_for_privacy_policy':3,
}
for name,value in options.items():m.ability('minikids/update-option',{'name':name,'value':value})
for email in ['new_order','customer_on_hold_order','customer_processing_order','customer_completed_order']:
    name='woocommerce_'+email+'_settings';old=m.ability('minikids/get-option',{'name':name})['value']
    value=old if isinstance(old,dict) else {};value['enabled']='yes'
    if email=='new_order':value['recipient']='vektronova@gmail.com'
    m.ability('minikids/update-option',{'name':name,'value':value})
path='mu-plugins/minikids-estetica.php'
old=m.ability('minikids/read-file',{'path':path})['content']
marker='''                    <li><a href="/wordpress/contacto/">Contacto</a></li>
                </ul>'''
new=old.replace(marker,marker+'''\n                <?php wp_nav_menu(['theme_location'=>'minikids_atencion','container'=>false,'menu_class'=>'footer-links mkp-attention-links','fallback_cb'=>false]); ?>''',1)
if new==old and 'mkp-attention-links' not in old:raise RuntimeError('No se encontró el punto de menú en el pie')
if new!=old:
    result=m.ability('minikids/write-file',{'path':path,'content':new,'backup':True})
    print('Pie actualizado mediante MCP con backup:',bool(result['backup']))
print('Datos comerciales y correos configurados por MCP; clave fuera de WordPress')
