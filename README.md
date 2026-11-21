# PropertyRent — Super Admin

SaaS de gestión de alquileres de propiedades. Roles: `superadmin`, `admin`, `landlord`, `tenant`. Multi-tenant por landlord; pagos con 11 pasarelas.

## Stack

- **Backend:** Laravel 11.x, PHP 8.3, MySQL 8, Sanctum (API), Spatie Permission
- **Frontend:** Blade + Bootstrap, Vite 6.4, axios 1.7, laravel-vite-plugin 1.3
- **Pagos:** 11 gateways en `app/Library/` (Stripe, Mollie, Razorpay, FedaPay, Paystack, Flutterwave, Instamojo, Toyyibpay, Thawani, Tap, Billplz)

## Requisitos

- PHP 8.3 (extensiones: `mbstring`, `pdo_mysql`, `gd`, `zip`, entre otras de Laravel)
- MySQL 8
- Node 18+ y npm
- Composer

## Setup

```bash
composer install
npm install
cp .env.example .env        # las 11 vars de gateways ya vienen (defaults sandbox; ver nota abajo)
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

## Credenciales demo

`migrate --seed` crea 4 usuarios demo con passwords predecibles — **solo para evaluación local, nunca en producción** (contraseñas no predecibles pendientes, ítem 5.2 del plan):

| Email | Password | Rol |
|---|---|---|
| `superadmin@acnoo.com` | `superadmin` | superadmin |
| `admin@acnoo.com` | `admin` | admin |
| `landlord@acnoo.com` | `landlord` | landlord (demo) |
| `tenant@acnoo.com` | `tenant` | tenant |

## Workflow de contribución

Nada se commitea directo a `main`: cada tarea trabaja en una rama dedicada (`spec/<id>-impl`, `fix/<tema>`, `docs/<tema>`) y entra por PR (ver `.agents/skills/workflow-ramas.md`).

## Tests

La suite corre sobre la DB MySQL dedicada `dbrentpro_test` (configurada en `phpunit.xml`, decisión ADR-0001; nunca sobre la DB del `.env`):

```bash
php artisan test                           # suite completa (254 tests)
php artisan test --group=characterization  # 1 test de caracterización (documenta deuda
                                           # pendiente; ver tests/README.md)
```

Precondiciones, mocks obligatorios y cobertura de endpoints: ver [`tests/README.md`](tests/README.md).

## Seguridad conocida

- `npm audit` reporta **0 vulnerabilidades** tras la migración del stack de build a Vite 6 (ítem 4.5 del plan, implementado). **No** ejecutar `npm audit fix --force` (instalaría versiones breaking).
- Laravel 11 y PHP 8.3 con **Fase 4 completa (4.1–4.10)**. El plan de remediación por fases (26 vulnerabilencias, 28 ítems de deuda) vive en `.analisis/plan/plan-accion.md` (carpeta gitignored, solo local). Estado actual: Fases 0–4 implementadas; Fase 5 (higiene) ongoing — ver `plan-accion.md` para el detalle.
- Los 4 usuarios demo tienen passwords predecibles (ver arriba): rotarlos/desactivarlos en cualquier despliegue real (ítem 5.2).

## Documentación interna

- [`AGENTS.md`](AGENTS.md) — convenciones para agentes: rutas, middleware, arquitectura de pagos, gotchas
- [`.agents/`](.agents/) — specs, skills operacionales y decisiones (ADRs)
- [`tests/README.md`](tests/README.md) — suite de tests smoke y caracterización
