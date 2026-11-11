---
id: spec-trait-acnoo-fase0
titulo: "Trait único HasAcnooActions para acnooFilter/deleteAll/status/exportExcel/exportCsv"
estado: Borrador
fecha: 2026-11-11
origen: .analisis/plan/plan-accion.md (Fase 0 / 0.3) — DT-C3
cobertura: Extracción de 169 métodos duplicados (55 acnooFilter + 33 deleteAll + 19 status + 62 exports) en 55 controladores Admin/Landlord a app/Traits/HasAcnooActions.php con comportamiento observable idéntico
---

# Spec — Trait único `HasAcnooActions` para `acnooFilter`/`deleteAll`/`status`/`exportExcel`/`exportCsv`

> Extraer los 5 métodos copiados controlador por controlador (DT-C3) a un trait único con comportamiento observable idéntico, para que los fixes de seguridad de Fase 2 (IDOR/ownership) y Fase 3 se apliquen en un solo punto en vez de ×55.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 0 / 0.3, origen DT-C3). Criterio de salida de Fase 0: "trait único reemplazando los 138 métodos duplicados".

---

## 1. Objetivo

Que los métodos `acnooFilter`, `deleteAll`, `status`, `exportExcel` y `exportCsv` existan en un único fichero (`app/Traits/HasAcnooActions.php`) y los 55 controladores que hoy los duplican los consuman vía `use`, sin ningún cambio observable de comportamiento — dejando caracterizados (tests) los bugs que las fases siguientes corregirán sobre esa base única.

---

## 2. Alcance

### IN
- Trait `app/Traits/HasAcnooActions.php` con los 5 métodos públicos y hooks de configuración protegidos (§4).
- Adopción en los 55 controladores (36 `Admin/*` + 19 `Landlord/*`) que hoy definen alguno de los 5 métodos: `use HasAcnooActions` + hooks con los valores actuales de cada controlador.
- Tests feature `G` (admin) e `H` (landlord) sobre controladores representativos, incluyendo 2 caracterizaciones (H3, H4).
- Ajuste del `phpstan-baseline.neon` por entradas unmatched (§5).
- Documentación: `AGENTS.md` (sección Controller layout) y `.agents/README.md` (índice de spec-impl).

### OUT
- Capa `Api/*`: 0 de estos 5 métodos (verificado 2026-11-11) — su duplicación es DT-A1 (Fase 4.2).
- Los 10 `status()` de `app/Library/*` (gateways de pago): contrato propio `make_payment`/`status`, sin relación con DT-C3.
- Cualquier fix de seguridad: scoping/ownership en `deleteAll`/`status`/`acnooFilter` (Seg-5 → Fase 2), rutas cruzadas de `routes/landlord.php` (Seg-15 → Fase 3.6), permisos granulares (Seg-18 → Fase 3.8).
- Limpieza de `request()` global dentro de las queries (DT-A3 → ítem 4.4, que ya prevé "refactor del trait de filtrado").
- Renombrado del prefijo `Acnoo` y de los nombres de método (DT-B1 → Fase 5.10; las rutas referencian los nombres).
- Unificar `index()` con `acnooFilter()` (comparten query base, pero `index` no está en el alcance de 0.3).

### Cobertura objetivo
- 169/169 ocurrencias de método reemplazadas por el trait, salvo overrides documentados en notas de implementación (objetivo: 0 overrides; cualquiera queda enumerado con motivo).
- Nota de conteo: el "138 métodos" del plan-accion agrupa el par `exportExcel`+`exportCsv` por controlador (55+33+19+31); este spec cuenta 169 ocurrencias de método (55+33+19+31+31).

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rama de trabajo | En `main` | `git checkout -b spec/trait-acnoo-fase0-draft` (este draft); implementación en `spec/trait-acnoo-fase0-impl` apilada sobre el draft |
| P2 | Suite smoke verde | ✅ 60/60 (~14 s, spec 0.1) | `php artisan test` antes de empezar; debe seguir verde tras cada commit |
| P3 | PHPStan 5 + Pint + CI | ✅ PR #5/#6 (spec 0.2) | Usarlos como red durante el refactor (procedimiento baseline en §5) |
| P4 | Pint full-run integrado antes de 0.3 | ✅ merge 66aca38 (orden crítico del spec 0.2) | Ninguna — el diff de 0.3 no conflictúa con formato |
| P5 | Inventario exhaustivo de variaciones | Parcial (§4 documenta los ejes conocidos) | Primer commit de la implementación: tabla fichero×método→hooks; detectar cuerpos que no encajan (→ override documentado) |

