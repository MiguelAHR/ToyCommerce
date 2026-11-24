from pathlib import Path
import shutil
R=Path(__file__).resolve().parents[1];B=R.parent/'local-private/v12-backups'
state='''## V12 · 2026-11-24 · comentarios, tarjeta común y acceso; Chrome pendiente

- Solo localhost8082; aplicación mu-plugins por MCP backup=true/relectura, php -l por PHP y node --check. Comentarios fuera de columnas antes de recomendaciones, 40/60; Descripción/Detalles a izquierda. Tarjeta Woo común con tag rojo redondeado - N %, stepper/AGREGAR/SVG y AJAX; portada renderiza los enlaces editables de sus cinco carruseles como tarjetas Woo sin cambiar bloques guardados.
- Dialog invitado de acceso con fallback /mi-cuenta/ hacia página nativa /my-account/, vistas/login WP nonce+wp_signon, hooks Woo/errores genéricos/no-store/honeypot; registro nativo deshabilitado (no se cambió). Recuperación native retrieve_password implementada, no enviada ni probada con identificador; no usuario creado/login correcto probado. Sin botones sociales.
- Foto única de Blocksy cubierta por CSS contain/480/aspect-ratio, variantes Flexy; títulos de pago limpios, costos reales de zonas15/gratis149, plazo editable sin provisional público, nota de oferta tras botón. No certificar geometría ni foto2: no publicado con exactamente2 imágenes declaradas.
- HTTP83/83 dos pasadas; regresión V11 51/51 y VM13modales/9contador/7pasos/6watchdog.13732 protegidos idénticos,14pedidos/hash y opciones iguales;99 productos comerciales no editados, solo caché nativa Blocksy styles_descriptor vacía v8 generada al consultar458. INFORME_V12.md y datos/integridad-V12.json detallan límites. Sin navegador/pedidos/correo/despliegue ni cierre P0–P2; Claude verifica Chrome y éxito de login controlado.

'''
bugs='''### V12-FICHA-TARJETAS-ACCESO · P2 · En verificación por Claude
- Local: /wordpress/tienda/, portada, fichas, carrito y Mi cuenta. Comentarios movidos fuera de columnas por hook antes de recomendaciones; un renderer Woo para todos los bucles y cinco carruseles de portada; tag - N % real redondeado, cantidad y AGREGAR, sin controles duplicados.
- Acceso dialog invitado, mejora progresiva /mi-cuenta/, login WP/Woo con nonce/no-store/errores genéricos; registro oculto por opción nativa no. Recuperación implementada sin enviar solicitudes/correos; éxito de login/retorno de carrito y registro habilitado no ejercitados. Foto única CSS, pago limpio, datos de zonas/plazo editable, etiqueta bajo compra.
- HTTP83/83 dos pasadas; V11 51/51 y regresiones VM. Sin Chrome: medidas/encuadre2fotos/interacción/foco/primer pintado pendientes.13732 protegidos y14pedidos/hash iguales; solo caché nativa Blocksy458. Reproducción y límites en diseno_minikids/v5/INFORME_V12.md. No cerrado ni autocertificado.

'''
for relative,prefix in [('brain/STATE.md',state),('docs/bugs.md',bugs)]:
 p=R/relative;backup=B/(p.name+'.before')
 if not backup.exists():shutil.copy2(p,backup)
 old=p.read_text('utf8')
 if prefix.splitlines()[0] not in old:p.write_text(prefix+old,encoding='utf8')
print('STATE and bugs updated; report exists:',(R.parent/'diseno_minikids/v5/INFORME_V12.md').is_file(),flush=True)
