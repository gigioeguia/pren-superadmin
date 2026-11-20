---
id: spec-inyectar-request-fase4
titulo: "Inyectar `Request $request` en lugar de `request()` global (Fase 4 / 4.4)"
estado: Implementado
fecha: 2026-11-20
origen: .analisis/plan/plan-accion.md (Fase 4 / 4.4) — DT-A3; absorbe Fase 5 / 5.3 (Seg-20)
cobertura: Eliminar todos los usos del helper global `request()` en `app/Http/Controllers/`, `app/Http/Middleware/` y `app/Exports/`, inyectando `Illuminate\Http\Request $request` en las firmas que falten y usándolo dentro de los closures de filtrado (acnooQuery/acnooFilter y queries Api). Meta: 0 ocurrencias de `request(` en esos directorios (verificable por grep).
---

# Spec — Inyectar `Request $request` en lugar de `request()` global (Fase 4 / 4.4)

> Resumen: sustituir el helper global `request()` por el parámetro inyectado `Request $request` en controladores, middleware y exports, de modo que la entrada HTTP esté tipada, testeable y no dependa del contenedor global. Incluye el fix latente de `AcnooWithdrawController::index` (Fase 5 / 5.3) y deja fuera los gateways de `app/Library/` (que usan `request()->expectsJson()`) por ser un cambio transversal de firma de `make_payment`.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 4 / 4.4) — `DT-A3`. Absorbe `Fase 5 / 5.3` (Seg-20).

---

## 1. Objetivo

Hacer que toda lectura de input HTTP en `app/Http/Controllers/`, `app/Http/Middleware/` y `app/Exports/` pase por el `Request $request` inyectado, eliminando el acoplamiento al helper global `request()`. Esto cierra el bug documentado de controladores que olvidan inyectar `Request` (ej. `AcnooWithdrawController::index`), mejora la testabilidad y la detección estática, y cumple la deuda `DT-A3`.

---

## 2. Alcance

### IN
- Todos los usos de `request(` (arg-style `request('key')` y arrow-style `request()->…`) en:
  - `app/Http/Controllers/` (32 archivos: 19 Admin + 5 Api + 7 Landlord + `PaymentController`).
  - `app/Http/Middleware/SetLocale.php`.
  - `app/Exports/AdminPropertiesExport.php` y `app/Exports/LandlordPropertiesExport.php`.
- Inyección de `Request $request` en firmas que hoy no lo tienen:
  - `Api\AcnooWithdrawController::index()`
  - `PaymentController::DepositRentPayment($id)` y `PaymentController::index(string $id, string $landlord_id)`
  - `Admin\AcnooSupportController` (donde use `request()` en `acnooFilter`).
- Captura de `$request` en los closures de `acnooQuery`/queries (`use ($request)`) donde hoy se usa `request('…')`.
- Exports: pasar `Request $request` al constructor (o leerlo del controlador que los instancia) y usarlo internamente.
- `SetLocale::handle(Request $request)`: reemplazar `request()->is(...)` por `$request->is(...)`.
- Absorción de `Fase 5 / 5.3` (Seg-20) en `plan-accion.md`.

### OUT
- `app/Library/*` (gateways): `request()->expectsJson()` (23 ocurrencias en 6 gateways). Requiere cambiar la firma de `make_payment($array)` en 11 gateways y tocar `PaymentController::payment()`. Se documenta como deuda diferida (candidato a Fase 4.6 "Helpers globales → clases" o spec aparte). Motivo: cambio transversal de pagos, fuera del "trait de filtrado".
- `app/Helpers/Helper.php` y otros helpers globales (Fase 4.6).
- `Plan::durationInDays()` y duplicación OTP (Fase 4.7).
- Cambios de comportamiento observable: el refactor es 1:1; no se alteran respuestas, filtros ni rutas.

