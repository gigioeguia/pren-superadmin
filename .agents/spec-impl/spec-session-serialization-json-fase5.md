---
id: spec-session-serialization-json-fase5
titulo: "Flip session `serialization: json` con precondición escalar en el flujo de pagos (Fase 5 / 5.21)"
estado: Borrador
fecha: 2026-11-23
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.21) — desviación confirmada por el decisor del spec 5.15 (§5/§8, "Pendiente de decisión" cerrado 2026-11-22); endurecimiento anti gadget-chain paralelo al `serializable_classes: false` (D2 del spec 5.15)
cobertura: Endurecer el canal SESSION con `serialization: json` (default del skeleton L13, sin válvula env) tras refactorar el único stash de modelos del repo (`PaymentController:96-97`, load-bearing para 2 de 3 flujos de pago) a IDs escalares + rehidratación consolidada en `PaymentService::success`; guard doble anti-objetos (custom PHPStan rule + arch-test allowlist), 7º check HARD en `ops:verify-deploy` y migración de los 6 bloques `withSession` con modelos. Decisiones selladas en grilling Q1–Q13 (2026-11-23).
---

# Spec — Flip session `serialization: json` con precondición escalar en el flujo de pagos (Fase 5 / 5.21)

> Resumen de una línea: la sesión deja de serializarse con PHP (`unserialize` = superficie gadget-chain) y pasa a `json` — con la precondición de que ninguna clave transporte objetos: el stash de modelos de `payment()` se reemplaza por `plan_id` escalar, la rehidratación ya existente en `success()` queda como único camino, y dos guards (PHPStan + arch-test) impiden reincidir.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.21, l.126) — paralelo al `serializable_classes: false` del cache (D2, `spec-laravel13-advisories-fase5` §5/§8).

---

## 1. Objetivo

Cerrar el endurecimiento anti gadget-chain del canal **sesión** (el cache ya quedó con `serializable_classes: false` en 5.15/D2): flip `session.serialization` a `json` con la precondición de que ningún payload de sesión dependa de objetos. Hoy `PaymentController:96-97` guarda los modelos `User`/`Plan` en la sesión y `PaymentState::fromSession` es su único lector; bajo `json` degradan a arrays planos (`json_encode` via `JsonSerializable`, `instanceof` falso → `null`). El stash es **load-bearing** para deposit/rent/subscription-vía-`index()` porque `plan_id` solo lo pone `SignupController` — por eso el slice refactora primero y flipea después, en el mismo PR.

---

## 2. Alcance

### IN

- **`PaymentController::payment()`** — el stash cambia a scalares:
  - `session()->put('plan_id', (int) $plan_id)` (el id ya viene por URL, l.60); puts de `user`/`plan` (ll.96-97) **eliminados**; `user_id` no se re-put (garantizado upstream: `SignupController:51/67`, `PaymentController:35/54`; el propio `resolvePayment` lo exige, l.73).
  - Listas `forget()` (ll.122/131) sin las claves `plan`/`user`.
  - Identity check 403 (l.116) simplificado a `$state->userId` (la prop `$state->user` que lee desaparece) — semántica equivalente, única fuente de verdad.
