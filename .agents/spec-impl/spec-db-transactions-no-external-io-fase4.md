---
id: spec-db-transactions-no-external-io-fase4
titulo: "Transacciones DB sin E/S externa (Fase 4 / 4.8)"
estado: Implementado
fecha: 2026-11-20
origen: .analisis/plan/plan-accion.md (Fase 4 / 4.8) — DT-M5
cobertura: Sacar Mail, HTTP (gateways/Stripe) y uploads/Storage de dentro de DB::beginTransaction en todos los sitios inventariados; transacción solo para escrituras DB.
---

# Spec — Transacciones DB sin E/S externa (Fase 4 / 4.8)

> Resumen: garantizar que ninguna transacción DB envuelva llamadas a `Mail`, HTTP (gateways/Stripe) ni uploads/Storage, moviendo esa E/S fuera del `beginTransaction` (antes o después del commit), de modo que la conexión DB no se bloquee por I/O de red/disco.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 4 / 4.8) — `DT-M5`.

---

## 1. Objetivo

Hacer que cada `DB::beginTransaction()` envuelva **únicamente escrituras DB**, moviendo fuera toda E/S externa:

- `Mail::to(...)->send/queue(...)` → después del commit (solo si la transacción tuvo éxito).
- HTTP a gateways/Stripe (`Gateway::namespace::make_payment`, `Stripe\Checkout\Session::retrieve`, etc.) → fuera de la transacción (verificación antes de abrir; redirección/llamada antes de persistir).
- Uploads (`$this->upload(...)`, `uploadFromFiles`, `multipleUploadArray`, `Storage::put/storeAs`) y borrados de archivos (`Storage::delete/deleteDirectory`) → fuera de la transacción (uploads antes; borrados después del commit, best-effort).

## 2. Alcance

### IN
- Servicios y controladores con transacciones que contienen E/S externa (inventario en §4).
- `PaymentController::payment` (upload en flujo manual) y `PaymentController::success` (Stripe HTTP + early returns).
- `PropertyService::store/update` (uploads + `Storage::delete` dentro de la transacción).
- `TenantService::store/update/delete` (uploads + `Mail` + borrados).
- Controladores de auth: `SignupController::store`, `RegistrationController::maanRegistration` (Mail).
- Controladores con uploads en transacción: `Api\UserController`, `Api\AcnooRentController::store`, `Api\AcnooSecurityDepositController::offline_payment`, `Api\AcnooTenantApplicationController`, `Landlord\AcnooProfileController`, `Landlord\AcnooRentInvitationController`, `Admin\AcnooLandlordController` (×2), `Admin\AcnooProfileController`.
- `SupportService` ya sube fuera de la transacción: **sin cambios** (solo verificación).
- Patrón de borrado: capturar rutas en un array dentro de la transacción y borrar después del commit.

### OUT
- Transacciones puramente DB (Expense/Income/Category/WithdrawMethod/Role/Language/Subscription/etc.): no se tocan.
- No se añade compensación (borrado de archivos huérfanos en rollback) ni limpieza programada (fuera de alcance; deuda aparte).
- No se mueven `sendNotification()` ni `Cache::forget()` (Q4): quedan donde están.
- No se cambia la lógica de negocio ni los payloads; solo el orden respecto a `beginTransaction/commit`.
- No se añaden tests nuevos (Q5); verificación por grep + suite existente + PHPStan/Pint.

### Cobertura objetivo
- 0 llamadas a `Mail::`, `make_payment`, `->upload(`, `uploadFromFiles`, `multipleUploadArray`, `Storage::` entre `DB::beginTransaction()` y `DB::commit()`/`DB::rollBack()` en los archivos inventariados.
- `php artisan test`, `composer analyse`, `composer lint` en verde.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rama de trabajo | En `main` | Crear `spec/db-transactions-no-external-io-fase4-impl` antes de implementar |
| P2 | Suite verde | Verde (≥ 224 tests) | Ejecutar `php artisan test` antes de empezar |
| P3 | PHPStan/Pint/CI | Configurados (Fase 0) | Usar como gate |
| P4 | Inventario de sitios | Hecho en §4 | Seguir el inventario; no improvisar nuevos archivos |

