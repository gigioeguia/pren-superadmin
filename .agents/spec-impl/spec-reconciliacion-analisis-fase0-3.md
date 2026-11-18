---
id: spec-reconciliacion-analisis-fase0-3
titulo: "Reconciliación de documentos .analisis con el estado real del proyecto (Fase 0–3)"
estado: Implementado
fecha: 2026-11-18
origen: .analisis/plan/plan-accion.md (Fase 0 / 0.1–0.3, Fase 1–3) — .analisis/seguridad.md (Seg-1..Seg-18) — .analisis/deuda-tecnica.md (DT-C1, DT-C3, DT-A4, DT-M6, DT-M10, DT-B3)
cobertura: Alinear .analisis/plan/plan-accion.md, .analisis/deuda-tecnica.md, .analisis/seguridad.md, .analisis/resumen-ejecutivo.md y referencia segura a .analisis/comand.txt con el estado real del repo (main@1ab518f)
---

# Spec — Reconciliación de documentos `.analisis` con el estado real del proyecto (Fase 0–3)

> Resumen de una línea: actualizar y sincronizar los documentos de análisis (plan, deuda técnica, seguridad y resumen ejecutivo) para que reflejen el estado real del repositorio tras Fase 0 y mantengan trazabilidad con Fases 1–3, sin alterar código de aplicación.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 0 / 0.1–0.3, Fase 1–3) — `.analisis/seguridad.md` (Seg-1..Seg-18) — `.analisis/deuda-tecnica.md` (DT-C1, DT-C3, DT-A4, DT-M6, DT-M10, DT-B3).

---

## 1. Objetivo

Que los documentos en `.analisis/` (plan, deuda técnica, seguridad, resumen ejecutivo y referencias sensibles) cuenten **una única historia coherente** con el estado real del proyecto (código, rutas, middleware, traits, tests, CI, specs), evitando falsos pendientes o falsos cierres, y dejando trazabilidad de qué está hecho, parcial, vigente o no verificable en repo.

---

## 2. Alcance

### IN
- Actualización de metadatos (fecha/última reconciliación) y estados en:
  - `.analisis/plan/plan-accion.md`
  - `.analisis/deuda-tecnica.md`
  - `.analisis/seguridad.md`
  - `.analisis/resumen-ejecutivo.md`
- Ajuste de Fase 0: marcar **0.3** como hecho (con alcance/excepciones), matizar criterio de salida y métricas.
- Reescritura de hallazgos de deuda obsoletos por avance de Fase 0: `DT-C1`, `DT-C3`, `DT-A4`; matización de `DT-M10`, `DT-M6`, `DT-B3`.
- Incorporación de leyenda de estados (`Vigente`, `Caracterizado`, `Parcial`, `No verificable en repo`, `Mitigado`) y tabla de estado para Seg-1..Seg-18 en `seguridad.md`, con referencia a caracterizaciones existentes (smoke tests).
- Sincronización ejecutiva: avance observado (Fase 0 parcial) y prioridad de cierre Fases 1–2, sin suavizar riesgos.
- Tratamiento documental de `.analisis/comand.txt` como artefacto sensible (sin reproducir valores).

### OUT
- Cambios de código de aplicación, rutas, middleware, modelos, traits o tests.
- Remediación de Seg-1..Seg-18 o DT-* (Fase 1–5) más allá de documentar su estado.
- Verificación operativa de secretos/entorno (1.5) — solo se define criterio de evidencia y estado `No verificable en repo`.
- Ejecución de CI o de la suite de tests (se documenta el estado esperado; la ejecución queda fuera de este spec).
- Modificación de `.analisis/comand.txt` fuera de la referencia segura (no se edita su contenido sensible en este spec).

