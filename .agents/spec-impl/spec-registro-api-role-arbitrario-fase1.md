---
id: spec-registro-api-role-arbitrario-fase1
titulo: "Registro API con role arbitrario — Fase 1 / 1.4"
estado: Implementado
fecha: 2026-11-18
origen: .analisis/plan/plan-accion.md (Fase 1 / 1.4) — Seg-4
cobertura: 3 casos HTTP (A5, A14, A15) + 1 unitario (U1) sobre /api/v1/sign-up y User::$fillable
---

# Spec — Registro API con `role` arbitrario (Fase 1 / 1.4)

> Resumen: remediar Seg-4 eliminando `role` del input válido y de `$fillable` de `User` en el registro de la API (`Api\Auth\RegistrationController::maanRegistration`), forzando siempre `role = 'landlord'`. Cualquier valor enviado por el cliente (incl. `superadmin`/`admin`) se ignora. Se actualizan los call sites internos que asignaban `role` por mass assignment para que lo asignen por propiedad directa.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 1 / 1.4) — `Seg-4`.

---

## 1. Objetivo

Cerrar la escalada de privilegios por registro en la API: hoy `POST /api/v1/sign-up` valida `'role' => 'required|string'` y hace `User::updateOrCreate(..., $request->except('password') + [...])`. Como `role` es fillable, un atacante puede registrarse con `role: "superadmin"` y obtener una cuenta privilegiada con token válido. Tras el fix, `role` deja de ser input válido ni fillable, y toda cuenta creada por este endpoint queda con `role = 'landlord'`.

## 2. Alcance

### IN
- `Api\Auth\RegistrationController::maanRegistration`: quitar `'role' => 'required|string'` de la validación; dejar de pasar `role` desde `$request`; forzar `'role' => 'landlord'` en ambas ramas (OTP on y OTP off).
- `App\Models\User`: quitar `'role'` de `$fillable`.
- Actualizar call sites internos que asignaban `role` por mass assignment para asignarlo por propiedad directa (no mass assignment):
  - `Auth\SignupController::maanRegistration` (ambas ramas, ya fuerza `landlord`).
  - `Admin\UserController::store` y `::update` (rol elegido por el admin).
  - `Admin\AcnooLandlordController::store` (`landlord`).
  - `Admin\AcnooTenantController::store` (`tenant`).
  - `Api\AcnooTenantController::store` (`tenant`).
  - `Landlord\AcnooTenantController::store` (`tenant`).
  - `Database\Factories\UserFactory` (definition y states).
- Conversión de A5 (caracterización) a comportamiento seguro + A14 y A15.
- U1: verificación unitaria de que `role` no es mass-assignable.

### OUT
- No se cambia la política de contraseña (Seg-10, Fase 3.2): se mantiene `min:6`.
- No se corrige la comparación laxa del OTP (Seg-9, Fase 3.1).
- No se añade throttle a los endpoints (Seg-17, Fase 3.7).
- No se modifican los roles Spatie (`HasRoles`): la columna `role` sigue siendo la fuente de verdad para middleware custom (`LandlordMiddleware`, `RedirectIfAuthenticated`).
- No se cambia el flujo OTP ni el envío de email.
- No se toca `AdminMiddleware` (Seg-2) ni otras rutas.

### Cobertura objetivo
- 3 casos HTTP (A5, A14, A15) sobre `POST /api/v1/sign-up`, más U1 sobre el modelo `User`. 100 % de las ramas OTP on/off del endpoint cubiertas a nivel de código (A15 ejercita la rama OTP on).

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Test de caracterización A5 existente | En `ApiAuthSmokeTest`, marcado `@group characterization` | Convertir a comportamiento seguro (ver §6) |
| P2 | `User::$fillable` incluye `role` | `app/Models/User.php` | Quitarlo (ver §4) |
| P3 | Validación del endpoint exige `role` | `RegistrationController::maanRegistration` | Quitar la regla |
| P4 | `Plan::factory()` disponible en tests | Usado por A4/A5 | Reutilizar |
| P5 | `RefreshDatabase` | Usado por `ApiAuthSmokeTest` | Mantener |
| P6 | Helper `get_option('email-varification')` | Usado por el controller | Configurar en A15 para ejercitar OTP on |

