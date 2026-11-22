---
id: spec-controladores-residuales-fase5
titulo: "Residuales < 200: 2ª pasada Property, DashboardService, trait RespondsJson, arch-test (Fase 5 / 5.13, hijo 6)"
estado: Aprobado
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.13) — DT-C4
cobertura: Última milla del criterio "< 200 estricto": Admin/PropertyController 225, Landlord/PropertyController 200, Api/LandlordDashboardController 228, Controller.php base 235, y activación del arch-test permanente
---

# Spec — Controladores residuales + arch-test (Fase 5 / 5.13, hijo 6)

> Cierra el criterio `< 200` estricto: los 4 controladores restantes sobre el umbral (incluido el ya migrado Property, que necesita 2ª pasada) y el `Controller.php` base. Al final activa `ControllersLineLimitArchTest` con las 2 reglas del paraguas — a partir de aquí el criterio es auto-enforced.

**Paraguas:** `spec-unificar-capas-services-fase5.md` (§5.2 define las reglas del arch-test).

## 1. Objetivo

`Admin/PropertyController` (225), `Landlord/PropertyController` (200), `Api/LandlordDashboardController` (228) y `Controller.php` (235) quedan < 200; el arch-test queda activo en CI.

## 2. Alcance

### IN
- 2ª pasada Property (los 2 controladores ya migrados en Fase 4; recorte de presentación → `PropertyService::loadForShow`/helpers, sin tocar contrato).
- `app/Services/DashboardService.php` (solo Api/LandlordDashboard: index, property_chart, account_chart).
- `app/Traits/RespondsJson.php`: los 15 helpers `respondWith*` + el array `$message` legado salen de `Controller.php`.
- `tests/Unit/Controllers/ControllersLineLimitArchTest.php` (2 reglas) + test de reflexión de rutas del paraguas.

### OUT
- `Admin/AdminDashboardController` (129) y `Landlord/DashboardController` (159) — < 200, quedan tal cual (duplicación residual documentada en el paraguas §2 OUT).
- `Admin/TranslationController` y el resto de controladores < 200.
- Cambios de contrato observable en Property/Dashboard.

### Cobertura objetivo
- 12/12 controladores < 200 (evidencia `wc -l`); arch-test activo; suite + PHPStan + Pint en verde.

## 3. Pre-requisitos

| # | Pre-requisito | Estado | Acción |
|---|---|---|---|
| P1 | Hijos 1–5 mergeados | Pendiente | **Desviación D1 (aprobada por decisor):** slice anticipado — se implementan las unidades independientes de P1 (trait, dashboard, property, rutas 4.2); arch-test U1/U2 y reflexión U3 completa quedan diferidos hasta que los hijos 1–5 aterricen |
| P2 | Rutas muertas del paraguas (U1–U3) limpiadas | Resuelto en el slice | Ver Revisión D2: las 13 "muertas" resultaron features rotas (las vistas las referencian) → restauradas; solo las 2 OTP (sin referencias) se eliminan; guard scoped aterriza en su lugar |
| P3 | Red de Property | Verde (F1–F29 spec 4.2 + ownership) | Sin regresiones en la 2ª pasada |
| P4 | `$message` usado por `Api/UserController` (4 refs L157–171) | Verificado | El array se MUEVE al trait, no se elimina |

## 4. Estructura propuesta

```
app/Services/DashboardService.php
app/Traits/RespondsJson.php
app/Http/Controllers/Controller.php            # ~20 líneas
app/Http/Controllers/Api/LandlordDashboardController.php   # < 200
app/Http/Controllers/Admin/PropertyController.php          # < 200 (2ª pasada)
app/Http/Controllers/Landlord/PropertyController.php       # < 200 (recorte trivial)
tests/Unit/Controllers/ControllersLineLimitArchTest.php
tests/Feature/Dashboard/ApiLandlordDashboardHttpTest.php   # caracterización previa
```

## 5. Decisiones técnicas

