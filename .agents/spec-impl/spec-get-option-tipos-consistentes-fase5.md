---
id: spec-get-option-tipos-consistentes-fase5
titulo: "Tipos consistentes en `get_option`: `App\Support\Options`, helper `: array` y guards en vistas (Fase 5 / 5.7)"
estado: Borrador
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.7) — .analisis/seguridad.md (Seg-24, l.141)
cobertura: Cerrar Seg-24 estrechando `get_option` a `: array` (delegador delgado en nueva clase `App\Support\Options` con `array()`/`string()` y default tipado, nunca null), reparando los 38 accesos Blade sin guard (fila ausente/corrupta = 500 hoy), eliminando la key fantasma `logo_setting` de Flutterwave (logo nunca mostrado) y migrando el único consumidor escalar (`version` en sidebar) a `Options::string()`. Caché intacta (claves crudas + `OptionObserver`, 4.9). Catálogo U1–U11 + Pub1 + F1–F4 + G1–G2 (18 casos; suite esperada 283+18=301) + arch-test anti-reincidencia
---

# Spec — Tipos consistentes en `get_option` (Fase 5 / 5.7)

> Resumen de una línea: `get_option` pasa de `mixed` (array | string | `[]`) a un contrato tipado por forma de key — nueva clase `App\Support\Options` con `array()`/`string()` y defaults tipados, helper `: array` delegador, 38 guards en vistas que hoy crashean con fila ausente, y las dos únicas lecturas no-array migradas (sidebar `version`, logo de Flutterwave).

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.7) — `.analisis/seguridad.md` (Seg-24, l.141: *"devuelve `[]` cuando no hay valor, pero otros código lo trata como string/array → bugs latentes"*).

---

## 1. Objetivo

Cerrar Seg-24: el helper más consumido del repo (`get_option`, ~100 call sites) devuelve `mixed` sin contrato — `array` para 4 keys de objeto, `string` para `version` y `[]` si la fila falta o el JSON está corrupto. Este spec tipa el contrato por forma de key (`App\Support\Options`), estrecha el helper global a `: array`, y repara las consecuencias observables del mixed: vistas públicas/invoices en 500 con fila ausente y logo de pasarela nunca mostrado.

---

## 2. Alcance

### IN

- **Nueva clase `App\Support\Options`** (patrón 4.6, §5):
  - `array(string $key, array $default = []): array` — fila ausente/corrupta → `[]`; deriva de tipo (fila escalar leída como array) → default silencioso (Q9).
  - `string(string $key, string $default = ''): string` — regla espejo.
  - Lectura cacheada con **clave cruda** `CacheKeys::option($key)` + `cache_remember` — claves, TTL e invalidación (`OptionObserver` → `CacheKeys::forgetModel()`) intactos (4.9); el acoplamiento de `ApiAuthSmokeTest:143` (`Cache::forget('email-varification')`) sigue válido.
- **Helper `get_option(string $key): array`** (`app/Helpers/Helper.php:40-43`) → delegador delgado a `Options::array($key)`. Misma firma de entrada, retorno estrechado; semántica idéntica para los ~90 usos Blade array.
- **2 migraciones de lecturas no-array**:
  - `resources/views/layouts/admin/partials/sidebar.blade.php:773-775` — único consumidor escalar: `@php $version = \App\Support\Options::string('version'); @endphp` (Q10). `@if($version)` + echo intactos (`''` es falsy → badge oculto sin fila, igual que `[]` hoy).
  - `app/Library/Flutterwave.php:99` — `get_option('general')['logo'] ?? asset('uploads/logo.png')` (Q5): key fantasma `logo_setting` (sin seeder/writer, acceso `->logo` sobre array → **siempre** fallback) eliminada; arregla "logo configurado nunca se muestra".
- **Tidy de redundancias** (Q8): `app/Support/Otp.php:9` pasa a `Options::array('email-varification')` directa — clase a clase, sin función global dentro de una clase Support (Q11) y cae el `?? []` redundante; `Api/Auth/RegistrationController.php:48` cae su `?? []`. Los guards internos (`['otp_status'] ?? 'off'`, etc.) **intactos**.
- **Reparación de 38 accesos sin guard** (Q4, §4 árbol) — `?? ''` en 34 accesos `get_option('general')['app_*']` de 10 vistas y `?? null` en 4 `Route::has($page_data['headings']['header_btn_link'])` de los partials de header. Hoy fila ausente/corrupta → `[]['app_name']` → `ErrorException` → HTTP 500 (documentado en `PublicRoutesSmokeTest:20-25`).
- **Catálogo de tests** (§6): unit `U1–U9`, arch `U10–U11`, feature `Pub1 + F1–F4 + G1–G2`.
- **Documentación**: `AGENTS.md` (helpers: retorno `: array` + `App\Support\Options` en la lista de clases Support), `tests/README.md` (conteos, árbol, mini-sección), `.agents/README.md` (índice).

