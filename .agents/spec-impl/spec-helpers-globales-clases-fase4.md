---
id: spec-helpers-globales-clases-fase4
titulo: "Helpers globales → clases (Currency, Payment, Otp) (Fase 4 / 4.6)"
estado: Implementado
fecha: 2026-11-20
origen: .analisis/plan/plan-accion.md (Fase 4 / 4.6) — DT-A6; absorbe la deuda diferida de spec-inyectar-request-fase4 (gateways `request()->expectsJson()`)
cobertura: Extraer la lógica de `currency_format`/`currency`/`default_currency`/`convert_money`/`convert_to_default_amount` a `App\Support\Currency`; `payable` a `App\Support\Payment`; el OTP duplicado `getOtpTimeInSeconds()` a `App\Support\Otp`; eliminar `request()` de `app/Library/`; mantener los helpers globales como delegadores delgados por compatibilidad.
---

# Spec — Helpers globales → clases (Fase 4 / 4.6)

> Resumen: convertir los helpers globales de moneda y el método duplicado de OTP en clases inyectables y testeables, y cerrar la deuda diferida de `request()->expectsJson()` en los gateways, sin cambiar el contrato observable.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 4 / 4.6) — `DT-A6`. Absorbe el ítem diferido de `spec-inyectar-request-fase4` (`app/Library/*`).

---

## 1. Objetivo

Hacer que la lógica de dominio/presentación de moneda (`currency_format`, `currency`, `default_currency`, `convert_money`, `convert_to_default_amount`), el cálculo de monto pagable (`payable`) y el tiempo de visibilidad del OTP (`getOtpTimeInSeconds`) viva en clases del contenedor (`App\Support\Currency`, `App\Support\Payment`, `App\Support\Otp`), inyectables y testeables, en lugar de funciones sueltas en `app/Helpers/Helper.php` y métodos duplicados en controladores. Como parte del mismo spec, eliminar el último uso del helper global `request()` en `app/Library/` pasando `expectsJson()` como parámetro a `make_payment()`.

## 2. Alcance

### IN
- `app/Support/Currency.php` con los métodos `format`, `default`, `currency`, `convert`, `convertToDefault`.
- `app/Support/Payment.php` con el método `payable` (depende de `Currency`).
- `app/Support/Otp.php` con el método `visibilityTimeInSeconds`.
- `app/Helpers/Helper.php`: los helpers `currency_format`, `currency`, `default_currency`, `convert_money`, `convert_to_default_amount`, `payable` pasan a ser delegadores delgados a `app(...)` (sin lógica propia).
- `app/Http/Controllers/Auth/SignupController.php` y `app/Http/Controllers/Api/Auth/RegistrationController.php`: eliminar `getOtpTimeInSeconds()` e inyectar `Otp`.
- Los 11 gateways en `app/Library/`: firma `make_payment(array $data, bool $expectsJson = false)` y sustitución de `request()->expectsJson()` por `$expectsJson`.
- `app/Http/Controllers/PaymentController.php`: pasar `$request->expectsJson()` a `make_payment()`.
- `tests/Stubs/StubGateway.php`: actualizar firma de `make_payment`.
- Tests unitarios de las 3 clases y tests de caracterización/feature.
- Verificación PHPStan nivel 5, Pint y suite completa.

### OUT
- El resto de helpers globales (`notify_users`, `sendNotification`, `plan_data`, `languages`, `demoReset`, `get_option`, `cache_remember`, `expectedItemId`, `getArrow`, `formatted_date`, `formatted_time`) quedan como están (candidatos a specs futuros o Fase 5).
- No se eliminan los helper functions globales ni se migran los ~93 call sites en vistas/controladores; los helpers se conservan como delegadores por compatibilidad.
- No se unifica `Plan::durationInDays()` ni se toca la duplicación del cálculo de duración (Fase 4 / 4.7).
- No se cambia la lógica de cálculo ni las respuestas HTTP (refactor 1:1).
- No se refactoriza `make_payment` a métodos de instancia ni se altera el flujo de pago.