- **`App\Support\PaymentState`** — 100% escalar: fuera las props `plan`/`user`, fuera los branches `instanceof User`/`sessionModel` (ll.46-51/86-93) y la rehidratación parcial de user. `fromSession` queda con el contrato `paymentType|planId|userId|amount|gatewayId|paymentInfo|sessionId|stripeSessionId`, todo escalar/array (docblock "replica 1:1" actualizado — el contrato de sesión cambia, que es lo que este spec fija).
- **`App\Services\PaymentService::success()`** — única rehidratación (hoy duplicada en dos niveles): el match por `paymentType` con `findOrFail($state->planId)` (ll.223-230) pasa a ser el único origen de `$plan`; `$user` se resuelve con `User::findOrFail($state->userId)` en el mismo punto donde hoy está el guard `RuntimeException` (ll.260-262) — userId ausente → `ModelNotFoundException` → `catch` → `SomethingWrong`, observable idéntico al actual.
- **`config/session.php`** — añadir **solo** `'serialization' => 'json'` (literal, sin válvula env — sync exacto con el skeleton L13, que la omite deliberadamente).
- **Micro-fixes de tipado** (precondición de la regla PHPStan, `mixed` rechazado): `PaymentController:34` (`'platform'` → guard `is_string`) y `TapPayment:76` (`'secret_key'` → cast/guard) + cualquier otro write `mixed` que la regla detecte al activarse (presupuesto acotado, ver §8).
- **Guard doble anti-objetos-en-sesión (Q7):**
  - **(a) Custom PHPStan rule** `phpstan/Rules/SessionPayloadRule.php` + `phpstan/rules.neon` incluido desde `phpstan.neon.dist` (primer custom rule del repo). Formas cubiertas (Q11): `put`/`push`/`flash`/`now` (Store y fachada `Session`), `session([...])` (forma array), `RedirectResponse::with()` (flash data es data de sesión). Estrictitud (Q12): valor top-level `scalar|array|null`; `mixed` y objetos/enums rechazados; contenidos de arrays sin recursión; **0 entradas al baseline**.
  - **(b) Arch-test** `tests/Unit/Session/SessionWritersArchTest.php`: allowlist congelada de los **14 archivos escritores** (`SignupController`, `PaymentController`, `SetLocale` + 11 gateways de `app/Library/`); archivo nuevo que escriba sesión ⇒ rojo.
- **`ops:verify-deploy`** — 7º check HARD: `config('session.serialization') === 'json'` (patrón 5.9/5.20, evidencia runtime del flip de producción).
- **Tests:**
  - `PaymentFlowSmokeTest`: aserciones 148-149 → scalares (`session('plan_id')`); los 5 bloques `withSession` con modelos (ll.164-165/368-369/387-388/407-408/427-428) migran a `plan_id`/`user_id` — pasan a cubrir la ruta de rehidratación; forget post-success (ll.475-476) sobre claves scalares; **nuevo caso** success `rent_payment` rehidratado (hoy sin cobertura, ver §6.2).
  - `BalanceForceFillTest`: bloque (ll.56-57) migra a scalares.
  - Nuevos: config U, `PaymentState` U, arch-test U, check de `VerifyDeploy` U+F.
- **Docs:** `AGENTS.md` (gotcha de sesión json + conteo), `README.md`/`tests/README.md` (conteos + mini-sección), `.agents/README.md` (índice fase5 26, total 71), cierres locales `.analisis/` (fila 5.21 → Mitigado + métrica).

### OUT

- **Flip de producción + `config:cache`** — residual con dueño (patrón 5.9); la evidencia es el 7º check HARD.
- **Sync completo `config/session.php` ↔ skeleton L13** (Q1): `partitioned`, cookie slug `-` vs `_`, `SESSION_TABLE`, `SESSION_PATH`… intocados — sin motivación documentada, amplían blast radius.
- **`TapPayment:76` `secret_key` en sesión como higiene de credencial** (Q9): nota lateral — bajo `json` es string funcional; su cuestionable exposición es herencia del template, no un bloqueante de este flip.
- **Otros writers de gateways/controladores** — sin cambios funcionales (ya scalares/arrays); solo los micro-fixes de tipado del IN.
- **Pruebas round-trip del framework** (Q8): la suite entera corre bajo `json` tras el flip — es el test; no se replica mecánica de `Store::save/readFromHandler`.
- **`serializable_classes`/cache** — cerrado en 5.15/D2; nada que hacer aquí.

### Cobertura objetivo

