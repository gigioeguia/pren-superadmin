---
id: implementar-spec
name: "Implementar Spec"
description: "Pasar un spec Aprobado a código, resolviendo pre-requisitos y verificando el DoD."
when_to_use: "Cuando un spec en .agents/spec-impl/ está Aprobado y se va a escribir código."
---

# Implementar Spec

## Pasos

1. **Verificar rama**: `git branch --show-current`. Si es `main`, crear `spec/<id-spec>-impl` (ver skill `workflow-ramas`).
2. **Confirmar estado del spec** = `Aprobado`. Si está en `Borrador` o `Revisado`, no implementar: volver al autor. Si se desvía durante la implementación, el spec vuelve a `Borrador` con nota del motivo.
3. **Resolver pre-requisitos primero** en orden `P1 → Pn`. Sin P1–P3 (DB, config, factories) los tests no corren en aislamiento.
4. **Implementar el catálogo en orden de dominio**: `L → S → R → A → P → Pub → G → H → M`. Cada dominio = un archivo `tests/Feature/<Dominio>/<...>SmokeTest.php`.
5. **Tests de caracterización**: marcar con `@group characterization` + comentario `// Expected to fail after Phase X (Seg-N fix)`. Ver skill `tests-caracterizacion`.
6. **Mocks obligatorios** según las decisiones técnicas del spec:
   - `Artisan::shouldReceive('call')` para rutas que ejecuten Artisan (jamás correr migraciones/seed reales en tests).
   - `Mail::fake()` para flujos que envíen email.
   - Mock de clases `App\Library\*` para gateways de pago (no HTTP real).
   - `Sanctum::actingAs($user, ['*'])` para API.
7. **Correr `php artisan test` tras cada dominio** completado, no solo al final.
8. **Verificar DoD checkbox por checkbox**. Cada `[ ]` debe poder tacharse con evidencia objetiva.
9. **Si un test de caracterización falla tras un fix** → **NO arreglarlo ciegamente**:
   - Verificar si el fallo corresponde al comportamiento esperado del fix de la fase indicada.
   - Si sí: actualizar el test intencionalmente al nuevo comportamiento seguro, cambiar su marca (ya no es caracterización) y documentar el cambio.
   - Si no: investigar como bug real.
10. **Al terminar**: el spec-impl puede moverse a estado `Obsoleto` solo si la implementación reemplaza el documento; normalmente se mantiene como referencia histórica.

## Evitar

- Implementar sin resolver los pre-requisitos `P#` primero.
- Correr tests que toquen la DB real del `.env` (siempre DB de testing dedicada).
- Arreglar tests de caracterización que fallan sin verificar antes si es el fix esperado.
- Implementar varios dominios a la vez sin correr tests entre ellos.
- Saltarse el DoD o marcar checkboxes sin evidencia.
- Commitear/pushear a `main` (siempre rama + PR).

## Relacionados

- `crear-spec.md` — el spec que se implementa.
- `revisar-spec.md` — la revisión que lo aprobó.
- `tests-caracterizacion.md` — marcado de tests de caracterización.
- `secure-coding.md` — checklist anti-reincidencia al escribir código.
- `testing-conventions.md` — convenciones de testing.
