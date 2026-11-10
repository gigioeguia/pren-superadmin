# Convenciones de Specs

Reglas para escribir y mantener especificaciones. Aplican a todo spec bajo `.agents/spec-impl/` y a plantillas bajo `.agents/spec/`.

## Estados del ciclo de vida

Cada spec declara su estado en el frontmatter. Flujo permitido:

```
Borrador → Revisado → Aprobado → Obsoleto
```

| Estado | Significado | Quién lo mueve |
|--------|-------------|----------------|
| `Borrador` | En elaboración, puede cambiar estructuralmente | Autor |
| `Revisado` | Contenido estable; revisado por pares/técnico | Revisor |
| `Aprobado` | Listo para implementar; cambios solo por desviación | Decisor |
| `Obsoleto` | Reemplazado o descartado; mantener por historial | Cualquiera con nota de reemplazo |

Un spec en `Aprobado` que se desvía en implementación vuelve a `Borrador` con nota del motivo.

## Frontmatter obligatorio

```yaml
---
id: spec-<tema>           # kebab-case, sin espacios
titulo: <Título humano>
estado: Borrador
fecha: YYYY-MM-DD
origen: <ruta a .analisis/plan/plan-accion.md punto Nx y/o .analisis/seguridad.md Seg-N>
cobertura: <resumen breve>
---
```

## Numeración de casos de test

Cada caso tiene un ID `<Prefijo><N>` correlativo dentro del spec.

| Prefijo | Dominio |
|---------|---------|
| `L` | Login (web) |
| `S` | Signup (web) |
| `R` | Reset de contraseña (web) |
| `A` | Auth API |
| `P` | Pagos |
| `Pub` | Rutas públicas |
| `G` | Guard admin |
| `H` | Landlord (guard/dashboard) |
| `M` | Maintenance / rutas Artisan |
| `U` | Unit tests |
| `F` | Feature transversal no cubierto arriba |

Cuando un caso caracteriza un **bug conocido** (comportamiento inseguro/incorrecto actual que se espera romper en una fase posterior):

- Marcar el test con `@group characterization` (en la implementación PHPUnit).
- En el spec, añadir tras la fila: `> **Caracterización de Seg-N / DT-N** — se espera que falle tras Fase X`.
- Listar estos casos en una sección **"Tests de caracterización"** al final del catálogo.

## Formato del catálogo de casos

Tabla con columnas fijas:

```
| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| L1 | Login válido | landlord con is_setupped=1 | POST /login/store email+pass | 200 JSON {redirect: landlord.dashboard} |
```

- **Dado:** precondiciones (estado de BD, sesión, env, mocks).
- **Cuando:** acción HTTP exacta (método + ruta + payload resumido).
- **Entonces:** aserción observable (status code, JSON shape, redirect, efecto en BD).

Si un caso necesita más detalle del que cabe en la tabla, expandirlo como sub-sección con código.

## Idioma

- **Español:** títulos, secciones, descripciones de escenarios, comentarios explicativos.
- **Inglés:** IDs de tests, nombres de funciones/clases, comandos, rutas, keys de JSON/env.
- Coherente con las instructions globales del agente (código en inglés, comentarios en español solo si aportan valor).

## Referencias

Todo spec debe citar su origen:

- `.analisis/plan/plan-accion.md` → punto de la fase que origina el spec (ej. `Fase 0 / 0.1`).
- `.analisis/seguridad.md` → `Seg-N` cuando el spec caracteriza o remedia una vulnerabilidad.
- `.analisis/deuda-tecnica.md` → `DT-N` cuando remedia deuda técnica.

## Decisiones técnicas

Sección donde se fijan las decisiones no negociables del spec (framework, DB, mocks, traits). Cualquier desviación en implementación requiere volver el spec a `Borrador`.

## Criterios de aceptación (Definition of Done)

Lista `[ ]` de condiciones objetivas y verificables. Un spec no pasa a `Aprobado` si sus criterios no son medibles.

## Estructura de archivos

```
.agents/
├── spec/                      # convenciones + plantilla
│   ├── spec-conventions.md    # este archivo
│   └── spec-template.md       # plantilla para nuevos specs
└── spec-impl/                 # specs concretos (un archivo por spec)
    └── spec-<tema>.md
```

Nombre de archivo: `spec-<tema>.md` en kebab-case (ej. `spec-tests-smoke-fase0.md`).

## Cuándo crear un spec

- Cuando una tarea del `plan-accion.md` requiera especificar comportamiento antes de implementar.
- Cuando se vaya a tocar un flujo crítico (auth, pagos, multi-tenant).
- Cuando múltiples tests/cambios compartan un mismo contrato.

No crear spec para cambios triviales (typo, rename, un solo test aislado).

## Workflow de ramas

- **Prohibido commitear ni pushear directamente a `main`.** Todo cambio (incluida la implementación de cualquier spec) se trabaja en una rama dedicada.
- Al detectar un intento de modificar `main` (commit/push directo), **detenerse y crear una rama** en su lugar, sin excepciones.
- Convención de nombres:
  - `spec/<id-spec>-<accion>` para trabajo derivado de un spec (ej. `spec/tests-smoke-fase0-impl`).
  - `fix/<tema>` para correcciones puntuales.
  - `feat/<tema>` para nuevas funcionalidades.
- Los cambios entran a `main` **solo vía PR/merge review**, nunca por push directo.
- Antes de empezar una tarea, verificar la rama actual con `git branch --show-current`; si es `main`, crear la rama con `git checkout -b <nombre>` antes de cualquier edición.
