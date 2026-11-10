---
id: spec-tests-smoke-fase0
titulo: "Tests Smoke — Fase 0 / 0.1"
estado: Borrador
fecha: 2026-11-10
origen: .analisis/plan/plan-accion.md (Fase 0 / 0.1) — DT-C1
cobertura: Catálogo completo (59 tests) sobre flujos críticos (auth web/API, pagos, guards, rutas Artisan)
---

# Spec — Tests Smoke (Fase 0 / 0.1)

> Caracterizar el comportamiento actual de los flujos críticos para que los fixes de Fase 1–3 rompan tests de forma detectable, no silenciosa.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 0 / 0.1, origen DT-C1).

---

## 1. Objetivo

Construir una red de seguridad mínima de **tests smoke** que registren el comportamiento actual (incluido el inseguro) de los flujos críticos, de modo que las remediaciones de Fase 1–3 del plan de acción produzcan fallos de test **intencionales y detectables**.

---

## 2. Alcance

### IN
- Auth web: login, signup, OTP, password reset.
- Auth API: sign-in, sign-up, OTP, reset code, refresh token, sign-out.
- Pagos: vistas de gateway, pago manual/automático, success/failed callback, order status, status endpoints públicos.
- Rutas públicas del CMS frontal.
- Guards admin y landlord (incluye middleware `expired`).
- Rutas Artisan públicas (`/update`, `/cache-clear`).

### OUT
- Tests unitarios de helpers/modelos.
- Cobertura de los 126 controladores.
- Tests de los 11 gateways de pago reales (se mockean).
- Performance, seguridad negativa exhaustiva (eso es Fase 1–3).

### Cobertura objetivo
- ≥ 60 % de los endpoints críticos listados en el catálogo.
- Verificación: comparar casos del catálogo contra `php artisan route:list` (rutas de auth, pagos, admin, landlord, maintenance) — conteo de rutas críticas cubiertas / total ≥ 0.60.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | DB de testing `dbrentpro_test` | No existe | `CREATE DATABASE dbrentpro_test` en MySQL |
| P2 | `phpunit.xml` con DB y APP_KEY de testing | `DB_CONNECTION` comentado; sin `APP_KEY` | Setear `DB_DATABASE=dbrentpro_test`, `DB_CONNECTION=mysql`, `APP_KEY=base64:<fija>` |
| P3 | `UserFactory` completa | Solo name/email/password | Extender con `role`, `is_setupped=1`, `balance`, `phone`(json), `unique_id`, `will_expire`, `email_verified_at`; estados `landlord()`, `admin()`, `tenant()`, `superadmin()` |
| P4 | Roles/permisos en test DB | Faltan (los da `PermissionSeeder`) | Correr `PermissionSeeder` en `setUp` de tests de auth |
| P5 | Factories mínimas para `Plan`, `Gateway`, `Property`, `Option` | No existen | Crear factories mínimas para alimentar tests de pago/auth |
| P6 | Trait compartido `CreatesUsers` | No existe | `tests/Concerns/CreatesUsers.php` con helpers `landlord()`, `admin()`, `tenant()`, `superadmin()` |

**Sin P1–P3 los tests no corren en aislamiento** (pisarían la DB real `dbrentpro`).

---

## 4. Estructura propuesta

```
tests/
├── Concerns/
│   └── CreatesUsers.php
├── Feature/
│   ├── Auth/
│   │   ├── WebLoginSmokeTest.php
│   │   ├── WebSignupSmokeTest.php
│   │   ├── WebPasswordResetSmokeTest.php
│   │   └── ApiAuthSmokeTest.php
│   ├── Payment/
│   │   └── PaymentFlowSmokeTest.php
│   ├── Public/
│   │   └── PublicRoutesSmokeTest.php
│   ├── Admin/
│   │   └── AdminGuardSmokeTest.php
│   ├── Landlord/
│   │   └── LandlordGuardSmokeTest.php
│   └── Maintenance/
│       └── ArtisanRoutesSmokeTest.php
└── README.md
```

`tests/README.md` explica cómo correr la suite y qué significa `@group characterization`.

---

## 5. Decisiones técnicas

