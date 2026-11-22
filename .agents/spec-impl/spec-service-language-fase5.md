---
id: spec-service-language-fase5
titulo: "Service Language — controlador < 200 líneas (Fase 5 / 5.13, hijo 1)"
estado: Implementado
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.13) — DT-C4
cobertura: Migración de Admin/LanguageController (264 líneas) a app/Services/LanguageService.php, una sola capa (Admin), sin duplicación entre capas
---

# Spec — Service Language (Fase 5 / 5.13, hijo 1)

> Migrar la lógica de negocio de `Admin/LanguageController` a `LanguageService`, dejando el controlador < 200 líneas, sin cambiar el contrato observable. Piloto barato del patrón Fase 5: una capa, sin dinero.

**Patrón de referencia:** `spec-unificar-capas-services-fase4.md` §8. **Paraguas:** `spec-unificar-capas-services-fase5.md`.

## 1. Objetivo

`Admin/LanguageController` (264) queda < 200 con toda la lógica (FS de `lang/*.json` — incluida la lectura de traducciones de `show()` — guard de `is_default`, invalidación de caché) en `LanguageService`.

## 2. Alcance

### IN
- `app/Services/LanguageService.php`: `list`, `store`, `update`, `delete`, `deleteMany`, `setStatus`, `setDefault`, `translations`.
- Refactor in-place de `Admin/LanguageController` (deja de usar `HasTableActions`; `filter` delega al service).
- Delegación de `show()`: el controlador conserva `abort(404)` + paginación + shaping vista/JSON; la lectura FS y la búsqueda viven en `LanguageService::translations()`.
- Tests de contrato HTTP previos (congelan el comportamiento actual; **no** son `@group characterization` — deben seguir en verde tras la migración) + unit tests del service.

### OUT
- `Admin/TranslationController` (166, < 200, sin duplicación) — queda tal cual.
- Migrar `File::` a disk de `Storage` (las ops sobre `lang/*.json` se mueven tal cual).
- `routes/admin.php` L257–261 (resource + filter/delete-all/status/default) se preservan intactas.

### Cobertura objetivo
- Controlador < 200 líneas; suite + PHPStan + Pint en verde; contrato HTTP idéntico.

## 3. Pre-requisitos

| # | Pre-requisito | Estado | Acción |
|---|---|---|---|
| P1 | Paraguas aprobado | Aprobado (2026-11-21, junto con este hijo tras auditoría sin altos) | — |
| P2 | Suite verde | 371 casos | `php artisan test` |
| P3 | Patrón Fase 4 | Implementado | Heredar convenciones |

## 4. Estructura propuesta

```
app/Services/LanguageService.php
tests/Unit/LanguageServiceTest.php
tests/Feature/Language/AdminLanguageHttpTest.php   # contrato previo a la migración
```

Contrato del service (FS de `lang/{code}.json` con `File::`, iconos vía `HasUploader`):

```php
class LanguageService
{
    public function list(array $filters = []): LengthAwarePaginator;
    public function store(array $data): Language;            // crea fila + copia/crea JSON base
    public function update(Language $language, array $data): Language;   // mueve el JSON si cambia el code
    public function delete(Language $language): void;        // fila + icono + JSON (guard 403 en el controlador)
    public function deleteMany(array $ids): int;             // excluye is_default (comportamiento actual)
    public function setStatus(Language $language, int $status): Language;   // guard is_default
    public function setDefault(string $id): ?Language;       // tx + forgetModel() explícito (spec 4.9)
    /** @return array{language: Language, entries: Collection<int, array{key: string, value: string}>}|null */
    public function translations(string $code, ?string $search = null): ?array;  // null si no existe lang/{code}.json
}
```

## 5. Decisiones técnicas

- Single-layer: sin `Channel` (no hay variantes Admin/Landlord/Api); el service recibe `$data` explícitos.
- `Language` es modelo cacheado con observer (`#[ObservedBy]`): `setDefault` conserva la tx + `CacheKeys::forgetModel()` explícito post-commit (Q15a spec 4.9). Mass-ops del service que no disparen observers llaman `forgetModel()` explícito.
- **Desviación menor aprobada (grill Q15):** `deleteAll` actual borra iconos+JSON **antes** de borrar filas. El service lo invierte: capturar rutas → borrar filas en tx → borrar archivos post-commit best-effort. No observable por HTTP; se documenta como desviación.
- El controlador deja de usar `HasTableActions`; `filter` se reimplementa delegando en `list($filters)` (regla paraguas §5.4: cero rutas muertas nuevas).
- `setStatus`/`deleteMany` preservan los guards de `is_default` actuales (la default no se borra ni se desactiva).
- `show()` se divide: `LanguageService::translations()` hace el lookup por code + lectura/decodificación/búsqueda del JSON; el controlador conserva el `abort(404)` de archivo ausente, la paginación (`LengthAwarePaginator` con `url()->current()`, acoplada a HTTP) y el shaping vista/JSON ajax. El controlador queda sin `File::` directo.
- **Quirks preservados verbatim (port fiel, documentados en §11; candidatos a spec propio de hardening, no se fixean aquí):**
  1. Guard de iconos con `file_exists($language->icon)` (ruta relativa al CWD que nunca coincide con el disk): en `delete`/`deleteMany` el icono **no se borra** — solo se reemplaza vía `HasUploader::upload(..., $oldFile)` en `update`. F5/F6 congelan este comportamiento real.
  2. `setDefault` con id inexistente corre la tx igual (deja el sistema sin idioma default) y no invalida caché (`find`-or-null): firma `setDefault(string $id): ?Language` para el port exacto.
  3. `deleteMany` sin `ids` → `TypeError` → 500: la firma tipada `array $ids` preserva el mismo código HTTP.