### Cobertura objetivo
- 0 ocurrencias de `request(` en `app/Library/` (verificable por grep).
- 0 ocurrencias de `getOtpTimeInSeconds` en controladores (verificable por grep).
- Lógica de moneda/OTP solo en las clases (verificable por inspección).
- Helpers globales delegan a las clases (verificable por inspección).
- Unit tests de las 3 clases en verde.
- `composer analyse`, `composer lint` y `php artisan test` en verde.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rama de trabajo | En `main`/otra rama | Crear `spec/helpers-globales-clases-fase4-impl` antes de implementar |
| P2 | Suite verde | Verde (201 tests) | Ejecutar `php artisan test` antes de empezar |
| P3 | PHPStan/Pint/CI | Configurados (Fase 0) | Usar como gate |
| P4 | Clases `App\Support` | Existe `ErrorMessage` | Añadir `Currency`, `Payment`, `Otp` en el mismo namespace |
| P5 | Gateways whitelist | Existe `config/gateways.php` (Fase 2.5) | Ninguna; la invocación dinámica se conserva |
| P6 | StubGateway de tests | Existe | Actualizar firma |
| P7 | Modelos `Currency`, `Gateway` | Existen | Ninguna |

## 4. Estructura propuesta

```
app/Support/
├── ErrorMessage.php              # existente
├── Currency.php                  # NUEVO
├── Payment.php                   # NUEVO
└── Otp.php                       # NUEVO

app/Helpers/Helper.php            # MODIFICADO: delegadores delgados
app/Http/Controllers/Auth/SignupController.php               # MODIFICADO
app/Http/Controllers/Api/Auth/RegistrationController.php     # MODIFICADO
app/Http/Controllers/PaymentController.php                   # MODIFICADO
app/Library/*.php (11 gateways)  # MODIFICADOS: firma + $expectsJson
tests/Stubs/StubGateway.php      # MODIFICADO
tests/Unit/Support/
├── CurrencyTest.php              # NUEVO
├── PaymentTest.php               # NUEVO
└── OtpTest.php                   # NUEVO
```

## 5. Decisiones técnicas

- **Namespace:** `App\Support` (junto a `ErrorMessage`), no `App\Services`, porque son utilidades de dominio/presentación inyectables; los Services quedan para entidades CRUD.
- **Inyección:** las clases se resuelven por el contenedor sin registro explícito (constructor sin dependencias excepto `Payment`, que recibe `Currency`). Los helpers delegan con `app(App\Support\Currency::class)` para permitir binding/mocking en tests.
- **Compatibilidad:** los helper functions globales se mantienen con la misma firma y delegan a la clase; no se rompen los call sites existentes (vistas, controladores, gateways).
- **OTP:** se elimina el método duplicado de los controladores; ambos inyectan `Otp` y llaman `visibilityTimeInSeconds()`. La opción `get_option('email-varification')` y el fallback `config('auth.otp_visibility_time')` se encapsulan en la clase.
- **Gateways:** se añade un parámetro `bool $expectsJson = false` a `make_payment()` (valor por defecto para no romper llamadas externas) y se sustituye cada `request()->expectsJson()` por `$expectsJson`. `PaymentController` pasa `$request->expectsJson()`.
- **Whitelist:** la invocación dinámica `$gateway->namespace::make_payment(...)` y la whitelist de Fase 2.5 se conservan sin cambios de seguridad.
- **Tests:** unitarios con PHPUnit; los de `Currency`/`Payment` pueden tocar BD/cache, por lo que se usan las factories/seeders existentes o mocks de modelos según convenga. Los de `Otp` mockean `get_option`/`config` cuando sea posible.
- **Estilo:** código en inglés, comentarios en español solo si aportan valor; Pint preset laravel.

## 6. Catálogo de casos

