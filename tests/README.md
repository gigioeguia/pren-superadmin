# Tests — Suite Smoke Fase 0

Suite de **tests smoke** (caracterización) de los flujos críticos: auth web/API,
pagos, rutas públicas, guards y rutas Artisan. Origen:
`.agents/spec-impl/spec-tests-smoke-fase0.md` (Fase 0 / 0.1 del plan de acción).
La amplían los tests del trait `HasAcnooActions` (G1–G8/H1–H4, con 2
caracterizaciones nuevas): `.agents/spec-impl/spec-trait-acnoo-fase0.md`
(Fase 0 / 0.3, DT-C3).

## Cómo correr la suite

```bash
php artisan test                        # suite completa
php artisan test --group=smoke           # solo smoke (todos los de esta suite llevan el grupo implícito por directorio)
php artisan test --group=characterization # solo tests de caracterización (9)
php artisan test tests/Feature/Auth/     # un dominio
```

## Precondiciones del entorno

1. **DB de testing MySQL**: `dbrentpro_test` debe existir (ADR-0001). `phpunit.xml`
   ya setea `DB_DATABASE=dbrentpro_test`, `DB_CONNECTION=mysql` y `APP_KEY` fija.
   Crearla (una vez, como root):

   ```sql
   CREATE DATABASE dbrentpro_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   GRANT ALL PRIVILEGES ON dbrentpro_test.* TO 'admRentpro'@'%';
   ```

   **Nunca** apuntar los tests a la DB del `.env` (`dbrentpro`).

2. **`vendor/autoload1.php` debe existir** (marcador del instalador
   `safiull/laravel-installer`). Sin él, el middleware `CheckToken` **borra
   `vendor/laravel`** en cualquier request web a una ruta no excluida y la app
   queda inservible. Tras un `composer install` fresco, recrearlo:

   ```bash
   echo '{"marker":"installer"}' > vendor/autoload1.php
   ```

3. `MAIL_USERNAME` está seteado en `phpunit.xml` (`smoke@test.local`) porque
   `SignupController` lo exige de forma incondicional. El test S8 lo unsetea
   en runtime (`$_ENV`/`$_SERVER`/`putenv`) y lo restaura en `tearDown`.

## `@group characterization`

Estos tests **no validan comportamiento correcto**: documentan el
comportamiento actual **inseguro** para que, al aplicar el fix de la fase
indicada del plan de acción, el test falle de forma **intencional y
detectable**. Un fallo se interpreta como "el fix funcionó":

```php
// Expected to fail after Phase X (Seg-N fix) — update to secure behavior when it fails.
```

Al fallar uno: confirmar que el fallo corresponde a la fase indicada,
actualizar el test al comportamiento seguro, quitar `@group characterization`
y el comentario, y actualizar el spec (ver skill `.agents/skills/tests-caracterizacion.md`).

| Test | Bug caracterizado | Debe fallar tras |
|------|-------------------|------------------|
| `ApiAuthSmokeTest::test_api_sign_up_accepts_arbitrary_role` | Seg-4 — registro API con `role` arbitrario | Fase 1 |
| `ApiAuthSmokeTest::test_api_reset_password_without_otp_changes_password` | Seg-3 — reset password sin OTP | Fase 1 |
| `AdminGuardSmokeTest::test_landlord_passes_broken_admin_middleware` | Seg-2 — `AdminMiddleware` bug de precedencia | Fase 1 |
| `AdminGuardSmokeTest::test_tenant_passes_broken_admin_middleware` | Seg-2 — ídem (tenant) | Fase 1 |
| `ArtisanRoutesSmokeTest::test_public_update_route_runs_migrations_and_sets_version` | Seg-1 — `/update` público ejecuta Artisan | Fase 1 |
| `ArtisanRoutesSmokeTest::test_public_cache_clear_route_clears_cache` | Seg-1 — `/cache-clear` público | Fase 1 |
| `PaymentFlowSmokeTest::test_payment_success_processes_subscription_from_session` | Seg-6 — `/payment/success` público procesa desde sesión | Fase 2 |
| `LandlordAcnooActionsSmokeTest::test_delete_all_deletes_foreign_amenities_without_ownership_check` | Seg-5 — `deleteAll` landlord sin ownership check (IDOR bulk) | Fase 2 |
| `LandlordAcnooActionsSmokeTest::test_filter_route_runs_delete_all_due_to_crossed_route` | Seg-15 — ruta cruzada `properties/filter`→`deleteAll` en `routes/landlord.php:15` | Fase 3.6 |

> Nota G2/G4: el 403 observado no lo produce `AdminMiddleware` (que ya dejó
> pasar al landlord/tenant por el bug de precedencia) sino el middleware Spatie
> `permission:dashboard-read`. Tras el fix de Seg-2 el resultado debe ser 302 a
> `/login` antes de llegar al check de permisos.

## Estructura

```
tests/
├── Concerns/CreatesUsers.php     # helpers landlord/admin/tenant/superadmin (+ rol Spatie)
├── Stubs/StubGateway.php         # stub de gateway (Gateway->namespace), sin HTTP real
└── Feature/
    ├── Auth/                     # L (login web), S (signup+OTP), R (reset), A (API)
    ├── Payment/                  # P (flujos de pago)
    ├── Public/                   # Pub (CMS frontal)
    ├── Admin/                    # G (guard admin)
    ├── Landlord/                 # H (guard landlord + expired)
    └── Maintenance/              # M (rutas Artisan públicas)
```

Mocks obligatorios (ver `.agents/skills/testing-conventions.md`):
`Artisan::shouldReceive('call')` en M (jamás ejecutar migraciones/seed reales),
`Mail::fake()`/`Notification::fake()` en S/R/A, `Sanctum::actingAs` en A,
`StubGateway`/`$this->mock(App\Library\*)` en P.

## Cobertura de endpoints críticos

Criterio del spec: rutas de auth (web + API), pagos, status de gateways,
CMS público, entradas de guards y maintenance.

| Dominio | Rutas críticas | Cubiertas | Casos |
|---------|---------------|-----------|-------|
| Auth web | 10 | 9 | L1–L6, S1–S8, R1–R5 (solo `otp-resend` sin cover) |
| Auth API | 8 | 7 | A1–A10 (solo `resend-otp` sin cover) |
| Pagos web | 6 | 6 | P1–P9 |
| Status gateways | 12 | 8 | P10 (GET; los 4 POST/view de paystack/razorpay sin cover) |
| CMS público | 12 | 10 | Pub1–Pub10 (sin `POST /blogs` ni `highlights/{id}`) |
| Guards | 2 | 2 | G1–G4, H1–H4 |
| Maintenance | 2 | 2 | M1–M2 |
| **Total** | **52** | **44 (84.6 %)** | ≥ 60 % ✓ |

## Desviaciones vs el catálogo del spec

Documentadas en `.agents/spec-impl/spec-tests-smoke-fase0.md` §12 (D1–D6):
A4 responde 200 (no 201), R2/R5 usan el broker real + `Notification::fake`,
G2/G4 esperan 403, P5 añade factories Currency/Blog/SecurityDeposit/
PlanSubscribe/PropertyDetail/Category, `MAIL_USERNAME` en `phpunit.xml`,
G3 requiere rol Spatie.
