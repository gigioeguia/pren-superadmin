---
id: spec-service-landlord-fase5
titulo: "Service Landlord — unificar alta de landlord entre Admin, Signup web y Api Registration (Fase 5 / 5.13, hijo 4)"
estado: Aprobado
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.13) — DT-A1, DT-C4
cobertura: Migración de Admin/LandlordController (425) a app/Services/LandlordService.php y absorción en el service de la creación de landlord por canal (SignupController 251, Api/Auth/RegistrationController 221); el trío free-plan queda en una única implementación con ramas por canal
---

# Spec — Service Landlord (Fase 5 / 5.13, hijo 4)

> `Admin/LandlordController` migra a `LandlordService`; la creación de `User` por canal y el trío free-plan (triplicados entre admin, signup web y registration api) quedan en el service — una implementación cada uno, divergencias preservadas. Los tres controladores quedan < 200 líneas.

**Patrón de referencia:** `spec-unificar-capas-services-fase4.md` §8. **Paraguas:** `spec-unificar-capas-services-fase5.md`.

## 1. Objetivo

`Admin/LandlordController` (425), `Auth/SignupController` (251) y `Api/Auth/RegistrationController` (221) quedan < 200, con la creación de `User` por canal y el trío free-plan absorbidos por el service y el CRUD admin de landlords en el service.

## 2. Alcance

### IN
- `app/Services/LandlordService.php`: `list`, `store` (admin CRUD: `User::create` + `UserDetail` + trío), `update`, `delete`, `deleteMany` + **`createAccount`** (web/api: `updateOrCreate`, columnas divergentes por canal) + **`activateFreePlan`** (trío free-plan con campos por canal — única implementación).
- Refactor in-place de los 3 controladores.
- Tests de contrato HTTP previos; `LandlordStoreSubscriptionTest` (G6–G7), `SubscriptionPaidTest` (G8–G9), `WebSignupMassAssignmentTest` (Seg-28), `DemoModeTest` (F1–F12, admin/landlord) y smoke auth como red de regresión.

### OUT
- Flujos OTP de signup/registration (viven en `App\Support\Otp` desde 4.6; los controladores los orquestan y siguen haciéndolo — incluido el envío de `WelcomeMail` post-commit).
- `Admin/UserController` (183) y el resto de auth (login/reset).
- Rutas `landlords/*` y de signup/registration: intactas.

### Cobertura objetivo
- 3 controladores < 200; una sola implementación del trío free-plan y de la creación de `User` por canal; suite + PHPStan + Pint en verde.

## 3. Pre-requisitos

| # | Pre-requisito | Estado | Acción |
|---|---|---|---|
| P1 | Hijo 3 (application) mergeado | Pendiente | Orden del paraguas (implementación apilada como hijos 2–3; merge de PRs en orden) |
| P2 | Spec 5.11 (`User` $fillable restrictivo) | Implementado | Escrituras `User` vía arrays explícitos + `forceFill` server-side — obligatorio en el service |
| P3 | Throttle en signup/registration | Implementado | `throttle:5,1` en `signup.store`/`submit-otp`/`otp-resend` (web) y `sign-up`/`submit-otp`/`send-reset-code` (api) — se preserva. **Nota:** `DemoMode` NO cubre signup hoy (verificado: `signup.store`/`apisign-up` fuera de ambas listas de `DemoMode`; la rama landlord exige auth) — F12 congela el gap; no presumir protección que no existe |
| P4 | Suite verde | Verde en rama base (hijo 3) | `php artisan test`; el conteo histórico "371" quedó desactualizado tras hijos 1–3 — fijar el número al arrancar |

## 4. Estructura propuesta

```
app/Services/LandlordService.php
app/Http/Requests/Landlord/StoreLandlordRequest.php     # admin CRUD
app/Http/Requests/Landlord/CreateAccountRequest.php    # compartido signup/registration (ramas por canal)
tests/Unit/LandlordServiceTest.php
tests/Feature/Landlord/AdminLandlordHttpTest.php       # tests de contrato previos
tests/Feature/Auth/SignupAccountHttpTest.php           # tests de contrato previos (web)
tests/Feature/Auth/RegistrationAccountHttpTest.php     # tests de contrato previos (api)
```

### Contrato del service

