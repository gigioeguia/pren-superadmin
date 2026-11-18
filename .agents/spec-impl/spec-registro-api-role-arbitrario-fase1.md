---
id: spec-registro-api-role-arbitrario-fase1
titulo: "Registro API con role arbitrario — Fase 1 / 1.4"
estado: Implementado
fecha: 2026-11-18
origen: .analisis/plan/plan-accion.md (Fase 1 / 1.4) — Seg-4; .analisis/deuda-tecnica.md (DT-B3, parcial)
cobertura: 6 casos (A14–A18 + U1) sobre /api/v1/sign-up y mass-assignment de `role`
---

# Spec — Registro API con `role` arbitrario (Fase 1 / 1.4)

> Resumen: cerrar la escalación de privilegios en el registro de la API. El endpoint `/api/v1/sign-up` ignora cualquier `role` enviado por el cliente y persiste siempre `landlord`. Además, se retira `role` de `User::$fillable` y se actualizan los sitios de asignación masiva para establecerlo de forma explícita, cerrando la vía de mass-assignment (DT-B3, parcial).

**Origen:** `.analisis/plan/plan-accion.md` (Fase 1 / 1.4) — `Seg-4`.

---

## 1. Objetivo

Impedir que un cliente registre una cuenta con un rol arbitrario (p. ej. `superadmin`/`admin`) a través de `/api/v1/sign-up`, forzando `landlord` en todos los registros exitosos, con o sin el campo `role` en el payload. Como defensa en profundidad, retirar `role` de `$fillable` de `User` y actualizar los controladores que lo asignan por asignación masiva.

## 2. Alcance

### IN
- `Api/Auth/RegistrationController::maanRegistration`: quitar `'role' => 'required|string'` de la validación; excluir `role` de `$request->except(...)`; forzar `role = 'landlord'` en el `updateOrCreate` (ambas ramas OTP on/off).
- `User::$fillable`: retirar `'role'`.
- Actualizar los controladores que asignan `role` por asignación masiva para establecerlo de forma explícita (no vía mass-assignment):
  - `Admin/UserController` (store/update): mantener validación de `role` (función administrativa), pero asignar la columna con `$user->role = $request->role` + `save()`.
  - `Admin/AcnooLandlordController`, `Admin/AcnooTenantController`, `Api/AcnooTenantController`, `Landlord/AcnooTenantController`: quitar `'role'` del array de `User::create(...)` y asignar `$user->role = '...'` + `save()`.
  - `Auth/SignupController`: excluir `'role'` de `$request->except('password')` y forzar `$user->role = 'landlord'` (preserva el comportamiento web y cierra el bypass por precedencia de array union).
- Conversión de `ApiAuthSmokeTest::test_api_sign_up_accepts_arbitrary_role` (caracterización Seg-4) a test de bloqueo + A14–A18.
- Actualizar `tests/README.md`: Seg-4 sin casos de caracterización.

### OUT
- No se toca el endpoint web `/signup` como flujo (solo se endurece su mass-assignment, sin cambiar validación ni respuestas).
- No se asignan roles Spatie en el registro (se mantiene el sistema paralelo basado en la columna `role`).
- No se retiran `balance`, `is_setupped`, `plan_subscribe_id`, `will_expire` de `$fillable` (DT-B3, Fase 5.11).
- No se añade throttle (Seg-17, Fase 3.7) ni se cambia la política de contraseña (Seg-10, Fase 3.2).
- No se cambia el flujo OTP ni el TTL del código.

### Cobertura objetivo
- 5 casos (A14–A18) sobre `/api/v1/sign-up` + 1 caso unit (U1) sobre mass-assignment, más preservación de los 59 smoke tests existentes.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Test de caracterización `test_api_sign_up_accepts_arbitrary_role` | Existe en `ApiAuthSmokeTest`, `@group characterization` | Convertir a test de bloqueo (ver §6) |
| P2 | `Plan::factory()` para registro | Usado en el smoke test | Mantener |
| P3 | `CreatesUsers` trait | Provee helpers de usuario | Usar en tests |
| P4 | `RefreshDatabase` | Usado por `ApiAuthSmokeTest` | Mantener |
| P5 | `UserFactory` asigna `role` vía `forceFill` | Factory usa `forceFill` internamente | Sin cambios; no afectado por el retiro de `$fillable` |

## 4. Estructura propuesta

```
app/Models/User.php                                          # MODIFICADO: quitar 'role' de $fillable
app/Http/Controllers/Api/Auth/RegistrationController.php     # MODIFICADO: quitar 'role' de validación e input; forzar landlord
app/Http/Controllers/Auth/SignupController.php               # MODIFICADO: excluir 'role' del request; forzar landlord
app/Http/Controllers/Admin/UserController.php               # MODIFICADO: asignar role explícito
app/Http/Controllers/Admin/AcnooLandlordController.php      # MODIFICADO: asignar role explícito
app/Http/Controllers/Admin/AcnooTenantController.php         # MODIFICADO: asignar role explícito
app/Http/Controllers/Api/AcnooTenantController.php          # MODIFICADO: asignar role explícito
app/Http/Controllers/Landlord/AcnooTenantController.php     # MODIFICADO: asignar role explícito
tests/Feature/Auth/ApiAuthSmokeTest.php                      # MODIFICADO: A14–A18, sin caracterización
tests/README.md                                             # MODIFICADO: Seg-4 sin casos de caracterización
```

