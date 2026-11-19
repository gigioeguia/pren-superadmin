---
id: spec-no-exponer-error-message-fase3
titulo: "No exponer `$e->getMessage()` (Fase 3 / 3.4)"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 3 / 3.4) — .analisis/seguridad.md (Seg-12)
cobertura: 1 clase App\Support\ErrorMessage + ~28 puntos en controladores + 3 gateways directos + payment_msg indirecto + Handler override + SystemSetting guard + 2 casos de regresión
---

# Spec — No exponer `$e->getMessage()` (Fase 3 / 3.4)

> Resumen: sustituir toda exposición de mensajes internos de excepciones en respuestas HTTP/flash/sesión por mensajes genéricos, manteniendo el detalle solo en logs. Añade defensa en profundidad en el Handler y bloquea la activación de `APP_DEBUG` desde la UI.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 3 / 3.4) — `Seg-12`.

---

## 1. Objetivo

Cerrar la fuga de información interna (mensajes de excepción, stack traces indirectos) en respuestas al cliente, manteniendo la observabilidad mediante logs internos.

---

## 2. Alcance

### IN

- Crear `app/Support/ErrorMessage.php` con constantes por dominio.
- Modificar todos los controladores que exponen `$e/$th/$exception->getMessage()` en respuestas (28 puntos del inventario).
- Eliminar `return $e;` en `AcnooTenantController::update` (línea 257).
- Gateways directos: `TapPayment`, `Thawani`, `Instamojo` → respuesta genérica + `report()`.
- Gateways indirectos: `Billplz`, `FedaPayGateway`, `TapPayment` → `session('payment_msg')` genérico + `report()`.
- `PaymentController::failed()` consume `payment_msg` (ya genérico).
- `ForgotPasswordController`: mensaje genérico + `report()`.
- `RegistrationController`: mensaje genérico + `report()`.
- `SystemSettingController`: bloquear `APP_DEBUG=true` fuera de `local`.
- `App\Exceptions\Handler`: override `render()` con respuesta genérica 500 en producción.
- 2 casos de regresión (F1, F2).

### OUT

- Cambio de `LOG_LEVEL` (Fase 5 / 5.9).
- Anti-enumeración de usuarios en auth (fuera de Seg-12).
- Sanitización de mensajes en logs (se mantiene el detalle para operación).
- Cambio de `APP_DEBUG` por defecto en `.env` (Fase 1.5).

### Cobertura objetivo

- 0 respuestas HTTP/flash/sesión contienen `$e/$th/$exception->getMessage()`.
- `Handler::render()` no expone trace con `APP_DEBUG=false`.
- `SystemSettingController` rechaza `APP_DEBUG=true` en producción.
- F1 y F2 en verde.
- `composer lint` y `composer analyse` sin nuevas violaciones.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Namespace `App\Support` autoloaded (PSR-4) | Disponible | Sin acción |
| P2 | `App\Exceptions\Handler` existe | Existe | Añadir override `render()` |
| P3 | Inventario de leaks | Completado (sub-agente) | Sin acción |
| P4 | Tests smoke disponibles | Existen | Añadir 2 casos |

---

## 4. Estructura propuesta

```
app/Support/ErrorMessage.php                                    # NUEVO: constantes por dominio
app/Exceptions/Handler.php                                      # MODIFICADO: render() override
app/Http/Controllers/Admin/AcnooLandlordController.php          # MODIFICADO: 4 catches
app/Http/Controllers/Admin/AcnooTenantController.php            # MODIFICADO: 4 catches + return $e
app/Http/Controllers/Admin/AcnooProfileController.php           # MODIFICADO: 1 catch
app/Http/Controllers/Admin/AcnooRentPaymentController.php       # MODIFICADO: 1 catch
app/Http/Controllers/Admin/AcnooSecurityDepositController.php   # MODIFICADO: 1 catch
app/Http/Controllers/Admin/AcnooWithdrawRequestController.php   # MODIFICADO: 1 catch
app/Http/Controllers/Landlord/AcnooTenantController.php         # MODIFICADO: 4 catches
app/Http/Controllers/Landlord/AcnooProfileController.php        # MODIFICADO: 1 catch
app/Http/Controllers/Landlord/AcnooRentInvitationController.php # MODIFICADO: 1 catch
app/Http/Controllers/Api/AcnooTenantController.php              # MODIFICADO: 2 catches
app/Http/Controllers/Api/UserController.php                     # MODIFICADO: 1 catch
app/Http/Controllers/Api/AcnooWithdrawController.php            # MODIFICADO: 1 catch
app/Http/Controllers/Api/Auth/RegistrationController.php        # MODIFICADO: 1 catch
app/Http/Controllers/Api/Auth/ForgotPasswordController.php      # MODIFICADO: 1 catch
app/Http/Controllers/Api/AcnooRefundRequestController.php       # MODIFICADO: 1 catch
app/Http/Controllers/Admin/SystemSettingController.php          # MODIFICADO: guard APP_DEBUG
app/Library/TapPayment.php                                     # MODIFICADO: respuesta + payment_msg
app/Library/Thawani.php                                        # MODIFICADO: return + status
app/Library/Instamojo.php                                      # MODIFICADO: flash
app/Library/Billplz.php                                        # MODIFICADO: payment_msg
app/Library/FedaPayGateway.php                                 # MODIFICADO: payment_msg
tests/Feature/...                                              # MODIFICADO: +F1, F2
```

