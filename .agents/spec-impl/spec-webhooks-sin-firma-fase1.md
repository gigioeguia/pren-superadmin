---
id: spec-webhooks-sin-firma-fase1
titulo: "Webhooks sin firma — validación de autenticidad en callbacks de pasarela (Fase 1 / 1.6)"
estado: Borrador
fecha: 2026-11-18
origen: .analisis/plan/plan-accion.md (Fase 1 / 1.6) — Seg-8
cobertura: 12 casos (P11–P22) sobre los 10 endpoints de status de pasarela + exención CSRF `/billplz-callback`
---

# Spec — Webhooks sin firma (Fase 1 / 1.6)

> Resumen: remediar Seg-8 exigiendo autenticidad (firma o verificación server-to-server) en cada callback `status` de pasarela antes de acreditar un pago, y eliminando la exención CSRF huérfana `/billplz-callback`. Los 4 gateways que hoy confían ciegamente en el request (Billplz, Instamojo, Toyyibpay, FedaPay) dejan de aceptar pagos forjados; los 6 que ya verifican por API (Mollie, Paystack, Razorpay, Flutterwave, Thawani, Tap) quedan cubiertos por tests de regresión y endurecidos frente a errores HTTP.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 1 / 1.6) — `Seg-8`.

---

## 1. Objetivo

Cerrar el spoofing de confirmaciones de pago: ningún endpoint `status` de pasarela puede acreditar un pago (redirigir a `payment/success` ni poblar `session('payment_info')` con `status=1`) únicamente porque el request lleve parámetros que dicen "pagado". Cada uno debe verificar autenticidad mediante firma del gateway o consulta server-to-server con las credenciales almacenadas en sesión, y fallar de forma controlada (redirect a `payment/failed`, sin 500) si la verificación no se puede completar.

## 2. Alcance

### IN
- Eliminación de la exención CSRF `/billplz-callback` en `app/Http/Middleware/VerifyCsrfToken.php`.
- `Billplz::status()`: dejar de confiar en `billplz[paid]`; verificar el bill vía API de Billplz con credenciales de sesión.
- `Instamojo::status()`: invocar el `isfraud()` ya existente (hoy no se llama) antes de acreditar.
- `Toyyibpay::status()`: verificar el bill vía API de Toyyibpay antes de acreditar.
- `FedaPayGateway::status()`: verificar la transacción vía API de FedaPay antes de acreditar (y exigir identificador de transacción).
- Endurecimiento de `Mollie`, `Paystack`, `Razorpay`, `Flutterwave`, `Thawani`, `TapPayment`: envolver las llamadas a la API del gateway en `try/catch` y redirigir a `payment/failed` en lugar de propagar 500; mantener la verificación existente.
- Almacenamiento en sesión de credenciales para los gateways que hoy no las guardan (`billplz_credentials`, `fedapay` transaction id) para poder verificar server-to-server.
- Conversión de P11–P15 (caracterización) a tests de bloqueo + P16–P22 de regresión/endurecimiento.

### OUT
- No se cambian los métodos HTTP de los endpoints (siguen `GET`/`POST` según el gateway redirige); la protección es por firma/IP, no por CSRF de método.
- No se añade IP allowlist de gateways (decisión futura, Fase 4).
- No se toca `/payment/success` público ni `session('user_id')` (Seg-6, Fase 2 / 2.3–2.4).
- No se toca `$gateway->namespace` dinámico (Seg-6, Fase 2 / 2.5).
- No se añaden columnas nuevas a `gateways`; se reutilizan las credenciales ya presentes en sesión.
- No se unifican los 11 gateways en un contrato común (fuera de alcance; se preserva la estructura actual).

