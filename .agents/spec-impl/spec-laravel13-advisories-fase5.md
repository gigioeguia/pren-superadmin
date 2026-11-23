---
id: spec-laravel13-advisories-fase5
titulo: "Remediación de advisories de laravel/framework: upgrade 11.57.1 → 13.x + hardening de serialización (Fase 5 / 5.15, absorbe 5.16)"
estado: Implementado
fecha: 2026-11-22
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.15; absorbe 5.16) — hallazgo lateral del spec 5.12 (composer audit, 2026-11-21)
cobertura: Upgrade de laravel/framework 11.57.1 (security-EOL) a 13.x cerrando los 4 advisories únicos del audit (1 high expuesto, 1 medium + 2 low no expuestos), rename nunomaduro/larastan → larastan/larastan (5.16), guard CI composer audit bloqueante y hardening de serialización de cache (D2); sin cambio de runtime (PHP 8.3, floor de L13)
---

# Spec — Remediación de advisories de laravel/framework: upgrade 11.57.1 → 13.x (Fase 5 / 5.15, absorbe 5.16)

> Upgrade a la línea de framework con soporte de seguridad (13.x) cerrando los 4 advisories únicos de `composer audit`, en dos entregas: D1 (mecánica: composer + CSRF + phpunit 12 + larastan rename + CI audit) y D2 (hardening de serialización de cache con contrato público invariante).

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.15; absorbe 5.16) — hallazgo lateral del spec 5.12.

---

## 1. Objetivo

Sacar `laravel/framework` de la rama sin parches (11.x security-EOL desde 2026-03-12; los 4 advisories del audit no tendrán fix en 9/10/11 nunca) llevándolo a 13.x (security hasta 2028-03-17, floor PHP 8.3 = sin cambio de runtime), cerrando por código parcheado los 4 advisories únicos, con guard de CI anti-reincidencia y hardening de serialización de cache.

---

## 2. Alcance

### Evaluación de exposición por advisory (2026-11-22, greps sobre `main`)

| Advisory | Severidad / fix | Exposición en el repo | Cierre |
|---|---|---|---|
| CRLF injection in default email rule — GHSA-5vg9-5847-vvmq / CVE-2026-48019 (PKSA-3r5d + dup PKSA-mdq4 en audit) | High 8.9; fix ≥12.60 / ≥13.10 | **Directa**: ~19 validadores `'email'` + ~14 `Mail::to($request->email)` / `Mail::to($data['email'])` en flujos de auth (signup/OTP, password reset, welcome) — el patrón exacto del advisory ("authentication flows"); sin workaround oficial | Código parcheado en 13.10+; guard app-side F2 |
| Temporary Signed URL Path Confusion — GHSA-crmm-hgp2-wgrp | Medium 4.2; fix ≥12.61.1 / ≥13.12 | **No expuesto**: es sobre `Storage::temporaryUrl()`/`temporaryUploadUrl()` en driver `local` — 0 usos en `app/`. Las rutas `signed` de pagos (spec 2.3) son otro mecanismo, no afectado | Fix + evidencia grep en este spec |
| Task Output Injection - Concurrency Process — GHSA-63hm-g96r-2m2q / CVE-2026-128083 | Low 2.3; fix ≥12.71 / ≥13.40 | **No expuesto**: requiere `Concurrency::run()` en código de app — 0 usos | Fix + evidencia grep |
| XSS in Debug Page Information — GHSA-jh5r-qr3c-85q8 / CVE-2026-102279 | Low 3.1; fix ≥12.69 / ≥13.30 | **Mitigada**: requiere `APP_DEBUG=true` (postura prod desde Fase 3 / 3.4) | Fix + postura |

Target de lock: **13.43+** (vigente al implementar), ≥ los 4 fixes (13.10/13.12/13.30/13.40). El 5º entry del audit (PKSA-mdq4-51ck-6kdq) es el mismo CVE-2026-48019 reportado por la fuente Packagist — desaparece con el upgrade.

