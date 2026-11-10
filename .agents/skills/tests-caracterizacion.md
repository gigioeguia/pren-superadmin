---
id: tests-caracterizacion
name: "Tests de Caracterización"
description: "Escribir y mantener tests que documentan comportamiento actual (inseguro/incorrecto) que se espera romper en una fase posterior."
when_to_use: "Al escribir tests @group characterization que registran bugs conocidos a remediar en fases futuras del plan-accion."
---

# Tests de Caracterización

Un test de caracterización **no valida comportamiento correcto**: documenta el comportamiento actual (inseguro o incorrecto) para que, al aplicar el fix de una fase posterior, el test falle de forma **intencional y detectable**. Cada fallo se interpreta como "el fix funcionó" y motiva la actualización del test al nuevo comportamiento seguro.

## Pasos

1. **Identificar el bug caracterizado**: referencia exacta a `Seg-N` (de `.analisis/seguridad.md`) o `DT-N` (de `.analisis/deuda-tecnica.md`), y la fase del `plan-accion.md` que lo remedia.
2. **En el spec** (`.agents/spec-impl/<spec>.md`):
   - Añadir `— **caracterización**` inline al final de la columna `Entonces` de la fila afectada.
   - Añadir blockquote **tras la tabla** que contenga el caso:
     `> **Caracterización de Seg-N** — se espera que falle tras Fase X.`
   - Listar el caso en la sección "Tests de caracterización" (consolidada) con columnas `ID | Bug caracterizado | Fase que lo romperá | Catálogo`.
3. **En la implementación PHPUnit**:
   - Anotar el test con `@group characterization`.
   - Añadir comentario en la primera línea del método:
     `// Expected to fail after Phase X (Seg-N fix) — update to secure behavior when it fails.`
4. **Documentar en `tests/README.md`** qué significa `@group characterization` y cómo correr solo esos tests: `php artisan test --group=characterization`.
5. **Cuando el test falle tras un fix**:
   - Confirmar que el fallo corresponde a la fase indicada.
   - Actualizar el test al nuevo comportamiento seguro (aserciones del estado remedido).
   - Quitar `@group characterization` y el comentario (ya no es caracterización).
   - Actualizar el spec: mover el caso fuera de la sección de caracterización y ajustar el `Entonces`.

## Evitar

- Arreglar un test de caracterización que falla **sin verificar primero** si es el fix esperado de la fase indicada.
- Mezclar tests de caracterización con tests normales sin marcar (`@group` + comentario).
- Escribir tests de caracterización sin referencia `Seg-N`/`DT-N` y fase esperada de ruptura.
- Dejar tests de caracterización marcados después de que el fix ya se aplicó (deben actualizarse al comportamiento seguro).
- Documentar el mismo bug en dos specs distintos (un bug = un test de caracterización).

## Relacionados

- `implementar-spec.md` — dónde se aplican los tests de caracterización.
- `testing-conventions.md` — convenciones generales de testing.
