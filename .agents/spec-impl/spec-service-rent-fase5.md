---
id: spec-service-rent-fase5
titulo: "Service Rent — unificar RentInvitation web + Api Rent (Fase 5 / 5.13, hijo 2)"
estado: Borrador
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.13) — DT-A1, DT-C4
cobertura: Migración de Rent a app/Services/RentService.php unificando Landlord/RentInvitationController (365) y Api/RentController (290); dueño único del bloque service-charge/SecurityDeposit
---

# Spec — Service Rent (Fase 5 / 5.13, hijo 2)

> Unificar las dos capas de Rent en `RentService`, dueño único del cálculo service-charge/depósito y de la creación tx `Rent`+`SecurityDeposit` (`createWithDeposit`), que el hijo application reutilizará. Controladores < 200 líneas, contrato idéntico.

**Patrón de referencia:** `spec-unificar-capas-services-fase4.md` §8. **Paraguas:** `spec-unificar-capas-services-fase5.md`.

## 1. Objetivo

`Landlord/RentInvitationController` (365) y `Api/RentController` (290) quedan < 200, con el `store()` casi idéntico (~95% de duplicación) unificado en `RentService::createWithDeposit`, única fuente del bloque hoy copiado 3 veces (la 3ª vive en `Api/TenantApplicationController::agreement_sign` y la consumirá el hijo 3).

## 2. Alcance

### IN
- `app/Services/RentService.php`: `listForChannel`, `loadForShow`, `createWithDeposit`, `approve`, `cancel`, `agreementUpload`, `tenantAccept`, `delete`, `deleteMany`, `unitOptions`.
- Form Requests compartidos (`StoreRentRequest`): validación de período (`match` days/weeks/months/years) y demás reglas actuales.
- Refactor in-place de ambos controladores (Api → Landlord web).

### OUT
- `Api/TenantApplicationController::agreement_sign` (se toca en el hijo 3, que llama a `createWithDeposit`).
- `Admin/RentPayment*` (ya migrados en Fase 4).
- Rutas `rents/*` y `rent/*`: se preservan intactas.

### Cobertura objetivo
- 2 controladores < 200; 0 duplicación del bloque service-charge; suite + PHPStan + Pint en verde.

## 3. Pre-requisitos

| # | Pre-requisito | Estado | Acción |
|---|---|---|---|
| P1 | Hijo 1 (language) mergeado | Pendiente | Orden del paraguas |
| P2 | Suite verde | 371 casos | `php artisan test` |
| P3 | Policies de Rent/Application | Existen (f7–f9 `IdorPolicySmokeTest`) | Reutilizar; sin cambios |

## 4. Estructura propuesta

```
app/Services/RentService.php
app/Http/Requests/Rent/StoreRentRequest.php
tests/Unit/RentServiceTest.php
tests/Feature/Rent/LandlordRentHttpTest.php    # caracterización previa
tests/Feature/Rent/ApiRentHttpTest.php         # caracterización previa
```

### Contrato del service

```php
class RentService
{
    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator;
    public function loadForShow(Rent $rent, Channel $channel): Rent;
    public function createWithDeposit(array $data, ?UploadedFile $landlordAgreement = null): Rent;
    // ↑ única fuente del bloque: Option 'service-charge' + deposit/advance/charge/landlord_balance
    //   + tx { Rent::create + SecurityDeposit::create } + sendNotification post-commit
    public function approve(Rent $rent, Channel $channel): Rent;          // Api añade sendNotification
    public function cancel(Rent $rent, Channel $channel, ?string $info): Rent; // cancel_info + status=cancelled
    public function agreementUpload(Rent $rent, UploadedFile $tenantAgreement): Rent;
    public function tenantAccept(Rent $rent): Rent;
    public function delete(Rent $rent, Channel $channel): void;
    public function deleteMany(array $ids, Channel $channel, ?User $actor): int;
    public function unitOptions(?int $landlordId): Collection;           // getUnit dropdown
}
```

### Reglas por canal (hoy divergentes — se preservan tal cual)

| Aspecto | Landlord web | Api |
|---|---|---|
| `approved()` notificación | sin `sendNotification` | con `sendNotification` |
| cancel | `canceled()` (web) | `tenant_cancelled()` — misma lógica `cancel_info`+status |
| index | vista + dropdowns (units/properties) | JSON con分支 por role (landlord/tenant) |
| extras | `getUnit`, `show` | `tenant_agreement`, `tenant_accept`, `show` |

## 5. Decisiones técnicas

