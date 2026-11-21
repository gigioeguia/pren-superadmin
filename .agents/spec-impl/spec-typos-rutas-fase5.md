---
id: spec-typos-rutas-fase5
titulo: "Typos y duplicados en rutas: `read-nitifications`, nombres muertos en DemoMode, colisión `apimaintenance.index` y tidy de notifications (Fase 5 / 5.8)"
estado: Implementado
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.8) — .analisis/seguridad.md (Seg-25, l.143) — .analisis/deuda-tecnica.md (DT-B2, l.98-99)
cobertura: Cerrar Seg-25/DT-B2 con el alcance real del inventario route:list (741 rutas, 2026-11-21): hard rename `read-nitifications`→`read-notifications` (0 call sites internos; residual = consumidor móvil hipotético), dedupe de `income-categories/delete-all`, 4 nombres muertos en DemoMode (transacciones y suscripciones hoy NO se bloquean en demo — hallazgo funcional no documentado), colisión `apimaintenance.index` landlord/tenant, naming aditivo de 36 rutas (34×`api` + 2×`admin.`) con convención intacta, borrado de `NotifyContrller` muerto, tidy de la familia notifications, y errata del DoD falso de spec-service-notification-fase4. Catálogo F9–F18 + G1–G5 + H1–H5 + A1–A4 (24 casos; suite esperada 301+24=325) + guards anti-reincidencia F13/F14/F18.
---

# Spec — Typos y duplicados en rutas (Fase 5 / 5.8)

> Resumen de una línea: reparar los typos y duplicados de rutas/nombres documentados (Seg-25/DT-B2) más los 4 hallazgos funcionales del inventario (nombres DemoMode muertos que dejan de bloquear, colisión silenciosa, 36 rutas con nombre de prefijo desnudo, controlador muerto), sellando la clase entera con guards anti-reincidencia — el "lint" que DT-B2 reclama.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.8, l.113) — `.analisis/seguridad.md` (Seg-25, l.143: *"`read-nitifications` (typo), rutas duplicadas (`income-categories/delete-all` registrada dos veces en `admin.php`)"*) — `.analisis/deuda-tecnica.md` (DT-B2, l.98-99: *"`read-nitifications`, doble registro de `income-categories/delete-all`, `admin..transactions.update` (doble punto) en DemoMode. Indican falta de lint/revisión."*).

---

## 1. Objetivo

Cerrar Seg-25/DT-B2 en su alcance real: no solo los 3 typos documentados, sino los 4 nombres muertos de `DemoMode` (hoy transacciones y suscripciones se mutan sin bloqueo en demo), la colisión `apimaintenance.index` (override silencioso del landlord por el tenant), 36 rutas cuyo "nombre" es el prefijo de grupo desnudo y un controlador muerto typo'd — todo con tests anti-regresión y 3 guards mecánicos (existencia de nombres DemoMode, unicidad de nombres, higiene de fuente) que convierten la clase de bug en no-reincidente.

---

## 2. Alcance

### IN

1. **Rename** `read-nitifications` → `read-notifications` + `->name('read-notifications')` (`routes/api.php:93`). Hard rename sin alias (Q2): 0 call sites internos (grep 2026-11-21: sin usos en `app/`, `resources/`, `tests/`, `lang/`, docs; sin colección Postman/repo móvil). Residual documentado: consumidor externo hipotético migra en su próximo release. Verbo GET intacto (Q3).
2. **Dedupe** `income-categories/delete-all` (`routes/admin.php:184`, byte-idéntica a :182). Runtime: Laravel colapsa method+URI repetidos — impacto cero, higiene de fuente.
3. **DemoMode, 4 nombres muertos corregidos** (`app/Http/Middleware/DemoMode.php`):
   - :104-105 `admin..transactions.update/destroy` → `admin.transactions.*` (nombres reales: resource `routes/admin.php:45`);
   - :96-97 `admin.subscription-reports.paid/reject` → `admin.subscriptions.paid/reject` (reales: `routes/admin.php:136-137`).
   - Impacto funcional hoy: en demo NO se bloquean transacciones ni suscripciones (hallazgo del inventario, no documentado en Seg-25/DT-B2).
