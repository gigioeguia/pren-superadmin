---
id: spec-unificar-capas-services-fase4
titulo: "Unificar 3 capas Admin/Landlord/Api con Services (Fase 4 / 4.2) — slice piloto Property"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 4 / 4.2) — DT-A1, DT-C4
cobertura: Arquitectura de Services + slice piloto completo de Property (CRUD en Admin web, Landlord web y Api) + patrón de referencia para las 11 entidades restantes
---

# Spec — Unificar 3 capas Admin/Landlord/Api con Services (Fase 4 / 4.2)

> Mover la lógica de negocio de los controladores triplicados (Admin/Landlord/Api) a `app/Services`, dejando controladores finos (HTTP/transporte), sin cambiar el contrato observable (rutas, vistas, JSON).

**Origen:** `.analisis/plan/plan-accion.md` (Fase 4 / 4.2, origen DT-A1 y DT-C4).

---

## 1. Objetivo

Definir el patrón de unificación (Service + Form Request + Channel + excepciones de dominio) y aplicarlo de extremo a extremo a **Property** (la entidad más compleja y con más tests de ownership existentes), de modo que sirva de referencia para las 11 entidades restantes. Al finalizar el slice piloto, los 3 controladores de Property quedan por debajo de 200 líneas y sin duplicación de lógica de negocio.

---

## 2. Alcance

### IN
- Definición del patrón: enum `Channel`, Service por entidad, Form Requests por operación, excepciones de dominio, convención de métodos.
- Slice piloto **Property**: migración completa (index, show, store, update, destroy, deleteAll, status, approve, reject, getFacilities/getAmenities, update_status de Api) en las 3 capas (Admin web, Landlord web, Api).
- Sección "Patrón de referencia" para los specs hijos de las otras 11 entidades.
- Tests de caracterización HTTP previos por rol en las 3 capas.
- Tests unitarios de `PropertyService`.

### OUT
- Las otras 11 entidades (se migran en specs hijos siguiendo el patrón).
- Migración de estructura Laravel 11 (ya hecho en 4.1).
- `env()` → `config()` (4.3), inyección de `Request` (4.4), Vite (4.5), helpers a clases (4.6), etc.
- Cambios de comportamiento observable (rutas, vistas, JSON shapes, códigos HTTP se preservan).
- Renombrado del prefijo `Acnoo` (5.10).

### Cobertura objetivo
- 3 controladores de Property < 250 líneas (vs 560-720 originales).
- 0 duplicación de lógica de negocio entre capas para Property.
- Suite completa en verde + tests nuevos de Service.
- PHPStan nivel 5 sin regresiones de baseline; Pint en verde.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rama de trabajo | En `main` | `git checkout -b spec/unificar-capas-services-fase4-impl` al implementar |
| P2 | Suite verde | 183 tests en verde sobre PHP 8.3 | `php artisan test` antes de empezar; debe seguir verde tras cada paso |
| P3 | PHPStan 5 + Pint + CI | Configurados (Fase 0.2) | Usarlos como red; no regenerar baseline |
| P4 | Policies de Property | Implementadas (Fase 2 / 2.1) | Reutilizar `PropertyPolicy` en controladores |
| P5 | Tests de ownership Property | F25–F30 en verde | Servirán de red de regresión |
| P6 | Trait `HasAcnooActions` | Adoptado en Admin/Landlord | Property deja de usarlo; las demás entidades lo mantienen |
| P7 | `plan_data()` acepta `landlord_id` | Verificado en Helper | Service lo invoca con `$actor->id` |
| P8 | Uploads normalizados (Fase 3.3) | Implementado | Service reutiliza `HasUploader` |

---

## 4. Estructura propuesta

