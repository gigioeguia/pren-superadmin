---
id: spec-demo-user-passwords-fase5
titulo: "Credenciales demo no predecibles: config-driven con fallback aleatorio (Fase 5 / 5.2)"
estado: Implementado
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.2) — .analisis/seguridad.md (Seg-19, parte UserSeeder) — .agents/spec-impl/spec-password-policy-fase3.md (policy 3.2)
cobertura: Migración de las 4 credenciales demo (superadmin/admin/landlord/tenant, 3 fuentes reales: PermissionSeeder + UserFactory x2) a config('demo.user_password') con fallback aleatoria impresa al crear; clase App\Support\DemoCredentials; tests U1-U2/F1-F3; README/AGENTS.md/.env.example commiteados; cierre documental local (plan-accion 5.2, Seg-19)
---

# Spec — Credenciales demo no predecibles: config-driven con fallback aleatorio (Fase 5 / 5.2)

> Resumen de una línea: ninguna credencial demo es predecible desde el repo — las 4 cuentas pasan a usar `config('demo.user_password')` y, si está vacía, una aleatoria por usuario generada e **impresa por consola solo al crear** — cerrando la parte de `UserSeeder` que Seg-19 dejaba pendiente.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.2) — `.analisis/seguridad.md` (Seg-19, parte `UserSeeder`).

---

## 1. Objetivo

Que el repo deje de publicar passwords demo predecibles: tras el cambio, `migrate --seed` produce 4 usuarios demo cuyo password es **el valor de `DEMO_USER_PASSWORD`** (si el operador lo fijó en su `.env`) o **una aleatoria por usuario** (`Str::password(20)`) impresa en consola en el momento de creación — nunca el nombre del rol. Cierra la parte pendiente de Seg-19 y el ítem 5.2.

---

## 2. Alcance

### IN

- **`config/demo.php`** (nuevo): `'user_password' => env('DEMO_USER_PASSWORD')` — `env()` legal solo aquí (patrón fase 4 / 4.3).
- **`app/Support/DemoCredentials.php`** (nuevo): única fuente del password demo (patrón Support, fase 4 / 4.6). API mínima:
  - `password(string $email): string` — devuelve el valor de config, o genera una aleatoria (recordándola por email para el reporte).
  - `hash(string $plain): string` — wrapper de bcrypt.
  - `generatedFor(string $email): ?string` — reporte interno para que los seeders impriman solo lo generado.
- **`database/seeders/PermissionSeeder.php`** (l.165–174): `bcrypt($generated_role)` → `DemoCredentials::password($generated_role.'@acnoo.com')`; impresión vía `$this->command->info()` solo si `$user->wasRecentlyCreated` y el password fue generado.
- **`database/factories/UserFactory.php`**: estados `demoLandlord` (l.95, `bcrypt('landlord')`) y `demoTenant` (l.118, hash literal de `'tenant'`) → `DemoCredentials`; actualizar los comentarios "credenciales fijas (spec 4.10 / DT-M9)".
- **`database/seeders/UserSeeder.php`**: reestructura mínima del `first() ?? create()` para saber si creó e imprimir solo entonces.
- **`.env.example`**: `DEMO_USER_PASSWORD=` (vacía) + comentario: "solo evaluación local; vacía ⇒ aleatoria impresa al sembrar; nunca producción".
- **`README.md`**: tabla "Credenciales demo" → nuevo modelo; bullet de "Seguridad conocida" (rotar/desactivar en despliegues reales) actualizado.
- **`AGENTS.md`**: nota de seeders "Demo users have known passwords … pending (Fase 5 / 5.2)" → nuevo modelo.
- **`tests/Feature/Seeders/DemoUserPasswordTest.php`** (nuevo): U1–U2 + F1–F3 (§6).
- **Cierre documental local** (gitignored): `plan-accion.md` fila 5.2 → Implementado + retirar la cláusula "hasta 5.2" de la fila 5.1; `seguridad.md` Seg-19 cierra completo (sin "pendiente bajo 5.2").

### OUT

- **Emails `*@acnoo.com`** — no cambian: son identificadores (dataset demo, `User::demoLandlords()`, idempotency tests dependen de ellos), no secretos.
- **Rotación forzada de DBs ya sembradas** — `firstOrCreate`/`first() ?? create` no tocan filas existentes: cualquier instancia previa conserva `superadmin`/`admin`/`landlord`/`tenant` hasta rotación manual. **Riesgo residual aceptado** (Q6 del grilling 2026-11-21), hermano del blob de 5.1; sin comando/migración de rotación.
- **Semántica de `APP_ENV=demo`** (fase 3.5) — no se mezcla con credenciales (opción (c) descartada en Q1).
- **Password policy (fase 3.2)** — se respeta, no se modifica.
- **`demoReset()`/`DemoMode`** — intocados.
- **Flujos de auth** — intocados: ningún test de login usa credenciales demo (verificado: idempotency tests cuentan emails; login tests usan factories).