4. **Tidy menor del mismo archivo**: entrada duplicada `admin.landlords.delete-all` (:66 ≡ :69) fuera; las dos listas extraídas a `public const ADMIN_DISABLED_ROUTES` / `LANDLORD_DISABLED_ROUTES` (mecánica de Q10: iterables por el guard; `handle()` las consume por constante, comportamiento idéntico).
5. **Borrado** `app/Http/Controllers/Admin/NotifyContrller.php` (Q4): clase muerta sin rutas (`grep -rn "Notify" routes/` → vacío), typo en el nombre, duplicado pre-Fase-4 del vivo `Admin\NotificationController` (mismos `mtIndex`/`mtView`/`mtReadAll`, lógica inline legacy). Requiere: retirar las 2 entradas huérfanas de `phpstan-baseline.neon` (:368, :374) — entrada por clase borrada, no regeneración de baseline (regla AGENTS.md, diff justificado en el PR) — y anotar la fila en `spec-permission-middleware-admin-fase3.md:162`.
6. **Naming aditivo** (Q9=A): `->name()` explícito a las 34 rutas de `routes/api.php` y 2 de `routes/admin.php` que hoy heredan el prefijo de grupo como nombre completo (`api`, `admin.`); **cero nombres existentes cambian**; convención `api` sin punto intacta (AGENTS.md). Colisión `apimaintenance.index` (`routes/api.php:48` landlord ↔ :77 tenant, el tenant pisa al landlord) resuelta con `->names(['index' => 'landlord-maintenance.index'])` / `'tenant-maintenance.index'` — **solo el index**; `apimaintenance.store/show/update` intactos (`DemoMode:140` referencia `apimaintenance.update`).
7. **Tidy familia notifications** (Q6 IN): `notification/filter/` → `filter` y `view/all/` → `view/all` (`routes/admin.php:271-272`, `routes/landlord.php:140-141`). Las vistas usan nombres (`admin.notifications.filter`, `*.mtReadAll` — `admin/notifications/index.blade.php:17`, headers) → riesgo cero.
8. **Catálogo de tests** (§6): F9–F18, G1–G5, H1–H5, A1–A4 (24 casos).
9. **Errata** en `spec-service-notification-fase4.md`: su DoD (:74) marca "Contrato HTTP idéntico verificado por tests" pero `tests/Feature/Notification/...HttpTest.php` (§4) nunca existió (`find tests -iname '*otification*'` → vacío). Los HttpTest G/H/A de este spec cubren ese contrato de verdad.
10. **Docs**: `tests/README.md` (conteos, árbol), `AGENTS.md` (Routes: nota de nombres propios + guard), `.agents/README.md` (índice 52→53, fase5 7→8).

### OUT

- **Verbo de `read-notifications`** (Q3): sigue GET aunque muta estado (`markAllRead`) — riesgo prefetch/crawler menor; higiene no rediseña el contrato. Deuda documentada aquí.
- **`'meassage'`** (`Api/AcnooWithdrawController:27`) — ya registrado en 5.10 (spec-withdraw-index-request-fase5); no se absorbe.
- **Inconsistencias de naming con call sites o estilísticas** (Q6 OUT, deuda adyacente documentada): `maintenance/completed` singular (`landlord.php:32`), `support/get-message` (:91), `application.filter` (:48), `withdraw.reject/approve` (`admin.php:91-92`, call site en `DemoMode:114-115`), `rent/cancelled` vs `rents/canceled` (`api.php:45` vs `landlord.php:73`), `only('reject')` muerto (`admin.php:89`), métodos `maan*` (5.10).
- **Duplicación 3 capas Admin↔Landlord de controllers de notifications** — patrón by-design de 4.2 (`NotificationService` compartido); deuda 5.13.
- **`POST /blogs` público sin throttle** (hallazgo lateral del inventario: único write público del sitio, `routes/web.php:7` resource `only(index,show,store)` sin auth ni `throttle:`) — Q11(A): finding propio #27 en `seguridad.md` + fila 5.14 en `plan-accion.md`; este spec solo lo registra, no lo repara.
- **Linter general de colisiones generadas por apiResource**: la colección final nunca muestra method+URI repetidos (colapsan) pero los nombres duplicados **sí** son visibles en la colección plana; F14 los atrapa. Fuera: cualquier fuente de nombres más exótica (p. ej. nombres generados dinámicamente) — residual documentado.

### Cobertura objetivo

