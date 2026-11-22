---
id: spec-user-fillable-restrictivo-fase5
titulo: "`$fillable` de `User` restrictivo: allowlist de 7 columnas, fin del patrón `except()` en escrituras de `User` y fixes colaterales (Fase 5 / 5.11)"
estado: Borrador
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.11) — .analisis/deuda-tecnica.md (DT-B3, l.101-102) — hallazgos nuevos Seg-28/Seg-29 a registrar en .analisis/seguridad.md
cobertura: Cerrar DT-B3 reduciendo `User::$fillable` de 15 a 7 columnas (allowlist; desviación documentada de la sugerencia literal `$guarded` de DT-B3), eliminando `$request->except()` en los 6 call sites que escriben `User` con arrays explícitos, migrando ~18 writes server-side legítimos a `forceFill`, y cerrando en el mismo diff tres hallazgos conexos: inyección masiva + override por merge-order en signup público (Seg-28, nuevo), IDOR de `Admin/ProfileController::update` (Seg-29, nuevo) y el drop silencioso de `role` en `PermissionSeeder` vigente desde 1.4. Catálogo U1–U6 + S1–S6 + A1–A4 + G1–G9 + P1–P3 + H1–H3 + F1–F5 (36 casos; suite esperada 334+36=370) + arch-test scoped anti-`except()`
---

# Spec — `$fillable` de `User` restrictivo (Fase 5 / 5.11)

> Resumen de una línea: `User` pasa a un allowlist de 7 columnas de perfil; las 8 columnas sensibles (saldo, suscripción, verificación, OTP, tenancy) solo se escriben vía `forceFill` explícito server-side; los 6 sitios que hoy construyen arrays desde el raw request bag pasan a arrays explícitos; y el mismo diff cierra el IDOR del perfil admin y alinea la columna `role` del seeder con Spatie.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.11) — `.analisis/deuda-tecnica.md` (DT-B3, l.101-102: *"`$fillable` demasiado permisivo en `User`… incluye `role`, `balance`, `is_setupped`, `plan_subscribe_id`, `will_expire` — todos asignables masivamente"*; `role` ya salió en 1.4).

---

## 1. Objetivo

Cerrar DT-B3 por la vía fuerte: contrato de allowlist en el modelo **+** eliminación del patrón `$request->except()` que hoy permite a un cliente inyectar cualquier columna fillable — incluido en las **rutas públicas** de signup (web y API) — y, en el mismo diff, sellar los tres hallazgos conexos detectados en el inventario (Seg-28 inyección/merge-order, Seg-29 IDOR perfil admin, drop silencioso de `role` en `PermissionSeeder` desde 1.4).

---

## 2. Alcance

### IN

- **Contrato del modelo** (`app/Models/User.php:32-48`): `$fillable` queda en exactamente 7 columnas: `name, email, image, phone, password, profile_type, is_setupped`. Sin `$guarded` (default `['*']`); el contrato es la lista blanca. **Desviación documentada** del texto literal de DT-B3 (que sugería "`$guarded` selectivo"): lista negra deja auto-assignables las columnas futuras/olvidadas.
- **Columnas retiradas (8):** `landlord_id, plan_subscribe_id, unique_id, balance, subscriptionDate, will_expire, email_verified_at, remember_token` — escribibles **solo** vía `forceFill` explícito (server-side). `role`/`is_demo` siguen fuera (specs 1.4/3.5, intactos). Hooks `booted()` (role default) y `boot()` (`unique_id`) intactos.
- **Fin del patrón `except()` en escrituras de `User` — 6 call sites → arrays explícitos** construidos desde datos validados/claves conocidas + claves server-only; nunca desde el raw bag (mapa exacto en §4):
  - `Admin/UserController::store:59` / `update:108` → `name, email, phone` + `image`/`password` server. Reglas muertas `mobile_no/mobile_code` (y `country` en update) fuera del `validate()`.
  - `Auth/SignupController::store:64` (OTP off) / `:91` (OTP on) → `name, email, password, is_setupped` + `forceFill` posterior de `remember_token`/`email_verified_at`. Clave muerta `'role' => 'landlord'` del merge (`:93`, drop silencioso desde 1.4) fuera; el role real ya se asigna por propiedad (`:100-101`).
  - `Api/Auth/RegistrationController::maanRegistration:77` → `name, email, password` + `forceFill` según `otpStatus`. Comportamiento preservado: **sin** `is_setupped` (los usuarios API quedan NULL hasta `profileSetUp`).
  - `Admin/ProfileController::update:68` → `name, email, phone` + `image`/`password` condicionales.
