# Tests — Suite Smoke Fase 0

Suite de **tests smoke** (caracterización) de los flujos críticos: auth web/API,
pagos, rutas públicas, guards y rutas Artisan. Origen:
`.agents/spec-impl/spec-tests-smoke-fase0.md` (Fase 0 / 0.1 del plan de acción).
La amplían los tests del trait `HasTableActions` (G1–G8/H1–H4, con 2
caracterizaciones nuevas): spec del trait de la Fase 0 (Fase 0 / 0.3, DT-C3 —
índice en `.agents/README.md`).

> **Nota (2026-11-23):** la suite creció con las fases 1–5 y hoy totaliza
> **569 casos** (371 al cierre de 5.11 + specs 5.12–5.20 — upgrade L13,
> controladores residuales, tx legadas, PHP 8.4 — + 8 del spec 5.21;
> conteo verificado contra salida real de `php artisan test`). Este README
> documenta el origen smoke de Fase 0
> y las convenciones; los dominios añadidos se listan abajo.

## Cómo correr la suite

```bash
php artisan test                        # suite completa
php artisan test --group=smoke           # solo smoke (todos los de esta suite llevan el grupo implícito por directorio)
php artisan test --group=characterization # solo tests de caracterización (4)
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

## `#[Group('characterization')]`

Estos tests **no validan comportamiento correcto**: documentan el
comportamiento actual **inseguro** para que, al aplicar el fix de la fase
indicada del plan de acción, el test falle de forma **intencional y
detectable**. Un fallo se interpreta como "el fix funcionó":

```php
// Expected to fail after Phase X (Seg-N fix) — update to secure behavior when it fails.
```

Al fallar uno: confirmar que el fallo corresponde a la fase indicada,
actualizar el test al comportamiento seguro, quitar `#[Group('characterization')]`
y el comentario, y actualizar el spec (ver skill `.agents/skills/tests-caracterizacion.md`).

> PHPUnit 12 no lee docblocks `@group`: los marcadores son atributos
> `#[Group('...')]` (`smoke`, `security`, `characterization`) desde el
> upgrade a Laravel 13 (spec `spec-laravel13-advisories-fase5.md`).

| Test | Bug caracterizado | Debe fallar tras |
|------|-------------------|------------------|
| `LandlordTableActionsSmokeTest::test_delete_all_deletes_foreign_amenities_without_ownership_check` | Seg-5 — `deleteAll` landlord sin ownership check (IDOR bulk) | Fase 2 |
| `LandlordApplicationHttpTest::test_f10_file_download_web_ignores_type_parameter` | Quirk file_download web — ignora `$type` y sirve siempre `landlord_agreement` (spec hijo 3 §5) | follow-up file_download (fuera de 5.13) |
| `LandlordApplicationHttpTest::test_f11_file_download_api_reads_missing_file_attribute` | Quirk file_download api — lee `$application->file`, atributo inexistente → null siempre (spec hijo 3 §5) | follow-up file_download (fuera de 5.13) |
| `SignupAccountHttpTest::test_f12_demo_mode_does_not_block_signup` | Gap DemoMode — signup público no bloqueado en `APP_ENV=demo` (`.analisis/seguridad.md` fila 14, hallazgo H3 de la revisión del hijo 4) | decisión de bloqueo de signup en demo (fuera de 5.13) |

> Nota G2/G4: el 403 observado no lo produce `AdminMiddleware` (que ya dejó
> pasar al landlord/tenant por el bug de precedencia) sino el middleware Spatie
> `permission:dashboard-read`. Tras el fix de Seg-2 el resultado debe ser 302 a
> `/login` antes de llegar al check de permisos.

## Estructura

