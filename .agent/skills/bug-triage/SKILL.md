---
name: bug-triage
description: Gestión, priorización, documentación y verificación del ciclo de vida de bugs en bugs.md.
---

# Bug Triage Skill

## Propósito
Mantener el registro centralizado de bugs (`bugs.md`), clasificar por severidad (P0 a P3), coordinar el bucle "detectar → registrar → corregir → verificar → regresión" y validar el cierre de cada fase.

## Formato Estándar de Entrada
```markdown
### BUG-[ID] · P[0-3] · Fase [1-8] · Estado: [Abierto / En Verificación / Cerrado]
- URL: /ruta-afectada/
- Dispositivo/ancho: [Ej. Móvil 390 px, Desktop 1280 px]
- Pasos para reproducir:
  1. Paso 1
  2. Paso 2
- Esperado: Comportamiento previsto
- Obtenido: Comportamiento erróneo observado
- Evidencia: archivo_evidencia.png o snippet de log
- Causa probable: Análisis técnico de la falla
- Corrección: Descripción de la solución aplicada en mu-plugins
- Verificación: Evidencia de validación exitosa (OK)
```

## Reglas de Cierre
Una fase se declara CERRADA únicamente cuando se alcanzan **0 bugs P0–P2** en **dos pasadas completas consecutivas**.