### IN

**Entrega D1 — mecánica (upgrade + 5.16 + guard CI):**

- `composer.json`: `laravel/framework` → `^13.0`, `phpunit/phpunit` → `^12.0`, `nesbot/carbon` → `^3.0`, `laravel/tinker` → `^3.0` (guide 13); rename `nunomaduro/larastan` → `larastan/larastan` (`^3.13`, cierra **5.16**). `phpstan.neon.dist`: include path a `vendor/larastan/larastan/extension.neon`.
- Constraints que NO cambian (ya permiten 13.x): `laravel/sanctum ^4.0` (resuelve 4.3.x), `spatie/laravel-permission ^6.3` (6.25), `maatwebsite/excel ^3.1` (3.1.70), `safiull/laravel-installer ^3.5` (sin constraint de framework), `nunomaduro/collision ^8.1` (resuelve 8.9.x), `spatie/laravel-ignition ^2.0` (resuelve 2.12.x; el platform pin php 8.3 excluye la 2.13 que exige 8.4).
- CSRF sweep: `app/Http/Middleware/VerifyCsrfToken.php` → `PreventRequestForgery.php` (extends `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`), referencia en `app/Http/Kernel.php` (alias `csrf`). 2 archivos, 0 referencias en tests. Trae la verificación de origen `Sec-Fetch-Site`.
- phpunit ^12: migrar los docblocks `@group` (~30 en ~15 archivos, grupos `smoke`/`security`/`characterization`) a atributos `#[Group]`. Verificado: 0 `@test`/`@dataProvider` en la suite.
- CI: 4º job `audit` (`composer audit --locked`, bloqueante, sin MySQL service, igual estructura que `static-analysis`); válvula documentada `audit.ignore` en `composer.json` para advisories sin fix.
- Disciplina de config: **no sincronizar** `config/*.php` del skeleton 13 (la trampa `__PHP_Incomplete_Class` / logouts vive ahí). Solo se añaden keys deliberadas (D2).
- `phpstan.neon.dist` mantiene `phpVersion: 80300`; `composer.json` mantiene `php: ^8.3` y `platform.php: 8.3` (sin bump de runtime).

**Entrega D2 — hardening de serialización de cache (contrato público invariante):**

- Los 3 caches de objetos (`plan_data()` → modelo `PlanSubscribe` con `will_expire` embebido, `languages()` → Collection Eloquent, `Currency::default()` → modelo `Currency`) pasan a **payload array en el store** con **rehidratación Eloquent en lectura**. El contrato público de los helpers no cambia: los consumidores siguen recibiendo `?PlanSubscribe` con `->plan`/`->will_expire` (`ExpiredMiddleware:27,110`, `PropertyService:338`, `TenantService:135`, `Api/LaborController:34`), Collection con `->where()/->first()` (vistas `layouts/{admin,landlord}/partials/header.blade.php`) y objeto con `->code` (`Payment:16`, helper `currency()`).
- `config/cache.php` += `'serializable_classes' => false` (endurece deserialización si `APP_KEY` se filtra; activo y verificado por test).
- `CacheKeys::forgetModel()` intacto: claves históricas, solo cambia el tipo del value.

### OUT

- **Session `serialization: json`** — desviación de la convergencia previa por evidencia nueva: `PaymentController:96-97` almacena **modelos** en la sesión (`session('user')`/`session('plan')`); `json` los degradaría a `stdClass` y rompería el flujo de pagos. Queda `php` (key ausente = comportamiento actual) y se registra el flip como **residual con precondición** (§8).
- Bump de PHP 8.3 → 8.4 (fila nueva del plan; security 8.3 hasta 2027-12).
- Sync completo de configs del skeleton 13 (solo las 2 keys deliberadas de D2; session/serializable fuera).
- Migración de estructura de app (Kernels → `bootstrap/app.php`): L13 soporta la estructura actual, como hizo L11 (precedente spec 4.1).
- Laravel 14 (Q1 2027), moderación de comentarios (5.19), restauración CRUD (5.17), tx legadas (5.18).
- Tests nuevos de flujos de negocio: la suite actual (496 casos según plan 5.14) es la red; solo guards puntuales (F2, F9–F11).