- **Mapa `forceFill` (~18 sitios server-side legítimos, §4):** `SignupController:150/:184/:236` · `RegistrationController:100/:131/:195` · `ForgotPassword:36/:85` · `Signin:51` · `ResetPassword:37` · `NewPassword:34` (ya era forceFill — precedente, sin cambio) · `TenantService:138/:171/:256` · `Landlord/ProfileController:90` · `Admin/LandlordController:132-136` · `PaymentController:278/:307/:361-363` · `Landlord/WithdrawController:79` · `Api/WithdrawController:81` (verificar también `Api/WithdrawMethodController:66/:141`).
- **Fix Q16-1:** `Admin/LandlordController::store:97` — clave `'plan_subscribe_id' => $request->plan_subscribe_id` **eliminada** del array de create: el form no la postea (verificado en `resources/views/admin/landlords/`), no está validada y el bloque free-plan (`:132-136`) la sobrescribe cuando existe plan gratis.
- **Fix Q14 (promoción):** `Admin/SubscriptionController::paid:74` — `$subscribe->user()->update([...])` (mass update vía relación: bypasea `$fillable` y no dispara `UserObserver`) → `$subscribe->user->forceFill([...])->save()`. Bajo contrato + invalidación de `plan-data-{user_id}` **estructural** (cierra el gap de inventario §3 P5 del spec 4.9, cuya regla Q15a exige invalidación explícita en mass-ops sobre modelos cacheados).
- **Fix Q15 (Seg-29):** `Admin/ProfileController::update:41` — `User::findOrFail($id)` con parámetro de ruta → usuario resuelto **desde sesión** (`$request->user()`); el param se ignora. Cierra el IDOR: hoy un admin con `profile-update` puede modificar (incl. password) la cuenta de cualquier usuario.
- **Fix Q6 (quirk 1.4):** `PermissionSeeder:167-177` — `'role'` en `firstOrCreate` se descarta silenciosamente desde 1.4 → las cuentas demo `admin/superadmin@example.com` tienen columna `role='landlord'` (hook) con rol Spatie correcto. Fix: `firstOrCreate` base (`email`, `name`, `password`) + asignación explícita de columna `role` + `forceFill` del resto (`plan_subscribe_id`, `will_expire`, `email_verified_at`) post-create. Idempotencia 4.10 intacta (natural key `email`). Impacto verificado en §8.
- **Fix Q16-2:** `Api/UserController::profileSetUp:90` — `'profile_type' => $request->profile_type` sin validar → añadir `'profile_type' => 'nullable|string'` al `validate()` (paridad con `UpdateTenantRequest:24`).
- **Sweep de tests:** `tests/Feature/Auth/ApiAuthSmokeTest.php:314/:337/:360/:385` — helpers que escriben `remember_token`/`email_verified_at` vía `update([...])` → `forceFill` (sin ellos, no-op silencioso).
- **Arch-test scoped (Q13):** 0 `$request->except(` en los 4 archivos migrados.
- **Doc sweep (Q10):** `AGENTS.md` (gotcha del `$fillable` reescrito), `tests/README.md`, `.agents/README.md`, y edición local (gitignored) de `seguridad.md` (Seg-28/Seg-29), `deuda-tecnica.md` (DT-B3), `plan/plan-accion.md` (fila 5.11 + métrica).

### OUT (registrados, sin fix)