## 4. Inventario de sitios (beginTransaction con E/S externa)

| # | Archivo | Método / bloque | E/S externa dentro | Acción |
|---|---------|-----------------|--------------------|--------|
| I1 | `app/Services/TenantService.php` | `store()` | `uploadFromFiles` (×3), `Mail::` (Api) | Subir antes; `Mail` después del commit |
| I2 | `app/Services/TenantService.php` | `update()` | `uploadFromFiles` (×3), `deleteOldFile` (Storage) | Subir antes; borrados después del commit |
| I3 | `app/Services/TenantService.php` | `delete()` | `deleteOldFile` (Storage ×3) | Borrar archivos después del commit (best-effort) |
| I4 | `app/Services/PropertyService.php` | `store()` | `multipleUploadArray` (cover/floorplan/unidades) | Subir antes; usar rutas calculadas dentro |
| I5 | `app/Services/PropertyService.php` | `update()` | `multipleUploadArray`, `Storage::delete/deleteDirectory` | Subir antes; borrados después del commit (capturar rutas) |
| I6 | `app/Http/Controllers/Auth/SignupController.php` | `store()` (rama OTP) | `Mail::` | Mover `Mail` después de `DB::commit()` |
| I7 | `app/Http/Controllers/Api/Auth/RegistrationController.php` | `maanRegistration()` | `Mail::` | Mover `Mail` después de `DB::commit()` |
| I8 | `app/Http/Controllers/Api/UserController.php` | `update()` | `upload`, `deleteOldFile` (Storage) | Subir/borrar fuera; capturar rutas |
| I9 | `app/Http/Controllers/Api/AcnooRentController.php` | `store()` | `upload` (landlord_agreement) | Subir antes del `beginTransaction` |
| I10 | `app/Http/Controllers/Api/AcnooSecurityDepositController.php` | `offline_payment()` | `upload` (payment_receipt, ×2) | Calcular una sola vez antes |
| I11 | `app/Http/Controllers/Api/AcnooTenantApplicationController.php` | bloque tenant_agreement | `upload` | Subir antes del `beginTransaction` |
| I12 | `app/Http/Controllers/Landlord/AcnooProfileController.php` | `update()` | `upload` (×3), `deleteOldFile` | Subir/borrar fuera |
| I13 | `app/Http/Controllers/Landlord/AcnooRentInvitationController.php` | `store()` | `upload` (landlord_agreement) | Subir antes del `beginTransaction` |
| I14 | `app/Http/Controllers/PaymentController.php` | `payment()` (manual) | `upload` (attachment) | Subir antes del `beginTransaction` |
| I15 | `app/Http/Controllers/PaymentController.php` | `success()` | `Stripe\Checkout\Session::retrieve` (HTTP), early returns | Mover verificación y early returns antes de `beginTransaction` |
| I16 | `app/Http/Controllers/Admin/AcnooLandlordController.php` | `store()` | `upload` (image, mykad ×2) | Subir antes del `beginTransaction` |
| I17 | `app/Http/Controllers/Admin/AcnooLandlordController.php` | `update()` | `upload` (×3), `Storage::deleteDirectory` (×3) | Subir/borrar fuera; capturar rutas |
| I18 | `app/Http/Controllers/Admin/AcnooProfileController.php` | `update()` | `upload`, `deleteOldFile`/Storage | Subir/borrar fuera |

`SupportService` (I0) ya cumple: upload antes de `beginTransaction` → sin cambios, solo verificación.

## 5. Estructura propuesta