## 4. Estructura propuesta

```
app/Models/User.php                                    # MODIFICADO: quitar 'role' de $fillable
app/Http/Controllers/Api/Auth/RegistrationController.php # MODIFICADO: sin regla role; fuerza landlord
app/Http/Controllers/Auth/SignupController.php          # MODIFICADO: asignación directa de role
app/Http/Controllers/Admin/UserController.php           # MODIFICADO: store/update asignan role por propiedad
app/Http/Controllers/Admin/AcnooLandlordController.php  # MODIFICADO: asignación directa
app/Http/Controllers/Admin/AcnooTenantController.php    # MODIFICADO: asignación directa
app/Http/Controllers/Api/AcnooTenantController.php      # MODIFICADO: asignación directa
app/Http/Controllers/Landlord/AcnooTenantController.php # MODIFICADO: asignación directa
database/factories/UserFactory.php                     # MODIFICADO: asignación directa de role
tests/Feature/Auth/ApiAuthSmokeTest.php                # MODIFICADO: A5 convertido + A14/A15
tests/Unit/UserModelTest.php (nuevo)                  # U1
tests/README.md                                       # MODIFICADO: Seg-4 sin casos de caracterización
```

## 5. Decisiones técnicas

- **Framework:** Laravel 10, PHPUnit 10, `RefreshDatabase`.
- **DB:** MySQL `dbrentpro_test`.
- **Auth en tests:** `postJson` (endpoints públicos de registro, sin Sanctum).
- **Validación:** se elimina la regla `'role' => 'required|string'`. Los campos válidos del endpoint pasan a ser `name`, `password`, `email` (y los opcionales que ya manejaba). Un `role` enviado por el cliente queda fuera del input válido y se ignora.
- **Mass assignment:** `role` se quita de `$fillable`. La asignación en el controller se hace por propiedad directa tras `updateOrCreate`:
  ```php
  $user = User::updateOrCreate([...], $request->except('password', 'role') + [...]);
  $user->role = 'landlord';
  $user->save();
  ```
  (en ambas ramas OTP on/off). Se usa `$request->except('password', 'role')` para no depender de que `role` sea fillable.
- **Rama OTP off:** tras forzar `role`, se continúa con la asignación del plan gratuito, `Auth::login`, token, etc.
- **Rama OTP on:** tras forzar `role`, se envía el email y se crea el `PlanSubscribe` como hoy.
- **Call sites internos:** donde se creaba/actualizaba un usuario con `'role' => ...` dentro del array de `create`/`update`, se extrae esa clave y se asigna por propiedad directa (`$user->role = '...'; $user->save();`) para no depender de fillable.
- **Factory:** `UserFactory` asigna `role` por propiedad directa en `create` (override) o vía `afterCreating`, de modo que los estados `landlord`/`admin`/`tenant`/`superadmin` sigan funcionando con `role` no fillable.
- **Respuesta:** el endpoint sigue devolviendo 200 con `token` (rama OTP off) o `{otp_required: true}` (rama OTP on). No se devuelve 422 por enviar `role`.
- **Mocks obligatorios:** `Mail::fake()` en A15 (rama OTP on); `Plan::factory()->create()` en todos los casos HTTP.

## 6. Catálogo de casos

