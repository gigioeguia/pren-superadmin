---
id: spec-password-policy-fase3
titulo: "Política de contraseña (Fase 3 / 3.2)"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 3 / 3.2) — Seg-10
cobertura: 4 flujos (registro web/API, reset web/API) + 4 casos negativos
---

# Spec — Política de contraseña (Fase 3 / 3.2)

> Resumen: unificar la validación de contraseña en `Password::min(8)->mixedCase()->numbers()->max(100)` mediante el default global de Laravel, aplicándola a registro y reset (web y API), y demostrar el rechazo de contraseñas débiles.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 3 / 3.2) — `Seg-10`.

---

## 1. Objetivo

Eliminar las políticas inconsistentes (`min:6` registro, `min:4` reset API, sin complejidad) y establecer una única regla compartida para toda contraseña fijada por el usuario en los flujos de registro y reset.

---

## 2. Alcance

### IN

- `App\Providers\AppServiceProvider::boot`: `Password::defaults(Password::min(8)->mixedCase()->numbers()->max(100))`.
- `Auth\SignupController::store` (registro web).
- `Api\Auth\RegistrationController::maanRegistration` (registro API).
- `Auth\NewPasswordController::store` (reset web).
- `Api\Auth\ResetPasswordController::userResetPassword` (reset API).
- Actualizar payloads de tests existentes a contraseñas conformes.
- Añadir 4 casos negativos (uno por flujo) que rechacen contraseñas débiles.

### OUT

- Flujos de perfil (admin/landlord) y creación de usuarios por admin/landlord (no citados en Seg-10).
- `uncompromised()` / historial de contraseñas.
- Throttle (Fase 3 / 3.7).
- Cambio de seeders/factories o tests de login (siguen usando `password`).

### Cobertura objetivo

4 flujos con política aplicada + 4 casos negativos de rechazo.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | `Illuminate\Validation\Rules\Password` disponible | Laravel 10 | Sin acción |
| P2 | `AppServiceProvider::boot` | Existe | Añadir `Password::defaults(...)` |
| P3 | Tests smoke de auth | Existen | Actualizar payloads y añadir casos negativos |
| P4 | `RefreshDatabase` | Usado | Mantener |

---

## 4. Estructura propuesta

```
app/Providers/AppServiceProvider.php                          # MODIFICADO: default de Password
app/Http/Controllers/Auth/SignupController.php                # MODIFICADO: password validation
app/Http/Controllers/Api/Auth/RegistrationController.php      # MODIFICADO: password validation
app/Http/Controllers/Auth/NewPasswordController.php           # MODIFICADO: password validation
app/Http/Controllers/Api/Auth/ResetPasswordController.php      # MODIFICADO: password validation
tests/Feature/Auth/WebSignupSmokeTest.php                     # MODIFICADO: payloads + caso negativo
tests/Feature/Auth/ApiAuthSmokeTest.php                       # MODIFICADO: payloads + caso negativo
tests/Feature/Auth/WebPasswordResetSmokeTest.php              # MODIFICADO: payloads + caso negativo
```

---

## 5. Decisiones técnicas

- **Regla única:** `Password::defaults(Password::min(8)->mixedCase()->numbers()->max(100))` en `AppServiceProvider::boot`.
- **Registro web:** `'password' => Password::required()` (reemplaza `required|min:6|max:100`).
- **Registro API:** idem.
- **Reset web:** `'password' => ['required', 'confirmed', Password::default()]` (mantiene `confirmed`).
- **Reset API:** `'password' => Password::required()` (reemplaza `required|min:4`; sin `confirmed`).
- **Mensajes:** los generados por Laravel para la regla `Password` (min 8, mixed case, número, max 100).
- **Tests existentes:** actualizar `'password' => 'password'` → `'Password123'` y `'new-password'`/`'new-secure-password'` → `'NewPassword123'` en los casos de éxito.
- **Casos negativos:** usar `'password'` (o `'Password'`) y esperar `422`/errores de validación en el campo `password`.

---

## 6. Catálogo de casos

### 6.1 Registro web (`S`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| S13 | Contraseña débil rechazada | `otp_status=off` | `POST /signup` con `password='password'` | `422`, error en `password` |

### 6.2 Registro API (`A`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| A18 | Contraseña débil rechazada | Plan creado | `POST /api/v1/sign-up` con `password='password'` | `422`, error en `password` |

### 6.3 Reset web (`R`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| R6 | Contraseña débil rechazada | Token válido | `POST /reset-password` con `password='password'` | `302` con errores en `password` |

### 6.4 Reset API (`A`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| A19 | Contraseña débil rechazada | Usuario con OTP válido | `POST /api/v1/user-password-reset` con `otp=123456`, `password='password'` | `422`, error en `password` |

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `AppServiceProvider::boot` define `Password::defaults(Password::min(8)->mixedCase()->numbers()->max(100))`.
- [x] Los 4 flujos usan `Password::required()` / `Password::default()`.
- [x] No quedan `min:6`/`min:4` en validaciones de contraseña de los 4 flujos.
- [x] Casos negativos S13, A18, R6, A19 devuelven error de validación en `password`.
- [x] Casos de éxito de registro/reset siguen pasando con contraseñas conformes.
- [x] `php artisan test tests/Feature/Auth/WebSignupSmokeTest.php tests/Feature/Auth/ApiAuthSmokeTest.php tests/Feature/Auth/WebPasswordResetSmokeTest.php` en verde.
- [x] `composer lint` y `composer analyse` sin nuevas violaciones.

---

## 8. Riesgos

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Tests existentes usan `password` | Medio | Actualizar payloads a `Password123`/`NewPassword123` en los casos de éxito |
| Web reset usa `confirmed` | Bajo | Mantener `confirmed` junto a `Password::default()` |
| Login sigue usando `password` | Bajo | Login no valida política; sin cambios |
| Admin create flows quedan sin política | Bajo | Fuera de alcance (Seg-10); se puede ampliar después |

---

## 9. Estimación

- AppServiceProvider + 4 controladores: 0.5 día.
- Tests (payloads + 4 negativos): 0.5 día.
- Total: 1 día.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 3 / 3.2
- `.analisis/seguridad.md` → Seg-10
- `.agents/spec/spec-conventions.md`
---