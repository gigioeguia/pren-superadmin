---
id: revisar-spec
name: "Revisar Spec"
description: "Auditar la cobertura de un spec-impl contra spec-conventions y spec-template antes de aprobarlo."
when_to_use: "Antes de mover un spec a Aprobado, o tras reorganizar carpetas de .agents/."
---

# Revisar Spec

## Pasos

1. **Leer reglas**: `.agents/spec/spec-conventions.md` (reglas) y `.agents/spec/spec-template.md` (estructura esperada).
2. **Leer el spec-impl** a auditar en `.agents/spec-impl/`.
3. **Frontmatter**: verificar los 6 campos (`id, titulo, estado, fecha, origen, cobertura`). `estado` debe estar en el flujo `Borrador → Revisado → Aprobado → Implementado → Obsoleto` (ver `spec-conventions.md`).
4. **Numeración**: IDs con prefijos oficiales (`L/S/R/A/P/Pub/G/H/M/U/F`) y correlativos sin huecos.
5. **Formato de catálogo**: todas las tablas con columnas `ID | Escenario | Dado | Cuando | Entonces`.
6. **Caracterizaciones**:
   - Blockquote `> **Caracterización de Seg-N / DT-N** — se espera que falle tras Fase X` tras cada tabla que contenga casos de caracterización.
   - Marca `— **caracterización**` inline en la columna Entonces de cada caso afectado (consistencia: todos los casos de caracterización marcados igual).
   - Sección "Tests de caracterización" consolidada al final del catálogo.
7. **Referencias vigentes**: paths correctos a `.agents/spec/...` y `.analisis/...`. Cuidado con referencias obsoletas tipo `specs/` tras reorganizaciones.
8. **Coherencia interna**:
   - Conteo de casos declarado (`~N tests` o `N tests`) == conteo real del catálogo.
   - Estimación acorde al conteo real (no al declarado).
   - DoD con condiciones medibles y alineadas al conteo real.
9. **Idioma**: español en prosa, inglés en IDs/funciones/rutas/keys.
10. **Producir tabla de hallazgos** `H#` con severidad:
    | H# | Hallazgo | Severidad |
    |----|----------|-----------|
    | H1 | ... | alto/medio/bajo |
11. **Decisión**:
    - Si hay `H` **altos** → el spec vuelve/queda en `Borrador` con nota de los hallazgos.
    - Si solo hay `H` **medios/bajos** → puede pasar a `Revisado`.
    - `Aprobado` solo tras revisión sin altos.

## Evitar

- Aprobar un spec con referencias rotas (paths que no existen).
- Aprobar un spec con conteo de casos inconsistente entre frontmatter, DoD y estimación.
- Aprobar sin verificar que los casos de caracterización estén marcados consistentemente.
- Mezclar hallazgos de forma con hallazgos de fondo (auditar cobertura primero, no contenido técnico).

## Relacionados

- `crear-spec.md` — cómo se construye el spec que se revisa.
- `implementar-spec.md` — qué se valida antes de implementar.