- **43 sitios `$request->except(` no-`User`** (28 archivos; inventario 2026-11-21): patrón extendido sobre otros modelos. Auditoría de sus `$fillable` pendiente; los 4 writes de blobs `Option` (`ServiceChargeController`, `ReminderMessageController`, `SettingController`, `WebSettingController`) son **by design**. Candidato a hallazgo/fila propia.
- **Doble hook `boot()` + `booted()`** y `unique_id` racy (`User::max('id')+1`, colisión bajo concurrencia; además siempre sobrescribe el `unique_id` de factory).
- **Dualidad estructural columna `role` vs Spatie** (dos sistemas paralelos): este spec alinea el seeder, no unifica los sistemas.
- **Ruta muerta `users.status`** (sin columna `status` en la tabla).
- **Doble deber de `remember_token`** (credential remember-me + OTP storage) y semántica sobrecargada de `email_verified_at` (flag verificado + TTL del OTP en signup pendiente, `SignupController:94` `$expire`): preservar **byte a byte** vía forceFill; rediseño = columna `otp` propia + migración + sweep ×12.
- **Métodos de dominio en `User`** (`addBalance()`, `applySubscription()`…): descartados (Q4); el trío de suscripción duplicado ×7 se registra como lateral, candidato a absorber por 5.13 (services).
- **Migración DB:** sin cambios de schema.
- **Login-redirect redesign:** `LoginController`/`RedirectIfAuthenticated` solo cambian de observable por la alineación del seeder (F2/F5); no se rediseñan.

### Cobertura objetivo

- U1–U6, S1–S6, A1–A4, G1–G9, P1–P3, H1–H3, F1–F5 en verde (36 casos).
- 0 escrituras mass-assignment de las 8 columnas retiradas en `app/` + `database/` (todo vía `forceFill`).
- 0 `$request->except(` en los 4 archivos migrados; 6 call sites request-driven con arrays explícitos.
- Flujos migrados a `forceFill` con regresión en verde: balance ×3, trío de suscripción ×5, OTP ×6, tenant `landlord_id` ×2.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Inventario exhaustivo de mass-assignment sobre `User` | Hecho — exploración 2026-11-21 (2 rondas, verificado con lectura directa) | Ninguna; base del mapa §4 y del catálogo §6 |
| P2 | Factories unguarded confirmado | Hecho — `vendor/.../Factories/Factory.php:428-437` (`Model::unguarded`) | Ninguna; `UserFactory` y seeders factory-based sin cambios |
| P3 | `role`/`is_demo` fuera de `$fillable` (specs 1.4/3.5) | Hecho — vigente | Ninguna; es la base del contrato |
| P4 | Red de regresión de flujos (signup/OTP/pagos/withdraw) | Hecho — `WebSignupSmokeTest`, `ApiAuthSmokeTest`, `PaymentFlowSmokeTest`, suite 334 | Deben seguir en verde tras el sweep |
| P5 | `PermissionSeeder` idempotente (4.10) | Hecho — `firstOrCreate` por `email` | Mantener idempotencia al reestructurar (Q6) |
| P6 | Caché centralizado (4.9) — `UserObserver` → `CacheKeys::forgetModel()` | Hecho — vigente | Q14 lo hace estructural para `paid()`; resto intacto |
| P7 | Suite base en verde (334 según `AGENTS.md`) | Por verificar al cerrar el PR | Verificar 334+36=370 contra salida real (lección 5.3) |

---

## 4. Estructura propuesta

Líneas al 2026-11-21; re-verificar al editar.