```php
class LandlordService
{
    public function list(array $filters = []): LengthAwarePaginator;                 // solo Admin
    public function store(array $data, ?UploadedFile $image = null): User;            // admin CRUD (tx, role server-side)
    public function update(User $landlord, array $data, ?UploadedFile $image = null): User;
    public function delete(User $landlord): void;                                     // media post-commit
    public function deleteMany(array $ids): int;                                      // tx + captura de media
    public function createAccount(array $data, Channel $channel): User;                // web/api: updateOrCreate + columnas divergentes por canal; SIN mail, SIN trío
    public function activateFreePlan(User $user, Channel $channel, ?Plan $plan = null): ?PlanSubscribe;
    //   ↑ única fuente del trío free-plan: PlanSubscribe::create (campos por canal) + forceFill(plan_subscribe_id/will_expire/subscriptionDate)
}
```

- `store` (admin) compone: `User::create` (array explícito) + `role = 'landlord'` server-side + `UserDetail::create` + `activateFreePlan(Channel::Admin, $freePlan)`.
- `createAccount` (web/api) crea **solo el User** con las columnas divergentes por canal (`match($channel)`, como `ApplicationService`); el trío lo activa `activateFreePlan` desde donde hoy ocurre (web: `submitOtp`; api: `maanRegistration`).
- `setStatus` no entra al contrato: no existe ruta `landlords.status` viva (mismo criterio que hijo 2 H6 para métodos sin consumidor).
- `createAccount` no envía OTP/`WelcomeMail`: los controladores retienen la orquestación OTP y el envío post-commit (§2 OUT, Fase 4 / 4.8).
- `landlord_id` no se asigna en ningún alta actual de landlord (el borrador lo citaba por error — era copia de `TenantService`).

### Divergencias actuales entre los 3 flujos — preservadas tal cual

| Aspecto | `Admin/LandlordController::store` | `SignupController` | `Api/RegistrationController` |
|---|---|---|---|
| Cómo llega al alta | CRUD admin directo | signup público web + OTP | registration api + OTP |
| Creación de `User` | `User::create` (array explícito) | `updateOrCreate` + `is_setupped=1` | `updateOrCreate` (sin `is_setupped`) |
| `UserDetail` | sí (`gender`/`mykad`/`address_info`) | **no** | **no** |
| Momento del trío free | mismo endpoint (store) | `submitOtp` — condicional al plan en sesión | mismo endpoint (`maanRegistration`, **pre-OTP** si OTP=on) |
| Campos de `PlanSubscribe` | 8: `plan_id, user_id, duration, plan_name, number_of_property, number_of_tenant, number_of_labor, payment_status='paid'` | 5: `plan_id, plan_name, user_id, duration, payment_status='paid'` | 3: `plan_id, user_id, duration` (sin `payment_status`) |
| `role` | asignación server-side explícita | idem (Seg-28: crafted keys bloqueadas) | idem (spec fase1: role arbitrario bloqueado) |
| OTP/`WelcomeMail` | no | sí (post-commit) | sí (post-commit) |

## 5. Decisiones técnicas

- **Reparto del alta (enmienda de revisión H2):** admin usa `store()` (crea `User` + `UserDetail` + trío); signup/registration delegan a `createAccount()` por canal. El trío free-plan tiene **una** implementación (`activateFreePlan`) con los campos divergentes por canal (8/5/3) preservados — no se unifican a la fuerza payloads que hoy difieren.
- Escrituras `User` con arrays explícitos de claves; `role`/trío free-plan **server-side** (`forceFill`/asignación), jamás del request (spec 5.11 + Seg-28).
- Tx 4.8: uploads antes; `WelcomeMail`/`sendNotification`/borrado de media post-commit best-effort — los envíos viven en los controladores, no en el service.
- `deleteMany` actual hace tx + captura de media en un override del trait — el service lo absorbe con el patrón captura-antes/borra-después.
- `Admin/LandlordController` deja de usar `HasTableActions`; `filter`/`deleteAll`(→`deleteMany`)/`exportExcel`/`exportCsv` (rutas `landlords.filter|delete-all|excel|csv`) se reimplementan delegando al service — cero rutas muertas.
- `file_exists()` sobre rutas de storage (L298/L355 actuales) se sustituye por `Storage::exists()` — interno, no observable.
- Tests de contrato **sin** `@group characterization` (paraguas §5.7); la marca queda reservada a F12 (bug conocido congelado).

## 6. Catálogo de casos (prefijo `F` HTTP, `U` unit)

