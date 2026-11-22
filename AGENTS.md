# AGENTS.md

Laravel 11.x / PHP 8.3 SaaS for property rental management (roles: `superadmin`, `admin`, `landlord`, `tenant`). Multi-tenant by landlord; payments via 11 gateways.

> Stack actualizado a Laravel 11.x / PHP 8.3 — **Fase 4 completa (4.1–4.10, 2026-11-21)**. Plan de remediación restante (Fase 5, higiene) en `.analisis/plan/plan-accion.md`.

## Commands

```bash
composer install
npm install                       # allowScripts en package.json auto-aprueba el postinstall de esbuild (no removerlo: sin el binario, npm run dev/build fallan)
php artisan key:generate
php artisan storage:link          # public/storage symlink is NOT committed
php artisan migrate --seed        # runs 24 seeders in order (see DatabaseSeeder)
php artisan test                  # suite completa (331 casos) over dbrentpro_test — see tests/README.md
npm run dev                       # vite (resources/css/app.css, resources/js/app.js)
npm run build
php artisan serve                 # local dev server
```

Run a single test: `php artisan test tests/Feature/Auth/WebLoginSmokeTest.php`

## Routes (non-standard — 6 files, loaded in `app/Providers/RouteServiceProvider.php`)

| File | Middleware | Prefix | Name prefix |
|------|-----------|--------|-------------|
| `routes/web.php` | `web` | — | — |
| `routes/auth.php` | `web` | — | — |
| `routes/api.php` | `api` | `/api` | `api` (no dot) |
| `routes/admin.php` | `web,auth,admin` | `/admin` | `admin.` |
| `routes/landlord.php` | `web,landlord,expired` | `/landlord` | `landlord.` |
| `routes/maintenance.php` | `web` + `auth` + `role:superadmin` | — | — |

- `maintenance.php` is `require`d from `web.php` — `GET /update` and `GET /cache-clear` now require `auth` + `role:superadmin` (Fase 1 / 1.1). Previously public (Seg-1), now mitigated.
- **Throttle (Fase 3 / 3.7):** rutas sensibles de auth (login, signup, `otp-submit`, `otp-resend`, `forgot-password`, `reset-password` web; `sign-in`, `sign-up`, `submit-otp`, `send-reset-code`, `resend-otp`, `user-password-reset` API) llevan `throttle:5,1`; el límite general de la API es 60/min (`RouteServiceProvider`, antes 2000).
- API route names: `api` prefix without dot (e.g. `apisign-in`, `apiproperties`).
- Todas las rutas tienen `->name()` propio (0 nombres `api`/`admin.` de prefijo desnudo, 0 duplicados) — guard `tests/Feature/Routes/RouteNamesSmokeTest.php` (Fase 5 / 5.8).

## Middleware aliases (`app/Http/Kernel.php`)

`admin`, `landlord`, `expired`, `role`, `permission`, `role_or_permission` (Spatie). Custom: `AdminMiddleware`, `LandlordMiddleware`, `ExpiredMiddleware`, `DemoMode`, `SetLocale`. `VerifyCsrfToken` has no exemptions (Fase 1 / 1.6 removed `/billplz-callback`).

## Global helpers (autoloaded via composer `files`)

`app/Helpers/Helper.php` is loaded globally (no namespace). Available everywhere: `get_option()` (returns `array`; scalar keys like `version` are read via `App\Support\Options::string()`), `default_currency()`, `currency_format()`, `convert_money()`, `cache_remember()`, `forgetModel()`, `notify_users()`, `sendNotification()`, `plan_data()`, `languages()`, `demoReset()`. Check here before assuming a function is missing.

Desde Fase 4 / 4.6 la lógica de moneda y OTP vive en clases inyectables del contenedor: `App\Support\Currency` (`format`, `currency`, `default`, `convert`, `convertToDefault`), `App\Support\Payment` (`payable`) y `App\Support\Otp` (`visibilityTimeInSeconds`). Los helpers globales `currency_format`, `currency`, `default_currency`, `convert_money`, `convert_to_default_amount` y `payable` son delegadores delgados a esas clases. Los gateways de `app/Library/` ya no usan `request()`; reciben `bool $expectsJson` en `make_payment($data, $expectsJson = false)`.

Desde Fase 5 / 5.7 `App\Support\Options` fija el contrato tipado de las options: `array($key, $default = [])` y `string($key, $default = '')` con default tipado (fila ausente/corrupta o deriva de tipo → default, nunca null). `get_option(): array` es un delegador delgado a `Options::array()`; solo vale para keys de fila única (`reminder-messages` se consume vía modelo). Arch-test vigila que ninguna vista reintroduzca `get_option(...)[...]` sin `??`.

## Service providers

- `WebsiteServiceProvider` registers `app('website')` singleton (first row of `Website` model).
- `AppServiceProvider` calls `Paginator::useBootstrap()`.

## Payments architecture

