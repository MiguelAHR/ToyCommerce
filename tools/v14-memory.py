from pathlib import Path
import shutil,json
R=Path(__file__).resolve().parents[1];B=R.parent/'local-private/v14-backups';O=R.parent/'diseno_minikids/v5'
for name in ['verificacion-V14-1.json','verificacion-V14-2.json','verificacion-V14-regresion-1.json','verificacion-V14-regresion-2.json']:
 d=json.loads((O/name).read_text('utf8'))
 checks=d.get('checks',d);assert all(v is True for v in checks.values()),name
state='''## V14 · 2026-11-24 · retoques locales; Chrome pendiente

- Retiradas estrellas doradas del pseudo de estado vacío en origen; promedio vacío conserva estrellas vacías y renderer nativo para promedio real. Títulos Comentarios/Escribe tu comentario comparten Barlow28, interlínea/márgenes y reinicio de posición del formulario; línea base pendiente de Chrome.
- Causa de barra ausente: dependencia V9 de V8 no registrada en carrito; ahora depende de V6 y carga su CSS. Mismo mk9_progress y CSS compartido bolsa/Resumen, pista6px gris, azul proporcional subtotal149 y completa al alcanzar umbral. Retirados overrides V13 de4px.
- Un filtro final de eliminación devuelve exactamente un enlace nativo por resultado; retirado filtro redundante. HTTP cuenta toda cart_item y prueba nonce/eliminar/total/progreso restante. Duplicado previo no reproducido en muestra local; no se oculta control adicional.
- Dos pasadas136/136 HTTP/HTML/declaraciones y dos regresiones51/51.13732 protegidos,14pedidos/hash,99productos y opciones auditadas iguales. MCP backup=true/relectura y PHP lint tras escritura; backups privados v14. Registro Woo sigue no, enlace oculto; NO login exitoso. Sin navegador/pedidos nuevos/correos/despliegue ni cierre P0–P2. INFORME_V14.md contiene reproducción y límites; modern-web-guidance offline ENOTCACHED.

'''
bugs='''### V14-COMENTARIOS-PROGRESO-ELIMINAR · P2 · En verificación por Claude
- Local: fichas, bolsa y carrito. Eliminado pseudo de cinco estrellas doradas, títulos con regla Barlow28 compartida. Corregida dependencia de V9 CSS ausente en carrito; componente común con pista6px y porcentaje real. Filtro único devuelve enlace eliminar nativo/SVG/aria; duplicado anterior no reproducido en muestra HTTP.
- Dos pasadas HTTP136/136 y dos regresiones51/51; eliminación nativa con nonce/total/progreso y componente idéntico bolsa/Resumen bajo/sobre149. Integridad13732 protegidos/14pedidos/99productos/opciones sin cambios. Registro no; login exitoso no probado. Ver diseno_minikids/v5/INFORME_V14.md.
- Estado: no cerrado. Chrome pendiente para barra visible, línea base, responsive, recorte, foco e interacción AJAX. Sin navegador, pedidos nuevos, correos ni despliegue.

'''
for rel,prefix in [('brain/STATE.md',state),('docs/bugs.md',bugs)]:
 p=R/rel;backup=B/(p.name+'.before')
 if not backup.exists():shutil.copy2(p,backup)
 s=p.read_text('utf8')
 if prefix.splitlines()[0] not in s:p.write_text(prefix+s,encoding='utf8')
print('V14 evidence checked; STATE and bugs updated')