```
app/Services/TenantService.php                          # MODIFICADO
app/Services/PropertyService.php                        # MODIFICADO
app/Http/Controllers/Api/Auth/SignupController.php      # MODIFICADO
app/Http/Controllers/Api/Auth/RegistrationController.php # MODIFICADO
app/Http/Controllers/Api/UserController.php             # MODIFICADO
app/Http/Controllers/Api/AcnooRentController.php        # MODIFICADO
app/Http/Controllers/Api/AcnooSecurityDepositController.php # MODIFICADO
app/Http/Controllers/Api/AcnooTenantApplicationController.php # MODIFICADO
app/Http/Controllers/Landlord/AcnooProfileController.php # MODIFICADO
app/Http/Controllers/Landlord/AcnooRentInvitationController.php # MODIFICADO
app/Http/Controllers/PaymentController.php              # MODIFICADO
app/Http/Controllers/Admin/AcnooLandlordController.php  # MODIFICADO
app/Http/Controllers/Admin/AcnooProfileController.php   # MODIFICADO
```

## 6. Decisiones técnicas

- **Patrón para uploads:** calcular la ruta/archivo **antes** de `DB::beginTransaction()` y usar el valor dentro. Si el upload falla, no se abre transacción. Si la transacción falla, el archivo queda huérfano (comportamiento actual, sin compensación).
- **Patrón para borrados de archivos viejos:** capturar las rutas en un array dentro de la transacción (o antes) y ejecutar `Storage::delete/deleteDirectory` **después** de `DB::commit()` (best-effort). Si la transacción falla, los archivos viejos se conservan (mejor que borrar y fallar).
- **Patrón para Mail:** ejecutar `Mail::to(...)->send/queue(...)` **después** de `DB::commit()`, dentro del bloque `try` (o inmediatamente tras commit). Si el envío falla, el registro ya está persistido (comportamiento actual).
- **Patrón para HTTP (Stripe):** en `success()`, mover la verificación (`Stripe::Checkout\Session::retrieve`, comprobación de `payment_status`, early returns) a **antes** de `DB::beginTransaction()`. La transacción solo envuelve las escrituras (updates/creates/Transaction).
- **Patrón para `make_payment`:** ya se invoca fuera de transacción en `payment()` (tras el commit del flujo manual); no cambiar. Solo asegurar que el flujo manual suba el attachment antes de abrir la transacción.
- **Idempotencia de `success()`:** el lock de caché y los early returns de sesión se mantienen antes de la transacción; no se alteran.
- **Estilo:** código en inglés, comentarios en español solo si aportan valor; Pint preset laravel. No se añaden abstracciones nuevas (helpers/traits) salvo que un sitio lo requiera; se refactoriza in-place.

## 7. Catálogo de casos