```
tests/
├── Concerns/CreatesUsers.php     # helpers landlord/admin/tenant/superadmin (+ rol Spatie)
├── Stubs/StubGateway.php         # stub de gateway (Gateway->namespace), sin HTTP real
├── Unit/                         # unitarios: modelos (User, Plan), PropertyService,
│   ├── Support/                  # CacheKeysTest (4.9) + OtpTest (4.6) + OptionsTest U1–U9 (5.7) + PaymentStateTest U2–U3 (5.21)
│   ├── Config/                   # SessionConfigTest U1–U5 (5.5) + ForceHttpsConfigTest U1–U6 (5.6) + LoggingArchTest U1–U2 (5.9) + SessionSerializationConfigTest U1 (5.21)
│   ├── Session/                  # SessionWritersArchTest U4 (5.21): allowlist congelada de 15 escritores
│   ├── Views/                    # arch-tests U10–U11: 0 get_option()[...] sin guard, 0 logo_setting (5.7)
│   ├── Models/                   # UserFillableTest U1–U5 (5.11): allowlist + fill/forceFill
│   ├── Hygiene/                  # arch-tests U6: 0 $request->except( en los 4 User writers (5.11)
│   ├── Console/                  # VerifyDeployPhpFloorTest U20–U21 (5.20) + VerifyDeploySessionTest U22–U23 (5.21)
│   └── Seeders/                  # arch-tests U1–U3 + demoReset U4 (spec 4.10)
└── Feature/
    ├── Auth/                     # L (login web), S (signup+OTP), R (reset), A (API)
    │   └── *MassAssignmentTest    # S1–S6 + A1–A4 (5.11): inyección cerrada + forceFill
    ├── Payment/                  # P (flujos de pago + whitelist de namespace); sesión escalar P1–P9 (5.21)
    │   └── BalanceForceFillTest   # P1–P3 (5.11): balance vía forceFill
    ├── Public/                   # Pub (CMS frontal)
    ├── Admin/                    # G (guard admin + denegación de permisos 3.8)
    │   └── *SelfTest/*SubscriptionTest/*MassAssignmentTest  # G1–G9 (5.11)
    ├── Landlord/                 # H (guard landlord + expired)
    │   └── TenantForceFillTest   # H1–H2 (5.11)
    ├── Api/                      # H3 (5.11): profile_type validado en profileSetUp
    ├── Maintenance/              # M (rutas Artisan)
    ├── Cache/                    # invalidación centralizada (4.9)
    ├── Demo/                     # DemoMode + demoReset (3.5)
    ├── Idor/                     # ownership smoke (Fase 2)
    ├── Seeders/                  # F1–F6 del spec 4.10
    ├── Withdraw/                 # regresión HTTP del index API (Fase 5 / 5.3, Seg-20)
    ├── Security/                # TrustHosts (5.4) + Session (5.5) + ForceHttps redirect/proxies (Fase 5 / 5.6)
    ├── Options/                 # Pub1 + F1–F4 (render sin options) + G1–G2 (version sidebar) (Fase 5 / 5.7)
    ├── Routes/                  # guards de higiene de la tabla de rutas F14–F18 (Fase 5 / 5.8)
    ├── Console/                 # ops:verify-deploy F1–F6 (patrón de evidencia, Fase 5 / 5.9 + F5 5.20 + F6 5.21)
    ├── Notification/            # contrato HTTP de notifications G1–G5 / H1–H5 / A1–A4 (Fase 5 / 5.8)
    └── HandlerGenericErrorSmokeTest.php  # Handler genérico sin fuga de mensaje (3.4)
```

Mocks obligatorios (ver `.agents/skills/testing-conventions.md`):
`Artisan::shouldReceive('call')` en M (jamás ejecutar migraciones/seed reales),
`Mail::fake()`/`Notification::fake()` en S/R/A, `Sanctum::actingAs` en A,
`StubGateway`/`$this->mock(App\Library\*)` en P.

## Cobertura de endpoints críticos

> Snapshot del catálogo smoke de **Fase 0** (52 endpoints críticos); la suite
> total creció con las fases 1–5 (569 casos al cierre de 5.21) sin renovar
> este conteo.

Criterio del spec: rutas de auth (web + API), pagos, status de gateways,
CMS público, entradas de guards y maintenance.

| Dominio | Rutas críticas | Cubiertas | Casos |
|---------|---------------|-----------|-------|
| Auth web | 10 | 9 | L1–L6, S1–S8, R1–R5 (solo `otp-resend` sin cover) |
| Auth API | 8 | 7 | A1–A10 (solo `resend-otp` sin cover) |
| Pagos web | 6 | 6 | P1–P22 (P1–P11 firma de URLs/endpoint API; P12–P19 /payment/success; P20–P22 whitelist de namespace gateway; ver spec-payment-controller-sin-user-id-fase2, spec-payment-success-fase2, spec-gateway-namespace-whitelist-fase2) |
| Status gateways | 12 | 8 | P10 (GET; los 4 POST/view de paystack/razorpay sin cover) |
| CMS público | 12 | 10 | Pub1–Pub10 (sin `POST /blogs` ni `highlights/{id}`) |
| Guards | 2 | 2 | G1–G4, H1–H4 |
| Maintenance | 2 | 2 | M1–M6 |
| **Total** | **52** | **44 (84.6 %)** | ≥ 60 % ✓ |

