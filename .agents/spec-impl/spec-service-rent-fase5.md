---
id: spec-service-rent-fase5
titulo: "Service Rent — unificar RentInvitation web + Api Rent (Fase 5 / 5.13, hijo 2)"
estado: Aprobado
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.13) — DT-A1, DT-C4
cobertura: Migración de Rent a app/Services/RentService.php unificando Landlord/RentInvitationController (365) y Api/RentController (290); dueño único del bloque service-charge/SecurityDeposit
---

# Spec — Service Rent (Fase 5 / 5.13, hijo 2)

> Unificar las dos capas de Rent en `RentService`, dueño único del cálculo service-charge/depósito y de la creación tx `Rent`+`SecurityDeposit` (`createWithDeposit`), que el hijo application reutilizará. Controladores < 200 líneas, contrato HTTP idéntico por canal.

**Patrón de referencia:** `spec-unificar-capas-services-fase4.md` §8. **Paraguas:** `spec-unificar-capas-services-fase5.md`.

## 1. Objetivo

`Landlord/RentInvitationController` (365) y `Api/RentController` (290) quedan < 200, con el `store()` casi idéntico (~95% de duplicación) unificado en `RentService::createWithDeposit`, única fuente del bloque hoy copiado 3 veces (la 3ª vive en `Api/TenantApplicationController::agreement_sign` y la consumirá el hijo 3).

## 2. Alcance

### IN
- `app/Services/RentService.php`: `listForChannel`, `listAll`, `indexDropdowns`, `loadForShow`, `createWithDeposit`, `approve`, `cancel`, `agreementUpload`, `tenantAccept`, `unitOptions`.
- Form Request compartido (`StoreRentRequest`): validación de período (`match` days/weeks/months/years) y demás reglas actuales.
- Refactor in-place de ambos controladores (Api → Landlord web).
- Limpieza de los verbos muertos del resource de rent (decisión §5.5).

### OUT
- `Api/TenantApplicationController::agreement_sign` (se toca en el hijo 3, que llama a `createWithDeposit`).
- `Admin/RentPayment*` (ya migrados en Fase 4) y `Admin/ServiceChargeController`/`RentPaymentGenerateController` (usan la option `service-charge` legítimamente, fuera de scope).
- Contrato HTTP observable: idéntico por canal; solo cambian las rutas muertas eliminadas (§5.5).

### Cobertura objetivo
- 2 controladores < 200; 0 duplicación del bloque service-charge en los 2 migrados; suite + PHPStan + Pint en verde.

## 3. Pre-requisitos

| # | Pre-requisito | Estado | Acción |
|---|---|---|---|
| P1 | Hijo 1 (language) mergeado | Pendiente | Orden del paraguas |
| P2 | Suite verde | ~402 casos tras el merge del hijo 1 (402 en la rama del hijo 1) | `php artisan test` |
| P3 | Policies | `ApplicationPolicy` existe (f7–f9 `IdorPolicySmokeTest`); **`RentPolicy` no existe** — los endpoints de Rent operan hoy sin ownership scoping (quirk §5.7, preservado) | Reutilizar la de Application; sin policies nuevas ni scoping en este hijo (el fix del IDOR es fase de seguridad, no refactor) |

## 4. Estructura propuesta

```
app/Services/RentService.php
app/Http/Requests/Rent/StoreRentRequest.php
tests/Unit/RentServiceTest.php
tests/Feature/Rent/LandlordRentHttpTest.php    # tests de contrato previos
tests/Feature/Rent/ApiRentHttpTest.php         # tests de contrato previos
```

### Contrato del service

```php
class RentService
{
    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator; // rama paginada (per_page; input landlord_id → quirk §5.7)
    public function listAll(?User $actor, array $filters = []): Collection;      // rama no_paginate del Api index (F14)
    public function indexDropdowns(int $landlordId): array;                      // tenants + properties libres (web index, F10)
    public function loadForShow(Rent $rent, Channel $channel): Rent;
    public function createWithDeposit(array $data, ?UploadedFile $landlordAgreement = null): Rent;
    // ↑ única fuente del bloque: Option 'service-charge' + deposit/advance/charge/landlord_balance
    //   + tx { Rent::create + SecurityDeposit::create } + sendNotification post-commit
    public function approve(Rent $rent, Channel $channel): Rent;          // Api añade sendNotification; error-paths por canal según tabla §4
    public function cancel(Rent $rent, Channel $channel, ?string $title = null, ?string $reason = null): Rent; // cancel_info {title, reason, prev_end_date derivado} + status=cancelled
    public function agreementUpload(Rent $rent, UploadedFile $tenantAgreement): Rent;
    public function tenantAccept(Rent $rent): Rent;
    public function unitOptions(int $propertyId): Collection;             // getUnit: unidades libres de property_id (scope actual, sin filtro landlord — quirk §5.7)
}
```

