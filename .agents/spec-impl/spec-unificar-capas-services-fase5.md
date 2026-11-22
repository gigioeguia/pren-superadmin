---
id: spec-unificar-capas-services-fase5
titulo: "Unificar capas restantes con Services hasta controladores < 200 líneas (Fase 5 / 5.13)"
estado: Borrador
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.13) — DT-A1, DT-C4
cobertura: Coordinación de la migración de las entidades no migradas (payments, landlords, rent, application, language) + 12 controladores > 200 líneas + arch-test de cumplimiento permanente
---

# Spec — Unificar capas restantes con Services (Fase 5 / 5.13)

> Coordinar la migración a `app/Services` de las entidades restantes hasta que **ningún controlador supere 200 líneas**, con arch-test que lo haga permanente. Spec paraguas: define patrón, orden, naming y el item de rutas muertas; la implementación vive en 6 specs hijos.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.13, desvío del criterio de salida de Fase 4, ver cierre 2026-11-21). **Patrón de referencia:** `.agents/spec-impl/spec-unificar-capas-services-fase4.md` §8.

---

## 1. Objetivo

Cerrar el desvío del criterio de salida de Fase 4: los 12 controladores que hoy superan 200 líneas quedan < 200, la lógica de negocio de las entidades restantes vive en `app/Services`, y un arch-test impide reintroducir controladores gordos o transacciones en controladores.

## 2. Alcance

### IN — Los 12 controladores > 200 líneas y su destino

| Controlador | Líneas hoy | Hijo | Service |
|---|---|---|---|
| `PaymentController` (web raíz) | 464 | payment | `PaymentService` + `SubscriptionService` |
| `Admin/LandlordController` | 425 | landlord | `LandlordService` |
| `Auth/SignupController` | 251 | landlord | `LandlordService::createAccount` |
| `Api/Auth/RegistrationController` | 221 | landlord | idem |
| `Landlord/RentInvitationController` | 365 | rent | `RentService` |
| `Api/RentController` | 290 | rent | idem |
| `Api/TenantApplicationController` | 315 | application | `ApplicationService` |
| `Landlord/ApplicationController` | 212 | application | idem |
| `Admin/LanguageController` | 264 | language | `LanguageService` |
| `Api/LandlordDashboardController` | 228 | residuales | `DashboardService` |
| `Admin/PropertyController` | 225 | residuales | 2ª pasada (`PropertyService`) |
| `Landlord/PropertyController` | 200 | residuales | 2ª pasada (recorte a <200 estricto) |
| `Controller.php` (base) | 235 | residuales | trait `RespondsJson` |

- Item único de limpieza de **rutas muertas** de Fase 4 (§6, vive en este paraguas).
- Arch-test `ControllersLineLimitArchTest` (reglas en §5, activación en el hijo residuales).

### OUT

- `Admin/TranslationController` (166, sin duplicación, < 200).
- Dashboards admin/landlord web (129/159) pese a duplicar listing.
- Duplicación residual de withdraw, maintenance, labor, blog/comment (deuda lateral, no reintroducir regla alguna).
- Los 43 sitios `except()` no-`User` (fila 5.13-adjacente, audit-pending).
- Fix del quirk de `file_download` de application (se preserva y caracteriza; follow-up fuera).
- Cambios de contrato observable (rutas, vistas, JSON shapes, códigos HTTP).

### Cobertura objetivo

- 12/12 controladores < 200 líneas (`wc -l` como evidencia).
- 0 duplicación de lógica de negocio entre capas para las 5 entidades migradas.
- Suite completa en verde + tests de caracterización nuevos por entidad.
- Arch-test en CI enforces ambas reglas permanentemente.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Suite verde | 371 casos en verde | `php artisan test` antes de empezar; verde tras cada paso |
| P2 | Patrón Fase 4 | Implementado (`spec-unificar-capas-services-fase4` §8) | Los hijos heredan Service + Channel + Form Requests + excepciones |
| P3 | PHPStan/Pint/CI | Configurados | Red; baseline congelado, sin regenerar |
| P4 | Skills | Disponibles | `tests-caracterizacion` antes de migrar cada controlador; `workflow-ramas` para cada rama `spec/<id>-impl` |

## 4. Estructura

```
.agents/spec-impl/
├── spec-unificar-capas-services-fase5.md          # este paraguas: patrón, orden, naming, rutas muertas
├── spec-service-language-fase5.md                 # hijo 1
├── spec-service-rent-fase5.md                     # hijo 2
├── spec-service-application-fase5.md              # hijo 3
├── spec-service-landlord-fase5.md                 # hijo 4
├── spec-service-payment-fase5.md                  # hijo 5
└── spec-controladores-residuales-fase5.md        # hijo 6 (2ª pasada Property, Dashboard, trait base, arch-test)
```

**Orden de implementación (dependencias):** language → rent → application → landlord → payment → residuales + arch-test. Dura: `ApplicationService` depende de `RentService::createWithDeposit` (hijo 3 tras hijo 2). Payments (dinero, 11 gateways, idempotencia) va último, con el patrón ya rodado. Language es el piloto barato (una capa, sin dinero).

**Naming:** `PaymentService`, `SubscriptionService`, `LandlordService` (+ `createAccount`), `RentService`, `ApplicationService`, `LanguageService`, `DashboardService`, trait `app/Traits/RespondsJson.php`.

## 5. Decisiones técnicas (fijadas en grill 2026-11-21)

