---
id: spec-cache-invalidacion-centralizada-fase4
titulo: "Caché con invalidación centralizada (CacheKeys + Observers)"
estado: Implementado
fecha: 2026-11-20
origen: .analisis/plan/plan-accion.md (Fase 4 / 4.9, DT-M8)
cobertura: 7/7 claves cacheadas con invalidación centralizada; 4/4 gaps de staleness con test; 18/18 forgets manuales eliminados
---

# Spec — Caché con invalidación centralizada (CacheKeys + Observers)

> Resumen de una línea: centralizar la invalidación de caché en `App\Support\CacheKeys::forgetModel()` disparada por observers Eloquent, eliminando los 18 forgets ad-hoc y cerrando los 4 gaps de staleness confirmados.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 4 / 4.9) — remedia `DT-M8` (`.analisis/deuda-tecnica.md:82-83`): *"Caché manual ad-hoc sin invalidación consistente... el invalidación se hace a mano (`Cache::forget(...)`) esparcida por controladores. Olvidos garantizados → datos stale."*

Diseño cerrado en sesión de grilling (Q1–Q16, 2026-11-20); las referencias a decisiones (Qn) remiten a esa sesión.

---

## 1. Objetivo

Que toda mutación de un modelo cacheado invalide su clave desde un punto único (`CacheKeys::forgetModel()`), de forma automática (observers), driver-agnostic y sin flush global, de modo que ningún flujo — Admin, Landlord, Api, pagos ni seeders — pueda dejar datos stale.

---

## 2. Alcance

### IN

- Las **7 claves/patrones cacheados** hoy: `general`, `manage-pages`, `email-varification`, `version`, `languages`, `default_currency`, `plan-data-{user_id}`. (`logo_setting` es lectura fantasma sin writer — queda cubierta por el observer de `Option` sin caso propio.)
- Los **5 modelos fuente**: `Option`, `Language`, `Currency`, `PlanSubscribe`, `User` (los dos últimos mapean a `plan-data-{user_id}` porque la clave embebe la fila de `PlanSubscribe` y `will_expire` de `User`).
- API central `App\Support\CacheKeys` + helpers delgados (`cache_remember()` sin el parámetro `$ttl` muerto; nuevo `forgetModel(Model)` global), siguiendo la convención de la Fase 4.6 (`Currency`/`Payment`/`Otp`).
- Migración mecánica de los 4 call sites de `cache_remember()` a claves vía `CacheKeys`.
- Eliminación de los **18 forgets manuales** (17 `Cache::forget` + 1 `cache()->forget`) en 13 archivos.
- Excepción masiva documentada: `AcnooLanguageController::isDefault` conserva sus 2 statements SQL y añade `forgetModel()` **tras** la `DB::transaction` (Q15a).
- Limpieza: 4 forgets fantasma, `CACHE_LIFETIME=1800` en `.env.example`.
- El forget dentro de la transacción (`PaymentController:367`) desaparece con los observers — restaura la convención 4.8 en ese punto.

### OUT

- Cambiar **qué** se cachea o la política de TTL (Q2): `cache.lifetime` queda como está (1800 s).
- Tags de caché / cambio de driver / Redis (Q1): el driver actual es `file` (sin phpredis/predis); el diseño funciona con cualquier store.
- Caché de Spatie (package-managed, se autoinvalida vía su API), lock `payment-success:*` (no es caché de datos), singleton muerto `app('website')` (0 consumidores), `cache:clear` de `routes/maintenance.php` (legítimo, superadmin-only) (Q9).
- **Semántica `plan_data()` sin filtro de pago** (`PlanSubscribe::where('user_id', ...)->latest()->first()`, `Helper.php:109`): una fila unpaid cuenta como plan vigente **también sin caché** (la consulta fresca es idéntica). Bug/regla de negocio, no invalidación. **Observación candidata a `.analisis/seguridad.md`/ítem aparte del plan** (Q10).
- Guard de `Currency::deleteAll` (masivo, preexistente): borra solo filas no-default que no participan en la clave cacheada — documentado, no se toca.
- Rename `CACHE_DRIVER` → `CACHE_STORE` (funciona; no-acción).

### Cobertura objetivo

