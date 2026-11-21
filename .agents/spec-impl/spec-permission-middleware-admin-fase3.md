---
id: spec-permission-middleware-admin-fase3
titulo: "Middleware permission en rutas admin (Fase 3 / 3.8)"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 3 / 3.8) — Seg-18
cobertura: cierre de huecos de permission: en los 53 controladores admin; 11 módulos nuevos en PermissionSeeder; Gate::before para superadmin; eliminación de Gate::define('isAdmin') roto; 15 casos de test (1 genérico + 12 por módulo + 2 bypass superadmin)
---

# Spec — Middleware `permission` en rutas admin (Fase 3 / 3.8)

> Resumen: Seg-18 señala que, pese a tener Spatie instalado, las rutas admin casi no usan middleware `permission`/`role` y cualquier admin autenticado opera todo. En la práctica los controladores aplican `permission:` en constructores de forma inconsistente: 12 controladores no tienen ninguno y todos tienen métodos (rutas reales) sin cubrir (`acnooFilter`, `exportExcel`, `exportCsv`, `getDashboardData`, etc.). Este spec cierra esos huecos, añade los permisos faltantes al seeder, introduce bypass de superadmin vía `Gate::before` y elimina un `Gate::define` roto, sin tocar el guard coarse `admin` (Fase 1.2).

**Origen:** `.analisis/plan/plan-accion.md` (Fase 3 / 3.8) — `Seg-18`.

---

## 1. Objetivo

Cerrar la superficie de autorización fina del panel admin: todo método alcanzable por ruta en los 53 controladores admin debe estar protegido por un middleware `permission:` de Spatie con la acción semántica correspondiente, y los permisos referenciados deben existir en `PermissionSeeder` para los roles `Super Admin` y `Admin`. Adicionalmente, `superadmin` obtiene bypass total de autorización vía `Gate::before` (estándar Spatie) y se elimina el `Gate::define('isAdmin')` roto.

---

## 2. Alcance

### IN

- Añadir/ajustar middleware `permission:` en los **53 controladores admin** (constructores), cubriendo todos los métodos públicos alcanzados por rutas en `routes/admin.php` (declarados + métodos del trait `HasAcnooActions` que tengan ruta).
- Añadir **11 módulos nuevos** al `PermissionSeeder` (`blogs`, `clients`, `comments`, `faqs`, `features`, `highlights`, `testimonials`, `website-settings`, `manage-settings`, `translations`, y reutilizar `permissions` ya existente) en los roles `Super Admin` y `Admin`, con el subconjunto real de acciones.
- `Gate::before` en `AuthServiceProvider` para bypass de `superadmin`.
- Eliminación de `Gate::define('isAdmin', ...)` roto.
- Ajuste de métodos no cubiertos en controladores ya protegidos (dashboard, property, currency, user, support, notification, etc.).
- 15 casos de test (1 genérico + 12 por módulo nuevo + 2 de bypass superadmin).
- Actualización de `tests/README.md` y `plan-accion.md` (3.8 → Mitigado) en la implementación.

### OUT

- No se elimina ni modifica el middleware coarse `admin` (`AdminMiddleware`): sigue siendo la primera puerta con redirect a `/login` (Fase 1.2). `permission:` es la segunda puerta (403).
- No se migra la verificación a nivel de ruta en `routes/admin.php`: se mantiene el patrón controller-constructor (deuda de Fase 4).
- No se crean permisos dedicados para operaciones compuestas (filter/export/status/deleteAll) cuando ya existe la acción semántica (read/update/delete).
- No se añaden permisos a roles `landlord`/`tenant` (no acceden al panel admin).
- No se cambia el `Handler` para convertir 403 en redirect (se deja el comportamiento por defecto de Spatie).
- No se tocan Policies de Fase 2 (siguen aplicando; superadmin las bypassa por `Gate::before`).

### Cobertura objetivo