```
app/Models/User.php                                          # MODIFICADO: $fillable → 7 columnas
app/Http/Controllers/Admin/UserController.php               # MODIFICADO: :59/:108 arrays explícitos; reglas muertas fuera (Q16-4)
app/Http/Controllers/Auth/SignupController.php              # MODIFICADO: :64/:91 arrays explícitos + forceFill; :93 clave muerta 'role' fuera (Q16-3); :150/:184/:236 forceFill
app/Http/Controllers/Api/Auth/RegistrationController.php    # MODIFICADO: :77 array explícito + forceFill; :100/:131/:195 forceFill
app/Http/Controllers/Admin/ProfileController.php            # MODIFICADO: :41 user desde sesión (Seg-29); :68 array explícito
app/Http/Controllers/Admin/LandlordController.php            # MODIFICADO: :97 clave plan_subscribe_id fuera (Q16-1); :132-136 forceFill
app/Http/Controllers/Admin/SubscriptionController.php       # MODIFICADO: :74 → $subscribe->user->forceFill([...])->save() (Q14)
app/Http/Controllers/PaymentController.php                   # MODIFICADO: :278/:307 balance, :361-363 trío → forceFill
app/Http/Controllers/Landlord/WithdrawController.php        # MODIFICADO: :79 balance → forceFill
app/Http/Controllers/Api/WithdrawController.php             # MODIFICADO: :81 balance → forceFill (+verificar WithdrawMethod:66/:141)
app/Http/Controllers/Api/UserController.php                 # MODIFICADO: validate() += 'profile_type' => 'nullable|string' (Q16-2)
app/Http/Controllers/Landlord/ProfileController.php        # MODIFICADO: :90 landlord_id → forceFill
app/Http/Controllers/Api/Auth/ForgotPasswordController.php  # MODIFICADO: :36/:85 forceFill
app/Http/Controllers/Api/Auth/SigninController.php          # MODIFICADO: :51 forceFill
app/Http/Controllers/Api/Auth/ResetPasswordController.php   # MODIFICADO: :37-38 forceFill
app/Http/Controllers/Auth/NewPasswordController.php         # SIN CAMBIO (:34 ya forceFill — precedente)
app/Services/TenantService.php                              # MODIFICADO: :138/:171/:256 forceFill (landlord_id, remember_token, email_verified_at)
database/seeders/PermissionSeeder.php                       # MODIFICADO: :167-177 firstOrCreate base + role explícito + forceFill trío (Q6)

tests/Feature/Auth/ApiAuthSmokeTest.php                      # MODIFICADO (sweep): :314/:337/:360/:385 → forceFill

tests/Unit/Models/UserFillableTest.php                      # NUEVO: U1–U5
tests/Unit/Hygiene/UserMassAssignmentArchTest.php           # NUEVO: U6 (precedente: VendorPrefixArchTest en Hygiene/)
tests/Feature/Auth/WebSignupMassAssignmentTest.php          # NUEVO: S1–S6
tests/Feature/Auth/ApiSignupMassAssignmentTest.php          # NUEVO: A1–A4
tests/Feature/Admin/UserAdminMassAssignmentTest.php         # NUEVO: G1–G3
tests/Feature/Admin/AdminProfileSelfTest.php                # NUEVO: G4–G5 (Seg-29)
tests/Feature/Admin/LandlordStoreSubscriptionTest.php       # NUEVO: G6–G7
tests/Feature/Admin/SubscriptionPaidTest.php                # NUEVO: G8–G9
tests/Feature/Payment/BalanceForceFillTest.php              # NUEVO: P1–P3
tests/Feature/Landlord/TenantForceFillTest.php             # NUEVO: H1–H2
tests/Feature/Api/ProfileSetUpValidationTest.php            # NUEVO: H3
tests/Feature/Seeders/SeededRoleAlignmentTest.php           # NUEVO: F1–F5

AGENTS.md                                                    # MODIFICADO: gotcha "$fillable … mass-assignable" → contrato nuevo
tests/README.md                                              # MODIFICADO: conteos → 370, árbol
.agents/README.md                                            # MODIFICADO: índice

.analisis/ (gitignored — edición local, sin commit)
  seguridad.md                                               # Seg-28/Seg-29 registrados y cerrados
  deuda-tecnica.md                                           # DT-B3 cerrado (desviación $guardada documentada)
  plan/plan-accion.md                                        # fila 5.11 + métrica
```

Sin cambios en `routes/`, `config/`, `database/migrations/`, `UserFactory`, `UserObserver`, vistas.

---

## 5. Decisiones técnicas