- 7/7 claves cacheadas con invalidación centralizada (automática vía observer o excepción masiva documentada).
- 4/4 gaps de staleness confirmados, cada uno con test.
- 18/18 forgets manuales eliminados (verificable por grep).

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Stack Laravel 11.x / PHP 8.3 con suite verde (224 casos) | Existe (Fase 4.1) | Ninguna |
| P2 | Convención Support classes inyectables + helpers delgados delegadores | Existe (Fase 4.6) | Ninguna |
| P3 | Convención "tx envuelve solo escrituras DB" para ubicar el `forgetModel()` de `isDefault` tras el commit | Existe (Fase 4.8) | Ninguna |
| P4 | Store array en tests (`phpunit.xml` fija `CACHE_DRIVER=array`) | Existe | Ninguna |
| P5 | Mutaciones de los 5 modelos vía Eloquent por-fila (eventos disparan) | Verificado en diseño: solo 2 mass-ops tocan modelos cacheados — `isDefault` (Language, ambos statements) y `Currency::deleteAll` (solo filas no-default) | `isDefault`: `forgetModel()` tras tx; `Currency::deleteAll`: sin acción (documentado en §8) |

---

## 4. Estructura propuesta

```
app/
├── Support/
│   └── CacheKeys.php                          # nuevo — dueño único de claves + forgetModel()
├── Observers/
│   ├── OptionObserver.php                     # nuevo
│   ├── LanguageObserver.php                   # nuevo
│   ├── CurrencyObserver.php                   # nuevo
│   ├── PlanSubscribeObserver.php              # nuevo
│   └── UserObserver.php                       # nuevo
├── Models/
│   ├── Option.php                             # + #[ObservedBy(OptionObserver::class)]
│   ├── Language.php                           # + #[ObservedBy]
│   ├── Currency.php                           # + #[ObservedBy]
│   ├── PlanSubscribe.php                      # + #[ObservedBy]
│   └── User.php                               # + #[ObservedBy]
├── Helpers/Helper.php
│   ├── cache_remember(string $key, callable $callback)   # sin $ttl; claves via CacheKeys en los callers
│   ├── get_option() / plan_data() / languages()           # claves via CacheKeys::option()/planData()/languages()
│   └── forgetModel(Model $model): void        # nuevo helper delgado → CacheKeys
└── Http/Controllers/
    ├── Admin/AcnooLanguageController.php      # isDefault: + forgetModel() tras tx; −5 Cache::forget
    ├── Admin/AcnooCurrencyController.php      # −1 cache()->forget
    └── (12 controladores con forgets)          # −17 Cache::forget restantes

.env.example                                    # + CACHE_LIFETIME=1800
tests/Unit/Support/CacheKeysTest.php            # nuevo (U1–U9)
tests/Feature/Cache/…                           # nuevo (F, G, S, P)
```

`app/Support/Currency.php:54` construye su clave via `CacheKeys::defaultCurrency()`.

### 4.1 Inventario de forgets manuales a eliminar (18)

| Controlador | Línea(s) | Clave olvidada |
|---|---|---|
| `Admin/AcnooLanguageController` | 53, 139, 167, 188, 229 | `languages` |
| `Api/Auth/RegistrationController` | 110, 154 | `plan-data-{id}` |
| `Admin/SettingController` | 44 | `general` |
| `Admin/AcnooWebSettingController` | 73 | `manage-pages` |
| `Admin/AcnooSettingsManagerController` | 45 | `email-varification` |
| `Admin/AcnooSubscriptionController` | 95 | `plan-data-{id}` |
| `Admin/AcnooLandlordController` | 142 | `plan-data-{id}` |
| `PaymentController` | 367 | `plan-data-{id}` (**dentro** de la tx — viola 4.8) |
| `Admin/AcnooTermConditionController` | 41 | `term-condition` (fantasma) |
| `Admin/AcnooPrivacyPolicyController` | 40 | `privacy-policy` (fantasma) |
| `Admin/ServiceChargeController` | 33 | `service-charge` (fantasma) |
| `Admin/AcnooReminderMessageController` | 53 | `reminder-messages` (fantasma) |
| `Admin/AcnooCurrencyController` | 98 | `default_currency` (vía `cache()->forget`) |

