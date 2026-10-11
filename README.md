# ToyCommerce

Tienda **MiniKids Perú** sobre WordPress + **WooCommerce**: catálogo de juguetes, carrito en servidor, checkout, pedidos y métodos de pago.

## Arquitectura

El sitio funciona con **WooCommerce 11.2.1**, que aporta el catálogo, el carrito, el checkout y los pedidos. La estética propia de MiniKids (paleta, tipografías y la ilustración del osito) se aplica **encima** mediante hojas de estilo, sin sustituir las plantillas del plugin.

| Elemento | Dónde vive |
|---|---|
| Catálogo (7 productos, SKU, stock) | Base de datos de WordPress (`wp_posts` tipo `product`) |
| Páginas de venta | Tienda, Carrito, Finalizar compra, Mi cuenta |
| Estética MiniKids | `wp-content/mu-plugins/minikids-estetica.css` |
| Redirección de la tienda | `wp-content/mu-plugins/minikids-redirect-tienda.php` |
| Pie de página | `wp-content/themes/twentytwentyfive/parts/footer.html` |

## Catálogo

| Producto | SKU | Precio | Antes | Stock |
|---|---|---|---|---|
| Peluche osito abrazable | MK-PEL-001 | S/ 39.90 | S/ 49.90 | 15 |
| Bloques creativos 60 piezas | MK-DID-001 | S/ 29.90 | S/ 39.90 | 12 |
| Set de cocina mini chef | MK-ROL-001 | S/ 27.90 | S/ 34.90 | 12 |
| Pista de carreras turbo | MK-VEH-001 | S/ 44.90 | S/ 54.90 | 10 |
| Kit de arte pequeños artistas | MK-CRE-001 | S/ 20.90 | S/ 24.90 | 10 |
| Juego de memoria animalitos | MK-FAM-001 | S/ 12.90 | S/ 19.90 | 10 |
| Mini karaoke con micrófono | MK-TEC-001 | S/ 59.90 | S/ 69.90 | 10 |

## Configuración de la tienda

- **Moneda:** Nuevo sol peruano (PEN), formato `S/ 39.90`
- **País:** Perú
- **Envío:** zona Perú · gratis desde S/ 149 · tarifa plana S/ 15 por debajo
- **Impuestos:** no calculados

### Métodos de pago

| Método | Estado | Requiere |
|---|---|---|
| Transferencia bancaria | Activo | — |
| Pago contra entrega | Activo | — |
| PayPal | Configurado en **sandbox** | Cuenta PayPal |
| Mercado Pago (tarjeta) | Instalado | Access Token |
| Yape | Instalado | Access Token de Mercado Pago |

Las credenciales de las pasarelas **no se versionan**: se introducen en WooCommerce → Ajustes → Pagos.

## Contenido versionado

- `wordpress/wp-content/`: contenido desplegable de WordPress (plugins, temas, idiomas, imágenes).
- `wordpress/wp-content/mu-plugins/`: estilos y ajustes propios de MiniKids.
- `wordpress/wp-config.example.php`: plantilla de configuración sin secretos.
- `database/wordpress-sanitized.sql`: estructura y contenido de la base de datos (52 tablas), excluyendo usuarios, hashes y claves.
- `tools/`: scripts auxiliares de instalación.

No se incluye el core de WordPress ni un `wp-config.php` real.

## Instalación

1. Crear la base de datos y cargar `database/wordpress-sanitized.sql`.
2. Copiar `wordpress/wp-config.example.php` como `wp-config.php` y definir credenciales y salts.
3. Copiar `wordpress/wp-content/` a la instalación de WordPress.
4. Activar los plugins: **WooCommerce**, **WordPress MCP**, **MCP Adapter** y, si se usan, **Mercado Pago** y **PayPal Payments**.
5. Verificar en WooCommerce → Ajustes que la moneda sea **PEN** y el país **Perú**.
6. Introducir las credenciales de las pasarelas de pago.

### Extensiones de PHP necesarias

`gd`, `zip` e `intl` deben estar habilitadas en `php.ini`. Sin `zip` WordPress no puede instalar plugins desde el panel; sin `gd` no se generan las miniaturas de las imágenes de producto.

## Edición de contenido

Todas las páginas son editables desde **WordPress → Páginas**:

- **Inicio:** portada con el osito y los productos destacados.
- **Tienda:** catálogo completo mediante el shortcode `[products]`.
- **Carrito / Finalizar compra / Mi cuenta:** páginas de WooCommerce.

El menú y el pie se editan desde **Apariencia → Editor**.

## Nota histórica

La primera versión del proyecto usaba un plugin propio (`minikids-pages`) con el catálogo escrito en PHP y el carrito en `localStorage`. Se retiró al migrar a WooCommerce, porque el carrito del navegador nunca llegaba al servidor y no permitía pedidos ni pagos. La estética de aquella versión se conserva en `PALETA-Y-OSITO.css`.
