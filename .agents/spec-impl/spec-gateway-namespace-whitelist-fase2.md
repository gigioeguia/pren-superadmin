---
id: spec-gateway-namespace-whitelist-fase2
titulo: "Whitelist de clases de gateway en `$gateway->namespace::make_payment` — Fase 2 / 2.5"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 2 / 2.5) — Seg-6
cobertura: 3 casos P (P20–P22) + 1 unitario (U1) sobre bloqueo de namespace no whitelisted en la invocación dinámica; regresión de `test_automatic_payment_redirects_to_gateway`
---

# Spec — Whitelist de clases de gateway (Fase 2 / 2.5)

> Resumen: remediar la parte de Seg-6 que corresponde a `$gateway->namespace::make_payment(...)`: la clase invocada dinámicamente desde la BD se valida contra una whitelist explícita de FQCN permitidos. Un `namespace` no whitelisted (o inexistente) bloquea el pago con `403` antes de invocar el método, cerrando el vector de RCE por compromiso de la tabla `gateways`. `/payment/success`, las rutas firmadas y las Policies quedan fuera (Fase 2 / 2.3–2.4 y 2.1).

**Origen:** `.analisis/plan/plan-accion.md` (Fase 2 / 2.5) — `Seg-6`.

---

## 1. Objetivo

Cerrar el vector de ejecución remota de código (RCE) derivado de invocar una clase PHP a partir de un string libre almacenado en la BD: `Gateway->namespace` solo puede apuntar a una de las 11 clases de gateway de `app/Library/`. Cualquier otro valor (incluyendo clases inexistentes o arbitrarias) se rechaza en servidor antes de la invocación.

## 2. Alcance

### IN
- Nuevo archivo de configuración `config/gateways.php` con la key `allowed_namespaces` (lista de FQCN de los 11 gateways de producción).
- Métodos estáticos en `App\Models\Gateway`: `allowedNamespaces(): array` y `isAllowedNamespace(?string $namespace): bool`. El primero fusiona la lista de config con `Tests\Stubs\StubGateway` cuando esa clase existe (acomodo automático para tests, sin acoplar producción a un stub de test en runtime).
- Verificación fail-closed en `PaymentController::payment()`: si `Gateway::isAllowedNamespace($gateway->namespace)` es `false`, `abort(403)` antes de poblar sesión ni invocar `make_payment`.
- Casos de test P20–P22 + U1, y regresión de `test_automatic_payment_redirects_to_gateway`.
- Actualización de `tests/README.md` con los casos P20–P22 y U1.

### OUT
- No se añade interfaz/contrato común a los gateways (Fase 4 / DT-A1).
- No se quita `namespace` de `$fillable` ni se añade mutator al modelo (hardening de mass-assignment → Fase 5 / DT-B3); la verificación en invocación cierra el RCE y se documenta el follow-up.
- No se valida `namespace` en `AcnooGatewayController::update` (mismo motivo; el valor persistido queda bloqueado en invocación).
- No se toca `/payment/success`, las URLs firmadas ni las Policies (Fase 2 / 2.3–2.4 y 2.1).
- No se cachea la whitelist más allá de `config()` (compatible con `config:cache`).
- No se cambian las clases de `app/Library/`.

### Cobertura objetivo
- 3 casos P (P20–P22) + 1 unitario (U1) en verde.
- 0 invocaciones de `make_payment` con una clase fuera de la whitelist.
- Regresión completa de `tests/Feature/Payment/PaymentFlowSmokeTest.php` en verde.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | `config()` disponible y `config:cache`-safe | Disponible | Crear `config/gateways.php` (no usar `env()` directo) |
| P2 | `Gateway` model con `$fillable` incluyendo `namespace` | Existe | Reutilizar; añadir métodos estáticos |
| P3 | `PaymentController::payment()` invoca `$gateway->namespace::make_payment` | Línea 175 | Insertar guard antes de la invocación |
| P4 | Factory `Gateway::factory()` usa `StubGateway::class` | Existe | Mantener; la auto-inclusión de `StubGateway` (si existe) lo cubre |
| P5 | Suite de pagos existente (P1–P19) | En verde | Mantener regresión |
| P6 | PHPStan nivel 5 | Configurado | Sin nuevas violaciones en archivos tocados |

