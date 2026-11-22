---
id: spec-service-application-fase5
titulo: "Service Application — unificar 3 controladores de tenant application (Fase 5 / 5.13, hijo 3)"
estado: Implementado
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.13) — DT-A1, DT-C4
cobertura: Migración de Application a app/Services/ApplicationService.php unificando Api/TenantApplicationController (315), Landlord/ApplicationController (212) y Api/LandlordApplicationController (130)
---

# Spec — Service Application (Fase 5 / 5.13, hijo 3)

> Unificar los 3 controladores de application en `ApplicationService`. `agreement_sign` deja su copia del bloque service-charge y llama a `RentService::createWithDeposit` (hijo 2). Los quirks observables de `file_download` se preservan y caracterizan.

**Patrón de referencia:** `spec-unificar-capas-services-fase4.md` §8. **Paraguas:** `spec-unificar-capas-services-fase5.md`.

## 1. Objetivo

`Api/TenantApplicationController` (315), `Landlord/ApplicationController` (212) y `Api/LandlordApplicationController` (130) quedan < 200, con las 3 copias casi exactas de processing/approved/rejected unificadas en el service y `agreement_sign` consumiendo `RentService::createWithDeposit`.

## 2. Alcance

### IN
- `app/Services/ApplicationService.php`: `listForChannel`, `loadForShow`, `store`, `agreementSign`, `processing`, `approve`, `reject`, `fileDownload`.
- Refactor in-place de los 3 controladores (Api tenant → Api landlord → Landlord web).
- Tests de caracterización HTTP previos + unit tests. `IdorPolicySmokeTest` f7–f9 como red.

### OUT
- Fix del quirk de `file_download` (preservado + caracterizado; follow-up fuera del spec).
- Rutas: todas se preservan (`application/sign/{id}`, `tenant-applications/*`, `application/{processing,approved,rejected,file}` en ambas capas, `applications-excel/csv`).

### Cobertura objetivo
- 3 controladores < 200; 0 duplicación processing/approve/reject; suite + PHPStan + Pint en verde.

## 3. Pre-requisitos

| # | Pre-requisito | Estado | Acción |
|---|---|---|---|
| P1 | Hijo 2 (rent) mergeado — `RentService::createWithDeposit` | Pendiente | Dependencia dura |
| P2 | Suite verde | 371 casos | `php artisan test` |
| P3 | Policies process/approve/reject/fileDownload | Existen (f7–f9) | Reutilizar sin cambios |

## 4. Estructura propuesta

```
app/Services/ApplicationService.php
app/Http/Requests/Application/StoreApplicationRequest.php
tests/Unit/ApplicationServiceTest.php
tests/Feature/Application/TenantApplicationHttpTest.php    # caracterización previa
tests/Feature/Application/LandlordApplicationHttpTest.php   # web + api landlord
```

### Contrato del service

```php
class ApplicationService
{
    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator;
    public function loadForShow(Application $application, Channel $channel): Application;
    public function store(array $data, ?User $tenant): Application;             // Api tenant (sin tx, 1 escritura — actual)
    public function agreementSign(Application $application, ?UploadedFile $tenantAgreement): Application;
    //   ↑ ramas por category_id 1–8 actuales; crea SecurityDeposit+Rent vía RentService::createWithDeposit
    public function processing(Application $application, ?UploadedFile $file, Channel $channel): Application; // upload + status 1
    public function approve(Application $application, Channel $channel): Application;  // deposit paid → status 2 + rent activo
    public function reject(Application $application, Channel $channel): Application;   // tenant_agreement guard + status 3
    public function fileDownload(Application $application, Channel $channel, ?string $type): string;
}
```

### Reglas por canal (divergencias actuales — preservadas tal cual)

| Aspecto | Landlord web | Api landlord | Api tenant |
|---|---|---|---|
| processing/approve/reject | sin notif | **+ `sendNotification`** | — |
| reject | orden de checks A | orden de checks B + 409 | — |
| file_download | **ignora `$type`**, sirve `landlord_agreement` | sirve `$application->file` (atributo distinto) | — |
| agreement | — | — | `agreement_sign` (tx + `sendNotification` post-commit) |

## 5. Decisiones técnicas

- **Quirk `file_download` preservado (grill Q11a):** el service recibe `$channel` y `$type` y reproduce el comportamiento actual por canal (web ignora `$type`; api usa `->file`). Se documenta como bug conocido; NO se unifica aquí. Casos F10/F11 lo congelan.
- `agreementSign` elimina la 3ª copia del bloque service-charge: llama `RentService::createWithDeposit`; las ramas por `category_id` 1–8 se preservan.
- Orden de migración: Api tenant → Api landlord → Landlord web.
- `Landlord/ApplicationController` deja de usar `HasTableActions`; `filter`/`exportExcel`/`exportCsv` (rutas `application.filter`, `applications.excel/csv`) se reimplementan delegando al service — cero rutas muertas.
- `store` de Api tenant sustituye `$request->except(...)` por Form Request validado (regla del patrón; sin cambio observable).
- Policies vía `authorize()` antes de delegar; `sendNotification` post-commit según canal (dentro del service).