### OUT

- **Null-checks de `service-charge` vía modelo** (`Api/AcnooTenantApplicationController:163`, `Landlord/AcnooRentInvitationController:143`, `Api/AcnooRentController:113` — `$chargeOption->value['amount']` sin null-check) — misma clase de bug pero **no** pasa por `get_option` (Q6). Deuda viva registrada aquí; sin DoD en este spec.
- **Migración de datos de `version`** (Q3): queda escalar (`'2.1"'` JSON); writer de `routes/maintenance.php:22-25`, seeder y `ArtisanRoutesSmokeTest:78-81` (assert crudo `'"2.1"'`) intocados.
- **`options.key` sin unique / `reminder-messages` multi-fila** (Q7): sin migración ni índice (rompería el diseño multi-fila). Restricción documentada en §5: `get_option`/`Options::array` solo válidos para keys de fila única; `reminder-messages` se consume vía modelo.
- **Vistas ya guardadas** (`web/index`, footers, masters, `refund-request/view`): intocadas.
- **Controladores (~13, `manage-pages`/`email-varification`)**: sin migrar — el helper ya tipado les sirve (Q11a).
- **Readers vía modelo** (`SettingController`, `AcnooWebSettingController`, etc.): intocados.

### Cobertura objetivo

- `U1–U11`, `Pub1`, `F1–F4`, `G1–G2` en verde (18 casos).
- 0 accesos `get_option(...)[...]` sin `??` en `resources/views/**` (U10); 0 referencias a `logo_setting` en `app/` + `resources/` (U11).
- Vistas de scope renderizan **200 / sin excepción con la tabla `options` vacía**.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Patrón clase Support + helper delegador (4.6) | Existe — `Currency`/`Payment`/`Otp`, métodos estáticos | Seguirlo en `Options` |
| P2 | Caché centralizado por clave cruda (4.9) | Existe — `CacheKeys::option()` + `OptionObserver` | Ninguna: claves/TTL/invalidación sin cambio |
| P3 | Inventario completo de call sites (~14 en `app/`, ~100 Blade, 5 keys) | Hecho — exploración 2026-11-21, verificado con grep (34+4 sin guard confirmados) | Ninguna; base del catálogo y del arch-test |
| P4 | Consumidores no-array identificados | Hecho — sidebar `version` (Blade escalar) y `Flutterwave` (`->logo` sobre array) | Migrar ambos (§2) para que el helper estrechado no deje mixed |
| P5 | Suite base en verde (283 casos según `AGENTS.md`/`tests/README.md`) | Por verificar al cerrar el PR | Verificar 283+18=301 contra salida real (lección 5.3) |
| P6 | `OtpTest` U14–U18 y `CacheObserverTest` vigentes | Existen — usan `value => []` y arrays | Deben seguir en verde sin cambios (regresión del read-path refactorizado) |

---

## 4. Estructura propuesta

