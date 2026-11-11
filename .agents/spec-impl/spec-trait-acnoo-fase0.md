---
id: spec-trait-acnoo-fase0
titulo: "Trait único HasAcnooActions para acnooFilter/deleteAll/status/exportExcel/exportCsv"
estado: Aprobado
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
| Exports | clase Export (31 en `app/Exports`), args (`$request->status`), filenames divergentes por formato | `acnooExport()` + `acnooExcelFilename()`/`acnooCsvFilename()` |

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
| H3 | deleteAll ajeno | landlord A autenticado; amenity de landlord B | POST `/landlord/amenities/delete-all` `{ids: [ajena]}` | fila de B BORRADA (sin ownership check) — **caracterización** |
| H4 | filter que borra | landlord A con 1 property propia | POST `/landlord/properties/filter` `{ids: [id]}` | la property queda BORRADA (ruta cruzada ejecuta `deleteAll`) — **caracterización** |

> **Caracterización de Seg-5** (H3) — `deleteAll` sin ownership: se espera que falle tras Fase 2 (fix en el trait/Policies).
> **Caracterización de Seg-15** (H4) — `routes/landlord.php:15` cruza `properties/filter`→`deleteAll`: se espera que falle tras Fase 3.6 (fix de rutas, fuera de 0.3).

### 6.3 Transversales (`F`) — verificaciones del PR

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | 0 duplicados restantes | adopción completa | `grep -rE 'function (acnooFilter\|deleteAll\|status\|exportExcel\|exportCsv)\(' app/Http/Controllers` | 0 resultados, salvo los overrides documentados (si los hay, exactamente los enumerados en notas) — el trait vive en `app/Traits/`, fuera del scope del grep |
| F2 | adoptantes | adopción completa | `grep -rl 'use App.Traits.HasAcnooActions' app/Http/Controllers \| wc -l` | 52 (34 Admin + 18 Landlord; 3 excluidos — D2) |
| F3 | phpstan nivel 5 verde | baseline ajustado | `composer analyse` | exit 0; baseline 709 → N (solo entradas unmatched removidas, diff en el PR) |
| F4 | estilo verde | — | `composer lint` | exit 0 |
| F5 | rutas intactas | — | `git diff origin/main -- routes/` | vacío (152 referencias sin cambio) |
| F6 | CI + suite | PR abierto | run de CI (4 jobs) | verde; suite completa (74 total) < 30 s |

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
- [ ] Suite completa (60 smoke + 12 de comportamiento + 2 estructurales = 74) verde en local y en los 4 jobs de CI; F6 < 30 s.
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
| Endpoints tocados sin cobertura smoke (la suite cubre 44/52 endpoints críticos, ninguno de estos 55×5 — verificado 2026-11-11) | alto | Moves verbatim + phpstan nivel 5 + G/H representativos + review por commits mecánicos |
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

---

## 11. Revisión (2026-11-11)

Auditoría según skill `revisar-spec`: frontmatter 6/6, prefijos G/H/F oficiales correlativos sin huecos, formato de catálogo, conteo 18 = 8+4+6 coherente entre §6, DoD (60+12) y estimación, referencias vigentes (todas existen) e idioma ✓. Cobertura del ítem 0.3 completa: 5 métodos, 55 controladores, criterio de salida de Fase 0 medible. Nota: `H#` abajo son hallazgos de revisión (convención del skill); los casos de catálogo landlord son H1–H4 de §6.2.

| H# | Hallazgo | Severidad | Resolución |
|----|----------|-----------|------------|
| H1 | F2: patrón `grep 'use HasAcnooActions'` no matchea el import real `use App\Traits\HasAcnooActions;` → la verificación mediría 0, no 55 | medio | Corregido: patrón `'App\Traits\HasAcnooActions'` |
| H2 | F1: el resultado esperado citaba `HasAcnooActions.php`, pero el grep se acota a `app/Http/Controllers` y el trait vive en `app/Traits/` → nunca aparecería | bajo | Corregido: esperado = 0 resultados salvo overrides documentados |
| H3 | Marca `— **caracterización**` en columna Escenario (H3/H4); la convención y el precedente del spec smoke la sitúan en columna Entonces | bajo | Corregido: marca movida a Entonces |
| H4 | §4 citaba "20 en `app/Exports`" — el conteo real es 31 | bajo | Corregido |
| H5 | §8 citaba "la suite cubre 52 endpoints críticos" — la cifra de 0.1 es 44/52; verificado además que ningún test smoke toca estos 55×5 endpoints | bajo | Corregido |