- **0 objetos en sesión** (audit estático): regla PHPStan activa con 0 reportes sobre `app/` post-fixes; arch-test verde con la allowlist exacta.
- **Los 3 payment types cruzan `success()` por rehidratación** (subscription, security_deposit, rent_payment — casos P).
- Suite completa esperada **561 + 8 = 569 passed** (verificar contra salida real — lección 5.3).

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Auditoría de writers de sesión | **Verificado (2026-11-23)** — ~75 writes en `app/`; solo `PaymentController:96-97` guardan modelos; resto scalares/arrays | Ninguna (evidencia: grep `->put(`/`Session::put(`/`session([` en `app/`) |
| P2 | Rehidratación por IDs | **Existe** — `PaymentService::success:223-234` (match por paymentType + user); duplicada con `PaymentState:49-51` | Consolidar en `success` (Q3) |
| P3 | `plan_id` en sesión para los 3 flujos | **Parcial** — solo `SignupController:52/68` lo pone; deposit/rent/`index()` no | Put escalar en `payment()` (Q2) |
| P4 | Base de conteos 561 (5.20 mergeado) | Existe — `AGENTS.md`/README | Verificar 561+8=569 contra salida real |
| P5 | Mecanismo de custom rules PHPStan | **Falta** — `phpstan.neon.dist` solo incluye larastan + baseline | Crear `phpstan/rules.neon` + include (Q13) |
| P6 | `ops:verify-deploy` | Existe — 5 HARD + 3 WARN + php floor (6º, 5.20) | Añadir 7º HARD (Q6) |
| P7 | `withSession` con modelos | Existe — 6 bloques (PaymentFlowSmokeTest ×5, BalanceForceFillTest ×1) | Migrar a scalares (Q8) |
| P8 | Session driver `database` + tabla | Cerrado (5.5) | Ninguna |

---

## 4. Estructura propuesta

```
config/session.php                                           # MODIFICADO: +1 key ('serialization' => 'json'), literal

app/Http/Controllers/PaymentController.php                   # MODIFICADO: stash escalar + forget + identity check
app/Support/PaymentState.php                                  # MODIFICADO: contrato 100% escalar
app/Services/PaymentService.php                               # MODIFICADO: rehidratación única en success()
app/Library/TapPayment.php                                    # MODIFICADO: micro-fix 'secret_key' (tipado)

phpstan/
  rules.neon                                                  # NUEVO: services (SessionPayloadRule) + scanDirectories
  Rules/SessionPayloadRule.php                                 # NUEVO: regla scalar|array top-level (put/push/flash/now/session([)/with)

phpstan.neon.dist                                             # MODIFICADO: +1 include (phpstan/rules.neon)
app/Console/Commands/VerifyDeploy.php                         # MODIFICADO: 7º check HARD session serialization

tests/Unit/Session/SessionWritersArchTest.php                # NUEVO: allowlist congelada de 14 escritores (U)
tests/Unit/Config/SessionSerializationConfigTest.php         # NUEVO: U (carpeta Config/ de 5.5)
tests/Unit/Support/PaymentStateTest.php                       # NUEVO: U
tests/Unit/Console/VerifyDeploySessionTest.php                # NUEVO: U (patrón VerifyDeployPhpFloorTest)
tests/Feature/Console/VerifyDeployTest.php                    # MODIFICADO: +1 caso F (fila del check)
tests/Feature/Payment/PaymentFlowSmokeTest.php                # MODIFICADO: 148-150 scalares, 5 bloques, forget, +1 rent_payment
tests/Feature/Payment/BalanceForceFillTest.php                # MODIFICADO: bloque deposit a scalares

README.md · tests/README.md · AGENTS.md · .agents/README.md   # MODIFICADO: conteos/índice/gotcha

.analisis/ (gitignored — edición local, sin commit)
  plan/plan-accion.md                                         # fila 5.21 → Mitigado + métrica
```

---

## 5. Decisiones técnicas

Fijadas (grilling Q1–Q13, 2026-11-23), no negociables salvo volver el spec a `Borrador`:

