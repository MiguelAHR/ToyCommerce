# MiniKids Perú — Guía de contexto y operación del proyecto

> Documento maestro para retomar el proyecto sin perder contexto.
> Curso: **E-Business y Analítica Web**. Última actualización: ver historial Git.

---

## 1. Qué es el proyecto

**MiniKids Perú** — tienda online de juguetes. Proyecto académico construido sobre
WordPress + WooCommerce en un XAMPP local, con pasarelas de pago peruanas reales
(Culqi, Mercado Pago / Yape) y PayPal en sandbox.

**Objetivo de negocio:** catálogo de juguetes por categoría, carrito, checkout con
pago en línea y transferencia, y una portada que comunique la marca.

---

## 2. Stack técnico

| Componente | Versión | Notas |
|---|---|---|
| XAMPP | portable en `E:\xampp` | |
| Apache | puerto **8088** | ⚠️ no es el 80 por defecto |
| PHP | 8.2.12 | extensiones `gd`, `zip`, `intl` activadas |
| MariaDB | 10.4.32 | |
| WordPress | 7.1.3 | |
| WooCommerce | 11.2.1 | usa **HPOS**: los pedidos viven en `wp_wc_orders`, no en `wp_posts` |
| Tema | **Blocksy 2.1.59** | tema **clásico**, no de bloques |

### URLs

| Qué | URL |
|---|---|
| Sitio | `http://localhost:8088/wordpress/` |
| Admin | `http://localhost:8088/wordpress/wp-admin/` |
| Tienda | `http://localhost:8088/wordpress/tienda/` |
| phpMyAdmin | `http://localhost:8088/phpmyadmin/` |

> ⚠️ **Siempre con `:8088`.** Usar `localhost` sin puerto es la causa nº1 de
> "no funciona" en este proyecto. Varias herramientas (incluida la IA de
> Antigravity) fallaron por esto.

---

## 3. Estructura de archivos

```
E:\xampp\htdocs\wordpress\
├── wp-config.php
├── wp-content\
│   ├── themes\blocksy\               ← tema activo
│   ├── plugins\
│   │   ├── woocommerce\              ← núcleo de la tienda
│   │   ├── blocksy-companion\        ← funciones extra del tema
│   │   ├── woocommerce-paypal-payments\   ← PayPal (sandbox)
│   │   ├── woocommerce-mercadopago\       ← Mercado Pago + Yape
│   │   ├── culqi-full-integration\        ← Culqi (gateway id: fullculqi)
│   │   ├── mcp-adapter\                   ← servidor MCP remoto
│   │   └── wordpress-mcp\                 ← servidor MCP alterno
│   └── mu-plugins\                   ← plugins "must use", se cargan siempre
│       ├── minikids-estetica.php     ← encola los CSS
│       ├── minikids-estetica.css     ← diseño de tienda y producto (1203 líneas)
│       ├── minikids-bloques.css      ← correcciones del home + feedback carrito
│       ├── minikids-mcp-abilities.php← 8 habilidades MCP propias
│       └── minikids-redirect-tienda.php ← redirige /shop/ → /tienda/
└── wp-content\uploads\
    ├── minikids\                     ← logo.png, logo-square.jpg, mockups
    └── 2026\10\                      ← 7 imágenes de producto
```

### Reglas de edición (importantes)

- El contenido de las páginas debe seguir siendo **bloques nativos** (`<!-- wp:... -->`).
  Nunca HTML crudo: rompe el editor y el flujo de WooCommerce.
- **No** editar PHP de plugins de terceros — se pierde en la próxima actualización.
- **No** escribir directo a la BD para productos, pedidos u opciones: usar el admin o la API.
- La página de producto **no se construye con bloques**. La controla Plantillas PHP de
  WooCommerce + `Apariencia → Personalizar → WooCommerce → Producto individual` + CSS.

---

## 4. Contenido del catálogo

### Páginas

| ID | Página | Rol |
|---|---|---|
| **8** | Inicio | portada (front page) |
| 9 | Tienda | |
| 10 | Nosotros | |
| 11 | Contacto | |
| 33 | Shop | página de tienda de WooCommerce |
| 34 | Cart | carrito |
| 35 | Checkout | pago |
| 36 | My account | mi cuenta |

### Productos (IDs 38–44)