### 6.1 Auth API (`A`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| A5 | Sign-up con `role=superadmin` fuerza `landlord` | `otp_status=off`, `Plan::factory()` | `POST /api/v1/sign-up` con `role=superadmin` | 200 con `token`; `assertDatabaseHas('users', email, role='landlord')`; no existe usuario con `role='superadmin'` para ese email |
| A14 | Sign-up sin campo `role` crea landlord | `otp_status=off`, `Plan::factory()` | `POST /api/v1/sign-up` sin `role` | 200 con `token`; `users.role='landlord'` |
| A15 | Sign-up en rama OTP on ignora `role` arbitrario | `otp_status=on` (get_option), `Mail::fake()`, `Plan::factory()` | `POST /api/v1/sign-up` con `role=admin` | 200 con `otp_required=true`; `users.role='landlord'` |

> Tras el fix, A5 deja de ser `@group characterization`; Seg-4 queda remediado.

### 6.2 Unit (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | `role` no es mass-assignable en `User` | Modelo `User` instanciado | Inspección de `getFillable()` | `'role'` no pertenece a la lista |

### Tests de caracterización

Ninguno: Seg-4 queda remediado por este spec; A5 se convierte a test de comportamiento seguro.

## 7. Criterios de aceptación (Definition of Done)

- [x] `maanRegistration` no valida `role` y no lo pasa desde `$request` a `updateOrCreate`.
- [x] `role` se fuerza a `'landlord'` por propiedad directa en las ramas OTP on y OTP off.
- [x] `User::$fillable` no contiene `'role'`.
- [x] Call sites internos (`SignupController`, `Admin\UserController`, `AcnooLandlordController`, `AcnooTenantController` admin/api/landlord, `UserFactory`) asignan `role` por propiedad directa y siguen funcionando.
- [x] `UserFactory` crea usuarios con el `role` correcto en todos los states.
- [x] A5 ya no lleva `@group characterization`; A14 y A15 añadidos y en verde.
- [x] U1 en verde.
- [x] Registro con `role=superadmin`/`admin`/`tenant` resulta en `role='landlord'` (assertDatabaseHas).
- [x] Registro sin `role` resulta en `role='landlord'`.
- [x] Rama OTP on (A15) resulta en `role='landlord'`.
- [x] `php artisan test tests/Feature/Auth/ApiAuthSmokeTest.php` en verde.
- [x] `tests/README.md` actualizado: Seg-4 sin casos de caracterización (retirar la fila de A5).
- [x] `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Quitar `role` de `$fillable` rompe factories y controllers que lo asignaban por mass assignment | Alto | Actualizar todos los call sites a asignación directa (listados en §2 IN); verificar con la suite completa. |
| `UserFactory` deja de setear `role` si no se ajusta | Alto | Override de `create` o `afterCreating` que asigne la propiedad y guarde. |
| Web `SignupController` usa `updateOrCreate(..., $request->except('password') + ['role'=>'landlord'])`; con `role` no fillable se ignora | Alto | Asignar `$user->role = 'landlord'; $user->save();` tras el `updateOrCreate` en ambas ramas. |
| `Admin\UserController::update` hace `$user->update($request->except(...) + ...)` que incluía `role` | Medio | Asignar `$user->role = $request->role;` y guardar antes/después del `update` de los demás campos. |
| Clientes API que enviaban `role` esperando controlarlo | Bajo | Comportamiento nuevo documentado: `role` se ignora y la cuenta queda `landlord`. No se devuelve error. |
| `role` sigue siendo columna fillable en otros contextos (seeders usan `insert`, no fillable) | Bajo | Seeders usan `User::insert` (no fillable), no se ven afectados. |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

## 9. Estimación

- Controller API + modelo `$fillable`: 0.25 día.
- Call sites internos + factory: 0.5 día.
- Tests (A5 conversión + A14/A15 + U1) + README: 0.5 día.
- Verificación lint/analyse/CI: 0.25 día.
- Total: 1–1.5 días.

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 1 / 1.4
- `.analisis/seguridad.md` → Seg-4
- `.agents/spec/spec-conventions.md`
- `.agents/spec-impl/spec-tests-smoke-fase0.md` (A5 original)
- `.agents/skills/tests-caracterizacion.md`
- `tests/README.md`
- `AGENTS.md` (sección Controller layout / User $fillable)