## 6. Catálogo de casos (prefijo `F` HTTP, `U` unit — hoy cero tests HTTP de application)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Store tenant api | Sanctum tenant, propiedad con application abierta | POST /api/v1/tenant/application | 201/200 JSON; fila creada (payload actual) |
| F2 | Show tenant api | Sanctum tenant, application propia | GET /api/v1/tenant/application/{id} | 200 JSON shape actual |
| F3 | Agreement sign (rama category_id=1) | Sanctum tenant, application aprobada | POST /api/v1/tenant/application/sign/{id} + tenant_agreement | 200; SecurityDeposit + Rent creados vía RentService; cálculo idéntico al actual |
| F4 | Agreement sign ramas 2–8 | ídem con cada category | ídem | misma relación/rama actual (congelar tabla de ramas) |
| F5 | Processing web | landlord A | POST /landlord/application/processing/{id} + file | 200; upload + status=1; sin notificación |
| F6 | Processing api | Sanctum landlord | POST /api/v1/landlord/application/processing/{id} | 200; idem + `sendNotification` |
| F7 | Approve web/api | landlord, application processing | POST .../application/approved/{id} | 200; deposit paid → status=2; rent activo (web sin notif, api con) |
| F8 | Reject web (orden A) | landlord | POST /landlord/application/rejected/{id} | 200/redirect actual; tenant_agreement guard; status=3 |
| F9 | Reject api (orden B, 409) | Sanctum landlord | POST /api/v1/landlord/application/rejected/{id} | código actual (409 cuando aplica); orden de checks preservado |
| F10 | **Quirk file_download web** | landlord, application con landlord_agreement | GET /landlord/application/file/{id}/{type} con type=NCS | sirve `landlord_agreement` (ignora `$type`) — congelado |
| F11 | **Quirk file_download api** | Sanctum landlord | GET /api/v1/landlord/application/file/{id} | sirve `$application->file` — congelado |
| F12 | Filter/excel/csv web | landlord A, 2 applications | POST application/filter, GET applications-excel/csv | rutas vivas tras migración |

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | agreementSign delega en RentService | stub RentService | `agreementSign(...)` | `createWithDeposit` invocado 1×; 0 copia local del cálculo |
| U2 | approve por canal | application processing | `approve(app, Api)` vs `approve(app, Landlord)` | notif solo Api |
| U3 | fileDownload por canal | application con ambos archivos | `fileDownload(app, Landlord, 'ncs')` / `fileDownload(app, Api, null)` | web → landlord_agreement; api → file |

> F10/F11 son **caracterizaciones de bug conocido** (quirk `$type`/atributo divergente): `@group characterization`, se espera que sigan pasando; se romperán solo en un follow-up futuro fuera de 5.13.

| ID | Bug caracterizado | Fase que lo romperá | Catálogo |
|----|-------------------|---------------------|----------|
| F10 | file_download web ignora `$type` y sirve siempre `landlord_agreement` | follow-up file_download (fuera de 5.13) | `tests/Feature/Application/LandlordApplicationHttpTest.php::test_f10` |
| F11 | file_download api lee `$application->file`, atributo inexistente → null siempre | follow-up file_download (fuera de 5.13) | `tests/Feature/Application/LandlordApplicationHttpTest.php::test_f11` |

## 7. Criterios de aceptación (Definition of Done)

- [x] `ApplicationService` en verde (U1–U3); `agreementSign` sin copia local del bloque service-charge (grep en service = 0 llamadas a `Option 'service-charge'` fuera de RentService). **Evidencia:** `grep -rn "service-charge" app/` → única lectura `Option::where` en `app/Services/RentService.php:364` (`calculatedCharge`, extraída de `createWithDeposit`) + usos admin legítimos (`Admin/ServiceChargeController`, `Admin/RentPaymentGenerateController`, `Console/RentPaymentGenerate`); `ApplicationService` solo lo menciona en docblock.
- [x] Caracterización F1–F12 en verde antes de migrar; sigue en verde después (incluidos F10/F11 tal cual). **Evidencia:** 18 passed (239 assertions) pre-migración (commit `bae0ff2`); 21 passed (253 assertions) post-migración — F1–F12 + F13 de refuerzo + U1–U3; `php artisan test --group=characterization` en verde.
- [x] 3 controladores < 200 líneas, `Landlord/ApplicationController` sin `HasTableActions`. **Evidencia:** Api tenant 315→80, Api landlord 130→94, Landlord web 212→140 (`wc -l`); única mención de `HasTableActions` = docblock de procedencia en `filter()` (sin `use`).
- [x] Rutas `application.filter`/`applications.excel/csv` vivas. **Evidencia:** `php artisan route:list` → `landlord.application.filter`, `landlord.applications.excel`, `landlord.applications.csv`, `landlord.application.{processing,approved,rejected,file-download}`, `apitenant-application.sign` y `apiapplication.{file,processing,approved,rejected}`.
- [x] Suite + PHPStan + Pint en verde. **Evidencia:** suite 442 passed (5174 assertions) — 421 base + 21 nuevos; `composer analyse` → `[OK] No errors` (baseline −20 entradas unmatched por relaciones tipadas + 1 entrada nueva para el quirk `$file`, ver §11 D5); `composer lint` → passed.

