---
id: spec-filter-shape-html-json-fase5
titulo: "L1 expandido — filtros inline devuelven HTML directo (shape JSON) (Fase 5 / 5.22)"
estado: Implementado
fecha: 2026-11-23
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.22); hallazgo lateral del inventario spec 5.17 (§2 OUT) y L1 de spec-controladores-residuales-fase5.md §11
cobertura: 11 rutas `filter` vivas con respuesta HTML directa que rompen el contrato AJAX `custom-ajax.js` (`dataType:"json"`, `res.data`); fix de shape a JSON `{data}` + fallback no-ajax
---

# Spec — L1 expandido: filtros inline HTML → JSON `{data}`

> Resumen: 11 endpoints `filter` vivos devuelven `view(...)` HTML directo; `public/backend/assets/js/custom-ajax.js` espera JSON `{data}` y hace `$(table).html(res.data)` → no-op silencioso en el navegador. Se alinea el contrato al shape canónico del repo.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.22). Amplía el L1 de `.agents/spec-impl/spec-controladores-residuales-fase5.md` §11 (3 filtros originales) y el inventario lateral de `.agents/spec-impl/spec-restauracion-crud-42-fase5.md` §2 OUT. Mismo bug-clase que D2 sin el 500; cambia contrato observable de rutas vivas ⇒ spec aparte (regla D2).

---

## 1. Objetivo

Restaurar el contrato AJAX de los 11 filtros inline afectados para que el filtrado de tablas vuelva a funcionar en navegador, sin cambiar semántica de consulta ni comportamiento de negocio.

---

## 2. Alcance

### IN

- 11 métodos `filter(Request $request)` inline que hoy retornan `view(...)` directo:
  1. `Admin\ExpenseCategoryController::filter` — `POST /admin/expense-categories/filter` (`admin.expense-categories.filter`)
  2. `Landlord\ExpenseCategoryController::filter` — `POST /landlord/expense-categories/filter` (`landlord.expense-categories.filter`)
  3. `Admin\IncomeCategoryController::filter` — `POST /admin/income-categories/filter` (`admin.income-categories.filter`)
  4. `Landlord\IncomeCategoryController::filter` — `POST /landlord/income-categories/filter` (`landlord.income-categories.filter`)
  5. `Admin\WithdrawMethodController::filter` — `POST /admin/withdraw-methods/filter` (`admin.withdraw-methods.filter`)
  6. `Landlord\RefundRequestController::filter` — `POST /landlord/refund-request/filter` (`landlord.refund-request.filter`)
  7. `Landlord\SecurityDepositController::filter` — `POST /landlord/security-deposits/filter` (`landlord.security-deposits.filter`)
  8. `Admin\PropertyController::filter` — `POST /admin/properties/filter` (`admin.properties.filter`)
  9. `Landlord\PropertyController::filter` — `POST /landlord/properties/filter` (`landlord.properties.filter`)
  10. `Landlord\RentPaymentController::filter` — `POST /landlord/rent-payments/filter` (`landlord.rent-payments.filter`)
  11. `Admin\RefundRequestController::filter` — `POST /admin/refund-request/filter` (`admin.refund-request.filter`) — mismo bug-clase, vivo, incluido por Q1 (la fila 5.22 decía "~10").
- Contrato canónico por endpoint: AJAX → `200 JSON {"data":"<html>"}`; no-AJAX → `redirect(url()->previous())`.
- Tests de contrato HTTP en `tests/Feature/Routes/FilterShapeContractTest.php`.
- Actualización de la fila 5.22 de `.analisis/plan/plan-accion.md` al cerrar (conteo 11, inventario real).

### OUT

- Migración de estos controladores a `HasTableActions` o extracción de helper compartido (fix mínimo inline, Q3).
- Cambios de query semantics, filtros, `per_page`, eager loads, nombres de vista, variables de vista o payload extra.
- Filtros ya correctos (JSON `{data}` en ajax): p.ej. Tenant, Income/Expense, Notification, Support, Admin SecurityDeposit, Admin RentPayment, Language, Landlord RentInvitation/Application, etc.
- Filtros servidos por `HasTableActions` (ya correctos por diseño).
- Arch-test genérico que prohíba `return view()` en métodos `filter` (falsos positivos en filtros no-AJAX legítimos, Q4).
- Cambios de permisos, middleware, rutas, o comportamiento de negocio.

### Cobertura objetivo

- 11/11 endpoints del alcance con contract test AJAX + fallback no-AJAX (22 aserciones de contrato, agrupadas en 11 casos o 11 tests).

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rutas `*.filter` declaradas y vivas | Existe (verificadas en `routes/admin.php` y `routes/landlord.php`) | Ninguna |
| P2 | Vistas `*.datas` usadas por cada filtro | Existe (render actual ya funciona como HTML) | Ninguna; se reutilizan con `->render()` |
| P3 | Contrato JS consumidor | Existe (`public/backend/assets/js/custom-ajax.js` L73-90, `dataType:"json"`, `$(table).html(res.data)`) | Ninguna; es el contrato a satisfacer |
| P4 | Patrón canónico de referencia | Existe (`HasTableActions::filter`, filtros restaurados 5.17) | Replicar shape, no refactorizar |

