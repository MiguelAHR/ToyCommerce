---
name: design-system-audit
description: Auditoría de tokens de diseño (:root), tipografía, espaciado, radios, sombras y consistencia estética.
---

# Design System Audit Skill

## Propósito
Verificar que cada componente de la interfaz cumpla con los design tokens oficiales declarados en `:root`, asegurando una experiencia visual coherente y evitando valores CSS arbitrarios.

## Áreas de Auditoría
1. **Paleta de Colores**: Solo variables oficiales (`--brand-yellow`, `--brand-blue`, `--brand-red`, `--brand-ink`, tonos pasteles).
2. **Tipografía**: Jerarquía entre `Baloo 2` (headings) y `Nunito` (cuerpo/UI/botones).
3. **Escala de Espaciado**: Múltiplos de 4px / 8px.
4. **Radios de Borde**: Coherencia en tarjetas (16-20px), botones y píldoras (50px / 12px).
5. **Sombras y Elevación**: Elevaciones suaves acordes al prototipo Next.js.
