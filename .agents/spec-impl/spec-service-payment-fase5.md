---
id: spec-service-payment-fase5
titulo: "Service Payment — PaymentController (464) + SubscriptionService para listing triplicado (Fase 5 / 5.13, hijo 5)"
estado: Aprobado
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.13) — DT-C4, DT-A1
cobertura: Migración del monolito PaymentController (3 payment types + 11 gateways + success idempotente) a un único PaymentService, y del listing/reject/paid de Subscription (triplicado Admin/Landlord/Api) a SubscriptionService
---

# Spec — Service Payment (Fase 5 / 5.13, hijo 5)

> `PaymentController` (464) queda < 200 con toda la lógica de pago en **un único `PaymentService`** (decisión grill Q3a): manual + 11 gateways + `success` idempotente con `Cache::lock`. El listing/reject/paid de Subscription triplicado va a `SubscriptionService`. Va último: dinero, máxima red de tests.

**Patrón de referencia:** `spec-unificar-capas-services-fase4.md` §8. **Paraguas:** `spec-unificar-capas-services-fase5.md`.

## 1. Objetivo

`PaymentController` < 200 líneas; `Admin/SubscriptionController` (187) delega listing/reject/paid/getInvoice/file_download a `SubscriptionService` (también consumido por `Landlord/SubscriptionController` 16 y `Api/SubscriptionsController` 25); cero cambio de contrato.

## 2. Alcance

### IN
- `app/Services/PaymentService.php`: `depositRentPaymentView`, `gatewayIndex`, `pay`, `success`, `paymentUrl`, `failed`, `orderStatus`.
- `app/Services/SubscriptionService.php`: `listForChannel`, `reject`, `paid`, `getInvoice`, `fileDownload`.
- Refactor in-place de `PaymentController` y `Admin/SubscriptionController`.

### OUT
- `Admin/TransactionController` (79) y `Admin/TransactionReportController` (90): lecturas, < 200 — quedan tal cual.
- `Admin/RentPaymentGenerateController` (88): < 200 — queda tal cual.
- Gateways de `app/Library/` (su contrato `make_payment($data, bool $expectsJson)` no cambia; whitelist Fase 2/2.5 intacta).
- Rutas web `online-payments-gateways`, `payments-gateways`, `payments/{plan}/{gateway}`, `payment/success|failed`, `order-status`, api `payment-url`: intactas (incl. middleware `signed`/`auth`).

### Cobertura objetivo
- `PaymentController` < 200; una implementación del listing de subscriptions (hoy ×3); suite + PHPStan + Pint en verde.

## 3. Pre-requisitos

| # | Pre-requisito | Estado | Acción |
|---|---|---|---|
| P1 | Hijo 4 mergeado | Pendiente | Orden del paraguas |
| P2 | Specs Fase 2 (2.2–2.5) y 4.8 | Implementados | `success` idempotente + verify-antes-de-tx ya vigentes — preservar |
| P3 | Red de pagos existente | Verde | `PaymentFlowSmokeTest`, `BalanceForceFillTest` (P1–P3), `GatewayNamespaceWhitelistTest`, `WebhookSignatureSmokeTest`, `CachePaymentInvalidationTest`, `SubscriptionPaidTest` (G8–G9) |
| P4 | `StubGateway` de tests | Existe | Reutilizar |

## 4. Estructura propuesta

```
app/Services/PaymentService.php
app/Services/SubscriptionService.php
tests/Unit/PaymentServiceTest.php
tests/Unit/SubscriptionServiceTest.php
tests/Feature/Payment/PaymentUrlHttpTest.php     # caracterización previa (hueco actual)
tests/Feature/Payment/OrderStatusHttpTest.php    # caracterización previa (hueco actual)
```

### Contrato de PaymentService

```php
class PaymentService
{
    public function depositRentPaymentView(int $paymentId): array;       // vista signed (session payment_id)
    public function gatewayIndex(int $planId, ?int $landlordId): array; // vista gateways (session)
    public function pay(int $planId, int $gatewayId, array $data, ?UploadedFile $attachment = null): mixed;
    //   ↑ manual: PlanSubscribe + attachment (upload ANTES de tx) | gateway: make_payment() FUERA de tx
    public function success(RequestState $state): mixed;                  // Cache::lock + Stripe verify FUERA/ANTES de tx
    //   ↑ 3 ramas privadas: security_deposit → landlord balance forceFill;
    //     rent_payment → rama actual; subscription → trío forceFill + Transaction::create
    public function paymentUrl(...): string;                             // signed URLs para app (api la reusa)
    public function failed(...): mixed;
    public function orderStatus(...): mixed;
}
```

### Contrato de SubscriptionService

```php
class SubscriptionService
{
    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator;
    public function reject(PlanSubscribe $subscription, Channel $channel): void;
    public function paid(PlanSubscribe $subscription, Channel $channel): void;  // trío forceFill + forgetModel (spec 5.11)
    public function getInvoice(PlanSubscribe $subscription): mixed;
    public function fileDownload(PlanSubscribe $subscription): string;
}
```

## 5. Decisiones técnicas