## 4. Estructura propuesta

```
config/gateways.php                                 # NUEVO: whitelist de namespaces
app/Models/Gateway.php                              # MODIFICADO: + allowedNamespaces(), + isAllowedNamespace()
app/Http/Controllers/PaymentController.php          # MODIFICADO: guard en payment()
tests/Feature/Payment/PaymentFlowSmokeTest.php      # MODIFICADO: P20–P22
tests/Unit/GatewayNamespaceWhitelistTest.php        # NUEVO: U1 (o en Feature si se prefiere)
tests/README.md                                     # MODIFICADO
```

## 5. Decisiones técnicas

Fijadas, no negociables salvo volver el spec a `Borrador`:

- **Framework:** Laravel 10, PHPUnit 10, `RefreshDatabase`.
- **DB:** MySQL `dbrentpro_test`.
- **Configuración:** `config/gateways.php` retorna `allowed_namespaces` (array de FQCN). Se lee vía `config('gateways.allowed_namespaces')`, nunca `env()` directo (compatible con `config:cache`).
- **Whitelist de producción:** los 11 gateways de `app/Library/` (`StripeGateway`, `Mollie`, `FedaPayGateway`, `Paystack`, `Razorpay`, `Instamojo`, `Toyyibpay`, `Flutterwave`, `Thawani`, `TapPayment`, `Billplz`).
- **Acomodo de tests:** `allowedNamespaces()` añade `Tests\Stubs\StubGateway::class` solo si `class_exists(\Tests\Stubs\StubGateway::class)` (autoload-dev). En producción la clase no existe, por lo que no se incluye. Referencia por string, sin autoload forzado.
- **Validación:** `isAllowedNamespace(?string $namespace): bool` devuelve `false` si `$namespace` es `null`, si la clase no existe (`class_exists`), o si no está en la whitelist. Comparación estricta (`in_array(..., true)`).
- **Enforcement:** en `PaymentController::payment()`, inmediatamente después de `$gateway = Gateway::findOrFail($gateway_id);` y antes de cualquier lectura de `session('user_id')`/poblado de sesión o escritura en BD: si `! Gateway::isAllowedNamespace($gateway->namespace)` → `abort(403)`. Aplica tanto a gateways manuales como automáticos (fail-closed uniforme).
- **Comportamiento bloqueado:** `403 Forbidden`. Sin mensaje con detalles del gateway. No se invoca `make_payment`, no se pobla `session('plan')`/`session('user')`/`session('gateway_id')`, no se crea `PlanSubscribe`.
- **Mocks obligatorios:** `Storage::fake()` para uploads; `Sanctum::actingAs` no aplica (flujo web).
- **Logging:** opcional pero recomendado: registrar `Log::warning('Blocked gateway namespace', ['gateway_id' => ..., 'namespace' => ...])` antes del `abort`. No bloqueante para el DoD.

## 6. Catálogo de casos

Prefijo `P` (pagos). P20–P22 cubren la invocación bloqueada; U1 cubre la lógica del modelo.

### 6.1 Invocación de `make_payment` (web)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| P20 | Gateway con namespace no whitelisted → bloqueado | `Plan` + landlord + `Gateway` con `namespace = 'App\\Library\\EvilGateway'` (is_manual=0), sesión `payment_type=subscription`, `user_id=landlord` | `POST /payments/{plan}/{gateway}` | `403`; `StubGateway::$lastPaymentData` vacío (no se invoca); sin `PlanSubscribe`; sin `session('plan')`/`session('user')`/`session('gateway_id')` |
| P21 | Gateway whitelisted (StubGateway) mantiene comportamiento | `Plan` + landlord + `Gateway::factory()` (namespace `StubGateway`), sesión completa | `POST /payments/{plan}/{gateway}` | `302` a `https://gateway-stub.example/checkout`; `StubGateway::$lastPaymentData['amount'] == 8`; `session('plan')`, `session('user')`, `session('gateway_id')` poblados (regresión de `test_automatic_payment_redirects_to_gateway`) |
| P22 | Namespace inexistente → bloqueado | `Plan` + landlord + `Gateway` con `namespace = 'App\\Library\\Nope'` (is_manual=0), sesión completa | `POST /payments/{plan}/{gateway}` | `403`; sin invocación ni escritura |

