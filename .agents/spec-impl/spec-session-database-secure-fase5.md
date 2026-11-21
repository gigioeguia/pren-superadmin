---
id: spec-session-database-secure-fase5
titulo: "Session driver `database` + cookie `secure` inferida de `APP_URL` (Fase 5 / 5.5)"
estado: Aprobado
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.5) — .analisis/seguridad.md (Seg-22, l.137)
cobertura: Cerrar Seg-22 fijando `SESSION_DRIVER=database` como default de config (migración `sessions` estándar; `redis` soportado vía env sin añadir `predis`) e infiriendo la cookie `secure` de `APP_URL` (`env('SESSION_SECURE_COOKIE', str_starts_with(...))` — desacopla 5.5 de 5.6, válvula manual comentada en `.env.example`), con tests U1–U5 + F1–F2 sobre `dbrentpro_test` y residual operacional declarado (flip del `.env` de producción pendiente de evidencia, patrón 5.9). Decisiones selladas en grilling Q1–Q11 (2026-11-21).
---

# Spec — Session driver `database` + cookie `secure` inferida de `APP_URL` (Fase 5 / 5.5)

> Resumen de una línea: el driver de sesión pasa de `file` (no escalable, filesystem efímero) a `database` por default, y la cookie gana el flag `Secure` automáticamente cuando `APP_URL` es https — si alguien revierte el default o borra la inferencia, U1/U5 y F1 se rompen.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.5) — `.analisis/seguridad.md` (Seg-22, l.137; contexto Seg-6, l.95: la sesión transporta `user_id`/`payment_info` en el flujo de pagos).

---

## 1. Objetivo

Cerrar Seg-22: hoy `SESSION_DRIVER=file` (`.env` l.16, `.env.example` l.28) no escala multi-servidor, se pierde en deploys con filesystem efímero, y la cookie de sesión viaja sin flag `Secure` (`'secure' => env('SESSION_SECURE_COOKIE')` → null → false, `config/session.php` l.171). Este spec fija `database` como default de config, añade la migración `sessions` y enciende `Secure` de forma automática e inferida — sin depender de que ops recuerde setear una key.

---

## 2. Alcance

### IN

- **`config/session.php`** — exactamente 2 líneas:
  - `'driver' => env('SESSION_DRIVER', 'database')` (l.21).
  - `'secure' => env('SESSION_SECURE_COOKIE', str_starts_with((string) env('APP_URL'), 'https://'))` (l.171). Válvula manual: la key explícita siempre gana.
- **Migración** `database/migrations/2026_11_21_*_create_sessions_table.php` (generada con `php artisan session:table`): `id` string PK, `user_id` nullable index, `ip_address` 45 nullable, `user_agent` text nullable, `payload` text, `last_activity` integer index. Additiva, sin guard `hasTable`.
- **`.env.example`** (ll.28–29): `SESSION_DRIVER=database` + `# SESSION_SECURE_COOKIE=` comentada con una línea de explicación (válvula documentada, Q11).
- **`.env` local** (gitignored): flip a `SESSION_DRIVER=database` — dev ejercita el camino real (Q4).
- **Tests** (5 unit + 2 feature, descomposición atómica de Q5/Q7 según regla "un test = una aserción principal" de `testing-conventions`):
  - `tests/Unit/Config/SessionConfigTest.php` — U1–U5 (§6.1).
  - `tests/Feature/Security/SessionSmokeTest.php` — F1–F2 (§6.2); carpeta `Security/` ya existe (5.4).
- **Conteos** (patrón trust-hosts, mecánico): `README.md`, `tests/README.md` (línea en el árbol + mini-sección), `AGENTS.md` → suite esperada 266 + 7 = **273**; `.agents/README.md` índice: fase5 → 5 specs, conteo 49 → 50.
- **Cierres documentales locales** (`.analisis/`, gitignored — edición en implementación, sin commit): `seguridad.md` #22 → **Mitigado** + residual; `plan-accion.md` fila 5.5 + Métricas de éxito (Q9).

### OUT