- 53/53 controladores admin con todos sus métodos alcanzados por ruta protegidos por `permission:`.
- 0 métodos trait (`acnooFilter`, `status`, `deleteAll`, `exportExcel`, `exportCsv`) alcanzados por ruta sin permiso.
- 11 módulos nuevos en `PermissionSeeder` (Super Admin + Admin).
- 15 casos de test (G6, F40–F51, U1, U2) en verde.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Spatie `permission` middleware registrado en `Kernel.php` | Existe (`PermissionMiddleware::class`) | Sin acción |
| P2 | `PermissionSeeder` con roles/permisos | Existe (roles `superadmin`/`admin` con muchos módulos) | Añadir 11 módulos nuevos |
| P3 | Trait `HasAcnooActions` (5 métodos) | Existe, 34 controladores lo usan | Ajustar `only` en cada uno |
| P4 | `AdminGuardSmokeTest` (G1–G5) | Existe, redirect a login | Mantener; añadir caso de denegación (403) |
| P5 | `CreatesUsers` trait (`createAdmin`/`createSuperadmin`) | Existe | Reutilizar; crear admin sin permisos vía factory + rol Spatie vacío |
| P6 | `RefreshDatabase` + `PermissionSeeder` en `setUp` | Existe en `AdminGuardSmokeTest` | Mantener para los nuevos tests |
| P7 | `AuthServiceProvider` con Policies Fase 2 | Existe | Añadir `Gate::before`; eliminar `Gate::define('isAdmin')` |

---

## 4. Estructura propuesta

```
app/Http/Controllers/Admin/*.php              # MODIFICADOS: constructores con permission: completo (53)
app/Providers/AuthServiceProvider.php         # MODIFICADO: Gate::before + eliminación Gate::define('isAdmin')
database/seeders/PermissionSeeder.php         # MODIFICADO: +11 módulos en Super Admin y Admin
tests/Feature/Admin/AdminGuardSmokeTest.php   # MODIFICADO: + caso de denegación (403) para admin sin permisos
tests/Feature/Admin/PermissionDenialSmokeTest.php  # NUEVO: 12 casos de denegación por módulo nuevo
tests/README.md                              # MODIFICADO: tabla de nuevos casos
.analisis/plan/plan-accion.md                # MODIFICADO: 3.8 → Mitigado (en implementación)
```

---

## 5. Decisiones técnicas

### 5.1 Capa de enforcement (Q1, Q2)

- Se mantiene `admin` (coarse, rol `admin|superadmin`) como primera puerta en `RouteServiceProvider` (`routes/admin.php` group `['web','auth','admin']`). Redirige a `/login` a anónimos y no-admin (preserva G1–G5).
- `permission:` se aplica en los **constructores de controladores** (patrón existente). Es la segunda puerta: un admin autenticado sin el permiso recibe `403` (Spatie `UnauthorizedException`).
- No se migra a nivel de ruta (deuda de Fase 4).

### 5.2 Mapeo acción semántica (Q6)

| Método / operación | Permiso |
|---|---|
| `index`, `show`, `acnooFilter`, `exportExcel`, `exportCsv`, `file_download`, `getInvoice`, `getDetailsMsg`, `getFacilities`, `getAmenities`, `propertySuccess`, `filterComment`, `mtView`, `mtReadAll`, `isDefault` (lectura), AJAX de gráficos (`getDashboardData`, `property_chart`, `account_chart`) | `<modulo>-read` |
| `create`, `store` | `<modulo>-create` |
| `edit`, `update`, `status`, `approve`, `reject`, `paid`, `default`, `isDefault`, `reply`, `bulkUpdate` | `<modulo>-update` |
| `destroy`, `deleteAll` | `<modulo>-delete` |

### 5.3 Módulos nuevos en `PermissionSeeder` (Q4, Q9)

Añadir a los arrays `Super Admin` y `Admin`:

| Módulo | Clave | Acciones |
|---|---|---|
| Blog | `blogs` | `r,c,u,d` |
| Client | `clients` | `r,c,u,d` |
| Comment | `comments` | `r,d` |
| Faq | `faqs` | `r,c,u,d` |
| Feature | `features` | `r,c,u,d` |
| Highlight | `highlights` | `r,c,u,d` |
| Testimonial | `testimonials` | `r,c,u,d` |
| Website settings | `website-settings` | `r,u` |
| Manage settings (OTP) | `manage-settings` | `r,u` |
| Translation | `translations` | `c,u,d` |
| Permission (ya existe) | `permissions` | `r,c` (sin cambios) |

Notas:
- `comments` solo `r,d` porque sus rutas son `index`/`destroy`/`filter`/`delete-all` (sin create/update/status/export).
- `translations` solo `c,u,d` porque no tiene `index` (solo store/update/bulkUpdate/destroy).
- Los módulos que ya estaban en el seeder (`languages`, `notifications`, `settings`, `currencies`, etc.) no cambian de acciones; solo se ajustan los `only` en sus controladores.

### 5.4 `Gate::before` para superadmin (Q5, Q11)

En `AuthServiceProvider::boot()`:

```php
Gate::before(function (User $user, string $ability) {
    return $user->hasRole('superadmin') ? true : null;
});
```

- Bypass total: permisos Spatie y Policies de Fase 2. Intencional: `superadmin` es rol de confianza total.
- `admin` sigue requiriendo permisos explícitos.
- Nota operativa: los roles `superadmin`/`admin` existentes en producción no tendrán los permisos nuevos hasta re-sembrar (`php artisan db:seed --class=PermissionSeeder`) o asignar manualmente. El bypass evita lockout de superadmin; admin debe re-sembrarse para operar los módulos nuevos.

### 5.5 Eliminación de `Gate::define('isAdmin')` roto (Q13)

Eliminar en `AuthServiceProvider`:

```php
Gate::define('isAdmin', function (User $user) {
    return $user->permissions()->first()->menu_slug;
});
```

Código roto (accede a `permissions()->first()->menu_slug`, null-prone). Sin usos conocidos en vistas/controladores (verificar con grep en implementación; si hay usos, reemplazar por `hasRole('admin')`).

### 5.6 Ajustes en controladores ya protegidos

Regla: para cada controlador que usa `HasAcnooActions`, añadir los métodos trait **expuestos por rutas** al grupo correspondiente según §5.2. Métodos trait no expuestos por rutas no se añaden (no son alcanzables); si se añade la ruta en el futuro, debe añadirse el permiso correspondiente.

Controladores y métodos adicionales a cubrir (no trait):

| Controlador | Métodos a añadir | Grupo |
|---|---|---|
| `AcnooAdminDashboardController` | `getDashboardData`, `property_chart`, `account_chart` | `dashboard-read` |
| `PropertyController` | `acnooFilter`, `exportExcel`, `exportCsv`, `propertySuccess`, `getFacilities`, `getAmenities` | `properties-read` |
| `AcnooCurrencyController` | `acnooFilter`, `deleteAll` | `currencies-read` / `currencies-delete` |
| `AcnooUserController` | `acnooFilter`, `status` | `users-read` / `users-update` |
| `AcnooSupportController` | `acnooFilter`, `getDetailsMsg` | `supports-read` |
| `NotificationController` | `acnooFilter`, `mtView`, `mtReadAll` | `notifications-read` |
| `NotifyContrller` | `mtReadAll` | `notifications-read` |
| `AcnooLandlordController` | `acnooFilter` | `landlords-read` |
| `AcnooTenantController` | `acnooFilter`, `file_download` | `tenants-read` |
| `AcnooRentPaymentController` | `acnooFilter`, `file_download` | `rent-payment-read` |
| `AcnooSecurityDepositController` | `acnooFilter`, `file_download` | `security-deposit-read` |
| `AcnooSubscriptionController` | `acnooFilter`, `file_download`, `getInvoice` | `subscriptions-read` |
| `AcnooWithdrawRequestController` | `acnooFilter`, `exportExcel`, `exportCsv` | `withdraw-request-read` |
| `AcnooRefundReasonController` | `acnooFilter` | `refund_reasons-read` |
| `AcnooWithdrawMethodController` | `acnooFilter` | `withdraw-methods-read` |
| `AcnooMessageController` | `acnooFilter` | `messages-read` |
| `AcnooExpenseController` / `AcnooIncomeController` | `acnooFilter` | `<modulo>-read` |