```
app/Support/Options.php                                        # NUEVO: array()/string() estáticos + read() privado (cache_remember + CacheKeys::option)
app/Helpers/Helper.php                                         # MODIFICADO: l.40-43 get_option(): array → delega en Options::array($key)
app/Support/Otp.php                                           # MODIFICADO: l.9 Options::array('email-varification') (cae `?? []`)
app/Http/Controllers/Api/Auth/RegistrationController.php      # MODIFICADO: l.48 cae `?? []` redundante
app/Library/Flutterwave.php                                    # MODIFICADO: l.99 get_option('general')['logo'] ?? asset(...)

resources/views/ (12 archivos, 38 accesos + `??`)
  api-invoice/security-deposit.blade.php                       # l.37,39,41,42
  api-invoice/rent-payment.blade.php                           # l.32,34,36,37
  api-invoice/withdraw.blade.php                               # l.20,21,22,23
  landlord/rent-payments/view.blade.php                        # l.52,54,56,57
  landlord/withdraw-request/invoice.blade.php                  # l.45,47,49,50
  landlord/security-deposits/view.blade.php                    # l.65,67,69,70
  admin/subscriptions/invoice.blade.php                       # l.43,45,47,48
  admin/reports/withdrawals/invoice.blade.php                 # l.45,47,49,50
  admin/properties/property-success.blade.php                 # l.13
  landlord/properties/property-success.blade.php              # l.14
  layouts/web/partials/header.blade.php                        # l.12,97 — Route::has(... ?? null)
  layouts/web/partials/common_header.blade.php                # l.53,98 — idem

resources/views/layouts/admin/partials/sidebar.blade.php      # MODIFICADO: l.773-775 \App\Support\Options::string('version')

tests/Unit/Support/OptionsTest.php                            # NUEVO: U1–U9
tests/Unit/Views/UnguardedOptionAccessTest.php                # NUEVO: U10–U11 (arch, carpeta Views/)
tests/Feature/Options/OptionlessViewsSmokeTest.php            # NUEVO: Pub1, F1–F4 (carpeta Options/)
tests/Feature/Options/VersionSidebarSmokeTest.php             # NUEVO: G1–G2

tests/README.md                                                # MODIFICADO: conteos → 301, árbol (Unit/Views, Feature/Options), mini-sección
AGENTS.md                                                      # MODIFICADO: helpers — get_option(): array, lista Support += Options
.agents/README.md                                              # MODIFICADO: índice — 51 → 52, fase5 (6 → 7)

.analisis/ (gitignored — edición local, sin commit)
  seguridad.md                                                 # #24 → cerrado
  plan/plan-accion.md                                          # fila 5.7, Métricas de éxito
```

Sin cambios en `routes/`, `config/`, `database/`, `app/Models/Option.php` (cast `json` intacto).

---

## 5. Decisiones técnicas

Fijadas por grilling Q1–Q13 (2026-11-21), no negociables salvo volver el spec a `Borrador`:

- **Q1(e) — Helper estrechado + clase:** `get_option(string $key): array` delegando en `App\Support\Options::array($key)`. Los ~90 usos Blade ya tratan el retorno como array — estrechar es la lectura literal de "tipos consistentes **en** `get_option`". `Options` es estática (precedente `Otp::visibilityTimeInSeconds()`, `Payment::payable()`).
- **Q2 — Default tipado, jamás null:** `array(default [])` / `string(default '')`. Fila ausente, `value` null o JSON corrupto → default. Sin `null` ni throws en el read-path: la página pública nunca crashea por una option.
- **Q3 — `version` queda escalar:** leída por `Options::string('version')` (Q10: FQCN en `@php`, única llamadora). Sin churn de datos/seeder/maintenance.
- **Q9 — Deriva de tipo silenciosa:** `is_array` / `is_string` guard en cada método; misuse (p. ej. `get_option('version')`) devuelve el default sin excepción. Coherente con Q2; el catálogo (U6/U7) documenta la semántica.
- **Q11(a) — Regla de migración:** controladores siguen con el helper (ya tipado); clases Support entre sí (`Otp` → `Options::array`). Migración total a `Options::` descartada (churn sin beneficio de tipos).
- **Caché sin cambio:** misma clave cruda (`CacheKeys::option`), mismo TTL (`cache_remember` → `config('cache.lifetime')`), misma invalidación (`OptionObserver`). El refactor es del read-path, no del cache-path.
- **Restricción multi-fila (Q7):** `get_option`/`Options::array` solo para keys de fila única; `reminder-messages` (N filas legítimas, `options.key` sin unique) se consume vía modelo. No se añade índice unique.
- **Deuda fuera de scope (Q6):** null-checks de `service-charge` vía modelo quedan vivos; registrar en este spec como nota, sin DoD.
- **Framework:** PHPUnit (`^10.1`, Laravel 11); unit tests sin HTTP (`RefreshDatabase` o `DatabaseTransactions` según `testing-conventions`); feature con `actingAs` para G; AAA comentado; `@group smoke`.
- **Un solo PR (precedente 5.3/5.4):** rama `spec/get-option-tipos-consistentes-fase5-impl`; el spec nace `Borrador`, el grilling Q1–Q13 hace de revisión → `Aprobado` → implementación → DoD tachado + `estado: Implementado` **antes** de pedir merge. Nada se mergea en Borrador.

---

## 6. Catálogo de casos