- `RentService` es **dueño del bloque service-charge/depósito** (grill Q5): `createWithDeposit` es la firma pública que el hijo 3 (`ApplicationService::agreementSign`) invocará. Prohibido reimplementar el cálculo en application.
- Orden de migración: Api → Landlord web, suite verde tras cada paso.
- Tx 4.8 vigente en ambos `store` actuales (uploads antes, `sendNotification` post-commit) — el service lo preserva.
- `RentInvitationController` deja de usar `HasTableActions`; `filter`/`exportExcel`/`exportCsv` (rutas `rents/filter`, `rents-excel`, `rents-csv`) se reimplementan delegando al service — cero rutas muertas (paraguas §5.4).
- Form Request único con reglas por canal donde hoy difieren.

## 6. Catálogo de casos (prefijo `F` HTTP, `U` unit — hoy cero tests HTTP de Rent)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Store web crea rent+deposit | landlord A con unidad libre | POST /landlord/rents (período months, landlord_agreement) | 200; Rent + SecurityDeposit creados; cálculo deposit/advance/charge idéntico al actual |
| F2 | Store api igual | Sanctum landlord A | POST /api/v1/landlord/rent | 200 JSON; mismo cálculo que F1 |
| F3 | Validación de período | payload weeks con days equivale | POST store | mismos 422/valores actuales por unidad days/weeks/months/years |
| F4 | Approved web sin notif | landlord A, rent pending | POST /landlord/rents/approved/{id} | 200; rent activa; sin `sendNotification` |
| F5 | Approved api con notif | Sanctum landlord | POST /api/v1/landlord/rent/approved/{id} | 200; rent activa; `sendNotification` emitido |
| F6 | Cancel web | landlord A | POST /landlord/rents/canceled/{id} (cancel_info) | 200; status=cancelled |
| F7 | Cancel api (tenant) | Sanctum tenant | POST /api/v1/landlord/rent/cancelled/{id} | 200; status=cancelled |
| F8 | Agreement upload api | Sanctum tenant, rent propia | POST /api/v1/landlord/rent/agreement-sign/{id} | 200; tenant_agreement guardado (sin tx, 1 escritura — comportamiento actual) |
| F9 | Tenant accept api | Sanctum tenant | POST /api/v1/landlord/rent/accept/{id} | 200; estado de aceptación actual idéntico |
| F10 | Index web con dropdowns | landlord A | GET /landlord/rents | 200 vista; units/properties cargados |
| F11 | Index api por role | Sanctum landlord / Sanctum tenant | GET /api/v1/landlord/rent | 200 JSON; ramas role actuales |
| F12 | GetUnit | landlord A | GET /landlord/get-units | 200 JSON opciones de unidad |
| F13 | Filter/excel/csv web | landlord A, 2 rents | POST /landlord/rents/filter, GET rents-excel/csv | 200/200/200 (rutas vivas tras migración) |

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | createWithDeposit cálculo | Option service-charge=S | `createWithDeposit(data)` | deposit/advance/charge/landlord_balance = fórmula actual |
| U2 | createWithDeposit tx | — | idem | Rent + SecurityDeposit atómicos; notificación post-commit |
| U3 | approve por canal | rent pending | `approve(rent, Api)` vs `approve(rent, Landlord)` | notif solo en Api |
| U4 | cancel unificado | rent activa | `cancel(rent, Landlord, info)` | cancel_info persistida, status=cancelled |

> F1–F13 caracterizan comportamiento actual; se implementan antes de migrar. El bloque de F1/F2 debe arrojar **los mismos números** que el código actual (caracterización numérica del cálculo).

## 7. Criterios de aceptación (Definition of Done)

- [ ] `RentService` en verde (U1–U4); `createWithDeposit` firma pública estable para el hijo 3.
- [ ] Caracterización F1–F13 en verde antes de migrar; sigue en verde después.
- [ ] 2 controladores < 200 líneas, sin `HasTableActions`, 0 copias del bloque service-charge en controladores (grep `service-charge` en `app/Http/Controllers` = 0).
- [ ] Rutas `rents/filter|excel|csv` vivas (delegan al service).
- [ ] Suite + PHPStan + Pint en verde.

## 8. Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Divergencia numérica del cálculo al unificar | F1/F2 con valores concretos congelados; U1 unit test |
| Diferencias de notificación web/api olvidadas | Tabla §4 + F4/F5 |
| `tenant_agreement`/`store` de application aún con copia vieja hasta hijo 3 | Aceptado (transitorio); grep documentado como pendiente del hijo 3 |

## 9. Estimación

- 5–7 días (caracterización 2, service+forms 1.5–2, migración Api+Landlord 1.5–2, validación 0.5–1).

## 10. Referencias

- `spec-unificar-capas-services-fase5.md` (paraguas) · `spec-unificar-capas-services-fase4.md` §8
- `.analisis/plan/plan-accion.md` → Fase 5 / 5.13 · DT-A1, DT-C4
- `tests/Feature/Idor/IdorPolicySmokeTest.php` (f7–f9, red de regresión)
