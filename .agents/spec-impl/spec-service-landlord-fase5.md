---
id: spec-service-landlord-fase5
titulo: "Service Landlord — unificar alta de landlord entre Admin, Signup web y Api Registration (Fase 5 / 5.13, hijo 4)"
estado: Borrador
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.13) — DT-A1, DT-C4
cobertura: Migración de Admin/LandlordController (425) a app/Services/LandlordService.php y unificación del flujo de alta de cuenta de landlord triplicado (SignupController 251, Api/Auth/RegistrationController 221)
---

# Spec — Service Landlord (Fase 5 / 5.13, hijo 4)

> `Admin/LandlordController` migra a `LandlordService` y el flujo "crear cuenta landlord" (User + UserDetail + PlanSubscribe free) triplicado entre admin, signup web y registration api queda en un único `LandlordService::createAccount`. Los tres controladores quedan < 200 líneas.

**Patrón de referencia:** `spec-unificar-capas-services-fase4.md` §8. **Paraguas:** `spec-unificar-capas-services-fase5.md`.

## 1. Objetivo

`Admin/LandlordController` (425), `Auth/SignupController` (251) y `Api/Auth/RegistrationController` (221) quedan < 200, con el alta de cuenta landlord unificada y el CRUD admin de landlords en el service.

## 2. Alcance

### IN
- `app/Services/LandlordService.php`: `list`, `store` (admin CRUD), `update`, `delete`, `deleteMany`, `setStatus` + **`createAccount`** (flujo compartido admin/signup/registration).
- Refactor in-place de los 3 controladores.
- Tests de caracterización HTTP previos; `LandlordStoreSubscriptionTest` (G6–G7), `SubscriptionPaidTest` (G8–G9) y reglas Seg-28 (spec 5.11) como red de regresión.

### OUT
- Flujos OTP de signup/registration (viven en `App\Support\Otp` desde 4.6; los controladores los orquestan y siguen haciéndolo).
- `Admin/UserController` (183) y el resto de auth (login/reset).
- Rutas `landlords/*` y de signup/registration: intactas.

### Cobertura objetivo
- 3 controladores < 200; una sola implementación del trío free-plan; suite + PHPStan + Pint en verde.

## 3. Pre-requisitos

| # | Pre-requisito | Estado | Acción |
|---|---|---|---|
| P1 | Hijo 3 (application) mergeado | Pendiente | Orden del paraguas |
| P2 | Spec 5.11 (`User` $fillable restrictivo) | Implementado | Escrituras `User` vía arrays explícitos + `forceFill` server-side — obligatorio en el service |
| P3 | DemoMode + throttle en signup | Implementados | Se preservan |
| P4 | Suite verde | 371 casos | `php artisan test` |

## 4. Estructura propuesta

```
app/Services/LandlordService.php
app/Http/Requests/Landlord/StoreLandlordRequest.php     # admin CRUD
app/Http/Requests/Landlord/CreateAccountRequest.php    # compartido signup/registration (ramas por canal)
tests/Unit/LandlordServiceTest.php
tests/Feature/Landlord/AdminLandlordHttpTest.php       # caracterización previa
tests/Feature/Auth/SignupAccountHttpTest.php           # caracterización previa (web)
tests/Feature/Auth/RegistrationAccountHttpTest.php     # caracterización previa (api)
```

### Contrato del service

```php
class LandlordService
{
    public function list(array $filters = []): LengthAwarePaginator;                 // solo Admin
    public function store(array $data, ?UploadedFile $image = null): User;            // admin CRUD (tx, role server-side)
    public function update(User $landlord, array $data, ?UploadedFile $image = null): User;
    public function delete(User $landlord): void;                                     // media post-commit
    public function deleteMany(array $ids): int;                                      // tx + captura de media
    public function setStatus(User $landlord, int $status): User;
    public function createAccount(array $data, Channel $channel, ?UploadedFile $image = null): User;
    //   ↑ única fuente: User::create (array explícito) + role server-side + forceFill(landlord_id/plan)
    //     + UserDetail::create + PlanSubscribe free (trío 5.11) — tx; OTP/WelcomeMail/sendNotification post-commit
}
```

### Divergencias actuales entre los 3 flujos — preservadas tal cual

| Aspecto | `Admin/LandlordController::store` | `SignupController` | `Api/RegistrationController` |
|---|---|---|---|
| Cómo llega al alta | CRUD admin directo | signup público web + OTP | registration api + OTP |
| `role` | asignación server-side explícita | idem (Seg-28: crafted keys bloqueadas) | idem (spec fase1: role arbitrario bloqueado) |
| OTP/`WelcomeMail` | no | sí (post-commit) | sí (post-commit) |
| Plan free | `PlanSubscribe` trío forceFill | idem | idem |

