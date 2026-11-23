---
id: spec-restauracion-crud-42-fase5
titulo: "Restauración CRUD 4.2: 22 rutas rotas restauradas + 32 rutas zombis eliminadas (Fase 5 / 5.17)"
estado: Aprobado
fecha: 2026-11-22
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.17) — DT-C4; desviación D6 del hijo 6 (spec-controladores-residuales-fase5 §12)
cobertura: 54 entradas del allowlist RESTORATION_PENDING: 22 restauradas con contrato observable verbatim pre-4.2 (excel/csv ×14, status ×5, delete-all ×2, filter ×1) + 32 create/edit eliminadas (métodos nunca existieron); allowlist 54→0
---

# Spec — Restauración CRUD 4.2 (Fase 5 / 5.17)

> Cerrar el allowlist de `RouteMethodsReflectionTest`: restaurar las 22 features rotas por la migración 4.2 y eliminar las 32 declaraciones zombis de create/edit.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.17) — DT-C4.

> **Errata de premisa (vs plan-accion 5.17 y D6).** La fila 5.17 asume "implementar los métodos de las 54 rutas referenciadas por vistas vivas (create/edit ×32 …)". La verificación con grep sobre vistas/JS + git-history (2026-11-22) muestra que:
> 1. Solo **22** están referenciadas como **rutas** (status ×5, excel/csv ×14, delete-all ×2, filter ×1) — sus métodos existían pre-4.2 (hooks del trait) y los mató el DoD de los child specs 4.2 ("controladores sin `HasAcnooActions`").
> 2. Las **32 create/edit** no tienen ni una referencia `route()`/URI: los formularios son modales `@include` que postean a `store`/`update` vivos, y sus métodos **nunca existieron en ningún commit** (verbos de `Route::resource` sin `->only()` desde el import inicial `816a734`) — la misma clase-L2 que D6 barrió con las 60 limpias. El conteo D6 las capturó por artefacto de grep: nombre de vista ≡ nombre de ruta (sintaxis de puntos).
>
> Decisión del decisor (2026-11-22): **restaurar 22, eliminar 32**. La fila 5.17 se corrige al cierre de este spec.

---

## 1. Objetivo

Dejar `RouteMethodsReflectionTest::RESTORATION_PENDING` vacío: 22 rutas restauradas funcionales end-to-end con su contrato observable pre-4.2 y 32 declaraciones muertas eliminadas, sin perder funcionalidad (los modales create/edit quedan intactos).

---

## 2. Alcance

### IN
- **Restaurar 22** (métodos thin, patrón D2 — shape JSON `{data}` para XHR, rows vía services, filenames verbatim pre-4.2):
  - **excel/csv ×14:** `admin/landlord` × `expenses`, `incomes`, `security-deposits` + `landlord refund-request` — las 7 Export classes ya existen, los downloads son one-liners.
  - **status ×5:** `admin/landlord expense-categories`, `admin/landlord income-categories`, `admin withdraw-methods`.
  - **delete-all ×2:** `landlord expense-categories`, `landlord income-categories`.
  - **filter ×1:** `admin security-deposits` (Q14-a: verbatim estricto — paginación sobre el branch Admin del service, sin filtros nuevos).
- **Eliminar 32** create/edit (trim de 16 resources con `->only([...])`; solo declaraciones de ruta — vistas y modales intactos).
- **Fix lateral in-scope (Q11):** mismatch `security-deposit` — el seeder siembra singular (`security-deposit-read/update`, solo r,u) y el controller gatea plural → hoy un admin real 403 hasta en el `index` vivo. Pluralizar la key del seeder.
- **ExpiredMiddleware:** añadir `landlord refund-request.excel/csv` al allowlist (familia read-only de index/show).
- Retiro de las 54 entradas del allowlist + errata de la fila 5.17 de plan-accion + fila nueva para el lateral L1 expandido.