1. **Umbral estricto `< 200`** (≤ 199): fiel al texto del plan; recortes triviales donde aplique.
2. **Arch-test** `tests/Unit/Controllers/ControllersLineLimitArchTest.php` (estilo `SeedersArchTest`), sin grandfather list, dos reglas globales:
   - Todo `.php` bajo `app/Http/Controllers/` < 200 líneas.
   - Cero `DB::beginTransaction` / `DB::transaction` en `app/Http/Controllers/` (los 8 controladores con tx hoy están todos en scope; la regla nace limpia).
3. **Patrón heredado de Fase 4 sin renegociar:** Service agnóstico de HTTP (no lee `auth()`/`request()`), `Channel` enum, Form Requests compartidos, excepciones de dominio, Api → Landlord → Admin dentro de cada entidad, tx solo escrituras con orden 4.8 (uploads antes de abrir tx; Mail/`sendNotification`/borrado de archivos después del commit, best-effort), contrato HTTP idéntico.
4. **Cero rutas muertas nuevas:** toda ruta existente de una entidad migrada se preserva funcional (delegando al service) o se elimina explícitamente en el mismo PR con decisión documentada. No se repite el desvío de 4.2.
5. **Quirks observables preservados:** el quirk de `file_download` (application) se caracteriza tal cual y se documenta como bug conocido; el orden interno de E/S de `LanguageController::deleteAll` SÍ se fixea (capturar rutas → borrar filas → borrar archivos post-commit) porque no es observable por HTTP — desviación menor documentada en el hijo.
6. **`File::` sobre `lang/*.json` se mueve tal cual** al service (sin migrar a disk de `Storage`).
7. **Tests de caracterización HTTP obligatorios por entidad, antes de tocar el controlador** (hoy hay cero para language, rent, application).
8. Baseline PHPStan congelado; errores nuevos se corrigen; Pint en verde.

## 6. Catálogo de casos — rutas muertas de Fase 4 (item del paraguas)

Rutas que apuntan a métodos eliminados en 4.2 (`BadMethodCallException` latente). Previo grep de `route()`/`to_route()`/BLADE para confirmar que ninguna vista/JS referencia los route names; si alguna lo hace, desviación documentada y se decide en el PR.

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | Rutas muertas admin | `routes/admin.php` L37–42, L97–102 | invocar `tenants/filter`, `tenants.otp-submit`, `tenants.otp-resend`, `tenants.excel`, `tenants.csv`, `rent-payments/filter`, `rent-payments.excel`, `rent-payments/csv` | las 8 rutas eliminadas de `routes/admin.php`; 0 referencias en vistas/JS |
| U2 | Rutas muertas landlord | `routes/landlord.php` L68–69 (`rent-payments-excel/csv`; `filter` NO está muerta: existe `Landlord/RentPaymentController::filter` L30) | idem | 2 rutas eliminadas; `rent-payments/filter` preservada |
| U3 | Middleware huérfano | `Admin/TenantController` L20 `->only('filter','exportExcel','exportCsv')` | leer el constructor | referencias a métodos inexistentes limpiadas |

Métodos de verificación: extender `tests/Feature/Routes/RouteNamesSmokeTest.php` (o un test dedicado) que asserts que todo route name declarado apunta a un método existente (reflexión), de forma permanente — cierre estructural, no puntual.

## 7. Criterios de aceptación (Definition of Done)

- [ ] Los 6 specs hijos creados, revisados (`revisar-spec`) y aprobados.
- [ ] Rutas muertas (U1–U3) eliminadas con evidencia de grep y test de reflexión en verde.
- [ ] 12/12 controladores < 200 líneas (`wc -l` pegado como evidencia en cada hijo).
- [ ] `ControllersLineLimitArchTest` activo en CI con las 2 reglas de §5.2.
- [ ] Suite completa en verde; PHPStan sin nuevas entradas de baseline; Pint en verde.
- [ ] `plan-accion.md` ítem 5.13 marcado cerrado con evidencia.

## 8. Riesgos

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Payments es el slice más duro (idempotencia, `Cache::lock`, 11 gateways) | alto | Va último; tests existentes (`PaymentFlowSmokeTest`, `BalanceForceFillTest`, `CachePaymentInvalidationTest`, `GatewayNamespaceWhitelistTest`) como red + caracterización nueva |
| Scope creep hacia dashboards/translation | medio | OUT explícito §2; arch-test solo vigila controladores, no services |
| Segundo desvío de rutas muertas al migrar | medio | Decisión técnica §5.4 + test de reflexión U permanente |
| Quirk de `file_download` "arreglado" por accidente | medio | Caso de caracterización explícito en hijo application que congela el comportamiento actual |
| Estimación total ~30–45 d | bajo | Un PR por hijo, reviewable de forma aislada; el paraguas no bloquea a los hijos |

## 9. Estimación

| Hijo | Días |
|---|---|
| language | 3–4 |
| rent | 5–7 |
| application | 5–7 |
| landlord | 6–8 |
| payment | 8–12 |
| residuales + arch-test | 3–5 |
| **Total** | **~30–45** |

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.13, cierre Fase 4 (2026-11-21)
- `.analisis/deuda-tecnica.md` → DT-A1 (3 capas paralelas), DT-C4 (controladores gigantes)
- `.agents/spec-impl/spec-unificar-capas-services-fase4.md` → patrón §8
- `.agents/spec/spec-conventions.md`, `.agents/spec/spec-template.md`
- Skills: `tests-caracterizacion`, `workflow-ramas`, `revisar-spec`
