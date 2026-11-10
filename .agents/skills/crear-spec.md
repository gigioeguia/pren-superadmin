---
id: crear-spec
name: "Crear Spec"
description: "Crear una nueva especificación en .agents/spec-impl/ siguiendo convenciones y plantilla."
when_to_use: "Antes de especificar el comportamiento de un flujo crítico (auth, pagos, multi-tenant) o cuando una tarea de .analisis/plan/plan-accion.md requiera spec."
---

# Crear Spec

## Pasos

1. **Verificar rama**: `git branch --show-current`. Si es `main`, crear `spec/<id>-draft` antes de editar (ver skill `workflow-ramas`).
2. **Copiar plantilla**: copiar `.agents/spec/spec-template.md` → `.agents/spec-impl/spec-<tema>.md` (kebab-case).
3. **Frontmatter**: llenar los 6 campos obligatorios:
   - `id: spec-<tema>`
   - `titulo: "<Título humano>"`
   - `estado: Borrador`
   - `fecha: YYYY-MM-DD`
   - `origen: <ruta a .analisis/plan/plan-accion.md punto Nx y/o .analisis/seguridad.md Seg-N>`
   - `cobertura: <resumen breve>`
4. **Origen**: debe citar `.analisis/plan/plan-accion.md` (Fase X / X.X) y, si aplica, `Seg-N` (seguridad) o `DT-N` (deuda técnica).
5. **Alcance**: definir IN (flujos cubiertos), OUT (lo excluido y por qué) y cobertura objetivo medible (ej. `≥ 60 % de endpoints críticos`).
6. **Pre-requisitos**: tabla `P#` con `Estado actual` y `Acción necesaria`. Sin resolver P1–P3 el spec no es implementable.
7. **Decisiones técnicas**: fijar framework, DB, mocks, traits. No negociables salvo volver el spec a `Borrador`.
8. **Catálogo de casos**: usar prefijos oficiales (`L`=login, `S`=signup, `R`=reset, `A`=API auth, `P`=pagos, `Pub`=públicas, `G`=guard admin, `H`=landlord, `M`=maintenance) con IDs correlativos `<Prefijo><N>`.
   - Tablas con columnas fijas: `ID | Escenario | Dado | Cuando | Entonces`.
   - `Dado` = precondiciones (BD, sesión, env, mocks).
   - `Cuando` = acción HTTP exacta (método + ruta + payload resumido).
   - `Entonces` = aserción observable (status, JSON shape, redirect, efecto BD).
9. **Caracterizaciones** (si aplica): marcar bugs conocidos con `— **caracterización**` inline en la columna Entonces + blockquote `> **Caracterización de Seg-N** — se espera que falle tras Fase X` tras la tabla + listar el caso en sección "Tests de caracterización" consolidada. Ver skill `tests-caracterizacion`.
10. **DoD**: lista `[ ]` de condiciones objetivas y medibles. Sin medibles, no pasa a `Aprobado`.
11. **Riesgos**, **estimación** (desglosada por dominio si hay >20 casos) y **referencias** (a `.agents/spec/spec-conventions.md`, `.analisis/...`, `AGENTS.md`).

## Evitar

- Crear spec para cambios triviales (typo, rename, un test aislado).
- Crear spec sin revisar primero `.analisis/plan/plan-accion.md`.
- Usar prefijos fuera de la tabla oficial.
- Mezclar specs en un solo archivo (un spec = un archivo).
- Olvidar el frontmatter o dejar `estado` vacío.

## Relacionados

- `revisar-spec.md` — auditar el spec antes de aprobarlo.
- `implementar-spec.md` — pasar el spec aprobado a código.
- `workflow-ramas.md` — crear rama antes de editar.