- F9–F18 + G1–G5 + H1–H5 + A1–A4 en verde (24 casos; suite esperada 301+24=**325**, verificar contra salida real — lección 5.3).
- `php artisan route:list` audit: 0 pares method+URI duplicados; 0 rutas nombradas exactamente `api` o `admin.`; `apiread-notifications` registrada con URI `api/v1/read-notifications`; `apilandlord-maintenance.index` ≠ `apitenant-maintenance.index`.
- Higiene de fuente (F17): 0 `read-nitifications` en `routes/`; 1 sola línea `income-categories/delete-all` en `routes/admin.php`; 0 `NotifyContrller` en `app/` + `phpstan-baseline.neon`; 0 `admin..` y 0 `subscription-reports.paid|reject` en `DemoMode.php`.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rama de trabajo | En `main` | `git checkout -b spec/typos-rutas-fase5-draft` (ya creada para este borrador; impl en `spec/typos-rutas-fase5-impl`) |
| P2 | Suite base en verde | 301 casos según `AGENTS.md`/`tests/README.md` | `php artisan test` y verificar **301 contra salida real** antes de empezar (lección 5.3) |
| P3 | Inventario de rutas | Hecho (2026-11-21): 741 rutas via `route:list --json`; 34×`api` + 2×`admin.` con nombre de prefijo desnudo; único par fuente duplicado (`admin.php:182/184`); 0 duplicados runtime method+URI | Ninguna: base del catálogo y del DoD |
| P4 | Call sites del rename | Verificado (2026-11-21): 0 usos de `read-nitifications` fuera de `routes/api.php:93`; 0 `route('api')` internos | Ninguna; residual externo documentado en §2/§8 |
| P5 | Patrón de aserciones sobre la tabla de rutas | Existe — `app('router')->getRoutes()->getByName(...)` (`tests/Feature/Landlord/LandlordAcnooActionsSmokeTest.php:212-213`) | Reutilizar en `RouteNamesSmokeTest` |
| P6 | Listas DemoMode accesibles para tests | Falta — arrays locales dentro de `handle()` (`DemoMode.php:16-189`) | Extraer a `public const` (mecánica de Q10); `handle()` idéntico consumiendo la constante |
| P7 | Tests de contrato HTTP de notifications | Faltan — `spec-service-notification-fase4` §4 declara `Notification/...HttpTest.php` que no existe | Crear los 3 HttpTest (G/H/A) + errata en ese spec |

---

## 4. Estructura propuesta

```
routes/api.php                                                # MODIFICADO: :93 rename URI + ->name; naming aditivo (34);
                                                              #   :48/:77 ->names(['index' => '...-maintenance.index'])
routes/admin.php                                              # MODIFICADO: :184 línea duplicada fuera; :27-28 ->name();
                                                              #   :271-272 tidy notifications (filter, view/all sin slash)
routes/landlord.php                                           # MODIFICADO: :140-141 tidy notifications
app/Http/Middleware/DemoMode.php                              # MODIFICADO: 4 nombres muertos corregidos; entrada duplicada
                                                              #   landlords.delete-all fuera; listas -> public const
app/Http/Controllers/Admin/NotifyContrller.php                # ELIMINADO (muerto, sin rutas)
phpstan-baseline.neon                                         # MODIFICADO: 2 entradas huérfanas fuera (:368, :374)

tests/Feature/Demo/DemoModeTest.php                           # EXTENDIDO: F9–F13 (F1–F8 existentes intactos)
tests/Feature/Routes/RouteNamesSmokeTest.php                  # NUEVO: F14–F18
tests/Feature/Notification/AdminNotificationHttpTest.php      # NUEVO: G1–G5
tests/Feature/Notification/LandlordNotificationHttpTest.php   # NUEVO: H1–H5
tests/Feature/Notification/ApiNotificationHttpTest.php        # NUEVO: A1–A4

.agents/spec-impl/spec-service-notification-fase4.md           # ERRATA bajo su DoD (:74)
.agents/spec-impl/spec-permission-middleware-admin-fase3.md   # NOTA en :162 (NotifyContrller borrado)
.agents/README.md                                              # índice 52→53, fase5 7→8
tests/README.md                                                # conteos 325, árbol (Routes/, Notification/)
AGENTS.md                                                      # Routes: nombres propios + guard RouteNamesSmokeTest

.analisis/ (gitignored — edición local al cierre, sin commit)
  seguridad.md                                                 # #25 cerrado + #27 nuevo (POST /blogs)
  deuda-tecnica.md                                             # DT-B2 cerrado
  plan/plan-accion.md                                          # fila 5.8 cerrada + fila 5.14 nueva + Métricas
```

**Naming aditivo — regla (Q9=A):** nombre = familia del resource hermano cuando existe (`properties.status` junto a `apiproperties.*`), si no, el segmento del URI; full name = prefijo `api` concatenado sin punto / `admin.` con punto. Cero nombres existentes cambian. Propuesta (el implementador valida colisiones con `route:list` antes de cerrar):