### 6.1 Admin (`F1–F6`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Admin store crea landlord + plan free | admin, plan free existe | POST /admin/landlords | 200; User+UserDetail+PlanSubscribe trío (8 campos); role server-side (regresión G6–G7) |
| F2 | Admin store bloquea crafted keys | payload con `plan_subscribe_id`/`role`/`will_expire` | POST /admin/landlords | asignaciones server-side prevalecen (Seg-28, red existente G7) |
| F3 | Admin update | admin, landlord B | PUT /admin/landlords/{id} (+imagen) | 200; imagen vieja borrada post-commit |
| F4 | Admin destroy | admin, landlord con media | DELETE /admin/landlords/{id} | 200; media eliminada post-commit (comportamiento actual) |
| F5 | Admin deleteAll | admin, 2 landlords | POST /admin/landlords/delete-all | 200; tx + media capturada antes/borrada después |
| F6 | Filter/excel/csv | admin, 2 landlords | POST landlords/filter, GET landlords-excel/csv | rutas vivas tras migración |

### 6.2 Signup web (`F7–F10`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F7 | Signup OTP=on crea User sin trío | visitor, OTP habilitado, plan en request | POST /signup | User creado (updateOrCreate, `is_setupped=1`, role landlord server-side, token=OTP); sin PlanSubscribe; `WelcomeMail`/OTP post-commit; `{openModal, otp_expiration}` |
| F8 | Signup OTP=off redirige a pago | visitor, OTP deshabilitado | POST /signup | User creado + login; redirect signed a `payments-gateways.index`; sin trío |
| F9 | submitOtp con plan free | user pre-OTP, OTP válido, plan free en sesión | POST /otp-submit | trío creado (5 campos actuales); redirect `landlord.dashboard` |
| F10 | submitOtp con plan paid | user pre-OTP, OTP válido, plan paid en sesión | POST /otp-submit | sin trío; redirect signed a `payments-gateways.index` |

### 6.3 Registration api (`F11`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F11 | Registration api crea User + trío | visitor, plan free existe | POST /api/sign-up | User + trío (3 campos actuales, mismo endpoint, pre-OTP si OTP=on); crafted keys bloqueadas; JSON shape actual (`{user, token}` en rama OTP=off) |

### 6.4 Demo (`F12`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F12 | DemoMode no cubre signup | APP_ENV=demo | POST /signup | **no bloqueado** (comportamiento actual: alta real muta la DB demo) — **caracterización** de bug conocido |

> **Caracterización de gap DemoMode** — se espera que siga pasando; se romperá solo cuando se decida bloquear signup en demo (seguimiento registrado en `.analisis/seguridad.md`, fila 14).

### 6.5 Unit (`U1–U4`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | createAccount por canal | datos válidos | `createAccount(data, Landlord)` / `createAccount(data, Api)` | columnas divergentes correctas por canal (`is_setupped` web=1/api ausente; token/verified según OTP); role landlord server-side |
| U2 | createAccount ignora crafted | payload con `role=admin` | idem | role del payload ignorado (server-side) |
| U3 | deleteMany captura media | 2 landlords con imagen | `deleteMany(ids)` | filas borradas en tx; media borrada post-commit |
| U4 | activateFreePlan campos por canal | plan free | `activateFreePlan(user, Admin/Landlord/Api, plan)` | 8/5/3 campos según canal; forceFill del trío; `null` sin plan |

### Tests de caracterización

| ID | Bug caracterizado | Fase que lo romperá |
|----|-------------------|---------------------|
| F12 | Gap DemoMode: signup público no bloqueado en `APP_ENV=demo` | Decisión futura de bloqueo (fuera de 5.13) |

> El resto del catálogo (F1–F11, U1–U4) son **tests de contrato**: congelan el comportamiento actual y deben seguir en verde tras la migración (paraguas §5.7) — no llevan `@group characterization`.

## 7. Criterios de aceptación (Definition of Done)

- [ ] `LandlordService` en verde (U1–U4); una sola implementación del trío free-plan: `grep 'PlanSubscribe::create' app/Http/Controllers/ = 0` y forceFill del trío idem (vive solo en `activateFreePlan`).
- [ ] Tests de contrato F1–F12 en verde antes de migrar y tras migrar; G6–G9, `WebSignupMassAssignmentTest` (Seg-28), `DemoModeTest` y smoke auth siguen en verde.
- [ ] 3 controladores < 200 líneas, sin `HasTableActions`, sin `file_exists()` sobre storage.
- [ ] Rutas `landlords.filter|delete-all|excel|csv` vivas.
- [ ] Suite + PHPStan + Pint en verde.

## 8. Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Signup/registration son flujos de **auth públicos** — regresión de seguridad | Red existente (Seg-28, throttle, DemoMode, smoke auth) + tests de contrato previos; no tocar middleware |
| Divergencias sutiles entre los 3 flujos (campos del trío, momento, `UserDetail`) | Contrato F1–F12 por separado ANTES; `activateFreePlan` con ramas por canal; no unificar payloads |
| Quirk api: trío asignado **antes** de verificar OTP (rama OTP=on) | F11 congela el momento actual; documentado en tabla §4 |
| Trío free-plan con divergencia | U4 + G6–G9 existentes |