- **Un solo `PaymentService`** (grill Q3a), sin split por tipo de pago: las 3 ramas de escritura de `success` quedan como métodos privados del service; `PropertyService` (762 líneas) demuestra que el tamaño del service es aceptable.
- **Se preserva la postura 4.8 literalmente:** Stripe HTTP verify + `Cache::lock` + gateway `make_payment` FUERA de la tx; la tx solo envuelve escrituras; attachment upload antes de abrirla.
- **Idempotencia (2.3–2.4) y whitelist de namespaces (2.5, 403) intactas.** `DemoMode` y middleware de rutas no cambian.
- `success` 3 ramas: landlord balance vía `forceFill` (BalanceForceFillTest P1–P3), trío suscripción vía `forceFill` + invalidación `plan-data-{user_id}` (`CachePaymentInvalidationTest`) — el service hereda las reglas 4.9/5.11.
- `SubscriptionService::paid` es la misma lógica que `PaymentService::success` rama subscription en cuanto al trío: se implementa UNA vez y `paid()` la consume — decisión de single-source dentro del slice; documentar en §Revisión si divergen hoy.
- El controlador `PaymentController` queda: parse de request + session + delegación + armado de respuesta/redirect. La API (`routes/api.php` L97) reusa `paymentUrl` — sin cambios.

## 6. Catálogo de casos (prefijo `P` pagos, `U` unit)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| P1 | DepositRentPayment signed | payment_id en session, URL firmada | GET /online-payments-gateways/{id} | 200 vista (red existente) |
| P2 | Gateway index | plan_id + landlord_id | GET /payments-gateways/{plan}/{landlord} | 200 vista gateways (red existente) |
| P3 | Pago manual | plan free/paid, attachment | POST /payments/{plan}/{gateway} manual | trío/plan escrito; attachment (red existente) |
| P4 | Success idempotente | pago ya procesado (lock) | GET /payment/success | no doble escritura (red existente) |
| P5 | Success rama security_deposit | pago de depósito de landlord B | GET /payment/success | balance acreditado forceFill (BalanceForceFillTest) |
| P6 | Success rama subscription | pago de suscripción | GET /payment/success | trío forceFill + Transaction (red existente) |
| P7 | **paymentUrl api** (hueco) | Sanctum landlord | GET /api/payment-url?... | 200 JSON shape actual congelado (caracterización nueva) |
| P8 | **orderStatus** (hueco) | sesión/estado de orden actual | GET /order-status | respuesta actual congelada (caracterización nueva) |
| P9 | **failed** (hueco) | — | GET /payment/failed | respuesta actual congelada |
| P10 | Subscriptions listing ×3 | admin/landlord/ Sanctum | GET /admin/subscriptions (+filter), /landlord/subscriptions, /api/plans | 3 canales, una implementación; shapes actuales |
| P11 | Subscriptions reject/paid | admin | POST /admin/subscriptions/reject|paid/{id} | comportamiento actual (G8–G9 red) |
| P12 | Subscriptions invoice/file | admin, suscripción pagada | GET .../get-invoice/{id}, .../file-download/{id} | descargas actuales |

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | success rama deposit | stub state deposit | `success(...)` | balance forceFill, dentro de tx |
| U2 | success idempotente | state ya procesado | `success(...)` | no reescribe (lock) |
| U3 | pay gateway fuera de tx | StubGateway | `pay(...)` | `make_payment` invocado fuera de tx |
| U4 | subscription paid trío | suscripción pending | `SubscriptionService::paid(...)` | trío + `forgetModel` |

> P1–P6/P10–P11 reutilizan la red existente; **P7–P9 y P12 son caracterizaciones nuevas de huecos actuales**, implementadas antes de migrar.

## 7. Criterios de aceptación (Definition of Done)

- [ ] `PaymentService` + `SubscriptionService` en verde (U1–U4).
- [ ] `PaymentController` < 200 líneas; `Admin/SubscriptionController` delega listing/reject/paid/invoice/file.
- [ ] Caracterizaciones nuevas P7–P9, P12 en verde antes de migrar; red completa de pagos sigue en verde.
- [ ] 0 `Cache::`/`DB::`/`Storage::` de negocio en el controlador (grep).
- [ ] Suite + PHPStan + Pint en verde.

## 8. Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Dinero: regresión silenciosa en `success` | Slice último + red de pagos existente + U1–U2 |
| Divergencia hoy entre `SubscriptionController::paid` y `success` rama subscription | Caracterizar ambos ANTES; si difieren, preservar divergence y documentar (no unificar a ciegas) |
| 11 gateways con credenciales sandbox | `StubGateway` en tests; nunca gateways reales |
| Session-state del flujo web (payment_id, plan_id) | P1–P2 caracterizan el contrato session actual |

## 9. Estimación

- 8–12 días (caracterización 2–3, services 2–3, migración 2, validación con red completa 2–3).

## 10. Referencias

- `spec-unificar-capas-services-fase5.md` · `spec-payment-success-fase2.md` · `spec-gateway-namespace-whitelist-fase2.md`
- `spec-db-transactions-no-external-io-fase4.md` (4.8) · `spec-cache-invalidacion-centralizada-fase4.md` (4.9) · `spec-user-fillable-restrictivo-fase5.md`
- `.analisis/plan/plan-accion.md` → Fase 5 / 5.13
