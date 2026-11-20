---
id: spec-service-expensecategory-fase4
titulo: "Service ExpenseCategory — unificar capas Admin/Landlord/Api (Fase 4 / 4.2)"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 4 / 4.2) — DT-A1, DT-C4
cobertura: Migración de ExpenseCategory a app/Services siguiendo el patrón de spec-unificar-capas-services-fase4 (slice piloto Property)
---

# Spec — Service ExpenseCategory (Fase 4 / 4.2)

> Migrar la lógica de negocio de `ExpenseCategory` (Admin/Landlord/Api) a `app/Services/ExpenseCategoryService.php`, dejando controladores finos, sin cambiar el contrato observable.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 4 / 4.2). **Patrón de referencia:** `.agents/spec-impl/spec-unificar-capas-services-fase4.md` §8.

---

## 1. Objetivo

Aplicar el patrón de unificación (Service + Form Request + Channel + excepciones) a la entidad `ExpenseCategory`, que existe en las 3 capas (`Admin`, `Landlord`, `Api`), siguiendo el slice piloto `Property`.

## 2. Alcance

### IN
- `app/Services/ExpenseCategoryService.php` con métodos `listForChannel`, `store`, `update`, `delete`, `deleteMany` (y específicos de la entidad).
- `app/Http/Requests/ExpenseCategory/StoreExpenseCategoryRequest.php` y `UpdateExpenseCategoryRequest.php`.
- Refactor in-place de los 3 controladores a < 250 líneas.
- Tests unitarios `ExpenseCategoryServiceTest` y caracterización HTTP por canal.

### OUT
- Otras entidades (specs hermanos).
- Cambios de comportamiento observable.

### Cobertura objetivo
- 3 controladores de ExpenseCategory < 250 líneas, sin duplicación.
- Suite completa en verde + tests nuevos.
- PHPStan nivel 5 y Pint en verde.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rama de trabajo | En `main` | `git checkout -b spec/service-expensecategory-fase4-impl` |
| P2 | Suite verde | Verde | `php artisan test` antes de empezar |
| P3 | Patrón Property | Implementado | Reutilizar `Channel`, excepciones, convenciones |
| P4 | Policies de ExpenseCategory | Verificar existencia en `app/Policies/` | Crear si falta (Fase 2) |

## 4. Estructura propuesta

```
app/Services/ExpenseCategoryService.php
app/Http/Requests/ExpenseCategory/StoreExpenseCategoryRequest.php
app/Http/Requests/ExpenseCategory/UpdateExpenseCategoryRequest.php
tests/Unit/ExpenseCategoryServiceTest.php
tests/Feature/ExpenseCategory/...HttpTest.php
```

Contrato mínimo: ver §8.2 del spec piloto.

## 5. Decisiones técnicas

- Mismo patrón que `PropertyService`: Service agnóstico de HTTP, `Channel` enum, Form Requests compartidos, excepciones tipadas, transacciones solo DB, notificaciones post-commit.
- Migrar en orden Api → Landlord web → Admin web, suite verde tras cada paso.

## 6. Catálogo de casos

Prefijos `F` (HTTP) y `U` (unit). Casos concretos a definir al revisar esta entidad (estructura CRUD estándar: index/store/show/update/destroy por canal).

## 7. Criterios de aceptación (Definition of Done)

- [ ] Service + Form Requests creados.
- [ ] 3 controladores < 250 líneas, sin `HasAcnooActions` (si migrado).
- [ ] Suite + PHPStan + Pint en verde.
- [ ] Contrato HTTP idéntico verificado por tests.

## 8. Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Lógica específica de la entidad no cubierta por el patrón | Revisar controladores antes de migrar; añadir métodos de dominio al Service |

## 9. Estimación

- 5–8 días por entidad siguiendo el patrón.

## 10. Referencias

- `.agents/spec-impl/spec-unificar-capas-services-fase4.md` (patrón de referencia §8)
- `.analisis/plan/plan-accion.md` → Fase 4 / 4.2
- `.analisis/deuda-tecnica.md` → DT-A1, DT-C4