### Cobertura objetivo
- 12 casos (P11–P22): 1 de CSRF + 4 de caracterización de spoofing + 6 de regresión/endurecimiento + 1 transversal de ausencia de sesión.
- 100 % de los endpoints de `status` de pasarela (10/10) con al menos un caso.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Tests smoke P10 (status endpoints públicos) | Existen en `PaymentFlowSmokeTest` mockeando `status()` | Mantener; no se eliminan |
| P2 | `VerifyCsrfToken` con exención `/billplz-callback` | Existe | Eliminar entrada del array `$except` |
| P3 | `CreatesUsers` y `RefreshDatabase` | Existen | Reutilizar |
| P4 | `GatewayFactory` con `namespace => StubGateway::class` | Existe | Reutilizar para camino feliz |
| P5 | Credenciales en sesión por gateway | Parcial (Mollie, Paystack, Razorpay, Flutterwave, Thawani, Tap, Instamojo, Toyyibpay las guardan; Billplz y FedaPay no) | Añadir `billplz_credentials` y `fedapay` transaction id en `make_payment` |
| P6 | Mocks de HTTP para tests | `Http::fake()` disponible; Mollie/Paystack/Razorpay usan SDK/curl | Refactorizar llamadas HTTP a `Http` facade donde sea viable (ver §5) para permitir `Http::fake()`; mantener SDK donde el proveedor lo exija |
| P7 | `isfraud()` en Instamojo | Existe pero no se invoca | Llamarlo desde `status()` |

## 4. Estructura propuesta

```
app/Http/Middleware/VerifyCsrfToken.php              # MODIFICADO: quitar '/billplz-callback'
app/Library/Billplz.php                            # MODIFICADO: guardar credenciales + verificar en status()
app/Library/Instamojo.php                          # MODIFICADO: invocar isfraud() en status()
app/Library/Toyyibpay.php                          # MODIFICADO: verificar bill vía API en status()
app/Library/FedaPayGateway.php                     # MODIFICADO: verificar transacción vía API en status()
app/Library/Mollie.php                             # MODIFICADO: try/catch alrededor de payments->get
app/Library/Paystack.php                          # MODIFICADO: try/catch alrededor de curl_exec/decode
app/Library/Razorpay.php                          # MODIFICADO: manejar excepción de SignatureVerify
app/Library/Flutterwave.php                       # MODIFICADO: try/catch alrededor de isfraud
app/Library/Thawani.php                           # MODIFICADO: try/catch alrededor de getResponse
app/Library/TapPayment.php                        # MODIFICADO: try/catch ya existe; verificar null body
tests/Feature/Payment/WebhookSignatureSmokeTest.php # NUEVO: P11–P22
tests/README.md                                   # MODIFICADO: tabla de caracterización (Seg-8)
```

## 5. Decisiones técnicas

- **Framework:** Laravel 10, PHPUnit 10, `RefreshDatabase`.
- **DB:** MySQL `dbrentpro_test`.
- **Auth en tests:** `actingAs` para sesión; los endpoints de gateway son `web` (sesión + CSRF).
- **CSRF en tests:** los casos POST incluyen `_token` válido vía `$this->post(...)` (Laravel testing client gestiona CSRF automáticamente para rutas web) salvo P11, que prueba la ausencia de exención.
- **Verificación server-to-server:** cada `status()` debe consultar al gateway con las credenciales almacenadas en sesión (`{gateway}_credentials`) y comparar el identificador/estado reportado por el gateway con el esperado. Si la consulta falla o el estado no es "pagado/capturado/aprobado", redirigir a `redirect_if_payment_faild()` y **no** poblar `payment_info` con `status=1`.
- **Manejo de errores:** toda llamada externa se envuelve en `try/catch (\Throwable)`; en caso de excepción se registra con `Log::error`/`Log::warning`, se redirige a `payment/failed` y no se propaga 500.
- **Billplz:** almacenar `billplz_credentials` (api_key, mode) en `make_payment`; en `status()` llamar `GET {base}/api/v3/bills/{bill_id}` con basic auth y verificar `paid == true`. Si no hay credenciales en sesión → `abort(404)` (como el resto).
- **Instamojo:** `status()` llama a `isfraud($creds)` con `payment_id` del request antes de acreditar; si devuelve `0` → redirect fail.
- **Toyyibpay:** consultar `GET {base}/index.php/api/getBillTransactions?billCode={billcode}` (o equivalente) y verificar `billpaymentStatus == 1`; si no hay credenciales en sesión → `abort(404)`.
- **FedaPay:** exigir `request->id` (transaction id); consultar la transacción vía SDK/API y verificar `status == 'approved'`; si falta el id o la verificación falla → redirect fail.
- **Mollie/Paystack/Razorpay/Flutterwave/Thawani/Tap:** mantener la verificación existente; añadir `try/catch` y no acreditar si la API devuelve error o el identificador no coincide con el almacenado en sesión (Paystack ya compara `amount`; añadir comparación de `reference`/`payment_id` donde falte).
- **HTTP testable:** refactorizar a `Http` facade las llamadas que usen `curl`/`Guzzle` directo (Paystack, Tap, Thawani) para poder usar `Http::fake()`; Mollie (SDK) y Razorpay (SDK) se mockean a nivel de clase en tests.
- **Mocks obligatorios:** `Http::fake()` para gateways HTTP; mocks de clase (`$this->mock(App\Library\...)`) solo para camino feliz de smoke; los casos P12–P21 ejercitan la lógica real de `status()`.
- **No se añade throttle ni rate limit** (Fase 3.7).

