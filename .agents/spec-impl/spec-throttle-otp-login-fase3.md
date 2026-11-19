---
id: spec-throttle-otp-login-fase3
titulo: "Throttle OTP y login (Fase 3 / 3.7)"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 3 / 3.7) — Seg-16/17
cobertura: 12 rutas sensibles (6 web + 6 API) con `throttle:5,1`; límite general de la API bajado a 60/min; 3 casos de verificación de 429
---

# Spec — Throttle OTP y login (Fase 3 / 3.7)

> Resumen: mitigar Seg-16 (límite API de 2000/min demasiado permisivo) y Seg-17 (ausencia de throttle en endpoints OTP/reset/login) aplicando `throttle:5,1` a las rutas sensibles y bajando el límite general de la API a 60/min, sin cambiar mensajes ni lógica de negocio.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 3 / 3.7) — `Seg-16`, `Seg-17`.

---

## 1. Objetivo

Cerrar la superficie de fuerza bruta y OTP bombing en los flujos de autenticación (web y API): limitar a 5 intentos por minuto por IP en login, registro, verificación de OTP, reenvío de OTP y reset de contraseña, y reducir el techo general de la API de 2000 a 60 req/min.

---

## 2. Alcance

### IN

- Aplicar `throttle:5,1` a 6 rutas POST sensibles en `routes/auth.php`.
- Aplicar `throttle:5,1` a 6 rutas sensibles en `routes/api.php` (bajo `/api/v1`).
- Bajar el límite del rate limiter `api` de 2000/min a 60/min en `RouteServiceProvider`.
- Mantener la capa `throttle:api` (60/min) además del `throttle:5,1` en las rutas sensibles (el más restrictivo aplica).
- Añadir 3 casos de test que verifiquen `429 Too Many Requests` en el 6.º intento.

### OUT

- Rate limiters nombrados por email+IP (se usa el shorthand por IP+ruta del plan).
- Cambio de mensajes, códigos de respuesta o TTL de OTP.
- Throttle en rutas admin no sensibles ni en vistas GET.
- Unificación de la lógica de OTP (Fase 4).
- Rotación de IP / protección avanzada anti-bot (fuera de alcance).

### Cobertura objetivo

12 rutas sensibles throttled (6 web + 6 API) + límite general API a 60/min + 3 tests de 429.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Middleware `throttle` registrado en `Kernel.php` (`ThrottleRequests::class`) | Existe | Sin acción |
| P2 | Rate limiter `api` definido en `RouteServiceProvider` | Existe (2000/min) | Cambiar a 60/min |
| P3 | Cache driver `array` en tests (`phpunit.xml`) | Existe | El estado del throttle se resetea por test (app recreada) |
| P4 | `MAIL_USERNAME` seteado en `phpunit.xml` | Existe | Requerido por endpoints OTP que envían mail |
| P5 | Tests smoke de auth existentes (`WebLoginSmokeTest`, `WebSignupSmokeTest`, `WebPasswordResetSmokeTest`, `ApiAuthSmokeTest`) | Existen | Reutilizar; añadir 3 casos de throttle |

---

## 4. Estructura propuesta

```
routes/auth.php                              # MODIFICADO: middleware throttle:5,1 en 6 rutas POST
routes/api.php                               # MODIFICADO: middleware throttle:5,1 en 6 rutas sensibles
app/Providers/RouteServiceProvider.php       # MODIFICADO: 2000 -> 60 en RateLimiter::for('api')
tests/Feature/Auth/WebLoginSmokeTest.php     # MODIFICADO: + L7 (throttle login web)
tests/Feature/Auth/ApiAuthSmokeTest.php      # MODIFICADO: + A20 (throttle sign-in API), + A21 (throttle send-reset-code API)
```

---

## 5. Decisiones técnicas