> Nota (2026-11-21): `NotifyContrller` (fila de arriba) era una clase muerta
> sin rutas y fue eliminada en `spec-typos-rutas-fase5`; la cobertura de
> permisos aplica al vivo `Admin\NotificationController`.

Los controladores de solo lectura (Transaction*, *Report, RentPaymentReport, etc.) cubren `acnooFilter`/`exportExcel`/`exportCsv` en su grupo `*-read` (ya tienen permiso read; solo se añaden los métodos al `only`).

### 5.7 Controladores nuevos a cubrir (12)

Constructores propuestos:

**AcnooBlogController** (`blogs`)
```php
public function __construct()
{
    $this->middleware('permission:blogs-read')->only('index', 'acnooFilter', 'filterComment');
    $this->middleware('permission:blogs-create')->only('create', 'store');
    $this->middleware('permission:blogs-update')->only('edit', 'update', 'status');
    $this->middleware('permission:blogs-delete')->only('destroy', 'deleteAll');
}
```

**AcnooClientController** (`clients`)
```php
$this->middleware('permission:clients-read')->only('index', 'acnooFilter');
$this->middleware('permission:clients-create')->only('create', 'store');
$this->middleware('permission:clients-update')->only('edit', 'update', 'status');
$this->middleware('permission:clients-delete')->only('destroy', 'deleteAll');
```

**AcnooCommentController** (`comments`)
```php
$this->middleware('permission:comments-read')->only('acnooFilter');
$this->middleware('permission:comments-delete')->only('destroy', 'deleteAll');
```

**AcnooFaqController** (`faqs`)
```php
$this->middleware('permission:faqs-read')->only('index', 'acnooFilter');
$this->middleware('permission:faqs-create')->only('create', 'store');
$this->middleware('permission:faqs-update')->only('edit', 'update', 'status');
$this->middleware('permission:faqs-delete')->only('destroy', 'deleteAll');
```

**AcnooFeatureController** (`features`)
```php
$this->middleware('permission:features-read')->only('index', 'acnooFilter');
$this->middleware('permission:features-create')->only('create', 'store');
$this->middleware('permission:features-update')->only('edit', 'update', 'status');
$this->middleware('permission:features-delete')->only('destroy', 'deleteAll');
```

**AcnooHighlightController** (`highlights`)
```php
$this->middleware('permission:highlights-read')->only('index', 'acnooFilter');
$this->middleware('permission:highlights-create')->only('create', 'store');
$this->middleware('permission:highlights-update')->only('edit', 'update', 'status');
$this->middleware('permission:highlights-delete')->only('destroy', 'deleteAll');
```

**AcnooTestimonialController** (`testimonials`)
```php
$this->middleware('permission:testimonials-read')->only('index', 'acnooFilter');
$this->middleware('permission:testimonials-create')->only('create', 'store');
$this->middleware('permission:testimonials-update')->only('edit', 'update');
$this->middleware('permission:testimonials-delete')->only('destroy', 'deleteAll');
```

**AcnooLanguageController** (`languages`, ya existe en seeder)
```php
$this->middleware('permission:languages-read')->only('index', 'show', 'acnooFilter');
$this->middleware('permission:languages-create')->only('store');
$this->middleware('permission:languages-update')->only('update', 'status', 'isDefault');
$this->middleware('permission:languages-delete')->only('destroy', 'deleteAll');
```

