---
name: browser-visual-qa
description: Inspección visual automatizada, captura de pantalla en múltiples breakpoints y detección de desbordes o regresiones visuales.
---

# Browser Visual QA Skill

## Propósito
Navegar, capturar y comparar pantallas en los breakpoints clave para detectar roturas de diseño, texto desbordado, solapamientos y comportamientos anómalos.

## Breakpoints Requeridos
- **Móvil ultra-compacto**: 360px
- **Móvil estándar**: 390px (iPhone 14/15)
- **Tablet / iPad vertical**: 768px
- **Laptop / iPad horizontal**: 1024px
- **Desktop estándar**: 1280px
- **Pantalla ancha**: 1440px

## Metodología
1. Ejecutar capturas de pantalla con Chrome/Playwright/Edge headless.
2. Comprobar que ningún elemento provoque `document.documentElement.scrollWidth > window.innerWidth` (desborde horizontal no intencionado).
3. Validar estados vacíos: carrito vacío, búsqueda sin resultados, 404.
4. Generar evidencias en `wp-content/qa-evidencia/` con formato `<pagina>-<breakpoint>.png`.