- **Framework:** PHPUnit 10 (ya en `composer.json`).
- **DB:** MySQL dedicada `dbrentpro_test` (mismo motor que producción — sin sorpresas de sintaxis JSON con `phone`, `address_info`, lang files).
- **Refresh DB:** trait `RefreshDatabase` (migrate:fresh por run). Si la suite excede ~30s, cambiar a `DatabaseTransactions` en tests que no toquen schema — decisión diferible.
- **Auth web:** `$this->actingAs($user)` + sesión.
- **Auth API:** `Sanctum::actingAs($user, ['*'])`.
- **Mail:** mock `Mail` facade (`Mail::fake()`).
- **Artisan:** mock obligatorio en M1–M2 (`Artisan::shouldReceive('call')`) — **jamás** ejecutar migraciones/seed reales en tests.
- **Gateways dinámicos:** mockear la clase resuelta desde `$gateway->namespace` a un stub que retorne un `Redirect`.
- **Seeders en tests:** solo `PermissionSeeder` (y opciones vía factory). **No** correr `DatabaseSeeder` completo (lento + dependiente de IDs fijos, ver DT-M9).
- **`unique_id`:** setear explícito en factory (el `User::boot()` usa `User::max('id')+1`, frágil en DB recién migrada).

---

## 6. Catálogo de casos

### 6.1 Login web (`L`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| L1 | Login landlord válido | landlord con `is_setupped=1` | `POST /login/store` email+pass | 200 JSON `{redirect: landlord.dashboard}` |
| L2 | Login credenciales inválidas | — | `POST /login/store` bogus | 401 JSON `{message: Invalid...}` |
| L3 | Login como tenant bloqueado | tenant | `POST /login/store` | 403 `{message: cannot log in as tenant}` |
| L4 | Login valida campos | — | `POST /login/store` sin email | 422 validation |
| L5 | Logout | usuario autenticado | `GET /logout` | redirect a `/login` |
| L6 | Vista login renderiza | — | `GET /login` | 200, view `auth.login` |

### 6.2 Signup web (`S`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| S1 | Registro OTP off | `Option email-varification.otp_status=off` | `POST /signup` datos válidos | 200, crea User role=landlord, `Auth::check()` true |
| S2 | Registro OTP on | `Option email-varification.otp_status=on` | `POST /signup` | 200 JSON `{openModal:true}`, no loguea, setea `remember_token` |
| S3 | OTP submit válido | user con token y `email_verified_at` futuro | `POST /otp-submit` | 200, loguea |
| S4 | OTP expirado | user con `email_verified_at` pasado | `POST /otp-submit` | 410 |
| S5 | OTP inválido | user con token X | `POST /otp-submit` otp=Y | 400 |
| S6 | Registro email duplicado | user existente `is_setupped=1` | `POST /signup` mismo email | 406 |
| S7 | Validación de campos | — | `POST /signup` sin name | 422 |
| S8 | Sin MAIL_USERNAME | env sin `MAIL_USERNAME` | `POST /signup` | 406 `{message: Mail service not configured}` |

### 6.3 Password reset web (`R`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| R1 | Vista forgot-password | — | `GET /forgot-password` | 200 |
| R2 | Envío reset link email válido | `Mail::fake()` | `POST /forgot-password` email existente | redirect con status |
| R3 | Envío reset link email inexistente | — | `POST /forgot-password` email bogus | error de validación |
| R4 | Vista reset con token | — | `GET /reset-password/{token}` | 200 |
| R5 | Reset con token válido | mock `Password` broker | `POST /reset-password` token+email+pass+confirm | redirect a login |

### 6.4 Auth API (`A`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| A1 | Sign-in landlord | landlord `is_setupped=1` | `POST /api/v1/sign-in` | 200 JSON con `token` |
| A2 | Sign-in inválido | — | `POST /api/v1/sign-in` bogus | 401 |
| A3 | Sign-in rol no permitido (admin) | admin | `POST /api/v1/sign-in` | 406 |
| A4 | Sign-up crea landlord + token | `otp_status=off` | `POST /api/v1/sign-up` datos válidos | 201 con `token` |
| A5 | Sign-up rol arbitrario | — | `POST /api/v1/sign-up` con `role=superadmin` | 201 — **caracterización** |
| A6 | Submit OTP válido | user con token válido no expirado | `POST /api/v1/submit-otp` | 200 con token |
| A7 | Send reset code | `Mail::fake()` | `POST /api/v1/send-reset-code` email existente | 200 |
| A8 | Reset password sin OTP | user existente | `POST /api/v1/user-password-reset` email+pass sin otp | 200 — **caracterización** |
| A9 | Refresh token | auth | `POST /api/v1/refresh-token` | 200 con nuevo token |
| A10 | Sign-out | auth | `GET /api/v1/sign-out` | 200 |

