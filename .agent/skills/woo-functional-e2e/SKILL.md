---
name: woo-functional-e2e
description: Pruebas funcionales de extremo a extremo (E2E) para flujos de catálogo, carrito, checkout, pedidos y cupones.
---

# WooCommerce Functional E2E Skill

## Propósito
Verificar exhaustivamente que el flujo de compra y transacciones de WooCommerce funcione a la perfección, tanto para usuarios invitados como registrados.

## Flujos de Prueba
1. **Catálogo y Búsqueda**: Filtrado en vivo, selección de categorías, búsqueda sin recarga.
2. **Ficha de Producto**: Selección de opciones/variaciones, verificación de stock, badges de edad.
3. **Carrito**: Añadido vía AJAX, cálculo de envío gratuito (umbral S/ 149), aplicación de cupones, actualización de cantidades.
4. **Checkout**: Validación de campos requeridos, cálculo de totales, creación de pedido y pantalla de agradecimiento.
5. **Integridad de Datos**: Pedido guardado en `wp_wc_orders` (HPOS) sin duplicidades ni errores.