- **Framework:** Laravel 10, middleware `throttle:5,1` (5 intentos / 1 minuto) por IP+ruta, vía `ThrottleRequests`.
- **Mecanismo:** shorthand `throttle:5,1` en las rutas, sin rate limiters nombrados (alineado con `.agents/skills/secure-coding.md` punto 9).
- **Capas:** las rutas API sensibles quedan con `throttle:api` (60/min, por usuario/IP) **y** `throttle:5,1` (5/min, por IP+ruta). El límite más restrictivo aplica en cada ventana.
- **API general:** `RouteServiceProvider::RateLimiter::for('api')` devuelve `Limit::perMinute(60)` (antes 2000), clave `$request->user()?->id ?: $request->ip()`.
- **Clave del throttle sensible:** la firma por defecto de `ThrottleRequests` (dominio + IP si no hay usuario autenticado), distinta por ruta; no compartida entre endpoints.
- **Respuesta al exceder:** `429 Too Many Requests` con header `Retry-After` (generada por `ThrottleRequestsException`). No se depende del mensaje en los tests.
- **Efecto en controladores:** ninguno. El middleware se evalúa antes del controlador; las peticiones throttled no ejecutan lógica de negocio ni envían mail.
- **Tests:** usar bucle de 6 peticiones en un mismo test; las 5 primeras deben tener el status esperado del endpoint (401/200/etc.) y la 6.ª debe ser `429`. Aserción sobre status + presencia de `Retry-After`.

---

## 6. Catálogo de casos

### 6.1 Login web (`L`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| L7 | Login web throttled | 5 `POST /login/store` con credenciales inválidas (mismo IP) | 6.º `POST /login/store` con credenciales inválidas | `429`, header `Retry-After` presente, sin sesión |

### 6.2 Auth API (`A`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| A20 | Sign-in API throttled | 5 `POST /api/v1/sign-in` con credenciales inválidas (mismo IP) | 6.º `POST /api/v1/sign-in` con credenciales inválidas | `429`, header `Retry-After` presente |
| A21 | Send-reset-code API throttled | Usuario existente; `Mail::fake()`; 5 `POST /api/v1/send-reset-code` con su email | 6.º `POST /api/v1/send-reset-code` con el mismo email | `429`, header `Retry-After` presente; no se envía mail en el 6.º |

### Tests de caracterización

Ninguno. Este spec remedia Seg-16/17; los casos L7, A20 y A21 verifican el comportamiento seguro nuevo y deben pasar (429) tras la implementación.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `routes/auth.php`: las 6 rutas POST sensibles (`/login/store`, `/signup`, `/otp-submit`, `/otp-resend`, `/forgot-password`, `/reset-password`) llevan `throttle:5,1`.
- [x] `routes/api.php`: las 6 rutas sensibles (`sign-in`, `sign-up`, `submit-otp`, `send-reset-code`, `resend-otp`, `user-password-reset`) llevan `throttle:5,1`.
- [x] `RouteServiceProvider`: `Limit::perMinute(60)` (antes 2000) en el rate limiter `api`.
- [x] No se eliminó `throttle:api` de las rutas sensibles (capas combinadas).
- [x] L7, A20 y A21 pasan (429 + `Retry-After`) en `php artisan test tests/Feature/Auth/WebLoginSmokeTest.php tests/Feature/Auth/ApiAuthSmokeTest.php`.
- [x] Los tests smoke existentes siguen en verde (el estado del throttle se resetea por test).
- [x] `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Falsos positivos en tests que hagan >5 requests al mismo endpoint en un solo test | Medio | Revisar tests existentes; el estado se resetea por test (app recreada), pero un test con >5 requests al mismo endpoint fallaría. Ajustar si aparece |
| Throttle por IP no protege contra rotación de IP | Medio | Aceptado (alcance del plan); mejora futura con rate limiter nombrado por email+IP (Fase 4) |
| Bajar API a 60/min puede afectar clientes legítimos con alto volumen | Bajo | 60/min es el estándar del plan; ajustable vía `RateLimiter` si se requiere |
| `throttle:5,1` en `/signup` puede bloquear creación legítima masiva | Bajo | 5/min por IP es razonable para registro; administradores usan CLI/seeders |
| Respuesta 429 sin JSON localizado en web | Bajo | Se aserta status + header, no mensaje; los tests usan `postJson` donde aplica |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

---

## 9. Estimación

- Rutas + `RouteServiceProvider`: 0.25 día.
- Tests (L7, A20, A21): 0.25 día.
- Verificación (lint, analyse, suite): 0.25 día.
- Total: ~0.75 día.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 3 / 3.7
- `.analisis/seguridad.md` → Seg-16, Seg-17
- `.agents/skills/secure-coding.md` → punto 9 (Throttle)
- `.agents/spec/spec-conventions.md`
- `.agents/spec-impl/spec-tests-smoke-fase0.md` (L1–L6, A1–A10, R1–R5)
- `tests/README.md`
- `app/Http/Kernel.php` (`throttle` middleware alias)
- `app/Providers/RouteServiceProvider.php` (`RateLimiter::for('api')`)
