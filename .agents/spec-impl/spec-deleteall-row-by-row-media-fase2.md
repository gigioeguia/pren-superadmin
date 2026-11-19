---
id: spec-deleteall-row-by-row-media-fase2
titulo: "deleteAll row-by-row: limpiar media tras el delete — Fase 2 / 2.6"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 2 / 2.6) — .analisis/deuda-tecnica.md (DT-M10)
cobertura: 7 casos (F31–F37) + 1 unitario (U1) sobre el contrato de limpieza de media del camino row-by-row
---

# Spec — `deleteAll` row-by-row: limpiar media tras el delete (Fase 2 / 2.6)

> Resumen: remediar DT-M10 rediseñando el hook `deletingAcnooRow` del trait `HasAcnooActions` para capturar los paths de media **antes** del delete de la fila y limpiarlos **después** del delete exitoso. Elimina la pérdida de media cuando el delete de la fila falla (FK RESTRICT, concurrencia, pérdida de conexión), respetando las relaciones `cascadeOnDelete` (`propertyDetail`, `units`).

**Origen:** `.analisis/plan/plan-accion.md` (Fase 2 / 2.6) — `.analisis/deuda-tecnica.md` (DT-M10).

---

## 1. Objetivo

Garantizar que, en el camino row-by-row de `deleteAll`, la media de una fila (imágenes, directorios) **solo** se borre cuando la fila se haya borrado con éxito. Capturar los paths (y relaciones con `cascadeOnDelete`) antes del delete, ejecutar `$row->delete()` y, solo si no lanza excepción, limpiar la media capturada. Preservar el comportamiento observable actual (qué se borra y qué no) en los 6 controladores Admin row-by-row y en el override documentado de `Landlord\PropertyController`.

---

## 2. Alcance

### IN
- `app/Traits/HasAcnooActions.php` → camino row-by-row de `deleteAll()` y contrato del hook `deletingAcnooRow()`.
- Los 6 controladores Admin que usan row-by-row con media:
  - `app/Http/Controllers/Admin/PropertyController.php`
  - `app/Http/Controllers/Admin/AcnooClientController.php`
  - `app/Http/Controllers/Admin/AcnooFeatureController.php`
  - `app/Http/Controllers/Admin/AcnooTestimonialController.php`
  - `app/Http/Controllers/Admin/AcnooBlogController.php`
  - `app/Http/Controllers/Admin/AcnooHighlightController.php`
- `app/Http/Controllers/Landlord/PropertyController.php` → override documentado de `deleteAll()` (§12 del spec-trait).
- Tests en `tests/Feature/Admin/AcnooActionsSmokeTest.php` y, si aplica, `tests/Feature/Landlord/LandlordAcnooActionsSmokeTest.php`.

### OUT
- No se toca `destroy()` (single delete) de `Property`/API; candidato a follow-up (DT-M10 residual).
- No se toca el IDOR bulk de `deleteAll`/`status()` (Seg-5) — queda para un spec posterior; `spec-policies-fase2` ya lo documentó como fuera de alcance de 2.1 y este spec mantiene esa decisión.
- No se tocan los controladores con `deleteAll` custom y transacción propia (`AcnooTenantController`, `AcnooLandlordController`, `AcnooLanguageController`) — su media se limpia dentro de su propia transacción y no pasan por el hook del trait.
- No se añaden columnas ni se cambian Policies.
- No se unifican los controladores duplicados (DT-C2).

### Cobertura objetivo
- 7 casos HTTP (F31–F37) + 1 unitario (U1).
- Los 6 controladores Admin row-by-row + override Landlord migrados al nuevo contrato.
- Test existente `test_delete_all_row_by_row_removes_media` en verde, ampliado a `units.images`.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Trait `HasAcnooActions` con camino row-by-row | Existe | Rediseñar el hook y la invocación |
| P2 | `spec-trait-acnoo-fase0` implementado (52/55 adoptantes) | Implementado | Reutilizar convenciones de hooks |
| P3 | Factories `Property`, `PropertyDetail`, `Unit` | Existen | Reutilizar en tests |
| P4 | `Storage::fake()` en suite smoke | Usado | Reutilizar |
| P5 | `PermissionSeeder` + `CreatesUsers` | Existen | Reutilizar |
| P6 | `Landlord\PropertyController::deleteAll` override documentado | Existe | Migrar manualmente |

---

## 4. Estructura propuesta

