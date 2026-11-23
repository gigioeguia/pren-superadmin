---
id: spec-tx-legadas-services-fase5
titulo: "Migrar las 9 tx legadas de controladores a services (Fase 5 / 5.18)"
estado: Implementado
fecha: 2026-11-22
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.18; decisión D5 de .agents/spec-impl/spec-controladores-residuales-fase5.md §12; premisa inexacta del paraguas spec-unificar-capas-services-fase5.md §5.2)
cobertura: "9 tx del allowlist U2 → 3 services nuevos (WithdrawService, ProfileService, RoleService) + 2 extensiones (SecurityDepositService::offlinePayment, LandlordService método compuesto de registration); TX_ALLOWLIST → 0 con guard U2 absoluto; verbatim + 4 desviaciones pre-declaradas"
---

# Spec — Migrar las 9 tx legadas de controladores a services

> Resumen de una línea: las 9 transacciones DB que aún viven en controladores (allowlist U2) pasan a ser propiedad de services siguiendo el patrón 4.2/5.13, dejando 0 tx en controladores y el arch-test como regla absoluta permanente.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.18). **Patrón de referencia:** `spec-unificar-capas-services-fase5.md` §5.3 (patrón heredado de Fase 4 sin renegociar) y §5.7 (tests de contrato HTTP previos). **Paraguas:** `spec-unificar-capas-services-fase5.md` (esta deuda nació OUT de su §2 — ver D5 del hijo 6 §12).

---

## 1. Objetivo

Las 9 tx legadas de `TX_ALLOWLIST` (`ControllersLineLimitArchTest`) dejan de declararse en controladores y pasan a services; `TX_ALLOWLIST` se elimina y U2 queda como escaneo absoluto bidireccional (0 tx en `app/Http/Controllers/`, sin excepciones). Contrato observable intacto (verbatim) salvo 4 desviaciones pre-declaradas (§5.2).

---

## 2. Alcance

### IN — los 9 slices, en orden de commits (dinero primero)

| # | Controlador (líneas hoy) | Tx (método; beginTransaction/commits/rollback) | Destino |
|---|---|---|---|
| 1 | Api/WithdrawController (140) | `store`; :71 / :89 / :105 | `WithdrawService` (nuevo) |
| 2 | Landlord/WithdrawController (168) | `store`; :69 / :85 / :100 | `WithdrawService` |
| 3 | Admin/WithdrawRequestController (180) | `approve`; :89 / :109 / :119 | `WithdrawService` |
| 4 | Admin/ProfileController (105) | `update`; :68 / :84 / :97 | `ProfileService` (nuevo) |
| 5 | Landlord/ProfileController (151) | `update`; :85 / :130 / :143 | `ProfileService` |
| 6 | Api/UserController (185) | `profileSetUp`; :81 / :130 / :143 | `ProfileService` |
| 7 | Api/SecurityDepositController (143) | `offline_payment`; :65 / :93 y :125 / :131 | `SecurityDepositService` (extensión) |
| 8 | Admin/RoleController (109) | `store`; `DB::transaction` :52–:58 | `RoleService` (nuevo) |
| 9 | Api/Auth/RegistrationController (166) | `maanRegistration`; :59 / :75 y :88 / :92 | `LandlordService` (extensión: método compuesto) |

También IN: las 4 desviaciones pre-declaradas (§5.2: `reject` sin tx, `Role::update` sin tx, ownership Landlord/Profile, reglas de validación rotas), la simplificación del arch-test (U1) y los contract tests F1–F15.

### OUT

- Trío bare de `submitOtp` web (sin tx, sin entrada en allowlist; hijo 4 §11 lo declara intencional) — **intacto**.
- Unificar la divergencia de notificaciones del trío Withdraw (se preserva; fila propia si algún día importa).
- Migración completa de los controladores: 5.18 mueve **la tx y su glue mínimo**; los controladores conservan validación, autorización, sesión y respuesta. La unificación 3-capas total de withdraw/profile/role queda como está.
- Hooks `HasTableActions` de los controladores afectados (no se tocan).
- Filas 5.17 (rutas muertas), 5.19 (moderación de comentarios), 5.21 (session json + modelos en sesión de `PaymentController`), 5.22 (filter HTML vs JSON) y lateral `tenants.show` sin gate.
- Renames o validaciones más allá de los laterales declarados en el slice 5.

### Cobertura objetivo

