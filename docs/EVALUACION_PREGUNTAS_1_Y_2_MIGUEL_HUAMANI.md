# EVALUACIÓN: E-BUSINESS Y ANALÍTICA WEB
**Estudiante:** Miguel Angel Huamani Rojas  
**Proyecto:** Tienda Virtual «MiniKids Perú» (https://minikids.vitorstream.shop)  
**Razón Social / Proveedor Legal:** MIMI KIDS STORE E.I.R.L. | RUC: 20609651530  

---

## PREGUNTA 1 (6 PUNTOS) — PROCESO LOGÍSTICO Y TRATAMIENTO DE DATOS PERSONALES

### 1. Almacenamiento y Gestión de Stock (Picking & Packing)
* **Hub Central:** Nuestro centro de almacenamiento físico opera en **Av. Alfredo Mendiola 3698, Independencia (Lima Norte)**, correspondiente al domicilio fiscal de *MIMI KIDS STORE E.I.R.L.* La cercanía directa a la Panamericana Norte facilita el despacho hacia toda Lima Metropolitana y la conexión con agencias de carga interprovincial.
* **Control de Inventario por SKU:** Cada producto cuenta con un identificador estructurado único en base de datos bajo el estándar `[MARCA]-[CATEGORÍA]-[SUBTIPO]-[EDAD]-[CORRELATIVO]` (por ejemplo, `SKU: MK-DID-BLQ-02A-004` para el Set de Bloques de Madera). Trabajamos con la arquitectura HPOS de WooCommerce, lo que asegura que el stock se descuente en tiempo real al procesar la orden, evitando sobreventas (*stockouts*).
* **Protocolo de Embalaje:** Clasificamos por tipo de material: los juguetes de madera y kits creativos llevan protección con plástico de burbujas en cajas corrugadas selladas; los peluches y muñecas van en bolsas termoselladas que preservan la higiene del textil contra polvo y humedad.

### 2. Transporte, Entregas y Última Milla (2 a 5 días hábiles)
* **Lima Metropolitana y Callao (24 a 48 horas):** Despacho urbano mediante flota de motorizados aliados con tarifa plana de **S/ 15.00**. Se coordina previamente con el cliente vía celular registrado.
* **Provincias a Nivel Nacional (2 a 5 días hábiles):** Alianzas estratégicas con operadores formales: **Olva Courier** (entrega directa a domicilio) y **Shalom Express** (recojo en agencia con DNI). Se genera guía de remisión electrónica y código de seguimiento (*tracking*) para el usuario.
* **Estrategia de Conversión:** Implementamos una barra de progreso en el carrito que desbloquea automáticamente **Envío Gratis a todo el Perú en compras desde S/ 149.00**.

> **[IMAGEN 1 AQUÍ: `01_checkout_logistica.png` — Pantalla de Checkout con captura de datos de envío, validación de celular peruano, cálculo de flete S/ 15.00 y pasarela de pago]**

### 3. Atención al Cliente y Fidelización
* **Canales Omnicanal:** Atención directa e inmediata mediante **WhatsApp Business (+51 991 117 308)** para consultas de stock y asesoría en vivo, complementado por correo institucional (`hola@minikids.pe`) y formulario web en `/contacto/` con respuesta en menos de 24 horas hábiles.
* **Notificaciones Transaccionales:** Conectadas vía webhook a la **API de Brevo** para confirmar compras, emisión de boletas y avisos de despacho en tiempo real.
* **Retención Postventa:** Ofrecemos una política de **30 días de cambio** ante cualquier falla de fábrica y un cupón promocional del 10% para incentivar la recompra en el siguiente pedido.

### 4. Marco Legal: Protección de Datos y Libro de Reclamaciones
* **Ley N° 29733 (Protección de Datos Personales):** En el Checkout y formularios se solicita consentimiento expreso. Los datos personales recolectados (Nombres, DNI, Dirección, Celular y Correo) se utilizan bajo estricto principio de finalidad para la entrega y facturación, viajando cifrados bajo **SSL/TLS 1.3 (HTTPS)**. Incluye validación de representante legal adulto en compras destinadas a menores de edad.
* **Libro de Reclamaciones Virtual (INDECOPI - Ley N° 29571):** Implementado en `/libro-de-reclamaciones/` identificando la razón social y RUC de la empresa. Distingue formalmente entre *Reclamo* y *Queja*, emite constancia con folio correlativo descargable en PDF y garantiza respuesta legal en un plazo máximo de **15 días hábiles**.

> **[IMAGEN 2 AQUÍ: `02_libro_reclamaciones.png` — Interfaz del Libro de Reclamaciones Virtual con identificación de MIMI KIDS STORE E.I.R.L. y consentimiento de tratamiento de datos personales]**

---

## PREGUNTA 2 (7 PUNTOS) — COMPONENTES DE HARDWARE, SOFTWARE Y ALCANCES EN LA NUBE

### 1. Matriz de Componentes de Hardware y Software

| Componente | Tipo | Especificación Técnica | Rol en la Arquitectura MiniKids Perú |
| :--- | :--- | :--- | :--- |
| **Servidor VPS** | Hardware (Cloud) | Virtualización KVM multi-core vCPU, 2 GB+ RAM, almacenamiento **NVMe SSD** RAID 10 y puerto de 1 Gbps. | Hosting en la nube de alto rendimiento con latencias &lt;50 ms en Perú y baja carga de CPU. |
| **Terminales** | Hardware (Local) | Laptops de gestión administrativa y Smartphones (Android/iOS) de clientes finales. | Gestión de inventario en almacén y navegación móvil de los compradores. |
| **S.O. y Web Server** | Software Base | **Ubuntu Server 22.04 LTS** + **Nginx** (Reverse Proxy) + **PHP-FPM 8.2**. | Manejo eficiente de conexiones concurrentes y entrega ultrarrápida de estáticos con búfer optimizado. |
| **Base de Datos** | Software Servidor | **MariaDB 10.4 / MySQL** con motor transaccional **InnoDB** y soporte para **HPOS**. | Integridad ACID en transacciones de órdenes (`wp_wc_orders`), separándolas de posts regulares. |
| **CMS y E-Commerce** | Software Aplicativo | **WordPress 7.1.3** + **WooCommerce 11.2.1** + Tema **Blocksy 2.1.59** + `mu-plugins` propios. | Capa de catálogo, buscador reactivo en vivo, carrito Ajax, pasarelas de pago y diseño responsive. |

> **[IMAGEN 1 AQUÍ: `14_inicio_wordpress_blocksy.png` — Portada principal desarrollada bajo WordPress 7.1.3 y tema Blocksy con enfoque Mobile-First]**

### 2. Plataformas Cloud Integradas
* **Cloudflare (Edge Cloud Platform):** Actúa como proxy perimetral gestionando DNS Anycast, caché CDN de imágenes/CSS para acelerar el TTFB, cortafuegos WAF contra bots y cifrado de seguridad **SSL/TLS 1.3**.
* **Brevo API (Cloud Email):** Infraestructura en la nube para el despacho de correos transaccionales certificados con entregabilidad a bandeja de entrada (evitando spam).
* **Git & GitHub:** Plataforma cloud para control de versiones del proyecto bajo el repositorio `MiguelAHR/ToyCommerce` con ramas protegidas.

### 3. Alcances del Proyecto en la Nube (Cloud Computing Scope)
1. **Alta Disponibilidad 24/7 (SLA 99.9%):** Desacople de servicios que permite mantener la tienda operativa permanentemente, sin caídas por concurrencia ni bloqueos de base de datos.
2. **Escalabilidad Elástica en Campañas:** La arquitectura combinada de Nginx, caché en memoria y CDN en Cloudflare permite absorber picos de tráfico repentinos (Día del Niño, Cyber Days, Navidad) sin degradar la experiencia de usuario.
3. **Seguridad Perimetral y Resiliencia (Disaster Recovery):** Mitigación automática de ataques DDoS volumétricos a nivel de DNS y copias de seguridad automatizadas de base de datos y archivos multimedia en almacenamiento externo, asegurando recuperación rápida (RTO &lt; 30 min).
4. **Optimización de Core Web Vitals:** Rendimiento móvil optimizado (LCP &lt; 2.5s, CLS &lt; 0.1), garantizando una experiencia de compra fluida con tiempos de carga inferiores a 2 segundos en redes móviles 4G/5G.

> **[IMAGEN 2 AQUÍ: `03_tienda_catalogo.png` — Catálogo en vivo (/tienda/) administrado con WooCommerce HPOS y base de datos relacional MariaDB]**
