# MiniKids · Portada comercial editable

La versión local utiliza una cabecera compacta con buscador, categorías, pedidos, cuenta y carrito; un carrusel panorámico de tres campañas; tres promociones horizontales y dos selecciones de productos. Conserva el logo y la paleta amarilla, azul y roja de MiniKids. La referencia de organización comercial es https://www.tailoy.com.pe/; las imágenes promocionales son nuevas y propias de esta propuesta.

En escritorio de 1440 px se contrastaron las proporciones con la referencia: aviso de 46 px, cabecera de 75 px, hero de borde a borde de aproximadamente 372 px, 70 px de separación antes de las promociones y tres piezas de 152 px de alto dentro de un ancho de 1216 px. Los títulos de sección utilizan Rajdhani a 40 px y el texto utiliza Roboto, con archivos locales y sus licencias. La versión móvil adapta los tamaños y apila las promociones.

## Edición manual

En **Apariencia → MiniKids · Diseño** están reunidos los accesos de mantenimiento:

- **Portada:** abre Páginas → Inicio. Las tres campañas del carrusel son bloques Portada dentro del grupo de campañas, con títulos, párrafos y botones dentro. Para cambiar su fondo, selecciona el bloque Portada y sustituye la imagen desde Medios. Para cambiar el orden, mueve sus bloques desde la Vista de lista. El texto no forma parte de la imagen. Las tres promociones inferiores son grupos con bloques Imagen, Encabezado y Botón.
- **Pie de página:** abre el bloque sincronizado «MiniKids · Pie de la tienda». Sus columnas, enlaces, horarios e imágenes son bloques nativos.
- **Menú principal:** utiliza el menú existente de WordPress, en Apariencia → Menús.
- **Aviso y buscador de cabecera:** se editan en Apariencia → Personalizar → MiniKids · Cabecera de tienda.
- **Productos, precios e inventario:** se mantienen desde Productos en WooCommerce. Las tarjetas leen esos valores del catálogo, sin precios duplicados en la portada.
- **Logo:** si se configura un logo en la identidad del sitio, la cabecera lo utiliza. Mientras tanto conserva el archivo original de MiniKids.

Las selecciones de productos están contenidas en bloques Código corto de WooCommerce. En la portada local se muestran cuatro productos con oferta y cinco productos adicionales. Los identificadores de esos códigos cortos controlan la selección; el nombre, la imagen, las existencias y el precio se editan desde el producto correspondiente.

## Implementación

Los archivos propios son `mu-plugins/minikids-retail.php`, `minikids-retail.css` y `minikids-retail.js`. Se escribieron mediante las abilities MCP `read-file` y `write-file`, con respaldo activado. La portada y el pie se consultaron, actualizaron y releyeron mediante `get-content` y `update-content`.

Las imágenes se registraron mediante la API nativa de Medios. La creación inicial del bloque del pie se realizó mediante la API nativa de WordPress y sus ediciones posteriores mediante MCP. El contenido se serializó con el propio Gutenberg, conservando comentarios de bloques y posibilidad de edición visual. No se editaron WordPress base, Blocksy ni WooCommerce.

La cabecera conserva la navegación nativa y los fragmentos del carrito de WooCommerce. El buscador recibe la URL del catálogo real `/tienda/`, evitando la ruta heredada `/shop/` que perdía el filtro.

## Alcance y comprobación local

- Dos pasadas sobre 360, 390, 768, 1024, 1280 y 1440 píxeles: sin desbordamiento horizontal y sin imágenes rotas en la nueva portada.
- En cada pasada: controles e indicadores del carrusel con botones y teclado, apertura/cierre de categorías, búsqueda desde la cabecera, añadido al carrito, contador y apertura del formulario de compra con el producto conservado. El carrusel permite desplazamiento táctil, no avanza automáticamente y comunica la campaña seleccionada a los lectores de pantalla.
- Portada y pie abiertos en Gutenberg: sin bloques inválidos.
- PHP propio comprobado con el analizador de sintaxis.
- Las pruebas no enviaron pedidos, pagos, mensajes ni correos.

La base de trabajo es la copia local de revisión. El juego de memoria mantiene su referencia original; se excluyó del escaparate porque esa copia no contiene su fotografía correcta. No se sustituyó por una fotografía de otro juguete. Esto no cambia su registro comercial.

## Publicación

Esta implementación está aplicada solo en local. Para llevarla al sitio publicado deben respaldarse allí la portada y el pie, incorporar los medios nuevos y ajustar los identificadores y direcciones de los bloques al entorno de destino. Se deben transferir solo el diseño y el contenido acordados; no se debe importar la base local sobre pedidos de producción ni copiar `wp-config.php`, credenciales o configuración privada.

Las fuentes se alojan en `wp-content/uploads/minikids/fonts/`: `roboto-variable.ttf` y `rajdhani-bold.ttf`. Deben conservar esa ruta al transferir el diseño. La entrega incluye sus archivos y licencias.
