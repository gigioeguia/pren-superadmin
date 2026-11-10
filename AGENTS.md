# AGENTS.md

Laravel 10.50 / PHP 8.2 SaaS for property rental management (roles: `superadmin`, `admin`, `landlord`, `tenant`). Multi-tenant by landlord; payments via 11 gateways.

> Laravel 10 and PHP 8.2 are past security support. Remediation plan lives in `.analisis/plan/plan-accion.md`.

## Commands

```bash
composer install
npm install
php artisan key:generate
php artisan storage:link          # public/storage symlink is NOT committed
php artisan migrate --seed        # runs 24 seeders in order (see DatabaseSeeder)
php artisan test                  # ⚠ only ExampleTest stubs exist — no real coverage
npm run dev                       # vite (resources/css/app.css, resources/js/app.js)
npm run build
php artisan serve                 # local dev server
```

Run a single test: `php artisan test --filter=ExampleTest`

## Routes (non-standard — 6 files, loaded in `app/Providers/RouteServiceProvider.php`)

| File | Middleware | Prefix | Name prefix |
|------|-----------|--------|-------------|
| `routes/web.php` | `web` | — | — |
| `routes/auth.php` | `web` | — | — |
| `routes/api.php` | `api` | `/api` | `api` (no dot) |
| `routes/admin.php` | `web,auth,admin` | `/admin` | `admin.` |
| `routes/landlord.php` | `web,landlord,expired` | `/landlord` | `landlord.` |
| `routes/maintenance.php` | `web` (PUBLIC) | — | — |

- `maintenance.php` is `require`d from `web.php` — `GET /update` and `GET /cache-clear` run Artisan commands with **no auth**. Known critical issue.
- API route names: `api` prefix without dot (e.g. `apisign-in`, `apiproperties`).

## Middleware aliases (`app/Http/Kernel.php`)

`admin`, `landlord`, `expired`, `role`, `permission`, `role_or_permission` (Spatie). Custom: `AdminMiddleware`, `LandlordMiddleware`, `ExpiredMiddleware`, `DemoMode`, `SetLocale`. `VerifyCsrfToken` exempts `/billplz-callback`.

## Global helpers (autoloaded via composer `files`)

`app/Helpers/Helper.php` is loaded globally (no namespace). Available everywhere: `get_option()`, `default_currency()`, `currency_format()`, `convert_money()`, `cache_remember()`, `notify_users()`, `sendNotification()`, `plan_data()`, `languages()`, `demoReset()`. Check here before assuming a function is missing.

## Service providers

- `WebsiteServiceProvider` registers `app('website')` singleton (first row of `Website` model).
- `AppServiceProvider` calls `Paginator::useBootstrap()`.

## Payments architecture

- 11 gateways in `app/Library/` (Stripe, Mollie, Razorpay, FedaPay, Paystack, Flutterwave, Instamojo, Toyyibpay, Thawani, Tap, Billplz). Each class implements `make_payment(array $data)` and `status()`.
- Gateways are DB-driven: `Gateway->namespace` holds the class FQCN, invoked dynamically as `$gateway->namespace::make_payment($data)`. **Editing a gateway class requires updating both the file and the DB row.**
- `.env.example` does **not** list the 11 gateway env vars (`STRIPE_KEY`, `STRIPE_SECRET`, `MOLLIE_API_KEY`, `FEDAPAY_API_KEY`, `PAYSTACK_*`, `RAZORPAY_*`, `INSTAMOJO_*`, `TOYYIBPAY_*`, `FLUTTERWAVE_*`, `THAWANI_*`, `TAP_*`, `BILLPLZ_*`). Copy from a working `.env`.
- `DEMO_MODE` env enables `DemoMode` middleware, which blocks mutating routes (see `$adminDisabledRoutes` / `$landlordDisabledRoutes` in `app/Http/Middleware/DemoMode.php`).

## Controller layout (high duplication — intentional heritage)

- Same entity has 3 controllers: `Admin/`, `Landlord/`, `Api/Acnoo*`. Logic is duplicated and divergent across the three.
- Most controllers prefixed `Acnoo` (vendor heritage).
- `acnooFilter()`, `deleteAll()`, `status()`, `exportExcel()`, `exportCsv()` are copied across ~55 controllers. There is **no shared trait** — changing one means changing all.
- Largest: `Admin/PropertyController` (688 lines), `Landlord/PropertyController` (628), `Api/AcnooPropertyController` (564).

## Translations

105 JSON locale files in `lang/` (e.g. `en.json`, `ar.json`) — keyed by filename, not PHP arrays. Selected via `SetLocale` middleware reading the `languages()` helper.

## Seeders

`DatabaseSeeder` calls 24 seeders **in order**; `PermissionSeeder` must run before role-dependent ones. `UserSeeder` inserts demo users with known passwords (see `comand.txt` at repo root, which is tracked in git — do not commit real credentials there).

## Gotchas

- `env()` is called directly inside `app/` code (Helpers, Library, Controllers). It breaks under `php artisan config:cache` (returns null). Use `config()` for any new code.
- `acnooFilter()` uses the global `request()` helper instead of injected `Request` — some controllers forget to inject `Request` (e.g. `Api/AcnooWithdrawController::index`).
- `User->role` is a plain string column AND `User` uses Spatie `HasRoles`. Two parallel role systems.
- `User` `$fillable` includes `role`, `balance`, `is_setupped`, `plan_subscribe_id` — mass-assignable.
- `safiull/laravel-installer` adds a `/install` route and `CheckToken` middleware in the `web` group.
- `.analisis/` is gitignored and contains security + debt analysis (`seguridad.md`, `deuda-tecnica.md`, `plan/plan-accion.md`). Do not commit it; consult it before risky refactors.

## Tooling

No CI, no PHPStan/Pint config, no pre-commit hooks. `laravel/pint` is installed but unconfigured. `.editorconfig` exists.

## Agent conventions (`.agents/`)

Detailed conventions, skills, specs and decisions live in `.agents/`:
- `.agents/README.md` — index and reading order.
- `.agents/spec/` — spec writing conventions and template.
- `.agents/spec-impl/` — concrete specs (one file per spec).
- `.agents/skills/` — operational skills: `crear-spec`, `revisar-spec`, `implementar-spec`, `tests-caracterizacion`, `workflow-ramas` (git/commits/PRs), `secure-coding` (anti-recurrence checklist), `testing-conventions`.
- `.agents/decisions/` — Architectural Decision Records (ADRs).
- `.agents/glossary.md` — domain glossary (landlord, tenant, security_deposit, etc.).

Consult `.agents/skills/workflow-ramas.md` before any edit (never commit to `main`), and `.agents/skills/secure-coding.md` before touching auth/payments/user data.