- **Solo la key (Q1):** `config/session.php` recibe exclusivamente `'serialization' => 'json'`; el resto de divergencias con el skeleton L13 queda declarado OUT.
- **Stash escalar (Q2):** `plan_id` escalar nuevo; claves `user`/`plan` borradas (semántica tramposa si se reutilizaran para ids); `user_id` no se re-put.
- **`PaymentState` 100% escalar (Q3):** sin props `plan`/`user`, sin branches de modelo; la rehidratación (hoy duplicada `PaymentState:49-51` + `success:232-234`) vive una sola vez en `PaymentService::success`.
- **Literal, sin válvula (Q4):** el skeleton L13 no ofrece `SESSION_SERIALIZATION`; una válvula de reversión para un hardening es un anti-patrón bajo presión de deploy (precedente `TrustHosts` "sin válvula de config"). Sin entrada en `.env.example`.
- **Invalidación aceptada (Q5):** logout general silencioso (framework: payload php → `json_decode` → null → sesión vacía, `Store.php:126-141`); sin migración ni truncate de `sessions` (filas ilegibles inofensivas hasta sweep, se pisan al re-login).
- **7º check HARD (Q6):** `ops:verify-deploy` valida el config runtime — evidencia del flip de producción, patrón 5.9/5.20.
- **Guard doble (Q7):** (a) regla PHPStan semántica exacta (Larastan infiere `$context->user` como `User`) + (b) arch-test allowlist patrón `SeedersArchTest`/`ControllersLineLimitArchTest`.
- **Cobertura de formas (Q11):** `put`/`push`/`flash`/`now`/`session([...])` + `RedirectResponse::with()` — flash data es data de sesión.
- **Estrictitud (Q12):** top-level `scalar|array|null`; `mixed` rechazado (obliga cast/PHPDoc shape — higiene alineada con el estilo defensivo ya existente, p. ej. `is_string(session('platform'))` en `PaymentController:103`); arrays sin recursión (los de gateways se arman desde `$request->input()`); **cero baseline** (regla single-use del repo).
- **Layout de la regla (Q13):** `phpstan/Rules/` (tooling fuera de `app/`), `scanDirectories` nativo de PHPStan como autoloading (sin composer autoload nuevo).
- **Sin round-trip del framework en tests (Q8):** la suite entera corre bajo `json` tras el flip — es el test.
- **Naming (Q10):** `spec-session-serialization-json-fase5.md`, template estándar.
- **Framework:** PHPUnit 12 (atributos `#[Group]`), `RefreshDatabase` en los feature, unitarios sin DB salvo indicación.
- **DB:** MySQL `dbrentpro_test`.

---

## 6. Catálogo de casos

### 6.1 Contrato escalar + tooling (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | config flip literal | `require config/session.php` fresco (sin `$_ENV` mutada) | evaluar el archivo | `['serialization'] === 'json'` y el archivo no introduce `env()` nueva |
| U2 | `PaymentState` escalar | sesión con `payment_type`/`plan_id`/`user_id`/`gateway_id`/`amount`/`payment_info` scalares | `PaymentState::fromSession()` | props espejo scalares; `planId === (int)` esperado |
| U3 | basura en sesión tolerada | `session(['plan_id' => ['array'], 'user_id' => []])` | `PaymentState::fromSession()` | props basura → `null` (tratadas como ausentes, sin crash) |
| U4 | arch-test allowlist congelada | árbol `app/` actual (14 escritores) | escanear formas `put/push/flash/now/Session::/session([` | exactamente los 14 archivos de la allowlist contienen writes; cualquier otro archivo ⇒ fail |
| U5 | check puro json | `VerifyDeploy::sessionSerializationPasses('json')` | llamada directa | `true` |
| U6 | check puro php | `VerifyDeploy::sessionSerializationPasses('php')` y `('')` | llamada directa | `false` |