| ID | Producto | SKU | Precio | Antes | Stock |
|---|---|---|---|---|---|
| 38 | Peluche osito abrazable | MK-PEL-001 | S/ 39.90 | 49.90 | 15 |
| 39 | Bloques creativos 60 piezas | MK-DID-001 | S/ 29.90 | 39.90 | 12 |
| 40 | Set de cocina mini chef | MK-ROL-001 | S/ 27.90 | 34.90 | 12 |
| 41 | Pista de carreras turbo | MK-VEH-001 | S/ 44.90 | 54.90 | 10 |
| 42 | Kit de arte pequeños artistas | MK-CRE-001 | S/ 20.90 | 24.90 | 10 |
| 43 | Juego de memoria animalitos | MK-FAM-001 | S/ 12.90 | 19.90 | 10 |
| 44 | Mini karaoke con micrófono | MK-TEC-001 | S/ 59.90 | 69.90 | 10 |

### Categorías (slugs reales)

`peluches` · `didacticos` · `juegos-de-rol` · `vehiculos` · `creatividad` ·
`juegos-familiares` · `tecnologia`

URL real: `http://localhost:8088/wordpress/product-category/<slug>/`

> ⚠️ `/tienda/categoria/<slug>/` y `/categoria-producto/<slug>/` dan **404**.
> Los bases configurados son `product_base = product` y `category_base = product-category`.

---

## 5. Configuración de la tienda

| Ajuste | Valor |
|---|---|
| Moneda | PEN (S/) |
| País | Perú |
| Formato de precio | `S/ 39.90` |
| Peso / dimensiones | kg / cm |
| Impuestos | desactivados |
| Cupones | activados |
| Compra como invitado | activada |
| AJAX add to cart | **sí** |
| Redirigir tras añadir | no (se queda en la página) |

**Zona de envío "Perú":** envío gratis desde **S/ 149**, tarifa plana **S/ 15**.

**Pedidos de prueba:** #61 (S/ 104.70, transferencia, `wc-on-hold`), #64 (S/ 213.40, transferencia).

---

## 6. Métodos de pago — estado real

| Gateway | ID | Estado | Nota |
|---|---|---|---|
| PayPal | `ppcp-gateway` | ✅ activo | sandbox conectado, `merchant_connected: 0` |
| Mercado Pago — tarjeta | `woo-mercado-pago-custom` | ✅ activo | operativo |
| Mercado Pago — Yape | `woo-mercado-pago-yape` | ✅ activo | operativo |
| Mercado Pago — básico | `woo-mercado-pago-basic` | ✅ activo | operativo |
| Mercado Pago — ticket | `woo-mercado-pago-ticket` | ✅ activo | |
| **Culqi** | `fullculqi` | ⚠️ **activo pero sin claves** | esto es el aviso "observado" |
| Transferencia bancaria | `bacs` | ✅ activo | |
| Pago contra entrega | `cod` | ✅ activo | |
| Cheque | `cheque` | ❌ desactivado | |

### Cómo arreglar Culqi ("observado")

La opción `woocommerce_fullculqi_settings` **no contiene `public_key` ni `secret_key`**.
Solo tiene textos de configuración, por eso el gateway aparece observado.

1. Entra a **Culqi Panel → Desarrollo → API Keys** y copia tus llaves de prueba.
2. WordPress → **WooCommerce → Ajustes → Pagos → Culqi Popup → Gestionar**.
3. Pega la llave pública y la privada, guarda.
4. Haz una compra de prueba con la tarjeta de test de Culqi.

---

## 7. Conectarse al MCP de WordPress

Hay **dos** servidores MCP disponibles en este WordPress:

| Servidor | Transporte | Ruta / comando |
|---|---|---|
| `mcp-adapter` | HTTP remoto | `/wp-json/mcp/mcp-adapter-default-server` |
| `wordpress-mcp` | proxy stdio | plugin `wordpress-mcp` 0.2.5 |

### Autenticación

Basic Auth con el usuario administrador:

```
usuario:    admin
contraseña: <CREDENCIAL_PRIVADA>
```

Cabecera ya codificada en Base64:

```
Authorization: Basic <BASIC_AUTH_PRIVADO>
```

> 🔒 Esta contraseña de aplicación está en texto plano en el proyecto.
> **Rótala antes de publicar nada** (Usuarios → Perfil → Contraseñas de aplicación).

### Comprobar que responde

```powershell
# Lista de herramientas disponibles
$h = @{ Authorization = 'Basic <BASIC_AUTH_PRIVADO>' }
Invoke-RestMethod -Uri 'http://localhost:8088/wordpress/wp-json/mcp/mcp-adapter-default-server' `
                  -Headers $h -Method Post `
                  -ContentType 'application/json' `
                  -Body '{"jsonrpc":"2.0","id":1,"method":"tools/list"}'