## 6. Catálogo de casos

### 6.1 CSRF y exención huérfana (`P`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| P11 | Exención CSRF `/billplz-callback` eliminada | Sin sesión ni token CSRF | `POST /billplz-callback` (sin `_token`) | 419 (CSRF) en lugar de 404 — **caracterización** (hoy exento) |

> Tras el fix, P11 deja de ser `@group characterization`; Seg-8 (exención) queda remediado.

### 6.2 Gateways que confían en el request (spoofing) (`P`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| P12 | Billplz acepta pago forjado | Sin sesión | `GET /payment/billplz?billplz[paid]=1&billplz[id]=forged` | 302 a `payment/success` (o `fund_callback.success_url`) — **caracterización Seg-8** |
| P13 | Instamojo acepta pago forjado | Sesión con `instamojo_credentials` válidas | `GET /payment/instamojo?payment_status=Credit&payment_id=forged` | 302 a `payment/success` y `payment_info.status=1` — **caracterización Seg-8** |
| P14 | Toyyibpay acepta pago forjado | Sesión con `toyyibpay_credentials` válidas | `GET /payment/toyyibpay?status_id=1&billcode=forged` | 302 a `payment/success` y `payment_info.status=1` — **caracterización Seg-8** |
| P15 | FedaPay acepta pago forjado | Sin sesión | `GET /fedapay/status?status=approved` | 302 a `payment/success` — **caracterización Seg-8** |

> Tras el fix, P12–P15 dejan de ser `@group characterization`: el mismo request debe redirigir a `payment/failed` (o `abort(404)` si falta sesión/credenciales) y no poblar `payment_info` con `status=1`.

### 6.3 Gateways con verificación por API (regresión/endurecimiento) (`P`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| P16 | Mollie no acredita pago inexistente | Sesión con `pay_id=forged` y `mollie_credentials` | `GET /payment/mollie` | Redirect a `payment/failed` (sin 500); `payment_info` no con `status=1` |
| P17 | Paystack rechaza monto adulterado | Sesión con `paystack_credentials` (amount=1000) | `POST /payment/paystack` con `ref_id` válido pero monto distinto en la respuesta del gateway | `abort(404)` o redirect fail; no acredita |
| P18 | Razorpay rechaza firma inválida | Sesión con `razorpay_credentials` | `POST /razorpay/status` con `rzp_signature` inválido | Redirect a `payment/failed`; `payment_info.status != 1` |
| P19 | Flutterwave rechaza transacción inválida | Sesión con `flutterwave_credentials` | `GET /payment/flutterwave?transaction_id=forged` | Redirect a `payment/failed` (isfraud=0) |
| P20 | Thawani rechaza invoice inválido | Sesión con `thawani_credentials` (invoice forjado) | `GET /payment/thawani` | Redirect a `payment/failed` (sin 500) |
| P21 | Tap rechaza charge no capturado | Sesión con `secret_key` | `GET /tap-payment/status?tap_id=forged` | Redirect a `payment/failed` (sin 500) |