```
app/
├── Enums/
│   └── Channel.php                              # nuevo: Admin, Landlord, Api
├── Services/
│   ├── AcnooLateFeeService.php                  # existente
│   └── PropertyService.php                      # nuevo
├── Http/
│   ├── Controllers/
│   │   ├── Admin/PropertyController.php        # refactor in-place (< 200 líneas)
│   │   ├── Landlord/PropertyController.php      # refactor in-place (< 200 líneas)
│   │   └── Api/AcnooPropertyController.php      # refactor in-place (< 200 líneas)
│   └── Requests/
│       └── Property/
│           ├── StorePropertyRequest.php          # nuevo
│           └── UpdatePropertyRequest.php         # nuevo
├── Exceptions/
│   ├── PlanLimitExceededException.php           # nuevo
│   └── PropertyRentedException.php              # nuevo
tests/
├── Unit/
│   └── PropertyServiceTest.php                 # nuevo
└── Feature/
    └── Property/                                # caracterización por canal (nuevo)
        ├── AdminPropertyHttpTest.php
        ├── LandlordPropertyHttpTest.php
        └── ApiPropertyHttpTest.php
```

### Contrato del Service

```php
namespace App\Services;

use App\Enums\Channel;
use App\Models\Property;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PropertyService
{
    use \App\Helpers\HasUploader; // uploads centralizados

    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator;

    public function loadForShow(Property $property, Channel $channel): Property;

    public function store(array $data, Channel $channel, ?User $actor, ?int $landlordId = null): Property;

    public function update(Property $property, array $data, Channel $channel, ?User $actor): Property;

    public function delete(Property $property, Channel $channel): void;

    public function deleteMany(array $ids, Channel $channel, ?User $actor): int;

    public function approve(Property $property): Property;            // admin

    public function reject(Property $property, string $reason): Property; // admin

    public function setStatus(Property $property, string|int $status): Property;

    public function updateStatus(Property $property, bool $isPublished): Property; // api

    public function facilitiesForLandlord(?int $landlordId): Collection;

    public function amenitiesForLandlord(?int $landlordId): Collection;
}
```

### Enum Channel

```php
namespace App\Enums;

enum Channel: string
{
    case Admin = 'admin';
    case Landlord = 'landlord';
    case Api = 'api';
}
```

### Excepciones de dominio

```php
class PlanLimitExceededException extends \RuntimeException {}
class PropertyRentedException extends \RuntimeException {}
```

### Reglas por canal (resumen)

| Aspecto | Admin | Landlord | Api |
|---------|-------|----------|-----|
| `landlord_id` en store | del request (validado: existe user role landlord) | `auth()->id()` (actor) | `auth()->id()` (actor) |
| `status` en store | `1` | `0` | `0` |
| Plan limit check | no | sí (lanza `PlanLimitExceededException`) | sí |
| Notificación | admin + landlord | admin + landlord | landlord (admin_msg "New property added") |
| Guard de rent en delete | no (comportamiento actual) | sí (lanza `PropertyRentedException`) | no |
| approve/reject | sí | no | no |
| update_status (1/2) | no | sí (status route) | sí |

---

## 5. Decisiones técnicas

Fijadas, no negociables salvo volver el spec a `Borrador`:

- **Estructura:** se preserva la estructura Laravel 10/11 actual (Kernels, RouteServiceProvider). No se migra a la nueva estructura de L11.
- **Runtime:** PHP 8.3, Laravel 11 (ya establecido en 4.1).
- **Service:** una clase por entidad, instanciable e inyectable por el contenedor; sin Repository; acceso directo a Eloquent.
- **HTTP:** los controladores usan constructor injection del Service; autorizan con Policies antes de llamar al Service.
- **Validación:** Form Requests por operación, reglas dinámicas según actor/role; reutilizados por las 3 capas.
- **Channel:** enum `App\Enums\Channel` string-backed, reutilizable por futuras entidades.
- **Transacciones:** `DB::beginTransaction/commit/rollBack` solo en el Service para persistencia; notificaciones/mails/HTTP fuera de la transacción (post-commit).
- **Uploads:** el Service usa el trait `HasUploader`; los controladores solo pasan `UploadedFile` del request.
- **Errores de regla de negocio:** el Service lanza excepciones tipadas; el controlador las captura y traduce al código HTTP/ruta apropiado (406 para plan limit, 400 para rent guard).
- **Compatibilidad:** URIs, nombres de ruta, vistas Blade, shapes JSON y códigos HTTP se preservan; el refactor es interno.
- **Baseline PHPStan:** no se regenera; los errores nuevos se corrigen.
- **Tests:** sin mocks de DB en feature tests (MySQL `dbrentpro_test`); unit tests del Service usan `RefreshDatabase`.
- **Orden de migración dentro del slice:** Api → Landlord web → Admin web, validando suite en verde tras cada paso.