Sin `delete`/`deleteMany` en el contrato: hoy ninguna ruta viva consume borrado de Rent (§5.5); si se reintroduce, será con decisión y ruta propias.

### Reglas por canal (hoy divergentes — se preservan tal cual)

| Aspecto | Landlord web | Api |
|---|---|---|
| `approved()` notificación | sin `sendNotification` | con `sendNotification` |
| `approved()` error-paths | 404 sin SecurityDeposit / 400 unpaid / try-catch → 500 con `report($e)` | crash 500 (lectura sobre null) sin depósito / 404 unpaid / sin try-catch |
| `store()` catch | 500 + `report($e)` | 500 silencioso |
| `store()` respuesta éxito | `{message, redirect}` | `{message, data}` |
| cancel | `canceled()` (web) | `tenant_cancelled()` — misma lógica `cancel_info`+status+notificación |
| index | vista + dropdowns (tenants/properties) | JSON con ramas por role (landlord/tenant) + rama `no_paginate` (F14) |
| extras | `getUnit`, `show` | `tenant_agreement`, `tenant_accept`, `show` |

## 5. Decisiones técnicas

1. `RentService` es **dueño del bloque service-charge/depósito** (grill Q5): `createWithDeposit` es la firma pública que el hijo 3 (`ApplicationService::agreementSign`) invocará. Prohibido reimplementar el cálculo en application.
2. Orden de migración: Api → Landlord web, suite verde tras cada paso.
3. Tx 4.8 vigente en ambos `store` actuales (uploads antes, `sendNotification` post-commit) — el service lo preserva.
4. `RentInvitationController` deja de usar `HasTableActions`; `filter`/`exportExcel`/`exportCsv` (rutas `rents/filter`, `rents-excel`, `rents-csv`) se reimplementan delegando al service — cero rutas muertas (paraguas §5.4).
5. **Verbos muertos del resource eliminados en este hijo** (paraguas §5.4): web `rents/create|edit|update|destroy` (métodos inexistentes; el trait no provee `destroy`) y api `rent/update|destroy`. Grep previo de vistas/JS: única coincidencia `@include('landlord.rents.create')` en `landlord/rents/index.blade.php` — es un partial blade (ruta de archivo), no una referencia de ruta. El borrado de Rent (`delete`/`deleteMany`) queda fuera del contrato: ninguna ruta viva lo consume hoy.
6. **Tests de contrato HTTP, no de caracterización** (paraguas §5.7): F1–F15 congelan el contrato y siguen en verde tras la migración; **no llevan `@group characterization`** (reservado a bugs con fase de ruptura).
7. **Quirks IDOR preservados y declarados** (estilo `file_download`, paraguas §5.5): ningún endpoint de Rent tiene ownership scoping hoy (web `findOrFail` en show/approved/canceled; api idem, sin global scopes en el modelo) y el Api index acepta `?landlord_id=` (un landlord lista rents ajenas — F15 congela la rama). El fix pertenece a una fase de seguridad con `RentPolicy`; este refactor no lo introduce (registro como seguimiento en `.analisis/seguridad.md` §5).
8. Form Request único `StoreRentRequest`: las reglas de `store` son **idénticas hoy en ambos canales** (verificado en revisión H9); request compartido, sin reglas por canal salvo nueva evidencia.

## 6. Catálogo de casos (prefijo `F` HTTP, `U` unit — hoy cero tests HTTP de Rent)