Fijadas por grilling Q1–Q16 (2026-11-21), no negociables salvo volver el spec a `Borrador`:

- **Q1 — Doble eje:** allowlist del modelo **+** arrays explícitos en los call sites. Restrict sin matar `except()` deja inyectables las supervivientes; matar `except()` sin allowlist deja la puerta abierta al próximo controlador.
- **Q2 — Allowlist, no `$guarded`:** lista blanca de 7. Desviación del texto literal de DT-B3 documentada en `deuda-tecnica.md` al cerrar.
- **Q3 — Lista exacta:** quedan `name, email, image, phone, password, profile_type, is_setupped` (input legítimo de perfil, todos verificados en uso). Salen las 8 de §2 IN (`unique_id` nunca mass-assignado; el resto server-computed o credenciales). `landlord_id` **fuera**: es el scoping multi-tenant y hoy es inyectable en signup público.
- **Q4 — `forceFill` en todos los writes server-side**, sin métodos de dominio nuevos: precedente `NewPasswordController:34`, greppable, diff mínimo. El trío de suscripción duplicado ×7 se registra como lateral (candidato a 5.13), no se deduplica aquí.
- **Q5 — Seg-28 dentro:** el override por merge-order (`$request->except(...) + [server keys]`: la unión da precedencia al request → el cliente sobrescribe `remember_token`/`email_verified_at` y fija `balance`/`will_expire`/`plan_subscribe_id` en **rutas públicas**) se fixea aquí — es el mismo diff — y se registra como hallazgo nuevo en `seguridad.md`, cerrado por este spec.
- **Q6 — Quirk `PermissionSeeder` dentro:** asignación explícita de columna `role` post-create + `forceFill` del trío. Impacto verificado (§8); no rompe idempotencia.
- **Q7/Q9 — Laterales OUT registrados sin fix:** lista en §2 OUT.
- **Q8 — Arrays explícitos desde validated/known keys + claves server-only.** Prohibido reintroducir `except()`/`all()` en escrituras de `User`. Misma semántica de valores; sin canal de inyección.
- **Q10 — Doc sweep:** los 4 documentos (+`tests/README.md`/índice), patrón de cierre 5.1/5.4/5.7.
- **Q11 — Orden de tests:** caracterización → fix → flip (mismo PR, commit previo documentando Seg-28/Seg-29/quirk vigente).
- **Q12 — Mecánica:** rama impl `spec/spec-user-fillable-restrictivo-fase5-impl` (desde este draft), PR a `main` con DoD tachado.
- **Q13 — Arch-test scoped (no blanket):** ban de `$request->except(` solo en los 4 archivos migrados. Ban general = migrar 43 sitios ajenos + romper los 4 blobs `Option` by design. Los 43 quedan como lateral audit-pending.
- **Q14 — `SubscriptionController:74` promovido a fix:** 1 línea, mismo territorio; deja el invariante "columnas retiradas solo vía `forceFill`" **limpio** y la invalidación de `plan-data` estructural (hoy incidental: depende del `$subscribe->update()` previo).
- **Q15 — Seg-29 dentro:** usuario desde sesión en `Admin/ProfileController::update`; el form ya postea el propio id → cero cambio en flujos legítimos.
- **Q16 — Hardening inline ×4:** (1) clave `plan_subscribe_id:97` eliminada; (2) `profile_type` validado en `profileSetUp`; (3) clave muerta `'role'` (`:93`) fuera; (4) reglas muertas `mobile_no/mobile_code/country` fuera.

Preservación estricta de valores: `remember_token`/`email_verified_at` se escriben con los mismos valores actuales (incl. el TTL-OTP en `email_verified_at` del signup pendiente), solo cambia la vía (`forceFill`).

---

## 6. Catálogo de casos