**Decisión:** 0 hallazgos altos, 1 medio y 4 bajos (todos resueltos en esta revisión) → estado pasa a `Revisado`. Pendiente de `Aprobado` por el decisor.

**Aprobación (2026-11-11):** el decisor aprueba el spec tras revisión sin hallazgos altos. Listo para implementar en `spec/trait-acnoo-fase0-impl` (apilada sobre `-draft`); las decisiones pendientes de §8 se resuelven en implementación.

---

## 12. Notas de implementación (2026-11-11)

Implementado en rama `spec/trait-acnoo-fase0-impl` (apilada sobre `-draft`).

### Inventario P5 (completado antes de la adopción)

Extracción mecánica de los 169 cuerpos (script de brace-matching sobre los 55 controladores) + cross-check rutas↔métodos con autoload. Resultado: **52 controladores adoptan** el trait, **3 excluidos** (su único método no encaja en el esqueleto y queda como override: `Admin\AcnooSupportController` — filter sin paginate, lista `get()`; `Admin\NotificationController` y `Landlord\NotificationController` — filter `get()` con JSON incondicional), **11 overrides** en adoptantes y **5 supresiones de `status`**. Migrados: 169 − 14 = **155** (verificado: verbatim-check 98 Admin + 57 Landlord).

**Admin (36):**

| Controlador | Migra al trait | Queda en el controlador | Notas |
|---|---|---|---|
| AcnooAmenityController | filter, deleteAll (bulk), status | — | per10; status msg `'Amenity'`; referencia de adopción |
| AcnooBlogController | filter, deleteAll (row-by-row), status | — | per10; media `image`; msg `'Blog'` |
| AcnooClientController | filter, deleteAll (2-fase→intercalado, D5), status | — | per10; msg `'Client '` (espacio final) |
| AcnooCommentController | filter, deleteAll (bulk) | — | redirect `admin.blogs.index` |
| AcnooCurrencyController | filter | deleteAll (guard moneda default → 400) | per10; `orderBy` antes de `latest()` |
| AcnooExpenseCategoryController | filter, deleteAll (bulk), status | — | `whereNull('landlord_id')`; per10 |
| AcnooExpenseController | deleteAll (bulk), exports | acnooFilter (total_expense en JSON); status suprimido (D1) | exports `AdminExpensesExport` `'admin-expenses'` |
| AcnooFacilityController | filter, deleteAll (bulk), status | — | per10 |
| AcnooFaqController | filter, deleteAll (bulk), status | — | per10; msg `'Faq '` (espacio final) |
| AcnooFeatureController | filter, deleteAll (2-fase→D5), status | — | per10; msg `'Feature '` (espacio final) |
| AcnooHighlightController | filter, deleteAll (row-by-row), status | — | per10; media `image` |
| AcnooHouseTypeController | filter, deleteAll (bulk), status | — | per**20** |
| AcnooIncomeCategoryController | filter, deleteAll (bulk), status | — | `whereNull('landlord_id')`; per10 |
| AcnooIncomeController | deleteAll (bulk), exports | acnooFilter (total_income); status suprimido (D1) | exports `AdminExportIncome` `'admin-incomes'` |
| AcnooLandlordController | filter, exports | deleteAll (transacción + user_details + `$e->getMessage()`) | per20; `AdminLandlordExport($request)` `'landlords'` |
| AcnooLandlordWithdrawReportController | filter, exports | — | per20 `input()` (D3); `'landlord-withdraw-report'` |
| AcnooLanguageController | filter | deleteAll (`is_default`+File+Cache), status (guard 403+Cache) | per20; `orderBy('order','asc')` |
| AcnooMessageController | filter, deleteAll (bulk) | — | redirect `admin.messages.index` |
| PropertyController | los 5 (deleteAll row-by-row con media) | — | per20; status `'Status Updated'` + redirect (único admin); `'properties'` |
| AcnooRefundReasonController | filter, deleteAll (bulk), status | — | per20; redirect `admin.refund-reason.index` (singular) |
| AcnooRefundRequestController | filter, exports | — | per20 `input()` (D3); `'refund-reports'` |
| AcnooRentPaymentController | filter, exports | — | per20; `'rent-payments'` |
| AcnooRentPaymentReportController | filter, exports | — | per20 `input()` (D3); `'rent-payemnt-report'` (**typo preservado**) |
| AcnooSecurityDepositController | filter, exports | — | per20; `'security-deposits'` |
| AcnooSecurityDepositReportController | filter, exports | — | per20; `'security-deposit-reports'` |
| AcnooSubscriptionController | filter, exports | — | per10; `'subscription-list'` |
| AcnooSubscriptionReportController | filter, exports | — | per10 `input()` (D3); `'subscription-reports'` |
| AcnooSupportController | — | acnooFilter (excluido: `get()` sin paginate) | no adopta el trait |
| AcnooTenantController | filter, exports | deleteAll (transacción + user_details) | per20; `AdminTenantExport($request->status)`; excel `'tenant'` ≠ csv `'tenants'` |
| AcnooTestimonialController | filter, deleteAll (row-by-row) | — | per10; media `client_image` |
| AcnooTransactionController | filter, exports | — | per20; `'transactions'` |
| AcnooTransactionReportController | filter, exports | — | per20 `input()` (D3); `'transaction-reports'` |
| AcnooWithdrawMethodController | los 5 (deleteAll bulk, status) | — | per20; status msg `'withdrawMethod'`; `__('Selected Items deleted successfully.')` |
| AcnooWithdrawRequestController | filter, exports | — | per20 (+`when($request->id)`); `'withdraw-request'` |
| NotificationController | — | acnooFilter (excluido) | no adopta el trait |
| UserController | filter, deleteAll (bulk) | status suprimido (D1: ruta muerta) | per20; `__('Selected Staff deleted successfully')` |