## 5. Decisiones técnicas

- **Framework:** Laravel 10, PHPUnit 10, `RefreshDatabase`.
- **DB:** MySQL `dbrentpro_test`.
- **Auth en tests:** `postJson` (endpoints públicos, sin Sanctum).
- **Forzado de rol:** tras `updateOrCreate`, establecer `$user->role = 'landlord'; $user->save();` (no depende de `$fillable`). En `User::create(...)` de otros controladores, quitar `'role'` del array y añadir `$user->role = '<rol>'; $user->save();`.
- **Mass-assignment:** `role` deja de ser asignable masivamente; solo asignación explícita o `forceFill` (factories/tests).
- **Validación API:** se elimina `'role' => 'required|string'`; campos no declarados se ignoran (no 422 por `role`).
- **Respuesta:** se mantiene `200` + `token` en registro exitoso; `role` del cliente se ignora.
- **OTP:** el forzado aplica en ambas ramas (OTP off y OTP on), porque `updateOrCreate` se ejecuta antes de la bifurcación.
- **Re-registro:** un usuario existente no setuppeado que se re-registra queda con `role = 'landlord'` (consistente con el flujo de registro de landlords).

## 6. Catálogo de casos

### 6.1 Auth API (`A`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| A14 | Registro API ignora `role=superadmin` | `Plan::factory()` creado | `POST /api/v1/sign-up` con `role=superadmin` | 200; `token` no vacío; `assertDatabaseHas users role=landlord` |
| A15 | Registro API sin `role` | `Plan::factory()` creado | `POST /api/v1/sign-up` sin `role` | 200; `token` no vacío; `role=landlord` |
| A16 | Registro API OTP on ignora `role=superadmin` | `Plan::factory()` creado; OTP on | `POST /api/v1/sign-up` con `role=superadmin` | 200 con `otp_required=true` (o equivalente); `role=landlord` persistido; sin login inmediato |
| A17 | Email ya setuppeado con `role=superadmin` | Usuario existente setuppeado | `POST /api/v1/sign-up` con `role=superadmin` | 406; `role` del usuario no cambia |
| A18 | Re-registro no setuppeado con `role=admin` | Usuario existente no setuppeado | `POST /api/v1/sign-up` con `role=admin` | 200; `role=landlord` (forzado) |

### 6.2 Unit (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | Mass-assignment no persiste `role` | — | `User::create(['role'=>'superadmin', 'name'=>..., 'email'=>..., 'password'=>...])` sin asignación explícita | `role` queda `null`/default; tras `$user->role='superadmin'; $user->save()` persiste |

### Tests de caracterización

Ninguno tras el fix: Seg-4 queda remediado; `test_api_sign_up_accepts_arbitrary_role` se convierte en A14 (sin `@group characterization`).

## 7. Criterios de aceptación (Definition of Done)

- [x] `Api/Auth/RegistrationController::maanRegistration` no valida ni acepta `role`; todo registro exitoso persiste `role='landlord'`.
- [x] `User::$fillable` no incluye `'role'`.
- [x] Los controladores listados en §2 asignan `role` de forma explícita (no por mass-assignment).
- [x] `Auth/SignupController` excluye `role` del request y fuerza `landlord`.
- [x] A14–A18 en verde; `test_api_sign_up_accepts_arbitrary_role` ya no es `@group characterization`.
- [x] U1 en verde (mass-assignment de `role` no persiste).
- [x] `php artisan test tests/Feature/Auth/ApiAuthSmokeTest.php` en verde.
- [x] `tests/README.md` actualizado: Seg-4 sin casos de caracterización.
- [x] `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Forzar `landlord` en re-registro de un usuario existente no setuppeado podría sobrescribir un rol previo | Bajo | Solo aplica a usuarios no setuppeados (registros incompletos); los setuppeados devuelven 406. |
| Retirar `role` de `$fillable` rompe asignación masiva en controladores no actualizados | Medio | Actualizar todos los sitios listados en §2; factories usan `forceFill` y no se ven afectadas. |
| Clientes que envíen `role` esperando error recibirán 200 con `landlord` | Bajo | Comportamiento definido por el plan ("forzar landlord"); documentado en el spec. |
| Comparaciones laxas de OTP (`==`) en registro persisten | Medio | Fuera de alcance (Seg-9, Fase 3.1). |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

## 9. Estimación

- Controlador API + `User::$fillable` + SignupController: 0.5 día.
- Actualización de los ~6 controladores de asignación masiva: 0.5 día.
- Tests (A14–A18, U1) y README: 0.5 día.
- Total: 1–1.5 días.

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 1 / 1.4
- `.analisis/seguridad.md` → Seg-4
- `.analisis/deuda-tecnica.md` → DT-B3
- `.agents/spec/spec-conventions.md`
- `.agents/spec-impl/spec-tests-smoke-fase0.md` (A8 original y caracterización Seg-4)
- `tests/README.md`