## Throttle (Fase 3 / 3.7)

Casos que verifican `429 Too Many Requests` en el 6.º intento (mismo IP): `L7` (login web), `A20` (sign-in API), `A21` (send-reset-code API). Aserción sobre status 429 y header `Retry-After`.

## Permisos admin (Fase 3 / 3.8)

Casos de denegación (`403`) para admin autenticado sin el permiso requerido: `G6` (admin sin permisos → 403 en `/admin`), `F40`–`F51` (uno por módulo nuevo: blogs, clients, comments, faqs, features, highlights, testimonials, website-settings, manage-settings, translations, permissions, languages), `U1` (superadmin sin permisos bypassa), `U2` (admin sin permiso no bypassa). Archivos: `AdminGuardSmokeTest`, `PermissionDenialSmokeTest`.

## Withdraw (Fase 5 / 5.3)

Regresión HTTP del bug Seg-20 (`index` sin `Request` inyectado; fix absorbido
por Fase 4 / 4.4): `F1` per_page respetado (`data.per_page` + 5 items),
`F2` rango `from_date`/`to_date`, `F3` scoping por `user_id` (`#[Group('security')]`).
Archivo: `tests/Feature/Withdraw/WithdrawIndexSmokeTest.php` — spec
`spec-withdraw-index-request-fase5`. La key de respuesta se aserta como
`'message'` desde el spec 5.10 (el typo `'meassage'` original quedó corregido).

## TrustHosts (Fase 5 / 5.4)

Validación del Host header (Seg-21): `F1` host ajeno → **400**, `F2` host de
`APP_URL` y `F3` subdominio pasan, `F4` dev host (`127.0.0.1`) pasa en `testing`
(rama dev de `hosts()`). Archivo:
`tests/Feature/Security/TrustHostsSmokeTest.php` — spec `spec-trust-hosts-fase5`.
Detalles operativos: el cliente de test normaliza la URI vía `url()` y
`Request::create` sobrescribe `HTTP_HOST`, así que el Host custom se inyecta
con request Symfony construida a mano (helper `getWithHost`); el `tearDown`
resetea los patterns static de Symfony (`Request::setTrustedHosts([])`).

## Session (Fase 5 / 5.5)

Driver `database` + cookie `secure` (Seg-22): `U1`–`U5` (en
`tests/Unit/Config/SessionConfigTest.php`) evalúan el archivo real
`config/session.php` con env mutado — el reader de `env()` consulta
`$_ENV` → `$_SERVER` → `getenv()` en cada lectura, así que las mutaciones
son en caliente y el `tearDown` restaura las tres fuentes (el snapshot se
toma tras el boot porque dotenv añade keys de `.env`). `F1` (flags
`Secure; HttpOnly; SameSite=Lax` de la cookie con `session.secure=true`)
y `F2` (login real con `DatabaseSessionHandler`: fila en `sessions` con
`user_id` + segunda request replicando la cookie del response — guest iría
a `login`, autenticado sin plan a `landlord.subscriptions.index` vía
`ExpiredMiddleware`). Archivo feature:
`tests/Feature/Security/SessionSmokeTest.php` — spec
`spec-session-database-secure-fase5`.

## Session serialization (Fase 5 / 5.21)

Flip `session.serialization` a `json` con contrato escalar: la suite
entera corre ya bajo json (sin test round-trip del framework — Q8).
`U1` (`tests/Unit/Config/SessionSerializationConfigTest.php`) evalúa el
archivo fresco y aserta el literal sin válvula `env()`; `U2`–`U3`
(`tests/Unit/Support/PaymentStateTest.php`) fijan el contrato escalar
de `PaymentState::fromSession` (basura en sesión → tratada como
ausente, sin crash); `U4` (`tests/Unit/Session/SessionWritersArchTest.php`)
congela la allowlist de 15 archivos escritores — el guard mismo atrapó
a `RespondsJson` (forma `session()->flash()`), omitido por el inventario
del spec; `U22`–`U23` (`tests/Unit/Console/VerifyDeploySessionTest.php`)
cubren el helper puro del 7º check HARD y `F6` su fila en la tabla de
`ops:verify-deploy`. Los casos `P1`–`P9` migran los 6 bloques
`withSession` con modelos a IDs scalares y añaden `rent_payment`
rehidratado (P4, sin cobertura previa) — spec
`spec-session-serialization-json-fase5`.