### 6.4 Ausencia de sesión (`P`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| P22 | Status sin sesión no explota | Sin sesión para el gateway correspondiente | `GET/POST` a cada endpoint de `status` | 404 o 400 (según endpoint) para los 10 gateways; nunca 500 |

### Tests de caracterización

| ID | Bug caracterizado | Fase que lo romperá |
|----|-------------------|---------------------|
| P11 | Seg-8 — exención CSRF `/billplz-callback` huérfana | Fase 1 / 1.6 |
| P12 | Seg-8 — Billplz confía en `billplz[paid]` | Fase 1 / 1.6 |
| P13 | Seg-8 — Instamojo confía en `payment_status` | Fase 1 / 1.6 |
| P14 | Seg-8 — Toyyibpay confía en `status_id` | Fase 1 / 1.6 |
| P15 | Seg-8 — FedaPay confía en `status=approved` | Fase 1 / 1.6 |

## 7. Criterios de aceptación (Definition of Done)

- [ ] `app/Http/Middleware/VerifyCsrfToken.php` no lista `/billplz-callback` en `$except`.
- [ ] `Billplz::status()` consulta la API de Billplz y no acredita con `billplz[paid]=1` forjado.
- [ ] `Instamojo::status()` invoca `isfraud()` antes de acreditar; pago forjado no acredita.
- [ ] `Toyyibpay::status()` verifica el bill vía API; pago forjado no acredita.
- [ ] `FedaPayGateway::status()` verifica la transacción vía API y exige identificador; pago forjado no acredita.
- [ ] Mollie, Paystack, Razorpay, Flutterwave, Thawani y TapPayment no propagan 500 ante error de verificación; redirigen a `payment/failed`.
- [ ] Ningún `status()` acredita (`payment_info.status=1` o redirect a `payment/success`) sin verificación exitosa.
- [ ] P11–P15 convertidos de `@group characterization` a tests de bloqueo tras el fix.
- [ ] P16–P22 en verde.
- [ ] `php artisan test tests/Feature/Payment/WebhookSignatureSmokeTest.php` en verde.
- [ ] P10 (smoke) sigue en verde.
- [ ] `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.
- [ ] `tests/README.md` actualizado: Seg-8 sin casos de caracterización (o listando los remanentes).

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Refactorizar curl/Guzzle a `Http` facade puede cambiar comportamiento (headers, timeouts) | Medio | Mantener parámetros equivalentes; añadir tests de regresión P16–P21 |
| Gateways sin credenciales en sesión (Billplz, FedaPay) requieren cambios en `make_payment` | Medio | Almacenar `billplz_credentials`/transaction id de forma análoga al resto; actualizar vistas si leen estas keys |
| `isfraud()` de Instamojo está sin usar y puede tener bugs (curl sin timeout) | Medio | Revisarla al integrarla; añadir manejo de excepciones |
| APIs de verificación pueden ser lentas → timeouts en tests | Bajo | `Http::fake()` en tests; timeouts razonables en producción |
| Tests de spoofing dependen del formato exacto de params del gateway | Bajo | Documentar el payload mínimo por gateway en el test |
| Eliminar exención CSRF puede romper un callback real no documentado | Bajo | No existe ruta `/billplz-callback` (verificado); si se añade en el futuro, debe verificar firma antes de eximir |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

## 9. Estimación

- CSRF + Billplz/Instamojo/Toyyibpay/FedaPay: 2–3 días.
- Endurecimiento de los 6 gateways restantes: 1–2 días.
- Tests P11–P22 + mocks HTTP: 2 días.
- Refactor a `Http` facade + README + verificación CI: 1 día.
- Total: 6–8 días.

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 1 / 1.6
- `.analisis/seguridad.md` → Seg-8
- `.agents/spec/spec-conventions.md`
- `.agents/spec-impl/spec-tests-smoke-fase0.md` (P10 original)
- `.agents/spec-impl/spec-trait-acnoo-fase0.md`
- `tests/README.md`
- `app/Http/Middleware/VerifyCsrfToken.php`
- `app/Library/{Billplz,Instamojo,Toyyibpay,FedaPayGateway,Mollie,Paystack,Razorpay,Flutterwave,Thawani,TapPayment}.php`