| Origen | Ruta | `->name()` propuesto |
|--------|------|----------------------|
| api.php:12 | GET /facility | `facilities.all` → `apifacilities.all` |
| api.php:13 | GET /amenity | `amenities.all` → `apiamenities.all` |
| api.php:21-23 | POST send-reset-code / resend-otp / user-password-reset | `send-reset-code` / `resend-otp` / `user-password-reset` |
| api.php:27-29 | GET /user · POST profile-setup · GET offline-gateway | `user-info` / `profile-setup` / `offline-gateway` |
| api.php:36 | POST landlord properties/status | `properties.status` (familia `apiproperties.*`) |
| api.php:38-41 | POST landlord application/{action}/{id} + file | `application.processing/approved/rejected/file` |
| api.php:43-46 | POST landlord rent/{action}/{id} | `rent.approved/accept/cancelled/agreement-sign` |
| api.php:49-52 | POST/GET landlord maintenance/{action}/{id} + reports | `landlord-maintenance.rejected/processing/completed/reports` |
| api.php:54 | GET landlord user-withdraw-methods | `user-withdraw-methods` |
| api.php:56-57 | GET landlord withdraw/invoice|summary/{id} | `withdraw.invoice` / `withdraw.summary` |
| api.php:59-60 | GET landlord dashboard/{chart} | `dashboard.property-chart` / `dashboard.account-chart` |
| api.php:72 | POST tenant application/sign/{id} | `tenant-application.sign` |
| api.php:74 | POST tenant offline-payment | `tenant-offline-payment` |
| api.php:80-81 | POST tenant refund-request/{action}/{id} | `refund-request.upload-receipt/approved` |
| api.php:93 | GET read-notifications (renombrada) | `read-notifications` → `apiread-notifications` |
| api.php:95-97 | GET sign-out · POST refresh-token · GET payment-url | `sign-out` / `refresh-token` / `payment-url` |
| admin.php:27-28 | GET properties/get-{facilities\|amenities}/{landlordId} | `properties.get-facilities` / `properties.get-amenities` |
| api.php:48/:77 | apiResource maintenance (landlord/tenant) | `->names(['index' => 'landlord-maintenance.index' \| 'tenant-maintenance.index'])` — solo index |

---

## 5. Decisiones técnicas

Fijadas por grilling Q1–Q11 (2026-11-21), no negociables salvo volver el spec a `Borrador`:

- **Q1 — Alcance real:** documentado + hallazgos funcionales (nombres DemoMode muertos ×4, colisión `apimaintenance.index`, borrado de `NotifyContrller`). Justifica el spec frente a la regla anti-trivial de `spec-conventions.md` ("no crear spec para cambios triviales"): los DemoMode son bugs funcionales vigentes y hay decisión de contrato externo.
- **Q2 — Hard rename sin alias:** sin evidencia de consumidor externo (0 call sites, sin colección API en repo); alias especulativo = clutter (YAGNI). Residual documentado en plan/seguridad: si existe app móvil, migra en su próximo release.
- **Q3 — GET se mantiene:** `read-notifications` sigue mutando estado vía GET; cambiar verbo duplica la superficie de ruptura. Deuda menor registrada (riesgo prefetch/crawler).
- **Q4 — `NotifyContrller` se borra aquí:** es exactamente "typo + duplicado", riesgo cero (sin rutas). Baseline: solo se retiran las 2 entradas huérfanas de la clase borrada (:368, :374) — no regeneración; diff revisado en el PR (regla AGENTS.md).
- **Q5/Q9 — Guard de unicidad + naming aditivo (A):** `->name()` explícito solo a las 34+2 rutas hoy sin nombre; cero nombres existentes cambian (nada de prefijos de subgroup que renameen `apiproperties`, ni refactor a puntos). El test de unicidad falla hoy (34×`api`, 2×`admin.`, 2×`apimaintenance.index`) y fuerza el naming completo.
- **Q6 — Tidy acotado a la familia notifications** (que ya pineamos con tests); el resto de inconsistencias queda OUT documentado (algunas tienen call sites, p. ej. `withdraw.reject` ↔ `DemoMode:114-115`).
- **Q7/Q10 — Guards:** regresión DemoMode (F9–F12) + guard de existencia de nombres (F13, itera las const; captura cualquier futuro nombre muerto/doble punto) + unicidad (F14) + higiene de fuente (F17) — el "lint" que DT-B2 reclama.
- **Q11 — `POST /blogs`:** finding #27 + fila 5.14; el spec 5.8 solo lo registra como origen.
- **`->names(['index' => ...])` parcial:** Laravel permite override por acción; se toca **solo** `index` para no romper `apimaintenance.store/show/update` (referenciado por `DemoMode:140`).
- **Framework:** PHPUnit (`^10.1`, Laravel 11); MySQL `dbrentpro_test`; `DemoModeTest` mantiene su `RefreshDatabase` existente; los archivos nuevos usan `DatabaseTransactions` (regla testing-conventions); web `actingAs` / API `Sanctum::actingAs($user, ['*'])`; `PermissionSeeder` en setUp para G (patrón `DemoModeTest`); sin mocks (no hay HTTP externo); AAA comentado; un test = una aserción principal.
- **Orden del pipeline (contexto, no cambio):** `DemoMode` corre en el grupo `web` (`Kernel.php:73`), antes de `auth`/`admin`/`permission` — el 499 se dispara pre-controller (no requiere fila existente; los tests lo ejercitan igual que `DemoModeTest::F2`).
- **Un solo PR (precedente 5.3–5.7):** este borrador nace en `spec/typos-rutas-fase5-draft`; implementación en `spec/typos-rutas-fase5-impl`; revisión → `Aprobado` → DoD tachado + `estado: Implementado` antes del merge. Nada se mergea en Borrador.