### 6.2 Flujo de pagos (`P`) — `PaymentFlowSmokeTest` + `BalanceForceFillTest`

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| P1 | stash escalar en `payment()` | landlord + plan + gateway (stub), sesión `payment_type`/`user_id` | `POST /payments/{plan_id}/{gateway_id}` | redirect al stub; `session('plan_id') === $plan->id` (int), `session('gateway_id')`, `session('amount')`; **sin claves `user`/`plan`** (`assertSessionMissing`) |
| P2 | success subscription rehidratado | `withSession` scalares (`payment_type=subscription`, `plan_id`, `user_id`, `amount`, `gateway_id`, `payment_info=[status=>1]`), superadmin existe | `GET /payment/success` (actingAs landlord) | redirect success; `plan_subscribes` paid; `transactions` credit; `will_expire` +30d |
| P3 | success security_deposit rehidratado | deposit + landlord balance 0, `withSession` scalares (`payment_type=security_deposit`, `plan_id`, `user_id`, `payment_info`) | `GET /payment/success` (actingAs tenant del deposit) | redirect success; deposit `status=paid`; landlord balance forceFill acreditado; `transactions` debit |
| P4 | success rent_payment rehidratado (**nuevo, hoy sin cobertura**) | rentPayment + landlord, `withSession` scalares (`payment_type=rent_payment`, `plan_id`, `user_id`, `payment_info`) | `GET /payment/success` (actingAs tenant del rent) | redirect success; `rent_payments.payment_status=paid`; landlord balance acreditado; `transactions` debit |
| P5 | identity mismatch 403 (check simplificado) | actingAs landlordA, `withSession` con `user_id` de landlordB | `GET /payment/success` | 403 |
| P6 | idempotencia con rehidratación | como P2, primer GET procesado | segundo `GET /payment/success` | redirect success; sin segunda fila en `plan_subscribes`/`transactions` |
| P7 | forget post-success | P2 ejecutado | inspección de sesión | sin `plan_id`/`user_id`/`gateway_id`/`amount`/`payment_info` |
| P8 | guest redirect login | `withSession` scalares, sin auth | `GET /payment/success` | redirect `/login` |
| P9 | sin `payment_info` → failed | `withSession` scalares sin `payment_info` | `GET /payment/success` | redirect order.status failed; sin fila en `plan_subscribes` |

### 6.3 Comando (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | `ops:verify-deploy` reporta session serialization | config runtime `session.serialization=json` | `Artisan::call('ops:verify-deploy')` | tabla con fila del check en PASS (HARD); con `config(['session.serialization' => 'php'])` runtime → fila FAIL (patrón `VerifyDeployTest` F1–F4) |

### Tests de caracterización

Ninguno: no hay bug vigente que se espere romper — el fix ES este spec. Los 6 bloques `withSession` migrados no caracterizan bug; migran contrato (P2–P9).

**Cambio de comportamiento intencional (documentado, no caracterización):** hoy una sesión crafteda con `payment_type` desconocido llega a `Success` sin escritura alguna (los 3 `if` de `success()` no matchean y el flujo comitea vacío); post-refactor el `match` con `default => null` la rechaza como `InvalidCredentials` — endurecimiento, no regresión.

---

## 7. Criterios de aceptación (Definition of Done)