---

## 4. Estructura propuesta

```
app/Http/Controllers/Admin/ExpenseCategoryController.php   # filter: view() -> json/redirect
app/Http/Controllers/Admin/IncomeCategoryController.php
app/Http/Controllers/Admin/WithdrawMethodController.php
app/Http/Controllers/Admin/PropertyController.php
app/Http/Controllers/Admin/RefundRequestController.php
app/Http/Controllers/Landlord/ExpenseCategoryController.php
app/Http/Controllers/Landlord/IncomeCategoryController.php
app/Http/Controllers/Landlord/RefundRequestController.php
app/Http/Controllers/Landlord/SecurityDepositController.php
app/Http/Controllers/Landlord/PropertyController.php
app/Http/Controllers/Landlord/RentPaymentController.php
tests/Feature/Routes/FilterShapeContractTest.php           # nuevo
.analisis/plan/plan-accion.md                               # actualizar fila 5.22 al cerrar
```

Sin traits/helpers nuevos. Sin cambios en services, rutas, vistas ni JS.

---

## 5. Decisiones técnicas

Fijadas, no negociables salvo volver el spec a `Borrador`:

- **Contrato canónico**: `if ($request->ajax()) { return response()->json(['data' => view(...)->render()]); } return redirect(url()->previous());` — idéntico al de `HasTableActions::filter` y restauraciones 5.17.
- **Preservación de query**: la llamada al service/query, `per_page`, filtros, eager loads, nombre de vista y variables pasadas a la vista quedan **idénticos** al estado actual. Solo cambia el envoltorio de respuesta.
- **Fix mínimo inline**: no se migra a `HasTableActions`, no se extrae helper, no se toca el trait. Q3.
- **AJAX detection**: se usa `$request->ajax()` (consistente con el patrón canónico). Los tests envían `X-Requested-With: XMLHttpRequest` (patrón `RestoredTableActionsSmokeTest`).
- **No arch-test genérico**: Q4; el riesgo es shape, no método ausente (`RouteMethodsReflectionTest` ya cubre ausencia).
- **Framework/DB/auth tests**: PHPUnit 12, MySQL `dbrentpro_test`, `actingAs` con usuarios/permisos reales de factory/seed helpers del test base; `DatabaseTransactions` o patrón del test base según convención del archivo.

---

## 6. Catálogo de casos

### 6.1 Contrato de shape por filtro (`F`)

Cada caso cubre el mismo endpoint en dos variantes: **AJAX** (contrato consumido por `custom-ajax.js`) y **no-AJAX** (fallback canónico). Se pueden implementar como 1 test con 2 requests o 2 tests por endpoint; el DoD exige ambas aserciones por endpoint.

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Admin expense categories filter shape | admin autenticado con `expense-categories-read`; ≥1 categoría visible en el canal | `POST /admin/expense-categories/filter` con `X-Requested-With: XMLHttpRequest` y payload vacío/búsqueda; luego mismo POST sin header AJAX | AJAX: `200`, JSON con clave `data` string que contiene HTML de la tabla (`expense_categories`). No-AJAX: redirect a `url()->previous()` |
| F2 | Landlord expense categories filter shape | landlord autenticado (grupo `landlord,expired`); ≥1 categoría propia | `POST /landlord/expense-categories/filter` AJAX y no-AJAX | AJAX: `200` JSON `{data}` con HTML de `expense_categories`. No-AJAX: redirect back |
| F3 | Admin income categories filter shape | admin con `income-categories-read`; ≥1 categoría | `POST /admin/income-categories/filter` AJAX y no-AJAX | AJAX: `200` JSON `{data}` con HTML de `income_categories`. No-AJAX: redirect back |
| F4 | Landlord income categories filter shape | landlord autenticado; ≥1 categoría propia | `POST /landlord/income-categories/filter` AJAX y no-AJAX | AJAX: `200` JSON `{data}` con HTML de `income_categories`. No-AJAX: redirect back |
| F5 | Admin withdraw methods filter shape | admin con `withdraw-methods-read`; ≥1 withdraw method | `POST /admin/withdraw-methods/filter` AJAX y no-AJAX | AJAX: `200` JSON `{data}` con HTML de `withdrawMethods`. No-AJAX: redirect back |
| F6 | Landlord refund request filter shape | landlord autenticado; datos mínimos para listar refund requests del landlord | `POST /landlord/refund-request/filter` AJAX y no-AJAX | AJAX: `200` JSON `{data}` con HTML de `refund_requests`. No-AJAX: redirect back |
| F7 | Landlord security deposit filter shape | landlord autenticado; datos mínimos para listar security deposits del landlord | `POST /landlord/security-deposits/filter` AJAX y no-AJAX | AJAX: `200` JSON `{data}` con HTML de `security_deposits`. No-AJAX: redirect back |
| F8 | Admin property filter shape | admin con `properties-read`; ≥1 property visible | `POST /admin/properties/filter` AJAX y no-AJAX | AJAX: `200` JSON `{data}` con HTML de `properties`. No-AJAX: redirect back |
| F9 | Landlord property filter shape | landlord autenticado; ≥1 property propia | `POST /landlord/properties/filter` AJAX y no-AJAX | AJAX: `200` JSON `{data}` con HTML de `properties`. No-AJAX: redirect back |
| F10 | Landlord rent payment filter shape | landlord autenticado; datos mínimos para listar rent payments del landlord | `POST /landlord/rent-payments/filter` AJAX y no-AJAX | AJAX: `200` JSON `{data}` con HTML de `rent_payments`. No-AJAX: redirect back |
| F11 | Admin refund request report filter shape | admin con `reports-read`; datos mínimos para listar refund requests admin/report | `POST /admin/refund-request/filter` AJAX y no-AJAX | AJAX: `200` JSON `{data}` con HTML de `refund_requests` (vista `admin.reports.refund-request.datas`). No-AJAX: redirect back |