### 6.1 Contrato del modelo (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | Allowlist exacto | — | `(new User)->getFillable()` | `=== ['name','email','image','phone','password','profile_type','is_setupped']` (orden fijo) |
| U2 | `fill()` ignora retiradas | user persistido | `$user->fill([...8 columnas retiradas...])->save()` | ninguna columna retirada cambia en BD |
| U3 | `forceFill()` sí asigna | ídem | `$user->forceFill(['balance' => 999])->save()` | `balance=999` persistido (mecanismo server-side) |
| U4 | Factory unguarded | — | `User::factory()->landlord()->create()` | row con `role`/`unique_id`/`balance`/`will_expire`/`remember_token` según `definition()` (idéntico a hoy) |
| U5 | Hooks creating intactos | tabla vacía | `User::create([name/email/password])` | `role='landlord'` (hook `booted`) + `unique_id` auto-generado (hook `boot`) |
| U6 | Arch: patrón extinto | repo | escaneo estático | 0 `$request->except(` en `Admin/UserController`, `Auth/SignupController`, `Api/Auth/RegistrationController`, `Admin/ProfileController` |

### 6.2 Signup web (`S`) — rutas públicas

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| S1 | Inyección de columnas en signup | `otp_status=off` | `POST /signup` + `balance`/`will_expire`/`plan_subscribe_id`/`landlord_id`/`is_setupped` extra | user creado **sin** ninguna clave inyectada — **caracterización Seg-28** |
| S2 | Override merge-order del OTP | `otp_status=on` | `POST /signup` + `remember_token`/`email_verified_at` propios | prevalecen los valores del servidor (el cliente no puede auto-verificar ni fijar su OTP) — **caracterización Seg-28** |
| S3 | Signup legítimo OTP off | — | `POST /signup` válido | landlord con `is_setupped=1`, `email_verified_at=now`, `remember_token=null` persistidos vía forceFill |
| S4 | Signup OTP on persiste OTP | `otp_status=on` | `POST /signup` válido | `remember_token=código`, `email_verified_at=expire` persistidos (semántica actual) |
| S5 | OTP submit válido | user pendiente con `remember_token=123456` | `POST` ruta `otp-submit` code=123456 | verificado: `email_verified_at=now`, `remember_token=null` (`:150`) |
| S6 | Free plan tras verificar | plan gratis existe | completar OTP | trío `plan_subscribe_id`/`will_expire`/`subscriptionDate` persistido (`:184`) |

### 6.3 Signup API (`A`) — rutas públicas

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| A1 | Inyección en sign-up Api | — | `POST /api/v1/sign-up` + claves extra (`balance`, `will_expire`, `plan_subscribe_id`, `landlord_id`, `is_setupped`) | claves extra ignoradas — **caracterización Seg-28** |
| A2 | Sign-up legítimo | — | `POST /api/v1/sign-up` name/email/password | user creado, `role='landlord'`, `is_setupped` **NULL** (comportamiento actual preservado) |
| A3 | submit-otp válido | user pendiente | `POST` ruta `submit-otp` code | `email_verified_at=now`, `remember_token=null` (`:195`) |
| A4 | Free plan trío Api | plan gratis existe | post-verificación | trío persistido (`:100/:131`) |

### 6.4 Admin users + self-profile (`G`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| G1 | Store con claves extra | admin con `users-create` | `POST /admin/users` + `balance`/`will_expire`/`plan_subscribe_id` | solo `name`/`email`/`phone`(+`image`/`password` server) escritos — **caracterización Seg-28** |
| G2 | Update con claves extra | admin + user existente | `PUT /admin/users/{id}` + extras | ídem — **caracterización Seg-28** |
| G3 | Store legítimo conserva role | ídem | `POST /admin/users` válido con `role=tenant` | user con columna `role='tenant'` + Spatie sync (property-set `:64` intacto) |
| G4 | Self-profile update legítimo | admin autenticado | `PUT /admin/profile/{auth id}` name/email/phone | 302, datos del admin actualizados |
| G5 | IDOR: id ajeno ignorado | admin + otro user (superadmin demo) | `PUT /admin/profile/{otro id}` password+name | el otro user **intacto**; solo el admin de sesión se modifica — **caracterización Seg-29** |

