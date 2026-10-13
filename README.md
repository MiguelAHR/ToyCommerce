# ToyCommerce — MiniKids Perú

Tienda de juguetes sobre **WordPress + WooCommerce**: catálogo por categorías,
carrito, checkout, pedidos y métodos de pago peruanos (Culqi, Mercado Pago / Yape)
más PayPal.

Proyecto del curso **E-Business y Analítica Web**.

> 🚀 **Instalación rápida para el docente (2 minutos):** [`INSTRUCCIONES-INSTALACION.md`](INSTRUCCIONES-INSTALACION.md)  
> *Pega la carpeta `wordpress` en tu `htdocs` de XAMPP, importa `database/wordpress-sanitized.sql` en phpMyAdmin y ¡listo! Funciona automáticamente en cualquier puerto (80, 8080, 8088).*
> 
> 📘 **Guía completa del proyecto:** [`docs/GUIA-PROYECTO.md`](docs/GUIA-PROYECTO.md) | 🎨 **Manual de Diseño:** [`docs/MANUAL-DISENO-MINIKIDS.md`](docs/MANUAL-DISENO-MINIKIDS.md) | 📄 **Memoria Académica DOCX:** [`docs/DOCUMENTACION-PROYECTO-MINIKIDS.docx`](docs/DOCUMENTACION-PROYECTO-MINIKIDS.docx)

---

## Arquitectura

El sitio funciona con **WooCommerce 11.2.1**, que aporta catálogo, carrito, checkout y
pedidos. El diseño de MiniKids se aplica **encima** mediante hojas de estilo propias,
sin sustituir las plantillas del plugin.

El tema activo es **Blocksy 2.1.59** (tema clásico).

| Elemento | Dónde vive |
|---|---|
| Catálogo (7 productos, SKU, stock) | Base de datos (`wp_posts` tipo `product`) |
| Páginas de venta | Tienda, Carrito, Finalizar compra, Mi cuenta |
| Estética de tienda y ficha de producto | `wp-content/mu-plugins/minikids-estetica.css` |
| Correcciones del home y feedback del carrito | `wp-content/mu-plugins/minikids-bloques.css` |
| Carga de los estilos | `wp-content/mu-plugins/minikids-estetica.php` |
| Redirección de `/shop/` a `/tienda/` | `wp-content/mu-plugins/minikids-redirect-tienda.php` |
| Habilidades MCP propias | `wp-content/mu-plugins/minikids-mcp-abilities.php` |

---

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

**Categorías:** `peluches` · `didacticos` · `juegos-de-rol` · `vehiculos` ·
`creatividad` · `juegos-familiares` · `tecnologia`

---

## Configuración de la tienda

- **Moneda:** Nuevo sol peruano (PEN), formato `S/ 39.90`
- **País:** Perú
- **Envío:** zona Perú · gratis desde S/ 149 · tarifa plana S/ 15 por debajo
- **Impuestos:** no calculados
- **Compra como invitado:** activada
- **AJAX add to cart:** activado

### Métodos de pago

| Método | Estado | Requiere |
|---|---|---|
| Transferencia bancaria (`bacs`) | ✅ Activo | — |
| Pago contra entrega (`cod`) | ✅ Activo | — |
| PayPal (`ppcp-gateway`) | ✅ Activo en **sandbox** | Cuenta PayPal |
| Mercado Pago — tarjeta | ✅ Activo | Access Token |
| Mercado Pago — Yape | ✅ Activo | Access Token |
| Mercado Pago — básico / ticket | ✅ Activo | Access Token |
| **Culqi** (`fullculqi`) | ⚠️ Activo pero **sin claves** | `public_key` y `secret_key` |

> ⚠️ **Culqi aparece "observado"** porque la opción `woocommerce_fullculqi_settings`
> no contiene ninguna API key. Hay que pegarla desde el panel de Culqi.
> Ver `docs/GUIA-PROYECTO.md` §6.

Las credenciales de las pasarelas **no se versionan**: se introducen en
WooCommerce → Ajustes → Pagos.

---

## Contenido versionado