> Nota: "HTML de la tabla" se verifica con `assertJsonStructure(['data'])` + `assertIsString`/contenido no vacío y, cuando sea barato, un marcador estable del partial (`assertStringContainsString` de un nombre/ID seedado). No se aserta el HTML completo.

### Tests de caracterización

Ninguno. Este spec corrige un bug de contrato (no-op silencioso), no congela comportamiento inseguro para romper en fase futura.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] Los 11 métodos `filter` del alcance devuelven AJAX JSON `{data}` con HTML renderizado y no-AJAX redirect a `url()->previous()`.
- [x] Ningún método del alcance cambia query/service call, `per_page`, filtros, vista o variables pasadas (diff mecánico: solo envoltorio de respuesta).
- [x] `tests/Feature/Routes/FilterShapeContractTest.php` cubre F1–F11 con ambas variantes (AJAX + no-AJAX) en verde — 11 passed (55 assertions).
- [x] Suite completa en verde (sin regresiones) — 580 passed (6358 assertions).
- [x] PHPStan nivel 5 en verde; Pint en verde.
- [x] Fila 5.22 de `.analisis/plan/plan-accion.md` actualizada al cerrar: conteo 11, inventario real, estado/resultado.
- [x] Sin arch-test genérico nuevo para `filter` (decisión Q4).

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Test data setup para endpoints landlord con relaciones (refund/security/rent payment) | Medio | Reusar factories/helpers existentes; si el setup es pesado, asertar shape con dataset mínimo válido y/o fixture ya usado por smoke tests del módulo |
| Algún filtro asume respuesta HTML no-AJAX en otro consumidor | Bajo | Búsqueda ya hecha: consumidor primario es `custom-ajax.js`; el fallback redirect preserva comportamiento razonable para acceso directo |
| Admin RefundRequest fuera del inventario original 5.22 | Bajo | Incluido por Q1; documentado en alcance y en actualización de fila 5.22 |

**Pendiente de decisión:** ninguna (Q1–Q6 cerradas en grilling 2026-11-23).

---

## 9. Estimación

- Spec + implementación: 0.5–1 día.
  - Fix 11 controllers: ~1h (mecánico, patrón idéntico).
  - Contract tests F1–F11: ~2-3h (setup de datos por canal/entidad).
  - Verificación suite + PHPStan + Pint + actualización plan-accion: ~0.5h.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.22 (L1 expandido).
- `.agents/spec-impl/spec-controladores-residuales-fase5.md` → §11 L1 (3 filtros originales).
- `.agents/spec-impl/spec-restauracion-crud-42-fase5.md` → §2 OUT / errata que creó 5.22.
- `public/backend/assets/js/custom-ajax.js` → L73-90, contrato `dataType:"json"` + `res.data`.
- `app/Traits/HasTableActions.php` → patrón canónico `filter()`.
- `tests/Feature/Routes/RestoredTableActionsSmokeTest.php` → patrón de test AJAX filter.
- `.agents/spec/spec-conventions.md`
- `.agents/spec/spec-template.md`

---

## 11. Resultado

- **Estado:** Implementado (2026-11-23), rama `spec/filter-shape-html-json-fase5-impl`.
- **Tests:** `tests/Feature/Routes/FilterShapeContractTest.php` — F1–F11, 11 passed (55 assertions).
- **Suite:** 580 passed (6358 assertions). PHPStan [OK]. Pint [OK].
- **Desviaciones:** ninguna.