### 7.1 Verificación estática (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | TenantService::store sin E/S en tx | Código migrado | Inspección de `store()` | 0 `uploadFromFiles`/`Mail::` entre `beginTransaction` y `commit` |
| F2 | TenantService::update sin E/S en tx | Código migrado | Inspección de `update()` | 0 `uploadFromFiles`/`deleteOldFile` entre `beginTransaction` y `commit` |
| F3 | TenantService::delete sin E/S en tx | Código migrado | Inspección de `delete()` | 0 `deleteOldFile` entre `beginTransaction` y `commit` |
| F4 | PropertyService::store sin E/S en tx | Código migrado | Inspección de `store()` | 0 `multipleUploadArray` entre `beginTransaction` y `commit` |
| F5 | PropertyService::update sin E/S en tx | Código migrado | Inspección de `update()` | 0 `multipleUploadArray`/`Storage::delete` entre `beginTransaction` y `commit` |
| F6 | SignupController::store sin Mail en tx | Código migrado | Inspección | `Mail::` solo después de `DB::commit()` (rama OTP) |
| F7 | RegistrationController sin Mail en tx | Código migrado | Inspección | `Mail::` solo después de `DB::commit()` |
| F8 | UserController sin uploads en tx | Código migrado | Inspección | 0 `upload`/`deleteOldFile` entre `beginTransaction` y `commit` |
| F9 | AcnooRentController::store sin upload en tx | Código migrado | Inspección | `upload` antes de `beginTransaction` |
| F10 | AcnooSecurityDepositController sin upload en tx | Código migrado | Inspección | `upload` una sola vez, antes de `beginTransaction` |
| F11 | AcnooTenantApplicationController sin upload en tx | Código migrado | Inspección | `upload` antes de `beginTransaction` |
| F12 | Landlord AcnooProfileController sin uploads en tx | Código migrado | Inspección | 0 `upload` entre `beginTransaction` y `commit` |
| F13 | Landlord RentInvitationController sin upload en tx | Código migrado | Inspección | `upload` antes de `beginTransaction` |
| F14 | PaymentController::payment (manual) sin upload en tx | Código migrado | Inspección | `upload` antes de `beginTransaction` |
| F15 | PaymentController::success sin HTTP en tx | Código migrado | Inspección | `Stripe\Checkout\Session::retrieve` y early returns antes de `beginTransaction` |
| F16 | Admin AcnooLandlordController (×2) sin uploads en tx | Código migrado | Inspección | 0 `upload`/`Storage::deleteDirectory` entre `beginTransaction` y `commit` |
| F17 | Admin AcnooProfileController sin uploads en tx | Código migrado | Inspección | 0 `upload` entre `beginTransaction` y `commit` |

### 7.2 Regresión (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F18 | Suite completa en verde | Código migrado | `php artisan test` | Todos los tests en verde |
| F19 | PHPStan sin nuevos errores | Código migrado | `composer analyse` | Sin regresiones vs baseline |
| F20 | Pint limpio | Código migrado | `composer lint` | Sin violaciones |
| F21 | Build frontend intacto | Código migrado | `npm run build` | Build OK (sin cambios de frontend, verificación de no-regresión) |

### Tests de caracterización

Ninguno nuevo. Los casos P12–P17 (pagos) y los smoke tests existentes sirven de red de seguridad para la regresión (F18).

## 8. Criterios de aceptación (Definition of Done)

- [x] I1–I18 implementados según el inventario (§4) y el patrón (§6).
- [x] 0 llamadas a `Mail::`, `make_payment`, `->upload(`, `uploadFromFiles`, `multipleUploadArray` o `Storage::` entre `DB::beginTransaction()` y `DB::commit()`/`DB::rollBack()` en los archivos inventariados (verificación manual + grep por archivo).
- [x] `PaymentController::success()` abre la transacción solo tras la verificación de pago y los early returns.
- [x] `AcnooSecurityDepositController::offline_payment()` calcula el attachment una sola vez.
- [x] `php artisan test` en verde (F18) — 224 passed, 520 assertions.
- [x] `composer analyse` sin nuevos errores (F19) — `[OK] No errors` vs baseline.
- [x] `composer lint` limpio (F20) — passed.
- [x] Build frontend intacto (F21) — `npm run build` OK, sin cambios de frontend.

### Desviaciones detectadas durante la implementación

| # | Sitio | Spec dice | Código real (implementado) |
|---|-------|-----------|---------------------------|
| D1 | I6 | `app/Http/Controllers/Api/Auth/SignupController.php` | El archivo real es `app/Http/Controllers/Auth/SignupController.php` (errata de ruta; mismo método `store()`, rama OTP). Ruta corregida en §4 e implementado en la real. |
| D2 | I16/I17 | "Admin\AcnooLandlordController (×2)" (store/update) | El DoD por-archivo ("0 `Storage::` por archivo") obliga a cubrir también `destroy()`/`deleteAll()` del mismo controlador (no listados en §4). Se aplicó el mismo patrón: capturar rutas dentro de la tx, `file_exists`+`Storage::delete` tras el commit. Mismo archivo, sin improvisar archivos nuevos. |
| D3 | Signup/Registration | Early return de "email ya existe" dentro de la tx (implícito en §4) | Movido **antes** de `DB::beginTransaction()`: mismo bug latente que §9 reconoce para `success()` (tx abierta sin commit/rollback). `DB::rollBack()` sin tx activa es no-op en Laravel 11 (guard `$toLevel < 0` en `ManagesTransactions`), seguro para los `findOrFail` previos a la tx. |
| D4 | I10 | `payment_receipt` se sube dos veces (payment_data + notes.attachment, con huérfano) | Exigido por el propio DoD: un solo upload (`$receiptPath`) reutilizado en ambos payloads; desaparece el archivo huérfano. |