## 5. Decisiones técnicas

- **`createAccount` compartido (grill Q4):** signup y registration delegan; sus controladores retienen solo validación + OTP-orchestration + respuesta. `Admin/LandlordController::store` usa `createAccount(Channel::Admin)` o `store()` según corresponda al comportamiento actual (caracterizar primero; no unificar a la fuerza lo que hoy difiere).
- Escrituras `User` con arrays explícitos de claves; `role`/`landlord_id`/trío free-plan **server-side** (`forceFill`/asignación), jamás del request (spec 5.11 + Seg-28).
- Tx 4.8: uploads antes; `WelcomeMail`/`sendNotification`/borrado de media post-commit best-effort.
- `deleteMany` actual hace tx + captura de media en un override del trait — el service lo absorbe con el patrón captura-antes/borra-después.
- `Admin/LandlordController` deja de usar `HasTableActions`; `filter`/`deleteAll`(→`deleteMany`)/`exportExcel`/`exportCsv` (rutas `landlords.filter|delete-all|excel|csv`) se reimplementan delegando al service — cero rutas muertas.
- `file_exists()` sobre rutas de storage (L298/L355 actuales) se sustituye por `Storage::exists()` — interno, no observable.

## 6. Catálogo de casos (prefijo `F` HTTP, `U` unit)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Admin store crea landlord + plan free | admin, plan free existe | POST /admin/landlords | 200; User+UserDetail+PlanSubscribe trío; role server-side (regresión G6–G7) |
| F2 | Admin store bloquea crafted keys | payload con `plan_subscribe_id`/`role`/`will_expire` | POST /admin/landlords | asignaciones server-side prevalecen (Seg-28, red existente) |
| F3 | Admin update | admin, landlord B | PUT /admin/landlords/{id} (+imagen) | 200; imagen vieja borrada post-commit |
| F4 | Admin destroy | admin, landlord con media | DELETE /admin/landlords/{id} | 200; media eliminada post-commit (comportamiento actual) |
| F5 | Admin deleteAll | admin, 2 landlords | POST /admin/landlords/delete-all | 200; tx + media capturada antes/borrada después |
| F6 | Filter/excel/csv | admin, 2 landlords | POST landlords/filter, GET landlords-excel/csv | rutas vivas tras migración |
| F7 | Signup web | visitor, plan free | POST /signup (flujo actual con OTP) | trío free-plan creado; OTP/WelcomeMail post-commit; sesión/respuesta actuales |
| F8 | Registration api | visitor | POST /api/sign-up (flujo actual con OTP) | ídem; JSON shape actual; crafted keys bloqueadas |
| F9 | DemoMode | APP_ENV=demo | POST /signup | bloqueado (red existente) |

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | createAccount trío free | plan free | `createAccount(data, Landlord)` | User+UserDetail+PlanSubscribe; role landlord server-side |
| U2 | createAccount ignora crafted | payload con `role=admin` | idem | role del payload ignorado (forceFill) |
| U3 | deleteMany captura media | 2 landlords con imagen | `deleteMany(ids)` | filas borradas en tx; media borrada post-commit |

> F1–F9 caracterizan el comportamiento actual (incl. Seg-28 y DemoMode, ya cubiertos por tests existentes — reusar/extender, no duplicar).

## 7. Criterios de aceptación (Definition of Done)

- [ ] `LandlordService` en verde (U1–U3); una sola implementación del alta (grep del trío free-plan fuera del service = 0).
- [ ] Caracterización F1–F9 en verde antes de migrar; G6–G9 y Seg-28 siguen en verde.
- [ ] 3 controladores < 200 líneas, sin `HasTableActions`, sin `file_exists()` sobre storage.
- [ ] Rutas `landlords.filter|delete-all|excel|csv` vivas.
- [ ] Suite + PHPStan + Pint en verde.

## 8. Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Signup/registration son flujos de **auth públicos** — regresión de seguridad | Red existente (Seg-28, throttle, DemoMode, smoke auth) + caracterización previa; no tocar middleware |
| Divergencias sutiles entre los 3 flujos (mensajes, orden) | Caracterizar los 3 por separado ANTES; unificar solo lo que caracterizaciones idénticas respalden |
| Trío free-plan con divergencia | U1 + G6–G9 existentes |

## 9. Estimación

- 6–8 días (caracterización 2–2.5, service+forms 2, migración 3 controladores 1.5–2, validación 0.5–1).

## 10. Referencias

- `spec-unificar-capas-services-fase5.md` · `spec-user-fillable-restrictivo-fase5.md` (escrituras User)
- `.analisis/plan/plan-accion.md` → Fase 5 / 5.13 · `tests/Feature/Admin/LandlordStoreSubscriptionTest.php` (G6–G7)