## Force HTTPS (Fase 5 / 5.6)

Redirect 302 http→https tras la válvula `FORCE_HTTPS` + proxies confiables
vía `TRUSTED_PROXIES` (Seg-23): `U1`–`U6` (en
`tests/Unit/Config/ForceHttpsConfigTest.php`) evalúan el archivo real
`config/app.php` con env mutado — default off, cast `'true'`/`'false'` de
`Env::get`, `null` default de proxies, `'*'` y CSV pasan intactos.
`F1` (válvula on: `http` → 302 con `Location` https y path+query
preservados), `F2` (https pasa con válvula on), `F3` (válvula off:
suite aislada, sin redirect), `F4` (proxy confiado con
`HTTP_X_FORWARDED_PROTO=https`: `request->secure()` true, sin redirect
infinito). Archivo feature: `tests/Feature/Security/ForceHttpsSmokeTest.php`
— spec `spec-app-url-https-force-tls-fase5`.

## Opciones tipadas (Fase 5 / 5.7)

Tipos consistentes en `get_option` (Seg-24): `U1`–`U9` (en
`tests/Unit/Support/OptionsTest.php`) fijan el contrato de
`App\Support\Options` — defaults tipados (`[]`/`''`, nunca null), JSON
corrupto → default, deriva de tipo silenciosa (scalar leído como array y
viceversa) e invalidación del observer a través del helper.
`U10`–`U11` (en `tests/Unit/Views/UnguardedOptionAccessTest.php`) son
arch-tests: 0 accesos `get_option(...)[...]` sin `??` en
`resources/views/**` (scan por línea, limitación documentada en el spec)
y 0 referencias a la key fantasma `logo_setting`. `Pub1` (`/privacy-policy`
con la tabla `options` vacía → 200; antes 500) y `F1`–`F4` (render directo
de un invoice por familia + property-success ×2 con tabla vacía; el render
fuera del middleware web comparte `$errors` vía `withViewErrors([])` y
autentica donde la vista/layout lee `auth()->user()`). `G1`–`G2` (en
`tests/Feature/Options/VersionSidebarSmokeTest.php`): la key escalar
`version` se lee vía `App\Support\Options::string` en el sidebar admin.
Archivos: `tests/Feature/Options/OptionlessViewsSmokeTest.php` — spec
`spec-get-option-tipos-consistentes-fase5`.

## Higiene de rutas (Fase 5 / 5.8)

`F9`–`F12` (en `tests/Feature/Demo/DemoModeTest.php`): transacciones
update/destroy y suscripciones paid/reject vuelven a bloquearse en
demo — los 4 nombres de ruta de `DemoMode` estaban muertos (doble punto
/ prefijo equivocado) y las acciones pasaban. `F13`: guard de
existencia — todo nombre en `DemoMode::ADMIN_DISABLED_ROUTES` /
`LANDLORD_DISABLED_ROUTES` (ahora `public const`) debe resolver a una
ruta registrada. `F14`–`F18` (en
`tests/Feature/Routes/RouteNamesSmokeTest.php`): unicidad de nombres en
la colección plana (nombres con `::` son del paquete LaravelInstaller,
fuera del guard), 0 nombres de prefijo desnudo (`api`/`admin.`), rename
`apiread-notifications` pineado (URI typo inexistente), colisión
`apimaintenance.index` resuelta (`landlord`/`tenant`) y salud de fuente
(0 `read-nitifications`, 1 `income-categories/delete-all`,
0 `NotifyContrller`). `G1`–`G5` / `H1`–`H5` / `A1`–`A4` (en
`tests/Feature/Notification/`): contrato HTTP de notifications admin/
landlord/api (post-tidy de URIs) — repara el DoD de
`spec-service-notification-fase4`, que declaraba estos tests sin
crearlos. Spec: `spec-typos-rutas-fase5`.

## Verificación de deploy (Fase 5 / 5.9)

