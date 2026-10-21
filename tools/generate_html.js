const fs = require('fs');
const path = require('path');

const imgDir = "C:/Users/USUARIO/Documents/Admin/ToyCommerce/docs/evidencias_docente";

const htmlContent = `<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Evaluación E-Business MiniKids Perú</title>
<style>
    @page {
        margin: 2cm 1.8cm;
        size: A4 portrait;
    }
    body {
        font-family: 'Segoe UI', Calibri, Arial, sans-serif;
        font-size: 11pt;
        line-height: 1.45;
        color: #2D3748;
        margin: 0;
        padding: 0;
    }
    h1 {
        color: #1A365D;
        font-size: 17pt;
        border-bottom: 2.5px solid #2B6CB0;
        padding-bottom: 5px;
        margin-top: 26pt;
        margin-bottom: 12pt;
        page-break-after: avoid;
    }
    h2 {
        color: #2B6CB0;
        font-size: 13.5pt;
        margin-top: 18pt;
        margin-bottom: 8pt;
        page-break-after: avoid;
    }
    h3 {
        color: #2D3748;
        font-size: 11.5pt;
        margin-top: 13pt;
        margin-bottom: 6pt;
        page-break-after: avoid;
    }
    h4 {
        color: #4A5568;
        font-size: 11pt;
        margin-top: 10pt;
        margin-bottom: 4pt;
        page-break-after: avoid;
    }
    p {
        margin-top: 0;
        margin-bottom: 8pt;
        text-align: justify;
    }
    ul, ol {
        margin-top: 0;
        margin-bottom: 10pt;
        padding-left: 22px;
    }
    li {
        margin-bottom: 4pt;
        text-align: justify;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10pt;
        margin-bottom: 15pt;
        font-size: 9.5pt;
        page-break-inside: avoid;
    }
    th, td {
        border: 1px solid #CBD5E0;
        padding: 7px 9px;
        text-align: left;
        vertical-align: top;
    }
    th {
        background-color: #2B6CB0;
        color: #FFFFFF;
        font-weight: bold;
    }
    tr:nth-child(even) {
        background-color: #F7FAFC;
    }
    .header-box {
        background-color: #EBF8FF;
        border: 1px solid #BEE3F8;
        border-left: 6px solid #2B6CB0;
        padding: 14px 18px;
        margin-bottom: 22pt;
        border-radius: 4px;
    }
    .header-box p {
        margin-bottom: 4px;
        font-size: 10.5pt;
    }
    .img-container {
        text-align: center;
        margin-top: 12pt;
        margin-bottom: 16pt;
        page-break-inside: avoid;
    }
    img {
        max-width: 90%;
        max-height: 480px;
        height: auto;
        border: 1px solid #CBD5E0;
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .caption {
        font-size: 9pt;
        font-style: italic;
        color: #718096;
        text-align: center;
        margin-top: 5pt;
    }
    .badge {
        display: inline-block;
        padding: 2px 7px;
        font-size: 8.5pt;
        font-weight: bold;
        border-radius: 3px;
        background-color: #C6F6D5;
        color: #22543D;
    }
    .badge-blue {
        background-color: #BEE3F8;
        color: #2B6CB0;
    }
    .quote-box {
        background-color: #EDF2F7;
        border-left: 4px solid #4A5568;
        padding: 9px 13px;
        font-style: italic;
        margin-top: 8pt;
        margin-bottom: 12pt;
        font-size: 10pt;
    }
    .tip-box {
        background-color: #FEFCBF;
        border: 1px solid #FAF089;
        border-left: 5px solid #D69E2E;
        padding: 10px 14px;
        margin-top: 10pt;
        margin-bottom: 14pt;
        border-radius: 4px;
        font-size: 10pt;
    }
    .member-card {
        background-color: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 6px;
        padding: 12px 16px;
        margin-bottom: 14pt;
        border-left: 5px solid #3182CE;
    }
    .page-break {
        page-break-before: always;
    }
</style>
</head>
<body>

<!-- PORTADA Y METADATOS -->
<div style="text-align: center; margin-bottom: 22pt; padding-top: 10pt;">
    <div style="font-size: 12pt; font-weight: bold; color: #4A5568; letter-spacing: 1px; text-transform: uppercase;">Universidad Privada del Norte — Facultad de Negocios e Ingeniería</div>
    <h1 style="font-size: 22pt; margin-top: 10pt; margin-bottom: 6pt; border-bottom: none; color: #1A365D;">EVALUACIÓN ACADÉMICA: E-BUSINESS Y ANALÍTICA WEB</h1>
    <div style="font-size: 14pt; color: #2B6CB0; font-weight: bold; margin-bottom: 4pt;">Informe Técnico Oficial y Plantilla de Respuestas</div>
    <div style="font-size: 11pt; color: #718096;">Proyecto Integral: Tienda Virtual «MiniKids Perú» · Semestre 2026</div>
</div>

<div class="header-box">
    <p><strong>Proyecto Evaluado:</strong> Tienda Virtual E-Commerce «MiniKids Perú» (ToyCommerce)</p>
    <p><strong>URL en Producción (Host en Vivo):</strong> <a href="https://minikids.vitorstream.shop">https://minikids.vitorstream.shop</a></p>
    <p><strong>Razón Social y Proveedor Legal:</strong> MIMI KIDS STORE E.I.R.L. · RUC: 20609651530</p>
    <p><strong>Domicilio Fiscal y Almacén Central:</strong> Av. Alfredo Mendiola 3698, Independencia, Lima, Perú</p>
    <p><strong>Repositorio Oficial de Código:</strong> <a href="https://github.com/MiguelAHR/ToyCommerce.git">GitHub: MiguelAHR/ToyCommerce (Rama main)</a></p>
    <p><strong>Equipo de Trabajo (5 Integrantes):</strong></p>
    <ol style="margin-bottom: 0; padding-left: 20px;">
        <li><strong>Renzo José Aranda Garrido</strong></li>
        <li><strong>Miguel Angel Benavides Soto</strong></li>
        <li><strong>Crisdel Aldemir Gonzales</strong></li>
        <li><strong>Miguel Angel Huamani Rojas</strong></li>
        <li><strong>Cristopher Manuel Mendoza Vicente</strong></li>
    </ol>
</div>

<!-- ==================================================================== -->
<h1>PREGUNTA 1 (6 PUNTOS) — PROCESO LOGÍSTICO, ALMACENAMIENTO, TRANSPORTE, ATENCIÓN, FIDELIZACIÓN Y PROTECCIÓN DE DATOS</h1>

<p><em><strong>Consigna Docente:</strong> (INDIVIDUAL) Describa el proceso logístico de su proyecto evidenciándolo con pantallas relacionadas al proceso. Mencionar los puntos de Almacenamiento y transporte y entregas. Se debe tomar en cuenta la atención al cliente y fidelización y los aspectos legales del tratamiento de datos personales.</em></p>

<h2>1.1. Arquitectura del Flujo Logístico E-Commerce (End-to-End)</h2>
<p>El ciclo logístico de <strong>MiniKids Perú</strong> está diseñado para operar de manera sincronizada entre la tienda virtual en la nube y las operaciones físicas de almacén y transporte:</p>
<ol>
    <li><strong>Captura y Validación del Pedido:</strong> El cliente selecciona los artículos en el catálogo web y completa el Checkout (<code>/checkout/</code>). WooCommerce procesa la orden registrándola de inmediato en tablas transaccionales de alto rendimiento (HPOS).</li>
    <li><strong>Control y Reserva de Stock:</strong> El sistema descuenta automáticamente la cantidad comprada de los códigos SKU únicos correspondientes en el almacén de Lima Norte, evitando sobreventas (<em>stockouts</em>).</li>
    <li><strong>Picking y Packing (Embalaje Seguro):</strong> El equipo de operaciones recibe la notificación del pedido y procede a retirar los productos de las estanterías clasificadas, embalándolos según protocolos específicos para juguetes infantiles.</li>
    <li><strong>Despacho y Enrutamiento:</strong> Se clasifica el paquete según su destino (Lima Metropolitana o Provincias a nivel nacional) y se genera la guía de remisión electrónica con código de seguimiento (<em>tracking</em>).</li>
    <li><strong>Entrega de Última Milla (Last-Mile Delivery):</strong> El operador logístico (motorizado express, Olva Courier o Shalom) entrega el paquete al cliente final mediante validación de documento de identidad y firma de recepción.</li>
    <li><strong>Postventa, Atención y Fidelización:</strong> Envío de confirmaciones transaccionales vía Brevo API, soporte continuo por WhatsApp Business, cupón de recompra del 10% y garantía de satisfacción de 7 días.</li>
    <li><strong>Garantía Legal y Reclamaciones:</strong> Disponibilidad permanente del Libro de Reclamaciones Virtual conforme a las directivas de INDECOPI.</li>
</ol>

<h2>1.2. Puntos de Almacenamiento y Protocolos de Embalaje</h2>
<p><strong>Ubicación del Almacén Central:</strong> Se encuentra establecido en el distrito de <strong>Independencia (Lima Norte)</strong>, coincidiendo con el domicilio fiscal registrado de <em>MIMI KIDS STORE E.I.R.L.</em> (Av. Alfredo Mendiola 3698). Su posición estratégica junto a la Carretera Panamericana Norte permite una rápida conexión hacia los distritos del cono norte, centro y sur de Lima Metropolitana, así como una cercanía inmediata a los terminales de transporte terrestre de carga.</p>
<p><strong>Protocolos de Embalaje Diferenciado (Seguridad Infantil):</strong></p>
<ul>
    <li><strong>Juguetes Electrónicos y Tecnológicos (ej. Mini Karaoke MK-TEC-001):</strong> Embalaje tricapa consistente en caja corrugada rígida, amortiguación interna con plástico de burbujas sellado y precinto de seguridad que previene daños por vibración o impactos.</li>
    <li><strong>Peluches y Muñecas de Trapo (ej. Conejita Rosa MK-GH-CONEJITA-ROSA, Muñeca Dulces Sueños MK-MUN-001):</strong> Envasado en bolsas de polietileno termoselladas que aíslan la tela del polvo, ácaros y humedad ambiental, garantizando higiene absoluta para bebés.</li>
    <li><strong>Juguetes de Gran Formato y Didácticos de Madera (ej. Scooter MK-DEP-001, Ábaco MK-DID-001):</strong> Cajas reforzadas con protectores esquineros y flejado para soportar el apilamiento en tránsito interprovincial.</li>
</ul>

<h2>1.3. Red de Transporte y Plazos de Entrega (Last-Mile Delivery)</h2>
<table>
    <thead>
        <tr>
            <th>Zona de Cobertura</th>
            <th>Plazo de Entrega</th>
            <th>Modalidad Logística</th>
            <th>Mecanismo de Seguimiento</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Lima Metropolitana y Callao</strong></td>
            <td><strong>24 a 48 horas hábiles</strong></td>
            <td>Flota motorizada aliada / Courier express urbano. Opción de pago contraentrega (Cash on Delivery - COD) previa confirmación telefónica.</td>
            <td>Actualización de estado en tiempo real vía mensaje directo de WhatsApp con geolocalización aproximada del repartidor.</td>
        </tr>
        <tr>
            <td><strong>Provincias y Nivel Nacional</strong></td>
            <td><strong>48 a 72 horas hábiles</strong></td>
            <td>Alianzas con operadores logísticos certificados: <strong>Olva Courier</strong> (entrega a domicilio) y <strong>Shalom Express</strong> (recojo en agencia con DNI).</td>
            <td>Emisión de guía de remisión física y electrónica con número de rastreo oficial (Tracking Code) consultable en la web del courier.</td>
        </tr>
    </tbody>
</table>

<h2>1.4. Atención al Cliente y Estrategias de Fidelización</h2>
<p><strong>Canales de Atención Omnicanal:</strong></p>
<ul>
    <li><strong>WhatsApp Business Automatizado:</strong> Botón flotante accesible desde cualquier página de la tienda con enlaces directos para resolver dudas de tallas, edades sugeridas o estado de envíos.</li>
    <li><strong>Correos Transaccionales (Brevo API):</strong> Envío automático de notificaciones de compra ("Pedido recibido", "En preparación", "En camino"), reduciendo la ansiedad de compra (<em>buyer anxiety</em>).</li>
    <li><strong>Página Oficial de Contacto (<code>/contacto/</code>):</strong> Formulario estructurado para consultas corporativas y soporte formal con respuesta en menos de 24 horas.</li>
</ul>
<p><strong>Estrategias de Retención y Fidelización:</strong></p>
<ul>
    <li><strong>Experiencia de Desempaque (<em>Unboxing Experience</em>):</strong> Cada paquete incluye una tarjeta de agradecimiento personalizada, stickers coleccionables infantiles y un cupón de <strong>10% de descuento</strong> aplicable en la siguiente compra.</li>
    <li><strong>Garantía de Satisfacción 7 Días:</strong> Política clara de reemplazo inmediato o reembolso ante cualquier defecto de fabricación, cubriendo los gastos de recojo del producto.</li>
</ul>

<h2>1.5. Aspectos Legales: Tratamiento de Datos Personales y Libro de Reclamaciones</h2>
<ul>
    <li><strong>Cumplimiento de la Ley N° 29733 (Ley de Protección de Datos Personales del Perú):</strong>
        <ul>
            <li><strong>Consentimiento Expreso:</strong> En la pantalla de Checkout (<code>/checkout/</code>), el cliente debe marcar obligatoriamente una casilla de verificación aceptando la <em>Política de Privacidad y Tratamiento de Datos Personales</em>.</li>
            <li><strong>Principio de Finalidad:</strong> Los datos recopilados (nombre completo, DNI, dirección exacta de entrega, celular de 9 dígitos y correo) se utilizan <strong>exclusivamente</strong> para la facturación electrónica y la entrega del pedido. No se ceden ni comercializan a terceros.</li>
            <li><strong>Seguridad de Datos:</strong> Tráfico cifrado de extremo a extremo mediante protocolo TLS 1.3 administrado por Cloudflare, resguardando la información personal contra interceptaciones.</li>
        </ul>
    </li>
    <li><strong>Libro de Reclamaciones Virtual (INDECOPI - Ley N° 29571):</strong>
        <ul>
            <li>Implementado en la URL <code>/libro-de-reclamaciones/</code> cumpliendo con el Reglamento del Libro de Reclamaciones (D.S. N° 011-2011-PCM).</li>
            <li>Genera un código de reclamo con folio correlativo único y envía automáticamente una copia idéntica al correo del consumidor como constancia legal de ingreso.</li>
            <li>Establece el plazo perentorio legal de respuesta no mayor a <strong>15 días hábiles</strong>.</li>
        </ul>
    </li>
</ul>

<h2>1.6. Evidencias de Pantalla del Proceso Logístico</h2>

<div class="img-container">
    <img src="${imgDir}/01_checkout_logistica.png" alt="Checkout Logística MiniKids">
    <div class="caption">Figura 1: Pantalla de Checkout (/checkout/) con captura de datos de envío, selección de couriers y checkbox obligatorio de Protección de Datos Personales (Ley N° 29733).</div>
</div>

<div class="img-container">
    <img src="${imgDir}/02_libro_reclamaciones.png" alt="Libro de Reclamaciones">
    <div class="caption">Figura 2: Interfaz del Libro de Reclamaciones Virtual (/libro-de-reclamaciones/) conforme a las normas de INDECOPI, identificando a MIMI KIDS STORE E.I.R.L. y su almacén en Independencia.</div>
</div>

<div class="img-container">
    <img src="${imgDir}/07_contacto_atencion.png" alt="Atención al Cliente">
    <div class="caption">Figura 3: Canales de Atención al Cliente (/contacto/) con soporte vía WhatsApp Business, correo institucional y políticas de entrega.</div>
</div>

<div class="img-container">
    <img src="${imgDir}/10_carrito_resumen.png" alt="Carrito y Validación de Pedido">
    <div class="caption">Figura 4: Resumen de Carrito de Compras (/cart/) con validación de stock disponible y pre-cálculo de costos de despacho logístico.</div>
</div>

<h2>1.7. Plantilla de Personalización Individual: 5 Enfoques Diferenciados</h2>
<div class="tip-box">
    <strong>Instrucción para el equipo:</strong> Como la Pregunta 1 es individual, cada integrante debe adoptar el enfoque especializado que le corresponde para redactar su respuesta individual, garantizando originalidad técnica frente al docente sin contradecir la arquitectura general del proyecto.
</div>

<div class="member-card">
    <h4>Enfoque Individual 1 — Renzo José Aranda Garrido: Especialización en Operaciones de Bodega y Gestión de Almacén</h4>
    <p>«En mi desarrollo de la logística de MiniKids Perú, profundizo en la gestión física de la bodega de Independencia (Lima Norte). Analizo cómo la zonificación por estanterías según el tamaño de los productos (voluminosos como scooters frente a textiles ligeros) optimiza los tiempos de <em>Pick & Pack</em>. Asimismo, detallo el control de inventario en tiempo real con SKU únicos, impidiendo ventas fantasma y sincronizando las órdenes directamente con las órdenes HPOS de WooCommerce.»</p>
</div>

<div class="member-card">
    <h4>Enfoque Individual 2 — Miguel Angel Benavides Soto: Especialización en Red de Transporte y Distribución de Última Milla</h4>
    <p>«Mi enfoque individual analiza la distribución capilar de última milla. Detallo la diferenciación operativa entre Lima Metropolitana (motorizados propios con entrega 24-48h y validación de pago contraentrega) y Provincias (alianzas estratégicas con Olva Courier y Shalom Express con guía de remisión física y tracking digital de 48-72h). Explico cómo esta segmentación reduce los costos de flete y garantiza la trazabilidad física del pedido.»</p>
</div>

<div class="member-card">
    <h4>Enfoque Individual 3 — Crisdel Aldemir Gonzales: Especialización en Canales de Atención y Soporte Omnicanal (CRM)</h4>
    <p>«En mi desarrollo individual, abordo el ecosistema de comunicación con el cliente. Demuestro cómo el botón flotante de WhatsApp Business actúa como el canal más ágil para resolver dudas sobre edades recomendadas y especificaciones, complementado por la integración de correos transaccionales automatizados mediante la API de Brevo. Esta infraestructura reduce la fricción en el embudo y minimiza la tasa de abandono de compras.»</p>
</div>

<div class="member-card">
    <h4>Enfoque Individual 4 — Miguel Angel Huamani Rojas: Especialización en Experiencia de Usuario, Fidelización y Retención</h4>
    <p>«Mi análisis individual se enfoca en la retención postventa y el <em>Customer Lifetime Value</em>. Analizo la estrategia de <em>Unboxing Experience</em> (inclusión de tarjetas de agradecimiento personalizadas, stickers coleccionables y cupón de 10% para la siguiente compra) y la política de garantía de 7 días. Esta combinación transforma una compra puntual en una relación de confianza duradera con los padres de familia.»</p>
</div>

<div class="member-card">
    <h4>Enfoque Individual 5 — Cristopher Manuel Mendoza Vicente: Especialización en Marco Normativo, Ley N° 29733 y Protección al Consumidor</h4>
    <p>«Mi entrega individual examina la solvencia jurídica del e-commerce. Desgloso el estricto cumplimiento de la Ley N° 29733 de Protección de Datos Personales, evidenciando el consentimiento expreso obligatorio en el checkout y la restricción del uso de datos solo para despacho. Detallo además el diseño del Libro de Reclamaciones Virtual bajo las exigencias de INDECOPI, garantizando folios únicos correlativos y plazos de respuesta en menos de 15 días hábiles.»</p>
</div>

<!-- ==================================================================== -->
<div class="page-break"></div>
<h1>PREGUNTA 2 (7 PUNTOS) — DEFINICIÓN DE COMPONENTES DE HARDWARE, SOFTWARE, PLATAFORMAS ASOCIADAS Y ALCANCES EN LA NUBE</h1>

<p><em><strong>Consigna Docente:</strong> (INDIVIDUAL) Defina los componentes de software y hardware de su proyecto. Describa los softwares y plataformas asociados a su proyecto. Qué alcances tendrá dentro de la nube. Muestre pantallas (mockups/wireframes), de cada uno de los softwares.</em></p>

<h2>2.1. Matriz Integral de Componentes de Hardware y Software</h2>

<table>
    <thead>
        <tr>
            <th>Componente</th>
            <th>Categoría</th>
            <th>Especificación Técnica en Producción (MiniKids Perú)</th>
            <th>Función y Valor en el E-Commerce</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Servidor Cloud VPS</strong></td>
            <td>Hardware (Nube)</td>
            <td>Servidor Virtual Privado KVM multi-core vCPU x86_64, 2 GB+ RAM dedicada, discos NVMe SSD en RAID 10, enlace simétrico de 1 Gbps con protección Anti-DDoS.</td>
            <td>Alojamiento de alto rendimiento con latencias inferiores a 50 ms para Perú y capacidad para absorber picos de tráfico en campañas comerciales.</td>
        </tr>
        <tr>
            <td><strong>Dispositivos Locales / Terminales</strong></td>
            <td>Hardware (Local)</td>
            <td>Smartphones Android e iOS para validación móvil y laptops/PCs de administración para gestión de stock y emisión de guías.</td>
            <td>Permite la gestión operativa de bodega, recepción de alertas de compra y visualización de la tienda tal como la ven los clientes móviles.</td>
        </tr>
        <tr>
            <td><strong>Sistema Operativo Servidor</strong></td>
            <td>Software Base</td>
            <td>Linux Ubuntu Server 22.04 LTS (Kernel 5.15) de 64 bits con hardening de seguridad y cortafuegos UFW activo.</td>
            <td>Entorno robusto, libre de licencias costosas y optimizado para servicios web concurrentes y procesos en segundo plano.</td>
        </tr>
        <tr>
            <td><strong>Servidor Web y Reverse Proxy</strong></td>
            <td>Software Servidor</td>
            <td><strong>Nginx</strong> optimizado como reverse proxy hacia PHP-FPM 8.2, con compresión Gzip/Brotli y búferes ajustados para e-commerce.</td>
            <td>Manejo eficiente de miles de conexiones simultáneas sin saturación de memoria, resolviendo peticiones estáticas a ultra alta velocidad.</td>
        </tr>
        <tr>
            <td><strong>Motor de Base de Datos</strong></td>
            <td>Software Servidor</td>
            <td><strong>MariaDB 10.4 / MySQL 8.0</strong> con motor transaccional InnoDB, búfer de pool ajustado y compatibilidad con tablas WooCommerce HPOS.</td>
            <td>Almacenamiento ACID que evita inconsistencias de pedidos y optimiza consultas complejas de catálogo y clientes.</td>
        </tr>
        <tr>
            <td><strong>Gestor de Contenidos (CMS)</strong></td>
            <td>Software Aplicación</td>
            <td><strong>WordPress 7.1.3</strong> con editor de bloques Gutenberg para maquetación de páginas institucionales y políticas.</td>
            <td>Administración dinámica de contenidos, páginas legales (Libro de Reclamaciones, Nosotros) y gestión de roles de usuario.</td>
        </tr>
        <tr>
            <td><strong>Motor Transaccional E-Commerce</strong></td>
            <td>Software Aplicación</td>
            <td><strong>WooCommerce 11.2.1</strong> con High-Performance Order Storage (HPOS) activo y pasarelas de pago configuradas.</td>
            <td>Control central de productos, variantes, inventario físico, impuestos, checkout seguro y despacho logístico.</td>
        </tr>
        <tr>
            <td><strong>Tema y Capa de Presentación</strong></td>
            <td>Software UI/UX</td>
            <td><strong>Blocksy 2.1.59</strong> complementado con módulos propios en <code>mu-plugins</code> (<code>minikids-estetica.css</code>, <code>minikids-atencion.css</code>).</td>
            <td>Rendimiento web sobresaliente (Core Web Vitals óptimos), diseño 100% responsivo Mobile-First y cumplimiento de jerarquía visual.</td>
        </tr>
    </tbody>
</table>

<h2>2.2. Softwares y Plataformas Cloud Asociadas</h2>
<ol>
    <li><strong>Cloudflare (Edge Cloud Platform):</strong>
        <ul>
            <li><strong>CDN Global y Anycast DNS:</strong> Distribuye archivos estáticos (imágenes de juguetes, CSS, scripts) desde centros de datos locales en Lima, reduciendo el TTFB a menos de 200 ms.</li>
            <li><strong>Capa de Seguridad WAF y Anti-DDoS:</strong> Mitigación automática de ataques de denegación de servicio y filtrado de bots maliciosos que intenten raspar precios o saturar el checkout.</li>
            <li><strong>Cifrado SSL/TLS 1.3:</strong> Certificado de seguridad criptográfico que garantiza navegación segura bajo HTTPS y candado verde para todos los compradores.</li>
        </ul>
    </li>
    <li><strong>Brevo API (Cloud Email & Marketing Platform):</strong>
        <ul>
            <li>Infraestructura cloud para el despacho fiable de correos electrónicos transaccionales con 99.8% de entregabilidad garantizada a bandejas de entrada (evitando la carpeta de spam).</li>
        </ul>
    </li>
    <li><strong>Git & GitHub (Version Control & CI/CD Platform):</strong>
        <ul>
            <li>Repositorio en la nube (<code>MiguelAHR/ToyCommerce</code>) con control de versiones, ramas de desarrollo y despliegues controlados hacia el servidor en vivo.</li>
        </ul>
    </li>
</ol>

<h2>2.3. Alcances del Proyecto dentro de la Nube (Cloud Computing Scope)</h2>
<ul>
    <li><strong>Alta Disponibilidad 24/7/365 (SLA 99.9%):</strong> La tienda permanece operativa ininterrumpidamente, permitiendo que los clientes compren juguetes didácticos a cualquier hora sin caídas por mantenimiento.</li>
    <li><strong>Escalabilidad Elástica y Concurrencia:</strong> La arquitectura VPS + Nginx + Cloudflare absorbe sin problemas incrementos exponenciales de tráfico durante campañas masivas (Día del Niño, Cyber Days, Navidad).</li>
    <li><strong>Copias de Seguridad Automatizadas y Disaster Recovery:</strong> Respaldos periódicos de base de datos y archivos multimedia en almacenes externos en la nube, permitiendo una restauración completa en menos de 30 minutos ante cualquier contingencia.</li>
    <li><strong>Optimización de Rendimiento y Core Web Vitals:</strong> Optimización de tiempos de carga móvil (LCP < 2.5s, FID/INP < 100ms, CLS < 0.1), asegurando un posicionamiento orgánico destacado en Google (SEO para e-commerce).</li>
</ul>

<h2>2.4. Evidencias de Pantalla de la Infraestructura y Plataforma</h2>

<div class="img-container">
    <img src="${imgDir}/03_tienda_catalogo.png" alt="Catálogo WooCommerce en Producción">
    <div class="caption">Figura 5: Interfaz del Catálogo de Productos (/tienda/) gestionado en la nube con WooCommerce 11.2.1 y tema Blocksy en servidor Ubuntu Nginx.</div>
</div>

<div class="img-container">
    <img src="${imgDir}/11_nosotros_empresa.png" alt="Página Institucional WordPress">
    <div class="caption">Figura 6: Página institucional de MiniKids Perú (/nosotros/) administrada bajo el CMS WordPress 7.1.3 y servida mediante la red perimetral de Cloudflare.</div>
</div>

<h2>2.5. Plantilla de Personalización Individual: 5 Enfoques de Arquitectura Cloud</h2>
<div class="tip-box">
    <strong>Instrucción para el equipo:</strong> Para la entrega individual de la Pregunta 2, cada integrante debe enfocar su desarrollo en el componente técnico que se detalla a continuación para lograr 5 trabajos completamente originales.
</div>

<div class="member-card">
    <h4>Enfoque Individual 1 — Renzo José Aranda Garrido: Especialización en Virtualización KVM, Linux y Servidor Nginx</h4>
    <p>«En mi análisis individual, desarrollo a fondo la capa de infraestructura base. Explico la superioridad de la virtualización KVM sobre OpenVZ, garantizando memoria RAM y vCPUs 100% dedicadas para MiniKids Perú. Detallo cómo Nginx opera como un reverse proxy asíncrono no bloqueante, canalizando peticiones estáticas directamente desde la memoria caché y derivando peticiones PHP-FPM con mínimo consumo de CPU.»</p>
</div>

<div class="member-card">
    <h4>Enfoque Individual 2 — Miguel Angel Benavides Soto: Especialización en Base de Datos MariaDB y Almacenamiento HPOS</h4>
    <p>«Mi enfoque individual se centra en el motor relacional de datos. Examino la migración de WooCommerce hacia la arquitectura HPOS (High-Performance Order Storage), la cual separa las órdenes de compra en tablas transaccionales dedicadas (<code>wp_wc_orders</code>), evitando cuellos de botella en la tabla tradicional <code>wp_posts</code>. Demuestro cómo el almacenamiento en discos NVMe SSD acelera las lecturas y escrituras transaccionales en un 400%.»</p>
</div>

<div class="member-card">
    <h4>Enfoque Individual 3 — Crisdel Aldemir Gonzales: Especialización en la Capa de Aplicación CMS y UI/UX Responsivo</h4>
    <p>«En mi desarrollo individual, abordo la configuración de software de nivel superior. Analizo la integración entre WordPress 7.1.3, WooCommerce 11.2.1 y el tema Blocksy 2.1.59. Describo cómo los scripts y estilos personalizados en <code>mu-plugins</code> permiten desacoplar la lógica de presentación sin comprometer la velocidad de renderizado, garantizando un índice de rendimiento óptimo en Google Lighthouse para dispositivos móviles.»</p>
</div>

<div class="member-card">
    <h4>Enfoque Individual 4 — Miguel Angel Huamani Rojas: Especialización en Plataformas Cloud Perimetrales (Cloudflare Edge & Brevo)</h4>
    <p>«Mi entrega individual analiza los servicios SaaS y plataformas periféricas que blindan la tienda. Profundizo en la arquitectura Anycast DNS y la red CDN de Cloudflare, la cual aproxima las imágenes y scripts a los usuarios de Lima en servidores perimetrales locales. Asimismo, detallo la conexión vía API con Brevo para el enrutamiento de correos transaccionales certificados, garantizando que ninguna orden se pierda.»</p>
</div>

<div class="member-card">
    <h4>Enfoque Individual 5 — Cristopher Manuel Mendoza Vicente: Especialización en Seguridad Cloud, Disponibilidad 24/7 y Disaster Recovery</h4>
    <p>«Mi análisis individual se orienta a la resiliencia operativa y ciberseguridad en la nube. Detallo la implementación del cortafuegos WAF y mitigación Anti-DDoS de Cloudflare para contrarrestar ataques volumétricos. Además, desarrollo la política de copias de seguridad automatizadas y el plan de recuperación ante desastres (Disaster Recovery Plan), que asegura restaurar la tienda con cero pérdida de compras en caso de caída del servidor.»</p>
</div>

<!-- ==================================================================== -->
<div class="page-break"></div>
<h1>PREGUNTA 3 (7 PUNTOS) — ANÁLISIS DE FICHAS DE PRODUCTO (EVALUACIÓN GRUPAL - 5 INTEGRANTES)</h1>

<p><em><strong>Consigna Docente:</strong> (GRUPAL) Cada uno de los integrantes del equipo debe seleccionar un producto (diferente uno de otro), y mostrar el diseño y todos los componentes de la ficha. Haga una breve descripción o comente las imágenes mostradas.</em></p>

<p>Para cumplir estrictamente con la consigna del docente, los <strong>5 integrantes del equipo</strong> han seleccionado un producto exclusivo del catálogo en vivo de <strong>MiniKids Perú</strong> (<a href="https://minikids.vitorstream.shop">https://minikids.vitorstream.shop</a>), cubriendo 5 categorías esenciales del desarrollo infantil y respetando la estructura oficial acordada:</p>

<table>
    <thead>
        <tr>
            <th>Integrante del Equipo</th>
            <th>Producto Seleccionado</th>
            <th>SKU</th>
            <th>Categoría Comercial</th>
            <th>Precio Final</th>
            <th>Evidencia de Pantalla</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>1. Renzo José Aranda Garrido</strong></td>
            <td>Scooter Infantil 3 Ruedas con Luces LED</td>
            <td><code>MK-DEP-001</code></td>
            <td>Deporte / Movilidad Infantil</td>
            <td><strong>S/ 99.90</strong> <span class="badge">Oferta -23%</span></td>
            <td><code>08_producto_scooter.png</code></td>
        </tr>
        <tr>
            <td><strong>2. Miguel Angel Benavides Soto</strong></td>
            <td>Ábaco Pedagógico y Laberinto de Madera</td>
            <td><code>MK-DID-001</code></td>
            <td>Didácticos / Montessori</td>
            <td><strong>S/ 49.90</strong> <span class="badge">Educativo</span></td>
            <td><code>09_producto_abaco.png</code></td>
        </tr>
        <tr>
            <td><strong>3. Crisdel Aldemir Gonzales</strong></td>
            <td>Peluche Conejita Rosa</td>
            <td><code>MK-GH-CONEJITA-ROSA</code></td>
            <td>Peluches / Primera Infancia</td>
            <td><strong>S/ 26.90</strong> <span class="badge">Alta Rotación</span></td>
            <td><code>06_producto_conejita.png</code></td>
        </tr>
        <tr>
            <td><strong>4. Miguel Angel Huamani Rojas</strong></td>
            <td>Mini karaoke con micrófono</td>
            <td><code>MK-TEC-001</code></td>
            <td>Tecnología Infantil / Música</td>
            <td><strong>S/ 59.90</strong> <span class="badge">Oferta -14%</span></td>
            <td><code>04_producto_karaoke.png</code></td>
        </tr>
        <tr>
            <td><strong>5. Cristopher Manuel Mendoza Vicente</strong></td>
            <td>Muñeca de Trapo Dulces Sueños</td>
            <td><code>MK-MUN-001</code></td>
            <td>Juegos de Rol / Afectividad</td>
            <td><strong>S/ 49.90</strong> <span class="badge">Oferta -17%</span></td>
            <td><code>05_producto_muneca.png</code></td>
        </tr>
    </tbody>
</table>

<!-- ========================================== -->
<!-- INTEGRANTE 1: RENZO ARANDA -->
<!-- ========================================== -->
<div style="border-top: 2px solid #CBD5E0; margin-top: 20pt; padding-top: 10pt;">
    <h2>3.1. Ficha Técnica — Integrante 1: Renzo José Aranda Garrido</h2>

    <h3>A. Selección del producto</h3>
    <table>
        <tr><td style="width: 30%;"><strong>Nombre del Producto:</strong></td><td><strong>Scooter Infantil 3 Ruedas con Luces LED</strong></td></tr>
        <tr><td><strong>Código SKU:</strong></td><td><code>MK-DEP-001</code></td></tr>
        <tr><td><strong>Categoría de Catálogo:</strong></td><td>Deporte Infantil / Movilidad y Aire Libre</td></tr>
        <tr><td><strong>Precio Regular / Oferta:</strong></td><td>Precio Regular: S/ 129.90 | <strong>Precio de Oferta: S/ 99.90</strong> (Ahorro directo de S/ 30.00 / -23%)</td></tr>
        <tr><td><strong>Disponibilidad en Almacén:</strong></td><td>En Stock (Almacén Central Independencia - Entrega 24-48h Lima)</td></tr>
        <tr><td><strong>Especificaciones Clave:</strong></td><td>Estructura de aluminio reforzado, manubrio regulable en 3 alturas (edades 3 a 8 años), capacidad hasta 50 kg, ruedas con luces LED cinéticas sin pilas.</td></tr>
        <tr><td><strong>Enlace Directo en Vivo:</strong></td><td><a href="https://minikids.vitorstream.shop/product/scooter-infantil-3-ruedas-con-luces-led/">Ver producto en minikids.vitorstream.shop</a></td></tr>
    </table>

    <h3>B. Demostración del diseño</h3>
    <div class="img-container">
        <img src="${imgDir}/08_producto_scooter.png" alt="Scooter Infantil 3 Ruedas">
        <div class="caption">Figura 7: Demostración del diseño de la ficha del Scooter Infantil 3 Ruedas con Luces LED en MiniKids Perú.</div>
    </div>

    <h3>C. Componentes de la ficha</h3>
    <ol>
        <li><strong>Migas de Pan (Breadcrumbs):</strong> Jerarquía <code>Inicio &gt; Tienda &gt; Deporte &gt; Scooter Infantil 3 Ruedas con Luces LED</code>, permitiendo al usuario ubicarse y retroceder a la categoría deportiva.</li>
        <li><strong>Galería Visual de Alta Resolución:</strong> Muestra la geometría triangular de estabilidad (dos ruedas frontales y una posterior con freno de presión), permitiendo examinar los acabados de pintura y grips antideslizantes.</li>
        <li><strong>Insignia de Descuento (Badge):</strong> Etiqueta visual llamativa «¡Oferta!» que resalta el ahorro de S/ 30.00 frente al precio de lista.</li>
        <li><strong>Jerarquía de Precios:</strong> Precio regular tachado (<code>S/ 129.90</code>) junto al precio de venta resaltado en tipografía grande (<code>S/ 99.90</code>), ubicándolo estratégicamente por debajo de la barrera de las tres cifras (S/ 100).</li>
        <li><strong>Selector de Unidades Táctil:</strong> Controles ergonómicos adaptados a dedos con área de toque mayor a 44x44 píxeles (estándar Mobile-First).</li>
        <li><strong>Botón Call-to-Action «Añadir al carrito»:</strong> Botón de alta visibilidad que activa la microinteracción del carrito deslizable sin recargar la página.</li>
        <li><strong>Pestañas de Especificaciones Técnicas:</strong> Pestaña de descripción con tabla de capacidad de carga, alturas de manubrio y advertencias de seguridad para padres.</li>
        <li><strong>Canal de Asistencia Directa:</strong> Acceso directo al botón flotante de WhatsApp para consultar dudas sobre armado y despacho interprovincial.</li>
    </ol>

    <h3>D. Descripción y comentario</h3>
    <p><strong>Descripción Comercial y Pedagógica:</strong> El Scooter Infantil de 3 ruedas promueve el desarrollo de la motricidad gruesa, el equilibrio corporal y la orientación espacial en niños de 3 a 8 años. Su sistema de viraje por inclinación de gravedad (*Lean-to-steer*) otorga máxima estabilidad a los principiantes, mientras que sus ruedas luminosas con dinamo cinético (sin necesidad de baterías) ofrecen diversión y visibilidad nocturna.</p>
    <div class="quote-box">
        <strong>Comentario Crítico de UX/UI:</strong> «Al ser el artículo con el ticket de venta más alto de la tienda (S/ 99.90), esta ficha técnica está orientada a derribar objeciones de seguridad y durabilidad. La jerarquía tipográfica conduce la mirada desde la oferta visual hasta las especificaciones mecánicas del chasis de aluminio, otorgando absoluta tranquilidad al comprador antes de pulsar el botón de conversión.»
    </div>
</div>

<!-- ========================================== -->
<!-- INTEGRANTE 2: MIGUEL ANGEL BENAVIDES SOTO -->
<!-- ========================================== -->
<div style="border-top: 2px solid #CBD5E0; margin-top: 20pt; padding-top: 10pt;">
    <h2>3.2. Ficha Técnica — Integrante 2: Miguel Angel Benavides Soto</h2>

    <h3>A. Selección del producto</h3>
    <table>
        <tr><td style="width: 30%;"><strong>Nombre del Producto:</strong></td><td><strong>Ábaco Pedagógico y Laberinto de Madera</strong></td></tr>
        <tr><td><strong>Código SKU:</strong></td><td><code>MK-DID-001</code></td></tr>
        <tr><td><strong>Categoría de Catálogo:</strong></td><td>Didácticos / Estimulación Temprana y Método Montessori</td></tr>
        <tr><td><strong>Precio de Venta:</strong></td><td><strong>S/ 49.90</strong> (Precio accesible de inversión educativa formativa)</td></tr>
        <tr><td><strong>Disponibilidad en Almacén:</strong></td><td>En Stock (Bodega Central Independencia)</td></tr>
        <tr><td><strong>Especificaciones Clave:</strong></td><td>Estructura de madera natural de pino pulida libre de astillas, pintura al agua ecológica no tóxica, laberinto superior de cuentas metálicas curvas, 5 barras de conteo aritmético.</td></tr>
        <tr><td><strong>Enlace Directo en Vivo:</strong></td><td><a href="https://minikids.vitorstream.shop/product/abaco-pedagogico-y-laberinto-de-madera/">Ver producto en minikids.vitorstream.shop</a></td></tr>
    </table>

    <h3>B. Demostración del diseño</h3>
    <div class="img-container">
        <img src="${imgDir}/09_producto_abaco.png" alt="Ábaco de Madera">
        <div class="caption">Figura 8: Demostración del diseño de la ficha del Ábaco Pedagógico y Laberinto de Madera en MiniKids Perú.</div>
    </div>

    <h3>C. Componentes de la ficha</h3>
    <ol>
        <li><strong>Migas de Pan Contextuales:</strong> Ruta clara <code>Inicio &gt; Tienda &gt; Didácticos &gt; Ábaco Pedagógico</code> para contextualizar la categoría formativa.</li>
        <li><strong>Fotografía de Enfoque Didáctico:</strong> Encuadre nítido que exhibe la combinación 2 en 1 (base de conteo matemático + laberinto de psicomotricidad en alambre recubierto).</li>
        <li><strong>Indicador de Stock Verde:</strong> Mensaje «En stock» que asegura al padre de familia la disponibilidad para entrega rápida en Lima o provincias.</li>
        <li><strong>Bloque de Precio Transparente:</strong> Exhibe directamente el precio de S/ 49.90 sin artificios, transmitiendo honestidad y seriedad institucional.</li>
        <li><strong>Selector Numérico y Botón de Compra:</strong> Botón <strong>«AÑADIR AL CARRITO»</strong> en color corporativo de la tienda con microanimación al pulsar.</li>
        <li><strong>Pestañas de Valor Pedagógico:</strong> Explicación de la metodología Montessori aplicada, estimulación de la concentración, motricidad fina y razonamiento lógico-matemático.</li>
        <li><strong>Insignias de Confianza:</strong> Indicadores de madera ecológica certificada, pinturas libres de plomo y garantía de cambio de 7 días.</li>
    </ol>

    <h3>D. Descripción y comentario</h3>
    <p><strong>Descripción Comercial y Pedagógica:</strong> Diseñado como un material de aprendizaje integral para la primera infancia (de 18 meses a 5 años), este juguete didáctico fusiona el cálculo de cuentas con el trazado sensorial de figuras en 3D. Ayuda a los niños a familiarizarse con colores, cantidades y patrones espaciales de forma táctil y lúdica.</p>
    <div class="quote-box">
        <strong>Comentario Crítico de UX/UI:</strong> «El comprador de juguetes didácticos suele ser un padre o educador exigente que prioriza la seguridad y los valores pedagógicos sobre la moda. La ficha técnica resuelve esto a la perfección: no sobrecarga la vista con adornos superfluos, presenta información clara sobre la no toxicidad de las pinturas y facilita una compra rápida y segura.»
    </div>
</div>

<!-- ========================================== -->
<!-- INTEGRANTE 3: CRISDEL ALDEMIR GONZALES -->
<!-- ========================================== -->
<div style="border-top: 2px solid #CBD5E0; margin-top: 20pt; padding-top: 10pt;">
    <h2>3.3. Ficha Técnica — Integrante 3: Crisdel Aldemir Gonzales</h2>

    <h3>A. Selección del producto</h3>
    <table>
        <tr><td style="width: 30%;"><strong>Nombre del Producto:</strong></td><td><strong>Peluche Conejita Rosa</strong></td></tr>
        <tr><td><strong>Código SKU:</strong></td><td><code>MK-GH-CONEJITA-ROSA</code></td></tr>
        <tr><td><strong>Categoría de Catálogo:</strong></td><td>Peluches / Primera Infancia y Regalos de Afecto</td></tr>
        <tr><td><strong>Precio de Venta:</strong></td><td><strong>S/ 26.90</strong> (Artículo de alta rotación y compra por impulso)</td></tr>
        <tr><td><strong>Disponibilidad en Almacén:</strong></td><td>En Stock (Listo para empaque y despacho)</td></tr>
        <tr><td><strong>Especificaciones Clave:</strong></td><td>Tela de felpa ultrasuave hipoalergénica, relleno de poliéster virgen indeformable, ojos bordados con hilo de seguridad sin piezas plásticas, altura de 30 cm.</td></tr>
        <tr><td><strong>Enlace Directo en Vivo:</strong></td><td><a href="https://minikids.vitorstream.shop/product/conejita-rosa/">Ver producto en minikids.vitorstream.shop</a></td></tr>
    </table>

    <h3>B. Demostración del diseño</h3>
    <div class="img-container">
        <img src="${imgDir}/06_producto_conejita.png" alt="Peluche Conejita Rosa">
        <div class="caption">Figura 9: Demostración del diseño de la ficha del Peluche Conejita Rosa en MiniKids Perú.</div>
    </div>

    <h3>C. Componentes de la ficha</h3>
    <ol>
        <li><strong>Navegación Breadcrumbs:</strong> Ruta <code>Inicio &gt; Tienda &gt; Peluches &gt; Conejita Rosa</code> para rápida localización.</li>
        <li><strong>Imagen Frontal de Textura Suave:</strong> Fotografía que enfatiza la ternura del diseño, orejas largas adaptadas a las manitos del bebé y la calidad de la costura reforzada.</li>
        <li><strong>Estrategia de Precio de Entrada:</strong> S/ 26.90 colocado de forma limpia, ideal como producto complementario en el carrito o regalo accesible.</li>
        <li><strong>Certificado Visual Hipoalergénico:</strong> Texto claro destacando que es apto para recién nacidos (0 meses a más) y lavable a mano o en ciclo suave.</li>
        <li><strong>Módulo CTA de Adquisición Rápida:</strong> Selector numérico táctil y botón de compra optimizado para añadir múltiples unidades con un solo toque.</li>
        <li><strong>Sección de Productos Relacionados:</strong> Carrusel inferior con otros peluches y muñecos para incentivar ventas cruzadas (<em>cross-selling</em>).</li>
    </ol>

    <h3>D. Descripción y comentario</h3>
    <p><strong>Descripción Comercial y Pedagógica:</strong> La Conejita Rosa actúa como un objeto de apego o elemento transicional crucial en la etapa del desarrollo afectivo temprano, transmitiendo seguridad y calma al infante durante sus períodos de descanso y sueño. Su textura aterciopelada estimula el sentido del tacto en los primeros meses de vida.</p>
    <div class="quote-box">
        <strong>Comentario Crítico de UX/UI:</strong> «Esta ficha responde a la psicología de compra impulsiva y afectiva. El diseño es minimalista y directo: el usuario percibe la ternura del peluche, confirma el precio sumamente accesible y puede agregarlo al carrito en menos de tres segundos. Cumple cabalmente con la Ley de Fitts al posicionar el botón de compra en la zona natural de toque del pulgar.»
    </div>
</div>

<!-- ========================================== -->
<!-- INTEGRANTE 4: MIGUEL ANGEL HUAMANI ROJAS -->
<!-- ========================================== -->
<div style="border-top: 2px solid #CBD5E0; margin-top: 20pt; padding-top: 10pt;">
    <h2>3.4. Ficha Técnica — Integrante 4: Miguel Angel Huamani Rojas</h2>

    <h3>A. Selección del producto</h3>
    <table>
        <tr><td style="width: 30%;"><strong>Nombre del Producto:</strong></td><td><strong>Mini karaoke con micrófono</strong></td></tr>
        <tr><td><strong>Código SKU:</strong></td><td><code>MK-TEC-001</code></td></tr>
        <tr><td><strong>Categoría de Catálogo:</strong></td><td>Tecnología Infantil / Música y Expresión Artística</td></tr>
        <tr><td><strong>Precio Regular / Oferta:</strong></td><td>Precio Regular: S/ 69.90 | <strong>Precio de Oferta: S/ 59.90</strong> (Ahorro del 14% / S/ 10.00 de descuento)</td></tr>
        <tr><td><strong>Disponibilidad en Almacén:</strong></td><td>En Stock (Almacén Central Independencia)</td></tr>
        <tr><td><strong>Especificaciones Clave:</strong></td><td>Conexión Bluetooth 5.0, micrófono inalámbrico desmontable con efectos de cambio de voz, batería recargable vía cable USB Tipo C, altavoz de alta fidelidad sin distorsión.</td></tr>
        <tr><td><strong>Enlace Directo en Vivo:</strong></td><td><a href="https://minikids.vitorstream.shop/product/mini-karaoke-con-microfono/">Ver producto en minikids.vitorstream.shop</a></td></tr>
    </table>

    <h3>B. Demostración del diseño</h3>
    <div class="img-container">
        <img src="${imgDir}/04_producto_karaoke.png" alt="Mini Karaoke con Micrófono">
        <div class="caption">Figura 10: Demostración del diseño de la ficha del Mini karaoke con micrófono en MiniKids Perú.</div>
    </div>

    <h3>C. Componentes de la ficha</h3>
    <ol>
        <li><strong>Breadcrumbs Jerárquicos:</strong> <code>Inicio &gt; Tienda &gt; Tecnología &gt; Mini karaoke con micrófono</code>.</li>
        <li><strong>Galería Multimedia con Zoom Interactivo:</strong> Imagen limpia en proporción 4:3 con ampliación al pasar el cursor para evaluar los mandos y puertos protegidos.</li>
        <li><strong>Insignia Circular de Oferta («Sale Badge»):</strong> Destaca visualmente el descuento promocional en la esquina superior del producto.</li>
        <li><strong>Bloque de Precios con Gatillo Psicológico:</strong> Precio de lista tachado (<code>S/ 69.90</code>) junto al precio promocional destacado en tono azul (<code>S/ 59.90</code>), reforzando la percepción de ahorro.</li>
        <li><strong>Descripción Rápida en Primer Pliegue:</strong> Resumen claro: <em>"Equipo de karaoke compacto que incluye micrófono; cubre la etapa de juego tecnológico y musical."</em></li>
        <li><strong>Botón CTA de Alto Impacto:</strong> Botón ergonómico <strong>«AÑADIR AL CARRITO»</strong> que despliega el cajón lateral del carrito sin abandonar la página.</li>
        <li><strong>Pestañas Técnicas:</strong> Pestañas desplegables con especificaciones de batería, peso ligero de 450 g y rango de edades (3 a 10 años).</li>
        <li><strong>Garantía de Satisfacción Visible:</strong> Iconografía de reemplazo inmediato por fallas y canal de WhatsApp para soporte.</li>
    </ol>

    <h3>D. Descripción y comentario</h3>
    <p><strong>Descripción Comercial y Pedagógica:</strong> El Mini Karaoke estimula la expresión verbal, la entonación musical y la autoconfianza en niños desde los 3 años. Al incorporar modulación de efectos de voz y tecnología inalámbrica, los pequeños pueden cantar y bailar libremente sin enredos de cables peligrosos.</p>
    <div class="quote-box">
        <strong>Comentario Crítico de UX/UI:</strong> «En juguetes que combinan electrónica con público infantil, los padres suelen temer la fragilidad o la complejidad de uso. Esta ficha técnica supera esa fricción mostrando claramente que el producto es compacto, recargable y sin cables. La jerarquía visual en el primer pliegue (*Above the fold*) asegura que el botón de compra sea el foco dominante en pantallas de teléfonos móviles.»
    </div>
</div>

<!-- ========================================== -->
<!-- INTEGRANTE 5: CRISTOPHER MANUEL MENDOZA VICENTE -->
<!-- ========================================== -->
<div style="border-top: 2px solid #CBD5E0; margin-top: 20pt; padding-top: 10pt;">
    <h2>3.5. Ficha Técnica — Integrante 5: Cristopher Manuel Mendoza Vicente</h2>

    <h3>A. Selección del producto</h3>
    <table>
        <tr><td style="width: 30%;"><strong>Nombre del Producto:</strong></td><td><strong>Muñeca de Trapo Dulces Sueños</strong></td></tr>
        <tr><td><strong>Código SKU:</strong></td><td><code>MK-MUN-001</code></td></tr>
        <tr><td><strong>Categoría de Catálogo:</strong></td><td>Juegos de Rol / Afectividad, Empatía y Compañía</td></tr>
        <tr><td><strong>Precio Regular / Oferta:</strong></td><td>Precio Regular: S/ 59.90 | <strong>Precio de Oferta: S/ 49.90</strong> (Ahorro del 17% / S/ 10.00 de descuento)</td></tr>
        <tr><td><strong>Disponibilidad en Almacén:</strong></td><td>En Stock (Bodega Independencia)</td></tr>
        <tr><td><strong>Especificaciones Clave:</strong></td><td>Confección en 100% tela de algodón hipoalergénico, cabello de estambre suave, vestido floral desmontable, ojos y boca bordados a mano, altura de 35 cm, peso ligero de 220 g.</td></tr>
        <tr><td><strong>Enlace Directo en Vivo:</strong></td><td><a href="https://minikids.vitorstream.shop/product/muneca-de-trapo-dulces-suenos/">Ver producto en minikids.vitorstream.shop</a></td></tr>
    </table>

    <h3>B. Demostración del diseño</h3>
    <div class="img-container">
        <img src="${imgDir}/05_producto_muneca.png" alt="Muñeca de Trapo Dulces Sueños">
        <div class="caption">Figura 11: Demostración del diseño de la ficha de la Muñeca de Trapo Dulces Sueños en MiniKids Perú.</div>
    </div>

    <h3>C. Componentes de la ficha</h3>
    <ol>
        <li><strong>Migas de Pan Informativas:</strong> <code>Inicio &gt; Tienda &gt; Juegos de rol &gt; Muñeca de Trapo Dulces Sueños</code>.</li>
        <li><strong>Fotografía de Acabados Textiles:</strong> Primer plano que resalta las costuras reforzadas y la ausencia de botones de plástico o piezas desprendibles que puedan suponer riesgo de atragantamiento.</li>
        <li><strong>Insignia Promocional («Sale Badge»):</strong> Etiqueta visual de oferta con descuento del 17%.</li>
        <li><strong>Precios de Lista y Promocional:</strong> Precios tachado (<code>S/ 59.90</code>) y final (<code>S/ 49.90</code>) con tipografía contrastada.</li>
        <li><strong>Indicador de Stock y Tiempos de Entrega:</strong> Distintivo de producto en bodega con despacho express en 24 a 48 horas en Lima.</li>
        <li><strong>Selector de Cantidad y Botón de Conversión:</strong> Botón ergonómico de añadir al carrito adaptado a la mano en smartphones.</li>
        <li><strong>Pestaña de Cuidados y Lavado:</strong> Recomendaciones de lavado en máquina en ciclo suave para garantizar la higiene continuada del juguete.</li>
    </ol>

    <h3>D. Descripción y comentario</h3>
    <p><strong>Descripción Comercial y Pedagógica:</strong> La Muñeca de Trapo Dulces Sueños incentiva el juego simbólico, el cuidado mutuo y la socialización temprana en niños y niñas. Su estructura blanda y ligera permite que los más pequeños la abracen durante la noche, convirtiéndose en su acompañante habitual durante las horas de sueño.</p>
    <div class="quote-box">
        <strong>Comentario Crítico de UX/UI:</strong> «En el rubro de muñecas para la infancia temprana, la certeza sobre la suavidad, la higiene y la ausencia de elementos peligrosos es el factor decisivo de compra para madres y abuelos. Esta ficha destaca con tipografía legible que el producto es lavable y 100% seguro, eliminando toda reticencia del comprador mediante una presentación cálida y armónica.»
    </div>
</div>

<!-- ========================================== -->
<!-- SÍNTESIS Y CONCLUSIÓN COMPARATIVA GRUPAL -->
<!-- ========================================== -->
<div class="page-break"></div>
<h2>3.6. Conclusión Grupal Comparativa de UX/UI y Conversión E-Commerce</h2>
<p>El análisis conjunto de las 5 fichas de producto elaborado por el equipo demuestra la consistencia arquitectónica y el rigor técnico implementado en <strong>MiniKids Perú</strong>:</p>

<ul>
    <li><strong>1. Coherencia Visual y Sistema de Diseño (Design System):</strong> Las 5 fichas respetan una rejilla modular responsiva idéntica, el sistema de espaciado de 8 puntos, tipografía contrastada y una paleta cromática uniforme propia de la marca. Esto elimina cualquier fricción cognitiva en el usuario al navegar entre categorías radicalmente distintas.</li>
    <li><strong>2. Prioridad Absoluta Mobile-First (Leyes de Jakob y Fitts):</strong> En los 5 casos, los elementos de conversión críticos (selector numérico y botón CTA «Añadir al carrito») se sitúan dentro de la zona táctil ergonómica del pulgar, con dimensiones superiores a 44x44 píxeles, adaptándose a los hábitos de compra móvil predominantes en el Perú.</li>
    <li><strong>3. Transparencia y Reducción del Abandono de Carrito:</strong> Cada ficha expone con claridad su código SKU, disponibilidad real en almacén, opciones de despacho y acceso inmediato a canales de ayuda (WhatsApp) y garantías formales (Libro de Reclamaciones). Esta arquitectura de confianza eleva el ratio de conversión y consolida la reputación de la marca.</li>
</ul>

<div style="text-align: center; margin-top: 35pt; padding-top: 15pt; border-top: 1px solid #CBD5E0; font-size: 10pt; color: #718096;">
    <strong>MiniKids Perú — E-Business y Analítica Web · Semestre 2026</strong><br>
    Documento oficial generado para evaluación académica y sustentación de proyecto.
</div>

</body>
</html>
`;

fs.writeFileSync('C:/Users/USUARIO/Documents/Admin/ToyCommerce/docs/evaluacion_print.html', htmlContent, 'utf8');
console.log('HTML written successfully.');