**Landlord (19):**

| Controlador | Migra al trait | Queda en el controlador | Notas |
|---|---|---|---|
| AcnooAmenityController | filter (scoped), deleteAll (bulk), status | — | per10 |
| AcnooApplicationController | filter, exports | — | per20; `$property = Property::...->get('id')` como statement del hook; `'landlord-applications'` |
| AcnooExpenseCategoryController | filter (scoped), deleteAll (bulk), status | — | per10 |
| AcnooExpenseController | deleteAll (bulk), exports | acnooFilter (total_expense); status suprimido (D1) | `'landlord-expenses'` |
| AcnooFacilityController | filter (scoped), deleteAll (bulk), status | — | per10 |
| AcnooIncomeCategoryController | filter (scoped), deleteAll (bulk), status | — | per10 |
| AcnooIncomeController | deleteAll (bulk), exports | acnooFilter (total_income); status suprimido (D1) | `'landlord-incomes'` |
| AcnooLaborController | filter (scoped), deleteAll (bulk), exports | — | per20; `'landlord-labors'` |
| AcnooMaintenanceController | filter, exports | — | per20; `'landlord-maintenances'` |
| AcnooMaintenanceReportController | filter, exports | — | per20; `acnooViewData` retorna 5 vars (4 contadores recomputados en el hook); `'landlord-maintenance-reports'` |
| AcnooRefundRequestController | filter (scoped), exports | — | per10; `'refund-request'` |
| AcnooRentInvitationController | filter, exports | — | per20 `input()` (D3); `redirect()->back()` ≡ `url()->previous()` (D4); `'landlord-rent-invitation'` |
| AcnooRentPaymentController | filter, exports | — | per20; `'landlord-rent-payments'` |
| AcnooSecurityDepositController | filter (scoped), exports | — | per20; `'security-deposits'` |
| AcnooTenantController | filter, exports | deleteAll (transacción + user_details) | per20; `LandlordTenantExport($request)` `'landlord-tenants'` |
| AcnooWithdrawController | filter (scoped), exports | — | per20; `'landlord-withdraw-method'` |
| AcnooWithdrawMethodController | filter (scoped), deleteAll (bulk), exports | — | per10; redirect `landlord.withdraw-method.index` (singular); `'landlord-user-method'` |
| NotificationController | — | acnooFilter (excluido) | no adopta el trait |
| PropertyController | filter (scoped), status, exports | deleteAll (guard rent→400 dentro del bucle) | per20; status msg `'Property'` sin redirect; `'landlord-properties'` |