## 6. Catálogo de casos (prefijo `F` HTTP, `U` unit — red existente parcial: `CacheAdminInvalidationTest` congela status/destroy/delete-all/default (200 + invalidación de caché) y `PermissionDenialSmokeTest::test_f51` el guard `languages-read`; F1–F11 congelan el resto del contrato, incluidos los quirks §5)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Index lista idiomas | admin, 3 idiomas | GET /admin/languages | 200, vista con 3 |
| F2 | Filter por nombre | admin, "en" y "fr" | POST /admin/languages/filter search=fr | 200, solo fr |
| F3 | Store crea fila + JSON | admin | POST /admin/languages (code=zz, icono) | 200 JSON redirect; `lang/zz.json` existe en FS; fila creada |
| F4 | Update sin romper JSON | admin, idioma con traducciones | PUT /admin/languages/{id} | 200; JSON intacto; icono nuevo subido, viejo borrado |
| F5 | Destroy limpia fila + JSON | admin, idioma no default con icono+JSON | DELETE /admin/languages/{id} | 200; fila y JSON eliminados; icono NO borrado (quirk guard `file_exists`, §5) |
| F6 | deleteAll excluye default | admin, 3 idiomas (1 default) | POST /admin/languages/delete-all ids=[las 3] | 200; 2 filas borradas (JSON post-commit); default intacta; iconos NO borrados (quirk §5) |
| F7 | Status guard | admin, idioma default | POST /admin/languages/status/{id} status=0 | 403 + mensaje; se mantiene activa (guard is_default) |
| F8 | Set default cambia opción | admin, idioma B | POST /admin/languages/default/{id} | B default, A deja de serlo; caché invalidada (`forgetModel`) |
| F9 | Traducciones intactas tras migración | admin, JSON con N keys | POST/PUT flujos F3–F4 | keys del JSON idénticas (solo se crea/mueve archivo, no se reescribe contenido) |
| F10 | Show lista traducciones | admin, idioma con JSON de N keys | GET /admin/languages/{code} | 200, vista con las N entradas paginadas (20/pág) |
| F11 | Show aborta sin JSON | admin, fila sin `lang/{code}.json` | GET /admin/languages/{code} | 404 |

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | store crea JSON base | — | `store(['code'=>'de', ...])` | fila + `lang/de.json` |
| U2 | deleteMany excluye default | default + 2 | `deleteMany([...])` | retorna 2; default persiste; JSON borrados post-commit (iconos no, quirk §5) |
| U3 | setStatus guard | default | `setStatus(default, 0)` | permanece status=1 |
| U4 | setDefault invalida caché | A default, B candidata | `setDefault(B)` | B default + `CacheKeys::forgetModel()` llamado |

> F1–F11 congelan el comportamiento actual (quirks incluidos) y se implementan **antes** de tocar el controlador. No llevan `@group characterization`: no documentan un bug con fase de ruptura asignada, sino el contrato que la migración debe preservar — el DoD exige que sigan en verde después.

## 7. Criterios de aceptación (Definition of Done)

- [x] `LanguageService` con el contrato §4 en verde (U1–U4).
- [x] Contrato F1–F11 en verde antes de migrar; sigue en verde después.
- [x] `Admin/LanguageController` < 200 líneas, sin `HasTableActions`, sin `File::` directo.
- [x] Desviación del orden interno de `deleteAll` (§5) implementada y documentada en §11 (Revisión).
- [x] Suite + PHPStan (sin nuevas entradas) + Pint en verde.

## 8. Riesgos

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| FS de `lang/*.json` fuera de tx (E/S no DB) | medio | Patrón 4.8: capturar rutas, borrar archivos post-commit best-effort; F9 congela contenido |
| Olvidar `forgetModel` en mass-op | alto | U4 + regla 4.9 del paraguas |
| Quirks (iconos, `setDefault` id inexistente) "arreglados" por accidente | medio | F5/F6 congelan el comportamiento real; §5 los declara fuera de scope |