- [ ] `config/session.php`: exactamente +1 key `'serialization' => 'json'` (diff de 1 línea + comentario); sin `env()` nueva; resto del archivo intacto (Q1).
- [ ] `PaymentController`: put `plan_id` escalar; puts `user`/`plan` eliminados; forget lists sin `plan`/`user`; identity check a `$state->userId`.
- [ ] `PaymentState`: sin props `plan`/`user` ni branches de modelo; docblock actualizado al contrato escalar.
- [ ] `PaymentService::success`: rehidratación única (`findOrFail` por `planId`/`userId`); sin lecturas de modelo desde `PaymentState`.
- [ ] Micro-fixes de tipado: `platform` (PaymentController:34) y `secret_key` (TapPayment:76) — y cualquier otro `mixed` que la regla reporte al activarse (0 restantes).
- [ ] `phpstan/Rules/SessionPayloadRule.php` + `phpstan/rules.neon` + include en `phpstan.neon.dist`; `composer analyse` **verde con la regla activa y 0 entradas nuevas al baseline** (regla single-use).
- [ ] `tests/Unit/Session/SessionWritersArchTest.php` verde; allowlist = los 14 archivos exactos (§6.1 U4).
- [ ] `ops:verify-deploy`: 7º check HARD presente; U5/U6 + F1 en verde.
- [ ] U1–U6, P1–P9, F1 en verde; suite completa **569 passed (561 + 8)** — verificar contra salida real de `php artisan test`.
- [ ] `composer lint` limpio (incluido `phpstan/Rules/`); `composer audit --locked` → 0 advisories.
- [ ] Los 3 payment types cruzan `success()` por rehidratación (P2/P3/P4).
- [ ] Docs: `AGENTS.md` gotcha (sesión `json`: solo scalares/arrays; flip invalida sesiones) + conteo 569; `README.md`/`tests/README.md` conteos + mini-sección; `.agents/README.md` fase5 26 (total 71).
- [ ] Cierres locales `.analisis/` (gitignored): `plan-accion.md` fila 5.21 → Mitigado (2026-11-23) + métrica ✅.
- [ ] Rama `spec/session-serialization-json-fase5-impl` + PR (squash, 1 aprobación + CI 4/4); sin push a `main`.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Invalidación de todas las sesiones activas (logout general) | **Medio** (aceptado por decisor 5.15 — hoy no hay usuarios activos; flips 5.5/5.6 ya pendientes de evidencia) | Framework no lanza excepción (`Store.php:126-141`: payload php → `json_decode` → null → sesión vacía); coste documentado aquí y en fila 5.21 |
| False positives de la regla en código template (gateways heredados) | Medio | Micro-fixes puntuales cast/`is_string` (2-3 verificados); prohibido el baseline; presupuesto = solo writes `mixed` existentes |
| `config:cache` congela el config leído por `ops:verify-deploy` | Bajo | El check es runtime-evidencia: tras flip+re-cache en producción, la salida del comando es la prueba (patrón 5.9) |
| Payload json algo mayor que php-serialize (column `payload` TEXT) | Bajo | Payloads chicos (ids, strings de traducción, arrays de credentials); irrelevante a escala actual |
| Drift de la allowlist del arch-test (renombres/moves) | Bajo | El test falla ruidosamente — intencional: forzar actualización consciente |
| Despliegues con sesiones Redis/file de otro entorno | Bajo | Driver default `database` (5.5); cualquier driver sufre la misma invalidación al leer payloads viejos |

**Residual operacional (Q6):** flip + `config:cache` en producción pendientes de evidencia vía `ops:verify-deploy` (captura bajo `.analisis/`, patrón 5.9/5.5/5.6/5.20).

**Pendiente de decisión:** ninguno (Q1–Q13 del grilling 2026-11-23).

---

## 9. Estimación

- Refactor (`PaymentController`/`PaymentState`/`PaymentService`) + flip config + micro-fixes: 0.25 día.
- Guards: custom PHPStan rule + arch-test + activación/estabilización de `composer analyse`: 0.5 día.
- Tests: U1–U6 + migración de bloques + P4 nuevo + F1 + corrida focal: 0.25 día.
- Docs + conteos + cierres documentales + PR: 0.25 día.
- Total: ~1.25 días.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.21 (l.126); métricas l.181 (residual 5.20 → fila 5.21)
- `.agents/spec-impl/spec-laravel13-advisories-fase5.md` → §5/§8 (decisor cerró "Pendiente de decisión" 2026-11-22) y D2 (`serializable_classes: false` — paralelo de cache)
- `.agents/spec-impl/spec-session-database-secure-fase5.md` → driver `database` (5.5), patrón de residual y de U sobre config fresco
- `.agents/spec-impl/spec-php84-bump-fase5.md` → patrón del 6º check HARD (`phpFloorPasses`) que este spec replica como 7º
- `config/session.php` (sin key `serialization` hoy); `vendor/laravel/framework/src/Illuminate/Session/Store.php:126-141/181-192` (read json/write json/`marshalErrorBag`)
- Skeleton L13 → `laravel/laravel` 13.x `config/session.php` (`'serialization' => 'json'` literal, sin env); docs upgrade guide (invalida sesiones)
- `app/Http/Controllers/PaymentController.php` → ll.60/73/88/93-99/116-131
- `app/Support/PaymentState.php` → ll.46-51/86-93; `app/Services/PaymentService.php` → ll.219-234/258-262
- `.agents/spec/spec-conventions.md` · `.agents/spec/spec-template.md`
- `.agents/skills/testing-conventions.md` · `.agents/skills/workflow-ramas.md`
- `AGENTS.md` → "Payments architecture", "Gotchas", "Tooling"

