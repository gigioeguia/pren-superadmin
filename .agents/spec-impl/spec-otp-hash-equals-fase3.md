---
id: spec-otp-hash-equals-fase3
titulo: "OTP con hash_equals (Fase 3 / 3.1)"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 3 / 3.1) — Seg-9
cobertura: 2 controladores (Web SignupController::submitOtp, API RegistrationController::submitOtp) y 8 casos (S9–S12, A14–A17)
---

# Spec — OTP con hash_equals (Fase 3 / 3.1)

> Resumen: cerrar el timing attack y el bypass por comparación laxa (`==`) en la verificación de OTP de registro, reemplazando la comparación por `hash_equals` con guard explícito de `null` en los dos `submitOtp` pendientes.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 3 / 3.1) — `Seg-9`.

---

## 1. Objetivo

Eliminar la comparación no constante-tiempo (`$user->remember_token == $request->otp`) en los flujos de verificación de OTP de registro (web y API), cerrando el bypass por coerción de tipos (p. ej. `null == '0000'` es `true` en PHP) y el timing attack sobre el código de 6 dígitos.

---

## 2. Alcance

### IN

- `Auth\SignupController::submitOtp` (`POST /otp-submit`): reemplazar `==` por `hash_equals` con guard de `null`.
- `Api\Auth\RegistrationController::submitOtp` (`POST /api/v1/submit-otp`): idem.
- Mantener los códigos de respuesta actuales (400 inválido, 410 expirado, 200/Ok éxito) y sus mensajes.
- Añadir casos de test que demuestren el bypass cerrado (`remember_token = null` + `otp = '0000'`) en ambos flujos.
- Añadir casos de test para expirado/inválido en el flujo API, hoy sin cobertura.

### OUT

- `SigninController`: no compara OTP (solo envía código al login de usuario no verificado); no entra en Seg-9 real.
- `ResetPasswordController`: ya mitigado en Fase 1 / 1.3 (usa `hash_equals`).
- Throttle de OTP/endpoints (Fase 3 / 3.7).
- Política de contraseña (Fase 3 / 3.2).
- Validación de usuario inexistente en `SignupController::submitOtp` (fuera de alcance; posible Fase 5).
- Unificación de TTL del OTP (se mantiene el actual).
- Cambio de códigos de respuesta o mensajes.

### Cobertura objetivo

8 casos: 4 por flujo (válido, expirado, inválido, token null / bypass cerrado).

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Tests smoke existentes (`WebSignupSmokeTest`, `ApiAuthSmokeTest`) | Existen y cubren el caso válido (S9, A14) | Reutilizar; añadir S12 y A15–A17 |
| P2 | `UserFactory` con estado `landlord()` | Existe | Reutilizar en los casos nuevos |
| P3 | Factory de `Option` (web) | Existe en `WebSignupSmokeTest::otpOption` | Reutilizar si se prueba el flujo completo de signup |
| P4 | `RefreshDatabase` en ambos tests | Usado | Mantener |
| P5 | `hash_equals` disponible | PHP 8.2 | Sin acción |
| P6 | `Mail::fake()` para signup con OTP on | Usado en tests de signup | No necesario para `submitOtp` |

---

## 4. Estructura propuesta

```
app/Http/Controllers/Auth/SignupController.php               # MODIFICADO: submitOtp comparación
app/Http/Controllers/Api/Auth/RegistrationController.php     # MODIFICADO: submitOtp comparación
tests/Feature/Auth/WebSignupSmokeTest.php                   # MODIFICADO: + S12 (bypass cerrado)
tests/Feature/Auth/ApiAuthSmokeTest.php                     # MODIFICADO: + A15, A16, A17
```

---

## 5. Decisiones técnicas

- **Framework:** Laravel 10, PHPUnit 10, `RefreshDatabase`.
- **DB:** MySQL `dbrentpro_test`.
- **Comparación:** `if ($user->remember_token === null || ! hash_equals((string) $user->remember_token, (string) $request->otp))` → inválido.
- **Orden de comprobación:** comparación (con guard de `null`) primero; si es inválida se devuelve 400 sin evaluar expiración. Si es válida, se evalúa `email_verified_at` (410 si expirada).
- **Respuestas:**
  - Inválido o token null: `400` JSON `{"error": "Invalid otp."}`.
  - Expirado: `410` JSON `{"error": "The verification otp has been expired."}`.
  - Válido: `200`/`Ok` y login (web) o token (API); se limpia `remember_token` y se actualiza `email_verified_at`.