**AcnooWebSettingController** (`website-settings`)
```php
$this->middleware('permission:website-settings-read')->only('index');
$this->middleware('permission:website-settings-update')->only('update');
```

**AcnooSettingsManagerController** (`manage-settings`)
```php
$this->middleware('permission:manage-settings-read')->only('index');
$this->middleware('permission:manage-settings-update')->only('store');
```

**TranslationController** (`translations`)
```php
$this->middleware('permission:translations-create')->only('store');
$this->middleware('permission:translations-update')->only('update', 'bulkUpdate');
$this->middleware('permission:translations-delete')->only('destroy');
```

**PermissionController** (`permissions`, ya existe en seeder)
```php
$this->middleware('permission:permissions-read')->only('index');
$this->middleware('permission:permissions-create')->only('store');
```

**RoleController** ya tiene `permission:roles-*` completo (sin cambios).

---

## 6. Catálogo de casos

### 6.1 Denegación genérica (`G`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| G6 | Admin sin permisos recibe 403 | Usuario autenticado con rol Spatie `admin` pero sin permisos asignados | `GET /admin` | `403` (no redirect, no 200) |

### 6.2 Denegación por módulo nuevo (`F`)

Un caso por módulo: admin con rol `admin` pero sin el permiso del módulo → `403` en la ruta principal del módulo.

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F40 | Admin sin blogs-read recibe 403 en blogs | Usuario autenticado con rol `admin` sin permiso `blogs-read` | `GET /admin/blogs` | `403` |
| F41 | Admin sin clients-read recibe 403 en clients | Usuario autenticado con rol `admin` sin permiso `clients-read` | `GET /admin/clients` | `403` |
| F42 | Admin sin comments-delete recibe 403 en comments | Usuario autenticado con rol `admin` sin permiso `comments-delete` | `POST /admin/comments/delete-all` | `403` |
| F43 | Admin sin faqs-read recibe 403 en faqs | Usuario autenticado con rol `admin` sin permiso `faqs-read` | `GET /admin/faqs` | `403` |
| F44 | Admin sin features-read recibe 403 en features | Usuario autenticado con rol `admin` sin permiso `features-read` | `GET /admin/features` | `403` |
| F45 | Admin sin highlights-read recibe 403 en highlights | Usuario autenticado con rol `admin` sin permiso `highlights-read` | `GET /admin/highlights` | `403` |
| F46 | Admin sin testimonials-read recibe 403 en testimonials | Usuario autenticado con rol `admin` sin permiso `testimonials-read` | `GET /admin/testimonials` | `403` |
| F47 | Admin sin website-settings-read recibe 403 en website-settings | Usuario autenticado con rol `admin` sin permiso `website-settings-read` | `GET /admin/website-settings` | `403` |
| F48 | Admin sin manage-settings-read recibe 403 en manage-settings | Usuario autenticado con rol `admin` sin permiso `manage-settings-read` | `GET /admin/manage-settings` | `403` |
| F49 | Admin sin translations-create recibe 403 en translations | Usuario autenticado con rol `admin` sin permiso `translations-create` | `POST /admin/translations` | `403` |
| F50 | Admin sin permissions-read recibe 403 en permissions | Usuario autenticado con rol `admin` sin permiso `permissions-read` | `GET /admin/permissions` | `403` |
| F51 | Admin sin languages-read recibe 403 en languages | Usuario autenticado con rol `admin` sin permiso `languages-read` | `GET /admin/languages` | `403` |

### 6.3 Bypass superadmin (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | Superadmin bypassa permisos | Usuario autenticado con rol Spatie `superadmin` (sin permisos explícitos en BD) | `Gate::check('blogs-read')` o acceso a `GET /admin/blogs` | `true` / `200` |
| U2 | Admin sin permiso no bypassa | Usuario autenticado con rol `admin` sin `blogs-read` | `Gate::check('blogs-read')` | `false` |