- 11 gateways in `app/Library/` (Stripe, Mollie, Razorpay, FedaPay, Paystack, Flutterwave, Instamojo, Toyyibpay, Thawani, Tap, Billplz). Each class implements `make_payment(array $data, bool $expectsJson = false)` and `status()`.
- Gateways are DB-driven: `Gateway->namespace` holds the class FQCN, invoked dynamically as `$gateway->namespace::make_payment($data)`. **Editing a gateway class requires updating the file, the DB row, and `config/gateways.php` (namespace whitelist Fase 2 / 2.5 + seeded sandbox `credentials` Fase 4 / 4.10).** A namespace not whitelisted is blocked with `403` at invocation.
- Payment URLs are signed (`signed` middleware) and `/payment/success` requires auth + gateway verification/idempotency (Fase 2 / 2.3–2.4); webhooks/callbacks are hardened (Fase 1 / 1.6). Desde Fase 4 / 4.8 la verificación de pago (Stripe HTTP) y los early returns ocurren **antes** de abrir la transacción DB; la tx solo envuelve escrituras.
- `.env.example` **lists the 11 gateway env vars** with sandbox defaults (`STRIPE_KEY` … `BILLPLZ_COLLECTION_ID`). Names must match the keys read by `config/gateways.php` `credentials` — real values only in the deployed `.env`, never committed.
- **`DemoMode`** blocks mutating routes in demo environments: activation is exclusively `APP_ENV=demo` (`app()->environment('demo')`, Fase 3 / 3.5 — `env('DEMO_MODE')` ya no se consulta). See `$adminDisabledRoutes` / `$landlordDisabledRoutes` in `app/Http/Middleware/DemoMode.php`. La restricción de landlords se basa en `users.is_demo`.

## Controller layout (Fase 4 / 4.2: patrón Services — migración parcial)

- **12 entidades migradas a Services** (`app/Services/`, spec `spec-unificar-capas-services-fase4` + 11 specs hijos): Property, Expense, ExpenseCategory, Income, IncomeCategory, Tenant, SecurityDeposit, RentPayment, RefundRequest, Support, WithdrawMethod, Notification — sus controladores Admin/Landlord/Api son delgados (<250 líneas) y sin duplicación.
- La duplicación 3-capas (Admin/Landlord/Api por entidad) persiste en las entidades **no migradas** (payments, landlords, rent, application, language…) — deuda rastreada como ítem 5.13 del plan.
- Most controllers prefixed `Acnoo` (vendor heritage).
- `acnooFilter()`, `deleteAll()`, `status()`, `exportExcel()`, `exportCsv()` live in **one shared trait**: `app/Traits/HasAcnooActions.php` (spec-trait-acnoo-fase0, DT-C3). Hoy: 33 controladores hacen `use HasAcnooActions`; 18 conservan un `acnooFilter` propio, 14 un `deleteAll` propio y 4 un `exportExcel` propio (la migración 4.2 reestructuró el reparto; inventario histórico en `.agents/spec-impl/spec-trait-acnoo-fase0.md` §12). **Changing shared behavior = edit the trait once.** Do not reintroduce copies of these 5 methods.
- Largest hoy: `PaymentController` (464 líneas), `Admin/AcnooLandlordController` (428), `Landlord/AcnooRentInvitationController` (367), `Api/AcnooTenantApplicationController` (315), `Api/AcnooRentController` (290).

## Translations

105 JSON locale files in `lang/` (e.g. `en.json`, `ar.json`) — keyed by filename, not PHP arrays. Selected via `SetLocale` middleware reading the `languages()` helper.

## Seeders

`DatabaseSeeder` calls 24 seeders **in order**; `PermissionSeeder` must run before role-dependent ones. Demo users (`*@acnoo.com`) have **non-predictable passwords** (Fase 5 / 5.2): `config('demo.user_password')` — `DEMO_USER_PASSWORD` in `.env`, evaluation only — or a random one per user printed at creation. `App\Support\DemoCredentials` is the single source; never reintroduce `bcrypt('<role>')` for demo accounts in seeders/factories.

**Convención (Fase 4 / 4.10, DT-M9):** seeders idempotentes por clave natural (`firstOrCreate`: `email`/`code`/`name`/`key`/`slug`); re-ejecutar `db:seed` sobre una DB ya poblada no duplica. Prohibido reintroducir IDs numéricos literales (`landlord_id => 3`), rutas a archivos no trackeados (`uploads/...`) o `env()` en `database/seeders/` — lo vigila el arch-test `tests/Unit/Seeders/SeedersArchTest.php`. El dataset demo vive en `database/seeders/DemoDataset.php` y lo siembra el trait `Concerns/SeedsDemoData` (consumido por Property/Income/Expense/DemoSeeder; PropertyDetail/Unit viajan bajo guard seed-if-absent porque `puid` se auto-genera en un hook del modelo). El landlord demo se resuelve por `User::demoLandlords()` (flag `is_demo`), nunca por id — `demoReset()` incluida. `FedaPayGatewaySeeder` fue eliminado (huérfano con credencial sandbox embebida); la key quedó en git history — rotarla si algún entorno la usó.

## Gotchas