### 6.1 Unit — `App\Support\Options` + helper (`U`) — `tests/Unit/Support/OptionsTest.php`

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | helper, fila ausente → `[]` | tabla `options` vacía, caché limpia | `get_option('general')` | devuelve `[]` con tipo `array` |
| U2 | helper, fila array → array exacto | `Option::create(['key' => 'general', 'value' => ['app_name' => 'X']])` | `get_option('general')` | devuelve `['app_name' => 'X']` |
| U3 | `string`, fila ausente → `''` | tabla vacía | `Options::string('version')` | devuelve `''` con tipo `string` |
| U4 | `string`, fila escalar → string | `Option::create(['key' => 'version', 'value' => '2.1'])` | `Options::string('version')` | devuelve `'2.1'` |
| U5 | JSON corrupto → default tipado | fila `general` insertada raw con `'{bad'` (`DB::table`, sin cast) | `Options::array('general')` y `Options::string('version')` sobre lo mismo | `[]` y `''` respectivamente |
| U6 | deriva escalar→array silenciosa | fila `version` escalar (U4) | `Options::array('version')` | devuelve `[]` (default, sin excepción) |
| U7 | deriva array→string silenciosa | fila `general` array (U2) | `Options::string('general')` | devuelve `''` (default, sin excepción) |
| U8 | default explícito respetado | tabla vacía | `Options::array('general', ['app_name' => 'D'])` + `Options::string('version', '0.0')` | devuelve el default dado en ambos |
| U9 | invalidación viva vía helper | fila `general` creada y `get_option('general')` leída (caché poblada) | `Option::where('key','general')->update(['value' => ['app_name' => 'Y']])` + `get_option('general')` | devuelve `['app_name' => 'Y']` (observer → `CacheKeys::forgetModel`) |

### 6.2 Arch anti-reincidencia (`U`) — `tests/Unit/Views/UnguardedOptionAccessTest.php`

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U10 | 0 accesos directos sin guard | `resources/views/**/*.blade.php` | scan por línea: matchea `get_option\(...\)\[` sin `??` en la línea | 0 matches (limitación documentada: accesos multilínea fuera del regex; los 38 conocidos son single-line) |
| U11 | 0 referencias a `logo_setting` | `app/` + `resources/` | grep `logo_setting` | 0 ocurrencias (key fantasma eliminada, Q5) |

### 6.3 Render sin options (`Pub`, `F`) — `tests/Feature/Options/OptionlessViewsSmokeTest.php`

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| Pub1 | página pública sin options | tabla `options` vacía | `GET /privacy-policy` | **200** sin `ErrorException` — antes: 500 por `Route::has($page_data['headings']['header_btn_link'])` sin guard (`header:12`, `common_header:53`) |
| F1 | invoice API renderiza sin options | tabla vacía | render directo `view('api-invoice.rent-payment', [...])` con payload mínimo | sin excepción; `app_name` vacío, logo fallback `backend/assets/img/logo.svg` |
| F2 | invoice landlord idem | tabla vacía | render directo `view('landlord.rent-payments.view', [...])` | idem |
| F3 | invoice admin idem | tabla vacía | render directo `view('admin.subscriptions.invoice', [...])` | idem |
| F4 | property-success idem (×2) | tabla vacía | render directo `admin/properties/property-success` + `landlord/properties/property-success` | sin excepción; `app_name` vacío |

### 6.4 Version en sidebar (`G`) — `tests/Feature/Options/VersionSidebarSmokeTest.php`

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| G1 | versión visible | admin autenticado (`actingAs`); fila `version` (`value => '2.1'`) | `GET /admin` | 200 + `Version : 2.1` visible |
| G2 | versión ausente no crashea | admin autenticado; tabla `options` vacía | `GET /admin` | 200 + `Version :` ausente del cuerpo |

### Tests de caracterización

Ninguno: no hay bug vigente que se espere romper — el fix (tipos + guards + logo) **es** este spec. El 500 con options ausentes queda convertido en anti-regresión (Pub1, F1–F4).

---

## 7. Criterios de aceptación (Definition of Done)