Las 4 claves fantasma no se cachean (lecturas directas a BD): los forgets se **borran**, sin empezar a cachear esas lecturas (Q8a).

### 4.2 Gaps de staleness a cerrar (4, con test)

| Gap | Path | Síntoma hoy |
|---|---|---|
| G1 | `Auth/SignupController:176-188` crea `PlanSubscribe` free sin invalidar | `ExpiredMiddleware` (cada ruta landlord) ve plan anterior 30 min |
| G2 | `Admin/AcnooCurrencyController::store/update` sin forget | dinero mal formateado/convertido app-wide 30 min |
| G3 | `Admin/AcnooLanguageController::isDefault:193-210` (mass-op, sin eventos) | headers con idioma default viejo 30 min |
| G4 | `PaymentController:126-143` (manual) y `Api/AcnooSecurityDepositController:76` crean `PlanSubscribe` unpaid | `plan_data()` sirve la fila anterior 30 min |

---

## 5. Decisiones técnicas

Fijadas (no negociables salvo volver el spec a `Borrador`):

- **Mecanismo:** invalidación por **claves explícitas** driver-agnostic; sin tags; prohibido `Cache::flush`/flush global en `app/` (Q1, Q4). Claves actuales **intactas** (Q12).
- **Observers:** registrados con atributo `#[ObservedBy]` (Q16a); eventos `saved` + `deleted`, event-agnostic y **sin filtrado `isDirty`** (la sobre-invalidación de `User` es trivial: un re-read) (Q6).
- **API:** `CacheKeys` exige el id donde hay datos por-landlord — `planData(int $userId)` sin valor default — para que el scope multi-tenant sea estructural (Q3, Q11).
- **Excepción masiva (Q15a):** mass-op sobre modelo cacheado ⇒ `forgetModel()` explícito en la misma acción, tras el commit. Hoy solo `isDefault`. Los controladores de modelos cacheados no usan la rama masiva del trait `deleteAll()` (Language la sobreescribe row-by-row; Currency es override propio que excluye la fila default).
- **TTL:** 100 % `config('cache.lifetime')` (1800 s); firma de `cache_remember()` sin `$ttl` (Q13); sin cambio de política (Q2).
- **Framework:** PHPUnit (suite existente), Laravel 11.x / PHP 8.3.
- **DB:** MySQL `dbrentpro_test`, `RefreshDatabase`.
- **Auth en tests:** `actingAs` (admin/landlord según caso).
- **Tests de invalidación:** comportamiento directo sobre el **store array real** (`phpunit.xml` ya fija `CACHE_DRIVER=array`): write → mutar → miss. Sin `Cache::fake` — concreción de Q5: se aserta el efecto observable (la clave deja de servirse), no llamadas al facade. **Sin tests de caracterización**: el comportamiento stale actual es el bug a romper.

---

## 6. Catálogo de casos

### 6.1 Unit — CacheKeys y helper (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | Clave de Option intacta | — | `CacheKeys::option('general')` | devuelve exactamente `'general'` |
| U2 | planData exige id | — | `CacheKeys::planData(7)` / llamada sin id | `'plan-data-7'`; sin id lanza `TypeError` (scope landlord estructural) |
| U3 | Claves fijas intactas | — | `CacheKeys::languages()`, `CacheKeys::defaultCurrency()` | `'languages'`, `'default_currency'` |
| U4 | forgetModel(Option) | `cache(['general' => 'stale'])` | `forgetModel($option)` con `key='general'` | `cache('general')` es null |
| U5 | forgetModel(PlanSubscribe) | `cache(['plan-data-7' => 'stale'])` | `forgetModel($planSubscribe)` con `user_id=7` | `cache('plan-data-7')` null |
| U6 | forgetModel(User) | `cache(['plan-data-7' => 'stale'])` | `forgetModel($user)` con `id=7` | `cache('plan-data-7')` null |
| U7 | forgetModel(Language) | `cache(['languages' => 'stale'])` | `forgetModel($language)` | `cache('languages')` null |
| U8 | forgetModel(Currency) | `cache(['default_currency' => 'stale'])` | `forgetModel($currency)` | `cache('default_currency')` null |
| U9 | cache_remember firma limpia | `config('cache.lifetime')` definido | `cache_remember('x', fn () => 'v')` ×2 | segundo read sirve `'v'` del store; la firma no acepta tercer parámetro TTL |

