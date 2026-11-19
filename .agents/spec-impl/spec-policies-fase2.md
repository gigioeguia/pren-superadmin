---
id: spec-policies-fase2
titulo: "Crear Policies para Property/Application/SecurityDeposit/Withdraw/RentPayment — Fase 2 / 2.1"
estado: Borrador
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 2 / 2.1) — Seg-5
cobertura: 24 casos (F1–F24) sobre IDOR en show/update/destroy/offline_payment/file_download de las 5 entidades
---

# Spec — Crear Policies (Fase 2 / 2.1)

> Resumen: remediar Seg-5 creando Policies para `Property`, `Application`, `SecurityDeposit`, `Withdraw` (incluye `LandlordWithdraw`) y `RentPayment`, registrándolas en `AuthServiceProvider`, e invocando `authorize()` en los métodos vulnerables de los controladores Admin/Landlord/API. Admin/superadmin conservan acceso total; landlords y tenants solo acceden a sus propios recursos.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 2 / 2.1) — `Seg-5`.

---

## 1. Objetivo

Cerrar los IDOR documentados en `seguridad.md` §5: ningún usuario autenticado puede leer, modificar, borrar, aprobar, descargar archivos ni marcar pagos de recursos ajenos. La autorización deja de ser inline/inconsistente y pasa a una capa única de Policies reutilizable por los 3 controladores (Admin, Landlord, API) de cada entidad.

## 2. Alcance

### IN
- Creación de `app/Policies/{Property,Application,SecurityDeposit,Withdraw,RentPayment}Policy.php`.
- Registro en `app/Providers/AuthServiceProvider::$policies`.
- `authorize()` en:
  - `Property`: `show`, `edit`/`update`, `destroy` (Admin, Landlord, API).
  - `Application`: `show`, `processing`, `approved`, `rejected`, `file_download`, `agreement_sign` (Landlord, API; Admin donde exista).
  - `SecurityDeposit`: `show`, `approve`, `rejected`, `file_download`, `offline_payment` (Admin, Landlord, API).
  - `Withdraw`: `show`, `approve`, `reject`, `invoice`, `summary` (Admin, Landlord, API).
  - `RentPayment`: `show`, `approve`, `rejected`, `file_download` (Admin, Landlord, API).
- `before()` en cada Policy que devuelva `true` para roles `admin`/`superadmin`.
- Conversión de F1–F24 (caracterización) a tests de bloqueo + casos de acceso legítimo.

### OUT
- No se toca `deleteAll` row-by-row ni `status()` de los controladores (Seg-5 bulk / DT-M10 → Fase 2 / 2.6).
- No se toca `/payment/success` público ni `session('user_id')` (Fase 2 / 2.3–2.4).
- No se toca `$gateway->namespace` dinámico (Fase 2 / 2.5).
- No se añaden columnas nuevas a las tablas; ownership se resuelve por columnas/relaciones existentes.
- No se unifican los controladores duplicados (DT-C2, fuera de alcance).
- No se crean Policies para otras entidades (PropertyDetail, Rent, Plan, etc.).

### Cobertura objetivo
- 24 casos (F1–F24): 12 de caracterización IDOR (acceso cruzado que hoy pasa) + 12 de acceso legítimo/autorizado (camino feliz).
- 5 Policies nuevas, 100 % de los métodos vulnerables listados con `authorize()`.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | `app/Policies/` inexistente | Ausente | Crear directorio y 5 Policies |
| P2 | `AuthServiceProvider::$policies` vacío | Solo comentario por defecto | Registrar las 5 Policies |
| P3 | `CreatesUsers` trait con `landlord()`/`tenant()`/`admin()`/`superadmin()` | Existe | Reutilizar en tests |
| P4 | `RefreshDatabase` + factories de Property/Application/SecurityDeposit/Withdraw/RentPayment | Parcial (Property, SecurityDeposit existen; Application/RentPayment/Withdraw verificar) | Crear factories mínimas si faltan |
| P5 | `Sanctum::actingAs` para tests API | Usado en suite A | Reutilizar |
| P6 | `Storage::fake()` para `file_download` | Disponible | Usar en casos de descarga |