### Tests de caracterización

Ninguno nuevo. Los casos G6, F40–F51 y U1–U2 verifican el comportamiento seguro nuevo y deben pasar tras la implementación. Si alguno falla por un hueco no cubierto, el fallo indica trabajo pendiente, no una caracterización.

---

## 7. Criterios de aceptación (Definition of Done)

- [ ] Los 53 controladores admin tienen todos sus métodos alcanzados por ruta cubiertos por `permission:` (verificación por grep/script).
- [ ] 0 métodos trait alcanzados por ruta sin permiso (`acnooFilter`, `status`, `deleteAll`, `exportExcel`, `exportCsv`).
- [ ] `PermissionSeeder` incluye los 11 módulos nuevos en los roles `Super Admin` y `Admin` con las acciones de §5.3.
- [ ] `AuthServiceProvider` contiene `Gate::before` para `superadmin` y no contiene `Gate::define('isAdmin', ...)`.
- [ ] No quedan usos de `Gate::define('isAdmin')`/`can('isAdmin')` sin reemplazar (grep).
- [ ] G6, F40–F51, U1, U2 pasan en `php artisan test tests/Feature/Admin/`.
- [ ] Los tests smoke existentes siguen en verde (especialmente `AdminGuardSmokeTest` G1–G5).
- [ ] `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.
- [ ] `tests/README.md` actualizado con los nuevos casos.
- [ ] `.analisis/plan/plan-accion.md`: fila 3.8 marcada `Mitigado` con referencia al spec y a los tests.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Lockout de admin en producción al activar permisos nuevos | Alto | `Gate::before` solo cubre `superadmin`; admin debe re-sembrar. Documentar en release notes y ejecutar `php artisan db:seed --class=PermissionSeeder` (idempotente vía `firstOrCreate` + `sync`). |
| Olvidar un método en un constructor → hueco residual | Medio | Verificación automatizada en DoD: script que cruza rutas admin vs `permission:` `only`. |
| `Gate::before` bypassa Policies de Fase 2 para superadmin | Medio (intencional) | Documentado en §5.4; superadmin es rol de confianza total. |
| Permisos nuevos no asignados a roles custom existentes | Bajo | Nota operativa en §5.4; los roles custom creados vía panel heredarán los nuevos permisos solo si se re-siembran o se asignan manualmente. |
| `Gate::define('isAdmin')` con usos ocultos | Bajo | Grep en implementación; si aparece, reemplazar por `hasRole('admin')`. |
| Inflación de permisos en módulos de solo lectura | Bajo | Solo se añaden grupos para métodos realmente alcanzados por rutas; `comments` y `translations` mantienen subconjuntos mínimos. |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

---

## 9. Estimación

- PermissionSeeder (+11 módulos) + AuthServiceProvider: 0.25 día.
- Ajuste de 53 controladores (constructores): 1.5 días.
- Tests (G6, F40–F51, U1, U2): 0.75 día.
- Verificación (script de cobertura, lint, analyse, suite): 0.5 día.
- Documentación (README, plan-accion): 0.25 día.
- Total: ~3.25 días.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 3 / 3.8
- `.analisis/seguridad.md` → Seg-18
- `.agents/skills/secure-coding.md` → punto 3 (middleware admin/landlord)
- `.agents/spec/spec-conventions.md`
- `.agents/spec-impl/spec-admin-middleware-fase1.md` (Fase 1 / 1.2)
- `.agents/spec-impl/spec-trait-acnoo-fase0.md` (trait `HasAcnooActions`)
- `tests/README.md`
- `app/Http/Kernel.php` (aliases `permission`, `admin`)
- `app/Providers/AuthServiceProvider.php`
- `database/seeders/PermissionSeeder.php`
- `routes/admin.php`
