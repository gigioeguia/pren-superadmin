---
id: spec-service-language-fase5
titulo: "Service Language — controlador < 200 líneas (Fase 5 / 5.13, hijo 1)"
estado: Borrador
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.13) — DT-C4
cobertura: Migración de Admin/LanguageController (264 líneas) a app/Services/LanguageService.php, una sola capa (Admin), sin duplicación entre capas
---

# Spec — Service Language (Fase 5 / 5.13, hijo 1)

> Migrar la lógica de negocio de `Admin/LanguageController` a `LanguageService`, dejando el controlador < 200 líneas, sin cambiar el contrato observable. Piloto barato del patrón Fase 5: una capa, sin dinero.

**Patrón de referencia:** `spec-unificar-capas-services-fase4.md` §8. **Paraguas:** `spec-unificar-capas-services-fase5.md`.

## 1. Objetivo

`Admin/LanguageController` (264) queda < 200 con toda la lógica (FS de `lang/*.json`, guard de `is_default`, invalidación de caché) en `LanguageService`.

## 2. Alcance

### IN
- `app/Services/LanguageService.php`: `list`, `store`, `update`, `delete`, `deleteMany`, `setStatus`, `setDefault`.
- Refactor in-place de `Admin/LanguageController` (deja de usar `HasTableActions`; `filter` delega al service).
- Tests de caracterización HTTP previos + unit tests del service.

### OUT
- `Admin/TranslationController` (166, < 200, sin duplicación) — queda tal cual.
- Migrar `File::` a disk de `Storage` (las ops sobre `lang/*.json` se mueven tal cual).
- `routes/admin.php` L257–261 (resource + filter/delete-all/status/default) se preservan intactas.

### Cobertura objetivo
- Controlador < 200 líneas; suite + PHPStan + Pint en verde; contrato HTTP idéntico.

## 3. Pre-requisitos

| # | Pre-requisito | Estado | Acción |
|---|---|---|---|
| P1 | Paraguas aprobado | Borrador | Aprobar antes de implementar |
| P2 | Suite verde | 371 casos | `php artisan test` |
| P3 | Patrón Fase 4 | Implementado | Heredar convenciones |

## 4. Estructura propuesta

```
app/Services/LanguageService.php
tests/Unit/LanguageServiceTest.php
tests/Feature/Language/AdminLanguageHttpTest.php   # caracterización previa
```

Contrato del service ( FS de `lang/{code}.json` con `File::`, iconos vía `HasUploader`):

```php
class LanguageService
{
    public function list(array $filters = []): LengthAwarePaginator;
    public function store(array $data): Language;            // crea fila + copia/crea JSON base
    public function update(Language $language, array $data): Language;
    public function delete(Language $language): void;        // fila + icono + JSON
    public function deleteMany(array $ids): int;             // excluye is_default (comportamiento actual)
    public function setStatus(Language $language, int $status): Language;   // guard is_default
    public function setDefault(Language $language): Language; // tx + forgetModel() explícito (spec 4.9)
}
```

## 5. Decisiones técnicas

- Single-layer: sin `Channel` (no hay variantes Admin/Landlord/Api); el service recibe `$data` explícitos.
- `Language` es modelo cacheado con observer (`#[ObservedBy]`): `setDefault` conserva la tx + `CacheKeys::forgetModel()` explícito post-commit (Q15a spec 4.9). Mass-ops del service que no disparen observers llaman `forgetModel()` explícito.
- **Desviación menor aprobada (grill Q15):** `deleteAll` actual borra iconos+JSON **antes** de borrar filas. El service lo invierte: capturar rutas → borrar filas en tx → borrar archivos post-commit best-effort. No observable por HTTP; se documenta como desviación.
- El controlador deja de usar `HasTableActions`; `filter` se reimplementa delegando en `list($filters)` (regla paraguas §5.4: cero rutas muertas nuevas).
- `setStatus`/`deleteMany` preservan los guards de `is_default` actuales (la default no se borra ni se desactiva).

## 6. Catálogo de casos (prefijo `F` HTTP, `U` unit — hoy hay cero tests de esta entidad)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Index lista idiomas | admin, 3 idiomas | GET /admin/languages | 200, vista con 3 |
| F2 | Filter por nombre | admin, "en" y "fr" | POST /admin/languages/filter q=fr | 200, solo fr |
| F3 | Store crea fila + JSON | admin | POST /admin/languages (code=de, icono) | 200 JSON redirect; `lang/de.json` existe en FS; fila creada |
| F4 | Update sin romper JSON | admin, idioma con traducciones | PUT /admin/languages/{id} | 200; JSON intacto; icono nuevo subido, viejo borrado |
| F5 | Destroy limpia todo | admin, idioma no default con icono+JSON | DELETE /admin/languages/{id} | 200; fila, icono y JSON eliminados |
| F6 | deleteAll excluye default | admin, 3 idiomas (1 default) | POST /admin/languages/delete-all ids=[las 3] | 200; 2 borradas (files post-commit); default intacta |
| F7 | Status guard | admin, idioma default | POST /admin/languages/status/{id} status=0 | se mantiene activa (guard is_default) |
| F8 | Set default cambia opción | admin, idioma B | POST /admin/languages/default/{id} | B default, A deja de serlo; caché invalidada (`forgetModel`) |
| F9 | Traducciones intactas tras migración | admin, JSON con N keys | POST/PUT flujos F3–F4 | keys del JSON idénticas (solo se crea/mueve archivo, no se reescribe contenido) |

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | store crea JSON base | — | `store(['code'=>'de', ...])` | fila + `lang/de.json` |
| U2 | deleteMany excluye default | default + 2 | `deleteMany([...])` | retorna 2; default persiste; archivos borrados post-commit |
| U3 | setStatus guard | default | `setStatus(default, 0)` | permanece status=1 |
| U4 | setDefault invalida caché | A default, B candidata | `setDefault(B)` | B default + `CacheKeys::forgetModel()` llamado |

> F1–F8 caracterizan el comportamiento actual y se implementan **antes** de tocar el controlador (skill `tests-caracterizacion`).

## 7. Criterios de aceptación (Definition of Done)

- [ ] `LanguageService` con el contrato §4 en verde (U1–U4).
- [ ] Caracterización F1–F9 en verde antes de migrar; sigue en verde después.
- [ ] `Admin/LanguageController` < 200 líneas, sin `HasTableActions`, sin `File::` directo.
- [ ] Desviación del orden interno de `deleteAll` (§5) implementada y documentada en §Revisión del spec.
- [ ] Suite + PHPStan (sin nuevas entradas) + Pint en verde.

## 8. Riesgos

| Riesgo | Mitigación |
|--------|------------|
| FS de `lang/*.json` fuera de tx (E/S no DB) | Patrón 4.8: capturar rutas, borrar archivos post-commit best-effort; F9 congela contenido |
| Olvidar `forgetModel` en mass-op | U4 + regla 4.9 del paraguas |

## 9. Estimación

- 3–4 días (caracterización 1–1.5, service 1, migración 0.5, validación 0.5–1).

## 10. Referencias

- `spec-unificar-capas-services-fase5.md` (paraguas) · `spec-unificar-capas-services-fase4.md` §8
- `.analisis/plan/plan-accion.md` → Fase 5 / 5.13 · `.analisis/deuda-tecnica.md` → DT-C4