### OUT
- **L1 expandido (~10 rutas VIVAS con `filter` shape HTML** cuando el JS espera JSON `{data}` → no-op silencioso): `Admin/Landlord ExpenseCategory::filter`, `Admin/Landlord IncomeCategory::filter`, `Admin WithdrawMethod::filter`, `Landlord RefundRequest::filter`, `Landlord SecurityDeposit::filter`, `Admin/Landlord Property::filter`, `Landlord RentPayment::filter`. Cambia contrato observable de rutas vivas ⇒ spec aparte (regla D2); se registra como fila nueva en plan-accion.
- **L4** `tenants.show` sin gate `tenants-read` — dueño distinto (lateral del slice D1 de 5.13).
- **Tests de services prometidos por los child specs 4.2** (`ExpenseServiceTest`, `IncomeServiceTest`, …) — nunca existieron; deuda de tests registrada, no absorbida aquí.
- **Policies para categorías** (Seg-5: `status` ×5 unscoped y el `deleteAll` del trait unscoped) — el contrato del trait prohíbe scoping unilateral; dueño: slice futuro de policies.
- **Páginas completas create/edit** (patrón vivo de `admin.withdraw-method`) — feature work, no restauración.
- `landlord labors` excel/csv y demás acciones trait-servidas de los adopters — VIVAS, no tocar.

### Cobertura objetivo
- 54/54 entradas del allowlist retiradas (22 restauradas con contrato verificado + 32 eliminadas); `RESTORATION_PENDING = []` al cierre.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Guards en `main` (línea apilada merged) | Resuelto — PR #64 + #66: `RouteMethodsReflectionTest` (54) y arch-tests vigentes | Verificar al partir la rama |
| P2 | Contrato verbatim pre-4.2 | Resuelto — inventario `spec-trait-acnoo-fase0` §12 + blobs `git show 537a626^:app/…` | La impl extrae strings exactos (filenames/mensajes) del blob; §12 como cross-check |
| P3 | Export classes | Resuelto — 7 clases (`AdminExpensesExport`, `AdminExportIncome`, `AdminSecurityDepositExport`, `LandlordExpensesExport`, `LandlordIncomeExport`, `LandlordSecurityDepositExport`, `LandlordRefundRequestExport`) | Ninguna |
| P4 | Methods de services | Parcial — `deleteMany` existe (ExpenseCategory/IncomeCategoryService, scoped); `updateStatus` falta | Añadir `updateStatus` ×3 (unscoped — contrato del trait, ver §5) |
| P5 | Permiso `security-deposits-*` | Falta — seeder siembra singular; controller gatea plural | Fix Q11: pluralizar la key del seeder |
| P6 | Red de tests | Falta — 0 cobertura HTTP para las entidades del 54 | Este spec trae su propia red (G/H/F/U, 27 casos) |

---

## 4. Estructura propuesta

```
routes/admin.php                                        # trim ×8 resources (create/edit fuera)
routes/landlord.php                                     # trim ×8 resources
app/Http/Controllers/Admin/
  ExpenseController.php                                 # +exportExcel/exportCsv
  IncomeController.php                                  # +exportExcel/exportCsv
  SecurityDepositController.php                         # +filter/exportExcel/exportCsv + append gate -read
  ExpenseCategoryController.php                         # +status
  IncomeCategoryController.php                          # +status
  WithdrawMethodController.php                          # +status
app/Http/Controllers/Landlord/
  ExpenseController.php                                 # +exportExcel/exportCsv
  IncomeController.php                                  # +exportExcel/exportCsv
  SecurityDepositController.php                         # +exportExcel/exportCsv
  RefundRequestController.php                           # +exportExcel/exportCsv
  ExpenseCategoryController.php                         # +status/deleteAll
  IncomeCategoryController.php                          # +status/deleteAll
app/Services/
  ExpenseCategoryService.php                            # +updateStatus
  IncomeCategoryService.php                             # +updateStatus
  WithdrawMethodService.php                            # +updateStatus
app/Http/Middleware/ExpiredMiddleware.php               # +2 allowlist (refund-request excel/csv)
database/seeders/PermissionSeeder.php                   # key 'security-deposit' → 'security-deposits'
tests/Feature/Routes/RouteMethodsReflectionTest.php     # RESTORATION_PENDING → []
tests/Feature/Admin/RestoredCrudActionsHttpTest.php     # red G
tests/Feature/Landlord/RestoredCrudActionsHttpTest.php  # red H (+F/U)
```