### 6.1 Clase `Currency` (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | Formato a la izquierda con icono | Moneda con `position=left`, `symbol=$` | `Currency::format(100.5, 'icon')` | `"$ 100.50"` |
| U2 | Formato a la derecha con código | Moneda con `position=right`, `code=EUR` | `Currency::format(100.5, 'code')` | `"100.50 EUR"` |
| U3 | Decimales configurables | Amount `10` | `Currency::format(10, 'icon', 0)` | Sin decimales |
| U4 | Moneda por defecto cacheada | `Currency` default en BD | `Currency::default()` | Devuelve el modelo/objeto default; segunda llamada usa caché |
| U5 | Fallback de moneda default | Sin default en BD | `Currency::default()` | Devuelve el objeto fallback USD |
| U6 | Clave concreta | Moneda default existente | `Currency::default('code')` | Devuelve solo el código |
| U7 | Convertir misma moneda | `currency->code == default` | `Currency::convert(100, $currency)` | `100` |
| U8 | Convertir multiplicando | Monedas distintas, `multiply=true` | `Currency::convert(100, $currency, true)` | `100 * rate` |
| U9 | Convertir dividiendo | Monedas distintas, `multiply=false` | `Currency::convert(100, $currency, false)` | `100 / rate` |
| U10 | Convertir a default | Amount en moneda extranjera | `Currency::convertToDefault(100, $currency)` | `100 * rate` |

### 6.2 Clase `Payment` (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U11 | Payable misma moneda | Gateway en moneda default | `Payment::payable(100, $gateway)` | `100 + charge` |
| U12 | Payable moneda distinta | Gateway en moneda distinta | `Payment::payable(100, $gateway)` | `(convert_to_default(charge) * rate) + charge` |
| U13 | Delegación de helper | Helper global existente | `payable(100, $gateway)` | Igual a `app(Payment::class)->payable(...)` |

### 6.3 Clase `Otp` (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U14 | OTP desactivado | `otp_status=off` | `Otp::visibilityTimeInSeconds()` | `0` |
| U15 | Tiempo vacío usa fallback | `otp_expiration_time=null` | `Otp::visibilityTimeInSeconds()` | `config('auth.otp_visibility_time') ?? 3` en segundos |
| U16 | Duración en minutos | `otp_duration_type=minute`, `time=5` | `Otp::visibilityTimeInSeconds()` | `300` |
| U17 | Duración en segundos | `otp_duration_type=second`, `time=90` | `Otp::visibilityTimeInSeconds()` | `90` |
| U18 | `otp_status` ausente | Settings sin `otp_status` | `Otp::visibilityTimeInSeconds()` | Se trata como `off` → `0` (caracterización del comportamiento actual `?? 'off'`) |

### 6.4 Refactor de controladores (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | SignupController sin método duplicado | Código migrado | Inspección | No existe `getOtpTimeInSeconds` en `SignupController`; usa `Otp` inyectado |
| F2 | RegistrationController sin método duplicado | Código migrado | Inspección | No existe `getOtpTimeInSeconds` en `RegistrationController`; usa `Otp` inyectado |
| F3 | OTP sigue funcionando en signup web | Settings OTP activo | Flujo de signup | Tiempo de visibilidad idéntico al anterior (tests smoke S*) |
| F4 | OTP sigue funcionando en signup API | Settings OTP activo | Flujo de registro API | Tiempo de visibilidad idéntico al anterior (tests A*) |

### 6.5 Eliminación de `request()` en gateways (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F5 | Sin `request(` en `app/Library/` | Gateways migrados | `grep -rn "request(" app/Library/` | 0 resultados |
| F6 | Firma de `make_payment` actualizada | 11 gateways | Inspección | `make_payment(array $data, bool $expectsJson = false)` en los 11 |
| F7 | StubGateway actualizado | Stub | Inspección | Firma compatible (acepta segundo parámetro) |
| F8 | PaymentController pasa `expectsJson` | Controller migrado | Inspección | `$gateway->namespace::make_payment($payment_data, $request->expectsJson())` |
| F9 | Whitelist intacta | Gateway no permitido | Invocación | Sigue devolviendo `403` (P20–P22) |
| F10 | Flujo de pago stub en verde | Tests de pago | `php artisan test tests/Feature/Payment` | En verde (P1–P11) |