- [ ] `app/Support/Options.php` creado: `array()`/`string()` estáticos con default tipado + deriva silenciosa (Q2/Q9); `read()` privado vía `cache_remember(CacheKeys::option($key), ...)`; sin `env()`, sin `request()`.
- [ ] `get_option(string $key): array` delegando en `Options::array($key)` — misma caché, claves e invalidación; 0 llamadores mixed restantes.
- [ ] `Otp.php:9` vía `Options::array` sin `?? []`; `RegistrationController.php:48` sin `?? []`; guards internos intactos.
- [ ] `Flutterwave.php:99` lee `general.logo` con fallback al asset; 0 referencias a `logo_setting` en `app/`+`resources/` (U11).
- [ ] Sidebar l.773-775 vía `\App\Support\Options::string('version')`; `@if` + echo intactos (G1–G2 en verde).
- [ ] 38 accesos con guard: 34 `?? ''` en las 10 vistas de §4 + 4 `?? null` en `Route::has(...)` de `header`/`common_header`; U10 en verde.
- [ ] Catálogo completo en verde: U1–U11, Pub1, F1–F4, G1–G2 (18 casos) — suite **esperada 301** (283+18), verificada contra salida real de `php artisan test` (lección 5.3).
- [ ] `composer analyse` sin nuevas entradas vs baseline; `composer lint` limpio.
- [ ] Docs: `tests/README.md` (conteo 301, árbol con `Unit/Views/` y `Feature/Options/`, mini-sección), `AGENTS.md` (helpers: `get_option(): array` + `App\Support\Options` en clases Support), `.agents/README.md` (índice 51 → 52, fase5 6 → 7).
- [ ] Cierres locales gitignored: `seguridad.md` #24 cerrado; `plan-accion.md` fila 5.7 + Métricas de éxito (redacciones en §Notas).
- [ ] Rama `spec/get-option-tipos-consistentes-fase5-impl` + PR (squash, 1 aprobación + CI); sin push a `main`; nada mergeado en Borrador.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Deriva silenciosa (Q9) puede enmascarar un futuro misuse array/escalar | Medio | Semántica fijada por U6/U7; defaults explícitos disponibles; arch-test + revisión |
| Arch-regex por línea: accesos multilínea no detectados | Bajo | Los 38 conocidos son single-line; Pub1/F1–F4 cubren los partials; limitación documentada en U10 |
| `Route::has(null)` tras el guard `?? null` | Bajo | Verificado: `getByName(null)` → array access con key null → `null` (sin warning); Pub1 lo ejercita |
| Render directo de invoices exige payload/layout context (auth, `$payment`) | Medio | Payload mínimo por vista; si un layout exige auth-view, sustituir por HTTP con `actingAs` y documentar desviación |
| Conteos desincronizados si se mergean otros tests antes del PR | Bajo | Verificar 301 contra salida real al cerrar (lección 5.3) |
| BC del helper estrechado: consumidor externo mixed no inventariado | Bajo | Inventario 2026-11-21 verificado por grep: tras migrar sidebar/Flutterwave quedan 0; sin plugins en el repo |

**Deuda viva registrada (Q6, sin DoD):** `Api/AcnooTenantApplicationController:163`, `Landlord/AcnooRentInvitationController:143`, `Api/AcnooRentController:113` — `Option::where('key','service-charge')->first()` accedido sin null-check; crash si la fila falta. Tratar en ítem de seguimiento, no en este spec.

**Pendiente de decisión:** ninguno (Q1–Q13 del grilling 2026-11-21).

---

## 9. Estimación

- Clase `Options` + helper + call sites (`Otp`, `RegistrationController`, `Flutterwave`, sidebar): 0.25 día.
- Guards de vistas (38 accesos, 12 archivos): 0.25 día.
- Tests (unit + arch + feature + corrida focal): 0.5 día.
- Docs + conteos + cierres documentales: 0.25 día.
- Total: ~1.25 días.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.7 (l.112)
- `.analisis/seguridad.md` → Seg-24 (l.141)
- `app/Helpers/Helper.php` → l.40-43 (`get_option` actual, `?? []`)
- `app/Models/Option.php` → `$casts['value' => 'json']`; `Eloquent\Builder::value()` hidrata → cast aplica (array|string|null)
- `app/Support/CacheKeys.php` → `option()` (clave cruda), `forgetModel()`; `app/Observers/OptionObserver.php` → invalidación (4.9)
- `app/Support/Otp.php:9` · `app/Http/Controllers/Api/Auth/RegistrationController.php:48` · `app/Library/Flutterwave.php:99` · `resources/views/layouts/admin/partials/sidebar.blade.php:773-779`
- `tests/Feature/Public/PublicRoutesSmokeTest.php:20-25` → documentación del 500 con options ausentes
- `.agents/spec-impl/spec-helpers-globales-clases-fase4.md` → patrón clase Support + helper delegador (4.6)
- `.agents/spec-impl/spec-cache-invalidacion-centralizada-fase4.md` → claves crudas + observer (4.9); `logo_setting` ya anotada como fantasma
- `.agents/spec-impl/spec-trust-hosts-fase5.md` → precedente de flujo (Borrador → revisión → Implementado, un solo PR) y lección de conteos
- `.agents/spec/spec-conventions.md` · `.agents/spec/spec-template.md`
- `.agents/skills/testing-conventions.md` · `.agents/skills/workflow-ramas.md` · `.agents/skills/crear-spec.md`
- `AGENTS.md` → "Global helpers" / "Caché con invalidación centralizada"

