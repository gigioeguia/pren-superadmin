---
id: workflow-ramas
name: "Workflow de Ramas"
description: "Reglas de git para nunca editar main directamente; crear rama dedicada antes de cualquier cambio."
when_to_use: "Antes de cualquier edición en el repo (crear spec, implementar, fix, feat)."
---

# Workflow de Ramas

## Pasos

1. **Verificar rama actual** antes de editar:
   ```bash
   git branch --show-current
   ```
2. **Si la rama es `main`**, crear una rama dedicada **antes** de cualquier edición:
   ```bash
   git checkout -b <nombre>
   ```
3. **Nomenclatura**:
   - `spec/<id-spec>-<accion>` para trabajo derivado de un spec (ej. `spec/tests-smoke-fase0-impl`, `spec/tests-smoke-fase0-draft`).
   - `fix/<tema>` para correcciones puntuales (ej. `fix/admin-middleware-logica`).
   - `feat/<tema>` para nuevas funcionalidades (ej. `feat/policies-property`).
4. **Nunca** ejecutar `git push origin main` ni `git commit` estando en `main`.
5. **Integrar a `main` solo vía PR/merge review**, nunca por push directo.
6. **Al detectar un intento de modificar `main`** (commit/push directo o edición sin rama): **detenerse inmediatamente** y crear la rama correspondiente, sin excepciones.
7. **Antes de empezar una sesión de edición**, repetir el paso 1 aunque "creas" que ya estás en una rama.

## Commits

- **Formato Conventional Commits**: `<tipo>(<scope>): <descripción>`
  - Tipos: `spec` (specs/convenciones), `feat` (nueva funcionalidad), `fix` (corrección), `test` (tests), `docs` (documentación), `refactor`, `chore`.
  - Scope: módulo o entidad (`auth`, `payment`, `property`, `agents`).
  - Ejemplo: `spec(auth): add smoke tests for login flow`
- **Cuerpo** (opcional): explica el "por qué", no el "qué". Líneas ≤ 72 caracteres.
- **Footer**: referenciar spec o issue: `Refs: .agents/spec-impl/spec-tests-smoke-fase0.md` o `Closes #123`.
- **Un commit = un cambio lógico.** No mezclar specs + código + refactor en el mismo commit.
- Mensaje consistente en idioma dentro del repo.

## Pull Requests

- **Título**: mismo formato que el commit principal (`tipo(scope): descripción`).
- **Descripción** debe incluir:
  - Qué cambia y por qué (vincular al spec si existe).
  - Checklist del DoD del spec (copiar las `[ ]` y tachar las cumplidas).
  - Salida de `php artisan test` / screenshots si aplica.
- **Linkear el spec**: `Refs: .agents/spec-impl/<spec>.md`.
- **Squash merge** por defecto (un commit por PR en `main`). Merge commit solo si el PR tiene commits atómicos relevantes a preservar.
- **Review mínimo**: 1 aprobación + CI verde (cuando exista CI).
- **No mergear a `main` sin review**, ni siquiera en cambios "triviales".

## Evitar

- Commitear o pushear estando en `main`.
- Crear ramas con nombres genéricos (`feature`, `test`, `wip`, `tmp`).
- Mergear a `main` sin review.
- Reabrir `main` para "un cambio rápido" — siempre rama, sin importar el tamaño.
- Dejar ramas sin nombre descriptivo (debe leerse y saber qué contiene).
- Mezclar varios cambios lógicos en un solo commit.
- Mergear PR sin tachar el DoD del spec asociado.

## Relacionados

- `crear-spec.md` — especificar antes de implementar (también requiere rama).
- `implementar-spec.md` — implementar un spec aprobado (rama `spec/<id>-impl`).
- `testing-conventions.md` — convenciones de testing.