## 9. Estimación

- 6–8 días (contrato 2–2.5, service+forms 2, migración 3 controladores 1.5–2, validación 0.5–1). Catálogo ampliado a 16 casos (split del flujo web) dentro del mismo rango: F7–F10 parten el flujo original F7 en ramas ya existentes.

## 10. Referencias

- `spec-unificar-capas-services-fase5.md` (paraguas §5.7) · `spec-unificar-capas-services-fase4.md` §8 · `spec-user-fillable-restrictivo-fase5.md` (escrituras User)
- `.analisis/plan/plan-accion.md` → Fase 5 / 5.13 · DT-A1, DT-C4
- `tests/Feature/Admin/LandlordStoreSubscriptionTest.php` (G6–G7) · `tests/Feature/Admin/SubscriptionPaidTest.php` (G8–G9)
- `tests/Feature/Auth/WebSignupMassAssignmentTest.php` (Seg-28) · `tests/Feature/Demo/DemoModeTest.php` (F1–F12, admin/landlord)

## 11. Revisión (2026-11-22)

Auditoría `revisar-spec` previa a la implementación. Hallazgos y resoluciones:

| H# | Hallazgo | Severidad | Resolución |
|----|----------|-----------|------------|
| H1 | F7 caracterizaba mal el flujo web: "POST /signup → trío creado" es falso — el trío web se crea en `submitOtp` (L171–193) condicional al plan en sesión, y la rama OTP=off redirige a pago sin trío. Un test según lo escrito no pasaría contra el comportamiento actual | alto | Split en F7–F10 (OTP=on sin trío / OTP=off redirect pago / submitOtp free / submitOtp paid); catálogo renumerado F1–F12 |
| H2 | Contrato `createAccount` no implementable sin cambiar comportamiento observable: `UserDetail::create` solo ocurre en admin; `forceFill(landlord_id/plan)` cita `landlord_id` que ningún flujo de alta de landlord asigna (copia de `TenantService`); campos de `PlanSubscribe` divergen en 3 variantes (admin 8 / web 5 / api 3); trío web en otro endpoint | alto | Contrato reformulado: `createAccount` solo web/api (columnas divergentes por canal, sin mail ni trío) + `activateFreePlan` única implementación del trío con ramas por canal; admin usa `store()`; `landlord_id` eliminado; `setStatus` fuera (sin ruta viva) |
| H3 | F9 falso + P3 impreciso: DemoMode no bloquea signup (`signup.store`/`apisign-up` fuera de ambas listas de `DemoMode`; rama landlord exige `auth()->check()` y signup es público) y no existe red que lo cubra (DemoModeTest F1–F12 sin signup) | alto | F9→F12 congela el gap actual (no bloqueado) como caracterización de bug conocido; P3 reescrito; gap registrado en `.analisis/seguridad.md` fila 14 |
| H4 | Tabla §4 de divergencias incompleta: "Plan free: idem" falso en campos y momento; faltan `UserDetail` (solo admin), método de creación (`create` vs `updateOrCreate`), `is_setupped`, momento del trío (web post-OTP vs api pre-OTP) | medio | Tabla ampliada a 7 filas; quirk pre-OTP api congelado por F11 |
| H5 | Contrato del borrador embebía "OTP/WelcomeMail/sendNotification post-commit" dentro de `createAccount`, contradiciendo §2 OUT (controladores orquestan OTP) | medio | `createAccount` sin mail; envíos quedan en controladores (4.8) |
| H6 | Terminología: paraguas §5.7 fija "tests de contrato" sin `@group characterization` para el freeze; el borrador usaba "caracterización" para todo el catálogo | bajo | Renombrado; marca reservada a F12 con sección consolidada |
| H7 | P4 "371 casos" desactualizado (hijos 1–3 añadieron red; mismo error ya corregido en hijo 2 H7) | bajo | P4 reescrito: suite verde en rama base, conteo se fija al arrancar |
| H8 | DoD "grep del trío free-plan fuera del service = 0" poco preciso (el trío son dos piezas: `PlanSubscribe::create` + forceFill) | bajo | DoD re-alocado a grep de ambas piezas en `app/Http/Controllers/` |

**Estado:** `Borrador` → `Aprobado` (decisor autoriza enmendar + aprobar en la misma sesión de revisión, como hijo 2). Implementación gateada por P1 (merge del hijo 3); rama apilada, como hijos 2–3.