### Cobertura objetivo
- 0 ocurrencias de `request(` en `app/Http/Controllers/`, `app/Http/Middleware/` y `app/Exports/` (verificable por grep).
- `composer analyse` (PHPStan nivel 5) sin nuevos errores vs baseline (910).
- `composer lint` (Pint) limpio.
- `php artisan test` completo en verde (los smoke tests existentes cubren los flujos afectados).

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Trait `HasAcnooActions` con métodos inyectando `Request $request` | Existe (spec-trait-acnoo-fase0) | Ninguna; los hooks `acnooQuery(Request $request)` y `acnooExport(Request $request)` ya reciben `$request` |
| P2 | Controladores adoptantes (52) con `use HasAcnooActions` | Existe | Refactor interno de `acnooQuery` |
| P3 | Controladores no adoptantes que usan `request()` (Api + PaymentController) | Existen | Inyectar `Request $request` en firmas y usarlo |
| P4 | Exports instanciados desde controladores | Existen | Añadir constructor con `Request $request` |
| P5 | Middleware `SetLocale` en grupo `web` | Existe | Usar `$request` del `handle` |
| P6 | Suite de tests smoke + PHPStan/Pint/CI | Existe (Fase 0) | Ejecutar como verificación |

---

## 4. Estructura propuesta

```
app/Http/Controllers/ (32 archivos)           # MODIFICADOS: request() → $request
  Admin/AcnooAmenityController.php
  Admin/AcnooBlogController.php
  Admin/AcnooClientController.php
  Admin/AcnooCommentController.php
  Admin/AcnooCurrencyController.php
  Admin/AcnooFacilityController.php
  Admin/AcnooFaqController.php
  Admin/AcnooFeatureController.php
  Admin/AcnooHighlightController.php
  Admin/AcnooHouseTypeController.php
  Admin/AcnooLandlordController.php
  Admin/AcnooMessageController.php
  Admin/AcnooRefundReasonController.php
  Admin/AcnooSecurityDepositReportController.php
  Admin/AcnooSupportController.php
  Admin/AcnooTestimonialController.php
  Admin/AcnooTransactionController.php
  Admin/AcnooWithdrawRequestController.php
  Admin/UserController.php
  Api/AcnooLandlordDashboardController.php
  Api/AcnooRentController.php
  Api/AcnooTenantApplicationController.php
  Api/AcnooTenantMaintenanceController.php
  Api/AcnooWithdrawController.php              # firma index() añade Request $request
  Landlord/AcnooAmenityController.php
  Landlord/AcnooApplicationController.php
  Landlord/AcnooFacilityController.php
  Landlord/AcnooLaborController.php
  Landlord/AcnooMaintenanceController.php
  Landlord/AcnooWithdrawController.php
  Landlord/AcnooWithdrawMethodController.php
  PaymentController.php                        # DepositRentPayment/index añaden Request $request

app/Http/Middleware/SetLocale.php              # MODIFICADO

app/Exports/AdminPropertiesExport.php          # MODIFICADO: constructor Request $request
app/Exports/LandlordPropertiesExport.php       # MODIFICADO: constructor Request $request
```

Sin cambios en `app/Traits/HasAcnooActions.php` (ya inyecta `Request $request`).

---

## 5. Decisiones técnicas

- **Framework:** Laravel 11 / PHP 8.3. Uso de `Illuminate\Http\Request` ya importado en la mayoría de controladores; añadir `use` donde falte.
- **Inyección obligatoria:** `Request $request` no nullable en todas las firmas tocadas. Si un controlador no inyecta, PHPStan/IDE detectan el uso de `$request` indefinido; la compilación/análisis falla, actuando como anti-regresión.
- **Closures:** en `acnooQuery` y queries Api, capturar `$request` con `use ($request)` en cada closure que referencie input. Evitar arrow functions cuando se necesite `$this` o múltiples statements; usar `use` explícito.
- **Exports:** añadir constructor `public function __construct(private Request $request) {}` y usar `$this->request`. Los controladores que los instancian (`Admin\PropertyController::exportExcel/exportCsv`, `Landlord\PropertyController::*`) pasarán `request()` → ahora `$request` (ya inyectado en esos métodos del trait? El trait `exportExcel(Request $request)` ya recibe `$request`; el controlador delega a Excel con `new …Export`. Ajustar para pasar `$request`).
  - Nota: el trait `acnooExport(Request $request)` ya recibe `$request`; los exports se instancian dentro de `acnooExport()` de cada controlador. Ahí se pasará `$request`.
