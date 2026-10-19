---
name: wp-env-setup
description: Prepara y valida el entorno de depuración, logs, Query Monitor y modo test de pagos en WordPress/WooCommerce.
---

# WP Env Setup Skill

## Propósito
Garantizar que el entorno local de desarrollo y QA tenga visibilidad total de errores PHP, notices de WooCommerce, consultas SQL lentas y gestión de correos locales.

## Procedimientos
1. **Configuración de wp-config.php**:
   - `WP_DEBUG`: `true`
   - `WP_DEBUG_LOG`: `true` (genera `wp-content/debug.log`)
   - `WP_DEBUG_DISPLAY`: `false` (no romper el renderizado de cabeceras ni JSON en APIs)
   - `SCRIPT_DEBUG`: `true` (carga scripts sin minificar para depuración)
2. **Validación de Base de Datos y Entorno**:
   - Inspeccionar versiones de PHP (>= 8.1/8.2) y MySQL (>= 8.0 o MariaDB 10.4+).
   - Comprobar que las tablas críticas de WooCommerce existen (incluyendo HPOS `wp_wc_orders`).
3. **Pasarelas de Pago en Modo Prueba / Sandbox**:
   - Verificar que BACS (transferencia), COD (pago contra entrega) y pasarelas de tarjeta estén en modo sandbox.
4. **Inspección de Logs**:
   - Monitorear `wp-content/debug.log` y `wp-content/uploads/wc-logs/`.