### 6.6 Compatibilidad de helpers (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F11 | Helper `currency_format` delega | Vista/controlador | Inspección + test | `currency_format(...)` == `app(Currency::class)->format(...)` |
| F12 | Helper `currency` delega | Vista/controlador | Inspección + test | `currency()` == `app(Currency::class)->currency()` |
| F13 | Helper `default_currency` delega | Vista/controlador | Inspección + test | `default_currency($key)` == `app(Currency::class)->default($key)` |
| F14 | Helper `convert_money` delega | Vista/controlador | Inspección + test | Igualdad |
| F15 | Helper `convert_to_default_amount` delega | Vista/controlador | Inspección + test | Igualdad |
| F16 | Helper `payable` delega | Vista/controlador | Inspección + test | Igualdad |

### 6.7 Calidad (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F17 | PHPStan sin nuevos errores | Código migrado | `composer analyse` | Sin regresiones vs baseline (910) |
| F18 | Pint limpio | Código migrado | `composer lint` | Sin violaciones |
| F19 | Suite completa en verde | Código migrado | `php artisan test` | Todos los tests en verde |

### Tests de caracterización

| ID | Bug caracterizado | Fase que lo romperá |
|----|-------------------|---------------------|
| U18 | `otp_status` ausente se trata como `off` (comportamiento actual `?? 'off'`) | Fase 4 / 4.7 o 5 (normalización) |

## 7. Criterios de aceptación (Definition of Done)

- [x] Clases `Currency`, `Payment`, `Otp` creadas en `app/Support/` con los métodos listados.
- [x] Helpers globales de moneda/pago delegan a las clases (F11–F16).
- [x] `getOtpTimeInSeconds` eliminado de los dos controladores; ambos inyectan `Otp` (F1–F2).
- [x] 0 ocurrencias de `request(` en `app/Library/` (F5).
- [x] Los 11 gateways usan `make_payment(array $data, bool $expectsJson = false)` (F6).
- [x] `PaymentController` pasa `$request->expectsJson()` (F8).
- [x] `StubGateway` actualizado (F7).
- [x] Unit tests `CurrencyTest`, `PaymentTest`, `OtpTest` en verde (U1–U18).
- [x] Tests de pago en verde (F10).
- [x] `composer analyse` sin nuevos errores (F17).
- [x] `composer lint` limpio (F18).
- [x] `php artisan test` completo en verde (F19).

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Cambio de firma de `make_payment` rompe gateways externos/call sites | Bajo | Parámetro con valor por defecto `false`; actualizar `StubGateway` |
| Helpers delegando a `app()` añaden overhead mínimo | Bajo | Aceptable; permite mocking en tests |
| Tests unitarios de `Currency` tocan BD/cache | Medio | Usar factories/seeders existentes y `RefreshDatabase` donde aplique |
| Comportamiento de `request()->expectsJson()` vs `$request->expectsJson()` en gateways | Bajo | Mismo valor; se pasa desde `PaymentController` |
| `otp_status` ausente se trata como `off` (U18) | Bajo | Caracterizado; no se normaliza en este spec |

**Pendiente de decisión:**
- Si `Payment` debe también encapsular la invocación del gateway (`invokeGateway`) o mantener solo `payable`. Este spec propone solo `payable` para minimizar el alcance; la invocación dinámica se conserva en `PaymentController`.
- Si los helpers globales deben eliminarse por completo tras migrar call sites (futuro spec).

## 9. Estimación

- Clases + delegadores + Otp: 1–2 días.
- Refactor de 11 gateways + PaymentController + StubGateway: 1 día.
- Tests unitarios/feature: 1–2 días.
- Verificación PHPStan/Pint/suite: 0.5 día.
- Total: 3.5–5.5 días.

## 10. Referencias

- `.agents/spec/spec-conventions.md`
- `.agents/spec/spec-template.md`
- `.analisis/plan/plan-accion.md` → Fase 4 / 4.6
- `.analisis/deuda-tecnica.md` → DT-A6
- `.agents/spec-impl/spec-inyectar-request-fase4.md` → deuda diferida de gateways
- `app/Helpers/Helper.php`
- `app/Http/Controllers/PaymentController.php`
- `tests/Stubs/StubGateway.php`
- `config/gateways.php`