Los controllers trait-adopters (`Admin/Landlord Amenity/Facility`, `Admin HouseType/RefundReason`, `Landlord Labor/WithdrawMethod`) **no ganan métodos**: solo pierden las declaraciones create/edit.

---

## 5. Decisiones técnicas

Fijadas por el decisor (sesión de diseño 2026-11-22, Q1–Q14); no negociables salvo volver a Borrador:

- **Contrato observable verbatim pre-4.2; implementación thin vigente (Q3/Q7, patrón D2):** shapes `{data}`, redirects, mensajes y filenames del inventario `spec-trait-acnoo-fase0` §12 — filenames: `admin-expenses`, `admin-incomes`, `landlord-expenses`, `landlord-incomes`, `security-deposits` (ambas capas), `refund-request`. Métodos delgados que delegan en services; **no** re-adoptar `HasTableActions` en los service-migrated (las 22 viven todas en controladores service-migrated).
- **status ×5 unscoped (contrato del trait, Seg-5):** `updateStatus` hace `Model::findOrFail($id)->update(...)` sin scoping — el trait lo documenta como contrato preservado 1:1; prohibido añadir scoping unilateralmente. H7 lo caracteriza.
- **deleteAll ×2 scoped (desviación security-positive, cubierta por Q7):** usa el `deleteMany` del service (scoped a `landlord_id`) — diverge del verbatim unscoped del trait pre-4.2 y es consistente con el `destroy` vivo de los mismos controladores. Desviación documentada aquí.
- **filter admin security-deposits verbatim estricto (Q14-a):** paginación sobre el branch Admin actual de `SecurityDepositService::listForChannel` (sin search/status/date), como pre-4.2; los inputs del form quedan como no-op silencioso. Residual documentado.
- **Gates (Q8):** solo append en `Admin/SecurityDepositController` (`security-deposits-read` → `filter,exportExcel,exportCsv`); los demás constructores ya listan los métodos (`expenses-read`/`incomes-read` → exports; `*-update` → status; `*-delete` → deleteAll). Sin cambios en trait-adopters.
- **Fix Q11:** pluralizar la **key del seeder** (`security-deposit` → `security-deposits`, alineado con la ruta y el controller); las filas `security-deposit-*` stale en DBs ya sembradas quedan como residual (patrón 5.2). El controller no se toca.
- **DemoMode: 0 cambios** — las 7 restauradas mutantes ya están en las listas (3 admin status + 4 landlord status/delete-all); siguen bloqueando (499) tras restaurar.
- **Limpieza 32 = solo rutas:** los modales `@include` y sus POST a `store`/`update` vivos quedan intactos; sin cambios de vistas.
- **Tests:** PHPUnit 12, `#[Group('smoke')]`, `RefreshDatabase` + `seed(PermissionSeeder::class)`, trait `CreatesUsers`, `PlanSubscribe::factory()` para landlords (middleware `expired`); gates probados con rol **admin real** (no superadmin — `Gate::before` enmascara fallos de permiso). Sin tx en controladores (arch-test U2); `Request` inyectado; 0 `env()`.

---

## 6. Catálogo de casos