---

## 4. Estructura propuesta

```
app/Traits/HasAcnooActions.php                            # trait único: 5 métodos públicos + hooks protegidos
app/Http/Controllers/Admin/*.php                          # 36 controladores: use + hooks con valores actuales
app/Http/Controllers/Landlord/*.php                       # 19 controladores: ídem
tests/Feature/Admin/AcnooActionsSmokeTest.php             # G1–G8
tests/Feature/Landlord/LandlordAcnooActionsSmokeTest.php  # H1–H4
phpstan-baseline.neon                                     # shrink por entradas unmatched (§5)
AGENTS.md                                                 # sección Controller layout: ahora existe trait compartido
.agents/README.md                                         # índice de spec-impl
```

### Contrato del trait (propuesta; P5 valida el set definitivo de hooks)

```php
trait HasAcnooActions
{
    // Los 5 métodos públicos: firmas idénticas a las actuales.
    public function acnooFilter(Request $request);        // paginate + ajax {data} + redirect back
    public function status(Request $request, string $id); // findOrFail + update(status) + JSON
    public function deleteAll(Request $request);          // bulk delete (+ hook de media si procede)
    public function exportExcel(Request $request);        // Excel::download(..., '*.xlsx')
    public function exportCsv(Request $request);          // Excel::download(..., '*.csv')

    // Hooks protegidos (prefijo acnoo* para evitar colisiones).
    // Cada controlador implementa solo los que sus rutas usan.
    protected function acnooQuery(Request $request): Builder; // query base + search + latest, cuerpo actual VERBATIM
    protected function acnooView(): string;                   // 'admin.amenities.datas' | 'landlord.properties.datas'
    protected function acnooViewData($rows): array;           // ['amenities' => $rows] (nombre de variable de la vista)
    protected function acnooPerPage(): int;                   // 10 | 20 (default actual por controlador)
    protected function acnooModel(): string;                  // FQCN del modelo
    protected function acnooRedirectRoute(): string;          // route() para el JSON de deleteAll
    protected function acnooStatusMessage(): string;          // 'Amenity' | 'Status Updated' (tal cual, aun rotos)
    protected function acnooStatusRedirectRoute(): ?string;   // null = sin key redirect en el JSON de status
    protected function acnooDeleteMessage(): string;          // 'Selected Amenity Deleted Successfully' | __(...)
    protected function acnooExport(Request $request): object; // new AdminPropertiesExport | new AdminTenantExport($request->status)
    protected function acnooExcelFilename(): string;          // base name ('tenant' en AcnooTenantController)
    protected function acnooCsvFilename(): string;            // base name ('tenants' — diverge del excel en el mismo controlador)
    protected function acnooDeletesRowByRow(): bool;          // false = whereIn->delete() bulk (sin model events)
    protected function deletingAcnooRow(Model $row): void;    // limpieza de media antes de $row->delete(); default no-op
}
```

### Ejes de variación observados (2026-11-11) → hook que los preserva

| Eje | Variación real observada | Hook |
|-----|--------------------------|------|
| Query base | eager loads/`withCount` divergentes; scoping `landlord_id` solo en capa Landlord | `acnooQuery()` (verbatim) |
| Campos de búsqueda | por entidad (columnas JSON, `orWhereHas`) | `acnooQuery()` (verbatim) |
| `per_page` default | 10 (amenities) vs 20 (properties) | `acnooPerPage()` |
| Vista render | `admin.*.datas` vs `landlord.*.datas`; nombre de variable distinto por vista | `acnooView()` + `acnooViewData()` |
| Mensaje `status` | `'Amenity'` (roto) vs `'Status Updated'` + `redirect` | `acnooStatusMessage()` + `acnooStatusRedirectRoute()` |
| `deleteAll` | `whereIn->delete()` bulk vs `get()`+`foreach` con borrado de media (Property, Highlight) | `acnooDeletesRowByRow()` + `deletingAcnooRow()` |
| Exports | clase Export (20 en `app/Exports`), args (`$request->status`), filenames divergentes por formato | `acnooExport()` + `acnooExcelFilename()`/`acnooCsvFilename()` |