### Cobertura objetivo

- 0 `bcrypt('<valor demo>')` y 0 hashes literales de passwords demo en `database/` (grep sobre `'superadmin'|'admin'|'landlord'|'tenant'` como password).
- Los 4 usuarios demo: password ≠ rol (F1), = config cuando está seteada (F2), estable tras re-seed (F3).
- 0 `env()` en `database/seeders/` (arch-test U2 verde).
- 0 credenciales publicadas en README/AGENTS.md.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rama de trabajo (no `main`) | `spec/demo-user-passwords-fase5-draft` creada | Sin acción |
| P2 | Policy de contraseña unificada (fase 3.2) | Existe: `Password::min(8)->mixedCase()->numbers()->max(100)` | Consumir en U2 (la generada debe pasarla) |
| P3 | Vetto de `env()` en seeders | arch-test `tests/Unit/Seeders/SeedersArchTest.php` | `env()` solo en `config/demo.php`; seeders vía `config()` |
| P4 | Idempotencia por clave natural (fase 4.10) | `firstOrCreate` (PermissionSeeder) + `first() ?? create` (UserSeeder) | Preservar (F3) |
| P5 | Patrón de clases Support (fase 4.6) | `App\Support\{Currency, Payment, Otp}` | Nueva clase `DemoCredentials` siguiéndolo |
| P6 | Tests de login no dependen de creds demo | Verificado (grep de tests: solo conteos por email) | Sin acción |

---

## 4. Estructura propuesta

```
config/demo.php                                             # NUEVO: user_password => env('DEMO_USER_PASSWORD')
app/Support/DemoCredentials.php                             # NUEVO: única fuente del password demo
database/seeders/PermissionSeeder.php                       # MODIFICADO: bcrypt($role) -> DemoCredentials + print on create
database/factories/UserFactory.php                         # MODIFICADO: demoLandlord/demoTenant -> DemoCredentials; comentarios
database/seeders/UserSeeder.php                             # MODIFICADO: print on create (sabe si creó)
.env.example                                                # MODIFICADO: DEMO_USER_PASSWORD= (vacía, documentada)
README.md                                                   # MODIFICADO: credenciales demo + seguridad conocida
AGENTS.md                                                   # MODIFICADO: nota de seeders
tests/Feature/Seeders/DemoUserPasswordTest.php              # NUEVO: U1-U2 + F1-F3
.analisis/plan/plan-accion.md                               # local (gitignored): fila 5.2 + cláusula fila 5.1
.analisis/seguridad.md                                      # local (gitignored): Seg-19 cierra completo
```

---

## 5. Decisiones técnicas

Fijadas (grilling 2026-11-21, Q1–Q6), no negociables salvo volver el spec a `Borrador`:

- **Modelo (Q1 opción a):** `DEMO_USER_PASSWORD` vía `config/demo.php`. Seteada ⇒ los 4 usuarios comparten ese password. Vacía ⇒ una aleatoria por usuario (`Str::password(20)`), impresa por consola solo al crear. El repo no publica ningún valor.
- **Elección del operador:** setear `DEMO_USER_PASSWORD` en el `.env` local es decisión del operador (gitignored); documentado como "solo evaluación local, nunca producción".
- **Impresión:** solo vía `$this->command->info()` cuando `wasRecentlyCreated` && generado; re-seed silencioso (idempotencia). Sin logging persistente — consola efímera (secure-coding: user data).
- **`env()` solo en `config/`** (fase 4.3); seeders/factories consumen `config()`.
- **Framework de tests:** PHPUnit sobre MySQL `dbrentpro_test`, `RefreshDatabase` (convención de la suite, ADR-0001).
- **Prefijos U/F** (patrón `spec-demo-reset-demomode-fase3`): `U` = helper, `F` = feature; este spec no cubre flujos HTTP — `Dado/Cuando/Entonces` adaptado a comportamiento de seeders.

---

## 6. Catálogo de casos

