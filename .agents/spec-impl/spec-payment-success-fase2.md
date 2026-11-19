---
id: spec-payment-success-fase2
titulo: "Endurecer /payment/success público — Fase 2 / 2.4"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 2 / 2.4) — Seg-6
cobertura: 8 casos (P12–P19) sobre auth, verificación de pago y idempotencia en /payment/success
---

# Spec — Endurecer `/payment/success` público (Fase 2 / 2.4)

> Resumen: remediar la parte de Seg-6 que corresponde a `/payment/success`: exigir autenticación, verificar que el pago fue confirmado por el gateway (vía `session('payment_info')` o verificación server-to-server), añadir idempotencia y verificar Stripe con `{CHECKOUT_SESSION_ID}`. `/payment/failed` y `/order-status` permanecen públicos. El refactor de mover la escritura a los callbacks queda para Fase 4.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 2 / 2.4) — `Seg-6`.

---

## 1. Objetivo

Cerrar el vector por el que un endpoint público procesa pagos a partir de una sesión forjable: `/payment/success` debe exigir usuario autenticado, solo procesar si el gateway confirmó el pago, no duplicar escrituras y verificar Stripe server-side.

## 2. Alcance

### IN
- `Route::get('/payment/success', ...)` con middleware `auth`.
- `PaymentController::success()`:
  - Guard de autenticación (middleware) + coincidencia `auth()->id()` con `session('user')`/`session('user_id')`.
  - Verificación de pago: exigir `session('payment_info.status') === 1` cuando exista; para Stripe verificar `session_id` con la API; para Billplz/FedaPay/Tap exigir que su `status()` haya seteado `payment_info`.
  - Idempotencia: lock de caché + flag `session('payment_processed')` + guards por estado ya pagado.
  - Stripe: `success_url` con `{CHECKOUT_SESSION_ID}` y verificación en `success()`.
- Gateways Billplz/FedaPay/Tap: setear `session('payment_info')` con `status=1` antes de redirigir a success.
- Actualización de `PaymentFlowSmokeTest` (casos P12–P19) y README.

### OUT
- No se mueve la escritura de "mark as paid" a los callbacks (Fase 4 / DT-C4).
- No se toca `$gateway->namespace` dinámico (Fase 2 / 2.5).
- No se añaden columnas nuevas (idempotencia vía caché + estado).
- `/payment/failed` y `/order-status` permanecen públicos.
- No se reescribe la app Flutter; se documenta que debe establecer sesión web antes del pago (follow-up).

### Cobertura objetivo
- 8 casos (P12–P19): auth, verificación, idempotencia, Stripe.
- 0 pagos procesados sin confirmación del gateway.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Middleware `auth` en web | Disponible | Aplicar a `/payment/success` |
| P2 | `Cache::lock` disponible | Laravel 10 | Reutilizar |
| P3 | Stripe SDK instalado | `stripe/stripe-php` | Reutilizar en `success()` |
| P4 | `session('payment_info')` seteado por gateways | Parcial (Mollie/Paystack/Razorpay/Flutterwave/Thawani/Instamojo/Toyyibpay) | Añadir en Billplz/FedaPay/Tap |
| P5 | Smoke test de caracterización existente | `test_payment_success_processes_subscription_from_session` | Convertir a test seguro (quitar `@group characterization`) |

## 4. Estructura propuesta

```
routes/web.php                                    # MODIFICADO: /payment/success con auth
app/Http/Controllers/PaymentController.php        # MODIFICADO: success() endurecido
app/Library/StripeGateway.php                    # MODIFICADO: success_url con {CHECKOUT_SESSION_ID}
app/Library/Billplz.php                          # MODIFICADO: set payment_info
app/Library/FedaPayGateway.php                   # MODIFICADO: set payment_info
app/Library/TapPayment.php                       # MODIFICADO: set payment_info
tests/Feature/Payment/PaymentFlowSmokeTest.php   # MODIFICADO: P12–P19, convertir caracterización
tests/README.md                                  # MODIFICADO
```

## 5. Decisiones técnicas

- **Framework:** Laravel 10, PHPUnit 10, `RefreshDatabase`.
- **DB:** MySQL `dbrentpro_test`.
- **Auth:** `auth` middleware en la ruta; si no autenticado → 302 a `/login`.
- **Identidad:** `auth()->id()` debe coincidir con `session('user')->id` o `session('user_id')`; si no → 403.
- **Verificación de pago:**
  - Si existe `session('payment_info')`: exigir `status == 1` (o `payment_status == 1`).
  - Si la request trae `session_id` (Stripe): verificar con `\Stripe\Checkout\Session::retrieve($session_id)` y exigir `payment_status === 'paid'`.
  - Si no hay `payment_info` ni `session_id` (Billplz/FedaPay/Tap): exigir que el gateway haya seteado `payment_info` (se añade); si no → 403.