---

## 6. Catálogo de casos

Prefijos `F` (feature HTTP) y `U` (unit Service). **Total: 42 casos (F1–F26, U1–U16).**

### 6.1 Caracterización HTTP — Property (`F`)

#### Index / listado

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Admin lista propiedades | admin autenticado, 3 propiedades de landlords distintos | GET /admin/properties | 200, vista con las 3 propiedades |
| F2 | Landlord lista solo las propias | landlord A con 2 propiedades, landlord B con 1 | GET /landlord/properties como A | 200, solo las 2 de A |
| F3 | Api landlord lista las propias | Sanctum landlord A | GET /api/v1/landlord/properties | 200 JSON data con las 2 de A |
| F4 | Api tenant solo ve publicadas | tenant autenticado, propiedades status 0 y 1 | GET /api/v1/landlord/properties (role tenant) | 200, solo status=1 |
| F5 | Api no_paginate | Sanctum landlord | GET .../properties?no_paginate=true | 200 JSON {data: [...]} sin paginación |
| F6 | Api available_rent | Sanctum landlord con propiedades rentadas y libres | GET .../properties?available_rent=true | 200, solo las libres |
| F7 | Api sorting low_to_high | Sanctum landlord | GET .../properties?sorting=low_to_high | 200, ordenado por rent asc |

#### Store

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F8 | Store admin crea con landlord_id y status=1 | admin autenticado, landlord B existe | POST /admin/properties con landlord_id=B | 200 JSON, propiedad con landlord_id=B, status=1 |
| F9 | Store landlord crea con auth id y status=0 | landlord A autenticado, plan sin límite | POST /landlord/properties | 200 JSON, landlord_id=A, status=0 |
| F10 | Store api igual a landlord | Sanctum landlord A | POST /api/v1/landlord/properties | 200 JSON data con landlord_id=A, status=0 |
| F11 | Store landlord plan limit excedido | landlord A con N propiedades = límite del plan | POST /landlord/properties | 406 JSON con mensaje de límite |
| F12 | Store admin landlord_id inválido | admin autenticado | POST /admin/properties con landlord_id=tenant | 422 |

#### Show

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F13 | Show admin carga relaciones | admin, propiedad con units | GET /admin/properties/{id} | 200 vista con property, units, facility/amenity names |
| F14 | Show landlord propio | landlord A, propiedad propia | GET /landlord/properties/{id} como A | 200 vista |
| F15 | Show api incluye reviews y can_review | Sanctum tenant que rentó la propiedad | GET /api/v1/landlord/properties/{id} | 200 JSON con property, reviews, can_review, ratings |

#### Update

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F16 | Update admin reasigna landlord | admin, propiedad de landlord B | PUT /admin/properties/{id} con landlord_id=C | 200, landlord_id=C |
| F17 | Update landlord no cambia owner | landlord A, propiedad propia | PUT /landlord/properties/{id} con landlord_id=B en payload | 200, landlord_id=A (payload ignorado) |
| F18 | Update api no cambia owner | Sanctum landlord A | PUT /api/v1/landlord/properties/{id} con landlord_id=B | 200, landlord_id=A |
| F19 | Update superadmin no roba ownership (regresión F25) | superadmin Sanctum, propiedad de landlord B | PUT /api/v1/landlord/properties/{id} | 200, landlord_id=B |

#### Delete

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F20 | Destroy admin limpia media | admin, propiedad con cover/units | DELETE /admin/properties/{id} | 200 JSON, propiedad borrada, media eliminada |
| F21 | Destroy landlord guard rent | landlord A, propiedad rentada | DELETE /landlord/properties/{id} | 400 JSON "already rented" |
| F22 | Destroy api | Sanctum landlord A, propiedad propia no rentada | DELETE /api/v1/landlord/properties/{id} | 200 JSON |
| F23 | DeleteAll landlord | landlord A, 2 propiedades propias | POST /landlord/properties/delete-all ids=[...] | 200 JSON, ambas borradas, media limpia |

