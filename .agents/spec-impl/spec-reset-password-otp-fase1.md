---
id: spec-reset-password-otp-fase1
titulo: "Reset de contraseña sin OTP (API) — Fase 1 / 1.3"
estado: Revisado
fecha: 2026-11-18
origen: .analisis/plan/plan-accion.md (Fase 1 / 1.3) — Seg-3
cobertura: 4 casos (A8, A11–A13) sobre el flujo de reset de contraseña API
---

# Spec — Reset de contraseña sin OTP (API)

> Resumen: remediar Seg-3 validando el código OTP (`remember_token`) y su expiración (`email_verified_at`) antes de cambiar la contraseña en `Api\Auth\ResetPasswordController::userResetPassword`. Unifica el TTL del OTP a 15 minutos y limpia el código tras un reset exitoso.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 1 / 1.3) — `Seg-3`.

---

## 1. Objetivo

Cerrar el account takeover en la API: `/api/v1/user-password-reset` hoy cambia la contraseña con solo `email` + `password`. Tras el fix, exige un `otp` válido y no expirado (enviado por `/send-reset-code` o `/resend-otp`), comparado con `hash_equals`, y limpia el código tras el uso.

## 2. Alcance

### IN
- `Api/Auth/ResetPasswordController::userResetPassword`: validar `otp`, comprobar expiración y comparar con `hash_equals`.
- Limpiar `remember_token` (y resetear `email_verified_at`) tras un reset exitoso.
- Unificar TTL del OTP a 15 minutos en `ForgotPasswordController::sendResetCode` y `resendOtp`.
- Actualizar el texto de expiración en la vista del email (`resources/views/mail/password-reset.blade.php`).
- Conversión de A8 (caracterización) a test de bloqueo + A11, A12, A13.

### OUT
- No se toca el flujo web de reset (token broker estándar de Laravel).
- No se añade throttle a los endpoints (Seg-17, Fase 3.7).
- No se cambia la política de contraseña (se mantiene `min:4`; Seg-10, Fase 3.2).
- No se añaden columnas nuevas a `users` (se reutiliza `remember_token` + `email_verified_at`).
- No se corrige la comparación `==` de OTP en registro/login (Seg-9, Fase 3.1), salvo como referencia.

### Cobertura objetivo
- 4 casos (A8, A11–A13) sobre `/api/v1/user-password-reset`, más ajuste de TTL en el flujo de envío.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Test de caracterización A8 existente | En `ApiAuthSmokeTest`, marcado `@group characterization` | Convertir a test de bloqueo (ver §6) |
| P2 | `ForgotPasswordController` almacena OTP | `remember_token` = código, `email_verified_at` = expiración | Reutilizar en el fix |
| P3 | `CreatesUsers` trait | Provee `createLandlord()` | Usar en los tests |
| P4 | `RefreshDatabase` | Usado por `ApiAuthSmokeTest` | Mantener |
| P5 | `Mail::fake()` para envío | Usado en A7 | Mantener para casos de envío si se añaden |

## 4. Estructura propuesta

```
app/Http/Controllers/Api/Auth/ResetPasswordController.php   # MODIFICADO: validar OTP + expiración
app/Http/Controllers/Api/Auth/ForgotPasswordController.php  # MODIFICADO: TTL 15 min unificado
resources/views/mail/password-reset.blade.php               # MODIFICADO: texto de expiración
tests/Feature/Auth/ApiAuthSmokeTest.php                    # MODIFICADO: A8 convertido + A11–A13
tests/README.md                                           # MODIFICADO: Seg-3 sin casos de caracterización
```

## 5. Decisiones técnicas

