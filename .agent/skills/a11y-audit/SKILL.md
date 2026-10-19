---
name: a11y-audit
description: Auditoría de accesibilidad WCAG 2.2 AA (contraste de color, foco por teclado, ARIA, objetivos táctiles).
---

# Accessibility Audit Skill (WCAG 2.2 AA)

## Propósito
Garantizar que la tienda sea 100% usable por adultos y cuidadores, cumpliendo las pautas WCAG 2.2 Nivel AA.

## Criterios Clave
1. **Ratio de Contraste**: Mínimo 4.5:1 para texto normal y 3:1 para texto grande/componentes UI.
2. **Navegabilidad por Teclado**: Todo control interactivo accesible vía `Tab`, con indicador de foco `:focus-visible` visible.
3. **Objetivos Táctiles (Touch Targets)**: Mínimo 44×44px en móviles para botones y enlaces críticos.
4. **Semántica y ARIA**: Formularios con `<label>`, badges con texto alternativo, modales/drawers con `aria-modal="true"`.
