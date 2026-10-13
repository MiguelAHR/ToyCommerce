# 🚀 Guía de Instalación Rápida para el Docente — MiniKids Perú

Esta tienda está configurada en modo **Plug & Play**. No requiere configuraciones complejas ni instalaciones desde cero.

---

## ⚡ Instalación en 3 Pasos (Tiempo estimado: 2 minutos)

### Paso 1: Copiar la carpeta del proyecto a XAMPP
Copia o mueve la carpeta **`wordpress`** descargada del repositorio dentro de tu directorio `htdocs` de XAMPP:

```text
C:\xampp\htdocs\wordpress\
```

> 💡 **Nota:** El archivo `wp-config.php` ya viene preconfigurado con los valores estándar de XAMPP (`root` sin contraseña) y con **detección dinámica automática de puerto**, por lo que funcionará sin importar si tu Apache corre en el puerto **80**, **8080**, **8088** o cualquier otro.

---

### Paso 2: Importar la Base de Datos
1. Abre el panel de control de **XAMPP** e inicia los módulos **Apache** y **MySQL**.
2. En tu navegador, ingresa a **phpMyAdmin**:
   - `http://localhost/phpmyadmin` (o con tu puerto, ej. `http://localhost:8088/phpmyadmin`)
3. En la barra lateral izquierda, haz clic en **«Nueva»** y crea una base de datos llamada:
   - Nombre: **`wordpress`**
   - Cotejamiento: `utf8mb4_general_ci` (o el valor predeterminado)
4. Haz clic en la pestaña superior **«Importar»**.
5. Presiona **«Seleccionar archivo»**, elige el archivo del repositorio:
   ```text
   database/wordpress-sanitized.sql
   ```
6. Desplázate al final y haz clic en el botón **«Importar»**. *(La importación tarda menos de 10 segundos)*.

---

### Paso 3: ¡Listo! Abrir la tienda en tu navegador

Visita en tu navegador:
- Si tu XAMPP usa el puerto habitual:  
  👉 **`http://localhost/wordpress/`**
- Si tu XAMPP usa el puerto 8088:  
  👉 **`http://localhost:8088/wordpress/`**

---

## 🔑 Credenciales de Acceso al Panel de Administración (WP-Admin)

Para evaluar la configuración de WooCommerce, ajustes de pasarelas, pedidos y catálogo:

- **URL de Acceso:** `http://localhost/wordpress/wp-admin/`
- **Usuario:** `admin`
- **Contraseña:** `MiniKids2026!`

---

## 🧸 Puntos Clave para la Evaluación del Proyecto

1. **Portada Interactiva (`/wordpress/`):**
   - Slider hero con botón «Ver juguetes» conectado directamente al catálogo.
   - Grilla interactiva de categorías con efecto hover.
   - Sección de beneficios y formulario de newsletter con feedback en vivo.
   - Navbar compacto (60px) con botón de carrito lateral y separación armónica.

2. **Catálogo de Juguetes (`/wordpress/tienda/`):**
   - Encabezado centrado «Todos los juguetes / Elige una nueva aventura para cada día 🎈».
   - **Buscador en tiempo real** con filtrado instantáneo por nombre y palabras clave sin recargar la página.
   - **Filtros por categoría** con píldoras interactivas y botón inteligente «Ver todos» (solo aparece cuando hay un filtro activo).
   - **Diferenciación visual de ofertas:** Productos rebajados en rojo de marca con el precio anterior tachado; productos normales en tono sobrio y elegante.

3. **Mini Carrito Lateral (Drawer AJAX):**
   - Se abre automáticamente al añadir cualquier juguete o al pulsar el icono del carrito en el navbar.
   - Barra informativa de **envío gratis** (a partir de S/ 149 a todo el Perú).
   - Botón directo **«Ver carrito»** hacia la página completa y botón **«Finalizar compra»**.

4. **Página de Contacto (`/wordpress/contacto/`):**
   - Botón directo de **WhatsApp** con enlace predeterminado codificado al número **`+51 991 117 308`**.
   - Formulario de contacto interactivo con validación visual inmediata.

5. **Checkout y Pasarelas de Pago:**
   - Campo personalizado para celular peruano en la sección de contacto.
   - Pasarelas listas para pruebas: Transferencia bancaria, Pago contra entrega, Mercado Pago / Yape, Culqi y PayPal en modo sandbox.

---

## 🛠️ Solución a dudas frecuentes

- **¿Qué pasa si mi MySQL en XAMPP tiene una contraseña para el usuario `root`?**  
  Por defecto en XAMPP la contraseña de root viene vacía. Si le habías asignado una, abre `wordpress/wp-config.php` y coloca tu contraseña en la línea:
  ```php
  define('DB_PASSWORD', 'tu_contraseña_aqui');
  ```
- **¿Qué pasa si mi base de datos se llama diferente?**  
  Si prefieres usar otro nombre de base de datos, cámbialo en la línea 9 de `wordpress/wp-config.php`:
  ```php
  define('DB_NAME', 'tu_base_de_datos');
  ```