`U1`–`U2` (en `tests/Unit/Config/LoggingArchTest.php`): el fallback de
`LOG_LEVEL` en `config/logging.php` no vuelve a ser `debug` (6 canales en
`warning`; slack conserva `critical`) y `.env.example` sigue pineando
`LOG_LEVEL=warning` + `LOG_DEPRECATIONS_CHANNEL=null` (Seg-26).
`F1`–`F4` (en `tests/Feature/Console/VerifyDeployTest.php`): el comando
`ops:verify-deploy` — patrón de evidencia de los residuales operacionales
de 5.9/5.5/5.6 — verifica config efectivo (5 HARD + 3 WARN, tabla
esperado/efectivo/resultado); enforcement solo con `APP_ENV=production`
(F3: no-producción es report-only), exit code binario y salida sin el
valor de `APP_KEY` (F4). La suite fuerza config en runtime (patrón
ForceHttpsSmokeTest), sin mutar `$_ENV`. Spec:
`spec-log-level-warning-fase5`.

## `$fillable` de `User` restrictivo (Fase 5 / 5.11)

Allowlist de 7 columnas + fin del patrón `$request->except()` en los 6
escritores de `User` + mapa `forceFill` (~18 sitios) — cierra DT-B3 y los
hallazgos **Seg-28** (inyección masiva + override por merge-order en
signup público web/Api y sitios admin) y **Seg-29** (IDOR de
`Admin/ProfileController::update`: el id de ruta resolvía cualquier
cuenta; ahora el target es siempre el usuario de sesión).

- `U1`–`U5` (`tests/Unit/Models/UserFillableTest.php`): allowlist exacto,
  `fill()` ignora las 8 retiradas, `forceFill()` sí asigna, factories
  unguarded intactas, hooks `booted()`/`boot()` intactos.
- `U6` (`tests/Unit/Hygiene/UserMassAssignmentArchTest.php`): arch-test
  scoped — 0 `$request->except(` en los 4 controladores migrados (los 43
  sitios no-`User` quedan como lateral audit-pending del spec).
- `S1`–`S6` / `A1`–`A4` (`WebSignupMassAssignmentTest` /
  `ApiSignupMassAssignmentTest`): las inyecciones se ignoran (S1/S2/A1
  nacieron como caracterización de Seg-28 y se flipearon en el mismo PR);
  valores server-side (OTP, verificación, free-plan trío) persisten vía
  forceFill.
- `G1`–`G9` (`UserAdminMassAssignmentTest`, `AdminProfileSelfTest`,
  `LandlordStoreSubscriptionTest`, `SubscriptionPaidTest`): inyección
  admin ignorada, self-profile siempre sobre el usuario de sesión (G4/G5),
  trío del free-plan y de `paid()` persistido — `paid()` además invalida
  `plan-data-{user_id}` estructuralmente (G9, cierre del gap §3 P5 de
  4.9).
- `P1`–`P3` (`Payment/BalanceForceFillTest.php`): balance acreditado
  (payment success security_deposit) y debitado (withdraw web/Api) vía
  forceFill.
- `H1`–`H2` (`Landlord/TenantForceFillTest.php`): `landlord_id` y OTP del
  tenant persisten vía forceFill (web y Api).
- `H3` (`Api/ProfileSetUpValidationTest.php`): `profile_type` validado
  (`nullable|string`) en `profileSetUp`.
- `F1`–`F5` (`Seeders/SeededRoleAlignmentTest.php`): columna `role` del
  seeder alineada con Spatie por asignación explícita (Q6) + login
  redirect, gate de sign-in Api, `notify_users()` y receptor de soporte.

Desviación **D1** (documentada en el spec): el "quirk 1.4" del seeder no
era reproducible — `SeedCommand` corre los seeders dentro de
`Model::unguarded()` (vendor `SeedCommand.php:69`), así que la clave
`'role'` de `firstOrCreate` sí se persistía; F2/F5 quedaron como
regresión pura y la reestructuración del seeder es hardening
(unguard-independiente). Desviación **D2**: `IdorPolicySmokeTest` no tenía
trait de DB y commiteaba sus fixtures a la DB compartida — ahora usa
`DatabaseTransactions` (descubierto por la sensibilidad al orden de F4).
Suite: 334 + 37 = **371**. Spec: `spec-user-fillable-restrictivo-fase5`.

## Desviaciones vs el catálogo del spec

Documentadas en `.agents/spec-impl/spec-tests-smoke-fase0.md` §12 (D1–D6):
A4 responde 200 (no 201), R2/R5 usan el broker real + `Notification::fake`,
G2/G4 esperan 403, P5 añade factories Currency/Blog/SecurityDeposit/
PlanSubscribe/PropertyDetail/Category, `MAIL_USERNAME` en `phpunit.xml`,
G3 requiere rol Spatie.
