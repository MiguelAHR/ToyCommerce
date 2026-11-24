from pathlib import Path
import json,hashlib,subprocess,shutil
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v11-backups';MU=(R/'wordpress/wp-content/mu-plugins').resolve()
subprocess.run(['C:/xampp/php/php.exe',str(B/'native-snapshot.php'),'after'],check=True)
a=json.loads((B/'native-before.json').read_text('utf8'));b=json.loads((B/'native-after.json').read_text('utf8'))
assert a['orders']==b['orders'] and a['options']==b['options']
changes=[]
for pid,old in a['products'].items():
 new=b['products'][pid]
 if old==new:continue
 diff=[k for k,v in old['data'].items() if v!=new['data'][k]]
 assert diff==['meta_data'] and old['attributes']==new['attributes'] and old['care']==new['care']
 added=[m for m in new['data']['meta_data'] if m not in old['data']['meta_data']]
 assert added and all(m['key']=='blocksy_post_meta_options' and set(m['value'])=={'styles_descriptor'} for m in added)
 assert all(m in new['data']['meta_data'] for m in old['data']['meta_data'])
 changes.append({'id':int(pid),'generated_cache':added})
protected=json.loads((B/'protected-before.json').read_text('utf8'));diff=[p for p,v in protected.items() if not (R/p).is_file() or hashlib.sha256((R/p).read_bytes()).hexdigest()!=v];assert not diff
names=['minikids-commerce-v5.php','minikids-commerce-v5.js','minikids-commerce-v6.php','minikids-commerce-v6.js','minikids-commerce-v9.php','minikids-commerce-v9.js','minikids-commerce-v9.css']
lint=[]
for name in names:
 path=MU/name
 if path.suffix not in ('.php','.js'):continue
 command=['C:/xampp/php/php.exe','-l',str(path)] if path.suffix=='.php' else ['node','--check',str(path)]
 result=subprocess.run(command,capture_output=True,text=True,check=True)
 lint.append({'file':name,'exit_code':result.returncode,'output':result.stdout.strip(),'sha256':hashlib.sha256(path.read_bytes()).hexdigest()})
receipts=json.loads((O/'aplicacion-V11.json').read_text('utf8'))
for receipt in receipts:
 name=receipt.get('backup')
 if not name:continue
 src=(MU/name).resolve();dest=(B/name).resolve()
 assert src.parent==MU and dest.parent==B.resolve() and '.bak-' in name
 if src.exists():
  assert not dest.exists();shutil.move(str(src),str(dest))
integrity={'orders_identical':True,'orders_count':len(b['orders']),'hpos_mail_gateway_options_identical':True,'products_count':len(b['products']),'all_product_data_except_generated_style_cache_identical':True,'generated_style_cache':changes,'protected_count':len(protected),'protected_differences':diff,'lint':lint,'application_files':names,'scope':'Native read-only snapshots and hashes. No product save, order creation, mail or browser.'}
(O/'integridad-V11.json').write_text(json.dumps(integrity,ensure_ascii=False,indent=2),encoding='utf8')
for number in (1,2):
 checks=json.loads((O/f'verificacion-V11-{number}.json').read_text('utf8'));assert checks['passed']==checks['total']==52