### 6.2 Unitario del modelo

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | `isAllowedNamespace()` discrimina correctamente | — | Llamar `Gateway::isAllowedNamespace(...)` | `true` para los 11 FQCN de `app/Library/` y para `Tests\Stubs\StubGateway` (en el entorno de test); `false` para `null`, `''`, `'App\\Library\\Nope'`, `'App\\Models\\User'` y cualquier string arbitrario |

### Tests de caracterización

Ninguno: este spec no caracteriza un bug a romper en una fase posterior; implementa el remedio directamente (fail-closed). Los casos P20/P22 verifican el comportamiento seguro nuevo.

## 7. Criterios de aceptación (Definition of Done)

- [x] Existe `config/gateways.php` con `allowed_namespaces` listando los 11 gateways de `app/Library/`.
- [x] `Gateway::allowedNamespaces()` devuelve la lista de config y añade `Tests\Stubs\StubGateway` solo si `class_exists()`.
- [x] `Gateway::isAllowedNamespace()` aplica `class_exists` + `in_array(..., true)`.
- [x] `PaymentController::payment()` ejecuta el guard `abort(403)` inmediatamente tras `findOrFail($gateway_id)`, antes de poblar sesión o escribir en BD.
- [x] P20, P21, P22 y U1 en verde.
- [x] `test_automatic_payment_redirects_to_gateway` (P21) y el resto de `PaymentFlowSmokeTest` siguen en verde.
- [x] `php artisan test tests/Feature/Payment/` en verde.
- [x] `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.
- [x] `tests/README.md` actualizado con P20–P22 y U1.
- [x] AGENTS.md (o nota en el spec) documenta que añadir un nuevo gateway requiere actualizar `config/gateways.php` además del archivo y la fila en BD.

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Añadir un gateway nuevo sin actualizar `config/gateways.php` provoca `403` silencioso | Medio | Documentar en AGENTS.md y en el DoD; considerar test de "todos los namespaces de la tabla gateways están whitelisted" (follow-up) |
| `class_exists` sobre un string puede ocultar typos en la whitelist | Bajo | Tests unitarios (U1) + revisión en PR |
| Mass-assignment de `namespace` sigue posible vía admin (aunque bloqueado en invocación) | Medio | Follow-up Fase 5 / DT-B3: quitar `namespace` de `$fillable` y/o mutator; documentado en OUT |
| `StubGateway` auto-incluido acopla el resolver a un nombre de test (string) | Bajo | Solo referencia por string, sin autoload forzado; en prod la clase no existe |
| Un cambio directo en BD (SQL) con un namespace malicioso queda bloqueado en invocación | Bajo (mitigado) | El guard es el punto de control; defense in depth (mutator) diferido |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

## 9. Estimación

- Config + métodos en `Gateway`: 0.25 día.
- Guard en `PaymentController`: 0.25 día.
- Tests P20–P22 + U1 + README: 1 día.
- Verificación CI (lint/analyse/test): 0.25 día.
- Total: 1.5–2 días.

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 2 / 2.5
- `.analisis/seguridad.md` → Seg-6
- `.agents/spec/spec-conventions.md`
- `.agents/skills/secure-coding.md` → punto 12 (`$gateway->namespace::make_payment`)
- `.agents/spec-impl/spec-payment-controller-sin-user-id-fase2.md`
- `.agents/spec-impl/spec-payment-success-fase2.md`
- `app/Http/Controllers/PaymentController.php` (línea 175)
- `app/Models/Gateway.php`
- `database/factories/GatewayFactory.php`
- `tests/Stubs/StubGateway.php`
- `tests/Feature/Payment/PaymentFlowSmokeTest.php`