### 6.5 Admin landlords + suscripción (`G` cont.)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| G6 | Crear landlord con free plan | plan gratis existe | `POST /admin/landlords` válido | trío asignado vía forceFill (`:132-136`); landlord funcional |
| G7 | `plan_subscribe_id` crafted | admin | `POST /admin/landlords` + `plan_subscribe_id` ajeno | clave ignorada (eliminada del array); asigna el free plan — **caracterización Seg-28** |
| G8 | `paid()` marca suscripción | subscription unpaid | `POST /admin/subscriptions/paid/{id}` | user trío actualizado + `payment_status='paid'` |
| G9 | `paid()` invalida plan-data | stale plantado en `plan-data-{user_id}` | `POST paid` | `plan_data()` re-lee: `will_expire` nuevo visible (UserObserver→`forgetModel` vía `forceFill->save`) — cierre del gap P5 de 4.9 |

### 6.6 Balance (`P`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| P1 | Payment success acredita | landlord `balance=0`, flujo de pago stubeado | completar payment success | `balance > 0` persistido (`:278`) |
| P2 | Withdraw web debita | landlord con balance | `POST` landlord withdraw | balance debitado (`:79`) |
| P3 | Withdraw Api debita | ídem (Sanctum) | `POST /api/v1/withdraw` | balance debitado (`:81`) |

### 6.7 Tenant + profileSetUp (`H`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| H1 | Landlord crea tenant | landlord autenticado | `POST` landlord tenants válido | tenant con `landlord_id` persistido (`:138`) |
| H2 | Tenant Api OTP | flujo Api tenant | store + submit-otp | `remember_token`/`email_verified_at` persistidos (`:171`) |
| H3 | profileSetUp valida `profile_type` | tenant Api autenticado | `POST` profileSetUp con `profile_type` no-string | 422 (regla nueva); con string válido → persistido |

### 6.8 Alineación seeder (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Seeder alinea role↔Spatie | DB fresca | `db:seed --class=PermissionSeeder` | `admin@example.com`: columna `role='admin'` === rol Spatie; superadmin ídem (fix del drop de 1.4) |
| F2 | Login redirect admin demo | F1 seed | `POST /login/store` con admin@example.com | redirect a dashboard admin — **caracterización del quirk** (hoy: `landlord.dashboard`) |
| F3 | `notify_users()` alcanza superadmin demo | F1 seed | notificación admin | superadmin demo incluido en destinatarios |
| F4 | `SupportService` resuelve superadmin | F1 seed | resolución del receptor de soporte | superadmin demo ≠ null |
| F5 | Api sign-in gate admin demo | F1 seed | `POST /api/v1/sign-in` con admin@example.com | rechazado (no landlord/tenant) — **caracterización** (hoy pasa como landlord) |

### Tests de caracterización

Casos que nacen afirmando el comportamiento inseguro/incorrecto vigente (commit previo al fix, `@group characterization`) y se **flipean al aplicar §5 en el mismo PR**:

| ID | Bug caracterizado | Flipeado por |
|----|-------------------|--------------|
| S1, S2 | Seg-28 — inyección masiva + override merge-order en signup público | §5 Q1/Q8 (arrays explícitos) |
| A1 | Seg-28 — sign-up Api | §5 Q1/Q8 |
| G1, G2, G7 | Seg-28 — sitios admin | §5 Q1/Q8 + Q16-1 |
| G5 | Seg-29 — IDOR self-profile admin | §5 Q15 |
| F2, F5 | Quirk 1.4 — drop silencioso de `role` en `PermissionSeeder` | §5 Q6 |
| H3 | `profile_type` sin validación en `profileSetUp` | §5 Q16-2 |

---

## 7. Criterios de aceptación (Definition of Done)