## 9. Estimación

- 3–4 días (caracterización 1–1.5, service 1, migración 0.5, validación 0.5–1).

## 10. Referencias

- `spec-unificar-capas-services-fase5.md` (paraguas) · `spec-unificar-capas-services-fase4.md` §8
- `.analisis/plan/plan-accion.md` → Fase 5 / 5.13 · `.analisis/deuda-tecnica.md` → DT-C4

## 11. Revisión (2026-11-21)

Auditoría `revisar-spec` previa a la implementación. Hallazgos y resoluciones:

| H# | Hallazgo | Severidad | Resolución |
|----|----------|-----------|------------|
| H1 | `show()` (ruta viva `GET /admin/languages/{code}`, enlazada desde `datas.blade.php`/`translate.blade.php`) fuera del contrato §4 y del catálogo — DoD "sin `File::`" insatisficiable | alto | Añadidos `translations()` al contrato, decisión §5 y casos F10–F11 |
| H2 | "Hoy hay cero tests de esta entidad" falso: `CacheAdminInvalidationTest` + `PermissionDenialSmokeTest::test_f51` ya congelan parte del contrato | medio | Red existente documentada en §6; F-cases la complementan sin duplicar |
| H3 | "Tests de caracterización" aplicado a tests que deben seguir verdes; el skill reserva ese marcado para bugs con fase de ruptura | medio | Renombrados a "tests de contrato" (§2, §6, §7); sin `@group characterization` |
| H4 | F2 decía `q=fr`; el trait filtra por `$request->search` | medio | Corregido a `search=fr` |
| H5 | Tabla §8 sin columna Impacto (plantilla) | bajo | Columna añadida + riesgo de quirks |
| H6 | Blockquote "F1–F8" vs DoD "F1–F9" | bajo | Unificado a F1–F11 |
| H7 | F5/F6 describían un comportamiento idealizado: el guard `file_exists($language->icon)` (ruta relativa al CWD, nunca coincide con el disk) hace que el icono NO se borre hoy | medio | Catálogo congelado al comportamiento real (quirk §5.1); port fiel |
| H8 | Firma `setDefault(Language)` forzaría `findOrFail` o cambiaría el quirk del id inexistente (tx corre igual, sin `forgetModel`) | bajo | Firma `setDefault(string $id): ?Language` (port exacto, quirk §5.2) |

**Estado:** `Borrador` → `Aprobado` (decisor autoriza enmendar + aprobar + implementar en la misma sesión de revisión; paraguas aprobado en el mismo acto por H2).

### Implementación (2026-11-21) — rama `spec/service-language-fase5-impl`

**Desviación `deleteAll` (§5, aprobada en grill Q15):** implementada. El service captura las rutas de los `lang/{code}.json` → borra las filas en tx (`each->delete()`, dispara observers → `forgetModel`) → borra los JSON tras el commit (best-effort). El guard muerto de iconos (`file_exists` relativo al CWD, quirk §5.1) se omite en el port: nunca llegó a disparar, el observable es idéntico (F5/F6 lo congelan en verde pre y post).

**Decisión de implementación — arrays explícitos en `store`/`update`:** el controlador original pasaba `$request->except('icon')` a `create()`/`update()`; el nuevo controlador construye arrays explícitos (`name`, `code`, `order`, `icon`), heredando el patrón Fase 4 (`IncomeCategoryService`) y la convención 5.11 + checklist secure-coding #7/#17. Delta observable: payloads crafted con campos fillable extra (`status`, `is_default`) ya no aplican en store/update — ni F1–F11 ni la red existente congelan ese edge, y los 43 sitios `except()` restantes siguen fuera de scope (paraguas §2 OUT).

**Quirks preservados (§5):** los tres, verificados por F5/F6 (iconos no borrados), U2 (default excluida) y el port find-or-null de `setDefault` (firma `string $id`).

**Evidencia DoD:**

| Criterio | Evidencia |
|---|---|
| Controlador < 200 | `wc -l` → **168** líneas (antes 264) |
| Sin `HasTableActions` / `File::` / `DB::` / `Storage::` en controlador | `grep -c` → **0** ocurrencias |
| Contrato pre-migración | F1–F11: **11 passed** contra el controlador original (43 assertions) |
| Contrato post-migración | F1–F11: **11 passed** tras la migración; U1–U4 **4 passed** |
| Suite completa | **402 passed (4721 assertions)** — antes 387; 0 fallidos |
| PHPStan (level 5 + baseline congelado) | **[OK] No errors** — sin entradas nuevas |
| Pint (preset laravel) | **passed** |
| Rutas | `routes/admin.php` intacto — cero rutas muertas nuevas (regla paraguas §5.4) |