### 6.1 Helper `App\Support\DemoCredentials` (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | Config seteada | `config(['demo.user_password' => 'DemoPass123'])` | `DemoCredentials::password('user@example.com')` | Devuelve exactamente `'DemoPass123'`, determinista, nada generado |
| U2 | Config vacía, policy | `demo.user_password` null | `password()` dos veces para emails distintos | Cada valor es aleatorio, distinto entre sí, distinto de `'superadmin'/'admin'/'landlord'/'tenant'` y pasa `Password::min(8)->mixedCase()->numbers()` (fase 3.2) |

### 6.2 Seeders (`F`) — `tests/Feature/Seeders/DemoUserPasswordTest.php`

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | No predecibles tras seed | `demo.user_password` null | Correr `PermissionSeeder` + `UserSeeder` | Los 4 `*@acnoo.com` existen y `Hash::check('<rol>', $user->password)` es **false** ×4 |
| F2 | Config aplicada | `config(['demo.user_password' => 'DemoPass123'])` | idem F1 | `Hash::check('DemoPass123', $user->password)` es **true** ×4 |
| F3 | Re-seed no rota | Usuarios ya creados con password conocido | Re-correr ambos seeders | El hash no cambia (idempotencia); sin duplicados (guardas: `DatabaseSeederIdempotencyTest`, `SeedOverExistingDataTest`) |

**Regression guards existentes (no se tocan):** `DatabaseSeederIdempotencyTest` (4 emails, sin duplicados), `SeedOverExistingDataTest`, `WebLoginSmokeTest` (login con factories, no con creds demo).

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `config/demo.php` creado y `DEMO_USER_PASSWORD=` documentada (vacía) en `.env.example`.
- [x] `App\Support\DemoCredentials` es la única fuente; grep en `database/` sin `bcrypt('superadmin'|'admin'|'landlord'|'tenant')` ni hashes literales demo (grep vacío, 2026-11-21).
- [x] U1–U2, F1–F3 en verde (30 aserciones); suite completa en verde: **259 passed, 806 assertions, 0 fallos**; `composer lint` (pint) y `composer analyse` (phpstan) sin errores.
- [x] Arch-test U2 verde: 0 `env()` en `database/seeders/` (grep vacío + suite en verde).
- [x] README y AGENTS.md actualizados: 0 credenciales publicadas, modelo nuevo documentado (grep vacío sobre README/AGENTS.md).
- [x] Cierre documental local: `plan-accion.md` fila 5.2 → Implementado (2026-11-21) y fila 5.1 con "desde 5.2"; `seguridad.md` Seg-19 cerrado completo (fila 19–26 actualizada).
- [x] Riesgo residual Q6 (DBs ya sembradas) documentado en §8 y en README — sin mecanismo de rotación forzada.
- [x] PR (código + tests + docs) con CI verde: PR #47, run https://github.com/gigioeguia/pren-superadmin/actions/runs/44920287642 (tests, static-analysis, style, frontend-build = success); merge por squash pendiente de review.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| DBs ya sembradas conservan los passwords viejos (`firstOrCreate` no rota) | Medio | Residual aceptado (Q6): README insta rotar/desactivar en despliegues reales; sin rotación forzada |
| Deployer setea `DEMO_USER_PASSWORD` con valor débil/predecible | Bajo | Elección del operador en su `.env` (gitignored); documentado "solo evaluación local, nunca producción" |
| Passwords generadas visibles en output de tests/CI que corren seeders | Bajo | DB de test desechable; impresión efímera de consola; sin logging persistente |
| Cambio de semántica en la creación (`UserSeeder` reestructurado) | Bajo | F3 + idempotency tests existentes como guardas |

**Pendiente de decisión:** ninguna — Q1–Q6 del grilling (2026-11-21) cerrados.

---

## 9. Estimación

- Helper + config + migración de las 3 fuentes: 0.5 día
- Tests U/F + docs commiteadas (README/AGENTS/.env.example) + cierre documental local: 0.5 día

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.2
- `.analisis/seguridad.md` → Seg-19 (parte `UserSeeder` pendiente tras 5.1)
- `.agents/spec-impl/spec-password-policy-fase3.md` → policy unificada (3.2)
- `.agents/spec-impl/spec-seeders-firstorcreate-fase4.md` → idempotencia; comentarios "credenciales fijas" en `UserFactory`
- `.agents/spec-impl/spec-demo-reset-demomode-fase3.md` → patrón de prefijos U/F para specs no-HTTP
- `.agents/spec-impl/spec-env-to-config-fase4.md` → patrón env→config
- `.agents/skills/secure-coding.md` → checklist al tocar user data
- Fuentes actuales: `PermissionSeeder.php:170`, `UserFactory.php:95` y `:118`