### 6.1 Restauración capa Admin (`G`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| G1 | expenses exports | admin con `expenses-read`, filas sembradas | GET `admin/expenses-excel` + `admin/expenses-csv` | 200 download; filenames `admin-expenses.xlsx`/`.csv` (content-disposition, verbatim §12) |
| G2 | incomes exports | admin con `incomes-read` | GET `admin/incomes-excel` + `-csv` | 200; `admin-incomes.*` |
| G3 | security-deposits exports | admin con `security-deposits-read` (post-P5) | GET `admin/security-deposits-excel` + `-csv` | 200; `security-deposits.*` |
| G4 | filter XHR | ídem + depósitos del scope admin | POST `admin/security-deposits/filter` con `X-Requested-With` | 200 JSON `{data}` con filas renderizadas (paginación; Q14-a) |
| G5 | filter no-ajax | ídem | POST sin header XHR | redirect a URL previa |
| G6 | status expense-categories | admin + categoría | POST `admin/expense-categories/status/{id}` `{status: 1}` | 200 JSON `{message}` verbatim §12 + `status=1` en BD |
| G7 | status income-categories | ídem | POST `admin/income-categories/status/{id}` | ídem |
| G8 | status withdraw-methods | ídem | POST `admin/withdraw-methods/status/{id}` | ídem (statusMessage `'withdrawMethod'`) |
| G9 | fix Q11 end-to-end | **admin rol real** (no superadmin) con permiso seeded | GET index + rutas de G3/G4 | 200 (pre-fix: 403 — el gate vivo heredaba el mismatch) |
| G10 | gate denial | admin sin `security-deposits-read` | GET `admin/security-deposits-excel` | 403 |
| G11 | demo bloquea status | `APP_ENV=demo` + admin | POST las 3 status | 499 JSON `'This action is disabled in demo mode.'` |

### 6.2 Restauración capa Landlord (`H`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| H1 | expenses exports | landlord suscrito (`PlanSubscribe`) | GET `landlord/expenses-excel` + `-csv` | 200; `landlord-expenses.*` |
| H2 | incomes exports | ídem | GET `landlord/incomes-excel` + `-csv` | 200; `landlord-incomes.*` |
| H3 | security-deposits exports | ídem | GET `landlord/security-deposits-excel` + `-csv` | 200; `security-deposits.*` |
| H4 | refund-request exports | ídem | GET `landlord/refund-request-excel` + `-csv` | 200; `refund-request.*` |
| H5 | exports sin suscripción | landlord **sin** `PlanSubscribe` | ídem H4 | 200 (allowlist ExpiredMiddleware Q12; pre-fix: muro expired) |
| H6 | status expense-categories | landlord + categoría propia | POST `landlord/expense-categories/status/{id}` | 200 JSON `{message}` verbatim §12 + BD flip |
| H7 | status ajeno | 2 landlords; categoría del otro | POST status con id ajeno | 200 + BD flip — **caracterización Seg-5** |
| H8 | delete-all expense-categories | landlord + 3 categorías propias | POST `landlord/expense-categories/delete-all` `{ids: […]}` | 200 JSON `{message, redirect: index}` + propias borradas |
| H9 | delete-all scoped | ídem + 2 ajenas mezcladas | POST delete-all con ids propios+ajenos | propias borradas, **ajenas intactas** (deleteMany del service) |
| H10 | income-categories espejo | ídem H6–H9 | POST `landlord/income-categories/status/{id}` + delete-all | ídem (status comparte la caracterización Seg-5 de H7) |
| H11 | demo bloquea | `APP_ENV=demo` + landlord | POST las 4 status/delete-all | 499 (ya listadas en DemoMode) |

> **Caracterización de Seg-5** — H7 (y el status de H10) congelan el lookup unscoped del trait; se espera que fallen cuando las policies cubran categorías.

### 6.3 Limpieza create/edit (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | 32 eliminadas | árbol tras el trim | `Route::has()` sobre los 32 nombres | `false` ×32 (16 resources × create/edit) |
| F2 | modales admin intactos | admin | GET `admin/amenities` + POST `admin/amenities` (form del modal) | index 200 con modal incluido + fila creada (store vivo sin cambios) |
| F3 | modales landlord intactos | landlord suscrito | GET `landlord/labors` + POST `landlord/labors` | ídem |

### 6.4 Guards (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | reflexión permanente | árbol completo | `RouteMethodsReflectionTest` | `RESTORATION_PENDING = []` + 0 muertas fuera del allowlist (guard bidireccional permanente) |
| U2 | arch-tests | controladores tocados | `ControllersLineLimitArchTest` | 100% < 200 líneas; 0 tx fuera del allowlist de 9 |