---

### Notas de implementación

1. **Clase `App\Support\Options`** (sketch):
   ```php
   <?php

   namespace App\Support;

   use App\Models\Option;
   use App\Support\CacheKeys;

   class Options
   {
       public static function array(string $key, array $default = []): array
       {
           $value = self::read($key);

           return is_array($value) ? $value : $default;
       }

       public static function string(string $key, string $default = ''): string
       {
           $value = self::read($key);

           return is_string($value) ? $value : $default;
       }

       /** Fila ausente, value null o JSON corrupto → null (el default tipado decide). */
       private static function read(string $key): mixed
       {
           return cache_remember(CacheKeys::option($key), fn () => Option::where('key', $key)->value('value'));
       }
   }
   ```
   `cache_remember` como única fuente del TTL (4.9) — sin duplicar `config('cache.lifetime')`.
2. **Helper** (`app/Helpers/Helper.php`):
   ```php
   function get_option(string $key): array
   {
       return App\Support\Options::array($key);
   }
   ```
3. **Call sites:**
   - `Otp.php:9` → `$otpSettings = Options::array('email-varification');` (mismo namespace, sin FQCN).
   - `RegistrationController.php:48` → `$otpSettings = get_option('email-varification');`.
   - `Flutterwave.php:99` → `$logo = get_option('general')['logo'] ?? asset('uploads/logo.png');`.
   - Sidebar → `@php $version = \App\Support\Options::string('version'); @endphp`.
4. **Guards:** `{{ get_option('general')['app_name'] ?? '' }}` (idem `app_address`/`app_phone`/`app_email`); `Route::has($page_data['headings']['header_btn_link'] ?? null)` en `header:12,97` y `common_header:53,98`.
5. **Redacciones de cierre (patrón 5.3/5.4):**
   - `seguridad.md` #24 → añadir al final: "— **cerrado (2026-11-XX, spec `spec-get-option-tipos-consistentes-fase5`)**: `get_option(): array` delegando en `App\Support\Options` (`array`/`string`, default tipado, nunca null); 38 guards en vistas (sin options → render, no 500); `logo_setting` fantasma eliminada (logo vía `general.logo`); `version` escalar vía `Options::string`."
   - `plan-accion.md` fila 5.7 → "**Cerrado (2026-11-XX):** spec `spec-get-option-tipos-consistentes-fase5` — helper `: array` + `App\Support\Options` con defaults tipados; 38 guards (Pub1, F1–F4 anti-regresión); arch-test U10–U11; `logo_setting` fuera; deuda viva: null-checks `service-charge` vía modelo."
   - `plan-accion.md` Métricas de éxito → línea nueva: "✅ `get_option` con tipos consistentes (`: array` + `App\Support\Options::string` para escalares) y vistas sin guard reparadas (Fase 5 / 5.7 — spec-get-option-tipos-consistentes-fase5, 2026-11-XX)".
   - `AGENTS.md` "Global helpers" → ajustar: `get_option()` (devuelve `array`; keys escalares vía `App\Support\Options::string()`), y añadir `Options` a la lista de clases Support de 4.6.
   - `tests/README.md` → conteo 301 + mini-sección "## Opciones tipadas (Fase 5 / 5.7)".
   - `.agents/README.md` → índice: 51 → 52, fase5 (6 → 7) con `spec-get-option-tipos-consistentes-fase5`.
6. **Verificación rápida:**
   ```bash
   php artisan test tests/Unit/Support/OptionsTest.php tests/Unit/Views/UnguardedOptionAccessTest.php
   php artisan test tests/Feature/Options
   composer analyse && composer lint && php artisan test
   ```
