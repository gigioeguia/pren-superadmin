---
id: spec-payment-controller-sin-user-id-fase2
titulo: "PaymentController sin user_id por URL (firmar URLs de pago) — Fase 2 / 2.3"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 2 / 2.3) — Seg-6
cobertura: 11 casos (P1–P11) sobre firma de URLs de pago, derivación de identidad y endpoint API de URLs firmadas
---

# Spec — PaymentController sin user_id por URL (Fase 2 / 2.3)

> Resumen: remediar Seg-6 eliminando el `user_id` no firmado de las URLs de pago. Los depósitos/rentas derivan el pagador del recurso; las suscripciones conservan `{landlord_id}` pero solo aceptado bajo URL firmada (`signed`). Se añade un endpoint API autenticado para que la app móvil obtenga URLs firmadas. `/payment/success` y el namespace dinámico de gateways quedan fuera (Fase 2 / 2.4–2.5).

**Origen:** `.analisis/plan/plan-accion.md` (Fase 2 / 2.3) — `Seg-6`.

---

## 1. Objetivo

Cerrar el vector Seg-6: ningún cliente puede suplantar al pagador manipulando `landlord_id`/`tenant_id` en la URL de pago. La identidad del pagador se resuelve en servidor (auth, recurso o URL firmada) y las URLs de pago exigen firma válida.

## 2. Alcance

### IN
- Rutas web de pago (`payments-gateways`, `online-payments-gateways`) protegidas con middleware `signed`.
- `PaymentController::DepositRentPayment`: elimina parámetro `{tenant_id}`; deriva `tenant_id` de `SecurityDeposit`/`RentPayment`.
- `PaymentController::index`: conserva `{landlord_id}` pero valida firma + coincidencia con `auth()->id()` cuando hay sesión web.
- `PaymentController::payment`: sin cambios de firma, pero `session('user_id')` ya no proviene de input libre.
- Nuevo endpoint API autenticado (Sanctum) que devuelve la URL firmada de pago para móvil.
- Actualización de vistas (`landlord/subscription/index.blade.php`) y `SignupController` para generar URLs firmadas.
- Actualización de `PaymentFlowSmokeTest` a URLs firmadas + casos P1–P11.

### OUT
- No se toca `/payment/success` público (Fase 2 / 2.4).
- No se toca `$gateway->namespace::make_payment` dinámico ni whitelist (Fase 2 / 2.5).
- No se reescribe la app Flutter; se documenta el cambio requerido (consumir el nuevo endpoint). La app queda rota hasta actualizarse (deprecación inmediata).
- No se unifican controladores ni se añaden transacciones (DT-M5, fuera de alcance).
- No se cambia el formulario POST `/payments/{plan_id}/{gateway_id}` (protegido por CSRF + sesión).

### Cobertura objetivo
- 11 casos (P1–P11): 6 de firma/bloqueo + 3 de derivación de identidad + 2 de endpoint API.
- 0 URLs de pago aceptadas sin firma válida.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Middleware `signed` registrado | `Kernel.php:97` → `ValidateSignature` | Reutilizar alias existente |
| P2 | `URL::signedRoute` / `temporarySignedRoute` | Disponible en Laravel 10.41 | Usar en vistas/controller/API |
| P3 | Factories `SecurityDeposit` | Existe | Reutilizar en tests |
| P4 | Factory `RentPayment` | No existe | Crear factory mínima o construir manualmente en tests |
| P5 | Sanctum en API | Usado en suite A | Reutilizar `Sanctum::actingAs` |
| P6 | Ruta `payment-url` en `api.php` | No existe | Añadir bajo `auth:sanctum` |
| P7 | `CreatesUsers` trait | Existe | Reutilizar |

## 4. Estructura propuesta