---

## 6. Catálogo de casos

### 6.1 DemoMode — regresión funcional y guard (`F`) — `tests/Feature/Demo/DemoModeTest.php` (extensión; F1–F8 intactos)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F9 | transacciones update bloqueado en demo | env `demo` (`app()->detectEnvironment`), `createAdmin` + `PermissionSeeder` | `PUT /admin/transactions/1` (`withoutMiddleware(VerifyCsrfToken)`) | **499** + JSON `{message: 'This action is disabled in demo mode.'}` — hoy NO bloqueado (nombre muerto `admin..`) |
| F10 | transacciones destroy bloqueado | idem | `DELETE /admin/transactions/1` | **499** + mismo JSON |
| F11 | suscripción paid bloqueado | idem | `POST /admin/subscriptions/paid/1` | **499** + mismo JSON |
| F12 | suscripción reject bloqueado | idem | `POST /admin/subscriptions/reject/1` | **499** + mismo JSON |
| F13 | guard de existencia (lint de nombres) | app booted, rutas registradas | iterar `DemoMode::ADMIN_DISABLED_ROUTES` + `LANDLORD_DISABLED_ROUTES` vs `app('router')->getRoutes()->getByName($name)` | cada nombre de ambas listas resuelve a una ruta registrada (captura dobles puntos y nombres muertos futuros) |

### 6.2 Tabla de rutas (`F`) — `tests/Feature/Routes/RouteNamesSmokeTest.php` (nuevo)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F14 | unicidad de nombres | app booted | iterar `app('router')->getRoutes()`, mapear `getName()` (filtrar null) | 0 nombres repetidos en la colección plana — hoy falla: 34×`api`, 2×`admin.`, 2×`apimaintenance.index` |
| F15 | 0 nombres de prefijo desnudo | idem | iterar `getName()` | ninguno es exactamente `api` ni `admin.` (las 36 renombradas) |
| F16 | rename pineado | idem | `getByName('apiread-notifications')` + scan de URIs | existe, URI `api/v1/read-notifications` (GET); ninguna ruta con URI `api/v1/read-nitifications` |
| F17 | colisión maintenance resuelta | idem | `getByName('apilandlord-maintenance.index')` + `getByName('apitenant-maintenance.index')` + `getByName('apimaintenance.update')` | ambas existen con URIs distintas (`landlord/maintenance` vs `tenant/maintenance`); `apimaintenance.update` sigue registrada (`DemoMode:140`) |
| F18 | higiene de fuente | archivos fuente vía `file_get_contents` + `substr_count` | scan `routes/api.php`, `routes/admin.php`, `app/`, `phpstan-baseline.neon`, `DemoMode.php` | api.php: 0 `read-nitifications`; admin.php: exactamente 1 línea `income-categories/delete-all`; app/ + baseline: 0 `NotifyContrller`; DemoMode: 0 `admin..` y 0 `subscription-reports.paid/reject` |

### 6.3 Contrato notifications Admin (`G`) — `tests/Feature/Notification/AdminNotificationHttpTest.php` (nuevo)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| G1 | listado admin | `createAdmin` + `PermissionSeeder`, `actingAs` | `GET /admin/notifications` | **200** + view `admin.notifications.index` |
| G2 | filter con nueva URI | idem | `POST /admin/notifications/filter` | **200** + JSON con key `data` (HTML render) |
| G3 | URI antigua fuera | idem | `POST /admin/notifications/notification/filter` | **404** |
| G4 | read all | admin con notificación no leída (`$admin->notifications()->create([...])`) | `GET /admin/notifications/view/all` | **302** (back) + `read_at` set en la notificación |
| G5 | view marca leída y redirige | notificación del admin con `data['admin_url'] = '/admin/plans'` | `GET /admin/notifications/{id}` | **302** a `/admin/plans` + `read_at` set |

### 6.4 Contrato notifications Landlord (`H`) — `tests/Feature/Notification/LandlordNotificationHttpTest.php` (nuevo)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| H1 | listado landlord | `createLandlord` (sin plan expirado), `actingAs` | `GET /landlord/notifications` | **200** + view `landlord.notifications.index` |
| H2 | filter con nueva URI | idem | `POST /landlord/notifications/filter` | **200** + JSON con key `data` |
| H3 | URI antigua fuera | idem | `POST /landlord/notifications/notification/filter` | **404** |
| H4 | read all | landlord con notificación no leída | `GET /landlord/notifications/view/all` | **302** + `read_at` set |
| H5 | view marca leída y redirige | notificación con `data['landlord_url'] = '/landlord/properties'` | `GET /landlord/notifications/{id}` | **302** a `/landlord/properties` + `read_at` set |