### Cobertura objetivo

- `composer audit --locked` → **0 advisories**.
- Suite completa 100 % en verde (conteo del momento del impl; 0 failures/errors) sobre L13 + phpunit 12.
- PHPStan nivel 5 exit 0 con `larastan/larastan` (baseline: solo adiciones de preexistente recién marcado, diff-revisadas) + Pint exit 0.
- CI 4 jobs en verde (tests, static-analysis, style, audit).

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rama de trabajo | En rama draft del spec | `git checkout -b spec/laravel13-advisories-fase5-impl` al implementar |
| P2 | Lock 11.57.1 con 5 entradas de audit | `composer audit` reporta 4 únicos + dup | `composer update` tras editar constraints; audit → 0 |
| P3 | Docblocks `@group` en tests | ~30 anotaciones en ~15 archivos (phpunit 12 no las lee) | Migrar a `#[Group]` antes de validar la suite |
| P4 | Include de larastan en PHPStan | `vendor/nunomaduro/larastan/extension.neon` (paquete abandonado) | Rename a `vendor/larastan/larastan/extension.neon` |
| P5 | CSRF stub app-side | `VerifyCsrfToken` (alias deprecated en L13) | Rename a `PreventRequestForgery` + Kernel |
| P6 | Caches de objetos en 3 claves | `plan_data`/`languages`/`Currency::default` cachean modelos/Collection | D2: payload array + rehidratación en lectura |
| P7 | Resolución del lock sobre platform 8.3 | Sin verificar contra 13.x | Confirmar en diff: ignition 2.12.x (no 2.13), collision 8.9.x, Symfony 7.4.x (no 8, exige PHP 8.4) |
| P8 | CI | 3 jobs, sin audit | Añadir 4º job `audit` bloqueante |
| P9 | Runtime PHP 8.3 local | Instalado desde 4.1 | Ninguna (floor L13 = 8.3) |

---

## 4. Estructura propuesta

### Archivos a modificar

```
# D1
composer.json                                  # framework ^13.0, phpunit ^12.0, carbon ^3.0, tinker ^3.0, larastan rename
composer.lock                                  # regenerado: 13.43+, ignition 2.12.x, collision 8.9.x, Symfony 7.4.x
phpstan.neon.dist                              # include vendor/larastan/larastan/extension.neon
phpstan-baseline.neon                         # solo adiciones preexistente recién marcado (política (b))
app/Http/Middleware/VerifyCsrfToken.php        # → PreventRequestForgery.php (extends clase nueva L13)
app/Http/Kernel.php                            # import + alias csrf renombrado
tests/**/*Test.php (~15 archivos)              # docblocks @group → #[Group]
.github/workflows/ci.yml                       # + job audit (composer audit --locked)
# D2
config/cache.php                               # + 'serializable_classes' => false
app/Helpers/Helper.php                        # plan_data()/languages(): store array, rehidratación en lectura
app/Support/Currency.php                       # default(): store array, rehidratación en lectura
tests/Unit/Security/EmailRuleCrlfRejectionTest.php   # guard del advisory high (F2)
tests/Unit/Cache/* + tests/Feature/Cache/*     # guards de payload array + enforcement (F9–F11)
# Docs
AGENTS.md                                      # stack Laravel 13, larastan rename, CI 4 jobs
```

### Cambios por archivo (D2, diseño de rehidratación)