## 4. Estructura propuesta

```
app/Policies/PropertyPolicy.php                    # NUEVO
app/Policies/ApplicationPolicy.php                 # NUEVO
app/Policies/SecurityDepositPolicy.php             # NUEVO
app/Policies/WithdrawPolicy.php                    # NUEVO (cubre Withdraw + LandlordWithdraw)
app/Policies/RentPaymentPolicy.php                 # NUEVO
app/Providers/AuthServiceProvider.php              # MODIFICADO: registrar $policies
app/Http/Controllers/Admin/PropertyController.php  # MODIFICADO: authorize() en show/edit/update/destroy
app/Http/Controllers/Landlord/PropertyController.php# MODIFICADO: authorize()
app/Http/Controllers/Api/AcnooPropertyController.php# MODIFICADO: authorize()
app/Http/Controllers/Landlord/AcnooApplicationController.php # MODIFICADO
app/Http/Controllers/Api/AcnooLandlordApplicationController.php # MODIFICADO
app/Http/Controllers/Api/AcnooTenantApplicationController.php # MODIFICADO
app/Http/Controllers/Admin/AcnooSecurityDepositController.php # MODIFICADO
app/Http/Controllers/Landlord/AcnooSecurityDepositController.php # MODIFICADO
app/Http/Controllers/Api/AcnooSecurityDepositController.php # MODIFICADO
app/Http/Controllers/Admin/AcnooWithdrawRequestController.php # MODIFICADO
app/Http/Controllers/Admin/AcnooLandlordWithdrawReportController.php # MODIFICADO
app/Http/Controllers/Landlord/AcnooWithdrawController.php # MODIFICADO
app/Http/Controllers/Api/AcnooWithdrawController.php # MODIFICADO
app/Http/Controllers/Admin/AcnooRentPaymentController.php # MODIFICADO
app/Http/Controllers/Landlord/AcnooRentPaymentController.php # MODIFICADO
app/Http/Controllers/Api/AcnooRentPaymentController.php # MODIFICADO
tests/Feature/Idor/IdorPolicySmokeTest.php          # NUEVO: F1–F24
tests/README.md                                    # MODIFICADO: tabla de caracterización (Seg-5)
```

## 5. Decisiones técnicas

- **Framework:** Laravel 10, PHPUnit 10, `RefreshDatabase`.
- **DB:** MySQL `dbrentpro_test`.
- **Auth en tests:** `actingAs($user)` (web) y `Sanctum::actingAs($user, ['*'])` (API).
- **Policies:**
  - `before(User $user): ?bool` → `true` si `$user->hasAnyRole(['admin','superadmin'])` (Spatie) **o** `$user->role` in `['admin','superadmin']` (columna paralela). En caso contrario `null` (continúa con el método).
  - Métodos: `view(User, Model)`, `update(User, Model)`, `delete(User, Model)`, y métodos de dominio `approve`, `reject`, `fileDownload`, `offlinePayment`, `process` según la entidad.
  - Ownership:
    - Property: `$user->id === $property->landlord_id`.
    - Application: tenant `$user->id === $application->tenant_id`; landlord `$user->id === $application->property->landlord_id`.
    - SecurityDeposit: `$user->id === $deposit->landlord_id || $user->id === $deposit->tenant_id`.
    - Withdraw/LandlordWithdraw: `$user->id === $withdraw->user_id`.
    - RentPayment: `$user->id === $rentPayment->rent->landlord_id || $user->id === $rentPayment->rent->tenant_id`.
- **Invocación:** `$this->authorize('view', $model)` (o el verbo correspondiente) al inicio del método, tras resolver el modelo. En `offline_payment`, autorizar sobre el `SecurityDeposit` resuelto.
- **Respuesta denegada:** 403 (Laravel `AuthorizationException` → handler devuelve 403). En API, JSON 403.
- **Model binding:** mantener `findOrFail($id)` actual y autorizar después (no cambiar firmas de ruta).
- **Mocks obligatorios:** `Storage::fake()` para descargas; `Sanctum::actingAs` para API; factories para crear recursos ajenos.
- **No se añade `authorizeResource()` automático** (control fino por método, evita romper endpoints no cubiertos).