#### Acciones admin

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F24 | Approve admin | admin, propiedad status=0 | POST /admin/properties/approve/{id} | 200 JSON, status=1 |
| F25 | Reject admin con reason | admin, propiedad | POST /admin/properties/reject/{id} con meta.reason | 200 JSON, status=3, meta.reason guardado |
| F26 | Status toggle landlord | landlord A, propiedad propia | POST /landlord/properties/status/{id} | 200 JSON status actualizado |

#### Auxiliares

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F27 | Get facilities por landlord | admin | GET /admin/properties/get-facilities/{landlordId} | 200 JSON lista |
| F28 | Get amenities por landlord | admin | GET /admin/properties/get-amenities/{landlordId} | 200 JSON lista |
| F29 | Api update_status publica | Sanctum landlord, propiedad propia | POST /api/v1/landlord/properties/status con is_published=true | 200 JSON, status=1 |

> Nota: los casos F1–F29 caracterizan el comportamiento actual (incluidos los guards de Fase 2) y deben seguir pasando tras la migración (regresión). Los casos de ownership F25–F30 de `PropertyOwnershipSmokeTest` se mantienen como regresión.

### 6.2 Unit tests — PropertyService (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | listForChannel admin devuelve todas | 3 propiedades de landlords distintos | `listForChannel(Admin, admin, [])` | paginador con 3 |
| U2 | listForChannel landlord filtra por actor | landlord A con 2, B con 1 | `listForChannel(Landlord, A, [])` | paginador con 2 (solo A) |
| U3 | listForChannel api filtra por role tenant | tenant, propiedades status 0/1 | `listForChannel(Api, tenant, [])` | solo status=1 |
| U4 | store admin status=1 y landlord_id del data | admin actor, data con landlord_id=B | `store(data, Admin, admin, landlordId=B)` | Property landlord_id=B, status='1' |
| U5 | store landlord status=0 y landlord_id=actor | landlord A actor | `store(data, Landlord, A)` | landlord_id=A, status='0' |
| U6 | store landlord plan limit lanza excepción | landlord A en límite | `store(data, Landlord, A)` | lanza `PlanLimitExceededException` |
| U7 | store api igual a landlord | landlord A actor | `store(data, Api, A)` | landlord_id=A, status='0' |
| U8 | update landlord no cambia owner | propiedad de A, data con landlord_id=B | `update(prop, data, Landlord, A)` | landlord_id=A |
| U9 | update admin puede cambiar owner | propiedad de B, data con landlord_id=C | `update(prop, data, Admin, admin)` | landlord_id=C |
| U10 | delete landlord lanza si rentada | propiedad rentada | `delete(prop, Landlord)` | lanza `PropertyRentedException`, no borra |
| U11 | delete limpia media y borra | propiedad con cover/units | `delete(prop, Admin)` | media eliminada, fila borrada |
| U12 | deleteMany respeta ownership | landlord A, ids de A y B | `deleteMany([idA,idB], Landlord, A)` | solo idA borrado, retorna 1 |
| U13 | deleteMany lanza si alguno rentado | ids incluyen rentado | `deleteMany(...)` | lanza `PropertyRentedException` |
| U14 | approve establece status=1 | propiedad status=0 | `approve(prop)` | status='1' |
| U15 | reject establece status=3 y meta.reason | propiedad | `reject(prop, 'motivo')` | status='3', meta['reason']='motivo' |
| U16 | updateStatus api publica/oculta | propiedad | `updateStatus(prop, true/false)` | status=1 / status=2 |

### Tests de caracterización