### 6.5 Contrato notifications API (`A`) — `tests/Feature/Notification/ApiNotificationHttpTest.php` (nuevo)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| A1 | listado | `Sanctum::actingAs($landlord, ['*'])` | `GET /api/v1/get-notifications` | **200** + JSON `{message: 'Data fetched successfully', data: [...]}` |
| A2 | read all (URI renombrada) | Sanctum + notificación no leída del actor | `GET /api/v1/read-notifications` | **200** + JSON `{message: 'Mark all read successfully'}` + `read_at` set |
| A3 | URI typo fuera | Sanctum | `GET /api/v1/read-nitifications` | **404** |
| A4 | sin token | anónimo | `GET /api/v1/read-notifications` | **401** |

### Tests de caracterización

Ninguno: no caracterizamos los bugs — los reparamos en este spec. Los 4 nombres muertos de DemoMode y las URIs typo'd dejan de existir; F9–F12, G3, H3 y A3 son anti-regresión.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] Rename aplicado: `routes/api.php:93` `read-notifications` + `->name('read-notifications')`; 0 `read-nitifications` en `routes/` (F16, F18).
- [x] Dedupe: exactamente 1 línea `income-categories/delete-all` en `routes/admin.php` (F18).
- [x] DemoMode: 4 nombres muertos → reales (`admin.transactions.*`, `admin.subscriptions.paid/reject`); entrada duplicada `landlords.delete-all` fuera; listas en `public const`; 0 `admin..` (F9–F13, F18 en verde).
- [x] `NotifyContrller` borrado; 2 entradas baseline fuera (diff justificado: commit `cab1e60`, entrada por clase borrada — no regeneración); nota en `spec-permission-middleware-admin-fase3.md` (F18).
- [x] Naming aditivo completo: `route:list` audit `bare=0`, `duplicate_method_uri=0`, 0 nombres repetidos de la app (F14, F15); convención `api` sin punto intacta (`apisign-in`, `apiproperties` sin cambio).
- [x] Colisión resuelta solo en index: `apilandlord-maintenance.index` / `apitenant-maintenance.index` registradas con URIs distintas; `apimaintenance.store/show/update` intactas (F17).
- [x] Tidy notifications: URIs `admin|landlord/notifications/filter` y `/view/all` (sin segmento `notification/` ni slash final); URIs antiguas → 404 (G3, H3).
- [x] Catálogo en verde: F9–F18, G1–G5, H1–H5, A1–A4 (24 casos) — suite **325 passed (1743 assertions)**, exacto 301+24, verificada contra salida real de `php artisan test`.
- [x] `composer analyse` ([OK] No errors; baseline −2 entradas huérfanas) + `composer lint` passed (tras auto-fix de Pint en 3 archivos de test — estilo, sin efecto semántico).
- [x] Errata en `spec-service-notification-fase4.md` (bajo su DoD :74) + docs: `tests/README.md` (conteo 325, árbol + sección "Higiene de rutas"), `AGENTS.md` (Routes: nota nombres propios + guard), `.agents/README.md` (índice 52→53, fase5 7→8).
- [x] Cierres locales gitignored: `seguridad.md` #25 cerrado + #27 nuevo (`POST /blogs`), `deuda-tecnica.md` DT-B2 cerrado, `plan-accion.md` fila 5.8 cerrada + fila 5.14 nueva + Métricas de éxito.
- [x] Rama `spec/typos-rutas-fase5-impl` + **PR #55** (squash, 1 aprobación + CI); sin push a `main`; DoD tachado antes de pedir merge.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Consumidor externo móvil de `read-nitifications` (hipotético) | Medio | Hard rename sin alias (Q2, YAGNI); residual documentado en plan 5.8 + seguridad #25; sin evidencia de consumidor (grep 0 + sin colección API en repo) |
| GET `read-notifications` muta estado | Bajo | Aceptado (Q3); riesgo prefetch/crawler menor; deuda documentada en §2 OUT |
| Overrides de nombre exóticos no atrapados por F14 (fuentes dinámicas) | Bajo | F14 cubre la colección plana (apiResource collisions incl.); linter general OUT documentado; revisar `route:list` en PRs que toquen rutas |
| Refactor const en `DemoMode` | Bajo | Comportamiento idéntico (`handle()` consume la constante); F1–F8 existentes + F9–F13 nuevos en verde |
| Nombres nuevos colisionan con existentes | Bajo | Regla de familia (§4) + tabla propuesta validada contra `route:list` en DoD; F14 como guard |
| Tests de notifications dependen del seed de notificaciones | Bajo | Usar la relación `notifications()` del actor (`NotificationService::markAllRead` la usa, :51); `DatabaseTransactions`; AAA |
| CSRF 419 antes del 499 en POST de demo | Bajo | `withoutMiddleware(VerifyCsrfToken::class)` — patrón vigente de `DemoModeTest::F2` |