### Desviaciones (documentadas; ninguna exige volver a Borrador)

| # | Punto | Detalle |
|---|-------|---------|
| D1 | Rutas muertas vs trait | 6 rutas apuntan hoy a métodos inexistentes (5×`status`: UserController y AcnooIncome/Expense de ambas capas; 1×`acnooFilter`: `Landlord\AcnooSupportController`, que no adopta). Los 5 adoptantes suprimen `status` con `use HasAcnooActions { status as protected; }` — la ruta sigue muerta (500 al invocarla) igual que hoy. Sin la supresión, el trait **reviviría** las rutas = cambio de comportamiento. |
| D2 | F2 = 52, no 55 | 3 controladores excluidos (inventario). El DoD "55/55" se cumple como 52 adoptando + 3 excluidos documentados con su único método como override. (Corrección sobre el borrador: los overrides en adoptantes son 11, no 10 — faltaba contar `Landlord\AcnooTenantController::deleteAll`; total restante 14 = 11 + 3.) |
| D3 | `input('per_page', N)` ≡ `?? N` | Los filtros de reportes leen per_page con `input('per_page', N)`; el trait usa `$request->per_page ?? acnooPerPage()`. Difieren únicamente si `per_page` se envía explícitamente como `null`. |
| D4 | `redirect()->back()` ≡ `redirect(url()->previous())` | RentInvitation usa `back()`; ambos producen 302 a la URL previa. |
| D5 | deleteAll 2-fase → intercalado | Client/Feature (admin) borran la media de todas las filas y luego `each->delete()`; el trait intercala media+delete por fila vía `deletingAcnooRow`. Mismo estado final y misma respuesta; cambia solo el orden interno de operaciones (relevante únicamente si un listener de eventos leyera archivos). |
| D6 | `deletingAcnooRow($row)` sin tipo nativo | El trait pasa `Model` al hook; tipar el parámetro nativo cambiaría los mensajes de phpstan de los accesos (`Blog::$image` → `Model::$image`) rompiendo el baseline. Sin tipo, los accesos quedan sobre mixed (nivel 5 no los reporta) y el único ajuste del baseline fue el count de `Admin\PropertyController` `Property::$units` 3→2 (709 → 708). Nota: se exploró `@template TModel` en el trait para resolver el tipo concreto por contexto; phpstan no propaga `class-string<TModel>` a través de la llamada estática, se descartó (2 iteraciones, regla "sin magia"). |
| D7 | MaintenanceReport: contadores recomputados | Los 4 contadores del `acnooFilter` original se calculaban tras el paginate; ahora se recomputan dentro de `acnooViewData` (payload idéntico, +1 query: el pluck de `$properties`). La línea recomputada usa `auth()->id()` (no `auth()->user()->id`) para no añadir una 3ª ocurrencia de un error congelado en el baseline. |

### Decisiones pendientes de §8 (resueltas en implementación)

1. **Test paramétrico sobre todas las rutas `*.filter`/`*.delete-all`: NO se añade.** Las 43 vistas `*.datas` requieren datos de seeders heterogéneos por entidad; el costo y el riesgo de falsos positivos superan el beneficio frente a la cobertura existente (G/H representativos + verbatim-check 155/155 + phpstan nivel 5).
2. **H4 con property del propio landlord: confirmado suficiente.** H3 documenta el ownership (Seg-5) por separado; H4 aísla el bug de rutas (Seg-15) usando solo datos propios, de modo que cada test rompa por exactamente una razón en su fase.

### Desviación de proceso D8: 1 PR → 3 PRs apilados