- **Transacciones sin E/S externa (Fase 4 / 4.8):** `DB::beginTransaction()` envuelve únicamente escrituras DB. Convención: uploads antes de abrir la tx; `Mail` y borrados de archivos viejos **después** del `DB::commit()` (rutas capturadas en arrays dentro de la tx, borrado best-effort); HTTP de gateways/Stripe y early returns de verificación fuera de la tx. No reintroducir `Mail::`/`make_payment`/uploads/`Storage::` entre `beginTransaction` y `commit`.

- **Caché con invalidación centralizada (Fase 4 / 4.9, DT-M8):** toda invalidación pasa por `App\Support\CacheKeys::forgetModel()`; los 5 modelos cacheados (`Option`, `Language`, `Currency`, `PlanSubscribe`, `User`) llevan observers `#[ObservedBy]` (`saved`/`deleted`). Reglas: no reintroducir `Cache::forget`/`cache()->forget` en `app/` (el único `forget` permitido vive en `CacheKeys`); una mass-op de query builder sobre un modelo cacheado **no dispara observers** ⇒ llamar `forgetModel()` explícito en la misma acción, tras el commit (ej. `AcnooLanguageController::isDefault`); las claves son strings históricas intactas — construirlas siempre vía `CacheKeys`. La clave `plan-data-{user_id}` embebe datos de `PlanSubscribe` **y** `will_expire` de `User`: si `plan_data()` empieza a embeber de otro modelo, añadir su rama en `forgetModel()`.

- `env()` is **not** used in `app/` (eliminado en Fase 4 / 4.3): breaks under `php artisan config:cache`. New code uses `config()`; `env()` remains legal only in `config/` files — `database/seeders/` is guarded by arch-test U2 (spec 4.10).
- No usar el helper global `request()` en `app/Http/Controllers/`, `app/Http/Middleware/` ni `app/Exports/`: inyectar `Request $request` (Fase 4 / 4.4). Los controladores ya inyectan `Request $request`; los gateways de `app/Library/` reciben `bool $expectsJson` en `make_payment()` (Fase 4 / 4.6 implementado).
- **`TrustHosts` activo en el stack global** (Seg-21 / 5.4): el Host header se valida contra `APP_URL` + subdominios (`hosts()` en `app/Http/Middleware/TrustHosts.php`); `local` es no-op del framework; despliegues multi-dominio requieren editar `hosts()` (sin válvula de config). `APP_URL` desalineado ⇒ sitio en 400.
- `User->role` is a plain string column AND `User` uses Spatie `HasRoles`. Two parallel role systems.
- `User` `$fillable` includes `balance`, `is_setupped`, `plan_subscribe_id` — mass-assignable (`role` fue removido en Fase 1 / 1.4; asignar vía `forceFill` solo donde el negocio lo exige).
- `safiull/laravel-installer` adds a `/install` route and `CheckToken` middleware in the `web` group.
- `.analisis/` is gitignored and contains security + debt analysis (`seguridad.md`, `deuda-tecnica.md`, `plan/plan-accion.md`). Do not commit it; consult it before risky refactors.

## Tooling

- **PHPStan (Larastan 3.x)**: `composer analyse` — level 5, paths `app/`, target PHP 8.3 (`phpVersion: 80300`), incluye `phpstan-baseline.neon` (245 entradas / 447 errores legacy congelados).
- **Baseline rule**: the baseline is single-use. Regenerating it to make a PR pass is forbidden; only with documented justification + diff review of the file in the PR. New/modified code errors must fail.
- **Pint (preset laravel)**: `composer lint` (test mode, CI-enforced) / `composer lint:fix` (auto-fix). Config in `pint.json`.
- **CI**: `.github/workflows/ci.yml` — 4 jobs on push to `main` + PRs: `tests` (MySQL 8 service over `dbrentpro_test`), `static-analysis` (phpstan), `style` (pint), `frontend-build` (npm ci + build). Note: the `tests` job must recreate the `vendor/autoload1.php` marker after `composer install`.
- **`php artisan ops:verify-deploy`**: verifica postura operacional efectiva (LOG_LEVEL/session/TLS; patrón de evidencia 5.9 para los residuales de 5.9/5.5/5.6; tabla PASS/FAIL/WARN + exit code; enforcement solo en `APP_ENV=production`, resto report-only; la salida se pega como evidencia bajo `.analisis/`, gitignored).
- `.editorconfig` exists. No pre-commit hooks (CI is the gate).

## Agent conventions (`.agents/`)

Detailed conventions, skills, specs and decisions live in `.agents/`:
- `.agents/README.md` — index and reading order.
- `.agents/spec/` — spec writing conventions and template.
- `.agents/spec-impl/` — concrete specs (one file per spec).
- `.agents/skills/` — operational skills: `crear-spec`, `revisar-spec`, `implementar-spec`, `tests-caracterizacion`, `workflow-ramas` (git/commits/PRs), `secure-coding` (anti-recurrence checklist), `testing-conventions`.
- `.agents/decisions/` — Architectural Decision Records (ADRs).
- `.agents/glossary.md` — domain glossary (landlord, tenant, security_deposit, etc.).

Consult `.agents/skills/workflow-ramas.md` before any edit (never commit to `main`), and `.agents/skills/secure-coding.md` before touching auth/payments/user data.
