# PropertyRent — Super Admin

SaaS de gestión de alquileres de propiedades. Roles: `superadmin`, `admin`, `landlord`, `tenant`. Multi-tenant por landlord; pagos con 11 pasarelas.

## Stack

- **Backend:** Laravel 10.50, PHP 8.2, MySQL 8, Sanctum (API), Spatie Permission
- **Frontend:** Blade + Bootstrap, Vite 3, laravel-vite-plugin
- **Pagos:** 11 gateways en `app/Library/` (Stripe, Mollie, Razorpay, FedaPay, Paystack, Flutterwave, Instamojo, Toyyibpay, Thawani, Tap, Billplz)

## Requisitos

- PHP 8.2 (extensiones: `mbstring`, `pdo_mysql`, `gd`, `zip`, entre otras de Laravel)
- MySQL 8
- Node 18+ y npm
- Composer

## Setup

```bash
composer install
npm install
cp .env.example .env        # ⚠ faltan las 11 vars de gateways: copiar de un .env funcional (ver AGENTS.md)
php artisan key:generate
php artisan storage:link    # public/storage no se commitea
php artisan migrate --seed  # 24 seeders en orden (DatabaseSeeder); PermissionSeeder primero
npm run build               # o npm run dev para desarrollo
php artisan serve
```

### Notas críticas del setup

- **`allowScripts` en `package.json`**: `npm install` auto-aprueba el postinstall de esbuild (persistido vía `npm approve-scripts esbuild`). **No removerlo**: sin el binario de esbuild, `npm run dev`/`npm run build` fallan.
- **`vendor/autoload1.php`**: marcador exigido por el middleware `CheckToken` del instalador. Tras un `composer install` fresco, recrearlo: `echo '{"marker":"installer"}' > vendor/autoload1.php`. Sin él, cualquier request web a una ruta no excluida **borra `vendor/laravel`**.
- `resources/css/app.css` está a 0 bytes: el build no emite asset CSS (estado preexistente, no un fallo).

## Tests

La suite corre sobre la DB MySQL dedicada `dbrentpro_test` (configurada en `phpunit.xml`, decisión ADR-0001; nunca sobre la DB del `.env`):

```bash
php artisan test                           # suite completa (~15 s)
php artisan test --group=characterization  # 7 tests de caracterización (se espera que
                                           # fallen al aplicar los fixes de Fase 1–2)
```

Precondiciones, mocks obligatorios y cobertura de endpoints: ver [`tests/README.md`](tests/README.md).

## Seguridad conocida

- `npm audit` reporta 2 advisory **dev-only** (vite GHSA-15r9, high; esbuild GHSA-67mh, moderate) que solo afectan al dev server local. **No** ejecutar `npm audit fix --force` (instalaría vite@8, breaking change). La remediación es la migración planificada del stack de build (ítem 4.5 del plan de acción).
- Laravel 10 y PHP 8.2 sin soporte de seguridad: el plan de remediación por fases (26 vulnerabilidades, 28 ítems de deuda) vive en `.analisis/plan/plan-accion.md` (carpeta gitignored, solo local).

## Documentación interna

- [`AGENTS.md`](AGENTS.md) — convenciones para agentes: rutas, middleware, arquitectura de pagos, gotchas
- [`.agents/`](.agents/) — specs, skills operacionales y decisiones (ADRs)
- [`tests/README.md`](tests/README.md) — suite de tests smoke y caracterización
