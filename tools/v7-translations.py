"""Residual strings observed in the installed native Woo templates; preserve placeholders."""
import json,re
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5'
audit=json.loads((ROOT.parent/'local-private/v7-backups/native-before.json').read_text('utf8'))
translations={
'cart':'''Ingresa tu dirección para ver las opciones de envío.
No hay opciones de envío disponibles. Comprueba tu dirección o contáctanos si necesitas ayuda.
No se encontraron opciones de envío para %s.
Ingresar otra dirección
Resumen
(estimado para %s)
Retirar %s del carrito
Actualizar carrito
También te puede interesar&hellip;
Se retiró &ldquo;%s&rdquo; de tu carrito
No hay productos en el carrito.
Selecciona un país o región&hellip;
Selecciona una opción&hellip;''',
'checkout':'''Hay problemas con algunos artículos de tu carrito. Regresa al carrito y revísalos antes de finalizar la compra.
Volver al carrito
Facturación y envío
¿Crear una cuenta?
Debes iniciar sesión para finalizar la compra.
Finalizar compra
Ingresa tu código de cupón
¿Ya tienes una cuenta?
Haz clic aquí para iniciar sesión
Si ya compraste antes, ingresa tus datos. Si eres un cliente nuevo, continúa a la sección de facturación.
Cant.
Totales
Métodos de pago
No hay métodos de pago disponibles para tu ubicación. Contáctanos si necesitas ayuda u otra opción.
No pudimos verificar el correo electrónico ingresado. Vuelve a intentarlo.
Para ver esta página, debes %1$siniciar sesión%2$s o verificar el correo asociado al pedido.
Verificar
Total:
No hay métodos de pago disponibles. Contáctanos si necesitas ayuda u otra opción.
Completa tus datos para ver los métodos de pago disponibles.
Si tu navegador no admite JavaScript o está desactivado, pulsa %1$sActualizar totales%2$s antes de realizar el pedido para comprobar el importe final.
No se pudo procesar tu pedido porque el banco o comercio rechazó la transacción. Vuelve a intentar la compra.
Pagar
Mi cuenta''',
'myaccount':'''Hola %1$s (¿no eres %1$s? <a href="%2$s">Cerrar sesión</a>)
Desde tu cuenta puedes ver tus <a href="%1$s">pedidos recientes</a>, gestionar tu <a href="%2$s">dirección de facturación</a> y <a href="%3$s">cambiar tu contraseña y datos de cuenta</a>.
Desde tu cuenta puedes ver tus <a href="%1$s">pedidos recientes</a>, gestionar tus <a href="%2$s">direcciones de envío y facturación</a> y <a href="%3$s">cambiar tu contraseña y datos de cuenta</a>.
Aún no hay descargas disponibles.
Ver productos
Métodos de pago
Añadir método de pago
Los nuevos métodos de pago se añaden al finalizar la compra. Contáctanos si necesitas ayuda.
Nombre público
Este nombre se mostrará en tu cuenta y en los comentarios
Este nombre se mostrará en tu cuenta
Cambiar contraseña
Contraseña actual (déjala vacía para conservarla)
Nueva contraseña (déjala vacía para conservarla)
Confirmar nueva contraseña
Guardar cambios
Guardar dirección
Iniciar sesión
Usuario o correo electrónico
Obligatorio
Contraseña
Recordarme
Ingresar
¿Olvidaste tu contraseña?
Registrarse
Usuario
Te enviaremos un enlace para crear una nueva contraseña a tu correo electrónico.
¿Olvidaste tu contraseña? Ingresa tu usuario o correo electrónico. Recibirás un enlace para crear una nueva contraseña.
Usuario o correo
Restablecer contraseña
Tu solicitud de desistimiento se ha enviado.
Enviamos una confirmación a %s con tus datos y la fecha y hora de envío. Consérvala como constancia de tu solicitud.
Revisaremos tu solicitud y te contactaremos sobre los siguientes pasos y cualquier reembolso correspondiente.
Volver a la tienda
Revisa tus datos antes de confirmar.
Aún no se ha enviado nada. Tu solicitud se enviará al seleccionar «Confirmar desistimiento».
Progreso de la solicitud de desistimiento
Tus datos
Revisar y confirmar
Después de confirmar, enviaremos una constancia a %s con estos datos y la fecha y hora de envío. Consérvala como comprobante.
Confirmar desistimiento
Editar datos
Indícanos si deseas desistir de un pedido realizado en esta tienda. No necesitas indicar un motivo.
Algunos artículos, como los personalizados, pueden no ser elegibles. Revisamos cada solicitud y respondemos por correo.
No necesitas indicar un motivo. Si seleccionaste artículos específicos, enuméralos aquí.
Continuar a la revisión
Ingresa una nueva contraseña.
Nueva contraseña
Repite la nueva contraseña
Guardar
Se envió el correo para restablecer tu contraseña.
Enviamos un correo para restablecer la contraseña a la dirección de tu cuenta. Puede tardar unos minutos. Espera al menos 10 minutos antes de intentarlo de nuevo.
Estas direcciones se usarán de forma predeterminada al finalizar la compra.
Editar %s
Añadir %s
Aún no has configurado este tipo de dirección.
Descargas disponibles
Pedido
Fecha
Estado
Pedidos recientes
Páginas de la cuenta
Ver pedido número %s
%1$s pedido número %2$s
Anterior
Siguiente
Aún no has realizado pedidos.
%1$s terminado en %2$s
No hay métodos guardados.
Pendiente de confirmación
Avisos de stock pendientes de confirmación
Activo
Avisos de stock activos
Fecha de registro
Acciones
Producto no disponible
Reenviar verificación
Cancelar
Paginación de avisos de stock activos
El pedido #%1$s se realizó el %2$s y su estado actual es %3$s.
Actualizaciones del pedido''',
'emails':'''El pedido #%1$s de %2$s ha sido cancelado:
Te informamos que el pedido #%1$s de %2$s ha sido cancelado.
El pago del pedido #%1$s de %2$s falló. Estos son sus detalles:
El pago del pedido #%1$s de %2$s no pudo completarse. Estos son sus detalles:
Recibiste el siguiente pedido de %s:
Recibiste un nuevo pedido de %s:
Un cliente envió una solicitud de desistimiento de un pedido.
WooCommerce vinculó esta solicitud con un pedido y añadió una nota.
WooCommerce no pudo vincular esta solicitud automáticamente con un pedido y no añadió ninguna nota.
ID del pedido vinculado: %d
Ver pedido vinculado
Hola %s:
Se habilitó el método de pago «%1$s» en este sitio: %2$s
Si no habilitaste este método de pago, inicia sesión y considera desactivarlo aquí:
Este correo se envió a %s
Pedido cancelado: #%s,
Gracias por leer este mensaje.
Pedido fallido: #%s,
Esperamos que vuelvan pronto. Consulta cómo <a href="https://woocommerce.com/document/managing-orders/">resolver pagos fallidos</a>.
Nuevo pedido: #%s
¡Felicitaciones por la venta!
Método de pago habilitado
Hola:
Se habilitó un método de pago en %s.
Si realizaste este cambio, puedes ignorar y eliminar este correo.
Pedido cancelado: #%s
Tu pedido #%s ha sido cancelado.
Gracias de nuevo. Si necesitas ayuda con tu pedido, contáctanos en %s.
¡Tu pedido está en camino!
Terminamos de procesar tu pedido.
Estos son los artículos de tu pedido:
No se pudo completar tu pedido
Estos son los detalles de tu pedido:
¡Tu artículo está en camino!
Algunos artículos de tu compra están en preparación de envío. Puedes consultar la siguiente información para hacer seguimiento:
Se retiró uno de tus envíos
Se retiró del sistema uno de los envíos de tu pedido, posiblemente por una corrección o actualización. Esto no afecta a los artículos que ya recibiste.
Tu envío se ha actualizado
Se actualizaron algunos detalles de tu envío, como el seguimiento, los artículos o el estado de entrega.
Esta es la información más reciente:
Detalles del pedido #%s,
Te damos la bienvenida a %s
Gracias por crear una cuenta en %s. Estos son tus datos de usuario.
Usuario: <b>%s</b>
Accede a tu cuenta para ver pedidos, cambiar tu contraseña y gestionar tus datos desde este enlace:
Mi cuenta
Esperamos verte pronto.
Se añadió una nota a tu pedido
Se añadió la siguiente nota a tu pedido:
Estos son los detalles de tu pedido como referencia:
Tu pedido está en espera
Gracias por tu pedido. Lo recibimos y está en espera mientras completamos el siguiente paso, como confirmar o procesar el pago.
Te enviaremos otra actualización cuando avance tu pedido. Este es su resumen:
Reembolso parcial: pedido %s
Tu pedido de %s se ha reembolsado parcialmente.
Hola:
Estos son los artículos que compraste:
Pedido reembolsado: %s
Gracias por tu pedido
Recibimos tu pedido y ya lo estamos procesando.
Tu pedido de %s se ha reembolsado.
Restablecer tu contraseña
Alguien solicitó una nueva contraseña para la siguiente cuenta en %s:
Si no hiciste esta solicitud, ignora este correo. Si deseas continuar, restablece tu contraseña con el siguiente enlace:
Valora tus compras recientes
Nos gustaría conocer tu opinión sobre los productos que compraste. Tu valoración ayuda a otros clientes y nos permite mejorar.
Pedido #%1$s (%2$s)
Confirma tu correo electrónico
Contenido de bloques predeterminado
Nota de la tienda:
No se pudo completar tu pedido en %1$s. Revisa los detalles y vuelve a intentar el pago desde este enlace: %2$s
Pagar este pedido
Se creó un pedido para ti en %1$s. Revisa los detalles y realiza el pago cuando estés listo: %2$s
Estos son los detalles de tu pedido realizado en %s:
Establecer tu nueva contraseña.
Confirmar correo electrónico
Información de la tienda
Política de reembolsos y devoluciones
Te informamos que tu pedido #%1$s ha sido cancelado.
Queremos informarte que tu pedido #%1$s ha sido cancelado.
Gracias por crear una cuenta en %1$s. Tu usuario es %2$s. Accede a tu cuenta para ver pedidos, cambiar tu contraseña y gestionar tus datos en: %3$s
Haz clic aquí para establecer tu nueva contraseña.
Recibimos tu pedido y está en espera hasta que confirmemos tu pago.
Recibimos tu solicitud de desistimiento del pedido que se muestra a continuación.
Recibimos tu pedido #%s y ya lo estamos procesando:
Tu pedido en %s se ha reembolsado parcialmente. Revisa los detalles a continuación:
Tu pedido en %s se ha reembolsado. Revisa los detalles a continuación:
Usuario: %s
Si no hiciste esta solicitud, ignora este correo. Si deseas continuar:
Haz clic aquí para restablecer tu contraseña
Dejar un comentario
Recibiste este mensaje porque tu correo se utilizó para solicitar avisos de stock en nuestra tienda.
Para gestionar tus avisos, %s e inicia sesión en tu cuenta.
Para dejar de recibir estos mensajes, %s y cancela la suscripción.
Este enlace estará activo durante %s.
Recibiste este mensaje porque tu correo se utilizó para solicitar avisos de stock. Si no fuiste tú y sigues recibiendo mensajes, contáctanos.
Datos del cliente
Descargas
Nunca
Por ahora no hay información de seguimiento para este envío.
Número de seguimiento
Empresa de transporte
Seguir tu envío
Consulta más detalles de tu pedido en <a href="%s" target="_blank">Mi cuenta > Pedidos</a> y selecciona el pedido que deseas revisar.
Resumen del envío
[Pedido #%s]
¡Gratis!
Nota:
Nota del cliente
Usuario: %s.
Para establecer tu contraseña, visita esta dirección: 
Dejar un comentario:
URL de seguimiento
Consulta los detalles de tu pedido en Mi cuenta > Pedidos y selecciona el pedido para ver el estado más reciente de la entrega.
[Pedido #%1$s] (%2$s)
Ver pedido: %s'''
}
mapping={}
for section,translated in translations.items():
    originals=list(audit['untranslated'][section]);values=translated.splitlines()
    assert len(originals)==len(values),(section,len(originals),len(values))
    mapping.update(zip(originals,values))