**Deuda viva registrada (sin DoD aquí):** verbo GET mutante (Q3); `'meassage'` (5.10); inconsistencias de naming de §2 OUT; duplicación 3 capas de notifications (5.13); linter general de nombres (§2 OUT); `POST /blogs` → fila 5.14.

**Pendiente de decisión:** ninguna (grilling Q1–Q11 cerrado el 2026-11-21).

---

## 9. Estimación

- Fixes de rutas + DemoMode + borrado + baseline: 0.25 día.
- Naming aditivo (36) + colisión maintenance: 0.25 día.
- Tests (24 casos) + corrida focal + suite completa: 0.5 día.
- Docs + errata + cierres documentales: 0.25 día.
- Total: ~1.25 días.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.8 (l.113)
- `.analisis/seguridad.md` → Seg-25 (l.143); Seg-27 (nuevo, registro en el cierre)
- `.analisis/deuda-tecnica.md` → DT-B2 (l.98-99)
- `routes/api.php` → :48, :77 (colisión `apimaintenance.index`), :93 (`read-nitifications`), 34 rutas sin `->name()`
- `routes/admin.php` → :27-28 (`admin.` desnudo), :45 (resource transactions), :136-137 (subscriptions paid/reject), :182/184 (duplicada), :268-273 (grupo notifications)
- `routes/landlord.php` → :137-142 (grupo notifications)
- `app/Http/Middleware/DemoMode.php` → :16-189 (listas), :96-97/:104-105 (nombres muertos), :66/:69 (entrada duplicada), :114-115 (`withdraw.*`, vivos)
- `app/Http/Middleware/Kernel.php` → :73 (`DemoMode` en grupo `web`)
- `app/Http/Controllers/Admin/NotifyContrller.php` (a borrar) · `Admin/NotificationController.php` · `Landlord/NotificationController.php` · `Api/AcnooNotificationController.php` (contrato JSON)
- `app/Services/NotificationService.php` → :48-53 (`markAllRead` vía `unreadNotifications()`)
- `phpstan-baseline.neon` → :368, :374 (entradas huérfanas)
- `tests/Feature/Demo/DemoModeTest.php` → F1–F8, patrón `detectEnvironment('demo')` + 499
- `tests/Feature/Landlord/LandlordAcnooActionsSmokeTest.php` → :212-213 (patrón `app('router')->getRoutes()`)
- `.agents/spec-impl/spec-service-notification-fase4.md` → §4/DoD :74 (HttpTest declarado, inexistente)
- `.agents/spec-impl/spec-permission-middleware-admin-fase3.md` → :161-162 (filas `NotificationController`/`NotifyContrller`)
- `.agents/spec-impl/spec-bug-rutas-landlord-cruzadas-fase3.md` → precedente de fix de higiene de rutas con regresión (Seg-15)
- `.agents/spec-impl/spec-get-option-tipos-consistentes-fase5.md` → precedente de flujo Borrador→Implementado + lección de conteos
- `.agents/spec/spec-conventions.md` · `.agents/spec/spec-template.md`
- `.agents/skills/crear-spec.md` · `.agents/skills/testing-conventions.md` · `.agents/skills/workflow-ramas.md`
- `AGENTS.md` → "Routes" (convención `api` sin punto), "Tooling" (regla del baseline)

---

### Notas de implementación

1. **DemoMode a const** (esqueleto):
   ```php
   public const ADMIN_DISABLED_ROUTES = [
       // ... entradas actuales, con:
       'admin.subscriptions.paid',        // era admin.subscription-reports.paid (:96)
       'admin.subscriptions.reject',      // era admin.subscription-reports.reject (:97)
       'admin.transactions.update',       // era admin..transactions.update (:104)
       'admin.transactions.destroy',      // era admin..transactions.destroy (:105)
       // ... entrada duplicada 'admin.landlords.delete-all' (:69) eliminada
   ];
   public const LANDLORD_DISABLED_ROUTES = [ /* entradas actuales intactas */ ];

   public function handle(Request $request, Closure $next): Response
   {
       if (app()->environment('demo')) {
           if (in_array(Route::currentRouteName(), self::ADMIN_DISABLED_ROUTES)) { ... }
           elseif (auth()->check() && ... && in_array(Route::currentRouteName(), self::LANDLORD_DISABLED_ROUTES)) { ... }
       }
       return $next($request);
   }
   ```
2. **Colisión maintenance — solo index:**
   ```php
   Route::apiResource('maintenance', Api\AcnooLandlordMaintenanceController::class)
       ->only('index')->names(['index' => 'landlord-maintenance.index']);   // :48
   Route::apiResource('maintenance', Api\AcnooTenantMaintenanceController::class)
       ->only('index', 'store', 'show', 'update')->names(['index' => 'tenant-maintenance.index']); // :77
   ```
   Partial `names()` — `apimaintenance.store/show/update` intactos (`DemoMode:140`).