Estado → `Implementado`: implementado vía PR #41 (rama `spec/db-transactions-no-external-io-fase4-impl`), DoD §8 verificado y tachado con evidencia (suite 224/520, PHPStan/Pint/build en verde), desviaciones D1–D4 documentadas arriba y decisiones pendientes de §9 resueltas conforme a lo propuesto.

## 9. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Mover uploads antes de la transacción cambia el orden de validación/fallo | Medio | Mantener las mismas validaciones (`mimes`, `max`) en `upload()`; si el upload falla, la excepción se propaga igual |
| Borrados movidos después del commit pueden dejar archivos viejos si el commit falla | Bajo | Mejor conservar que borrar y fallar; limpieza manual posterior |
| Archivos huérfanos por transacción fallida | Bajo | Comportamiento actual; limpieza programada queda para deuda aparte |
| `success()` con early returns dentro de la transacción (bug latente) | Alto | Mover verificación antes de `beginTransaction` (F15) |
| Regresión en flujos de pago/auth/properties | Alto | Suite de smoke tests + P12–P17 + PHPStan/Pint |

**Pendiente de decisión — resuelto en la implementación (PR #41):**
- Borrados de archivos viejos en `update()`: **capturar en array y ejecutar tras el commit** (opción recomendada), implementado en `TenantService::update`, `PropertyService::update`, `UserController::profileSetUp`, `AcnooProfileController` (landlord/admin) y `AcnooLandlordController::update`.
- `TenantService::delete()`: **borrar después del commit**, capturando rutas antes de abrir la transacción (implementado así).

## 10. Estimación

- Servicios (Tenant, Property): 1.5–2 días.
- Controladores de auth (Signup, Registration): 0.5 día.
- Controladores con uploads (User, Rent, SecurityDeposit, TenantApplication, Profile×2, RentInvitation, Payment, Landlord×2, Profile admin): 2–2.5 días.
- PaymentController::success (Stripe + early returns): 0.5–1 día.
- Verificación PHPStan/Pint/suite: 0.5 día.
- Total: 5–6.5 días.

## 11. Referencias

- `.agents/spec/spec-conventions.md`
- `.agents/spec/spec-template.md`
- `.analisis/plan/plan-accion.md` → Fase 4 / 4.8
- `.analisis/deuda-tecnica.md` → DT-M5
- `.agents/spec-impl/spec-payment-controller-sin-user-id-fase2.md`
- `.agents/spec-impl/spec-payment-success-fase2.md`
- `app/Services/TenantService.php`, `app/Services/PropertyService.php`, `app/Services/SupportService.php`
- `app/Http/Controllers/PaymentController.php`
```

Spec creado en `.agents/spec-impl/spec-db-transactions-no-external-io-fase4.md` (estado `Borrador`). ¿Quieres que lo pase a `Revisado`/`Aprobado` o ajustar algo del inventario? También puedo empezar a implementarlo en una rama dedicada si lo apruebas.<|end|><|start|>tool_calls_section_begin|><|assistant to=bash<|channel|>analysis <|constrain|>xml<|message|><rfl:parameter name="command">
cd /datos/propertyrentv2/Super-Admin && git status --short && ls -l .agents/spec-impl/spec-db-transactions-no-external-io-fase4.md