- **`encrypt`, `same_site`, `partitioned`, `lifetime`, `expire_on_close`** — intocados (Q3): el store pasa a DB server-side, `http_only` ya es `true`, `lax` ya es razonable.
- **Cache driver** (también `file` hoy) — Seg-22 no lo menciona; fuera de scope para que nadie expanda silenciosamente el ítem.
- **5.6 (`APP_URL` https + forzar TLS)** — desacoplado por la inferencia (Q2): 5.5 se puede implementar antes; cuando 5.6 fije `APP_URL=https`, la cookie se vuelve `Secure` sola, sin tocar código.
- **`predis`/infra Redis** — `redis` queda soportado vía `SESSION_DRIVER=redis` nativo del framework, sin añadir dependencia (Q1).
- **UI del wizard `/install`** (vendor `safiull/laravel-installer`, escribe `SESSION_DRIVER` desde su formulario) — edge aceptado y documentado (Q4); el default de config aplica solo cuando la key no existe.
- **Notas de despliegue en `README.md`** — excluidas por Q10; los caveats viven solo en este spec (§8) y en la fila residual del plan. La actualización de conteos en `README.md` es mecánica y no es nota de despliegue.

### Cobertura objetivo

- U1–U5 + F1–F2 en verde; suite completa esperada **273 passed** (266 + 7, verificada contra salida real).
- La suite actual (266) queda intacta: `phpunit.xml` sin cambios (`SESSION_DRIVER=array`, `APP_URL=http://localhost` — inferencia da `false` en testing).

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Tabla `sessions` + migración | **Falta** — sin migración en `database/migrations/` | Generar `session:table` (disponible en L11, verificado: `make:session-table [session:table]`) |
| P2 | `config/session.php` | Stock — `driver` default `'file'` (l.21), `secure` env-only null (l.171) | Modificar las 2 líneas del §2 IN |
| P3 | `.env`/`.env.example` con `SESSION_DRIVER=file` | Existe — key presente **pisa** el default de config | Actualizar `.env.example` (commit) + `.env` local (sin commit); producción = residual (§8) |
| P4 | Suite aislada del cambio | Verificado — `phpunit.xml` l.23 `APP_URL=http://localhost`, l.37 `SESSION_DRIVER=array` | Ninguna |
| P5 | Cliente Redis | **No hay** — sin `predis` en composer, sin ext phpredis (verificado `php -m`) | Ninguna — decisión Q1: `database`, sin nueva dependencia |
| P6 | Handler DB escribe `user_id` | Verificado (2026-11-21) — `DatabaseSessionHandler` l.186 `last_activity`, l.208 `user_id`, GC por `last_activity` l.281 | Ninguna; F2 aserta sobre `sessions.user_id` |
| P7 | Carpeta `tests/Feature/Security/` | Existe (5.4, `TrustHostsSmokeTest`) | Reutilizar; `tests/Unit/Config/` es carpeta nueva |
| P8 | Base de conteos = 266 (5.4 mergeado) | Existe — AGENTS.md/README/test-README | Verificar 266+7=273 contra salida real al cerrar el PR (lección 5.3) |

---

## 4. Estructura propuesta

```
config/session.php                                          # MODIFICADO: 2 líneas (driver default + secure inferido)
database/migrations/2026_11_21_*_create_sessions_table.php  # NUEVO (session:table)
.env.example                                                # MODIFICADO: l.28 SESSION_DRIVER=database + válvula comentada
.env                                                        # MODIFICADO local (gitignored): SESSION_DRIVER=database

tests/Unit/Config/SessionConfigTest.php                    # NUEVO: U1–U5 (carpeta Config/ nueva)
tests/Feature/Security/SessionSmokeTest.php                 # NUEVO: F1–F2 (carpeta Security/ de 5.4)

README.md                                                   # MODIFICADO: conteo 273
tests/README.md                                             # MODIFICADO: conteo, árbol, mini-sección "## Session (Fase 5 / 5.5)"
AGENTS.md                                                   # MODIFICADO: conteo 273
.agents/README.md                                           # MODIFICADO: índice — fase5 (5 specs), conteo 50

.analisis/ (gitignored — edición local, sin commit)
  seguridad.md                                              # MODIFICADO: #22 → Mitigado + residual
  plan/plan-accion.md                                       # MODIFICADO: fila 5.5 + Métricas de éxito
```

Sin cambios en `routes/`, `app/`, `phpunit.xml`, `config/` fuera de `session.php`.

---

## 5. Decisiones técnicas