```

---

## 8. Conectar OpenCode al MCP de WordPress

OpenCode lee la configuración de `opencode.json` (o `opencode.jsonc`). Puede ir en la
raíz del proyecto o en `~/.config/opencode/opencode.json` para que sea global.

### 8.1 Servidor remoto (recomendado — no requiere Node)

Crea `opencode.json` en la raíz de tu proyecto:

```json
{
  "$schema": "https://opencode.ai/config.json",
  "mcp": {
    "wordpress": {
      "type": "remote",
      "url": "http://localhost:8088/wordpress/wp-json/mcp/mcp-adapter-default-server",
      "enabled": true,
      "headers": {
        "Authorization": "Basic <BASIC_AUTH_PRIVADO>"
      }
    }
  }
}
```

Notas importantes:

- `oauth: false` **no hace falta** aquí: como enviamos `Authorization` en `headers`,
  el servidor responde 200 y OpenCode nunca dispara el flujo OAuth.
  Si aun así se queja, añade `"oauth": false`.
- `url` debe apuntar al **puerto 8088**. Es el error más común.
- Añade `"timeout": 15000` si tu XAMPP tarda en responder la primera vez.

### 8.2 Servidor local (proxy stdio)

Úsalo solo si prefieres no exponer credenciales en el JSON del proyecto, o si el
proxy te da acceso a más herramientas:

```json
{
  "$schema": "https://opencode.ai/config.json",
  "mcp": {
    "wordpress-local": {
      "type": "local",
      "command": ["npx", "-y", "@automattic/mcp-wordpress-remote"],
      "enabled": true,
      "environment": {
        "WP_API_URL": "http://localhost:8088/wordpress/wp-json/mcp/mcp-adapter-default-server",
        "WP_API_USERNAME": "admin",
        "WP_API_PASSWORD": "<CREDENCIAL_PRIVADA>"
      }
    }
  }
}
```

### 8.3 Comandos de diagnóstico de OpenCode

```bash
opencode mcp list                  # lista servidores y su estado de autenticación
opencode mcp auth list             # estado OAuth
opencode mcp debug wordpress       # prueba conectividad HTTP y flujo OAuth
```

### 8.4 Cómo usarlo en un prompt

Los nombres de herramienta salen con el prefijo del servidor MCP:

```
usa la herramienta wordpress para listar los plugins activos
usa wordpress_get-content para leer la página con ID 8
```

### 8.5 Reducir consumo de contexto

Los MCP consumen muchos tokens. Desactiva lo que no uses:

```json
{
  "mcp": {
    "wordpress": { "type": "remote", "url": "...", "headers": {}, "enabled": true },
    "ruidoso":    { "type": "local",  "command": ["npx", "-y", "algo"] }
  },
  "tools": {
    "ruidoso*": false
  }
}
```

---

## 9. Habilidades MCP propias (mu-plugin `minikids-mcp-abilities.php`)

Ocho herramientas personalizadas, útiles porque evitan tocar la BD a mano:

| Habilidad | Para qué |
|---|---|
| `minikids/list-content` | listar páginas y entradas con su ID |
| `minikids/get-content` | leer el contenido de una página |
| `minikids/update-content` | escribir el contenido de una página |
| `minikids/get-option` | leer una opción de WordPress |
| `minikids/update-option` | actualizar una opción |
| `minikids/list-plugins` | listar plugins y su estado |
| `minikids/read-file` | leer un archivo de `wp-content` |
| `minikids/write-file` | escribir un archivo de `wp-content` |

---

## 10. Conexión a la base de datos (para diagnóstico)

```powershell
$mysql = 'E:\xampp\mysql\bin\mysql.exe'

# Listar páginas
& $mysql -u root --host=127.0.0.1 -e "SELECT ID, post_title, post_status FROM wordpress.wp_posts WHERE post_type='page';"

# Leer el contenido de la portada
& $mysql -u root --host=127.0.0.1 -N -B -e "SELECT post_content FROM wordpress.wp_posts WHERE ID=8;" 
```

> ⚠️ **`-B` (batch) escapa saltos de línea como `\n` literal en la salida.**
> Eso **no** significa que el contenido tenga `\n` literales. Este detalle causó un
> diagnóstico equivocado una vez. Para ver el contenido real, vuelca a archivo con
> `OUTFILE` o usa PHP con `get_post_field()`.

---

## 11. Cosas que rompen el proyecto (aprendidas a golpes)

| Síntoma | Causa real | Solución |
|---|---|---|
| "No carga la web" | falta `:8088` | usar `http://localhost:8088/wordpress/` |
| Contenido del home duplicado ×7 | bloque `woocommerce/product-collection` mal formado (autocerrado, atributos inválidos) | reemplazar por bloques nativos |
| Divs que se salen del contenedor | `alignfull` dentro de `.entry-content` constrained de Blocksy | quitar `alignfull` del contenido |
| MySQL se muere a los 30 s | procesos lanzados desde una consola que ya cerró | **arrancar MySQL desde el Panel de Control de XAMPP** |
| MaríaDB no arranca | tablas Aria corruptas / `mysql.plugin` corrupto | ver sección de recuperación abajo |
| `unknown tool` en el MCP del agente | el perfil activo no declara el servidor | añadirlo al perfil |
| curl se queda colgado | sin `-m` o `--max-time`, y apuntando al puerto 80 | siempre `-m 30` y puerto 8088 |