- **2ª pasada Property = solo presentación:** mover enriquecimiento de vistas/shape JSON a `PropertyService` (o helpers privados del service); las rutas/vistas/JSON quedan idénticos. Red: F1–F29 del spec 4.2 + `PropertyOwnershipSmokeTest` + `IdorPolicySmokeTest`.
- **`DashboardService` mínimo:** solo `Api/LandlordDashboardController` (228) cruza el umbral; admin/landlord web quedan (paraguas §2 OUT).
- **Trait `RespondsJson`:** los 15 `respondWith*` + `$message` se mueven **tal cual** (comportamiento idéntico; `$message` NO se elimina — lo consume `Api/UserController`). Follow-up fuera de scope: reemplazar `$message[n]` por claves de traducción.
- **Arch-test (paraguas §5.2), sin grandfather list:**
  1. Todo `.php` en `app/Http/Controllers/` (recursivo) < 200 líneas (`count(fileLines)`, sin contar saltos finales).
  2. Cero `DB::beginTransaction` / `DB::transaction(` en `app/Http/Controllers/`.
  Estilo `SeedersArchTest` (lee archivos, sin DB).
- Test de reflexión rutas↔métodos (cierre permanente del bug de rutas muertas, paraguas §6).

## 6. Catálogo de casos (prefijo `F` HTTP, `U` unit)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Dashboard api index | Sanctum landlord con datos | GET /api/v1/landlord/dashboard | 200 JSON shape actual congelado |
| F2 | Dashboard charts | Sanctum landlord | GET .../dashboard/property-chart y account-chart | 200 shapes actuales congelados |
| F3–F6 | Property 2ª pasada | red F1–F29 del spec 4.2 | re-ejecutar suite Property | verde sin cambios (regresión pura) |

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | Límite global | árbol de controladores actual | correr arch-test | 100% archivos < 200 líneas |
| U2 | Sin tx en controladores | ídem | grep `DB::beginTransaction\|DB::transaction(` | 0 matches en `app/Http/Controllers/` |
| U3 | Rutas ↔ métodos | todas las rutas declaradas (6 archivos) | reflexión | todo route name apunta a método existente |
| U4 | `$message` intacto | `Api/UserController` | llamar endpoints que usan `$this->message[0]`/`[6]` | mensajes idénticos tras el trait |

> F1–F2 son caracterizaciones nuevas (hoyo actual de dashboard api); U1–U3 son los arch-tests; U4 congela la compatibilidad del trait.

## 7. Criterios de aceptación (Definition of Done)

- [x] `wc -l`: Admin/Property **191**, Landlord/Property **164**, Api/LandlordDashboard **46**, `Controller.php` **14** (< 30).
- [ ] `ControllersLineLimitArchTest` (U1–U2) y reflexión de rutas (U3) activos y en verde en CI. **Diferido (D1):** requiere hijos 1–5 mergeados; en su lugar aterrizó el guard scoped `RestoredTableActionsSmokeTest` (11 rutas restauradas resuelven + 2 OTP eliminadas).
- [x] Caracterización F1–F2 en verde antes de migrar dashboard ✓; red Property (F3–F6) en verde tras la 2ª pasada ✓ (suite completa 387 passed).
- [x] `Api/UserController` sin cambio (U4) ✓ — mensajes byte-idénticos tras el trait (shape anidado congelado).
- [ ] Suite + PHPStan + Pint en verde ✓ (387 passed / 4654 assertions; PHPStan OK, baseline −9 entradas huérfanas, 0 nuevas); `plan-accion.md` 5.13 cerrado — **pendiente de hijos 1–5**.

## 8. Riesgos

| Riesgo | Mitigación |
|--------|------------|
| 2ª pasada de Property reintroduce duplicación | Solo mueve presentación al service ya existente; red F1–F29 completa |
| Arch-test frágil (líneas con espacios/CRLF) | Conteo simple de líneas tipo `SeedersArchTest`; umbral fijo 200 |
| Alguien "arregla" el arch-test excluyendo archivos | Regla del paraguas: sin grandfather list; tocarlo requiere desviación documentada |

## 9. Estimación

- 3–5 días (dashboard 1, property 2ª pasada 1, trait 0.5, arch-tests 0.5, validación 0.5–1).