Fijadas (grilling Q1–Q11, 2026-11-21), no negociables salvo volver el spec a `Borrador`:

- **Driver `database` (Q1):** MySQL ya existe; la tabla es aditiva y sobrevive a deploys con filesystem efímero. `redis` queda como opción nativa vía env, **sin** añadir `predis` — infra que hoy nadie opera.
- **`secure` inferida (Q2):** `env('SESSION_SECURE_COOKIE', str_starts_with((string) env('APP_URL'), 'https://'))` — se enciende sola cuando `APP_URL` sea https (5.6), es overridable, nunca rompe dev local http, y elimina el modo de fallo "ops olvidó setear la key" (el mismo que dejó 5.9 en Parcial). Alternativas descartadas: env-only frágil (a), `true` incondicional rompe dev por LAN http (c).
- **Scope mínimo (Q3):** solo driver + `secure`; el resto de flags queda declarado OUT para bloquear expansión silenciosa.
- **Defaults seguros shipeados (Q4):** `.env.example=database`, `.env` local flip, wizard `/install` documentado como edge aceptado.
- **Tests puntuales, no suite extendida (Q5):** no se re-corre la suite de smoke auth bajo `SESSION_DRIVER=database` en CI; F1–F2 cubren de frente los dos contratos (cookie y handler).
- **Evidencia operativa = residual (Q6):** el flip del `.env` de producción no es versionable; queda documentado como pendiente de evidencia (patrón 5.9).
- **Marcado "Mitigado" (Q9):** ni "Cerrado" (queda trabajo en producción) ni "Parcial" puro (el repo sí queda completo).
- **Notas de despliegue solo en spec + plan (Q10):** `README.md` solo recibe la actualización mecánica de conteos.
- **Framework:** PHPUnit `^10.1` (Laravel 11); AAA comentado; `@group smoke` + `@group security` en la clase feature.
- **DB:** MySQL `dbrentpro_test`; `RefreshDatabase` en la clase feature (F2 necesita el schema de `sessions`); la clase unit no toca DB.
- **Mecanismo unitario:** U1–U5 evalúan el archivo real `require config/session.php` con `$_ENV` mutado en runtime y restore en `tearDown` (nota 2) — no asserts sobre `config()` ya cacheado, que no probarían el mapeo env→config del archivo.

---

## 6. Catálogo de casos

### 6.1 Config: mapeo env → driver/secure (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | `APP_URL` https ⇒ secure | `$_ENV['APP_URL']='https://app.example.com'`, sin `SESSION_SECURE_COOKIE` | `require config/session.php` (evaluación fresca) | `['secure'] === true` |
| U2 | `APP_URL` http ⇒ no secure | `$_ENV['APP_URL']='http://localhost'` (valor de `phpunit.xml`), sin override | idem | `['secure'] === false` (nota: `APP_URL` ausente → `(string) null = ''` → mismo camino false) |
| U3 | override `'true'` gana | `$_ENV['SESSION_SECURE_COOKIE']='true'` + `APP_URL` http | idem | `['secure'] === true` |
| U4 | override `'false'` gana | `$_ENV['SESSION_SECURE_COOKIE']='false'` + `APP_URL` https | idem | `['secure'] === false` |
| U5 | driver default | `SESSION_DRIVER` ausente de `$_ENV` | idem | `['driver'] === 'database'` |

`tearDown`: restaurar `$_ENV` a los valores de `phpunit.xml` (`APP_URL=http://localhost`, unset de las keys inyectadas).

### 6.2 HTTP: cookie y handler real (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | flags de la cookie de sesión | `config(['session.secure' => true])` en runtime | `GET /login` | 200 y la cookie de sesión (vía `$response->headers->getCookies()`) cumple `isSecure() && isHttpOnly() && getSameSite() === 'lax'` |
| F2 | sesión persiste con driver database | `config(['session.driver' => 'database'])` en runtime, landlord de factory, tabla `sessions` migrada (`RefreshDatabase`) | `POST /login/store` (JSON email+password) y luego `GET` a ruta autenticada landlord | login OK (200 JSON `{redirect}`), existe fila en `sessions` con `user_id = $user->id`, y el GET autenticado responde 200 — sesión leída de DB entre requests |

### Tests de caracterización