### 6.1 HTTP (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Store web crea rent+deposit | landlord A, tenant propio, unidad libre con deposit/advance definidos | POST /landlord/rents (período months, landlord_agreement) | 200; Rent + SecurityDeposit creados; cálculo deposit/advance/charge/landlord_balance idéntico al actual |
| F2 | Store api igual | Sanctum landlord A, mismos datos que F1 | POST /api/v1/landlord/rent | 200 JSON; mismo cálculo que F1 |
| F3 | Validación de período | unidad con min_rental_value=V, min_rental_unit ∈ {days,weeks,months,years} | POST store con rentalDays < mínimo | mismos 422/valores actuales por unidad (multiplicadores 1/7/30/365) |
| F4 | Approved web sin notif | landlord A, rent pending + security_deposit status=paid | POST /landlord/rents/approved/{id} | 200; rent activa; sin `sendNotification` |
| F5 | Approved api con notif | Sanctum landlord, rent pending + security_deposit status=paid | POST /api/v1/landlord/rent/approved/{id} | 200; rent activa; `sendNotification` emitido |
| F6 | Cancel web | landlord A, rent active | POST /landlord/rents/canceled/{id} (cancel_info) | 200; status=cancelled |
| F7 | Cancel api (tenant) | Sanctum tenant, rent active | POST /api/v1/landlord/rent/cancelled/{id} | 200; status=cancelled |
| F8 | Agreement upload api | Sanctum tenant, rent propia | POST /api/v1/landlord/rent/agreement-sign/{id} | 200; tenant_agreement guardado (sin tx, 1 escritura — comportamiento actual) |
| F9 | Tenant accept api | Sanctum tenant, rent propia | POST /api/v1/landlord/rent/accept/{id} | 200; estado de aceptación actual idéntico (status=processing) |
| F10 | Index web con dropdowns | landlord A | GET /landlord/rents | 200 vista; tenants/properties cargados |
| F11 | Index api por role | Sanctum landlord / Sanctum tenant | GET /api/v1/landlord/rent | 200 JSON; ramas role actuales |
| F12 | GetUnit | landlord A, property con unidades libres | GET /landlord/get-units?property_id=P | 200 JSON opciones de unidad |
| F13 | Filter/excel/csv web | landlord A, 2 rents | POST /landlord/rents/filter (ajax), GET rents-excel/csv | 200/200/200 (rutas vivas tras migración) |
| F14 | Index api no_paginate | Sanctum landlord / Sanctum tenant | GET /api/v1/landlord/rent?no_paginate=true | 200 JSON {data:[...]} sin paginación (rama Collection actual) |
| F15 | Index api landlord_id input (quirk) | Sanctum landlord A; rents de landlord B | GET /api/v1/landlord/rent?landlord_id={B} | 200 JSON con rents de landlord B — quirk IDOR §5.7 preservado |

### 6.2 Unit (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | createWithDeposit cálculo | Option service-charge=S | `createWithDeposit(data)` | deposit/advance/charge/landlord_balance = fórmula actual |
| U2 | createWithDeposit tx | — | idem | Rent + SecurityDeposit atómicos; notificación post-commit |
| U3 | approve por canal | rent pending | `approve(rent, Api)` vs `approve(rent, Landlord)` | notif solo en Api |
| U4 | cancel unificado | rent activa | `cancel(rent, Landlord, title, reason)` | cancel_info persistida, status=cancelled |

> F1–F15 son **tests de contrato** (paraguas §5.7): congelan el comportamiento actual (quirks incluidos) y se implementan antes de migrar; **no llevan `@group characterization`** — el DoD exige que sigan en verde después. El bloque de F1/F2 debe arrojar **los mismos números** que el código actual (congelado numérico del cálculo).

## 7. Criterios de aceptación (Definition of Done)

- [ ] `RentService` en verde (U1–U4); `createWithDeposit` firma pública estable para el hijo 3.
- [ ] Contrato F1–F15 en verde antes de migrar; sigue en verde después.
- [ ] 2 controladores < 200 líneas, sin `HasTableActions`; 0 ocurrencias de `service-charge` en `RentInvitationController` y `Api/RentController` (la copia de `Api/TenantApplicationController` queda hasta hijo 3; `Admin/ServiceChargeController`/`RentPaymentGenerateController` son usos legítimos fuera de scope).
- [ ] Rutas vivas `rents/filter|excel|csv` operativas (delegan al service); verbos muertos `rents/create|edit|update|destroy` y api `rent/update|destroy` eliminados con grep previo documentado.
- [ ] Suite + PHPStan + Pint en verde.