## 6. Catálogo de casos

Prefijo `F` (feature transversal / IDOR). Cada caso se marca `@group characterization` hasta implementar el fix.

### 6.1 Property (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Landlord ve propiedad propia | Landlord A con propiedad P1 | `GET /landlord/properties/{P1}` (o `show`) | 200 — **camino feliz** |
| F2 | Landlord no ve propiedad ajena | Landlord A, propiedad de Landlord B | `GET /landlord/properties/{P_B}` | 403 — **caracterización Seg-5** |
| F3 | Landlord no actualiza propiedad ajena | Landlord A, propiedad de Landlord B | `PUT /landlord/properties/{P_B}` | 403 — **caracterización Seg-5** |
| F4 | Landlord no borra propiedad ajena | Landlord A, propiedad de Landlord B | `DELETE /landlord/properties/{P_B}` | 403 — **caracterización Seg-5** |
| F5 | API landlord no actualiza propiedad ajena | Landlord A (Sanctum), propiedad de B | `PUT /api/v1/properties/{P_B}` | 403 — **caracterización Seg-5** |
| F6 | Admin ve cualquier propiedad | Admin + propiedad de Landlord B | `GET /admin/properties/{P_B}` | 200 — **camino feliz** |

### 6.2 Application (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F7 | Tenant no ve aplicación ajena | Tenant A, aplicación de Tenant B | `GET /api/v1/tenant/applications/{app_B}` | 403 — **caracterización Seg-5** |
| F8 | Landlord no procesa aplicación ajena | Landlord A, aplicación sobre propiedad de B | `POST .../processing/{app_B}` | 403 — **caracterización Seg-5** |
| F9 | Landlord no descarga archivo ajeno | Landlord A, aplicación de B | `GET .../file-download/{app_B}` | 403 — **caracterización Seg-5** |
| F10 | Admin procesa cualquier aplicación | Admin + aplicación de B | `POST .../approved/{app_B}` | 200/redirect — **camino feliz** |

### 6.3 SecurityDeposit (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F11 | Landlord no ve depósito ajeno | Landlord A, depósito de Landlord B | `GET /landlord/security-deposits/{dep_B}` | 403 — **caracterización Seg-5** |
| F12 | Tenant no marca pago ajeno | Tenant A, depósito de Tenant B | `POST /api/v1/.../offline-payment/{gw}/{dep_B}` | 403 — **caracterización Seg-5** |
| F13 | Admin aprueba cualquier depósito | Admin + depósito de B | `POST /admin/security-deposits/approve/{dep_B}` | 200/redirect — **camino feliz** |
| F14 | Landlord ve depósito propio | Landlord A + depósito propio | `GET /landlord/security-deposits/{dep_A}` | 200 — **camino feliz** |

### 6.4 Withdraw (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F15 | Landlord no ve retiro ajeno | Landlord A, retiro de Landlord B | `GET /landlord/withdraw/{w_B}` | 403 — **caracterización Seg-5** |
| F16 | API no expone invoice ajeno | Landlord A (Sanctum), retiro de B | `GET /api/v1/withdraw/invoice/{w_B}` | 403 — **caracterización Seg-5** |
| F17 | API no expone summary ajeno | Landlord A, retiro de B | `GET /api/v1/withdraw/summary/{w_B}` | 403 — **caracterización Seg-5** |
| F18 | Admin ve cualquier retiro | Admin + retiro de B | `GET /admin/withdraw-request/{w_B}` | 200 — **camino feliz** |

### 6.5 RentPayment (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F19 | Landlord no ve pago de renta ajeno | Landlord A, RentPayment de propiedad de B | `GET /landlord/rent-payments/{rp_B}` | 403 — **caracterización Seg-5** |
| F20 | Tenant no ve pago de renta ajeno | Tenant A, RentPayment de B | `GET /api/v1/rent-payments/{rp_B}` | 403 — **caracterización Seg-5** |
| F21 | Landlord no descarga archivo ajeno | Landlord A, RentPayment de B | `GET /admin/rent-payments/file-download/{rp_B}` (o landlord) | 403 — **caracterización Seg-5** |
| F22 | Admin aprueba cualquier pago | Admin + RentPayment de B | `POST /admin/rent-payments/approve/{rp_B}` | 200/redirect — **camino feliz** |