## 10. Referencias

- `spec-unificar-capas-services-fase5.md` (§5.2, §6) · `spec-unificar-capas-services-fase4.md` (red F1–F29)
- `.analisis/plan/plan-accion.md` → Fase 5 / 5.13 · `tests/Unit/Seeders/SeedersArchTest.php` (estilo)

## 11. Revisión — slice anticipado (2026-11-21)

Implementado en rama `spec/controladores-residuales-fase5-impl`. El decisor aprobó arrancar este hijo antes que los hijos 1–5, recortando el alcance a las unidades independientes de P1.

### Desviaciones

| # | Desviación | Detalle |
|---|-----------|---------|
| D1 | Slice anticipado | P1 (hijos 1–5) sigue pendiente ⇒ arch-test U1/U2 y reflexión U3 completa diferidos. Aterrizó el guard scoped `tests/Feature/Routes/RestoredTableActionsSmokeTest.php` (8 casos: reflexión de las 11 rutas restauradas, OTP eliminadas, filter/excel/csv HTTP por capa). DoD parcial: 4/5 boxes con evidencia; el quinto espera a los hijos. |
| D2 | Rutas "muertas" 4.2 = features rotas | El grep previo a la eliminación (proceso Q12 del paraguas) reveló que 11 de las 13 rutas referenciadas estaban usadas por vistas vivas (forms `.filter-form` + dropdowns de export en tenants/rent-payments de ambas capas) — hoy hacían 500 (`BadMethodCallException`). Decisión del decisor: **restaurar** (trait-shape JSON `{data}` para XHR, rows vía services, filenames verbatim de los hooks pre-4.2) en vez de borrar. Solo `tenants.otp-submit/otp-resend` (sin referencias) se eliminan. La premisa del paraguas §6/U1–U2 queda desactualizada — ver corrección en el paraguas. |
| D3 | Gate de permisos restaurado | `Admin/RentPaymentController`: `filter`/`file_download`/`exportExcel`/`exportCsv` vuelven bajo `rent-payments-read` (gate pre-4.2 perdido en 4.2; `file_download` estaba sin gate desde 4.2 — security-positive). |

### Hallazgos laterales (follow-up fuera de este slice)

- **L1 — filters con shape HTML:** `Admin/Landlord PropertyController::filter` y `Landlord/RentPaymentController::filter` devuelven la vista directa, pero el JS (`public/backend/assets/js/custom-ajax.js`, `dataType: "json"`, `res.data`) espera JSON `{data: html}` → no-op silencioso en el navegador. Mismo bug-clase que D2 sin el 500. Corrección recomendada: alinear al shape del trait en un slice propio (cambia contrato observable ⇒ spec aparte).
- **L2 — inventario completo de rutas muertas (~110):** la reflexión total (la que U3 exigiría) detecta ~110 rutas más apuntando a métodos inexistentes: verbos de `Route::resource` sin `->only()` en ~25 declaraciones (transactions, website-settings, comments, incomes, expenses, labors, facilities…) y rutas de entidades de los hijos 2–5 (`rents.create/edit/update/destroy` → hijo 2). Reparto propuesto: cada hijo limpia sus entidades (regla paraguas §5.4) + un slice de higiene de resources para los no-asignados; U3 completo nace verde al final.
- **L3 — baseline PHPStan:** −9 entradas huérfanas (5 del dashboard + 4 de los property controllers tras mover el código a services), 0 nuevas; el `formatData` heredado reescrito lookup-equivalente (`$row === null ? 0 : ...`) para no resurrectar los ignores de `nullsafe.neverNull` del código viejo.

### Evidencia (DoD parcial)

- `php artisan test` → **387 passed (4654 assertions)**: 371 base + 4 F1–F2 + 2 U4 + 2 U17/U18 + 8 guard.
- `vendor/bin/phpstan analyse` → `[OK] No errors`; `vendor/bin/pint --test` → passed.
- `wc -l`: Admin/Property 191 (antes 225) · Landlord/Property 164 (199) · Api/LandlordDashboard 46 (228) · `Controller.php` 14 (235).