---

### Notas de implementación

1. **Diff de `payment()` (stash y forget):**
   ```php
   session()->put('fund_callback.success_url', '/payment/success');
   session()->put('fund_callback.cancel_url', '/payment/failed');

   session()->put('plan_id', (int) $plan_id);   // antes: put('user', $context->user) + put('plan', $context->plan)
   session()->put('gateway_id', $context->gateway->id);
   session()->put('amount', $context->amount);
   ```
   Forget lists (ll.122/131): `['plan_id', 'user_id', 'gateway_id', 'amount', 'payment_info', 'payment_processed']` (sin `plan`/`user`). La rama manual (l.88, `forget('user_id')`) queda como está: su comportamiento con `plan_id` residual de signup es preexistente y observacionalmente igual.

2. **`PaymentState` nuevo shape** (props: `paymentType`, `planId`, `userId`, `amount`, `gatewayId`, `paymentInfo`, `sessionId`, `stripeSessionId`):
   ```php
   public static function fromSession(?string $stripeSessionId = null): self
   {
       return new self(
           paymentType: self::sessionScalar('payment_type'),
           planId: self::sessionScalar('plan_id'),
           userId: self::sessionScalar('user_id'),
           amount: session('amount'),
           gatewayId: self::sessionScalar('gateway_id'),
           paymentInfo: self::sessionArray('payment_info'),
           sessionId: session()->getId(),
           stripeSessionId: $stripeSessionId,
       );
   }
   ```
   `sessionScalar`/`sessionArray` se conservan; `sessionModel` y las lecturas `instanceof` se eliminan. Docblock: el contrato de sesión es 100% escalar y cualquier valor no-escalar se trata como ausente.

3. **`PaymentService::success`** — rehidratación única:
   ```php
   if (! $state->planId || ! $state->paymentType) {
       return PaymentOutcome::InvalidCredentials;
   }

   $plan = match ($state->paymentType) {
       'security_deposit' => SecurityDeposit::findOrFail($state->planId),
       'rent_payment' => RentPayment::findOrFail($state->planId),
       'subscription' => Plan::findOrFail($state->planId),
       default => null,
   };

   if (! $plan) {
       return PaymentOutcome::InvalidCredentials;
   }
   ```
   `$user` se resuelve en el punto del guard actual (l.260) con `User::findOrFail($state->userId)` — `ModelNotFoundException` cae en el `catch (\Exception)` existente → `SomethingWrong`, observable idéntico al `RuntimeException` actual. Identity check del controlador: `if ($state->userId && auth()->id() !== (int) $state->userId) abort(403);`.

4. **Custom PHPStan rule** — esqueleto:
   - `phpstan/rules.neon`:
     ```neon
     parameters:
         scanDirectories:
             - phpstan/Rules
     services:
         -
             class: SessionPayloadRule
             tags: [phpstan.rules.rule]
     ```
   - `phpstan/Rules/SessionPayloadRule.php`: matcher sobre `StaticCall` (`Session::put/flash/push/now`) y `MethodCall` (`put/push/flash/now` sobre `Illuminate\Session\Store` o facade, `with` sobre `Illuminate\Http\RedirectResponse`), y la forma array `session([...])`. Tipo del valor (resolved via `TypeSpecifier`/`NodeScopeResolver`): aceptado si es subtype de `scalar|array|null`; `MixedType` y objetos/enums → error `Session payload must be scalar|array under serialization=json (session key "%s")`. `null` aceptado.
   - `phpstan.neon.dist` → `includes:` + `- phpstan/rules.neon`.
   - Verificación de arranque: `composer analyse` sobre `app/` con la regla activa → los 2-3 `mixed` conocidos (platform, secret_key, y cualquiera adicional) se corrigen en el mismo PR; **prohibido** tocar `phpstan-baseline.neon`.

