from pathlib import Path
import json,shutil,datetime
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5';PRIVATE=ROOT.parent/'local-private/v5-backups'
v=json.loads((OUT/'verificacion-P.json').read_text('utf8'));assert all(v['checks'].values())
assert (OUT/'INFORME_P.md').exists()and(OUT/'verificacion-http-P.json').exists()
stamp=datetime.datetime.now().strftime('%Y%m%d-%H%M%S')
for rel in ['brain/STATE.md','docs/bugs.md','brain/graphify-out/graph.json','brain/graphify-out/graph.html']:
    src=ROOT/rel
    if src.exists():shutil.copy2(src,PRIVATE/(src.name+'.before-P-state-'+stamp))
state=ROOT/'brain/STATE.md';old=state.read_text('utf8');heading,sep,rest=old.partition('\n')
entry='''
## V5 · Fases A y P locales · catálogo demo de 98 publicados

- Destino actual verificado por wp-load: base local `minikids_t3_revision_20261111`, conexión loopback, home `http://localhost:8082/wordpress/`. El nombre minikeds_dev2 de las entradas históricas no identifica esta copia de revisión. No se renombró ni migró ninguna base.
- A medida por curl/inventario, con límites explícitos: tienda HTML <3 s, menor peso de recursos, estáticos rápidos; PHP todavía >1 s. Router rápido activo; Apache multihilo preparado pero sin sustituir el listener por restricciones del entorno. Sin Chrome/Playwright, sin certificación visual/editor/CWV.
- P: 81 nuevos mediante CRUD WooCommerce, SKU MK-018 a MK-098 y meta _mk_demo=1. Total 98 publicados, 14 por cada categoría existente, 35 ofertas totales, 20 nuevas galerías de 2 imágenes. Los 17 publicados anteriores y el borrador 212 conservan sus campos. Otros 22 medios de galería recuperaron derivados ausentes, con originales SHA-256 intactos.
- 121 imágenes demo seleccionadas (81 principales + 40 galerías), PNG con alfa y variantes 1200/600, WebP calidad 82; hojas de contacto sobre blanco/oscuro. Prompts, fuentes y lista de IDs/URLs en diseno_minikids/v5. Producto físico, edades, peso, medidas y cotizaciones no certificados: datos demo señalados, pendientes del dueño. No sustituir las siete fotos heredadas incongruentes sin fuente confirmada; personaje heredado ID 85 conservado por la orden vigente.
- Nuevo filtro/checkbox de Productos en minikids-demo-catalogo.php, aplicado/releído por MCP con backup=true. Consulta nativa de 81 demo / 17 originales; dos pasadas CRUD/HTTP e imágenes decodificadas; Ajax agregar/retirar en sesión aislada. No se solicitó creación de pedidos ni envío de correo. No SQL ni cambios de terceros/pasarelas.
- INFORME_A.md, INFORME_P.md, catalogo_demo.json y catalogo_contacto.jpg son la entrega local. Respaldos y journal fuera de WordPress en local-private/v5-backups. No despliegue. B–E no iniciadas.

'''
state.write_text(heading+'\n'+entry+rest,encoding='utf8')
bugs=ROOT/'docs/bugs.md';old=bugs.read_text('utf8');heading,sep,rest=old.partition('\n')
entry='''
## V5 · Catálogo demo y revisión ampliada de galerías

### BUG-V5-P-GAL · P2 · Estado: reparación técnica comprobada; identidad comercial pendiente
- Inventario completo de los 17 publicados detectó 35 URLs de galería ausentes más allá de las tres páginas de muestra de A.
- Corrección: 22 medios regenerados con APIs nativas, sin cambiar imágenes asignadas; originales SHA-256 conservados. Respaldos privados antes de metadata/derivados.
- Evidencia: reparacion-galerias-ampliada.json y dos pasadas de verificacion-http-P.json, con todas las imágenes inventariadas 200 y decodificadas.
- Las fotos heredadas siguen requiriendo confirmación de identidad/SKU por el dueño; no se sustituyeron por fotos de otros juguetes.

### QA-V5-P · Estado: verificación técnica completada; revisión visual/comercial pendiente
- 98 publicados, 81 demo identificados, 14 por categoría, 35 ofertas totales; 20 nuevas galerías con 2 fotos extra. 121 imágenes fuente con alfa y hashes distintos, variantes 1200/600 y WebP calidad 82; hojas para Claude.
- Dos pasadas CRUD y HTTP/Store API; filtro admin probado por WP_Query nativo; prueba Ajax aislada sin pedido. Los 17 publicados y el borrador 212 se conservaron.
- Precios, edades, contenidos, pesos y medidas son demo, sin verificación de proveedor/mercado. Validar antes de venta/despliegue. No se certifica navegador, responsive, bordes perfectos ni editor Gutenberg; Chrome/Playwright prohibidos por el usuario.
- Evidencias: INFORME_P.md y verificacion-P*.json, verificacion-http-P.json, verificacion-admin-P.json, imagenes_auditoria.json.
- B–E fuera de alcance; sin despliegue ni cambios de pasarelas.

'''
bugs.write_text(heading+'\n'+entry+rest,encoding='utf8')
coord=OUT/'COORDINACION_P.md'
if coord.exists():shutil.copy2(coord,PRIVATE/('COORDINACION_P.before-complete-'+stamp+'.md'))
coord.write_text('''FASE P implementada y verificada técnicamente en local.

- 98 publicados: 17 anteriores + 81 demo, SKU MK-018 a MK-098.
- 121 imágenes seleccionadas: 81 principales + 40 galerías; fuentes y prompts en imagenes/.
- No hay importación P pendiente. No relanzar generadores ni mutaciones para repetir verificaciones de lectura.
- INFORME_A.md e INFORME_P.md documentan mediciones, evidencias y decisiones del dueño pendientes.
- Fases B–E no ejecutadas. No se desplegó.
''',encoding='utf8')
print('STATE and bugs updated with backups; regenerate graph separately')