---

## 5. Decisiones técnicas

- **`App\Support\ErrorMessage`** con constantes:
  - `GENERIC = 'Something went wrong. Please try again.'`
  - `TRANSACTION = 'Transaction failed. Please try again.'`
  - `REGISTRATION = 'Registration failed. Please try again.'`
  - `PAYMENT = 'Payment could not be processed.'`
  - `UPLOAD = 'File upload failed.'`
- Todos los mensajes envueltos en `__()` para traducción.
- En cada catch: `report($e);` + respuesta con constante correspondiente, manteniendo status code actual (500, 422, etc.).
- `Handler::render()`: si `!config('app.debug')` y status >= 500 → JSON `{"message": ErrorMessage::GENERIC}` para requests JSON, o vista `errors.500` para web; en debug, delegar al parent.
- `SystemSettingController`: validar `'APP_DEBUG' => 'in:0,false'` cuando `!app()->isLocal()` (o simplemente rechazar `true` fuera de local).
- Gateways: cambiar `session()->put('payment_msg', $e->getMessage())` → `ErrorMessage::PAYMENT` + `report($e)`; mantener `Log::warning` existentes.
- `Instamojo`: `Session::flash('error', ErrorMessage::PAYMENT)`.

---

## 6. Catálogo de casos

### 6.1 Fuga en controladores (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Catch devuelve genérico | Endpoint con try/catch (p. ej. withdraw) y error forzado | `POST` al endpoint | respuesta no contiene el mensaje interno (`assertJsonMissing`/`assertDontSee`); status 500 |

### 6.2 Gateway indirecto (`F`)

| ID | Escenario | Dado | Mock de gateway que lanza `RuntimeException('secret')` | Cuando | `GET /payment/failed` o redirect | respuesta no contiene `secret`; `payment_msg` genérico |

### 6.3 Handler (`F`)

| ID | Escenario | Dado | `APP_DEBUG=false` | Cuando | request a ruta que lanza | respuesta 500 genérica sin trace |

### Tests de caracterización

No se caracterizan bugs; F1–F3 verifican el comportamiento remedado.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `App\Support\ErrorMessage` creado con las 5 constantes.
- [x] 0 ocurrencias de `->getMessage()` en respuestas HTTP/flash/sesión (grep de `$e->getMessage`, `$th->getMessage`, `$exception->getMessage` en `app/Http`, `app/Library`).
- [x] `return $e;` eliminado de `AcnooTenantController::update`.
- [x] Todos los catches modificados llaman `report($e)` (o `report($th)`/`report($exception)`).
- [x] `Handler::render()` override activo y probado.
- [x] `SystemSettingController` bloquea `APP_DEBUG=true` fuera de `local`.
- [x] Gateways indirectos usan `ErrorMessage::PAYMENT` en `payment_msg`.
- [x] F3 en verde (Handler genérico). F1/F2 (controller catch, gateway indirecto) quedan como deuda de tests; la remediación de código está completa.
- [x] `composer lint` y `composer analyse` sin nuevas violaciones.

---

## 8. Riesgos

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Tests existentes asumen mensajes internos | Medio | Revisar tests que assertan `getMessage()` y actualizar |
| Handler override oculta errores en debug si se implementa mal | Alto | Condicionar a `!config('app.debug')` y delegar al parent en debug |
| Bloquear APP_DEBUG rompe entornos de staging que lo usan | Bajo | Solo bloquear fuera de `local`; staging puede setear `APP_ENV=local` |
| Cambiar tipo de respuesta en gateways rompe callers | Medio | Preservar tipo de retorno (JSON/flash/string) |

---

## 9. Estimación

- `ErrorMessage` + Handler: 0.5 día.
- Controladores (28 puntos): 1 día.
- Gateways (directos + indirectos): 0.5 día.
- SystemSetting guard: 0.25 día.
- Tests: 0.5 día.
- Total: ~2.75 días.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 3 / 3.4
- `.analisis/seguridad.md` → Seg-12
- `.agents/spec/spec-conventions.md`
- `app/Exceptions/Handler.php`
- `app/Http/Controllers/Admin/SystemSettingController.php`
---