- 9/9 tx migradas; `TX_ALLOWLIST` eliminada; 0 `DB::beginTransaction` / `DB::transaction(` en `app/Http/Controllers/`.
- ≥ 1 contract test HTTP **previo** por slice (happy path + efectos DB exactos); los 4 flujos hoy sin cobertura HTTP (Admin/WithdrawRequest approve+reject, Landlord/Profile update, Admin/Role store/update, Api/SecurityDeposit `offline_payment`) reciben su primera red.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Allowlist U2 con guard bidireccional | Existe (hijo 6 / D5): tx nueva en cualquier controlador rompe CI; una entrada solo sale cuando su controlador ya no declara tx | Ninguna — es el mecanismo de salida por slice |
| P2 | Suite en verde sobre `main` | 535/535 tras 5.17 (PR #68); PHPStan nivel 5 + Pint | Ninguna |
| P3 | Services base a extender | `SecurityDepositService` existe (`offline_payment` hoy lo bypassa; solo `index` usa `listForChannel`); `LandlordService` existe (`createAccount` :308–333, `activateFreePlan` :340–382) | Extender; no crear duplicados |
| P4 | Verificar ownership en `Landlord/Profile::update` | **Desconocido** — `User::findOrFail($id)` con route-id (:42), cero tests | Verificar ANTES del move del slice 5; si no hay check → desviación D-IDOR (patrón Seg-29: ignorar route-id, target `auth()->user()`); si ya existe → congelarlo y omitir el fix |
| P5 | Red de contract tests para los 4 flujos sin cobertura | Falta | Escribir contract tests previos por slice (§5.5, piso mínimo) |
| P6 | Convención 4.8 (tx sin E/S externa) | Cumplida por las 9 tx hoy: uploads antes de abrir; Mail/`deleteOldFile` tras commit; la notificación de Api/Withdraw (:87) es escritura DB (canal `database`), no E/S externa | Ninguna — la excepción 4.8 pre-declarada en la sesión de diseño queda **vacía** |

---

## 4. Estructura propuesta

```
app/
├── Services/
│   ├── WithdrawService.php          # nuevo: store (Api/Landlord unificados), approve, reject
│   ├── ProfileService.php           # nuevo: update (Admin/Landlord), profileSetUp (Api)
│   ├── RoleService.php              # nuevo: store, update
│   ├── SecurityDepositService.php   # extensión: + offlinePayment(...)
│   └── LandlordService.php          # extensión: + createAccountWithFreePlan(...) dueño de la tx
└── Http/Controllers/
    ├── Api/WithdrawController.php            # sin tx
    ├── Landlord/WithdrawController.php       # sin tx
    ├── Admin/WithdrawRequestController.php  # sin tx (approve + reject)
    ├── Admin/ProfileController.php          # sin tx
    ├── Landlord/ProfileController.php       # sin tx (+ fix ownership/reglas, §5.2)
    ├── Api/UserController.php               # sin tx
    ├── Api/SecurityDepositController.php    # sin tx
    ├── Admin/RoleController.php             # sin tx (store + update)
    └── Api/Auth/RegistrationController.php  # sin tx (supersede hijo 4 §11)

tests/
├── Feature/...                                        # contract tests por slice (previos al move)
└── Unit/Controllers/ControllersLineLimitArchTest.php # TX_ALLOWLIST eliminada; U2 escaneo absoluto
```

**Contratos (propuesta; firmas finales ajustadas en impl. Service agnóstico de HTTP: no lee `auth()`/`request()`, recibe `User`/datos por parámetro):**

```php
// WithdrawService (nuevo)
public function store(User $user, array $data, Channel $channel): Withdraw;        // Api + Landlord unificados; divergencia: notifica solo canal Api (in-tx, escritura DB)
public function approve(User $admin, Withdraw $withdraw, array $data): Transaction; // tx: Transaction::create + withdraw update; notifica tras commit
public function reject(User $admin, Withdraw $withdraw, ?string $reason): void;    // D-FIX-reject: tx nueva envolviendo status + refund de balance

// ProfileService (nuevo)
public function update(User $user, array $data, Channel $channel): void;            // Admin (password condicional) + Landlord (mykad, forceFill landlord_id) — divergencias por canal
public function profileSetUp(User $user, array $data): void;                       // Api (is_setupped, profile_type)

// RoleService (nuevo)
public function store(array $data): Role;                                          // tx: Role::create + permissions sync
public function update(Role $role, array $data): void;                             // D-FIX-role-update: tx nueva envolviendo role update + permissions sync

// SecurityDepositService (extensión)
public function offlinePayment(User $tenant, array $data): array;                  // tx: ramas subscription (PlanSubscribe::create) y security_deposit/rent_payment (update + gateway)

// LandlordService (extensión)
public function createAccountWithFreePlan(array $data, Channel $channel): User;    // dueño de la tx: envuelve createAccount + activateFreePlan en una DB::transaction; submitOtp web intacto
```

**Divergencias preservadas tal cual (documentadas en el service):**

| Divergencia | Api | Landlord | Admin |
|---|---|---|---|
| Notificación en `store` withdraw | sí, dentro de la tx (:87) | no | n/a (`approve` notifica tras commit :111) |
| Campos profile | `is_setupped`, `profile_type` | mykad ×2, `forceFill(landlord_id)`, password | password condicional (`Hash::make` merge) |
| Target profile | `auth()->user()` | route-id hoy → D-IDOR: `auth()->user()` | route-id ignorado (Seg-29, 5.11) |
| Tx registration | `maanRegistration` (trío pre-OTP) | n/a | n/a |

---

## 5. Decisiones técnicas

No negociables salvo volver el spec a `Borrador`. Cerradas con el decisor el 2026-11-22 (sesión de grilling):

1. **Verbatim**: contrato observable intacto (status, shapes, redirects, efectos DB, mensajes). La excepción 4.8 pre-declarada quedó vacía: las 9 tx cumplen hoy (P6).
2. **Desviaciones pre-declaradas** (numeradas D1–D4 al implementar; security-positive, precedente 5.17 Q7/D1):
   - **D-FIX-reject**: `Admin/WithdrawRequest::reject` hace 2 escrituras de dinero sin tx (status :61–64 + refund `increment('balance')` :66) → tx en `WithdrawService::reject`. Happy path invariante (F5); solo cambia la semántica de crash.
   - **D-FIX-role-update**: `Admin/Role::update` escribe role :87–89 + `permissions()->sync` :91 sin tx → tx en `RoleService::update`.
   - **D-IDOR** (condicional a P4): si no existe check de ownership en `Landlord/Profile::update`, patrón Seg-29 (ignorar route-id, target `auth()->user()`); si se confirma, se registra como **Seg-31** en `.analisis/seguridad.md`.
   - **D-FIX-validation**: reglas rotas de Landlord/Profile — `'required|string:255px'` (:32) → `'required|string|max:255'`; clave `'email'` duplicada (:33/:36, hoy gana la segunda) consolidada. Diff exacto verificado en impl.
3. **Supersede hijo 4 §11**: la tx de `maanRegistration` pasa a ser propiedad de `LandlordService` (método compuesto que envuelve `createAccount` + `activateFreePlan` en una sola `DB::transaction`). La **atomicidad User+trío queda intacta** — el "como hoy" de hijo 4 era la restricción de alcance de esa migración verbatim, no un diseño permanente. `submitOtp` web (trío bare) intacto y OUT.
4. **Divergencia de notificaciones del trío Withdraw preservada**: Api notifica dentro de la tx (escritura DB), Landlord no notifica, Admin notifica tras commit. Regla por canal documentada en el service; unificarla exige fila propia.
5. **Secuencia por slice** (orden = commits, dinero primero): (a) contract test HTTP previo congela el contrato actual → (b) move de la tx al service + desviaciones del slice → (c) contract test en verde; los casos afectados por una desviación se actualizan en el mismo commit con nota de la desviación.
6. **Contract tests, no caracterizaciones** (paraguas §5.7): sin `@group characterization` (reservado a bugs con fase de ruptura futura); los bugs conocidos se corrigen in-slice (2), no se congelan.
7. **Patrón heredado de Fase 4/5.13 sin renegociar**: service agnóstico de HTTP, `Channel` enum, tx solo escrituras con orden 4.8, controladores < 200 (ya cumplen), Api → Landlord → Admin dentro de cada entidad.
8. **Estrategia**: rama única `spec/tx-legadas-services-fase5-impl`, un PR, commits por slice, dinero primero y registration al final (reusa F11a/b como red).
9. **PHPStan**: nivel 5, sin adiciones netas al baseline; las entradas que migren con el código se retiran con justificación en el PR (regla de baseline único vigente).

---

## 6. Catálogo de casos

Prefijo `F` (HTTP feature transversal) + `A` (reuso auth API) + `U` (unit/arch). Todos los F son contract tests **previos al move** (congelan el comportamiento actual); los marcados **[D-fix]** se actualizan con la desviación en el mismo commit del move.

### 6.1 Withdraw — dinero (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Api store happy | Landlord autenticado (token), `WithdrawMethod` con charge fixed | POST `/api/v1/landlord/withdraw` payload válido | Respuesta HTTP como hoy (congelada); `Withdraw` creado (`amount`, `charge`, `user_method_id`); `balance` debitado amount+charge; fila en `notifications` **presente** (divergencia Api) |
| F2 | Landlord store happy | Landlord en sesión, método válido | POST `/landlord/withdraw` payload válido | Respuesta como hoy; `Withdraw` creado; `balance` debitado; fila en `notifications` **ausente** (divergencia Landlord) |
| F3 | Store bajo el mínimo | Método con min > amount | POST withdraw (web y api) | Rechazo como hoy (validación congelada); 0 filas en `withdraws`; balance intacto |
| F4 | Admin approve happy | Admin con permiso, `Withdraw` pending | POST approve | Respuesta como hoy; `Transaction` creada (type=debit, payment_type=withdraw, amount, charge); withdraw `status=approved`; notificación **tras commit**; balance intacto |
| F5 | Admin reject happy | Admin, withdraw pending | POST reject con reason | Respuesta como hoy; withdraw `status=rejected` + `reason`; `balance` reembolsado (`increment`) — **[D-FIX-reject: ambas escrituras ahora atómicas]** |
| F6 | Approve/reject de withdraw ajeno | Policy `Withdraw` (Fase 2 / 2.1) activa | POST approve/reject sobre withdraw de otro landlord | Bloqueado como hoy (política congelada) |

### 6.2 Profile (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F7 | Admin update own | Admin en sesión | PUT `/admin/profile/{id}` | `AdminProfileSelfTest` (g4/g5) existente sigue en verde; user + user_details actualizados; `deleteOldFile` tras commit |
| F8 | Landlord update own | Landlord en sesión | PUT `/landlord/profile/{id}` (id propio) | Respuesta como hoy; user + user_details actualizados; mykad persistido |
| F9 | Landlord update foreign id | Landlord en sesión, id de otro usuario en la ruta | PUT `/landlord/profile/{otro}` | **[D-IDOR]** actualiza el propio, el ajeno queda intacto (patrón Seg-29/g5); si P4 revela check existente, se congela el check actual |
| F10 | Landlord name > 255 | Landlord en sesión | PUT con name de 256 chars | **[D-FIX-validation]** rechazo por `max:255` (hoy pasa: regla rota `string:255px` congelada primero) |
| F11 | Api profileSetUp happy | Usuario con token | POST `/api/v1/profile-setup` válido | Respuesta como hoy; `is_setupped=1`; user_details creado/actualizado; `ProfileSetUpValidationTest` existente sigue en verde |

### 6.3 SecurityDeposit offline (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F12 | Rama subscription | Tenant con token, receipt subido, `payment_type=subscription` | POST `/api/v1/offline-payment/{gateway_id}/{deposit_id}` | Respuesta como hoy; `PlanSubscribe` creada; receipt subido **antes** de abrir tx; `session('user_id')` olvidada tras commit |
| F13 | Rama security_deposit/rent_payment | Tenant, `payment_type` deposit/rent | POST offline-payment | Respuesta como hoy; fila actualizada (`payment_date`, `status=pending`, `gateway_id`, `payment_data`); Gate f12 existente sigue en verde |

### 6.4 Role (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F14 | Store happy | Admin con permiso | POST create role + permissions | Respuesta como hoy; `Role` creada + permissions sincronizados (tx) |
| F15 | Update happy | Role existente | POST update role + permissions | Respuesta como hoy; role + sync aplicados — **[D-FIX-role-update: ahora atómicos]** |

### 6.5 Registration (`A` — reuso, sin casos nuevos)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| A1 | Reuso F11a/b | Existentes en `tests/Feature/Auth/RegistrationAccountHttpTest.php` | POST `/api/v1/sign-up` | F11a (OTP-off: user + trío) y F11b (OTP-on: trío pre-verificación) siguen en verde tras el move — la atomicidad User+trío es el contrato congelado |

### 6.6 Arch (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | U2 absoluto | 9/9 controladores sin tx | `ControllersLineLimitArchTest` | `TX_ALLOWLIST` eliminada; escaneo absoluto de `app/Http/Controllers/` sin excepciones; falla ante cualquier `DB::beginTransaction`/`DB::transaction(` |
| U2 | U1 intacto | Post-migración | `ControllersLineLimitArchTest` | Los 9 controladores < 200 líneas |
| U3 | Mass-assignment | Escritores de `User` tocados (slices 4–6, 9) | `UserMassAssignmentArchTest` | Sin `$request->except(` en los archivos tocados; guard existente sigue en verde |
| U4 | Services agnósticos | 3 services nuevos + 2 extensiones | grep | 0 `request(`/`auth()` en los methods migrados de `app/Services/` |

### Tests de caracterización

Ninguno. Los bugs conocidos del inventario (reject sin tx, role update sin tx, IDOR condicional, reglas rotas) se corrigen in-slice como desviaciones (§5.2), no se congelan esperando una fase de ruptura.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `TX_ALLOWLIST` eliminada de `ControllersLineLimitArchTest`; U2 = escaneo absoluto bidireccional permanente (0 tx en `app/Http/Controllers/`, sin excepciones). **Evidencia:** commit `f93fa48` — constante y guard de allowlist fuera; docblock actualizado. Grep: `DB::beginTransaction|DB::transaction(` en `app/Http/Controllers/` → **0 matches**.
- [x] grep de no-regresión: 0 tx en controladores; `wc -l` pegado, todos < 200. **Evidencia:** Api/Withdraw **124**, Landlord/Withdraw **156**, Admin/WithdrawRequest **147**, Admin/Profile **97**, Landlord/Profile **133**, Api/User **157**, Api/SecurityDeposit **103**, Admin/Role **103**, Api/Auth/Registration **155**.
- [x] 9/9 tx con dueño en service. **Evidencia:** `WithdrawService` (store/approve/reject), `ProfileService` (update ×2 canales + profileSetUp), `RoleService` (store/update), `SecurityDepositService::offlinePayment`, `LandlordService::createAccountWithFreePlan` — `grep -l "DB::transaction\|DB::beginTransaction" app/Services/`.
- [x] Contract tests F1–F15 escritos **antes** de cada move y en verde tras cada move; casos `[D-fix]` actualizados en el commit de su desviación. **Evidencia:** commits de freeze previos a cada move (`4e1860b` dinero, `dad7161` profile, `3734b1e` offline, `59ff789` role); F9/F10 actualizados en `ed20caa` (mismo commit del fix).
- [x] Divergencia de notificaciones preservada y congelada por test. **Evidencia:** F1 notifica (in-tx) / F2 no notifica / F4 notifica tras commit — en verde.
- [x] P4 resuelto con evidencia: no existía check de ownership → D-IDOR aplicada. **Evidencia:** F9 actualizado (cuenta ajena intacta); **Seg-31** registrada en `.analisis/seguridad.md` (gemelo landlord de Seg-29).
- [x] Suite completa en verde con conteo exacto. **Evidencia:** `php artisan test` → **550 passed (6214 assertions)** — 535 baseline + 15 casos nuevos del spec.
- [x] `composer analyse` [OK] nivel 5; sin adiciones netas al baseline (entradas migradas retiradas con justificación en el PR). **Evidencia:** No errors; saneo **−4 entradas** en `5fe8fbf` (3 huérfanas: unknown-class ×2 + alwaysTrue en WithdrawRequestController, `Withdraw::$user` en Api/WithdrawController, dup `'email'` en Landlord/ProfileController; 3 counts ajustados a los usos que quedan). Código nuevo corregido en fuente (`Role::query()->create()`, relaciones tipadas explícitas), nunca al baseline.
- [x] `composer lint` passed. **Evidencia:** `{"tool":"pint","result":"passed"}`.
- [x] Documentación: AGENTS.md actualizado (regla absoluta 0 tx), nota de supersede en `spec-service-landlord-fase5.md` §11, fila 5.18 de plan-accion flip a Cerrado con evidencia (+ filas 5.23/5.24 para laterales L1/L2).
- [x] Rama única `spec/tx-legadas-services-fase5-impl`, commits por slice en el orden del §2 IN. **Evidencia:** `4e1860b` (freeze dinero) → `c681a83` (slice 1) → `96633ea` (2) → `17b81e4` (3) → `dad7161` (freeze profile) → `c31131b` (4) → `ed20caa` (5) → `03dad6c` (6) → `3734b1e` (freeze offline) → `dc75cc2` (7) → `59ff789` (freeze role) → `635fe64` (8) → `f93fa48` (9 + guard absoluto) → `5fe8fbf` (phpstan saneo).

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Registration es el flujo más sensible (post-Seg-4, trío pre-OTP) | alto | Va al final del orden; reusa F11a/b como contrato congelado; supersede de hijo 4 §11 documentado; la atomicidad User+trío no cambia de forma observable |
| IDOR landlord-profile no confirmado (P4) | alto | Verificar antes del move del slice 5; si ya hay check, D-IDOR se omite y se congela el check actual |
| `max:255` endurece una ruta viva (datos reales > 255) | bajo | F10 congela el rechazo; verificado contra dataset demo en impl |
| Mover código desplaza entradas del baseline PHPStan | medio | Entradas retiradas con justificación en el PR; sin adiciones netas (regla de baseline único) |
| Contract tests congelando shapes frágiles | medio | Congelar solo lo observable esencial (status, redirect/JSON shape, efectos DB), no HTML completo |
| `reject` atómico cambia semántica de crash | bajo | D-FIX-reject documentada; happy path invariante (F5) |

**Pendiente de decisión:** ninguna — las decisiones quedaron cerradas con el decisor el 2026-11-22 (verbatim + 4 desviaciones; unificar por entidad; allowlist → 0; dinero primero; un PR). La única contingencia (P4) ya tiene resolución condicional definida (§5.2 D-IDOR).

---

## 9. Estimación

- Contract tests previos (F1–F15 + reuso A1): 1.5–2 d
- Slices 1–3 dinero (`WithdrawService`): 1.5–2 d
- Slices 4–7 profile + security-deposit (`ProfileService` + extensión): 1–1.5 d
- Slices 8–9 role + registration (`RoleService` + extensión `LandlordService`): 1–1.5 d
- Guard U1/U2 + docs + suite/CI completa: 0.5–1 d
- **Total: 4.5–7 d**

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.18 (D5 del hijo 6; premisa §5.2 del paraguas)
- `.agents/spec-impl/spec-controladores-residuales-fase5.md` → §11 U2 (allowlist de 9) y §12 D4/D5 (origen de este spec)
- `.agents/spec-impl/spec-unificar-capas-services-fase5.md` → §2 (deuda OUT), §5.3 (patrón heredado), §5.7 (contract tests previos)
- `.agents/spec-impl/spec-service-landlord-fase5.md` → §11 (diseño intencional de la tx de registration — **supersede por este spec**)
- `.agents/spec/spec-conventions.md` y `.agents/spec/spec-template.md`
- `tests/Unit/Controllers/ControllersLineLimitArchTest.php` (allowlist U2 bidireccional a cerrar)
- `tests/Feature/Payment/BalanceForceFillTest.php` (p2/p3, débito de balance — base de F1/F2)
- `tests/Feature/Auth/RegistrationAccountHttpTest.php` (F11a/b — base de A1)
- `AGENTS.md` → "Gotchas: Transacciones sin E/S externa (Fase 4 / 4.8)" y "Controller layout (patrón Services)"

---

## 12. Implementación (2026-11-22) — rama `spec/tx-legadas-services-fase5-impl`

El spec pasó `Borrador → Aprobado` por decisión directa del decisor tras la sesión de grilling (sin auditoría `revisar-spec` intermedia). Desviaciones y hallazgos de la implementación:

**Desviaciones:**

| # | Desviación | Detalle |
|---|-----------|---------|
| D1 | D-FIX-reject (pre-declarada §5.2) | `reject` envuelto en tx (`WithdrawService::reject`); happy path invariante (F5). El else-404 inalcanzable (post-`findOrFail`) se retiró — sin cambio observable. |
| D2 | D-FIX-role-update (pre-declarada §5.2) | `RoleService::update` envuelve rename + resync en tx; happy path invariante (F15). La property muerta `$role` del controlador se retiró. |
| D3 | D-IDOR (pre-declarada, condicional a P4) | P4 confirmó que NO había check: aplicado el patrón Seg-29 (target `Auth::id()`, route-id ignorado, `unique` contra auth). **Seg-31** registrada en `.analisis/seguridad.md`. F9 congeló el hole vivo primero y se actualizó en el mismo commit del fix. |
| D4 | D-FIX-validation (pre-declarada §5.2, con errata de premisa) | `'string:255px'` → `'string|max:255'`; dup `'email'` consolidada (la 2ª regla era la vigente). **Errata del catálogo:** F10 decía "hoy pasa" — en realidad pasa la validación rota y muere en la DB (`1406 Data too long` → **500 con rollback**); el freeze real fue ese 500, post-fix 422 limpio. |
| D5 | Errata F6 | "Bloqueado como hoy" resultó ser **302 → login** (`AdminMiddleware` redirige en vez de abortar), no 403. F6 congela el observable real. |
| D6 | Firmas ajustadas (permitido por §4 "firmas finales ajustadas en impl") | `offlinePayment(User, array): void` (el controlador posee las 3 respuestas y el `session()->forget`); `WithdrawService::store(User, UserMethod, amount, charge, Channel)` en vez de `array $data`; `createAccountWithFreePlan(..., ?Plan $freePlan)` — el plan se resuelve ANTES de abrir la tx (4.8). |
| D7 | Lecturas reordenadas fuera de la tx (4.8-compliant) | El chequeo de balance de ambos withdraw stores y la resolución del método en `approve` ocurren antes de abrir la tx (antes vivían dentro). Sin cambio observable. |
| D8 | Limpieza muerta sin cambio observable | `$landlord` muerto en `approve`; `Role::create` → `Role::query()->create()` (tipado de spatie, semántica idéntica); relaciones mágicas del service resueltas vía calls tipadas (`account_info()->first()`...). |

**Observaciones empíricas congeladas:**

- **F13b (caso añadido al catálogo):** offline payment sobre depósito ajeno → `AuthorizationException` dentro del try → **500 'Payment failed'**, no 403 (el Gate sí bloquea la escritura). Congelado verbatim; su corrección (authorize fuera del try → 403) exigiría cambiar un contrato observable sin desviación aprobada.
- `notify_users()` = usuarios con `role in (superadmin, admin)` + ids dados — la fila de notificación del landlord es la que F1 congela.
- `Auth::login` + `createToken` permanecen **dentro** del try del controlador en `maanRegistration` — un fallo de token conserva el 403 verbatim (no un 500 del handler).
- `submitOtp` web (trío bare) intacto, como declaró hijo 4 y confirmó §2 OUT.

**Hallazgos laterales (follow-up fuera de este slice):**

| # | Hallazgo | Tratamiento |
|---|----------|-------------|
| L1 | `WithdrawService::store` (Api y Landlord) resuelve `UserMethod::findOrFail($request->user_method_id)` **sin ownership** — un landlord puede retirar referenciando el método bancario de otro usuario (dinero) | Fila **5.23** del plan |
| L2 | Rama `rent_payment` de `offlinePayment`: `RentPayment::findOrFail($id)` **sin authorize** (la rama `security_deposit` sí tiene Gate) — tenant puede marcar pending una renta ajena (dinero) | Fila **5.24** del plan |
| L3 | Depósito ajeno → 500 en vez de 403 (authorize dentro del try) | Congelado por F13b; documentado |
| L4 | `Landlord/Withdraw` catch sin `report()` (gap de log) y `'profile_type' => 'nullable|string:255'` segunda regla con param ignorado | Preservados verbatim; registrados |

**Suite y guards:** 550/550 (535 + 15 nuevos; 6214 assertions) · PHPStan nivel 5 **[OK]** con baseline saneo −4 entradas migradas (justificación en `5fe8fbf`) · Pint passed · `TX_ALLOWLIST` eliminada (U2 absoluto) · 9/9 controladores sin tx y < 200 líneas (máx. 157).

**Commits:** `4e1860b` freeze F1–F6 → `c681a83` slice 1 → `96633ea` slice 2 → `17b81e4` slice 3 → `dad7161` freeze F8–F11 → `c31131b` slice 4 → `ed20caa` slice 5 (D-IDOR + D-FIX-validation) → `03dad6c` slice 6 → `3734b1e` freeze F12–F13 → `dc75cc2` slice 7 → `59ff789` freeze F14–F15 → `635fe64` slice 8 → `f93fa48` slice 9 + guard absoluto → `5fe8fbf` phpstan saneo → docs de cierre.