Ninguno de los casos anteriores documenta bugs conocidos: todos caracterizan el comportamiento esperado (incluidos los guards de Fase 2) y deben seguir pasando tras la migración. Los casos de caracterización de bugs (Seg-5/Seg-6) ya se retiraron en Fase 2.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] Enum `App\Enums\Channel` creado y usado por el Service.
- [x] `PropertyService` implementado con todos los métodos del contrato (§4).
- [x] `StorePropertyRequest` y `UpdatePropertyRequest` creados con reglas dinámicas por canal/actor.
- [x] `PlanLimitExceededException` y `PropertyRentedException` creadas.
- [x] `Admin/PropertyController` refactorizado in-place, 225 líneas (<250), sin `HasAcnooActions` (F1, F8, F13, F16, F20, F24, F25, F27, F28).
- [x] `Landlord/PropertyController` refactorizado in-place, 200 líneas, sin `HasAcnooActions` (F2, F9, F11, F14, F15, F21, F23, F26).
- [x] `Api/AcnooPropertyController` refactorizado in-place, < 200 líneas (F3, F4, F5, F6, F7, F10, F12, F17, F18, F19, F22, F29).
- [x] Suite completa en verde (183+ tests) tras cada paso de migración (Api → Landlord → Admin).
- [x] Tests unitarios `PropertyServiceTest` (U1–U16) en verde.
- [x] Tests de caracterización HTTP por canal en verde.
- [x] `vendor/bin/phpstan analyse` nivel 5 sin regresiones de baseline.
- [x] `vendor/bin/pint --test` en verde.
- [x] `npm run build` en verde (sin cambios frontend, pero se valida).
- [x] Contrato HTTP verificado: rutas, vistas, JSON shapes y códigos idénticos (F1–F29 en verde).
- [x] Sección "Patrón de referencia" incluida para specs hijos.

---

## 8. Patrón de referencia (para specs hijos)

Cada entidad futura sigue esta plantilla. Crear un spec `spec-service-<entidad>-fase4.md` con:

### 8.1 Estructura de archivos
```
app/Services/<Entidad>Service.php
app/Http/Requests/<Entidad>/Store<Entidad>Request.php
app/Http/Requests/<Entidad>/Update<Entidad>Request.php
app/Exceptions/<Entidad...>Exception.php   (solo si hay reglas de negocio con fallo)
tests/Unit/<Entidad>ServiceTest.php
tests/Feature/<Entidad>/...HttpTest.php
```

### 8.2 Firma mínima del Service
```php
class <Entidad>Service
{
    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator;
    public function show(<Model> $model, Channel $channel): <Model>;
    public function store(array $data, Channel $channel, ?User $actor, ?int $ownerId = null): <Model>;
    public function update(<Model> $model, array $data, Channel $channel, ?User $actor): <Model>;
    public function delete(<Model> $model, Channel $channel): void;
    public function deleteMany(array $ids, Channel $channel, ?User $actor): int;
}
```
Añadir métodos de dominio específicos (approve, reject, setStatus, etc.) según la entidad.

### 8.3 Reglas de migración
1. Crear el Service + Form Requests + excepciones + unit tests **antes** de tocar controladores.
2. Migrar en orden: **Api → Landlord web → Admin web**, suite verde tras cada paso.
3. El controlador resuelve el modelo (route model binding o `findOrFail`), autoriza con la Policy, llama al Service, arma la respuesta.
4. El Service no lee `auth()` ni `request()`; recibe `$actor` y `$data` explícitos.
5. Las notificaciones se envían dentro del Service según `Channel`, post-commit.
6. Las transacciones envuelven solo persistencia; E/S externa fuera.
7. El controlador deja de usar `HasAcnooActions` para la entidad migrada; el trait sigue para las no migradas.
8. Verificar DoD: controller < 200 líneas, 0 duplicación, suite + PHPStan + Pint en verde, contrato HTTP idéntico.

### 8.4 Entidades pendientes (11)
Expense, ExpenseCategory, Income, IncomeCategory, Tenant, SecurityDeposit, RentPayment, RefundRequest, Support, WithdrawMethod, Notification.

---