**Regla de oro:** el cuerpo de cada hook se copia VERBATIM del controlador actual (incluido `request()` global y `__()` en mensajes). Cero reescritura, cero "mejoras".

---

## 5. Decisiones técnicas

Fijadas, no negociables salvo volver el spec a `Borrador`:

- **Preservación estricta:** comportamiento observable idéntico, incluyendo mensajes rotos (`'Amenity'` como mensaje completo de status), `per_page` divergentes y JSON shapes. Mejoras cosméticas quedan para cuando exista razón (o Fase 5.10), no ahora.
- **Nombres de método intactos:** 152 líneas de `routes/admin.php` + `routes/landlord.php` los referencian por nombre. `git diff origin/main -- routes/` debe quedar vacío.
- **Trait único, no varios:** un solo trait con hooks (decisión del plan 0.3). No se divide por método: el valor es UN punto de edición para los fixes de Fase 2.
- **Moves verbatim:** las queries conservan `request()` global y su estructura exacta (DT-A3 se limpia en 4.4, que ya prevé este trait). No se "aprovecha" para inyectar `Request` ni refactorizar.
- **Firma unificada de exports:** `exportExcel(Request $request)` / `exportCsv(Request $request)` en el trait; Laravel inyecta `Request` igual en los controladores que hoy no lo declaran — sin efecto observable. Los args del constructor (`new AdminTenantExport($request->status)`) se resuelven en `acnooExport()`.
- **Model events preservados:** los `deleteAll` bulk mantienen `whereIn->delete()` (sin model events); los row-by-row mantienen `get()` + `$row->delete()` (con events). Cambiar de vía es comportamiento observable por listeners — prohibido.
- **Overrides documentados:** si el inventario (P5) encuentra un cuerpo que no encaja en el esqueleto, ese método NO se migra: queda como override en el controlador, enumerado en notas de implementación con su motivo. Ejemplo ya verificado: `Admin\AcnooLanguageController::status` (guard `is_default` → 403 + `Cache::forget('languages')`). Silently-drop está prohibido.
- **Baseline PHPStan — shrink, jamás regeneración:** al borrar código, entradas del `phpstan-baseline.neon` quedan unmatched y el análisis falla. Procedimiento: (1) `composer analyse` lista las unmatched; (2) remover SOLO esas entradas del baseline; (3) el PR documenta el conteo 709 → N y el diff del fichero. `--generate-baseline` está prohibido (regla de un solo uso del spec 0.2).
- **Adopción en 1 PR con commits atómicos:** rama `spec/trait-acnoo-fase0-impl` (apilada sobre `-draft`), commits: (1) inventario P5 + trait + tests G/H; (2) adopción Admin (36); (3) adopción Landlord (19) + shrink baseline + docs. Squash merge al pasar review + CI.
- **Ubicación y nombre:** `app/Traits/HasAcnooActions.php`, consistente con `app/Traits/HasPermission.php` (ubicación) y `HasUploader` (patrón `Has*`). El prefijo `Acnoo` se retira globalmente en 5.10.
- **Tests:** PHPUnit, patrón de la suite smoke: `RefreshDatabase` + `PermissionSeeder` en `setUp` + `CreatesUsers`, DB `dbrentpro_test` (ADR-0001). `Storage::fake` para G6. `@group smoke` en G/H; `@group characterization` en H3/H4.

---

## 6. Catálogo de casos

**Total: 18 casos — 8 `G` (guard admin), 4 `H` (landlord), 6 `F` (transversales de PR).** G/H son tests PHPUnit (12) sobre controladores representativos (`Admin\AcnooAmenityController` simple, `Admin\PropertyController` con media, `Landlord\AcnooAmenityController`); F son verificaciones del PR/CI. No se testean los 55 controladores: la adopción es mecánica y la cubren F1/F2 + phpstan.