3. **Seed de notificaciones en tests (G4/G5, H4/H5, A2):** `$actor->notifications()->create(['type' => ..., 'data' => [...]])` — misma relación que consume `NotificationService::markAllRead` (:51).
4. **Errata `spec-service-notification-fase4.md`** (bajo su DoD :74):
   > **Errata (2026-11-XX, `spec-typos-rutas-fase5`):** el DoD marcaba "Contrato HTTP idéntico verificado por tests" como cumplido, pero `tests/Feature/Notification/...HttpTest.php` (§4) nunca existió. Los tests G1–G5 / H1–H5 / A1–A4 del spec 5.8 cubren ese contrato desde ahora.
5. **Nota `spec-permission-middleware-admin-fase3.md`** (:162):
   > Nota (2026-11-XX): `NotifyContrller` (clase muerta de la fila superior) fue eliminada en `spec-typos-rutas-fase5`; la cobertura de permisos aplica al vivo `Admin\NotificationController`.
6. **Redacciones de cierre (patrón 5.7, aplicadas al verificar el DoD):**
   - `seguridad.md` #25 → "— **cerrado (2026-11-XX, spec `spec-typos-rutas-fase5`)**: rename + dedupe + 4 nombres DemoMode muertos corregidos (transacciones/suscripciones vuelven a bloquearse en demo) + guards F13/F14/F18; residual: GET mutante y consumidor móvil hipotético."
   - `seguridad.md` #27 (nuevo): "**27. `POST /blogs` público sin throttle** (registrado 2026-11-21, hallazgo del inventario 5.8; fuera de su scope): único write público del sitio (`routes/web.php:7`, resource `only(index,show,store)`; `BlogController::store` crea comentarios sin auth ni `throttle:`) → vector de spam. Tratamiento: fila 5.14."
   - `deuda-tecnica.md` DT-B2 → "— **cerrado (2026-11-XX, spec `spec-typos-rutas-fase5`)**: el 'lint' que reclamaba existe como guards F13/F14/F18."
   - `plan-accion.md` fila 5.8 → "**Cerrado (2026-11-XX):** spec `spec-typos-rutas-fase5` — rename `read-notifications`, dedupe, 4 nombres DemoMode (funcionales), colisión `apimaintenance.index` + naming aditivo 36 rutas, `NotifyContrller` borrado, tidy notifications + errata spec 4.2; residual: GET mutante (Q3); hallazgo lateral `POST /blogs` → #27 + fila 5.14."
   - `plan-accion.md` fila 5.14 (nueva): "| 5.14 | Throttle/validación de `POST /blogs` público (comentarios anónimos) | Seg-27 (nuevo, hallazgo del inventario 5.8) |"
   - `plan-accion.md` Métricas de éxito → "✅ 0 typos/duplicados de rutas y nombres DemoMode muertos; tabla de rutas sin nombres repetidos ni prefijos desnudos (Fase 5 / 5.8 — spec-typos-rutas-fase5, 2026-11-XX)"
   - `AGENTS.md` Routes → añadir: "Todas las rutas tienen `->name()` propio (0 nombres `api`/`admin.` de prefijo desnudo) — guard `tests/Feature/Routes/RouteNamesSmokeTest.php`."
   - `tests/README.md` → conteo 325 + mini-sección "## Higiene de rutas (Fase 5 / 5.8)".
   - `.agents/README.md` → índice 52→53, fase5 (7→8).
7. **Verificación rápida:**
   ```bash
   php artisan test tests/Feature/Demo/DemoModeTest.php
   php artisan test tests/Feature/Routes/RouteNamesSmokeTest.php
   php artisan test tests/Feature/Notification
   composer analyse && composer lint && php artisan test
   ```

### Desviaciones menores (implementación, 2026-11-21)

1. **F14 acotado a rutas de la app**: la colección reveló un duplicado preexistente del paquete `safiull/laravel-installer` (`LaravelInstaller::codeVerifyProcess` ×2, no documentado en el inventario) — el guard filtra nombres con `::` (marcador de namespace vendor); ninguna ruta de la app lo usa.
2. **Mecánica del seed de notificaciones** (G4/G5, H4/H5, A2): `User::notifications()` usa el modelo custom `App\Models\Notification` (sin `HasUuids`) → el test pasa `id` uuid explícito; `will_expire` es columna de `users` (`plan_data()` la mergea sobre `PlanSubscribe`, Helper.php:122) → el landlord del H se crea con `will_expire` futura + fila `PlanSubscribe`; `Sanctum::actingAs()` devuelve el User → no es encadenable con `$this->getJson()`.
3. **Pint auto-fix** en 3 archivos de test (estilo, sin efecto semántico): comillas simples, import sin uso fuera, FQCN `\App\Models\User` → import, orden de imports.