### Recuperar MariaDB si no arranca

```
1. Respaldar E:\xampp\mysql\data\mysql\plugin.*  (plugin.frm, plugin.MAD, plugin.MAI)
2. Borrar los archivos plugin.* de esa carpeta
3. Borrar todos los aria_log.* incluido aria_log_control
4. Reparar tablas Aria:  aria_chk -r <tabla>
5. Arrancar MySQL desde el Panel de Control de XAMPP
```

---

## 12. Estado actual y siguientes pasos

### ✅ Hecho

- Tema migrado a Blocksy (clásico) — reemplazó a Twenty Twenty-Five.
- Catálogo real de 7 productos con precios, SKUs, stock e imágenes.
- Categorías reales creadas y asignadas.
- Portada reescrita: barra superior, hero, 7 tarjetas de categoría, banner CTA, 4 beneficios.
- Eliminada la duplicación ×7 y el desbordamiento por `alignfull`.
- Pulido del feedback "Ver carrito" (`minikids-bloques.css`).
- Flujo completo verificado: tienda → producto → carrito → checkout (todo HTTP 200).
- Pedidos de prueba #61 y #64 generados correctamente.
- Repositorio GitHub con el proyecto y la BD saneada.

### 🔜 Pendiente, en orden sugerido

1. **Pegar las claves de Culqi** y probar un pago de prueba. *(bloquea el T1 de pagos)*
2. **4 atributos de producto**: Edad recomendada, Material, Dimensiones, Qué desarrolla → asignar a los 7 productos.
3. **Bloque de descripción corta del producto**: ahí va la barra de especificaciones (color crema, 4 columnas), porque la ficha no se hace con bloques.
4. **Personalizar Blocksy**: `Apariencia → Personalizar`
   - Paleta: `#FFD400` amarillo · `#0F62D6` azul · `#F0342C` rojo · `#111111` tinta · `#6B6B6B` gris · `#FAF6EF` crema · `#FFFFFF` blanco
   - Tipografías: **Baloo 2 ExtraBold** (títulos) · **Nunito Sans** (texto) · **Caveat** (manuscrita)
   - Header Builder: logo + barra superior · Footer: 4 columnas
   - Ocultar las valoraciones (no hay reseñas todavía)
5. **README del repositorio** con la instalación y las credenciales de prueba.
6. **Rotar las sales de WordPress** y la contraseña de aplicación (quedaron en el historial de Git).
7. Limpiar páginas basura (Política de privacidad, Refund and Returns Policy en borrador).
8. Borrar diagnósticos temporales en `E:\xampp\tmp\`.

---

## 13. Repositorio

| Dato | Valor |
|---|---|
| Repo | `MiguelAHR/ToyCommerce`, rama `main` |
| Clon local | `C:\Users\USUARIO\Documents\Admin\ToyCommerce\` |
| Contenido | `wordpress/wp-content`, `database/wordpress-sanitized.sql`, `docs/PALETA-Y-OSITO.css` |

> ⚠️ `wp-config.php` se sacó del índice pero **sigue en el historial** (commit `dad856f`).
> Hay que rotar las sales.

---

## 14. Cómo trabajar con el agente (este asistente)

- **Pídele verificación, no confianza.** Cualquier cambio debe comprobarse con una
  petición HTTP real a `http://localhost:8088/wordpress/...`.
- **Nunca aceptes "listo" sin prueba.** Ni de una IA intermedia (Antigravity)
  ni de este agente.
- El agente **sí puede ver imágenes**; Antigravity **no**.
- Antigravity reportó éxito falso dos veces y no tiene WP-CLI instalado.
  Si lo usas, verifica su salida siempre.
- Para tareas de estética, la fuente de verdad son los mockups en
  `wp-content/uploads/minikids/`. Recuerda que usan **productos y precios ficticios**:
  copia el **diseño**, no los datos.