### 6.2 Observers — invalidación automática (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Option observer | `cache(['general' => 'stale'])` | `Option::create` / `update` / `delete` (Eloquent) | `cache('general')` null tras cada mutación |
| F2 | Language observer | `cache(['languages' => 'stale'])` | `Language::create` / `update` / `delete` | `cache('languages')` null |
| F3 | Currency observer | `cache(['default_currency' => 'stale'])` | `Currency::create` / `$currency->update()` | `cache('default_currency')` null |
| F4 | PlanSubscribe observer (cubre gap G4) | `cache(['plan-data-9' => 'old'])` | `PlanSubscribe::create(['user_id' => 9, ...])` unpaid incluido | `cache('plan-data-9')` null |
| F5 | User observer | `cache(['plan-data-9' => 'old'])` | `$user(id=9)->update([...])` | `cache('plan-data-9')` null |

### 6.3 Gaps admin vía HTTP (`G`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| G1 | currency update refresca dinero | `default_currency` cacheada (rate/symbol viejos), admin | `PUT /admin/currencies/{currency}` con datos nuevos | clave olvidada; `currency_format()` usa datos nuevos de inmediato (gap G2) |
| G2 | currency store default | admin | `POST /admin/currencies` con `is_default=1` | `cache('default_currency')` null (gap G2) |
| G3 | languages isDefault (excepción masiva) | `cache(['languages' => 'stale'])`, admin | `POST /admin/languages/default/{id}` | `cache('languages')` null — `forgetModel()` tras la tx (gap G3) |
| G4 | languages status/destroy/deleteAll | `cache(['languages' => 'stale'])`, admin | `POST /admin/languages/status/{id}`; `DELETE /admin/languages/{language}`; `POST /admin/languages/delete-all` | `cache('languages')` null en las tres acciones |

### 6.4 Gap signup (`S`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| S1 | signup plan free invalida plan-data | flujo de registro web completado (OTP ok) | el flujo crea el `PlanSubscribe` free del landlord nuevo | `cache('plan-data-{user_id}')` null (gap G1) |

### 6.5 Gap rama unpaid de pagos (`P`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| P1 | rama manual crea PlanSubscribe unpaid | `cache(['plan-data-{id}' => 'old'])`, landlord autenticado | flujo de pago gateway manual/offline (`PaymentController`) crea la suscripción | clave null → `plan_data()` refleja la fila nueva (gap G4; el path de `Api/AcnooSecurityDepositController` queda cubierto por F4) |

### Tests de caracterización

Ninguno. Decisión Q5: el comportamiento stale actual es el bug que este spec rompe; nada se preserva con `@group characterization`.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `App\Support\CacheKeys` con `option()/planData()/languages()/defaultCurrency()/forgetModel(Model): void`; `planData(int $userId)` sin valor default.
- [x] `cache_remember(string $key, callable $callback)` sin tercer parámetro; helper global `forgetModel(Model): void` delegando en `CacheKeys`.
- [x] 5 observers en `app/Observers/` registrados vía `#[ObservedBy]` en `Option`, `Language`, `Currency`, `PlanSubscribe`, `User`; eventos `saved` + `deleted`.
- [x] `grep -rn "Cache::forget\|cache()->forget" app/` devuelve resultados **solo en `app/Support/CacheKeys.php`** (punto único, Q11); 18/18 forgets de controladores eliminados (precisión en §11).
- [x] `AcnooLanguageController::isDefault` llama `forgetModel()` tras la `DB::transaction` (única excepción masiva).
- [x] `PaymentController` sin forget dentro de la tx (convención 4.8 restaurada en ese punto).
- [x] 4 claves fantasma (`term-condition`, `privacy-policy`, `service-charge`, `reminder-messages`) sin forgets muertos; lecturas siguen directas a BD.
- [x] `.env.example` documenta `CACHE_LIFETIME=1800`.
- [x] Catálogo completo en verde: U1–U9, F1–F5, G1–G4, S1, P1 (20 casos).
- [x] Suite completa en verde; PHPStan nivel 5 sin nuevas entradas al baseline; `composer lint` limpio.
- [x] Tiempo total de la suite sin degradación (< 10 % de incremento).
- [x] `AGENTS.md` actualizado: sección caché/invalidación en gotchas (regla Q15a: mass-op sobre modelo cacheado ⇒ `forgetModel()` en la misma acción).

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Observers disparan durante seeders/migraciones (invalidaciones no-op) | bajo | Aceptar: coste nulo; los seeders no dependen de caché |
| `User` observer sobre-invalida en cada save (name, etc.) | bajo | Aceptar: un re-read trivial; sin filtrado `isDirty` por simplicidad |
| Futuras mass-ops sobre modelos cacheados sin eventos Eloquent | medio | Regla Q15a documentada (§5) + DoD en `AGENTS.md`; `Currency::deleteAll` documentado como seguro por diseño (excluye la fila default, no participa en la clave) |
| `plan-data-*` embebe `will_expire` de `User`: un futuro campo extra de `User` en `plan_data()` exigiría cubrirlo en el observer | medio | Docblock en `CacheKeys::planData()` + nota en `AGENTS.md` |
| Claves con strings intactas heredan colisiones si alguien reutiliza un string | bajo | `CacheKeys` como único constructor de claves (Q11) reduce la superficie |

