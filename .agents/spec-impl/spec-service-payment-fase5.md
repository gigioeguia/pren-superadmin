---
id: spec-service-payment-fase5
titulo: "Service Payment — PaymentController (464) + SubscriptionService para listing triplicado (Fase 5 / 5.13, hijo 5)"
estado: Implementado
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

- [x] `PaymentService` + `SubscriptionService` en verde (U1–U4). **Evidencia:** unit 5 passed / 33 assertions (u1, u2a spent-state, u2b lock-busy, u3 make_payment sin abrir tx vía captura de `DB::transactionLevel` en StubGateway, u4 trío + quirk exacto + `Notification::assertNothingSent` — el service no notifica).
- [x] `PaymentController` < 200 líneas; `Admin/SubscriptionController` delega listing/reject/paid/invoice/file. **Evidencia:** `wc -l` → PaymentController 464→**191**, Admin/SubscriptionController 187→**131** (delega vía `listForChannel(Channel::Admin)`/`adminQuery`/`reject`/`paid`/`getInvoice`/`fileDownload`), Landlord 19 / Api 28 delgados.
- [x] Caracterizaciones nuevas P7–P9, P12 en verde antes de migrar; red completa de pagos sigue en verde. **Evidencia:** commit `60017fc` (22 passed / 65 assertions pre-migración: `PaymentUrlHttpTest` p7a–f, `OrderStatusHttpTest` p8a–c/p9a–b, `SubscriptionChannelsHttpTest` p10a–d/p11a–d/p12a–c) + red existente (PaymentFlowSmokeTest, BalanceForceFillTest, GatewayNamespaceWhitelistTest, WebhookSignatureSmokeTest, CachePaymentInvalidationTest, SubscriptionPaidTest G8–G9) en verde pre y post.
- [x] 0 `Cache::`/`DB::`/`Storage::` de negocio en el controlador (grep). **Evidencia:** grep en los 4 controladores migrados → único hit `Storage::download($filePath)` en Admin/SubscriptionController (delivery del stream, mismo criterio que los hermanos ya migrados SecurityDeposit/RentPayment); PaymentController 0 hits de los tres facades (Cache/lock, DB tx y Stripe verify viven en el service).
- [x] Suite + PHPStan + Pint en verde. **Evidencia:** `php artisan test` **486 passed (5500 assertions)** (459 previos + 22 contrato + 5 unit); `composer analyse` [OK] — baseline 180→**157** entradas (23 legacy resueltas por la migración, ver §11); `composer lint` passed.

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

## 11. Implementación (2026-11-22) — rama `spec/service-payment-fase5-impl`

**Desviación P1:** implementado apilado sobre la rama del hijo 4 (aún sin mergear a `main`), como hijos 2–4. El orden del paraguas se preserva vía merge de PRs encadenados.

Commits: `60017fc` (contrato P7–P12 pre-migración) → `8940ee1` (services + Support + U1–U4) → `7c8a8e7` (migración 4 controladores).

**Divergencia trío `paid` vs `success` (anticipada por §5/§8) — confirmada y preservada, NO unificada:**

| Aspecto | `SubscriptionService::paid` (admin aprueba manual) | `PaymentService::success` rama subscription |
|---|---|---|
| PlanSubscribe | actualiza fila existente (`unpaid`→`paid`) | crea fila nueva `paid` |
| `will_expire` | `now()->addDays($subscribe->plan->duration ?? 0)` — **duration crudo: +1 día para plan mensual** (quirk) | `now()->addDays($plan->durationInDays())` — +30 días |
| `received_by` | admin autenticado (`$approvedBy`) | primer `superadmin\|admin` |
| `notes` | del request (validadas) | n/a (la fila manual ya las trae) |

U4 congela el quirk exacto (`+1 día`); G8–G9 siguen cubriendo el trío/invalidación a nivel HTTP. El single-source del §5 solo era posible a ciegas — se descarta por §8.

### Desviaciones de contrato (sobre el sketch §4)