## 8. Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Ramas category_id 1–8 de agreement_sign mal portadas | F4 con tabla de ramas congelada antes de migrar |
| Divergencia de orden de checks en reject web/api | F8/F9 por separado; service con `match($channel)` |
| Unificar file_download "por accidente" | F10/F11 + U3 congelan el quirk; revisión de PR atenta |

## 9. Estimación

- 5–7 días (caracterización 2, service 1.5, migración 3 controladores 1.5, validación 0.5–1).

## 10. Referencias

- `spec-service-rent-fase5.md` (`createWithDeposit`) · `spec-unificar-capas-services-fase5.md`
- `.analisis/plan/plan-accion.md` → Fase 5 / 5.13 · `tests/Feature/Idor/IdorPolicySmokeTest.php` (f7–f9)

## 11. Desviaciones documentadas y cierre

Pre-requisitos verificados (2026-11-22): P1 satisfecho sobre la rama del hijo 2 (`spec/service-rent-fase5-impl`, pendiente de merge a main — dependencia dura resuelta en rama); P2 suite 421 passed pre-trabajo; P3 policies f7–f9 reutilizados intactos (verde tras la migración).

**D1 — `createWithAgreement` en vez de `createWithDeposit` literal (decisión del decisor):** el spec §5/U1 pedían "llama a `RentService::createWithDeposit`… invocado 1×", pero F3/F4 exigen cálculo idéntico al actual y las ramas 4/5/7/8 son incompatibles con `createWithDeposit` (deposit sin utility, `rent.utility_bill` NULL explícito, notificación de agreement vs rent-invitation, sin min-rental check). Contradicción interna resuelta con pregunta de diseño al decisor: nuevo `RentService::createWithAgreement($rentAttributes, $depositAttributes, $chargeBaseAmount)` que comparte con `createWithDeposit` la lectura del Option (`calculatedCharge` extraída — única lectura fuera de admin, DoD §7 cumple) y no abre tx propia (la abre el caller para envolver también el update del application, comportamiento actual). U1 re-dirigido al nuevo método (stub Mockery, invocado 1× con el plan de rama congelado). El contrato §4 queda: `agreementSign` resuelve ramas 1–8 → delega charge+creación a RentService.

**D2 — granularidad del catálogo:** F1 se implementó como f1a/f1b/f1c (happy/duplicado 409/min-rental 422), F7 como f7a–f7d (happy web/api, unpaid divergente, agreement-missing web 404 vs api 200) y se añadió F13 (index api brancha por rol — refuerzo de red, mismo espíritu que F11 del hijo 2). Cobertura objetivo intacta.

**D3 — `StoreApplicationRequest` valida `unit_id` (`nullable|integer`):** antes se leía sin validar; input no-numérico producía 404 (findOrFail) o 500 (FK) según el punto del flujo, ahora 422. Misma clase de desviación que hijo 2 §11 (payloads crafted).

**D4 — categoría fuera de 1–8 en agreement_sign:** antes crash por null-dereference (Error no capturado → 500 con página del handler); ahora `RuntimeException('Agreement sign failed')` → 500 con el JSON del catch (mismo código, cuerpo unificado con el resto de fallos del endpoint). La tx se deshace en ambos casos.

**D5 — baseline PHPStan:** −20 entradas unmatched (raíz corregida: relaciones del modelo `Application` tipadas + genéricas `@return BelongsTo<X, $this>`; también limpia `ApplicationPolicy` sin tocarlo) + 1 entrada nueva para `Application::$file` en `ApplicationService` (quirk intencional congelado por F11/U3; se elimina en el follow-up del fix). No se regeneró el baseline; edición quirúrgica por path con las entradas re-matcheadas de Maintenance/Dashboard conservadas.

**D6 — `fileDownload` devuelve `?string`** (el contrato §4 decía `string`): el null representa el 'File not found' actual de ambos canales; los controllers mapean null → JSON 200. Comportamiento observable idéntico.

Observaciones empíricas congeladas durante la caracterización: `where('unit_id', null)` compila como whereNull (approve funciona para applications sin unidad); `security_deposits.utility_advance` tiene default DB 0 (cats 4–8) mientras `rents.utility_bill` queda NULL (insertado explícito desde el atributo ausente del modelo en memoria) — asimetría congelada en la tabla de ramas de F4.

**Estado:** `Aprobado` → `Implementado` (DoD §7 verificado con evidencia; suite 442 passed + PHPStan OK + Pint passed).