El diff total de la implementación es de **265,678 caracteres**, por encima del límite de review de GitHub (**150,000**). La entrega se divide en 3 PRs apilados siguiendo el propio plan de commits atómicos de §5:

| PR | Rama head → base | Contenido | Diff |
|----|------------------|-----------|------|
| 1 | `spec/trait-acnoo-fase0-impl-1` → `main` | ciclo del spec (Borrador→Aprobado) + trait + tests G/H + `tests/README.md` | 49k |
| 2 | `spec/trait-acnoo-fase0-impl-2` → `impl-1` | adopción Admin (34) + baseline 709→708 | 134k |
| 3 | `spec/trait-acnoo-fase0-impl` → `impl-2` | adopción Landlord (18) + docs + D6/D7 | 87k |

Merge en orden 1→2→3 con **merge commit** (no squash): preserva los commits atómicos del plan de §5 y mantiene el diff incremental de cada PR tras el retarget automático de base al borrarse la rama anterior (con squash, el merge-base se perdería y el PR siguiente volvería a mostrar el diff completo, excediendo otra vez el límite). Justificación frente a workflow-ramas ("merge commit solo si el PR tiene commits atómicos relevantes a preservar"): se cumple — cada PR lleva exactamente los commits atómicos del spec.

### Respuesta al review del PR 1 (2026-11-11)

Hallazgo (medio): "Shared actions remain unused — the controllers do not use HasAcnooActions, so the smoke tests exercise the old implementations rather than the trait". **Correcto para el PR 1 aislado**: por el diseño apilado (D8), ese slice introduce trait + tests sin adopción — el trait queda sin uso y los tests fijan el comportamiento de las copias locales hasta que los PR 2/3 aterrizan. Resolución:

1. **Docblocks corregidos (PR 1)**: los tests G/H ahora declaran explícitamente que en ese punto fijan el comportamiento de las copias locales y que la continuidad en verde tras la adopción es la prueba de preservación.
2. **Pruebas estructurales (PR 2 y PR 3)**: `test_routed_actions_are_provided_by_the_trait` verifica por reflection que los controladores representativos usan `HasAcnooActions` y que sus ficheros ya no definen los métodos enrutados (Admin: Amenity 3 + Property 5; Landlord: Amenity 3 + Property 4, comprobando además que su `deleteAll` **sigue** local por ser override documentado). Con esto la centralización no es solo un grep del PR: está cubierta por la suite.
3. Los mismos endpoints, tras la adopción, son servidos por el trait (verificado: suite en verde en los tres estados del stack — 72 en PR 1, 73 en PR 2, 74 en PR 3).

### Respuesta al review — hallazgo crítico Seg-5 (diferido por el decisor)

Hallazgo (crítico): "Landlords can alter others' records — status() usa findOrFail sin scope y deleteAll() usa whereIn sin scope". **El IDOR es real pero preexistente**: las 33+19 copias originales tenían exactamente el mismo código unscoped (verificado en el inventario P5), y este spec lo preserva 1:1 por contrato (§5 preservación estricta; §2-OUT: "scoping/ownership en deleteAll/status → Fase 2"). Es el hallazgo 5 de `seguridad.md`, caracterizado por H3 — diseñado para romper de forma detectable cuando el fix llegue.

**Decisión del decisor (2026-11-11): diferir a Fase 2** (plan-accion 2.1, con Policies). Motivos: (a) el plan ordena las fases por urgencia y dependencia — Fase 1 (críticos Seg-1..8) va antes; (b) un fix correcto no es un cambio global: el trait sirve a Admin (sin scope, correcto) y Landlord (scoped, pendiente), y cada controlador landlord scopea distinto (`landlord_id`, `user_id`, `whereHas('rent')`) — el mecanismo (hook de scoping vs Policies) es la decisión central del spec de Fase 2, y adelantarlo aquí arriesga rework; (c) la concentración en el trait es precisamente lo que hará el fix trivial y único. Mitigación en este PR: notas de seguridad en las líneas citadas del trait (`status()`/`deleteAll()`) advirtiendo además contra un scoping global que rompería la capa Admin.
