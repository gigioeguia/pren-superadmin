---
id: spec-controladores-residuales-fase5
titulo: "Residuales < 200: 2ª pasada Property, DashboardService, trait RespondsJson, arch-test (Fase 5 / 5.13, hijo 6)"
estado: Implementado
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
- [x] `ControllersLineLimitArchTest` (U1–U2) y reflexión de rutas (U3) activos y en verde. **Evidencia (D1, 2026-11-22):** `tests/Unit/Controllers/ControllersLineLimitArchTest.php` — U1: 377 archivos de controlador < 200 (incluido `Admin/TenantController` 206→**193** tras su 2ª pasada, fuera de la lista de los 12 — desviación D4, con contract test `AdminTenantShowHttpTest` F1 en verde); U2: 0 tx fuera del allowlist de 9 con justificación por entrada + guard bidireccional (desviación D5, decisor). `tests/Feature/Routes/RouteMethodsReflectionTest.php` — U3: 114 rutas muertas→**54** (60 removibles con grep-previo: 52 sin referencias, 7 solo-DemoMode, 1 F17), 54 restantes en allowlist temporal con guard que fuerza su retiro (desviación D6, decisor). **En CI cuando los PRs de la línea apilada mergueen.**
- [x] Caracterización F1–F2 en verde antes de migrar dashboard ✓; red Property (F3–F6) en verde tras la 2ª pasada ✓ (suite completa 387 passed).
- [x] `Api/UserController` sin cambio (U4) ✓ — mensajes byte-idénticos tras el trait (shape anidado congelado).
- [x] Suite + PHPStan + Pint en verde. **Evidencia (D1):** `php artisan test` **488 passed (5876 assertions)** — 486 de la línea post-hijo-5 + F1 tenant + reflexión U3 + 2 arch-tests − F9/F10 obsoletas; `composer analyse` [OK]; `composer lint` passed. `plan-accion.md` 5.13 actualizado con implementación en rama apilada (merges pendientes) y las 2 deudas nuevas registradas (9 tx, 54 rutas).

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

### Verificación (2026-11-22, sobre la línea con hijos 2–5 implementados in-tree)

**Slice entregado — sigue en verde:**
- `wc -l` re-verificado: `Controller.php` **14** · Api/LandlordDashboard **46** · Admin/Property **192** (±1 vs 191 por ediciones de hijos 2–5; < 200) · Landlord/Property **165**.
- Tests del hijo en verde: `ApiLandlordDashboardHttpTest` (F1–F2, 4) + `RestoredTableActionsSmokeTest` (guard, 8) + `RespondsJsonCompatTest` (U4, 2) — dentro de la suite completa **486 passed (5500 assertions)** (las 387 del corte crecieron con la red de hijos 2–5).
- `tests/Unit/Controllers/ControllersLineLimitArchTest.php` **no existe** — consistente con D1.

**D1 (arch-test + reflexión) — diferido sigue vigente; bloqueos cuantificados hoy:**

| Regla | Bloqueo actual | Origen |
|---|---|---|
| U1 (< 200 global) | `Admin/TenantController` **206** (único ≥ 200) | Nunca estuvo en la lista de los 12 del paraguas: la cobertura "12/12 < 200" se cumple, pero la regla global del arch-test lo captura. TenantService ya existe — es un recorte/2ª pasada no asignado a ningún hijo |
| U2 (0 tx en controllers) | **9 hits** `DB::beginTransaction/transaction(`: Api/UserController, Api/SecurityDepositController, Api/Auth/RegistrationController (tx conservada intencionalmente por hijo 4 §11), Api+Landlord/WithdrawController, Admin/WithdrawRequestController, Admin+Landlord/ProfileController, Admin/RoleController | El paraguas §5.2 asumía "los 8 con tx están todos en scope" — inexacto: withdraw/profile/role/user/security-deposit son deuda lateral OUT (paraguas §2 OUT) |
| U3 (reflexión rutas↔métodos) | **114 rutas muertas** confirmadas por reflexión real (coincide con L2 ~110): verbos de `Route::resource` sin `->only()` (~25 declaraciones: transactions, website-settings, comments, incomes, expenses, facilities, amenities…) + verbos api (`apitenant.destroy`, `apisupports.*`, `apilandlord-maintenance.index`) | Requiere el slice de higiene de resources del paraguas + la limpieza por entidad (regla §5.4) que los hijos 2–5 no ejecutaron (sus specs declararon las rutas "intactas" en OUT) |