- `wordpress/wp-content/`: contenido desplegable (plugins, temas, idiomas, imágenes).
- `wordpress/wp-content/mu-plugins/`: estilos y ajustes propios de MiniKids.
- `wordpress/wp-config.example.php`: plantilla de configuración sin secretos.
- `database/wordpress-sanitized.sql`: base de datos completa (52 tablas) **saneada**.
- `docs/GUIA-PROYECTO.md`: guía de contexto y operación del proyecto.
- `docs/MANUAL-DISENO-MINIKIDS.md`: manual completo de diseño y sistema de diseño UI/UX.
- `docs/DOCUMENTACION-PROYECTO-MINIKIDS.docx`: memoria integral del proyecto en formato Word (.docx).
- `docs/bugs.md`: registro y seguimiento del ciclo de vida de bugs (QA).
- `docs/COMO-SUBIR-A-GITHUB.md`: guía de sincronización y subida a GitHub.
- `docs/PALETA-Y-OSITO.css`: estética de la primera versión, conservada como referencia.
- `tools/`: scripts auxiliares de instalación y `tools/verify_improvements.php` (suite de verificación).

No se incluye el core de WordPress ni un `wp-config.php` real.

### Qué se saneó en la base de datos

El volcado se genera con un script que elimina o anonimiza:

- ✅ Contraseñas de aplicación de WordPress
- ✅ Tokens de Mercado Pago (`_mp_access_token_prod`, `_mp_access_token_test`)
- ✅ Credenciales OAuth de PayPal
- ✅ Claves de Culqi
- ✅ Emails de clientes → `clienteN@example.com`
- ✅ Contraseña de admin → sustituida por una de demo

---

## Instalación rápida (Plug & Play)

> 💡 **Ver la guía visual detallada:** [`INSTRUCCIONES-INSTALACION.md`](INSTRUCCIONES-INSTALACION.md)

1. **Copiar archivos:** Copia la carpeta **`wordpress`** descargada del repositorio dentro de tu directorio `htdocs` de XAMPP (`C:\xampp\htdocs\wordpress`). *(El archivo `wp-config.php` ya viene preconfigurado con detección dinámica de puertos para que funcione en 80, 8080, 8088 sin tocar nada)*.
2. **Importar base de datos:** En phpMyAdmin (`http://localhost/phpmyadmin`), crea una base de datos llamada **`wordpress`** e importa el archivo `database/wordpress-sanitized.sql`.
3. **Abrir la tienda:** Ingresa a `http://localhost/wordpress/` (o si tu XAMPP corre en otro puerto: `http://localhost:8088/wordpress/`).

### Credenciales de acceso al Panel de Administración

```text
URL:        http://localhost/wordpress/wp-admin/
Usuario:    admin
Contraseña: MiniKids2026!
```

> 🔒 Cámbialas en cuanto importes, y vuelve a generar las contraseñas de aplicación.

### Extensiones de PHP necesarias

`gd`, `zip` e `intl` deben estar habilitadas en `php.ini`. Sin `zip` WordPress no
puede instalar plugins desde el panel; sin `gd` no se generan las miniaturas.

### URL de desarrollo

Con XAMPP en el puerto **8088**:

```
http://localhost:8088/wordpress/
```

---

## Edición de contenido

Todas las páginas son editables desde **WordPress → Páginas** y su contenido se
mantiene en **bloques nativos**:

- **Inicio:** barra de anuncio, hero, 7 tarjetas de categoría, banner de ofertas y
  4 beneficios. **No** lleva rejilla de productos: el catálogo vive en la tienda.
- **Tienda:** catálogo completo.
- **Carrito / Finalizar compra / Mi cuenta:** páginas de WooCommerce.

La ficha de producto **no se edita con bloques**: se controla desde
**Apariencia → Personalizar → WooCommerce → Producto individual** más CSS.

El tema es **clásico**, así que el menú y el pie se editan en
**Apariencia → Personalizar**, no en el Editor de bloques.

---

## Nota histórica

La primera versión del proyecto usaba un plugin propio (`minikids-pages`) con el
catálogo escrito en PHP y el carrito en `localStorage`. Se retiró al migrar a
WooCommerce, porque el carrito del navegador nunca llegaba al servidor y no permitía
pedidos ni pagos. La estética de aquella versión se conserva en `docs/PALETA-Y-OSITO.css`.