Ninguno: no hay bug vigente que se espere romper — el fix ES este spec.

---

## 7. Criterios de aceptación (Definition of Done)

- [ ] `config/session.php`: las 2 líneas del §2 IN; `http_only`, `same_site`, `encrypt`, `lifetime` intactos (inspección).
- [ ] Migración `sessions` con el schema estándar de `session:table`; `php artisan migrate` en verde; corre bajo `RefreshDatabase` sin errores.
- [ ] `.env.example` l.28 `SESSION_DRIVER=database` + `# SESSION_SECURE_COOKIE=` comentada con explicación de una línea (válvula, Q11).
- [ ] `.env` local con `SESSION_DRIVER=database` (sin commit — lo verifica el implementador en su entorno).
- [ ] U1–U5 en verde (focal: 5 passed); `$_ENV` restaurado en `tearDown`.
- [ ] F1–F2 en verde (focal: 2 passed).
- [ ] Suite completa: esperados **273 passed** (266+7), **verificados contra salida real** de `php artisan test` (lección 5.3).
- [ ] `phpunit.xml` sin cambios (la suite existente corre con `array`/`http://localhost`).
- [ ] `composer analyse` sin nuevas entradas vs baseline; `composer lint` limpio.
- [ ] Conteos **273** en `README.md`, `tests/README.md`, `AGENTS.md`; `tests/README.md` con `Unit/Config/` en el árbol + mini-sección "## Session (Fase 5 / 5.5)"; `.agents/README.md` índice fase5 (5 specs, conteo 50).
- [ ] Sin notas de despliegue nuevas en `README.md` (Q10) — conteos únicamente.
- [ ] Cierres locales `.analisis/` (gitignored): `seguridad.md` #22 → Mitigado + residual; `plan-accion.md` fila 5.5 + métrica (redacciones de la nota 4).
- [ ] Rama `spec/session-database-secure-fase5-impl` + PR review (squash, 1 aprobación + CI); sin push a `main`; nada mergeado en Borrador.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| `.env` de producción ya escribe `SESSION_DRIVER=file` — key presente pisa el default de config | **Alto** (el riesgo real persiste hasta el flip) | Residual operacional declarado (Q6/Q9): flip + re-cache pendientes de evidencia; mismo tratamiento que 5.9 |
| `config:cache` congela la inferencia (env se evalúa al cachear) | Medio | Nota de despliegue: tras cambiar `APP_URL`/`SESSION_SECURE_COOKIE` en un servidor con config cacheada, re-ejecutar `config:cache` |
| Mutación de `$_ENV` en tests frágil frente a los internals de dotenv (writer inmutable) | Medio | Mecanismo primario de nota 2 + verificación temprana en implementación; si no cooperara, fallback: re-evaluar el archivo con env despoblado en proceso fresco — verificación real vence suposición (lección 5.4) |
| Wizard `/install` escribe su propio `SESSION_DRIVER` | Bajo | Edge aceptado (Q4): documentado aquí; fresh installs vía wizard eligen driver en UI |
| Driver `database` añade I/O MySQL por request | Bajo | Payload chico + índices (`user_id`, `last_activity`) + lottery 2/100 barre expiradas; `SESSION_CONNECTION` disponible si se quiere aislar |
| F2 depende de que el cliente de test retenga cookies entre requests y resuelva el driver tras el override runtime | Bajo | Patrón ya usado por los smoke de auth; verificación temprana (nota 3) con fallback `$this->withCookie`/reboot puntual |

**Residual operacional (Q6/Q9):** editar `SESSION_DRIVER` en el `.env` de producción + re-ejecutar `config:cache`; evidencia pendiente. La mitad `secure` **no** requiere acción: se enciende cuando 5.6 fije `APP_URL=https`.

**Pendiente de decisión:** ninguno (Q1–Q11 del grilling 2026-11-21).

---

## 9. Estimación