- **Validación de entrada:** se mantiene `otp => required|min:4|max:15` y `email => required|email`. El caso `otp = '0000'` pasa la validación (longitud 4) y es el vector del bypass.
- **Cast:** ambos operandos a `string` antes de `hash_equals`.
- **Sin cambios en:** TTL, mensajes, rutas, middleware, throttle.

---

## 6. Catálogo de casos

### 6.1 Web Signup (`S`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| S9 | OTP válido inicia sesión | Usuario con `remember_token=123456` y `email_verified_at` futura | `POST /otp-submit` con `otp=123456`, `email` | `200`, autenticado, `remember_token` = null |
| S10 | OTP expirado devuelve 410 | Usuario con `remember_token=123456` y `email_verified_at` pasada | `POST /otp-submit` con `otp=123456` | `410`, guest, token sin cambios |
| S11 | OTP inválido devuelve 400 | Usuario con `remember_token=123456` y expiración futura | `POST /otp-submit` con `otp=999999` | `400` `"Invalid otp."`, guest |
| S12 | Bypass cerrado (token null) | Usuario con `remember_token=null` y `email_verified_at` futura | `POST /otp-submit` con `otp='0000'` | `400` `"Invalid otp."`, guest, `remember_token` sigue null |

> Antes del fix, S12 devolvía `200` y autenticaba (bypass por `null == '0000'`); tras el fix debe ser `400`.

### 6.2 Auth API (`A`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| A14 | OTP válido devuelve token | Usuario con `remember_token=123456` y `email_verified_at` futura | `POST /api/v1/submit-otp` con `otp=123456`, `email` | `200`, `token` no vacío |
| A15 | OTP expirado devuelve 410 | Usuario con `remember_token=123456` y `email_verified_at` pasada | `POST /api/v1/submit-otp` con `otp=123456` | `410` `"The verification otp has been expired."`, sin token |
| A16 | OTP inválido devuelve 400 | Usuario con `remember_token=123456` y expiración futura | `POST /api/v1/submit-otp` con `otp=999999` | `400` `"Invalid otp."`, sin token |
| A17 | Bypass cerrado (token null) | Usuario con `remember_token=null` y `email_verified_at` futura | `POST /api/v1/submit-otp` con `otp='0000'` | `400` `"Invalid otp."`, sin token, `remember_token` sigue null |

> Antes del fix, A17 autenticaba y devolvía token (bypass); tras el fix debe ser `400`.

### Tests de caracterización

Ninguno: este spec remedia Seg-9. Los casos S12 y A17 documentan el bypass que se cierra y deben pasar (400) tras la implementación, no caracterizar un bug a romper en una fase posterior.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `SignupController::submitOtp` usa `hash_equals` con guard de `null`; sin `==` ni `===` sueltos para OTP.
- [x] `RegistrationController::submitOtp` idem.
- [x] Bypass con `remember_token=null` y `otp='0000'` devuelve `400` en ambos flujos (S12, A17).
- [x] Casos válidos/expirados/inválidos siguen pasando (S9–S11, A14–A16).
- [x] No quedan comparaciones laxas de OTP en los dos controladores.
- [x] `php artisan test tests/Feature/Auth/WebSignupSmokeTest.php tests/Feature/Auth/ApiAuthSmokeTest.php` en verde.
- [x] `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| `hash_equals` requiere strings; `remember_token` puede ser null | Bajo | Guard explícito de `null` + cast a `string` en ambos operandos |
| `remember_token` almacenado como entero en BD | Bajo | Cast a `string` en la comparación; el código generado se persiste como string |
| Fuga de timing residual en la comprobación de expiración | Bajo | Se mantiene el orden comparar→expirar; el bypass es el riesgo real y queda cerrado |
| Cambio mínimo no unifica TTL ni mensajes | Bajo | Fuera de alcance (Fase 3.2 / 3.7) |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

---

## 9. Estimación

- Controladores (2 `submitOtp`): 0.5 día.
- Tests (S12, A15–A17) y README: 0.5 día.
- Total: 1 día.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 3 / 3.1
- `.analisis/seguridad.md` → Seg-9
- `.agents/spec/spec-conventions.md`
- `.agents/spec-impl/spec-tests-smoke-fase0.md` (S1–S8, A1–A10)
- `.agents/spec-impl/spec-reset-password-otp-fase1.md` (patrón de `hash_equals` + guard de null)
- `tests/README.md`
---