### 6.1 Guard admin (`G`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| G1 | filter ajax | superadmin; 3 `Amenity` | POST `/admin/amenities/filter` con `X-Requested-With` + `search` | 200 JSON con key `data` (HTML de `admin.amenities.datas`) |
| G2 | filter no-ajax | superadmin | POST `/admin/amenities/filter` sin header ajax | 302 redirect a URL previa |
| G3 | status válido | superadmin; amenity `status=0` | POST `/admin/amenities/status/{id}` `{status: 1}` | 200 JSON `{message: 'Amenity'}` (mensaje roto preservado) + BD `status=1` |
| G4 | status inexistente | superadmin | POST `/admin/amenities/status/999999` | 404 |
| G5 | deleteAll simple | superadmin; 3 amenities | POST `/admin/amenities/delete-all` `{ids: [a, b]}` | filas borradas + JSON `{message, redirect}` |
| G6 | deleteAll con media | superadmin; property con `cover_image` sembrado en `Storage::fake` | POST `/admin/properties/delete-all` `{ids: [id]}` | fila borrada + directorios de imágenes borrados del storage (valida `deletingAcnooRow`) |
| G7 | exportExcel | superadmin | GET `/admin/properties-excel` | 200 download `properties.xlsx` (Content-Disposition attachment) |
| G8 | exportCsv | superadmin | GET `/admin/properties-csv` | 200 download `properties.csv` |

### 6.2 Landlord (`H`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| H1 | filter ajax con scoping | landlord A con 2 amenities propias + 1 de landlord B | POST `/landlord/amenities/filter` ajax | 200 JSON `{data}`; el HTML no contiene la ajena (scoping actual preservado) |
| H2 | deleteAll propio | landlord A con 3 amenities | POST `/landlord/amenities/delete-all` `{ids: [a, b]}` | filas propias borradas + JSON `{message, redirect}` |
| H3 | deleteAll ajeno — **caracterización** | landlord A autenticado; amenity de landlord B | POST `/landlord/amenities/delete-all` `{ids: [ajena]}` | fila de B BORRADA (sin ownership check) |
| H4 | filter que borra — **caracterización** | landlord A con 1 property propia | POST `/landlord/properties/filter` `{ids: [id]}` | la property queda BORRADA (ruta cruzada ejecuta `deleteAll`) |

> **Caracterización de Seg-5** (H3) — `deleteAll` sin ownership: se espera que falle tras Fase 2 (fix en el trait/Policies).
> **Caracterización de Seg-15** (H4) — `routes/landlord.php:15` cruza `properties/filter`→`deleteAll`: se espera que falle tras Fase 3.6 (fix de rutas, fuera de 0.3).

### 6.3 Transversales (`F`) — verificaciones del PR

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | 0 duplicados restantes | adopción completa | `grep -rE 'function (acnooFilter\|deleteAll\|status\|exportExcel\|exportCsv)\(' app/Http/Controllers` | solo `HasAcnooActions.php` + los overrides documentados (si los hay, exactamente los enumerados en notas) |
| F2 | 55/55 adoptan | adopción completa | `grep -rl 'use HasAcnooActions' app/Http/Controllers \| wc -l` | 55 |
| F3 | phpstan nivel 5 verde | baseline ajustado | `composer analyse` | exit 0; baseline 709 → N (solo entradas unmatched removidas, diff en el PR) |
| F4 | estilo verde | — | `composer lint` | exit 0 |
| F5 | rutas intactas | — | `git diff origin/main -- routes/` | vacío (152 referencias sin cambio) |
| F6 | CI + suite | PR abierto | run de CI (4 jobs) | verde; suite completa (60 + 12) < 30 s |

### Tests de caracterización

| ID | Bug caracterizado | Fase que lo romperá |
|----|-------------------|---------------------|
| H3 | Seg-5 — `deleteAll` landlord sin ownership check (IDOR bulk delete) | Fase 2 (2.1 Policies / scoping en el trait) |
| H4 | Seg-15 — ruta cruzada `properties/filter`→`deleteAll` en `routes/landlord.php:15` | Fase 3 (3.6) |

---

## 7. Criterios de aceptación (Definition of Done)