- **`plan_data()`**: el closure de `cache_remember` devuelve `?array` (fila de `PlanSubscribe` + fila de `Plan` embebida + `will_expire`); la lectura rehidrata `PlanSubscribe::hydrate(...)->first()` con `setRelation('plan', Plan::hydrate(...))` y el atributo `will_expire`. Retorno público invariante: `?PlanSubscribe`.
- **`languages()`**: store `Language::...get()->toArray()`; lectura `Language::hydrate($rows)` → Eloquent Collection (mantiene `->where()/->first()` de las vistas).
- **`Currency::default()`**: store array de la fila default; lectura `CurrencyModel::hydrate($rows)->first()`; el fallback sin fila (stdClass con defaults USD) se construye en lectura, no en el store.
- **Consumidores**: cero cambios (`ExpiredMiddleware`, `PropertyService`, `TenantService`, `Api/LaborController`, `SetLocale`, vistas header, `Payment`, helper `currency()`).

---

## 5. Decisiones técnicas

Fijadas, no negociables salvo volver el spec a `Borrador`:

- **Target 13.x, no 12.x:** 12 security-EOL 2027-02-24 (repetiría el slice en ~10 semanas); 13 security hasta 2028-03-17, bug-fixes Q3-2027. Salto único 11→13; el coste extra (CSRF rename, phpunit 12, serialización) es enumerable y está guardado por la suite.
- **Runtime:** PHP 8.3 sin cambio (floor de L13); `phpVersion: 80300` y platform pin intactos. Symfony queda en 7.4.x (8 exige PHP 8.4).
- **Constraints:** estilo major-only del repo (`^13.0`) + lock en el último patch vigente (≥13.43). Dependencies sin cambios salvo las 5 de la guía (framework/phpunit/carbon/tinker/larastan).
- **CSRF:** sweep ahora (rename + `Sec-Fetch-Site`), no cabalgar el alias deprecated (cultura 0-Deprecated).
- **Configs del skeleton NO se sincronizan**; solo keys deliberadas: `cache.serializable_classes = false` (D2). Session queda `php` (ver OUT).
- **D2 = contrato invariante:** el store de cache cambia a arrays, la lectura rehidrata; consumidores y vistas sin cambios. Es la superficie mínima para activar `serializable_classes = false` sin tocar lógica de negocio.
- **Session `serialization` queda `php`** — desviación documentada de la convergencia previa (`json`), por evidencia: `PaymentController:96-97` guarda modelos en sesión. El flip queda como residual con precondición (§8).
- **Baseline PHPStan (política (b)):** adiciones solo para código preexistente recién marcado por larastan 3.13/framework 13, diff-revisadas en el PR; código nuevo/modificado limpio; saneado de entradas stale si procede (precedentes 4.6/5.11). Sin regeneración salvaje.
- **CI:** job `audit` bloqueante (`composer audit --locked`); válvula `audit.ignore` en `composer.json` solo para advisories sin fix disponible, con justificación en el PR.
- **Tests:** sin caracterizaciones (upgrade de infraestructura, como 4.1); guards nuevos: rechazo CRLF en regla `email` (F2), payload array + enforcement (F9–F11).
- **phpunit ^12** con `@group` migrados a `#[Group]`; sin `@test`/`@dataProvider` que migrar (verificado por grep).

---

## 6. Catálogo de casos

Prefijo `F` (feature transversal). **Total: 16 verificaciones (F1–F16).**

### 6.1 Cierre de advisories

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Audit limpio | lock en framework 13.43+ | `composer audit --locked` | exit 0, 0 advisories (las 5 entradas — 4 únicos + dup PKSA — desaparecen) |
| F2 | Regla email rechaza CRLF | framework parcheado (≥13.10) | unit: `Validator::make(['email' => "u@x.com\r\nBcc: e@x.com"], ['email' => 'required\|email'])` | validación falla (guard app-side del advisory high) |