**Conclusión:** el hijo NO puede cerrarse todavía — el quinto checkbox espera (a) recorte de TenantController < 200, (b) decisión sobre las 9 tx (migrar withdraw/profile/role a services o desviación documentada de la regla U2), (c) slice de higiene de las 114 rutas muertas. Ninguno está en el scope aprobado de este hijo; el estado `Aprobado` (slice anticipado D1) es correcto mientras tanto.

## 12. Implementación D1 (2026-11-22) — rama `spec/controladores-residuales-fase5-d1`

Cierre del diferido con los hijos 1–5 **implementados in-tree** en la línea apilada (merges de PRs pendientes en orden: language→rent→application→landlord→payment→residuales). El decisor resolvió los tres bloqueos en la misma sesión:

| # | Desviación | Detalle |
|---|-----------|---------|
| D4 | TenantController (U1) | 206→**193**: 2ª pasada presentación (ver §5) — `show()` eager-load → `TenantService::loadForShow` (verbatim), selects countries/phone_codes a helper. Contract test `AdminTenantShowHttpTest` F1 congelado en verde pre-recorte. El controlador nunca estuvo en la lista de los 12 del paraguas: la regla GLOBAL del arch-test lo capturó. Hallazgo lateral L4: `show` sigue sin gate `tenants-read` (preexistente, fuera de scope). |
| D5 | Allowlist U2 (9 tx) | La premisa del paraguas §5.2 ("los 8 con tx están todos en scope") era inexacta. Decisor: allowlist documentada con guard bidireccional — la tx NUEVA en cualquier controlador rompe CI; retirar una entrada exige que ya no declare tx. 1 entrada es diseño intencional (Api/Auth/Registration, hijo 4 §11); 8 son deuda lateral OUT (withdraw ×3, profile ×2, role, api user, api security-deposit) rastreadas en plan-accion para migración con specs propios. |
| D6 | U3 escalonado (114→54) | Decisor: las 54 rutas referenciadas por vistas vivas NO se restauran aquí — son otro PR tan grande como un hijo (mismo bug-clase D2, ~16 entidades). Este slice remueve las **60 limpias** (52 sin referencias — grep-previo sobre vistas/JS/DemoMode/app/routes/tests/lang; 7 solo-DemoMode con limpieza de sus listas; 1 congelada por F17) y activa `RouteMethodsReflectionTest` con allowlist de las 54 + guard que falla si una se restaura y no se retira. Slice futuro: **"restauración CRUD 4.2"** (spec propia, como D2). |

**Adaptaciones de tests de estado congelado (documentadas):** `RouteNamesSmokeTest` F17 (la ruta muerta `apilandlord-maintenance.index` pasa de assertNotNull a assertNull — su método no existía desde siempre; las rutas explicitas de maintenance quedan vivas); `DemoModeTest` F9/F10 eliminadas (protegían rutas que dejan de existir — `admin.transactions.update/destroy`; la cobertura restante de DemoMode sigue verde vía F6/F13, que ahora validan contra las listas limpias).

**Fix flaky preexistente:** `RestoredTableActionsSmokeTest` fallaba intermitente por nombres de faker con apóstrofe ("O'Reilly") — Blade los escapa a `&#39;` en el HTML renderizado y el assert del nombre crudo no matchea. Nombres fijos sin caracteres HTML.

**Evidencia:** suite completa **488 passed (5876 assertions)**; `composer analyse` [OK]; `composer lint` passed; reflexión post-corte = exactamente las 54 del allowlist (0 nuevas, 0 restauradas-sin-retiro).

**Commits:** `4184458` (U1: 2ª pasada TenantController + F1) → `21fcb75` (U3: 60 removidas + DemoMode/F17/F9-F10 + reflexión + arch-tests U1/U2).