### Cobertura objetivo
- 100% de los ítems de Fase 0 (0.1–0.3) con estado y evidencia actualizados.
- 100% de los hallazgos de seguridad Seg-1..Seg-18 con estado trazable (incluyendo `No verificable en repo` donde aplique).
- 0 contradicciones críticas entre documentos sobre estado de Fase 0, tests/CI/tooling y duplicación centralizada.
- Referencias cruzadas consistentes (`plan` ↔ `seguridad` ↔ `deuda` ↔ `resumen`) y trazabilidad a specs/PRs/casos de caracterización.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rama de trabajo (no `main`) | `main@1ab518f` | Crear `spec/reconciliacion-analisis-fase0-3-draft` antes de editar (hecho para este borrador) |
| P2 | Evidencia de estado Fase 0–3 en repo | Presente | Usar código/rutas/tests/specs/CI como fuente de verdad para lo implementado |
| P3 | Aprobación del contenido propuesto | Aprobado por usuario (2026-11-18) | Formalizar cambios en este spec y luego implementar en `.analisis/*` por PR |
| P4 | Convenciones de specs | Presentes | Seguir frontmatter y estructura de `.agents/spec/spec-conventions.md` y `spec-template.md` |

---

## 4. Estructura propuesta

```
.agents/spec-impl/spec-reconciliacion-analisis-fase0-3.md   # este spec
.analisis/plan/plan-accion.md                               # actualizar Fase 0, notas 1.5/2.2/2.6, métricas
.analisis/deuda-tecnica.md                                  # DT-C1/C3/A4 + resumen/impacto
.analisis/seguridad.md                                      # leyenda + tabla estado Seg-1..Seg-18 + conclusión
.analisis/resumen-ejecutivo.md                              # corte actual + avance + recomendación
.analisis/comand.txt                                        # solo referencia segura (sin valores)
```

---

## 5. Decisiones técnicas

Fijadas, no negociables salvo volver el spec a `Borrador`:

- **Fuente de verdad:** código/artefactos ejecutables (rutas, middleware, traits, tests, CI, specs) para “implementado”; `.analisis/*` para “riesgo/deuda/plan”. Discrepancias se resuelven caso por caso con evidencia.
- **No exposición de secretos:** ninguna corrección documental incluirá valores de credenciales/claves ni copiará contenido sensible de `comand.txt`.
- **Estado `No verificable en repo`:** se usa para ítems operativos (p.ej. 1.5) y no se declara cierre sin evidencia.
- **Preservación de inventario técnico:** `seguridad.md` mantiene el detalle de hallazgos; la reconciliación agrega trazabilidad/estado, no borra el diagnóstico.
- **Caracterizaciones no equivalen a fixes:** los tests de caracterización documentan comportamiento actual; no se marcan hallazgos como mitigados solo por existir tests.
- **Cambios solo documentales:** este spec no autoriza modificaciones de código; la implementación se limita a `.analisis/*` y al propio spec.

---

## 6. Catálogo de verificaciones (prefijo `F`)

Este spec no cubre flujos HTTP; el catálogo adapta `Dado/Cuando/Entonces` a verificaciones documentales (prefijo `F` — feature transversal). **Total: 12 verificaciones (F1–F12)**.

### 6.1 Coherencia Fase 0

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Estado 0.1 reflejado | `tests/README.md` y specs smoke existen | Revisar `plan-accion.md` | Fila 0.1 marcada ✅ con fecha/evidencia |
| F2 | Estado 0.2 reflejado | `phpstan.neon.dist`, `pint.json`, `ci.yml` existen | Revisar `plan-accion.md` | Fila 0.2 marcada ✅ con fecha/evidencia |
| F3 | Estado 0.3 reflejado | `app/Traits/HasAcnooActions.php` y spec-trait existen | Revisar `plan-accion.md` | Fila 0.3 marcada ✅ (con alcance/excepciones) |
| F4 | Métricas de tests actualizadas | Suite smoke Fase 0 presente | Revisar `deuda-tecnica.md` | DT-C1 ya no afirma “solo ExampleTest / cobertura ≈ 0%”; describe deuda residual |

### 6.2 Coherencia Fases 1–3

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F5 | Seg-1 vigente y documentado | `routes/maintenance.php` público | Revisar `seguridad.md` | Estado `Vigente, Caracterizado` + referencia a smoke M1/M2 |
| F6 | Seg-2 vigente y documentado | `AdminMiddleware` con precedencia rota | Revisar `seguridad.md` | Estado `Vigente, Caracterizado` + referencia a smoke G1–G4 |
| F7 | Seg-4 vigente y documentado | Registro API acepta `role` y `User.role` fillable | Revisar `seguridad.md` | Estado `Vigente, Caracterizado` + referencia a smoke A1–A10 |
| F8 | IDOR y Policies documentados | `app/Policies/` inexistente | Revisar `seguridad.md`/`deuda-tecnica.md` | Seg-5 `Vigente, Parcialmente caracterizado`; DT-M6 vigente |
| F9 | Pagos/session documentados | `PaymentController` usa params/sesión y `/payment/success` público | Revisar `seguridad.md` | Seg-6 `Vigente, Caracterizado` + referencia a smoke P7 |
| F10 | Rutas cruzadas documentadas | `routes/landlord.php` líneas 15–16 | Revisar `seguridad.md` | Seg-15 `Vigente, Caracterizado` + referencia a smoke landlord |