- **Idempotencia:**
  - Lock de caché `Cache::lock('payment-success:' . session()->getId(), 10)` durante el procesamiento.
  - Flag `session('payment_processed')`: si está seteado, redirigir a success sin reescribir.
  - Guards por estado: si `SecurityDeposit.status === 'paid'` o `RentPayment.payment_status === 'paid'`, no reescribir (solo limpiar sesión y redirigir).
  - Para suscripciones, el flag de sesión cubre el caso normal; el lock cubre concurrencia.
- **Stripe:** `success_url => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}'`.
- **Respuesta denegada:** 403 (pago no verificado o identidad no coincide) o redirect a `/payment/failed` según el caso; 302 a login si no autenticado.
- **Mocks obligatorios:** `Sanctum::actingAs` no aplica (web); mock de `\Stripe\Checkout\Session` en tests; `Storage::fake()` para uploads.

## 6. Catálogo de casos

Prefijo `P` (pagos). P12–P19.

### 6.1 `/payment/success` endurecido

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| P12 | Sin autenticación → redirect a login | Sesión con plan/user/payment_info | `GET /payment/success` sin auth | 302 a `/login` |
| P13 | Autenticado con pago verificado procesa | Landlord autenticado + sesión completa + `payment_info.status=1` | `GET /payment/success` | 302 a `order.status` success; `PlanSubscribe` creado; sesión limpiada |
| P14 | Autenticado sin `payment_info` → no procesa | Landlord autenticado + sesión completa, sin `payment_info` | `GET /payment/success` | 302 a `order.status` failed (o `/payment/failed`); sin escritura |
| P15 | Autenticado con `payment_info.status=0` → no procesa | Landlord autenticado + `payment_info.status=0` | `GET /payment/success` | 302 failed; sin escritura |
| P16 | Identidad no coincide → 403 | Landlord A autenticado, sesión de Landlord B | `GET /payment/success` | 403 |
| P17 | Idempotencia: segunda llamada no duplica | Tras P13, misma sesión (flag seteado) | `GET /payment/success` de nuevo | 302 success; sin nuevo `PlanSubscribe`/`Transaction` |
| P18 | Depósito ya pagado no se re-procesa | `SecurityDeposit` con `status='paid'` + sesión completa | `GET /payment/success` | 302 success; sin nuevo `Transaction` |
| P19 | Stripe con `session_id` válido procesa | Landlord autenticado + sesión + `?session_id=cs_test` (mock Stripe paid) | `GET /payment/success?session_id=...` | 302 success; procesado |

### Tests de caracterización

| ID | Bug caracterizado | Fase que lo romperá |
|----|-------------------|---------------------|
| (ninguno nuevo) | El caso `test_payment_success_processes_subscription_from_session` deja de ser caracterización tras el fix (se actualiza a P13) | Fase 2 / 2.4 |

## 7. Criterios de aceptación (Definition of Done)

- [x] `Route::get('/payment/success', ...)` lleva middleware `auth`.
- [x] `success()` verifica identidad (`auth()->id()` vs sesión).
- [x] `success()` exige `payment_info.status=1` o verificación Stripe.
- [x] Stripe `success_url` incluye `{CHECKOUT_SESSION_ID}` y se verifica en `success()`.
- [x] Billplz/FedaPay/Tap setean `payment_info` con `status=1` antes de redirigir.
- [x] Idempotencia: lock de caché + flag `payment_processed` + guards por estado pagado.
- [x] P12–P19 en verde.
- [x] El caso de caracterización `test_payment_success_processes_subscription_from_session` actualizado a comportamiento seguro (sin `@group characterization`).
- [x] `php artisan test tests/Feature/Payment/` en verde.
- [x] `composer lint` y `composer analyse` sin nuevas violaciones.
- [x] `tests/README.md` actualizado.

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| App móvil sin sesión web queda bloqueada (auth) | Alto | Documentar follow-up: endpoint para establecer sesión web en el WebView antes del pago |
| Gateways que no setean `payment_info` (Stripe/Billplz/FedaPay/Tap) | Alto | Añadir seteo/verificación en cada uno; tests con mocks |
| Lock de caché depende del driver (file/database) | Medio | Usar driver por defecto de tests (array?); verificar en CI con database |
| Idempotencia por flag de sesión no cubre replay tras logout | Bajo | Guard por estado pagado cubre recursos; flag cubre sesión |
| Mock de Stripe en tests requiere cuidado | Medio | Usar `$this->mock(\Stripe\Checkout\Session::class)` |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

## 9. Estimación

- Ruta + middleware + controller: 1 día.
- Verificación Stripe + gateways payment_info: 1 día.
- Idempotencia + tests P12–P19: 2 días.
- README + CI: 0.5 día.
- Total: 4.5 días.

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 2 / 2.4
- `.analisis/seguridad.md` → Seg-6
- `.agents/spec/spec-conventions.md`
- `.agents/spec-impl/spec-payment-controller-sin-user-id-fase2.md`
- `app/Http/Controllers/PaymentController.php`
- `app/Library/{StripeGateway,Billplz,FedaPayGateway,TapPayment}.php`
- `tests/Feature/Payment/PaymentFlowSmokeTest.php`