- [ ] U1–U6, S1–S6, A1–A4, G1–G9, P1–P3, H1–H3, F1–F5 (36 casos) en verde; suite completa esperada **334+36=370** (verificar la base 334 contra salida real — lección 5.3)
- [ ] `User::$fillable` === 7 columnas exactas (U1); 0 usos de `fill/create/update/updateOrCreate/firstOrCreate` con las 8 columnas retiradas en `app/` + `database/` (mapa §4 migrado a `forceFill`; grep final en implementación)
- [ ] 0 `$request->except(` en los 4 archivos migrados (U6); los 43 sitios no-`User` intactos (lateral §2 OUT)
- [ ] Caracterizaciones Seg-28/Seg-29/quirk-1.4 flipeadas en el mismo PR, con commit previo documentándolas
- [ ] `php artisan test` completo en verde; PHPStan nivel 5 sin nuevas entradas (baseline intacta); Pint en verde
- [ ] `ApiAuthSmokeTest` ×4 helpers migrados a `forceFill` (sweep §4)
- [ ] Doc sweep: `AGENTS.md` (gotcha `$fillable` reescrito) + `tests/README.md` (conteos/árbol) + `.agents/README.md` (índice) commiteados; `seguridad.md` (Seg-28/Seg-29), `deuda-tecnica.md` (DT-B3 + desviación), `plan-accion.md` (fila 5.11 + métrica) editados localmente (gitignored)
- [ ] PR desde `spec/spec-user-fillable-restrictivo-fase5-impl` a `main`, DoD tachado en la descripción

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Writes legítimos no migrados → **no-op silencioso** (create/update con claves retiradas se descartan sin error) | alto | Mapa §4 exhaustivo (2 exploraciones) + grep final en implementación + regresiones por flujo (S/A/G/P/H) + suite completa |
| Alineación `role` del seeder cambia observables: login redirect (`LoginController:34-58`), `RedirectIfAuthenticated`, conteos de dashboard, listados/export de landlords, `notify_users()`, `SupportService:18`, gate Api (`Signin:24`) | medio | F1–F5 flipean las caracterizaciones; revisar `AdminGuardSmokeTest`/`SessionSmokeTest` por asserts del redirect viejo; Policies seguras (dual-check Spatie OR columna) |
| DBs ya sembradas conservan `role='landlord'` en cuentas admin (idempotencia: `firstOrCreate` no re-alinea filas existentes) | medio | Residual documentado (patrón 5.2); re-align manual o wipe+re-seed en entornos de evaluación |
| `paid()` promocionado cambia el mecanismo de invalidación (incidental→estructural) | bajo | G9 aserción de caché; `CacheObserverTest` vigente como red |
| Consumidor hipotético de `plan_subscribe_id` en `Admin/LandlordController::97` | bajo | Blade verificado sin campo; form nunca la postea; free-plan sobrescribe; residual estilo 5.8 ("consumidor hipotético") |
| Drift del conteo de suite base | bajo | Verificar 334 contra salida real antes de sumar (lección 5.3) |

**Pendiente de decisión:** ninguna — Q1–Q16 cerradas por grilling (2026-11-21).

---

## 9. Estimación

- Contrato + 6 call sites + mapa forceFill + seeder: 1–1.5 días
- Catálogo de tests (36 casos) + sweep de `ApiAuthSmokeTest`: 1–1.5 días
- Doc sweep + PR + verificación DoD: 0.5 día

**Total: 2.5–3.5 días.**

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.11
- `.analisis/deuda-tecnica.md` → DT-B3 (l.101-102)
- `.analisis/seguridad.md` → Seg-28/Seg-29 (nuevos, a registrar; contexto: Seg-4 base del contrato, Seg-5 familia IDOR de Seg-29)
- `.agents/spec/spec-conventions.md` · `.agents/spec/spec-template.md`
- Specs relacionados: `spec-registro-api-role-arbitrario-fase1` (1.4, `role` fuera de fillable), `spec-demo-reset-demomode-fase3` (3.5, `is_demo`), `spec-cache-invalidacion-centralizada-fase4` (4.9, §3 P5 gap que Q14 cierra), `spec-seeders-firstorcreate-fase4` (4.10, idempotencia del seeder), `spec-typos-rutas-fase5` (patrón de hallazgos laterales)
- `AGENTS.md` → gotchas (`User` `$fillable` mass-assignable — a reescribir)