- Código (config 2 líneas + migración + env): 0.25 día.
- Tests U1–U5 + F1–F2 + corrida focal: 0.25 día.
- Docs + conteos + cierres documentales + PR: 0.25 día.
- Total: ~0.75 día.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.5
- `.analisis/seguridad.md` → Seg-22 (l.137); contexto Seg-6 (l.95, sesión en el flujo de pagos)
- `config/session.php` → l.21 (driver), l.171 (secure)
- `.env.example` → l.28; `phpunit.xml` → l.23, l.37
- `vendor/laravel/framework/src/Illuminate/Session/DatabaseSessionHandler.php` → l.186/208/281 (`last_activity`, `user_id`, GC)
- `vendor/safiull/laravel-installer/src/Helpers/EnvironmentManager.php` → l.112 (wizard escribe `SESSION_DRIVER`)
- `.agents/spec-impl/spec-trust-hosts-fase5.md` → precedente de flujo (F*, conteos verificados, override de testabilidad, redacciones documentales)
- `.agents/spec-impl/spec-comand-txt-credenciales-fase5.md` → patrón de residual aceptado
- `.agents/spec/spec-conventions.md` · `.agents/spec/spec-template.md`
- `.agents/skills/testing-conventions.md` · `.agents/skills/workflow-ramas.md`
- `AGENTS.md` → "Commands" (suite 266 → 273)

---

### Notas de implementación

1. **Cambios exactos en `config/session.php`:**
   ```php
   'driver' => env('SESSION_DRIVER', 'database'),
   // ...
   'secure' => env('SESSION_SECURE_COOKIE', str_starts_with((string) env('APP_URL'), 'https://')),
   ```
   El cast `(string)` cubre `APP_URL` ausente (`null` → `''` → false). `env()` sigue legal en `config/` (regla 4.3: prohibido solo en `app/`).
2. **Mecanismo U1–U5:** mutar `$_ENV` directo (`$_ENV['APP_URL'] = 'https://...'`) y `require base_path('config/session.php')` — el reader de dotenv consulta `$_ENV` en cada `env()`, la inmutabilidad solo guarda el writer. `tearDown`: restaurar `APP_URL='http://localhost'` y unset de `SESSION_SECURE_COOKIE`/`SESSION_DRIVER`. Correr la focal **antes** que la suite completa para confirmar que no hay contaminación entre tests del mismo proceso.
3. **F2:** `config(['session.driver' => 'database'])` como primera línea del test (resolución lazy del handler al primer request); login real `POST /login/store` (patrón `WebLoginSmokeTest` + trait `CreatesUsers`, factory `landlord()`) y aserción de fila con `DB::table('sessions')->where('user_id', $user->id)->exists()`. Si el cliente de test no retuviera la cookie entre los dos requests, usar `$this->withCookie(...)` con el ID de la fila — el contrato (fila en DB + request autenticado leído de DB) no cambia.
4. **Redacciones documentales (patrón trust-hosts):**
   - `seguridad.md` #22 → añadir al final: "— **mitigado (2026-11-21, spec `spec-session-database-secure-fase5`)**: default config `database` + migración `sessions`; cookie `secure` inferida de `APP_URL` (válvula `SESSION_SECURE_COOKIE`); tests U1–U5 + F1–F2. Residual operacional: flip de `SESSION_DRIVER` + `config:cache` en producción pendiente de evidencia."
   - `plan-accion.md` fila 5.5 → "**Mitigado (2026-11-21):** spec `spec-session-database-secure-fase5` — driver `database` por default (migración `sessions`), cookie `secure` inferida de `APP_URL` (desacoplada de 5.6), U1–U5 + F1–F2; residual: flip del `.env` de producción + re-cache pendientes de evidencia (patrón 5.9)."
   - `plan-accion.md` Métricas de éxito → línea: "✅ Session driver `database` por default + cookie `secure` inferida de `APP_URL` (Fase 5 / 5.5 — spec-session-database-secure-fase5, 2026-11-21; residual operativo: `.env` de producción)".
   - `.env.example` l.28–29 → `SESSION_DRIVER=database`, `SESSION_LIFETIME=120` intacto, y bajo ellas:
     ```
     # La cookie de sesión toma Secure automáticamente cuando APP_URL es https.
     # Descomenta para forzar true/false manualmente (p.ej. http detrás de TLS-terminating proxy).
     # SESSION_SECURE_COOKIE=
     ```
5. **Verificación rápida:**
   ```bash
   php artisan session:table && php artisan migrate
   php artisan test tests/Unit/Config/SessionConfigTest.php tests/Feature/Security/SessionSmokeTest.php
   composer analyse && composer lint && php artisan test
   ```