5. **Arch-test** (`tests/Unit/Session/SessionWritersArchTest.php`): escanear `app/` recursivo; un archivo "escribe sesión" si contiene alguna de: `session()->put(`, `Session::put(`, `->flash(`, `->now(`, `session([`). Assert: conjunto de archivos detectados === allowlist de 14:
   `Auth/SignupController`, `PaymentController`, `Middleware/SetLocale`, `Library/Billplz`, `Library/FedaPayGateway`, `Library/Thawani`, `Library/Paystack`, `Library/Razorpay`, `Library/Toyyibpay`, `Library/Flutterwave`, `Library/Mollie`, `Library/StripeGateway`, `Library/TapPayment`, `Library/Instamojo`. (El helper `session([` de `SetLocale` entra por la forma array.) `with()` NO se escanea aquí (vive en todos los controladores; lo cubre la regla PHPStan por tipo).

6. **7º check HARD** (`VerifyDeploy`): helper puro `public static function sessionSerializationPasses(?string $value): bool` → `'json' === $value`; consumido con `config('session.serialization')`. Fila en la tabla con etiqueta `session serialization=json`, sección HARD (misma mecánica del check php floor: 5.20). F1 usa override runtime `config(['session.serialization' => 'php'])` para la rama FAIL, patrón `VerifyDeployTest`.

7. **Migración de los 6 bloques `withSession`** — reemplazo textual uniforme:
   ```php
   // antes
   'plan' => $plan, 'user' => $landlord,
   // después
   'plan_id' => $plan->id, 'user_id' => $landlord->id,
   ```
   Aplica a `PaymentFlowSmokeTest` (ll.164-165/368-369/387-388/407-408/427-428) y `BalanceForceFillTest` (ll.56-57). Aserciones 148-149 → `assertEquals($plan->id, session('plan_id'))` + `assertSessionMissing(['user', 'plan'])`; 475-476 → assert de null/ausencia de las claves scalares tras forget.

8. **Suite/conteos:** 561 (base 5.20) + U1–U6 (config 1, PaymentState 2, arch-test 1, verify-deploy 2) + P4 (rent_payment nuevo) + F1 (VerifyDeployTest) = **569 esperados** — verificar contra salida real y actualizar `AGENTS.md`/`README.md`/`tests/README.md` antes de cerrar el PR.

9. **Redacciones documentales (patrón 5.5):**
   - `plan-accion.md` fila 5.21 → "**Mitigado (2026-11-23):** spec `spec-session-serialization-json-fase5` — stash de `PaymentController` a `plan_id` escalar + rehidratación única en `PaymentService::success` (`PaymentState` 100% escalar); flip `'serialization' => 'json'` (literal, sin válvula; invalida sesiones — sin usuarios activos); guard doble (custom PHPStan rule `phpstan/Rules/` + arch-test allowlist 14) y 7º check HARD en `ops:verify-deploy`; U1–U6 + P1–P9 + F1, suite 569. Residual: flip + re-cache de producción pendientes de evidencia (patrón 5.9)."
   - `plan-accion.md` métricas → "✅ Session `serialization: json` + contrato escalar de sesión del flujo de pagos (Fase 5 / 5.21 — spec-session-serialization-json-fase5, 2026-11-23; residual operativo: `.env`/config de producción)".
   - `AGENTS.md` Gotchas → nueva viñeta: "**Sesión bajo `serialization: json`:** todo payload de sesión debe ser scalar/array (regla PHPStan `SessionPayloadRule` + arch-test `SessionWritersArchTest`); prohibido stashear modelos — rehidratar por ID (patrón `PaymentService::success`). El flip invalida sesiones viejas."
   - `tests/README.md` → árbol (`Unit/Session/`, `Unit/Config/` +1, `Unit/Console/` +1) + mini-sección "## Session serialization (Fase 5 / 5.21)".

10. **Verificación rápida:**
    ```bash
    composer analyse   # regla activa, 0 reportes nuevos, baseline intacto
    php artisan test tests/Unit/Session tests/Unit/Config tests/Unit/Support/PaymentStateTest.php tests/Feature/Payment tests/Feature/Console
    composer lint && php artisan test
    php artisan ops:verify-deploy   # fila session serialization PASS
    ```
