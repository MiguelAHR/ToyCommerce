# Cómo se sube el proyecto a GitHub

Documento de referencia para cualquier herramienta o persona que trabaje en ToyCommerce
(MiniKids Perú). Describe **el método real que se usa hoy**, no el ideal.

---

## 1. Datos del repositorio

| Dato | Valor |
|---|---|
| Remoto | `https://github.com/MiguelAHR/ToyCommerce.git` |
| Rama | `main` (única rama de trabajo) |
| Clon local | `C:\Users\USUARIO\Documents\Admin\ToyCommerce` |
| Identidad Git | `Mahr16` / `78241410+MiguelAHR@users.noreply.github.com` |
| Credenciales | Git Credential Manager (`credential.helper=manager`), ya cacheadas |
| Tamaño `.git` | ~66 MB |
| Árbol de trabajo | ~263 MB |
| Archivos rastreados | **11.813** |

### WordPress vivo (origen de los cambios)

```
E:\xampp\htdocs\wordpress\            <- aquí se edita de verdad
http://localhost:8088/wordpress/      <- puerto 8088, NO el 80
```

---

## 2. El método de subida (flujo real)

**No hay despliegue automático.** El repo es una **copia manual** del WordPress vivo.
El flujo es siempre el mismo:

```
1. Editar en el WordPress vivo          E:\xampp\htdocs\wordpress\
2. Copiar los archivos a mano           -> C:\...\ToyCommerce\wordpress\...
3. (Si cambió contenido) Regenerar la BD saneada
4. git add / commit / push
```

### Paso 2 — qué se copia

Origen y destino son espejos de `wp-content`:

| Origen (vivo) | Destino (repo) |
|---|---|
| `E:\xampp\htdocs\wordpress\wp-content\mu-plugins\` | `wordpress\wp-content\mu-plugins\` |
| `E:\xampp\htdocs\wordpress\wp-content\themes\` | `wordpress\wp-content\themes\` |
| `E:\xampp\htdocs\wordpress\wp-content\plugins\` | `wordpress\wp-content\plugins\` |
| `E:\xampp\htdocs\wordpress\wp-content\uploads\` | `wordpress\wp-content\uploads\` |

Comando tipo:

```powershell
Copy-Item 'E:\xampp\htdocs\wordpress\wp-content\mu-plugins\minikids-bloques.css' `
          'C:\Users\USUARIO\Documents\Admin\ToyCommerce\wordpress\wp-content\mu-plugins\' -Force
```

### Paso 3 — regenerar la base de datos saneada

Solo si se tocó contenido (páginas, productos, ajustes, menús).

```powershell
# 1. Volcar la BD real
E:\xampp\mysql\bin\mysqldump.exe -u root --host=127.0.0.1 --default-character-set=utf8mb4 `
  --single-transaction --skip-lock-tables --routines --triggers `
  --result-file=E:\xampp\tmp\wordpress-raw.sql wordpress

# 2. Sanear (quita secretos y anonimiza)
E:\xampp\php\php.exe E:\xampp\tmp\sanitize-dump.php

# 3. Copiar al repo
Copy-Item E:\xampp\tmp\wordpress-sanitized.sql `
          'C:\Users\USUARIO\Documents\Admin\ToyCommerce\database\wordpress-sanitized.sql' -Force
```

> ⚠️ **Usa siempre `--result-file`, nunca `| Out-File`.** `Out-File` trunca líneas
> largas y corrompe el volcado sin avisar.

> ⚠️ **No uses `--databases`.** Sin ese flag, `mysqldump` no emite `CREATE DATABASE`
> ni `USE`, así que el volcado se puede importar en cualquier base sin riesgo de
> pisar la real.

### Paso 4 — commit y push

```powershell
Set-Location 'C:\Users\USUARIO\Documents\Admin\ToyCommerce'