```
routes/web.php                                    # MODIFICADO: rutas firmadas
routes/api.php                                    # MODIFICADO: GET /api/v1/payment-url
app/Http/Controllers/PaymentController.php        # MODIFICADO: DepositRentPayment($id), index(...), + paymentUrl()
app/Http/Controllers/Auth/SignupController.php   # MODIFICADO: URLs firmadas
resources/views/landlord/subscription/index.blade.php # MODIFICADO: URL::signedRoute
database/factories/RentPaymentFactory.php         # NUEVO (si falta)
tests/Feature/Payment/PaymentFlowSmokeTest.php   # MODIFICADO: URLs firmadas + P1–P11
```

## 5. Decisiones técnicas

- **Framework:** Laravel 10, PHPUnit 10, `RefreshDatabase`.
- **DB:** MySQL `dbrentpro_test`.
- **Firma:** `URL::temporarySignedRoute(name, now()->addHours(2), params)` para todas las URLs de pago. Expiración de 2 h (suficiente para completar un pago; reduce ventana de reutilización).
- **Middleware:** `signed` en ambas rutas GET de pago. Sin firma válida → 403.
- **Identidad depósito/renta:** derivar `tenant_id` del recurso; eliminar `{tenant_id}` de la ruta. Si hay sesión web y `auth()->id()` no coincide con el tenant derivado → 403 (defensa en profundidad; móvil sin sesión se omite).
- **Identidad suscripción:** mantener `{landlord_id}` en la URL, pero solo aceptado si la firma es válida. Si hay sesión web y `auth()->id() !== (int) $landlord_id` → 403.
- **Endpoint API:** `GET /api/v1/payment-url` autenticado (Sanctum). Parámetros: `payment_type` (`subscription`|`security_deposit`|`rent_payment`) + `plan_id` (subscription) o `payment_id` (deposit/rent). Valida ownership antes de firmar. Devuelve `{ url }`.
- **Sesión:** `PaymentController` sigue poblando `session('user_id')` desde la identidad resuelta (no desde input libre).
- **Respuesta denegada:** 403 (firma inválida o identidad no coincide). 401 para API sin token.
- **Mocks obligatorios:** `Storage::fake()` para uploads; `Sanctum::actingAs` para API.

## 6. Catálogo de casos

Prefijo `P` (pagos). Casos P1–P8 cubren rutas web; P9–P11 el endpoint API.

### 6.1 Rutas de pago firmadas (web)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| P1 | URL firmada de suscripción renderiza vista | `Plan` + landlord, URL firmada con `landlord_id=landlord` | `GET` signed `/payments-gateways/{plan}/{landlord}` | 200, view `payments.index`, `session('user_id') == landlord.id`, `session('payment_type') == 'subscription'` |
| P2 | URL de suscripción sin firma → 403 | `Plan` + landlord | `GET` unsigned `/payments-gateways/{plan}/{landlord}` | 403 |
| P3 | URL firmada de otro usuario con sesión web → 403 | Landlord A logueado, URL firmada para Landlord B | `GET` signed URL (`landlord_id=B`) con sesión A | 403 |
| P4 | URL firmada de depósito renderiza vista | `SecurityDeposit` (con property+detail) + tenant | `GET` signed `/online-payments-gateways/{deposit}` | 200, view `payments.online_payment`, `session('user_id') == deposit.tenant_id`, `session('payment_type') == 'security_deposit'` |
| P5 | URL firmada de renta renderiza vista | `RentPayment` + tenant | `GET` signed `/online-payments-gateways/{rentPayment}` | 200, `session('user_id') == rentPayment->rent->tenant_id` |
| P6 | URL de depósito sin firma → 403 | deposit | `GET` unsigned `/online-payments-gateways/{deposit}` | 403 |
| P7 | Ruta antigua con `{tenant_id}` ya no existe | deposit + tenant | `GET` `/online-payments-gateways/{deposit}/{tenant}` | 404 |
| P8 | `payment_type` inválido → redirect con error | id 1 | `GET` signed `/online-payments-gateways/1?payment_type=xxx` | 302 + `session('error')` |