report='''# MiniKids V11 · Cambios locales; verificación visual pendiente

24 de noviembre de 2026. Destino exclusivo: `http://localhost:8082/wordpress/`. Implementados los puntos 1–3 de la orden V11 y preparada la comprobación del punto 4 para Claude en Chrome real. No se certifican alturas, primer pintado ni comportamiento visual/interactivo sin navegador. No se usaron Chrome, Playwright ni otro navegador; no hubo despliegue, creación de pedidos ni envío de correos.

## 1. Mi bolsa

La evidencia HTTP inicial mostró la causa de estructura: Blocksy resolvía `cart/mini-cart.php` a su plantilla, con dos enlaces (imagen y título) y un contenedor `.product-data` para nombre, precio y cantidad. El CSS V9 esperaba otra estructura y aplicaba padding izquierdo y posicionamiento absoluto. No era suficiente cambiar únicamente el ancho de las columnas.

El mu-plugin selecciona ahora la plantilla distribuida nativa de WooCommerce mediante `woocommerce_locate_template`, sin copiarla ni editar WooCommerce o Blocksy. Se conservan `.widget_shopping_cart_content`, enlaces de retirada, nonces, endpoint de cantidad y fragmentos/eventos Ajax. Un filtro de nombre limitado al render del mini-carrito añade el span que permite truncar el nombre.

La fila define tres columnas: `72px minmax(0,1fr) 100px`. El enlace nativo de imagen/nombre usa una cuadrícula anidada en las dos primeras; la imagen ocupa 72 px y dos filas, el nombre tiene `min-width:0` y line-clamp de dos líneas. El precio ocupa la segunda columna debajo del nombre; papelera arriba y cantidad debajo en la tercera, alineadas a la derecha. Hay separador fino y reglas específicas que anulan el padding/posicionamiento heredados, también en móvil. No se cambió la composición del pie fijo V9; su aviso comparte las correcciones de caja compacta.

HTTP comprobó estructura con 1, 2 y 5 productos de nombres largos reales. Entre ellos: Tren Expreso de Madera con Vagones Magnéticos. Se comprobó también el fragmento devuelto tras cambiar cantidad. Esto no verifica el recorte visual, clics, scroll ni la posición computada.

## 2. Carrito

- Envío gratis: flex explícito, SVG fijo de 24 px a la izquierda, texto alineado a la izquierda, progreso fino, fondo gris y radio de 8 px. Altura declarada de 64 px con padding incluido. Se refuerza la especificidad frente a reglas heredadas de tablas.
- Resumen: paddings y márgenes reducidos; cupón en una fila flexible con campo y botón «Aplicar». La fila nativa de envío usa una celda de ancho completo y presenta método/precio, destino abreviado y «Cambiar dirección» seguidos. Se conservan métodos reales, campos y calculador nativo; al expandirlo puede crecer la tarjeta. No se inventan destinos ni cotizaciones si falta información.
- Hueco: «Vaciar carrito de compra» está inmediatamente después de la tabla, por el hook nativo `woocommerce_after_cart_table`. Los relacionados continúan en esa misma columna izquierda; así la altura de Resumen no obliga a dejar toda esa columna vacía. En escritorio el carrusel tiene tres tarjetas; en móvil, dos. El Resumen permanece en la columna derecha y ambos siguen el apilado móvil existente.
- Una sola papelera SVG por enlace de retirada, con `aria-label="Eliminar"`; se anulan ambos pseudoelementos heredados. Se preservan URL, nonce y atributos Woo.
- Relacionados: sección, envoltorio Woo y carrusel comparten el borde izquierdo/ancho disponible, sin segundo inset. Etiqueta de porcentaje «- N %», pequeña, en esquina; no franja OFERTA. El guion se emite como entidad para evitar su transformación tipográfica por WordPress.
- Vaciado delegado en document y reinicialización del carrusel tras los eventos de actualización nativos, porque Woo reemplaza el formulario del carrito por Ajax. Se conserva Store API para retirar artículos. No se ha ejecutado el vaciado mediante clic en navegador.

Metas de Resumen ≤520 px y hueco bajo la tabla ≤120 px: implementadas las medidas y estructura para alcanzarlas en el estado predeterminado con un producto, pero **no medidas ni certificadas**. No se recorta la tarjeta ni se ocultan errores para forzar una altura. Dirección expandida, errores o contenido largo pueden requerir más altura.

## 3. Checkout seguro

El servidor sigue bloqueando todo el formulario antes de `mk-secure-ready`, con CSS crítico en head. El failsafe contiene primero dos tarjetas grises neutras. El mensaje de fallo empieza con `visibility:hidden` y animación CSS discreta retrasada 4 s; esta ruta permite mostrar el aviso incluso si falta todo el JS, sin descubrir el formulario. Las reglas de animación críticas prevalecen sobre el reset global de reduced motion; no hay movimiento decorativo.

El JS añade `mk-secure-failed` al terminar el plazo si falta ready, mediante timeout y evento de la animación testigo. El timeout descuenta el tiempo transcurrido desde el inicio de la respuesta. Si la verificación de servidor confirma la ventana, retira failed y añade ready; el failsafe desaparece. Un vencimiento real conserva su aviso inmediato y retorno al carrito. No se cambió el límite de servidor, validación de pedidos, tokens ni lógica de pasarelas; no se añadió recarga de entrada.

Candado SVG decorativo antes de «Compra segura». «Resumen de tu bolsa» tiene fondo blanco, borde, radio de 12 px y sombra suave sobre el fondo gris. Se mantiene el resumen colapsable móvil.

El pie y las etiquetas públicas de los medios de pago no muestran «(prueba local)». Se aplica una limpieza de presentación mediante filtro nativo de título, limitada al checkout público; las opciones guardadas, instrucciones y procesamiento de las pasarelas no se modifican. El entorno continúa siendo local de pruebas, aunque no se rotule en el escaparate.

## 4. Verificaciones realizadas

- Dos pasadas finales HTTP/DOM: **52/52 cada una**. Bolsa 1/2/5, nombres/imagen/controles, pie, papelera única, ubicación de vaciado/relacionados, etiquetas, envío/cupón, CSS crítico/esqueleto/aviso retrasado, candado, títulos públicos, cabeceras no-store/Cookie, status nativo y fragmento de cantidad. También estructura V10 en Ábaco, MK-095 y Set de circuitos inicial.
- Watchdog real ejecutado en VM sintética: **6/6**. No marca fallo antes del timeout; marca si no hay ready, lo suprime si ya lo hay, descuenta el tiempo de respuesta y atiende solo su animación testigo. No mide primer pintado ni ejecuta CSS.
- Regresión sintética reutilizada con nombres nuevos: **13 casos de modales V10, 9 de contador/estado seguro y 7 de pasos**. Sin certificar interacción, navegación, accesibilidad o renderizado reales.
- Cada escritura de PHP tuvo `php -l` antes de instalar y después en el PHP instalado. JS pasó `node --check`. Relectura exacta tras cada escritura MCP con `backup=true`. Lint final registrado en `integridad-V11.json`.
- Mismos 14 pedidos y hashes; HPOS y opciones de correo/pasarelas iguales. Datos de los 99 productos conservados salvo caché nativa de estilos generada por Blocksy al consultar HTTP el producto 430: `blocksy_post_meta_options.styles_descriptor`, estilos vacíos y versión 8. No se guardaron productos ni se borró esa caché. Comparación de 13.732 archivos protegidos sin diferencias.

Evidencias: `aplicacion-V11.json`, `verificacion-V11-1.json`, `verificacion-V11-2.json`, `fallo-checks-V11.json`, `modales-checks-V11-regresion.json`, `timer-checks-V11-regresion.json`, `pasos-checks-V11-regresion.json`, `integridad-V11.json` y HTML `V11-*`. El primer pase de desarrollo detectó la plantilla distinta y el guion transformado; se corrigieron antes de las dos pasadas finales.

## Reproducción para Claude en Chrome real

1. Usar únicamente `http://localhost:8082/wordpress/`, sesión de pruebas. En 360/390/768/1024/1280/1440 px, añadir 1, 2 y 5 productos distintos, incluidos nombres largos. Abrir Mi bolsa: medir 72 px de imagen, nombre máximo dos líneas, precio y controles; comprobar separadores y ausencia de desbordamiento. Cambiar cantidades y retirar un artículo; verificar actualización nativa de subtotal/progreso, pie fijo, scroll solo de lista y enlaces Comprar/Ver carrito.
2. Abrir `/cart/` con un producto. Medir altura de Resumen predeterminado (meta ≤520 px), hueco tras tabla (meta ≤120 px) y caja de envío gratis (≤64 px). Comprobar fila de envío, cupón campo/botón en una línea, papelera única, Vaciar inmediatamente bajo tabla y relacionados alineados en la columna izquierda. Expandir/cerrar Cambiar dirección y recalcular sin perder controles. Probar cupón inválido sin crear uno. Actualizar cantidad por Ajax y repetir carrusel/vaciado en sesión desechable; verificar que sus controles siguen funcionando.
3. En relacionados con oferta, confirmar etiqueta pequeña «- N %», sin franja OFERTA, también en móvil. Verificar que los productos continúan siendo enlaces/carrito nativos dentro del formulario y no provocan envíos accidentales del formulario.
4. Ir a `/checkout/` con bolsa llena y observar primer pintado/150 ms con navegación normal y red lenta: solo cabecera mínima/esqueleto hasta ready, ninguna aparición del formulario sin controlar ni fallo temprano. Sin ready durante unos 4 s, debe verse el aviso y Volver al carrito. Repetir bloqueando el módulo y luego todos los JS; el formulario debe seguir oculto. Restaurar recursos. Verificar también reduced motion, navegación única, vuelta con Atrás y fallo de red del status sin acceso al formulario.
5. Confirmar candado antes de Compra segura, contador a la derecha, ausencia del sufijo en pie y nombres de pago, sombra/radio del resumen sobre gris y colapso móvil. Continuar pasos con datos de prueba sin pulsar Realizar pedido ni enviar nada. El vencimiento puede probarse con una ventana nueva `?mk6_ttl=15` en sesión local aislada; volver al carrito para comenzar otra.
6. Regresión V10: cuatro modales en Ábaco, X/Esc/backdrop/foco y después de Ajax/Atrás; una miga y un Comentarios, precio normal y oferta MK-095, disponibilidad amarilla, compra azul y caja de enlaces SVG. No enviar reseñas, pedidos ni correos.

## Archivos y límites

Código de aplicación modificado solo en `ToyCommerce/wordpress/wp-content/mu-plugins/`:

- `minikids-commerce-v5.php` y `.js`: ubicación de herramientas/relacionados y vaciado delegado.
- `minikids-commerce-v6.php` y `.js`: candado, texto Aplicar e inicialización de carrusel tras Ajax.
- `minikids-commerce-v9.php`, `.js` y `.css`: plantilla nativa de mini-carrito, nombres/papeleras/etiquetas, envío compacto, estado inicial seguro/aviso diferido, títulos públicos y presentación de las tarjetas.

Se conservaron handles, dependencias y versiones `filemtime`. Originales y backups automáticos MCP están en `_trabajo/local-private/v11-backups`, fuera de WordPress/Git/grafo. Los espejos y auxiliares `ToyCommerce/tools/v11-*` son material de desarrollo. Se actualizan `brain/STATE.md`, `docs/bugs.md` y Graphify con el wrapper secuencial existente. El paquete modern-web-guidance no estaba disponible en caché offline (`ENOTCACHED`); no se descargó software.

No verificado aquí: geometría/alturas/colores computados, line-clamp visual, primer pintado sin parpadeo, responsive, consola, tacto/teclado/lectores de pantalla, clics Ajax, BFCache o ausencia de regresión visual. Esas comprobaciones quedan para Claude en Chrome real; no hay cierre global P0–P2 ni autocertificación. Core, tema, WooCommerce y pasarelas permanecen sin editar; no se modificaron pedidos ni se envió correo. Los pendientes previos de foto 41 y Brevo no se reabrieron ni se declaran resueltos.
'''
(O/'INFORME_V11.md').write_text(report,encoding='utf8')
state='''## V11 · 2026-11-24 · bolsa, carrito y primer estado de checkout; Chrome pendiente

- Solo mu-plugins propios por MCP backup=true/relectura y php -l tras cada PHP. Bolsa usa selección de plantilla nativa Woo (Blocksy añadía product-data y rompía el grid esperado), fila72/texto flexible/controles100, nombre2 líneas. Papelera única accesible y pie conservado.
- Carrito: envío/progreso compactos, cupón en fila, resumen con menos padding; vaciado/relacionados tras tabla en columna izquierda para ocupar el hueco, porcentajes pequeños; vaciado delegado y carrusel reinicializado tras Ajax. Metas520/120/64px implementadas, no medidas.
- Checkout: esqueleto neutro, fallo CSS tras4s y clase failed por watchdog JS si falta ready; CSS crítico sigue cerrado. Candado, sombra/radio12 del resumen, sufijo prueba local retirado solo de presentación pública, sin opciones/pasarelas editadas.
- Dos pasadas HTTP52/52, watchdogVM6, regresiones VM13modales/9contador/7pasos.99 productos comerciales y14 pedidos/hash intactos, salvo caché nativa Blocksy de estilos del430 al consultar HTTP;13732 protegidos sin diferencias. INFORME_V11.md contiene reproducción y límites. Sin navegador/pedidos/correo/despliegue ni cierre P0–P2; Claude debe verificar en Chrome.

'''
bugs='''### V11-BOLSA-CARRITO · P2 · En verificación por Claude
- URL local: /wordpress/cart/ y drawer Mi bolsa; 1/2/5 productos largos, seis anchos. Causa comprobada por HTTP: plantilla mini-cart de Blocksy con product-data no corresponde al CSS V9. Selección nativa Woo desde mu-plugin y grid72/flexible/100; papeleras/pseudoelementos unificados.
- Aviso/cupón/envío/Resumen compactos; vaciado y relacionados tras tabla en izquierda; carrusel sin inset y etiquetas porcentuales. Reinit/delegación tras Ajax. Dos pasadas52/52 HTTP/DOM y datos/integridad; no medición de metas520/120/64px ni interacción real. INFORME_V11.md. Pendiente Chrome; no cerrado.

### V11-CHECKOUT · P2 · En verificación por Claude
- URL local: /wordpress/checkout/. Failsafe antes visible de inmediato; ahora esqueleto, fallo diferido4s CSS+JS si falta ready, formulario sigue bloqueado. SVG candado, resumen con sombra/radio12 y sufijo prueba local retirado de títulos públicos sin editar opciones de pasarelas.
- Watchdog6VM, regresión13modales/9contador/7pasos y HTTP52/52 dos pasadas. Primer pintado, no-JS visual, reduced motion, consola/Ajax/BFCache y seis anchos pendientes de Claude.14 pedidos/hash y opciones iguales,13732 protegidos intactos; sin pedidos/correos/navegador. No cerrado.

'''
for relative,prefix in [('brain/STATE.md',state),('docs/bugs.md',bugs)]:
 p=R/relative;backup=B/(p.name+'.before')
 if not backup.exists():shutil.copy2(p,backup)
 old=p.read_text('utf8')
 if prefix.splitlines()[0] not in old:p.write_text(prefix+old,encoding='utf8')
print(json.dumps({'report':str(O/'INFORME_V11.md'),'protected_count':len(protected),'orders':len(b['orders']),'generated_style_cache_ids':[x['id'] for x in changes],'linted_files':len(lint)},ensure_ascii=True))