> **A5 — Caracterización de Seg-4** — actualmente el registro API permite asignar `role` arbitrario. Se espera que falle tras Fase 1 (debe devolver 403/422 o ignorar el rol).
> **A8 — Caracterización de Seg-3** — `userResetPassword` no valida OTP. Se espera que falle tras Fase 1 (debe requerir OTP válido).

### 6.5 Pagos (`P`)

Mocks: `Artisan`, `Mail`, clases `App\Library\*` (gateway).

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| P1 | Vista gateways de suscripción | Plan + Gateway activos | `GET /payments-gateways/{plan}/{landlord}` | 200, setea `session('user_id','payment_type=subscription')` |
| P2 | Vista depósito/renta | SecurityDeposit + Gateway | `GET /online-payments-gateways/{id}/{tenant}?payment_type=security_deposit` | 200 |
| P3 | Vista pago tipo inválido | — | `GET /online-payments-gateways/{id}/{tenant}?payment_type=xxx` | redirect con error |
| P4 | Pago manual (offline) | Gateway `is_manual=1`, attachment válido | `POST /payments/{plan}/{gateway}` | redirect a `order.status success`, crea `PlanSubscribe` unpaid |
| P5 | Pago gateway automático | mock `make_payment` retorna redirect | `POST /payments/{plan}/{gateway}` | redirect al gateway |
| P6 | Success callback suscripción | session con plan,user,amount,type=subscription | `GET /payment/success` | redirect success, crea `PlanSubscribe` paid + `Transaction` + actualiza `user.will_expire` — **caracterización** |
| P7 | Success sin sesión | sesión vacía | `GET /payment/success` | redirect failed |
| P8 | Failed callback | — | `GET /payment/failed` | redirect/200 |
| P9 | Order status | — | `GET /order-status` | 200 |
| P10 | Status endpoints públicos | mock gateway classes | `GET /payment/mollie`, `/payment/flutterwave`, `/payment/thawani`, `/payment/instamojo`, `/payment/toyyibpay`, `/payment/billplz`, `/payment/fedapay/status`, `/tap-payment/status` | 200 (no 500) |

> **P6 — Caracterización de Seg-6** — `/payment/success` es público y procesa pagos desde sesión manipulable. Se espera que falle tras Fase 2 (debe requerir auth + verificación de firma).

### 6.6 Rutas públicas (`Pub`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| Pub1 | Home | — | `GET /` | 200 |
| Pub2 | Blogs index | blog existente | `GET /blogs` | 200 |
| Pub3 | Blog show | blog existente | `GET /blogs/{blog}` | 200 |
| Pub4 | Plans | — | `GET /plans` | 200 |
| Pub5 | About us | — | `GET /about-us` | 200 |
| Pub6 | Terms | — | `GET /terms-conditions` | 200 |
| Pub7 | Privacy policy | — | `GET /privacy-policy` | 200 |
| Pub8 | Contact | — | `GET /contact-us` | 200 |
| Pub9 | Contact store | payload válido | `POST /contact/store` | 200/redirect |
| Pub10 | Filter blogs by tag | — | `GET /filter-blogs-by-tag` | 200 |

### 6.7 Guard admin (`G`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| G1 | Anónimo a /admin | — | `GET /admin` | redirect a `/login` |
| G2 | Landlord a /admin | auth landlord | `GET /admin` | **caracterización** — actualmente pasa por middleware roto |
| G3 | Admin autenticado | auth admin/superadmin | `GET /admin` | 200 |
| G4 | Tenant a /admin | auth tenant | `GET /admin` | **caracterización** — actualmente pasa por middleware roto |

> **G2, G4 — Caracterización de Seg-2** — `AdminMiddleware` tiene la lógica `auth()->check() && role!=='landlord' || role!=='tenant'` con bug de precedencia. Se espera que fallen tras Fase 1 (deben redirect a login).

### 6.8 Guard landlord (`H`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| H1 | Anónimo a /landlord | — | `GET /landlord` | redirect login |
| H2 | Landlord autenticado | auth landlord con suscripción no expirada | `GET /landlord` | 200 |
| H3 | Tenant a /landlord | auth tenant | `GET /landlord` | redirect login |
| H4 | Suscripción expirada | landlord con `will_expire` pasado | `GET /landlord` | redirect (middleware `expired`) |

### 6.9 Rutas Artisan públicas (`M`) ⚠ crítico