- **D1 — `RequestState` → `PaymentState::fromSession()`:** el DTO vive en `app/Support` y su factory estática es la única fuente del contrato de sesión del flujo de pago (replica las lecturas del original 1:1; valores de sesión no-escalar/no-modelo → ausentes). `pay()` one-shot no es implementable sin que el service toque sesión: se parte en `resolvePayment` (gateway whitelist + user + plan/amount) → `payManual` / `payGateway`, con el controlador stasheando `fund_callback`+`user`/`plan`/`gateway_id`/`amount` antes de `make_payment` (orden del original).
- **D2 — `success(): mixed` → `PaymentOutcome`:** enum puro (PHP no admite backing duplicado 'failed'×2) con `status()/message()/flashKey()` replicando los 3 outcomes observables. El controller marca `payment_processed`+forget solo en `Success` (ver error-paths).
- **D3 — `paymentUrl(...)` → `subscriptionUrl/depositUrl/rentUrl`:** 403 de ownership vía `AuthorizationException` (Handler → 403, idéntico al `abort(403)` original); la validación 422 por tipo queda en el controller (contrato HTTP).
- **D4 — `failed()`/`orderStatus()` quedan en el controller:** 100 % session-parse + redirect, cero dominio — el sketch los listaba en el service, pero paraguas §5.3 manda (service agnóstico de HTTP). Congelados por P8–P9.
- **D5 — SubscriptionService:** `listForChannel(Channel): LengthAwarePaginator|Collection` (sin `?User $actor` ni `array $filters` — ningún canal los usa; el retorno del sketch era irreal: Admin pagina PlanSubscribe, Landlord/Api devuelven Collection de Plan activos). `reject`/`paid` ganan `string $notes` (el sketch los omitía) y `paid` gana `int $approvedBy`; pierden `Channel` (rutas admin-only, el canal no discrimina). `fileDownload(): ?string` devuelve el path; el streaming queda en el controller (precedente SecurityDeposit/RentPayment ya migrados). El hook `query()` del trait delega en `adminQuery(?string $search)`.
- **D6 — P10–P12 viven en `tests/Feature/Subscriptions/SubscriptionChannelsHttpTest.php`** (la estructura §4 no asignaba archivo al catálogo de subscriptions).

### Observaciones empíricas congeladas durante la implementación

- **Enum backing:** `PaymentOutcome` nació `: string` con dos casos `'failed'` — PHP lo rechaza ("Duplicate value in enum"); enum puro + `status()`.
- **U3 y RefreshDatabase:** el trait envuelve cada test en una tx ⇒ el assert compara contra el baseline de `DB::transactionLevel()` (capturado dentro de StubGateway, que gana `static int $transactionLevelAtCall`), no contra 0.
- **`offerPrice` vuelve de MySQL como float** (8.0) — asserts de payment_data por valor, no por identidad de tipo.
- **Baseline PHPStan 180→157 entradas (−23):** los generics `@return BelongsTo<X, $this>` en `Gateway::currency`, `PlanSubscribe::user/plan/gateway` y `RentPayment::rent` + la eliminación del `if ($reject)` muerto resolvieron 23 entradas legacy (controllers de subscriptions/reports, exports y los 11 gateways de `app/Library`). Regeneración justificada (regla de baseline single-use: errores legacy resueltos por la migración misma, 0 errores nuevos); diff del archivo en el PR.
- **Larastan no infiere la clase relacionada desde `: BelongsTo` pelado** (resuelve `Model`): los generics del punto anterior son requisito, no cosmética.

### Error-paths y edges preservados/mejorados (documentados)

- **Orden interno intacto:** 404 (plan) → 403 (identidad) → session en las vistas; `payment_processed` check antes del lock; verificación (Stripe HTTP / payment_info) antes de abrir tx; writes dentro de tx; guard-throws (`payer missing`, `landlord not found`, `admin missing`, `type/plan mismatch`) reproducen los crash→catch→`Something went wrong!` del original con excepciones tipadas.
- **Sesión tras fallo de commit (corner no observable):** el original olvidaba las claves y marcaba `payment_processed` ANTES del `DB::commit()` — un commit fallido dejaba la sesión "procesada". Ahora el forget ocurre solo en outcome `Success`. Requeriría que el propio `commit()` lance para divergir.
- **Sesiones/inputs craftedas (inalcanzables por los flujos reales):** mismatch tipo/plan en sesión, `payment_type` no-escalar, `session_id` array, o `manual_data` array: antes `ErrorException`/`\Error` no capturados (500) en varios puntos; ahora fallo manejado (redirect failed o `Something went wrong!`). `manual_data` array se almacena como `null` (antes: el array crudo). Ninguno alcanzable por la UI/vistas actuales.
- **`paid` con plan inexistente:** el `?? ''` original era código muerto (`plans.duration` NOT NULL default 0) → `?? 0` equivalente; un plan borrado con FK viva era 500 no capturado (`addDays('')` TypeError/\Error) y sigue siendo fallo manejado vía la tx del service.
- **Mejora documentada:** `paid()` ahora `report($e)` antes del JSON 404 `'Something Went wrong!'` (el original tragaba la excepción en silencio); `reject`/`paid` pierden su rama `else` muerta tras `findOrFail` (unreachable desde siempre).