### 6.2 Entrega D1 — upgrade mecánico

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F3 | Composer resuelve sobre platform 8.3 | constraints editados | `composer update` | exit 0; diff del lock: framework 13.43+, phpunit 12.x, carbon 3.x, tinker 3.x, larastan/larastan, ignition 2.12.x, collision 8.9.x, Symfony 7.4.x |
| F4 | App arranca + migraciones | framework 13.x instalado | `php artisan about` y `php artisan migrate:fresh --seed` sobre `dbrentpro_test` | exit 0 ambos; versión 13.x reportada; seeders idempotentes intactos |
| F5 | CSRF renombrado y activo | stub `PreventRequestForgery` + Kernel actualizado | smoke web: POST sin token CSRF; POST con header `Sec-Fetch-Site: cross-site` | 419 sin token; cross-site rechazado; suite auth (L/S/R) en verde |
| F6 | Anotaciones migradas | 0 docblocks `@group` en tests/ | `php artisan test --group=smoke` | filtra y ejecuta > 0 casos (atributos funcionales); suite completa en verde |
| F7 | PHPStan con larastan rename | include path renombrado | `composer analyse` | exit 0, nivel 5; diff de baseline revisado (solo adiciones preexistente) |
| F8 | Paginación bootstrap intacta | `Paginator::useBootstrap()` activo | render de cualquier índice paginado (suite Feature) | sin error de vista; suite en verde |

### 6.3 Entrega D2 — hardening de serialización

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F9 | Payloads array en el store | D2 implementado | unit: llamar `plan_data()`/`languages()`/`Currency::default()` y leer `cache(CacheKeys::...)` | el value almacenado es `array` (no objeto/Collection) |
| F10 | Contrato público invariante | D2 implementado | unit: tipos de retorno | `plan_data(): ?PlanSubscribe` con `->plan` y `->will_expire` accesibles; `languages()` Eloquent Collection con `->where()/->first()`; `Currency::default('code')` escalar; suite Feature/Cache + Language + Payment en verde |
| F11 | Enforcement de `serializable_classes` | `config/cache.php` con `false` | unit: cachear objeto de clase cualquiera y leerlo | no vuelve como instancia (endurecimiento activo, no paper) |
| F12 | Invalidación 4.9 intacta | observers + `forgetModel` sin cambios | suite `CacheObserverTest`, `CachePaymentInvalidationTest`, `LanguageServiceTest` | en verde (staleness cubierto con payload nuevo) |

### 6.4 Guard CI

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F13 | Job audit en CI verde | workflow con 4º job | run del PR | job `audit` success (`composer audit --locked` exit 0) |
| F14 | El job bloquea (sonda) | rama de prueba con advisory conocido en lock | push de la sonda | job `audit` falla; sonda documentada en el PR, no mergeada |

### 6.5 Regresión global

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F15 | Suite completa | D1 + D2 implementados | `php artisan test` sobre L13/phpunit 12 | 100 % en verde (conteo del impl; hoy 496 según plan 5.14) |
| F16 | Installer de safiull sobre 13 | paquete sin test contra L13 | `GET /install` | respuesta no-500 (wizard o guard según marcador `vendor/autoload1.php`) |

### Tests de caracterización