**Mock `Artisan::shouldReceive('call')`** — jamás ejecutar migraciones reales.

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| M1 | `/update` público y ejecuta Artisan | mock `Artisan::call` | `GET /update` sin auth | 302 redirect, `Artisan::call('migrate')` invocado, crea `Option version=2.1` — **caracterización** |
| M2 | `/cache-clear` público | mock `Artisan::call` | `GET /cache-clear` sin auth | redirect, `Artisan::call('cache:clear')` invocado — **caracterización** |

> **M1, M2 — Caracterización de Seg-1** — rutas públicas que ejecutan Artisan. Se espera que fallen tras Fase 1 (deben devolver 403/redirect a login al requerir auth).

---

## 7. Tests de caracterización (consolidado)

| ID | Bug caracterizado | Fase que lo romperá | Catálogo |
|----|-------------------|---------------------|----------|
| A5 | Seg-4 — Registro API con role arbitrario | Fase 1 | §6.4 |
| A8 | Seg-3 — Reset password sin OTP | Fase 1 | §6.4 |
| G2 | Seg-2 — AdminMiddleware roto (landlord) | Fase 1 | §6.7 |
| G4 | Seg-2 — AdminMiddleware roto (tenant) | Fase 1 | §6.7 |
| M1 | Seg-1 — `/update` público | Fase 1 | §6.9 |
| M2 | Seg-1 — `/cache-clear` público | Fase 1 | §6.9 |
| P6 | Seg-6 — `/payment/success` público | Fase 2 | §6.5 |

Estos 7 tests se marcan con `@group characterization` en la implementación PHPUnit y se documentan en `tests/README.md`. **Se espera que fallen** al aplicar las remediaciones de Fase 1–2; cada fallo debe interpretarse como "el fix funcionó" y motivar la actualización intencional del test al nuevo comportamiento seguro.

---

## 8. Criterios de aceptación (Definition of Done)

- [ ] `dbrentpro_test` creada y `phpunit.xml` configurado (`DB_DATABASE=dbrentpro_test`, `APP_KEY` seteada).
- [ ] `php artisan test` corre sobre `dbrentpro_test` sin tocar la DB del `.env`.
- [ ] `UserFactory` extendida con estados `landlord()`, `admin()`, `tenant()`, `superadmin()`.
- [ ] Trait `tests/Concerns/CreatesUsers.php` creado.
- [ ] Los 59 casos del catálogo (L1–L6, S1–S8, R1–R5, A1–A10, P1–P10, Pub1–10, G1–G4, H1–H4, M1–M2) pasan en verde sobre el código actual.
- [ ] Cobertura de endpoints críticos ≥ 60 % verificada comparando casos del catálogo contra `php artisan route:list` (rutas de auth, pagos, admin, landlord, maintenance).
- [ ] Los 7 tests de caracterización marcados con `@group characterization` y comentario que explique que se espera que fallen tras Fase 1–2.
- [ ] `tests/README.md` explica cómo correr la suite y qué significa `@group characterization`.
- [ ] Tiempo de ejecución total < 30 s (si se excede, cambiar a `DatabaseTransactions` en tests no-schema).

---

## 9. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| `unique_id` generado en `User::boot()` con `User::max('id')+1` frágil en DB recién migrada | Tests de creación de user fallan | Setear `unique_id` explícito en factory |
| `PermissionSeeder` puede tener dependencias (tablas spatie) | Tests de roles fallan | Asegurar migraciones de spatie publicadas + seeder en `setUp` |
| Rutas de gateway `status` hacen HTTP externo | Tests lentos/inestables | Mockear todas las clases `App\Library\*` |
| `RefreshDatabase` + 52 migraciones lento | Suite > 30s | Cambiar a `DatabaseTransactions` en tests que no crean schema |
| Tests de caracterización pueden confundir a futuros agentes | "Tests en rojo" se interpretan como bugs nuevos | Comentario + `@group` + `tests/README.md` explícito |
| Migraciones con sintaxis MySQL-specific (JSON columns) | Incompatible si se cambia a SQLite | Mantener MySQL `dbrentpro_test` (decisión ya fijada) |

---

## 10. Estimación

- Pre-requisitos (P1–P6): 0.5–1 día
- Catálogo (59 tests) desglosado por dominio:
  - Auth web (L+S+R = 19 tests): 1 día
  - Auth API (A = 10 tests): 0.5 día
  - Pagos (P = 10 tests): 1 día
  - Rutas públicas (Pub = 10 tests): 0.25 día
  - Guards admin/landlord (G+H = 8 tests): 0.5 día
  - Rutas Artisan (M = 2 tests): 0.25 día