subjects={
"[{site_title}]: You've got a new order: #{order_number}":'[{site_title}]: Nuevo pedido: #{order_number}',
'[{site_title}]: Order #{order_number} has been cancelled':'[{site_title}]: Pedido #{order_number} cancelado',
'[{site_title}]: Your order #{order_number} has been cancelled':'[{site_title}]: Tu pedido #{order_number} ha sido cancelado',
'[{site_title}]: Order #{order_number} has failed':'[{site_title}]: Pedido #{order_number} fallido',
'Your order at {site_title} was unsuccessful':'No se pudo completar tu pedido en {site_title}',
'Your {site_title} order has been received!':'¡Recibimos tu pedido de {site_title}!',
'Your order from {site_title} is on its way!':'¡Tu pedido de {site_title} está en camino!',
'Your {site_title} order #{order_number} has been refunded':'Tu pedido #{order_number} de {site_title} se ha reembolsado',
'Details for order #{order_number} on {site_title}':'Detalles del pedido #{order_number} en {site_title}',
'A note has been added to your order from {site_title}':'Se añadió una nota a tu pedido de {site_title}',
'Reset your password for {site_title}':'Restablece tu contraseña de {site_title}',
'Your {site_title} account has been created!':'¡Se creó tu cuenta de {site_title}!',
'[{site_title}] Payment gateway "{gateway_title}" enabled':'[{site_title}] Método de pago «{gateway_title}» habilitado',
'Your in-store purchase #{order_number} at {site_title}':'Tu compra en tienda #{order_number} en {site_title}',
'Confirm your email address for {site_title}':'Confirma tu correo electrónico de {site_title}',
'New order':'Nuevo pedido','Cancelled order':'Pedido cancelado','Failed order':'Pedido fallido','Order received':'Pedido recibido','Processing order':'Pedido en proceso','Completed order':'Pedido completado','Refunded order':'Pedido reembolsado','Customer invoice / Order details':'Factura del cliente / Detalles del pedido','Customer note':'Nota del cliente','New account':'Nueva cuenta',
'Dashboard':'Escritorio','Orders':'Pedidos','Addresses':'Direcciones','Account details':'Datos de la cuenta','Logout':'Cerrar sesión','Log out':'Cerrar sesión','An error occurred while processing your request. Please try again.':'Ocurrió un error al procesar tu solicitud. Vuelve a intentarlo.',
'Please enter a valid email address.':'Ingresa un correo electrónico válido.','Please enter your password.':'Ingresa tu contraseña.','Username is required.':'El usuario es obligatorio.','Please enter your username or email address.':'Ingresa tu usuario o correo electrónico.','Invalid username or email.':'Usuario o correo electrónico no válido.','Please enter an account password.':'Ingresa una contraseña para tu cuenta.','Please provide a valid email address.':'Ingresa un correo electrónico válido.','Please enter a valid account username.':'Ingresa un nombre de usuario válido.',
'%1$s is not a valid postcode / ZIP.':'%1$s no es un código postal válido.','%s is not a valid postcode / ZIP.':'%s no es un código postal válido.','%s is not a valid phone number.':'%s no es un teléfono válido.','%s is not a valid email address.':'%s no es un correo electrónico válido.',
}
mapping.update(subjects)
for a,b in mapping.items():
    assert sorted(re.findall(r'%(?:\d+\$)?[sd]',a))==sorted(re.findall(r'%(?:\d+\$)?[sd]',b)),(a,b)
    assert sorted(re.findall(r'\{\w+\}',a))==sorted(re.findall(r'\{\w+\}',b)),(a,b)
(OUT/'minikids-language-v7.json').write_text(json.dumps(mapping,ensure_ascii=False,indent=2),encoding='utf8')
print('Prepared',len(mapping),'translations; printf/email placeholders match.')