- **Framework:** Laravel 10, PHPUnit 10, `RefreshDatabase`.
- **DB:** MySQL `dbrentpro_test`.
- **Auth en tests:** `postJson` (endpoints públicos, sin Sanctum).
- **Almacenamiento OTP:** `remember_token` (código de 6 dígitos) + `email_verified_at` (expiración).
- **TTL unificado:** 15 minutos (`now()->addMinutes(15)`) en `sendResetCode` y `resendOtp`; se elimina el uso de `env('OTP_VISIBILITY_TIME')`.
- **Comparación:** `hash_equals((string) $user->remember_token, (string) $request->otp)`.
- **Expiración:** rechazar si `remember_token` es null, si `email_verified_at` es null o si `now()->greaterThan($user->email_verified_at)`.
- **Respuesta de OTP inválido/expirado:** `422` con JSON `{"message": "Invalid or expired OTP code."}`.
- **Falta de `otp` en el payload:** validación Laravel `required` → 422 automático.
- **Tras éxito:** `password = bcrypt(...)`, `remember_token = null`, `email_verified_at = now()`.
- **Política de contraseña:** se mantiene `min:4` (Seg-10 aparte).
- **Mocks obligatorios:** `Mail::fake()` solo si se prueba el envío; los casos de reset no envían email.

## 6. Catálogo de casos

### 6.1 Auth API (`A`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| A8 | Reset sin OTP bloqueado | Usuario existente (landlord) | `POST /api/v1/user-password-reset` con `email` + `password` (sin `otp`) | 422; contraseña no cambia |
| A11 | Reset con OTP inválido bloqueado | Usuario con `remember_token=123456` y expiración futura | `POST /api/v1/user-password-reset` con `otp=999999` | 422 `"Invalid or expired OTP code."`; contraseña no cambia |
| A12 | Reset con OTP expirado bloqueado | Usuario con `remember_token=123456` y `email_verified_at` en el pasado | `POST /api/v1/user-password-reset` con `otp=123456` | 422 `"Invalid or expired OTP code."`; contraseña no cambia |
| A13 | Reset con OTP válido cambia contraseña | Usuario con `remember_token=123456` y expiración futura | `POST /api/v1/user-password-reset` con `otp=123456` + `password` | 200 `"Your password has been changed!"`; contraseña actualizada; `remember_token` = null |

> Tras el fix, A8 deja de ser `@group characterization`; Seg-3 queda remediado.

### Tests de caracterización

Ninguno: Seg-3 queda remediado por este spec; A8 se convierte a test de bloqueo.

## 7. Criterios de aceptación (Definition of Done)

- [ ] `userResetPassword` valida `otp` (required) y comprueba expiración con `hash_equals`.
- [ ] OTP inválido o expirado devuelve 422 `"Invalid or expired OTP code."` sin cambiar la contraseña.
- [ ] Reset sin `otp` devuelve 422 por validación Laravel.
- [ ] Reset con OTP válido cambia la contraseña, limpia `remember_token` y actualiza `email_verified_at`.
- [ ] `sendResetCode` y `resendOtp` usan TTL de 15 minutos.
- [ ] Vista del email muestra expiración de 15 minutos.
- [ ] A8 ya no lleva `@group characterization`.
- [ ] `php artisan test tests/Feature/Auth/ApiAuthSmokeTest.php` en verde.
- [ ] `tests/README.md` actualizado: Seg-3 sin casos de caracterización.
- [ ] `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| `email_verified_at` se reutiliza como expiración del OTP y como verificación de email | Medio | Patrón ya existente en registro; se mantiene por consistencia. |
| Cambiar TTL de 60/3 min a 15 min puede afectar UX si el email tarda | Bajo | 15 min es un compromiso razonable; se puede ajustar vía constante. |
| `hash_equals` requiere strings; `remember_token` puede ser null | Bajo | Cast a string y comprobación de null antes de comparar. |
| Throttle ausente en endpoints de reset (Seg-17) | Medio | Fuera de alcance; se aborda en Fase 3.7. |
| Política de contraseña débil (`min:4`) persiste | Medio | Fuera de alcance; se aborda en Seg-10 (Fase 3.2). |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

## 9. Estimación

- Controlador + TTL + vista: 0.5 día.
- Tests (A8, A11–A13) y README: 0.5 día.
- Total: 1 día.

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 1 / 1.3
- `.analisis/seguridad.md` → Seg-3
- `.agents/spec/spec-conventions.md`
- `.agents/spec-impl/spec-tests-smoke-fase0.md` (A8 original)
- `tests/README.md`
---