## 8. Riesgos

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Divergencia numérica del cálculo al unificar | alto | F1/F2 con valores concretos congelados; U1 unit test |
| Diferencias de notificación web/api olvidadas | medio | Tabla §4 + F4/F5 |
| `tenant_agreement`/`store` de application aún con copia vieja hasta hijo 3 | medio | Aceptado (transitorio); grep documentado como pendiente del hijo 3 |
| IDOR de rent (sin scoping, `?landlord_id=`) "arreglado" por accidente en el refactor | alto | Quirk declarado §5.7 + F15 congela la rama; fix solo en fase de seguridad con `RentPolicy` |

## 9. Estimación

- 5–7 días (contrato 2, service+forms 1.5–2, migración Api+Landlord 1.5–2, validación 0.5–1).

## 10. Referencias

- `spec-unificar-capas-services-fase5.md` (paraguas §5.4/§5.7) · `spec-unificar-capas-services-fase4.md` §8
- `.analisis/plan/plan-accion.md` → Fase 5 / 5.13 · DT-A1, DT-C4
- `tests/Feature/Idor/IdorPolicySmokeTest.php` (f7–f9 application; f19 rent_payment)
- `.analisis/seguridad.md` §5 (IDOR API — rent registrado como seguimiento en la revisión de este spec)

## 11. Revisión (2026-11-21)

Auditoría `revisar-spec` previa a la implementación. Hallazgos y resoluciones:

| H# | Hallazgo | Severidad | Resolución |
|----|----------|-----------|------------|
| H1 | DoD "grep `service-charge` en `app/Http/Controllers` = 0" insatisficible: `Api/TenantApplicationController` mantiene la copia hasta hijo 3 (§8 del propio spec) y `Admin/ServiceChargeController`/`RentPaymentGenerateController` la referencian legítimamente | alto | DoD re-alcado a los 2 controladores migrados (§7) |
| H2 | Firmas que no pueden expresar el comportamiento actual: `unitOptions(?int $landlordId)` (getUnit filtra por `property_id`), `cancel(..., ?string $info)` (cancel_info lleva title+reason), `listForChannel(): LengthAwarePaginator` (rama `no_paginate` devuelve Collection) y dropdowns del web index sin cabida en el contrato | medio | Contrato §4 corregido: `unitOptions(int $propertyId)`, `cancel(..., ?string $title, ?string $reason)`, `listAll()`, `indexDropdowns()`; F14 cubre no_paginate |
| H3 | Error-paths de `approved` divergentes sin documentar (web 404/400/try-catch+report vs api crash-null/404); F4/F5 no ejecutables tal cual (Dado sin "deposit paid") | medio | Filas añadidas a la tabla §4; Dado de F4/F5 completado |
| H4 | Terminología «caracterización» contradice paraguas §5.7 (e hijo 1 H3, ya corregido allí): son tests de contrato, sin `@group characterization` | medio | Renombrados a "tests de contrato" (§4, §5.6, §6, §7); decisión explícita añadida |
| H5 | P3 falso: no existe `RentPolicy`; f7–f9 cubren Application (f19 es RentPayment). Endpoints de Rent sin ownership scoping (web `findOrFail`; api `index` acepta `?landlord_id=` → lectura cruzada entre landlords) — IDOR ausente de `seguridad.md` §5 | medio | P3 reescrito; quirk declarado (§5.7) + F15 congela la rama; registrado en `seguridad.md` §5 |
| H6 | OUT "rutas preservadas intactas" choca con paraguas §5.4: verbos muertos del resource (web create/edit/update/destroy — el trait no provee `destroy`; api update/destroy) + contrato con `delete`/`deleteMany` sin ruta viva que los consuma | medio | Decisión §5.5: verbos muertos eliminados con grep previo (única coincidencia: `@include('landlord.rents.create')`, partial blade); `delete`/`deleteMany` fuera del contrato; ítem de DoD dedicado |
| H7 | P2 "371 casos" desactualizado (402 en la rama del hijo 1 implementado) | bajo | P2 corregido (~402 tras merge P1) |
| H8 | Mojibake "分支" en tabla §4 (fila index) | bajo | Corregido a "ramas por role" |
| H9 | Dados incompletos/redacción: F1 sin `tenant_id`, F13 sin cabecera AJAX, F3 confusa, "reglas por canal donde hoy difieren" sin instancia (store idéntico en ambos canales) | bajo | Catálogo pulido (F1/F3/F13); §5.8 precisa que las reglas de store son idénticas hoy |

**Estado:** `Borrador` → `Aprobado` (decisor autoriza enmendar + aprobar en la misma sesión de revisión, como hijo 1). Implementación gateda por P1 (merge del hijo 1).