```
app/Traits/HasAcnooActions.php                          # MODIFICADO: invocación row-by-row + firma del hook
app/Http/Controllers/Admin/PropertyController.php       # MODIFICADO: deletingAcnooRow() retorna array de callables
app/Http/Controllers/Admin/AcnooClientController.php    # MODIFICADO: idem
app/Http/Controllers/Admin/AcnooFeatureController.php   # MODIFICADO: idem
app/Http/Controllers/Admin/AcnooTestimonialController.php # MODIFICADO: idem
app/Http/Controllers/Admin/AcnooBlogController.php      # MODIFICADO: idem
app/Http/Controllers/Admin/AcnooHighlightController.php # MODIFICADO: idem
app/Http/Controllers/Landlord/PropertyController.php    # MODIFICADO: deleteAll() captura+limpia tras delete
tests/Feature/Admin/AcnooActionsSmokeTest.php          # MODIFICADO: F31–F34 + units
tests/Feature/Landlord/LandlordAcnooActionsSmokeTest.php # MODIFICADO: F35 (si aplica)
tests/Unit/HasAcnooActionsMediaTest.php                # NUEVO: U1
```

Diff del trait (conceptual):

```php
public function deleteAll(Request $request)
{
    if ($this->acnooDeletesRowByRow()) {
        $this->acnooModel()::whereIn('id', $request->ids)->get()
            ->each(function (Model $row) {
                // Captura ANTES del delete: las relaciones cascadeOnDelete
                // (propertyDetail, units) desaparecen tras el delete.
                $cleanup = $this->deletingAcnooRow($row);

                $row->delete();

                // Limpieza DESPUÉS del delete exitoso.
                foreach (is_array($cleanup) ? $cleanup : [] as $callback) {
                    if ($callback instanceof \Closure) {
                        $callback();
                    }
                }
            });
    } else {
        $this->acnooModel()::whereIn('id', $request->ids)->delete();
    }

    return response()->json([
        'message' => $this->acnooDeleteMessage(),
        'redirect' => route($this->acnooRedirectRoute()),
    ]);
}
```

Firma del hook:

```php
/**
 * Captura la media de la fila y devuelve la lista de callables que la
 * limpian. Los callables se ejecutan DESPUÉS de `$row->delete()` exitoso.
 *
 * @return array<int, \Closure>
 */
protected function deletingAcnooRow(Model $row): array
{
    return [];
}
```

Ejemplo de migración (Property):

```php
protected function deletingAcnooRow($row): array
{
    $cleanup = [];

    if (! empty($row->cover_image)) {
        foreach ($row->cover_image as $image) {
            $dir = dirname($image);
            $cleanup[] = fn () => Storage::deleteDirectory($dir);
        }
    }

    if ($row->propertyDetail && ! empty($row->propertyDetail->floorplan_image)) {
        $dir = dirname($row->propertyDetail->floorplan_image);
        $cleanup[] = fn () => Storage::deleteDirectory($dir);
    }

    $unitImages = [];
    foreach ($row->units as $unit) {
        foreach ($unit->images ?? [] as $image) {
            if ($image) {
                $unitImages[] = $image;
            }
        }
    }
    if ($unitImages !== []) {
        $cleanup[] = fn () => Storage::delete($unitImages);
    }

    return $cleanup;
}
```

Ejemplo de migración (controlador simple, preservando `file_exists`):

```php
protected function deletingAcnooRow($row): array
{
    $image = $row->image;

    if ($image && file_exists($image)) {
        return [fn () => Storage::delete($image)];
    }

    return [];
}
```

Override Landlord (conceptual):

```php
public function deleteAll(Request $request)
{
    $properties = Property::whereIn('id', $request->ids)->get();

    foreach ($properties as $property) {
        // Captura ANTES del delete (incluye units por cascadeOnDelete).
        $cleanup = $this->capturePropertyMedia($property); // o inline

        if ($property->rent !== null) {
            return response()->json([
                'message' => __('Your property is already rented by tenant. Cannot delete it until remove the tenant first'),
            ], 400);
        }

        $property->delete();

        foreach ($cleanup as $callback) {
            $callback();
        }
    }

    return response()->json([
        'message' => __('Selected property deleted successfully'),
        'redirect' => route('landlord.properties.index'),
    ]);
}
```

> Nota: el bloqueo por propiedad rentada se evalúa **antes** del delete; si se bloquea, no se borra media ni fila.

---

## 5. Decisiones técnicas

- **Framework:** Laravel 10, PHPUnit 10, `RefreshDatabase`.
- **DB:** MySQL `dbrentpro_test`.
- **Auth en tests:** `actingAs($user)` (web).
- **Contrato del hook:** `deletingAcnooRow(Model $row): array`. Devuelve lista de `\Closure` (o `[]`). El trait ignora valores no-array y no-closure (compatibilidad defensiva).
- **Orden garantizado:** captura → delete → cleanup. Si `$row->delete()` lanza, los callables no se ejecutan y la media se preserva.
- **Relaciones cascade:** se leen dentro del hook (antes del delete), capturando valores escalares/arrays en los closures. No se accede a relaciones dentro de los closures.
- **`file_exists`:** se preserva en los controladores simples (evaluado antes de armar el closure).
- **Compatibilidad:** el valor por defecto del hook pasa de `void` a `[]`. Todos los adoptantes que lo sobrescriben deben devolver `array`. PHPStan detectará los que no lo hagan.
- **Mocks obligatorios:** `Storage::fake()`; categorías/propiedades vía factories.