### 6.3 Coherencia cruzada y sensibilidad

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F11 | Resumen ejecutivo sincronizado | Avance Fase 0 parcial, Fase 1–2 pendientes | Revisar `resumen-ejecutivo.md` | Sección “Avance observado” y recomendación ajustada; riesgo no suavizado |
| F12 | Referencia segura a `comand.txt` | Archivo local con credenciales demo | Revisar docs | Se menciona ubicación/tipo de dato y acción, **sin valores** |

### Tests de caracterización

No aplica: este spec no añade tests de código; las caracterizaciones referenciadas son las existentes de Fase 0 (Seg-1/2/3/4/6/15).

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `.agents/spec-impl/spec-reconciliacion-analisis-fase0-3.md` creado con frontmatter completo y estado `Implementado`.
- [x] `plan-accion.md`: Fase 0 actualizada (0.1–0.3 con estado/evidencia), notas para 1.5/2.2/2.6, métricas y DoD ajustados.
- [x] `deuda-tecnica.md`: DT-C1/C3/A4 reescritos a estado residual; resumen e impacto cruzado actualizados; DT-M6/M10/B3 matizados.
- [x] `seguridad.md`: leyenda de estados + tabla de estado Seg-1..Seg-18 + conclusión reconciliada.
- [x] `resumen-ejecutivo.md`: corte actual + avance + recomendación ajustada.
- [x] `comand.txt`: referencias cruzadas seguras (sin valores) en docs.
- [x] 0 contradicciones críticas entre documentos sobre estado de Fase 0, tests/CI/tooling y duplicación centralizada.
- [ ] Cambios integrados vía PR (no push directo a `main`), manteniendo `.analisis/` como local/ignorado por git. (pendiente: abrir PR)
- [ ] Spec revisado (`revisar-spec`) antes de pasar a `Aprobado` (si se decide formalizar implementación). (opcional para este spec documental)

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Falsa sensación de progreso (Fase 0 documentada como más avanzada) | Medio | Marcar explícitamente que Fases 1–2 siguen abiertas y que caracterizaciones no son fixes |
| Falsos cierres operativos (secretos/entorno) | Alto | Estado `No verificable en repo` + criterio de evidencia (rotación/config productiva) |
| Exposición accidental de secretos al documentar `comand.txt` | Alto | Prohibir reproducir valores; referenciar solo ubicación/tipo/acción |
| Desviación entre documentos por edición parcial | Medio | Aplicar cambios en el orden recomendado y validar referencias cruzadas al final |
| Mezclar documentación con remediación real | Alto | Limitar cambios a `.analisis/*` y spec; no tocar código |

**Pendiente de decisión:**
- Si se formaliza el paso a `Aprobado` e implementación inmediata, o se mantiene en `Borrador` hasta una revisión por pares (`revisar-spec`).

---

## 9. Estimación

- Especificación: 0.5–1 día.
- Implementación documental (docs + spec): 0.5–1 día.
- Validación cruzada (referencias/consistencia): 0.5 día.
- Total: 1.5–2.5 días.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 0 / 0.1–0.3, Fase 1–3
- `.analisis/seguridad.md` → Seg-1..Seg-18
- `.analisis/deuda-tecnica.md` → DT-C1, DT-C3, DT-A4, DT-M6, DT-M10, DT-B3
- `.analisis/resumen-ejecutivo.md`
- `.analisis/comand.txt` (sensible — solo referencia)
- `.agents/spec/spec-conventions.md`
- `.agents/spec/spec-template.md`
- `AGENTS.md` (Tooling, Agent conventions)
- `tests/README.md` (cobertura y caracterizaciones)
- `app/Traits/HasAcnooActions.php` (consolidación Fase 0.3)
- `.github/workflows/ci.yml` (pipeline Fase 0.2)