- **SetLocale:** usar el `$request` del método `handle(Request $request, Closure $next)`.
- **Sin nuevos tests de comportamiento:** refactor 1:1. Verificación por grep + análisis estático + suite.
- **Rama:** `spec/inject-request-fase4-impl` (ver `workflow-ramas.md`). Spec en estado `Borrador` → `Revisado` → `Aprobado` antes de implementar.

---

## 6. Catálogo de casos

### 6.1 Métrica estática (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Sin `request(` en `app/Http/Controllers/` | Refactor completo | `grep -rn "request(" app/Http/Controllers/` | 0 resultados |
| F2 | Sin `request(` en `app/Http/Middleware/` | Refactor completo | `grep -rn "request(" app/Http/Middleware/` | 0 resultados |
| F3 | Sin `request(` en `app/Exports/` | Refactor completo | `grep -rn "request(" app/Exports/` | 0 resultados |
| F4 | `AcnooWithdrawController::index` inyecta `Request $request` | Firma modificada | Inspección | `public function index(Request $request)` |
| F5 | `PaymentController::DepositRentPayment` inyecta `Request $request` | Firma modificada | Inspección | `public function DepositRentPayment(Request $request, $id)` (o equivalente con `$request` primero/según convención) |
| F6 | `PaymentController::index` inyecta `Request $request` | Firma modificada | Inspección | `public function index(Request $request, string $id, string $landlord_id)` |
| F7 | Exports con constructor `Request $request` | Clase modificada | Inspección | `public function __construct(private Request $request) {}` y uso de `$this->request` |
| F8 | `SetLocale` usa `$request` del handle | Middleware modificado | Inspección | `$request->is('/')` / `$request->is('login')`, sin `request()` |
| F9 | Closures de `acnooQuery` capturan `$request` | Controladores | Inspección + `composer analyse` | Sin "Undefined variable $request" ni errores de análisis |
| F10 | PHPStan sin nuevos errores | Análisis | `composer analyse` | Sin regresiones vs baseline (910) |
| F11 | Pint limpio | Estilo | `composer lint` | Sin violaciones |
| F12 | Suite completa en verde | Tests | `php artisan test` | Todas las suites pasan |
| F13 | `request(` permanece en `app/Library/` (OUT) | Sin cambios en gateways | `grep -rn "request(" app/Library/` | ≥ 1 (documentado como diferido) |

### Tests de caracterización

Ninguno nuevo: el refactor es 1:1 y no cambia contratos observables. Los smoke tests existentes (Fase 0) cubren los flujos afectados (auth, pagos, filtros admin/landlord). No se caracteriza ningún bug nuevo; el único bug absorbido (5.3/Seg-20) se corrige directamente.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] F1–F3: 0 ocurrencias de `request(` en `app/Http/Controllers/`, `app/Http/Middleware/` y `app/Exports/`.
- [x] F4–F6: firmas inyectadas en `AcnooWithdrawController::index`, `PaymentController::DepositRentPayment` y `PaymentController::index`.
- [x] F7: exports usan `Request $request` inyectado.
- [x] F8: `SetLocale` usa `$request` del handle.
- [x] F9: closures capturan `$request` sin errores.
- [x] F10: `composer analyse` sin nuevos errores.
- [x] F11: `composer lint` limpio.
- [x] F12: `php artisan test` completo en verde.
- [x] F13: `app/Library/` documentado como OUT (sin cambios).
- [x] Absorción de Fase 5 / 5.3 (Seg-20) registrada en `plan-accion.md`.
- [x] Rama dedicada y PR (sin push directo a `main`).

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Olvidar `use ($request)` en un closure → variable indefinida en runtime | Alto | PHPStan nivel 5 + revisión por controlador + suite de filtros |
| Cambio de firma de `PaymentController::index/DepositRentPayment` rompe rutas/call-sites | Medio | Buscar call-sites (`route('payment.index', …)`, `DepositRentPayment`) y actualizar; rutas pasan params por URL, no por inyección (Laravel inyecta Request automáticamente en cualquier posición) |
| Exports instanciados en trait sin pasar `$request` | Medio | Auditar `acnooExport()` de cada controlador que usa exports (Admin/Landlord PropertyController) |
| Regresión silenciosa en filtros de búsqueda (`search`, `status`, `from_date`/`to_date`) | Alto | Smoke tests + verificación manual de un filtro por controlador representativo (Admin Amenity, Api Rent, Landlord Withdraw) |
| Gateways siguen usando `request()->expectsJson()` (OUT) | Bajo | Documentado; no bloquea este spec |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