- [ ] `app/Traits/HasAcnooActions.php` con los 5 métodos públicos y los hooks de §4; 0 errores PHPStan nuevos.
- [ ] F2: 55/55 controladores (36 Admin + 19 Landlord) con `use HasAcnooActions`.
- [ ] F1: 0 definiciones propias de los 5 métodos fuera del trait, salvo overrides enumerados en notas de implementación con motivo.
- [ ] G1–G8 y H1–H2 en verde: mensajes (incluidos los rotos), `per_page`, JSON shapes y redirects preservados.
- [ ] H3–H4 en verde hoy, marcados `@group characterization` con comentario "expected to fail after Phase 2 / 3.6" (patrón de la suite smoke).
- [ ] Suite completa (60 smoke + 12 nuevas) verde en local y en los 4 jobs de CI; F6 < 30 s.
- [ ] F3: `composer analyse` exit 0; baseline reducido solo con entradas unmatched; conteo 709 → N y diff documentados en el PR.
- [ ] F4: `composer lint` exit 0.
- [ ] F5: `git diff origin/main -- routes/` vacío.
- [ ] G6 con `Storage::fake` valida el camino row-by-row del `deleteAll`.
- [ ] `AGENTS.md` (Controller layout) actualizado: el trait existe, cómo se edita lo compartido y qué queda por controlador.
- [ ] `plan-accion.md` 0.3 marcado ✅ con spec y rama referenciados (al cierre — criterio de salida de Fase 0).

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Variaciones no catalogadas (cuerpos con lógica extra) | medio | Inventario P5 como primer commit; override documentado o hook nuevo — jamás silently-drop |
| Endpoints tocados sin cobertura smoke (la suite cubre 52 endpoints críticos, no estos 55×5) | alto | Moves verbatim + phpstan nivel 5 + G/H representativos + review por commits mecánicos |
| Baseline con entradas unmatched al borrar código | medio | Procedimiento §5 (shrink quirúrgico, conteo y diff en el PR) |
| Diff masivo (~60 ficheros) satura la review | medio | Commits atómicos por capa; review con `--stat`; P4 (Pint ya integrado) evita ruido de formato |
| Frontend depende de los JSON shapes actuales | medio | Preservación estricta §5; G/H asertan shapes; cero cambios de mensaje |
| Colisión de hooks con métodos existentes de controladores | bajo | Prefijo `acnoo*` / `deletingAcnooRow`; grep de colisiones en P5 |
| Rutas cruzadas Seg-15 confunden la adopción (método invocado ≠ nombre de ruta) | medio | La adopción es por método, no por ruta; los tests apuntan a las URLs tal cual están (H4) |
| `deleteAll` bulk vs row-by-row dispara (o no) model events | bajo | `acnooDeletesRowByRow()` preserva la vía actual por controlador (§5) |

**Pendiente de decisión:**
- ¿Añadir un test paramétrico que itere todas las rutas `*.filter`/`*.delete-all` de admin con superadmin (200/302)? Evaluar en implementación: las vistas pueden requerir datos de seeders; si es barato, sumarlo a `G`.
- H4 usa una property del propio landlord para aislar la ruta cruzada del IDOR (H3 ya cubre ownership); confirmar en implementación que es suficiente.

---

## 9. Estimación

- Inventario P5 + trait + hooks + tests G/H: 1–1,5 días
- Adopción Admin (36 controladores): 1–1,5 días
- Adopción Landlord (19 controladores): 0,5–1 día
- Shrink baseline + PR + CI + docs (AGENTS.md, plan-accion): 0,5 días
- **Total: 4–5,5 días**

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 0 / 0.3 (criterio de salida de Fase 0)
- `.analisis/deuda-tecnica.md` → DT-C3 (duplicación masiva), DT-A3 (`request()` global — limpieza diferida a 4.4), DT-M4 (traits apenas usados)
- `.analisis/seguridad.md` → hallazgos 5 (IDOR) y 15 (rutas cruzadas) — caracterizados por H3/H4
- `.agents/spec-impl/spec-tests-smoke-fase0.md` → suite que debe seguir verde (60 casos)
- `.agents/spec-impl/spec-phpstan-pint-ci-fase0.md` → baseline y CI (procedimiento de shrink §5)
- `.agents/skills/testing-conventions.md`, `.agents/skills/tests-caracterizacion.md`, `.agents/skills/workflow-ramas.md`
- `.agents/spec/spec-conventions.md`