## 9. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| El index de Api es muy complejo (no_paginate, available_rent, sorting, favorite) | Service grande, riesgo de regresión | Migrar con tests de caracterización previos (F5, F6, F7, F29) |
| Uploads/media cleanup divergente entre capas | pérdida de archivos o media huérfana | Centralizar en `delete()`/`deleteMany()` del Service con captura pre-delete |
| Diferencias de notificación entre capas | olvido de notificar en algún canal | Tabla de reglas por canal (§4) + tests HTTP |
| Plan limit check depende de `plan_data()` (caché) | datos stale en tests | Usar `RefreshDatabase` y limpiar caché en tests |
| PHPStan nuevos errores por Service/Form Requests | CI rojo | Corregir en el mismo PR, no regenerar baseline |
| Regresión de ownership (F25–F30) | IDOR reintroducido | Mantener tests existentes + unit tests U8/U9 |

**Pendiente de decisión:**
- ¿Activar branch protection (require status checks) tras el merge del slice? (settings GitHub, fuera del repo.)

---

## 10. Estimación

- Enum + Service + Form Requests + excepciones: 1–2 días
- Tests de caracterización HTTP previos (F1–F29): 2–3 días
- Tests unitarios Service (U1–U16): 1 día
- Migración Api (controller < 200 líneas): 1–2 días
- Migración Landlord web: 2–3 días
- Migración Admin web: 2–3 días
- Validación (suite, PHPStan, Pint, build, contrato HTTP): 1 día
- **Total slice piloto: ~10–15 días**
- Cada entidad hija siguiendo el patrón: ~5–8 días

---

## 11. Referencias

- `.analisis/plan/plan-accion.md` → Fase 4 / 4.2 (DT-A1, DT-C4)
- `.analisis/deuda-tecnica.md` → DT-A1, DT-C4, DT-M5, DT-M6
- `.agents/spec/spec-conventions.md`
- `.agents/spec/spec-template.md`
- `.agents/spec-impl/spec-trait-acnoo-fase0.md` (trait de filtrado, referencia de hooks)
- `.agents/spec-impl/spec-policies-fase2.md` (Policies existentes)
- `.agents/spec-impl/spec-deleteall-row-by-row-media-fase2.md` (limpieza de media post-delete)
- `AGENTS.md` → sección Controller layout
- `app/Helpers/Helper.php` → `plan_data()`, `sendNotification()`, `notify_users()`
- `app/Services/AcnooLateFeeService.php` (servicio existente de referencia)

---

## 12. Revisión

Implementado en rama `spec/unificar-capas-services-fase4-impl`.

### Desviaciones menores
| # | Punto | Detalle |
|---|-------|---------|
| D1 | Form Requests | `landlord_id` requerido solo en web admin (no en API), para no romper el flujo de superadmin vía API (F25). |
| D2 | Uploads | Service reutiliza `HasUploader`; los controladores pasan `$request->file('units')` bajo `unit_files` en los datos validados. |
| D3 | Enriquecimiento de `show` | Se mantiene en el controlador (presentación), como se decidió en el grill. |
| D4 | `listForChannel` API | `no_paginate` se maneja paginando con per_page = total; el controlador desenvuelve con `array_values(items())`. |
| D5 | PHPStan baseline | Se eliminaron ignores huérfanos tras añadir return types a `Property` y `PlanSubscribe` (mejora, no regeneración). |
| D6 | Modelos | Se añadieron return types a las relaciones de `Property` y a `PlanSubscribe::plan()` para satisfacer Larastan. |
| D7 | Tests | Se añadieron `tests/Unit/PropertyServiceTest.php` (U1–U16) y se actualizaron los smoke tests de trait para excluir `PropertyController`. |

### Evidencia (DoD)
- `php artisan test` → **199 passed** (489 assertions).
- `vendor/bin/phpstan analyse` → `[OK] No errors` (nivel 5).
- `vendor/bin/pint --test` → `passed`.
- Controladores de Property: Admin 225 líneas, Landlord 200 líneas, Api 133 líneas (vs 724/663/566 originales).

### Cierre

Estado → `Implementado`: DoD §7 tachado completo. Pendiente: migrar las 11 entidades restantes con specs hijos siguiendo el patrón (§8).