**Pendiente de decisión:**

- Ninguna — árbol de diseño cerrado (Q1–Q16, sesión 2026-11-20).
- Fuera de alcance registrado como observación (Q10): semántica `plan_data()` sin filtro de pago — candidata a `.analisis/seguridad.md`/ítem aparte del plan.

---

## 9. Estimación

- API central (CacheKeys + helpers + firma `cache_remember` + `.env.example`): 1–1.5 días
- Observers (5) + excepción `isDefault` + eliminación de 18 forgets: 0.5–1 día
- Catálogo de tests (20 casos) + DoD + `AGENTS.md`: 1.5–2 días
- **Total: 3–4.5 días**

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 4 / 4.9
- `.analisis/deuda-tecnica.md` → DT-M8
- `.agents/spec-impl/spec-env-to-config-fase4.md` → key `cache.lifetime` (F7)
- `.agents/spec-impl/spec-db-transactions-no-external-io-fase4.md` → convención tx (invalidación tras commit)
- `.agents/spec-impl/spec-trait-acnoo-fase0.md` → hooks `acnooDeletesRowByRow()` / rama masiva de `deleteAll()`
- `.agents/spec-impl/spec-helpers-globales-clases-fase4.md` → patrón Support classes + helpers delgados
- `.agents/spec/spec-conventions.md`, `.agents/spec/spec-template.md`
- `AGENTS.md` → helpers globales / gotchas

---

## 11. Notas de implementación (2026-11-20)

DoD verificado con evidencia:

- **Suite:** 244/244 en verde (224 previos + 20 del catálogo). Baseline pre-cambio medido vía stash: 224 tests / 41.29 s → 244 tests / 41.68 s (**+0.9 %** — DoD de duración cumplido).
- **PHPStan nivel 5:** sin nuevas entradas; se **eliminó** la entrada huérfana `if.alwaysTrue` de `AcnooTermConditionController` (baseline 910→909) — el código que cubría (el `if` muerto del forget fantasma de `term-condition`) fue borrado por este spec. Pint limpio.
- **Precisión del DoD grep:** el único `cache()->forget` de `app/` vive en `CacheKeys::forgetModel()` — el punto único diseñado en Q11 debe poder llamar a `forget`; el DoD exige cero usos fuera de esa clase.
- **Deferring after-commit descartado en implementación:** `ShouldHandleEventsAfterCommit` (verificado contra el código del framework: solo aplica a post-eventos como `saved`/`deleted`) rompería la observabilidad bajo `RefreshDatabase` — los callbacks difieren al commit real, que en tests es rollback, y los 20 tests del catálogo no verían la invalidación. Los observers invalidan en el momento del evento Eloquent; la carrera pre-commit que el deferring cerraría existe hoy igualmente (p.ej. el forget inside-tx de `PaymentController:367` pre-cambio) y quedó fuera del alcance fijado en Q2.
- Normalizado typo de estado `Aprovado` → `Aprobado`.