### 6.2 Endpoint API de URLs firmadas

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| P9 | Endpoint devuelve URL firmada (suscripción) | Landlord autenticado (Sanctum) + Plan | `GET /api/v1/payment-url?payment_type=subscription&plan_id=X` | 200 JSON `{ url }`, URL firmada con `landlord_id == auth.id` |
| P10 | Endpoint rechaza depósito ajeno | Tenant A autenticado, deposit de Tenant B | `GET /api/v1/payment-url?payment_type=security_deposit&payment_id=B` | 403 |
| P11 | Endpoint rechaza sin token | sin Authorization | `GET /api/v1/payment-url` | 401 |

### Tests de caracterización

| ID | Bug caracterizado | Fase que lo romperá |
|----|-------------------|---------------------|
| `PaymentFlowSmokeTest::test_payment_success_processes_subscription_from_session` | Seg-6 — `/payment/success` público procesa desde sesión | Fase 2 / 2.4 |

## 7. Criterios de aceptación (Definition of Done)

- [x] Rutas `online.payments-gateways.index` y `payments-gateways.index` llevan middleware `signed`.
- [x] `DepositRentPayment` recibe solo `$id` y deriva `tenant_id` del recurso.
- [x] La ruta antigua `/online-payments-gateways/{payment_id}/{tenant_id}` ya no existe (404).
- [x] `index()` valida firma + coincidencia con `auth()->id()` cuando hay sesión.
- [x] Vistas y `SignupController` generan URLs con `URL::temporarySignedRoute` (expiración 2 h).
- [x] Existe `GET /api/v1/payment-url` autenticado que devuelve URL firmada tras validar ownership.
- [x] `session('user_id')` se popula desde identidad resuelta, nunca desde input libre.
- [x] P1–P8 en verde en `PaymentFlowSmokeTest`.
- [x] P9–P11 en verde (nuevo test API o extendido).
- [x] `php artisan test tests/Feature/Payment/` en verde.
- [x] `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.
- [x] `tests/README.md` actualizado con los casos P1–P11.

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| App móvil queda rota hasta actualizarse (URLs sin firma → 403) | Alto | Publicar app actualizada consumiendo `/api/v1/payment-url` antes del deploy; o desplegar backend y app juntos |
| `RentPayment` sin factory dificulta tests | Medio | Crear `RentPaymentFactory` mínima o construir manualmente |
| `temporarySignedRoute` de 2 h puede expirar en pagos largos | Bajo | Ajustar a 4 h si QA lo requiere |
| `signed` middleware devuelve 403 o 400 según handler | Bajo | Verificar en implementación y ajustar tests |
| Derivar tenant de `RentPayment->rent` requiere relación cargada | Medio | `load('rent')` antes de leer `tenant_id` |
| Mobile no comparte sesión web: `auth()->check()` es false, se omite el check de coincidencia | Medio | La firma garantiza integridad; el endpoint API valida ownership al mintear la URL |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

## 9. Estimación

- Rutas + middleware + controller: 0.5 día.
- Endpoint API + validación ownership: 1 día.
- Vistas + SignupController + factory: 0.5 día.
- Tests P1–P11: 1.5–2 días.
- README + verificación CI: 0.5 día.
- Total: 3.5–4.5 días.

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 2 / 2.3
- `.analisis/seguridad.md` → Seg-6
- `.agents/spec/spec-conventions.md`
- `.agents/spec-impl/spec-policies-fase2.md`
- `.agents/spec-impl/spec-property-update-landlord-id-fase2.md`
- `tests/Feature/Payment/PaymentFlowSmokeTest.php`
- `app/Http/Controllers/PaymentController.php`
- `app/Http/Controllers/Auth/SignupController.php`
- `resources/views/landlord/subscription/index.blade.php`
- `Rentpro_app_codecanyon-2.0.0/lib/app/pages/common/pages/online_payment/_online_payment_view.dart`
- `app/Http/Kernel.php` (middleware `signed`)
