---
id: spec-service-incomecategory-fase4
titulo: "Service IncomeCategory — unificar capas Admin/Landlord/Api (Fase 4 / 4.2)"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 4 / 4.2) — DT-A1, DT-C4
cobertura: Migración de IncomeCategory a app/Services siguiendo el patrón de spec-unificar-capas-services-fase4 (slice piloto Property)
---

# Spec — Service IncomeCategory (Fase 4 / 4.2)

> Migrar la lógica de negocio de `IncomeCategory` (Admin/Landlord/Api) a `app/Services/IncomeCategoryService.php`, dejando controladores finos, sin cambiar el contrato observable.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 4 / 4.2). **Patrón de referencia:** `.agents/spec-impl/spec-unificar-capas-services-fase4.md` §8.

---

## 1. Objetivo

Aplicar el patrón de unificación (Service + Form Request + Channel + excepciones) a la entidad `IncomeCategory`, que existe en las 3 capas (`Admin`, `Landlord`, `Api`), siguiendo el slice piloto `Property`.

## 2. Alcance

### IN
- `app/Services/IncomeCategoryService.php` con métodos `listForChannel`, `store`, `update`, `delete`, `deleteMany` (y específicos de la entidad).
- `app/Http/Requests/IncomeCategory/StoreIncomeCategoryRequest.php` y `UpdateIncomeCategoryRequest.php`.
- Refactor in-place de los 3 controladores a < 250 líneas.
- Tests unitarios `IncomeCategoryServiceTest` y caracterización HTTP por canal.

### OUT
- Otras entidades (specs hermanos).
- Cambios de comportamiento observable.

### Cobertura objetivo
- 3 controladores de IncomeCategory < 250 líneas, sin duplicación.
- Suite completa en verde + tests nuevos.
- PHPStan nivel 5 y Pint en verde.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rama de trabajo | En `main` | `git checkout -b spec/service-incomecategory-fase4-impl` |
| P2 | Suite verde | Verde | `php artisan test` antes de empezar |
| P3 | Patrón Property | Implementado | Reutilizar `Channel`, excepciones, convenciones |
| P4 | Policies de IncomeCategory | Verificar existencia en `app/Policies/` | Crear si falta (Fase 2) |

## 4. Estructura propuesta

```
app/Services/IncomeCategoryService.php
app/Http/Requests/IncomeCategory/StoreIncomeCategoryRequest.php
app/Http/Requests/IncomeCategory/UpdateIncomeCategoryRequest.php
tests/Unit/IncomeCategoryServiceTest.php
tests/Feature/IncomeCategory/...HttpTest.php
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