---

## 9. Estimación

- Controladores Admin (20 archivos, acnooQuery): 1.0 día.
- Controladores Landlord (7) + Api (5): 0.75 día.
- `PaymentController` (firmas + 7 usos): 0.25 día.
- `SetLocale` + Exports + controladores que los instancian: 0.25 día.
- Verificación (phpstan, pint, suite, grep): 0.25 día.
- Total: ~2.5 días.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 4 / 4.4 (y absorción de Fase 5 / 5.3)
- `.analisis/deuda-tecnica.md` → DT-A3
- `.analisis/seguridad.md` → Seg-20 (absorbido)
- `.agents/spec/spec-conventions.md`
- `.agents/spec-impl/spec-trait-acnoo-fase0.md` (trait que ya inyecta `Request $request`)
- `.agents/spec-impl/spec-env-to-config-fase4.md` (precedente de spec estructural con métrica grep)
- `AGENTS.md` (sección "Controller layout", "Gotchas")

---

### Notas de implementación

1. **Patrón de refactor en `acnooQuery`**:
   ```php
   // Antes
   ->when(request('search'), function ($q) {
       $search = request('search');
       ...
   })
   // Después
   ->when($request->search, function ($q) use ($request) {
       $search = $request->search; // o $request->input('search')
       ...
   })
   ```
   Preferir `$request->input('key')` cuando el valor pueda ser `0`/`false` (evitar truthiness accidental); mantener `request('key')` semantics (`input` con default null) para no cambiar comportamiento. Para filtros `when(request('status'))` mantener `when($request->status)` (mismo truthiness que `request('status')` que usa `input`).
2. **`AcnooWithdrawController::index`**:
   ```php
   public function index(Request $request)
   {
       ...
       ->when($request->from_date && $request->to_date, function ($q) use ($request) { ... })
   }
   ```
3. **Exports**: en `acnooExport()` de `Admin\PropertyController`/`Landlord\PropertyController`, pasar `$request` al constructor:
   ```php
   return new AdminPropertiesExport($request);
   ```
   y en la clase:
   ```php
   public function __construct(private Request $request) {}
   // dentro del query:
   ->when($this->request->id, ...)
   ```
4. **`SetLocale`**:
   ```php
   if ($request->is('/') || $request->is('login')) { ... }
   ```
5. **Verificación rápida**:
   ```bash
   grep -rn "request(" app/Http/Controllers/ app/Http/Middleware/ app/Exports/   # debe dar 0
   grep -rn "request(" app/Library/                                            # debe dar >=1 (OUT)
   composer analyse && composer lint && php artisan test
   ```
6. **Absorción 5.3**: en `plan-accion.md`, marcar la fila 5.3 como "Absorbido por 4.4 (spec-inyectar-request-fase4)" y, al implementar, actualizar también la métrica de éxito correspondiente.
7. **Rama**: crear `spec/inject-request-fase4-impl` antes de editar (nunca `main`).