Ninguno: este spec no documenta bugs de aplicación; valida un upgrade de infraestructura con hardening asociado.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `composer audit --locked` → **0 advisories**; salida pegada como evidencia en el PR (§12).
- [x] Diff del lock verificado (P7): framework 13.43+, phpunit 12.5.45, carbon 3.15.1, tinker 3.2.0, `larastan/larastan` 3.15.0, ignition 2.12.1, collision 8.9.6, Symfony 7.4.23 (F3).
- [x] CSRF: stub renombrado a `PreventRequestForgery`, Kernel actualizado, smoke de wiring + evidencia 419/cross-site contra `artisan serve` (F5, desviación D2 de §12).
- [x] 0 docblocks `@group` en `tests/`; `--group=smoke` ejecuta 73 casos (F6).
- [x] Guard F2 (`EmailRuleCrlfRejectionTest`) en verde.
- [x] D2: `'serializable_classes' => false` en `config/cache.php` con test de enforcement (F11); payloads de las 3 claves en array (F9); contratos públicos invariantes (F10); suite de invalidación 4.9 en verde (F12).
- [x] Session `serialization` documentado como `php` + residual registrado con su precondición (§5, §8 y fila nueva en `plan-accion.md`).
- [x] CI en verde con 4 jobs (`tests`, `static-analysis`, `style`, `audit`) — [run 45090339197](https://github.com/gigioeguia/pren-superadmin/actions/runs/45090339197) sobre el head pre-closeout; revalidado sobre el head final del PR (F13); sonda ejecutada: job `audit` **failure** con los 5 PKSA sobre un lock 11.57.1 ([PR draft #67, cerrado sin merge](https://github.com/gigioeguia/pren-superadmin/pull/67)) (F14).
- [x] Suite completa 100 % en verde sobre L13 + phpunit 12: 510 passed / 5.961 assertions (F15).
- [x] `composer analyse` exit 0, nivel 5; diff del baseline revisado en el PR — saneado −1 entrada stale, 0 adiciones (política (b)) (F7).
- [x] `composer lint` exit 0 (Pint).
- [x] `GET /install` sin 500: 404 por guard `canInstall` (F16, desviación D4 de §12).
- [x] `AGENTS.md` actualizado: stack Laravel 13 / PHP 8.3, larastan rename (5.16 cerrado), CI 4 jobs, phpunit 12 `#[Group]`, gotcha `serializable_classes`.
- [x] Residuales registrados en `plan-accion.md`: bump PHP 8.4 (antes del 4T-2027), flip session `json` con precondición, evidencia de safiull sobre 13.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Ignition 2.12.x sin soporte declarado `^13` en su constraint (evidencia indirecta: boilerplates Nov-2026 + tracker) | medio | Si composer no resuelve: remover `spatie/laravel-ignition` del require-dev (página de error cubierta por 3.4) — decisión en impl, documentada como desviación |
| Carbon 2→3 con API diffs en usos directos (`Plan::durationInDays` etc.) | bajo | grep de uso directo + suite como red |
| Rename de vistas de paginación bootstrap en L13 vs `Paginator::useBootstrap()` | bajo | suite cubre índices paginados; fix puntual si rompe |
| phpunit 12 más estricto (deprecations, tipos de datos) | bajo | suite como red; migración `@group` mecánica ya enumerada |
| larastan 3.13 marca código preexistente nuevo | medio | Política (b): adiciones diff-revisadas; código nuevo limpio |
| `egulias/email-validator` ^4 cambia aceptación de emails legítimos raros | bajo | Suite auth en verde; el rechazo de CRLF es el comportamiento deseado |
| Advisory nuevo sin fix durante el ciclo | bajo | Válvula `audit.ignore` con justificación en el PR |
| Sonda F14 requiere un lock con advisory conocido | bajo | Rama desechable, nunca mergeada; evidencia screenshot/log |
| Modelos en sesión (`PaymentController:96-97`) bloquean el flip `json` | medio | Residual con precondición: refactor `session('user')`/`session('plan')` a IDs en un slice propio (dueño: fila nueva del plan) |

**Pendiente de decisión (decisor):**

- **Confirmar la desviación de session `serialization`**: la convergencia previa decía `json` (sin usuarios activos en prod, flips 5.5/5.6 pendientes); la evidencia de `PaymentController:96-97` (modelos en sesión) la hace inviable sin romper pagos. Este spec fija `php` + residual. Veto abierto hasta `Aprobado`.
- Si la resolución de ignition exigiera decisión no contemplada (remover vs bump PHP), volver a `Borrador`.

---

## 9. Estimación

- D1: constraints + lock + verificación de resolución (P2, P7): 0.5 día
- D1: CSRF rename + migración `@group` + include larastan: 0.5 día
- D1: job `audit` + sonda: 0.25 día
- D2: 3 productores de cache (rehidratación) + guards unit: 0.5–1 día
- Validación global (suite, phpstan, pint, audit, smokes, AGENTS.md): 0.5–1 día
- **Total: 2.25–4 días**

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.15 (absorbe 5.16); hallazgo lateral del spec 5.12
- `.agents/spec-impl/spec-devdeps-limpieza-fase5.md` (origen del `composer audit`, 2026-11-21)
- `.agents/spec-impl/spec-upgrade-laravel-php-fase4.md` (precedente de upgrade de framework/estructura; su §12 D6 registró el abandono de larastan que este spec cierra)
- Laravel 13 Upgrade Guide: https://laravel.com/framework/docs/13.x/upgrade
- Laravel support policy (11 security-EOL 2026-03-12; 12 hasta 2027-02-24; 13 hasta 2028-03-17, PHP 8.3–8.5): https://laravel.com/framework/docs/13.x/releases
- GHSA-5vg9-5847-vvmq (CRLF email, high), GHSA-crmm-hgp2-wgrp (signed URL filesystem, medium), GHSA-63hm-g96r-2m2q (task output, low), GHSA-jh5r-qr3c-85q8 (debug page XSS, low) — github.com/laravel/framework/security/advisories
- `.agents/spec/spec-conventions.md`

---

## 11. Revisión

Sin revisión formal intermedia: el decisor aprobó directamente (frontmatter → `Aprobado`, 2026-11-22), confirmando la desviación de session `serialization` (§8 pendiente de decisión) y la implementación.

---

## 12. Notas de implementación (2026-11-22)

Implementado en rama `spec/laravel13-advisories-fase5-impl` (entregas D1/D2 en commits separados, sobre `spec/laravel13-advisories-fase5-draft` + commit de estado `Aprobado`).

### Desviaciones menores

| # | Punto | Detalle |
|---|-------|---------|
| D1 | CSRF sweep ampliado | El spec decía "2 archivos, 0 referencias en tests"; la realidad: +`config/sanctum.php` (`validate_csrf_token` apuntaba a `ValidateCsrfToken`, alias deprecated) + ~12 archivos de tests con 43 referencias `withoutMiddleware(...)`/`ReflectionClass` (el grep original solo cubrió `app/`). Mecánico, misma naturaleza; sweep completo sin aliases deprecados. |
| D2 | F5 reformulado dentro de la suite | L13 by-passea CSRF bajo phpunit (`runningUnitTests()` en `handle()`), así que el "419 sin token" no es observable en la suite. El smoke in-suite aserta wiring (clase en el grupo `web`, sin aliases deprecados), `$except === []` y el slot de sanctum; la evidencia del rechazo real se tomó contra `artisan serve` (curl): sin token → 419; same-origin sin token → pasa (401 de credenciales); cross-site sin token → **419**. Nota de framework: con token **válido** el cross-site pasa — el token es la credencial; la verificación de origen cubre el caso sin token. |
| D3 | Shape-guards defensivos en D2 | El spec no los preveía: los 3 productores recomputan (sin escribir) si el payload cacheado no tiene el shape esperado (`'stale'` de los tests 4.9 o modelos cacheados pre-deploy con formato pre-D2). Protege la rehidratación y hace la transición del deploy auto-curable. |
| D4 | F16: `/install` → 404 | No-500 ✓, pero vía el guard `canInstall` del paquete (`storage/installed` existe → `abort(404)` por config `installedAlreadyAction: default`). Semántica: el wizard carga sus rutas sobre L13 sin error y responde el guard diseñado. |
| D5 | Baseline: saneado, no adiciones | larastan 3.15 sobre L13 no marcó preexistente nuevo; solo quedó 1 entrada **stale** (`Admin/UserController` `BelongsToMany::sync`) que se eliminó. La política (b) no llegó a usarse para adiciones. |
| D6 | `phpunit.xml` | Puntero de schema 10.1 → 12.5 (sin `--migrate-configuration`, para conservar los comentarios de ADR/determinismo). |
| D7 | Ruta sin fila default en `Currency::default()` | Pre-D2 cacheaba el **objeto** fallback cuando no existía fila; post-D2 el fallback se construye en lectura y el store solo cachea filas reales (null nunca cachea → re-query por call en esa ruta marginal, más correcto). |

### Evidencia (DoD §7)

- `composer update --with-all-dependencies` exit 0. Lock: `laravel/framework v13.43.0`, `phpunit 12.5.45`, `carbon 3.15.1`, `tinker 3.2.0`, `larastan/larastan v3.15.0` (nunomaduro fuera), `ignition 2.12.1` (la 2.13 exige PHP 8.4: excluida por el platform pin, el riesgo medio de §8 no se materializó), `collision 8.9.6`, `Symfony http-kernel 7.4.23`, `sanctum 4.5.0`, `permission 6.25.0`, `excel 3.1.70`.
- `composer audit --locked` → **"No security vulnerability advisories found."** (antes: 5 entradas / 4 únicos).
- `php artisan about` → Laravel 13.43.0; `php artisan migrate:fresh --seed` exit 0 (corrida 2× — seeders idempotentes; una corrida temprana tocó la DB dev del `.env` por error mío, regenerada idéntica por los seeders idempotentes, sin pérdida de datos).
- `php artisan test` → **510 passed (5.961 assertions)**: 496 preexistentes + 3 smoke CSRF (F5) + 9 guards D2 (F9 ×3, F10 ×5, F11) + 2 guard F2.
- `php artisan test --group=smoke` → 73 casos (atributos `#[Group]` funcionales; 0 docblocks `@group` vivos).
- `composer analyse` → `[OK] No errors` (nivel 5, baseline −1 entrada stale).
- `composer lint` → `passed`.
- F5 en vivo (`artisan serve` + curl): sin token → **419**; same-origin sin token → 401 (CSRF pasa); cross-site sin token → **419**.
- F16: `GET /install` → **404** (guard `canInstall`, no-500).
- F2: la regla `email` de 13.43 rechaza `\r\n`/`\n`/`\r` y acepta direcciones limpias (tinker, luego test).

### Pendiente post-push

- ~~**F13/F14**: run de CI con los 4 jobs (URL al PR) + sonda del job `audit`~~ → **completados (2026-11-22):**
  - **F13**: PR #66 — CI **4/4 jobs success** sobre el head `ae90a16`: [run 45090339197](https://github.com/gigioeguia/pren-superadmin/actions/runs/45090339197) (`tests` sobre PHP 8.3 real — cierra el riesgo proxy 8.4→8.3, `static-analysis`, `style`, `audit`).
  - **F14**: sonda ejecutada según diseño — PR draft **#67** (base `spec/laravel13-advisories-fase5-impl`, nunca tocó `main`), lock degradado a `11.57.1` con `policy.advisories.block=false` para que el ROJO naciera del step `composer audit --locked`: job `audit` **failure** ([run 45091530059](https://github.com/gigioeguia/pren-superadmin/actions/runs/45091530059/job/137421962982), job 137421962982) con los 5 PKSA; `style` verde en el mismo run (diseño confirmado). Cerrada sin merge + rama borrada; evidencia en comentario del PR. Nota: Composer 2.11 bloqueó por sí mismo el primer intento de downgrade **por los mismos advisories** (`require` falló citando los 5 PKSA) — el guard también opera a nivel de Composer, no solo de CI.
- ~~Flip del spec a `Implementado` al verificar el run del PR (decisor).~~ → pendiente: tras el merge (decisor verifica DoD).