- **Total: 4–4.5 días**

---

## 11. Referencias

- `.analisis/plan/plan-accion.md` → Fase 0 / 0.1 (origen DT-C1)
- `.analisis/seguridad.md` → Seg-1, Seg-2, Seg-3, Seg-4, Seg-6 (tests de caracterización)
- `.analisis/deuda-tecnica.md` → DT-C1, DT-M9
- `.agents/spec/spec-conventions.md`
- `AGENTS.md` → secciones Routes, Middleware, Payments architecture

---

## 12. Notas de implementación (2026-11-11)

Implementado en rama `spec/tests-smoke-fase0-impl`. El spec permanece en
`Borrador` con estas notas (decisión del autor: implementar tal cual y
documentar desviaciones aquí). Ver `tests/README.md` para ejecución y cobertura.

### Desviaciones detectadas durante la implementación

| # | Caso | Spec dice | Código real (implementado) |
|---|------|-----------|---------------------------|
| D1 | A4 | 201 con token | `RegistrationController::maanRegistration` responde **200** (sin código explícito). Assert 200. |
| D2 | R2/R5 | `Mail::fake()` + mock broker | El broker de password usa **Notification** (canal mail). Implementado con `Notification::fake()` y flujo real del broker (token extraído de la notificación). |
| D3 | G2/G4 | "pasa por middleware roto" | Pasa `AdminMiddleware` (bug Seg-2) pero aborta **403** en `permission:dashboard-read` (constructor de `AcnooAdminDashboardController`). Assert 403: sigue siendo caracterización de Seg-2 (el fix debe producir 302 a login). |
| D4 | P5 | Factories Plan/Gateway/Property/Option | Se añadieron además **Currency** (`convert_money()` requiere `gateway->currency`), **Blog** (Pub3), **SecurityDeposit** (P2), **PlanSubscribe** (H2), **PropertyDetail** (vista `online_payment` usa `property->propertyDetail` no null-safe) y **Category** (FK de Property). |
| D5 | S2/S8 | Mail configurado en test | `SignupController` chequea `env('MAIL_USERNAME')` de forma **incondicional** (afecta S1, S2, S6). Seteado `MAIL_USERNAME=smoke@test.local` en `phpunit.xml` (gana a `.env`); S8 lo unsetea en runtime (`$_ENV`/`$_SERVER`/`putenv`) y lo restaura en `tearDown`. |
| D6 | G3 | admin → 200 | `GET /admin` exige permiso Spatie `dashboard:dashboard-read` → `dashboard-read` (middleware en constructor). `CreatesUsers::createAdmin()` garantiza rol+permiso (`ensureSpatieRole`); `AdminGuardSmokeTest` corre además `PermissionSeeder` en `setUp` (P4). |

### Hallazgos nuevos (no previstos en §9)

- **`vendor/autoload1.php`**: el middleware `CheckToken` (`safiull/laravel-installer`) borra `vendor/laravel` si no existe ese marcador en cualquier request web a ruta no excluida. Precondición documentada en `tests/README.md`.
- **`layouts/web/partials/header.blade.php`** accede `$page_data['headings']['header_btn_link']` sin `??`: toda vista pública requiere la Option `manage-pages` (se seedea en `PublicRoutesSmokeTest::setUp`).
- **`Plan.offerPrice` es NOT NULL default 0** y la app lo usa con semántica "0 = gratis": factory default `offerPrice=8, subscriptionPrice=10`; estado `free()` con ambos en 0.
- **`User.will_expire` no está castado** (DATE string): asserts por comparación de `toDateString()`.

### Evidencia del DoD (§8)

- Suite completa **en verde**: 60 tests, 153 assertions, **~17 s** (`RefreshDatabase`, migrate:fresh una vez por proceso). < 30 s ✓.
- Grupo `characterization`: 7/7 en verde aislado (`php artisan test --group=characterization`) ✓.
- Cobertura de endpoints críticos: **44/52 = 84.6 %** ≥ 60 % ✓ (desglose en `tests/README.md`).
- 59 casos del catálogo implementados (L 6, S 8, R 5, A 10, P 10, Pub 10, G 4, H 4, M 2).
- `tests/Feature/ExampleTest.php` (stub) eliminado: `GET /` falla 500 sin la Option `manage-pages` y ya está cubierto por Pub1. `tests/Unit/ExampleTest.php` se conserva.