### 6.6 Acceso cruzado tenant/landlord (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F23 | Tenant no actualiza propiedad | Tenant autenticado + propiedad de Landlord B | `PUT /api/v1/properties/{P_B}` | 403 — **caracterización Seg-5** |
| F24 | Superadmin bypass total | Superadmin + recurso de cualquier usuario | `GET/PUT/DELETE` sobre recurso ajeno | 200/204 — **camino feliz** |

### Tests de caracterización

| ID | Bug caracterizado | Fase que lo romperá |
|----|-------------------|---------------------|
| F2, F3, F4, F5, F7, F8, F9, F11, F12, F15, F16, F17, F19, F20, F21, F23 | Seg-5 — IDOR en show/update/destroy/offline_payment/file_download | Fase 2 / 2.1 |

## 7. Criterios de aceptación (Definition of Done)

- [ ] Existen `PropertyPolicy`, `ApplicationPolicy`, `SecurityDepositPolicy`, `WithdrawPolicy`, `RentPaymentPolicy` en `app/Policies/`.
- [ ] Las 5 Policies están registradas en `AuthServiceProvider::$policies`.
- [ ] Cada Policy tiene `before()` con bypass para `admin`/`superadmin` (Spatie + columna `role`).
- [ ] Todos los métodos listados en §2 invocan `$this->authorize(...)` con el verbo correspondiente.
- [ ] `offline_payment` autoriza sobre el `SecurityDeposit` resuelto.
- [ ] `file_download` autoriza sobre el recurso resuelto.
- [ ] F2–F5, F7–F9, F11–F12, F15–F17, F19–F21, F23 convertidos de `@group characterization` a tests de bloqueo tras el fix.
- [ ] F1, F6, F10, F13, F14, F18, F22, F24 en verde.
- [ ] `php artisan test tests/Feature/Idor/IdorPolicySmokeTest.php` en verde.
- [ ] `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.
- [ ] `tests/README.md` actualizado: Seg-5 sin casos de caracterización (o listando los remanentes).

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| `before()` bypass para admin puede ocultar IDOR en admin (pero admin es rol de confianza) | Bajo | Documentar; admin actions ya requieren middleware `permission` (Fase 3.8) |
| Modelos con ownership indirecto (RentPayment) requieren cargar relación `rent` | Medio | `load('rent')` antes de autorizar; evitar N+1 con `with('rent')` |
| `Application` puede no tener `property` cargado en API | Medio | Eager load en controlador o en Policy con `loadMissing` |
| `WithdrawPolicy` compartida entre `Withdraw` y `LandlordWithdraw` puede no cubrir campos distintos | Medio | Verificar que ambos expongan `user_id`; si no, ajustar Policy por modelo |
| Tests de caracterización masivos (16 casos) pueden ser lentos | Bajo | Agrupar por entidad, usar factories mínimas |
| `authorize()` en `deleteAll`/`status` queda fuera (2.6) → IDOR bulk persiste | Medio | Documentado como fuera de alcance; abordar en 2.6 |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

## 9. Estimación

- Policies + registro: 1 día.
- `authorize()` en controladores (5 entidades × 3 capas): 3–4 días.
- Tests F1–F24 + factories: 2–3 días.
- README + verificación CI: 0.5 día.
- Total: 6.5–8.5 días.

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 2 / 2.1
- `.analisis/seguridad.md` → Seg-5
- `.agents/spec/spec-conventions.md`
- `.agents/spec-impl/spec-tests-smoke-fase0.md`
- `.agents/spec-impl/spec-trait-acnoo-fase0.md`
- `tests/README.md`
- `app/Providers/AuthServiceProvider.php`
- `app/Http/Controllers/{Admin,Landlord,Api}/` (controladores listados en §4)
- `app/Models/{Property,Application,SecurityDeposit,Withdraw,LandlordWithdraw,RentPayment}.php`