### Tests de caracterización

| ID | Bug caracterizado | Fase que lo romperá |
|----|-------------------|---------------------|
| H7 | Seg-5 (status unscoped sobre fila ajena — contrato del trait) | Policies para categorías |

---

## 7. Criterios de aceptación (Definition of Done)

- [ ] `RouteMethodsReflectionTest` en verde con `RESTORATION_PENDING = []` (las 54 retiradas; guard permanente).
- [ ] 22 restauradas funcionales: G1–G11 + H1–H11 en verde, con filenames verbatim (content-disposition) y mensajes verbatim §12/blob.
- [ ] 32 eliminadas: F1 verde; modales intactos (F2/F3); grep previo sobre referencias a los 32 nombres (tests/vistas/JS/DemoMode) sin adaptaciones ocultas — cualquier adaptación documentada como desviación.
- [ ] Fix Q11 verificado: `PermissionSeeder` siembra `security-deposits-read/update`; G9 verde con rol admin real; residual de filas stale documentado.
- [ ] ExpiredMiddleware: H5 verde (allowlist +2).
- [ ] DemoMode: G11/H11 verde **sin cambios en las listas**.
- [ ] Suite completa + PHPStan nivel 5 (0 adiciones a baseline) + Pint en verde.
- [ ] Arch-tests en verde: todos los controladores < 200 tras los añadidos; 0 tx nuevas.
- [ ] Errata aplicada: fila 5.17 de plan-accion reescrita (22 restauradas + 32 eliminadas, no 54 implementaciones) + fila nueva para L1 expandido (~10).

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Divergencia de strings entre §12 y el blob pre-4.2 | bajo | La impl extrae del blob (`git show 537a626^:…`); §12 como cross-check; tests de content-disposition congelan |
| Tests congelados que referencien las 32 eliminadas | medio | grep previo (precedente D6: F17/F9–F10); adaptación documentada como desviación |
| Bit-rot de vistas al restaurar | bajo (index/datas se renderizan hoy vía index vivo) | Fix mínimo funcional in-scope (Q4); cambios estructurales OUT |
| Fix Q11 en DBs ya sembradas | bajo | Filas `security-deposit-*` stale; re-seed crea las nuevas; residual patrón 5.2 |
| `updateStatus` unscoped dentro de un service scoped | bajo | Comentario citando el contrato del trait + H7 caracteriza; prohibido el scoping unilateral |

**Pendiente de decisión:** ninguna — las bifurcaciones (estructura, verbatim, eliminar 32, fix Q11, Q14-a verbatim estricto, L1 OUT, estimación 4–6 días) fueron cerradas por el decisor el 2026-11-22.

---

## 9. Estimación

- Restauración 22 (14 exports one-liner, 5 status + 3 `updateStatus`, 2 deleteAll, 1 filter): 2–3 días
- Limpieza 32 + retiro de allowlist: 0.5–1 día
- Red de tests G/H/F/U (27 casos): 1–1.5 días
- Validación (suite/PHPStan/Pint/arch-tests + errata plan-accion): 0.5 día

**Total: 4–6 días.**

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.17
- `.analisis/seguridad.md` → Seg-5 · `.analisis/deuda-tecnica.md` → DT-C4 / DT-A1
- `.agents/spec/spec-conventions.md` · `.agents/spec/spec-template.md`
- `.agents/spec-impl/spec-controladores-residuales-fase5.md` → §11 (D2/D3) y §12 (D6: origen del allowlist)
- `.agents/spec-impl/spec-trait-acnoo-fase0.md` → §12 (inventario verbatim: filenames/mensajes/per-page)
- `.agents/spec-impl/spec-unificar-capas-services-fase5.md` → L105 (decisión D2 re-statada)
- `tests/Feature/Routes/RouteMethodsReflectionTest.php` (54 entradas) · `tests/Feature/Routes/RestoredTableActionsSmokeTest.php` (patrón de la red)