git add -A
git commit -F 'E:\xampp\tmp\commit-msg.txt'    # mensaje desde archivo
git push origin main
```

> ⚠️ **El mensaje va en un archivo (`-F`), no en `-m`.** Un mensaje multilínea con
> comillas pasado por `-m` desde PowerShell se parte en trozos y git lo interpreta
> como rutas, fallando con `pathspec ... did not match any file(s) known to git`.

### Verificación tras el push

```powershell
git fetch origin
git rev-list --count origin/main..main     # debe ser 0
git rev-list --count main..origin/main     # debe ser 0
```

---

## 3. Contenido: qué se versiona y qué no

### ✅ Versionado

| Ruta | Contenido |
|---|---|
| `wordpress/wp-content/mu-plugins/` | **5 archivos** — todo el código propio de MiniKids |
| `wordpress/wp-content/plugins/` | 5 plugins (WooCommerce, MP, PayPal, MCP ×2) |
| `wordpress/wp-content/themes/` | 5 temas (los de Twenty* y `minikids-toys`) |
| `wordpress/wp-content/uploads/` | 67 archivos (imágenes de producto, mockups, logo) |
| `wordpress/wp-content/languages/` | 172 archivos |
| `wordpress/wp-includes/`, `wordpress/wp-admin/` | **core de WordPress** (3.322 archivos) |
| `database/wordpress-sanitized.sql` | BD completa saneada (52 tablas) |
| `docs/` | guías y paleta |
| `tools/` | scripts de instalación |
| `README.md`, `.gitignore`, `wp-config.example.php` | raíz |

### ❌ No versionado

| Ruta | Motivo |
|---|---|
| `wordpress/wp-config.php` | credenciales reales (ignorado y verificado) |
| `*.log`, `*.bak`, `*.tmp` | ruido |
| `wp-content/upgrade/`, `cache/`, `ai1wm-backups/` | generados en runtime |

---

## 4. PROBLEMAS CONOCIDOS DEL REPO (hay que arreglarlos)

### 🔴 Crítico: falta el tema activo y 2 plugins activos

Comparación entre el WordPress vivo y lo que hay en el repo:

| Componente | En vivo | En el repo |
|---|---|---|
| **Tema Blocksy** (ACTIVO) | ✅ 1.530 archivos | ❌ **AUSENTE** |
| **Blocksy Companion** (ACTIVO) | ✅ | ❌ **AUSENTE** |
| **Culqi Full Integration** (ACTIVO) | ✅ | ❌ **AUSENTE** |
| WooCommerce | ✅ | ✅ |
| Mercado Pago | ✅ | ✅ |
| PayPal Payments | ✅ | ✅ |
| MCP Adapter | ✅ | ✅ |
| WordPress MCP | ✅ | ✅ |
| Twenty Twenty-Five, -Four, -Three, -Two | ✅ | ✅ (instalados, ninguno activo) |
| `minikids-toys` | ✅ | ✅ (tema viejo, ya no se usa) |

**Consecuencia:** quien clone el repo **no obtiene un sitio funcional**. Al activar
el tema, WordPress no encuentra Blocksy y cae a un tema por defecto. Y Culqi no
aparece como método de pago.

**Arreglo:**

```powershell
$src = 'E:\xampp\htdocs\wordpress\wp-content'
$dst = 'C:\Users\USUARIO\Documents\Admin\ToyCommerce\wordpress\wp-content'
Copy-Item "$src\themes\blocksy"                 "$dst\themes\"  -Recurse -Force
Copy-Item "$src\plugins\blocksy-companion"      "$dst\plugins\" -Recurse -Force
Copy-Item "$src\plugins\culqi-full-integration" "$dst\plugins\" -Recurse -Force
```

### 🟡 El README contradice al repo

El README dice *"No se incluye el core de WordPress"*, pero **`wp-includes/` y
`wp-admin/` sí están rastreados** (3.322 archivos). Hay que decidir:

- **Opción A (recomendada):** dejar de versionar el core y añadir a `.gitignore`:
  ```
  wordpress/wp-admin/
  wordpress/wp-includes/
  wordpress/wp-*.php
  ```
  El repo baja muchísimo de peso. Requiere `git rm -r --cached` de esas rutas.
- **Opción B:** corregir el README para reflejar que el core sí se incluye.

### 🟡 Logs de WooCommerce versionados

Cinco archivos `.log` están rastreados y **`*.log` ya está en `.gitignore`**, pero
`gitignore` no afecta a archivos ya rastreados:

```
wordpress/wp-content/uploads/wc-logs/log-2026-10-11-*.log
wordpress/wp-content/uploads/wc-logs/transactional-emails-*.log
wordpress/wp-content/uploads/wc-logs/wc-analytics-order-import-*.log
wordpress/wp-content/uploads/wc-logs/wc_logger-*.log
wordpress/wp-content/uploads/wc-logs/woocommerce-paypal-payments-*.log
```

**Arreglo:** `git rm -r --cached wordpress/wp-content/uploads/wc-logs/`
y añadir `!wordpress/wp-content/uploads/wc-logs/.htaccess` si se quiere conservar.

### 🟡 `.gitignore` con caracteres corruptos

Los comentarios tienen mojibake (`configuraci��n` en vez de `configuración`).
Es un problema de codificación al guardarlo. Hay que reescribirlo en UTF-8 sin BOM.

---

## 5. Instrucciones para Antigravity (o cualquier agente)

### Lo que SÍ puede hacer

- Leer y editar **solo** `wordpress/wp-content/mu-plugins/*`:
  - `minikids-estetica.php` — encola las hojas de estilo
  - `minikids-estetica.css` — diseño de tienda y ficha de producto
  - `minikids-bloques.css` — correcciones del home y feedback del carrito
  - `minikids-mcp-abilities.php` — habilidades MCP
  - `minikids-redirect-tienda.php` — redirección `/shop/` → `/tienda/`
- Editar contenido de páginas **con bloques nativos de Gutenberg**
- Ejecutar `git add`, `git commit -F`, `git push origin main`
- Leer la base de datos para diagnóstico

### Lo que NO debe hacer nunca

| Prohibido | Motivo |
|---|---|
| Editar `wp-content/themes/blocksy/**` | se pierde en la próxima actualización del tema |
| Editar `wp-content/plugins/woocommerce/**` ni ningún plugin de terceros | idem |
| Escribir directo a la BD (productos, pedidos, opciones) | rompe HPOS y las cachés de WooCommerce |
| Inyectar `the_content`, `wp_head` o `wp_footer` | rompe el flujo de carrito y checkout |
| Poner HTML crudo en el contenido de páginas | rompe el editor de bloques |
| Aplicar `overflow: hidden` / `overflow-x: clip` a `.ct-container` | **rompe los desplegables del menú y el panel del carrito de Blocksy** |
| Usar `http://localhost/wordpress/` sin `:8088` | el sitio no está en el puerto 80 |
| Ejecutar `curl` sin `-m 30` | se queda colgado indefinidamente |

### Herramientas que necesita

| Herramienta | Ruta / uso |
|---|---|
| `git` | con Credential Manager ya configurado |
| `E:\xampp\mysql\bin\mysqldump.exe` | volcado de la BD |
| `E:\xampp\mysql\bin\mysql.exe` | consultas de diagnóstico |
| `E:\xampp\php\php.exe` | ejecutar `sanitize-dump.php` y scripts de contenido |
| `Invoke-WebRequest` (PowerShell) | verificar el render real |
| MCP `wordpress` | `http://localhost:8088/wordpress/wp-json/mcp/mcp-adapter-default-server` |

### Regla de verificación obligatoria

**Nunca reportar éxito sin comprobarlo con una petición HTTP real.** En este proyecto
ya hubo dos reportes de éxito falsos. Verificación mínima:

```powershell
$h = (Invoke-WebRequest 'http://localhost:8088/wordpress/' -UseBasicParsing -TimeoutSec 60).Content
"HTTP OK, $([int]($h.Length/1KB)) KB"
"bloques duplicados : " + ([regex]::Matches($h,'Explora por categor')).Count   # debe ser 1
"rejillas producto  : " + ([regex]::Matches($h,'<ul[^>]*class="[^"]*products')).Count
"errores PHP        : " + ([regex]::Matches($h,'Fatal error|wpdberror')).Count   # debe ser 0
```

Criterios que debe cumplir la portada:

- `Explora por categoría` aparece **exactamente 1 vez**
- **0** rejillas `<ul class="products">` (el catálogo vive en `/tienda/`)
- **0** `fatal error` / `wpdberror`
- menos de **100 KB** de HTML
- **0** `wp-block-cover alignfull`

---

## 6. Trampas conocidas del entorno

| Síntoma | Causa | Solución |
|---|---|---|
| `No database selected` al importar | se importó sin indicar la base | `mysql ... NOMBRE_BD < volcado.sql`, o crear la BD antes |
| `pathspec ... did not match any file(s)` | mensaje de commit multilínea pasado con `-m` | usar `git commit -F archivo` |
| Volcado SQL corrupto / truncado | se usó `\| Out-File` | usar `--result-file` |
| MariaDB no arranca | tablas Aria o `mysql.plugin` corruptos | borrar `aria_log.*` y los `plugin.*` corruptos, luego `aria_chk -r` |
| MySQL se muere a los 30 s | proceso lanzado desde una consola ya cerrada | arrancar desde el **Panel de Control de XAMPP** |
| `unknown tool` en el MCP | el perfil activo no declara el servidor | añadirlo a la config del perfil |
| Mojibake en el `.gitignore` | codificación incorrecta al guardar | reescribir en UTF-8 sin BOM |

---

## 7. Lista de verificación antes de cada push

```
[ ] Los archivos editados están copiados al repo (no solo en el WordPress vivo)
[ ] Si cambió contenido -> volcado regenerado y saneado en database/
[ ] El volcado NO contiene tokens, claves ni emails reales
[ ] El sitio responde 200 en localhost:8088 y sin errores PHP
[ ] git status muestra solo los archivos que pretendías cambiar
[ ] El commit usa -F, no -m
[ ] Tras el push: git rev-list --count origin/main..main  ==  0
```