---

## 6. Catálogo de casos

Prefijo `F` (feature transversal). Los casos que documentan el comportamiento actual (buggy) se marcan `@group characterization`.

### 6.1 Limpieza de media tras delete exitoso (Admin) (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F31 | Row-by-row limpia `cover_image` | Propiedad con `cover_image = ['properties/101/cover.jpg']` | `POST /admin/properties/delete-all` con su id | 200; fila borrada; directorio `properties/101` ausente |
| F32 | Row-by-row limpia `floorplan_image` | Propiedad con `PropertyDetail.floorplan_image = 'properties/102/floorplan.jpg'` | `POST /admin/properties/delete-all` | 200; fila borrada; directorio `properties/102` ausente |
| F33 | Row-by-row limpia `units.images` | Propiedad con una unidad cuya `images = ['units/7/img.jpg']` | `POST /admin/properties/delete-all` | 200; fila borrada; archivo `units/7/img.jpg` ausente (caso cascadeOnDelete) |
| F34 | Row-by-row limpia imagen simple | `AcnooClient` con `image = 'clients/x.png'` existente | `POST /admin/clients/delete-all` | 200; fila borrada; archivo `clients/x.png` ausente |

### 6.2 Preservación de media ante fallo de delete (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | Si `delete()` lanza, no se limpia media | Mock de modelo cuyo `delete()` lanza `\RuntimeException`; media preexistente en `Storage::fake()` | Se invoca el camino row-by-row del trait con ese modelo | La excepción se propaga; los archivos/directorios siguen presentes en el disco falso |

### 6.3 Override Landlord (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F35 | Landlord deleteAll limpia media tras delete | Propiedad del landlord con media | `POST /landlord/properties/delete-all` | 200; fila borrada; media ausente |
| F36 | Landlord deleteAll no limpia si rentada | Propiedad del landlord con `rent !== null` y media | `POST /landlord/properties/delete-all` | 400; fila y media preservadas |
| F37 | Controlador simple preserva `file_exists` | Imagen inexistente en disco | `POST /admin/clients/delete-all` | 200; fila borrada; sin error por archivo ausente |

### Tests de caracterización

| ID | Bug caracterizado | Fase que lo romperá |
|----|-------------------|---------------------|
| — | Ninguno nuevo. DT-M10 se remedia en este spec; el comportamiento observable (qué se borra) se preserva. | Fase 2 / 2.6 |

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `HasAcnooActions::deleteAll()` captura el resultado de `deletingAcnooRow()` antes de `$row->delete()` y ejecuta los callables después.
- [x] La firma del hook es `protected function deletingAcnooRow(Model $row): array` y el default devuelve `[]`.
- [x] Los 6 controladores Admin row-by-row devuelven `array` de `\Closure` (o `[]`).
- [x] `Landlord\PropertyController::deleteAll()` captura media, bloquea si rentada, borra y luego limpia.
- [x] `test_delete_all_row_by_row_removes_media` en verde, ampliado a `units.images`.
- [x] U1 en verde: si `delete()` lanza, la media se preserva.
- [x] F35 y F36 en verde (override Landlord).
- [x] `php artisan test tests/Feature/Admin/AcnooActionsSmokeTest.php` en verde.
- [x] `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.
- [x] `.analisis/deuda-tecnica.md` / `plan-accion.md`: DT-M10 marcado como mitigado con referencia al spec.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Closures que capturen `$row` en lugar de valores escalares accederían a relaciones ya cascadeadas | Alto | Capturar arrays/strings dentro del hook antes de devolver los closures (decisión fijada en §4) |
| Un callable que lance deja la fila borrada pero media parcial | Medio | Aceptable: mejora frente al estado actual (media antes que fila). Opcionalmente envolver cada callable en try/catch en implementación |
| Adoptantes que no actualicen la firma rompen PHPStan/llamadas | Bajo | PHPStan detecta; migración mecánica de 6 + override |
| Tests que mockean `delete()` pueden ser frágiles | Bajo | U1 sobre un modelo simple o trait aislado |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

---

## 9. Estimación

- Trait + 6 controladores Admin: 0.5–1 día.
- Override Landlord: 0.5 día.
- Tests (F31–F37, U1): 1 día.
- Total: 2–2.5 días.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 2 / 2.6
- `.analisis/deuda-tecnica.md` → DT-M10
- `.agents/spec-impl/spec-trait-acnoo-fase0.md` → §12 (override Landlord), §378 (deuda DT-M10)
- `.agents/spec/spec-conventions.md`
- `.agents/spec/spec-template.md`
