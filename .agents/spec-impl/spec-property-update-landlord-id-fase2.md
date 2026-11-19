---
id: spec-property-update-landlord-id-fase2
titulo: "AcnooPropertyController::update no reasigna landlord_id — Fase 2 / 2.2"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 2 / 2.2) — Seg-5
cobertura: 6 casos (F25–F30) sobre preservación de ownership en update de Property
---

# Spec — `AcnooPropertyController::update` no reasigna `landlord_id` (Fase 2 / 2.2)

> Resumen: remediar el hallazgo Seg-5 residual en el que `update()` de Property sobrescribe `landlord_id` con `auth()->id()` (API y capa Landlord), robando la propiedad al usuario que edita. Se elimina la reasignación automática para preservar el propietario original; la capa Admin conserva la reasignación explícita por request, endurecida a usuarios con rol `landlord`.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 2 / 2.2) — `Seg-5`.

---

## 1. Objetivo

Garantizar que editar una `Property` **nunca** cambia su `landlord_id` salvo cuando un administrador lo reasigna explícitamente a otro landlord. Eliminar la transferencia silenciosa de ownership que hoy ocurre en `Api/AcnooPropertyController::update` y `Landlord/PropertyController::update` (`'landlord_id' => auth()->id()`), y cerrar el vector de asignación a usuarios no-landlord en `Admin/PropertyController::update`.

---

## 2. Alcance

### IN
- `app/Http/Controllers/Api/AcnooPropertyController.php` → `update()`: eliminar `'landlord_id' => auth()->id()`.
- `app/Http/Controllers/Landlord/PropertyController.php` → `update()`: eliminar `'landlord_id' => auth()->id()`.
- `app/Http/Controllers/Admin/PropertyController.php` → `update()`: mantener `'landlord_id' => $request->landlord_id`, pero endurecer la validación para que el valor sea un usuario con `role = landlord`.
- Tests HTTP end-to-end F25–F30 en `tests/Feature/Idor/PropertyOwnershipSmokeTest.php`.

### OUT
- No se toca `store()` (la creación sigue asignando `landlord_id = auth()->id()`).
- No se toca `update_status()` ni otros métodos sin `authorize()` (residual documentado; candidato a follow-up de 2.1).
- No se hace sweep de otros controladores con el mismo patrón (`Amenity`, `Facility`, `Expense`, etc.) — deuda residual para un spec futuro.
- No se añaden columnas ni se cambian Policies (ya cubiertas por 2.1).
- No se unifican los controladores duplicados (DT-C2, fuera de alcance).

### Cobertura objetivo
- 6 casos (F25–F30): 1 de caracterización (pre-fix falla) + 5 de verificación post-fix (happy paths y feature admin preservada).

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | `spec-policies-fase2` implementado (Policies + `authorize()` en update) | Implementado | Reutilizar; el IDOR cross-user ya bloqueado por Policy |
| P2 | `PropertyFactory` con `landlord_id` | Existe | Reutilizar en tests |
| P3 | `CreatesUsers` trait (`createLandlord`, `createAdmin`, `createSuperadmin`) | Existe | Reutilizar en tests |
| P4 | `Sanctum::actingAs` para tests API | Usado en ApiAuthSmokeTest | Reutilizar |
| P5 | `RefreshDatabase` en tests Feature | Disponible | Usar (estado aislado) |
| P6 | Rutas `PUT /api/landlord/properties/{id}`, `PUT /landlord/properties/{id}`, `PUT /admin/properties/{id}` | Existen (resource) | Reutilizar |
| P7 | `Category` disponible en tests | Factory / seeder | Crear vía `Category::factory()` o usar seeder |

---

## 4. Estructura propuesta

```
app/Http/Controllers/Api/AcnooPropertyController.php        # MODIFICADO: update() sin reasignación
app/Http/Controllers/Landlord/PropertyController.php        # MODIFICADO: update() sin reasignación
app/Http/Controllers/Admin/PropertyController.php          # MODIFICADO: validación landlord_id endurecida
tests/Feature/Idor/PropertyOwnershipSmokeTest.php          # NUEVO: F25–F30
```

Diff mínimo:

```php
// Api/AcnooPropertyController::update y Landlord/PropertyController::update
// ANTES:
$property->update([
    'landlord_id' => auth()->id(),
    'category_id' => $request->category_id,
    ...
]);

// DESPUÉS:
$property->update([
    'category_id' => $request->category_id,
    ...
]);
```

```php
// Admin/PropertyController::update — validación
// ANTES:
'landlord_id' => 'required|exists:users,id',

// DESPUÉS:
'landlord_id' => ['required', Rule::exists('users', 'id')->where('role', 'landlord')],
```

(requiere `use Illuminate\Validation\Rule;` en el controlador si no está importado.)

---

## 5. Decisiones técnicas

- **Framework:** Laravel 10, PHPUnit 10, `RefreshDatabase`.
- **DB:** MySQL `dbrentpro_test`.
- **Auth en tests:** `actingAs($user)` (web) y `Sanctum::actingAs($user, ['*'])` (API).
- **Ownership preservada:** tras el fix, `landlord_id` no se incluye en el payload de `update()`; el valor original de BD se mantiene.
- **Admin reasignación:** se conserva como funcionalidad de soporte, pero solo a usuarios con `role = landlord` (validación a nivel de request, no Policy).
- **Mocks obligatorios:** `Storage::fake()` para uploads; categorías vía factory o seeder.
- **Payload mínimo de update:** `category_id`, `full_name`, `phone` (únicos requeridos); el resto nullable.

---

## 6. Catálogo de casos

Prefijo `F` (feature transversal / IDOR). Los casos que documentan comportamiento actual (buggy) se marcan `@group characterization` hasta implementar el fix.

### 6.1 Preservación de ownership en `update` de Property (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F25 | API superadmin no roba ownership al editar | Propiedad P de Landlord B; superadmin autenticado (Sanctum) | `PUT /api/landlord/properties/{P}` con payload válido | 200; `P.landlord_id` sigue siendo B — **caracterización del bug pre-fix** (hoy pasaría a superadmin) |
| F26 | API landlord edita su propiedad sin cambiar owner | Propiedad P de Landlord A; Landlord A autenticado (Sanctum) | `PUT /api/landlord/properties/{P}` con payload válido | 200; `P.landlord_id` sigue siendo A |
| F27 | Web landlord edita su propiedad sin cambiar owner | Propiedad P de Landlord A; Landlord A autenticado (web) | `PUT /landlord/properties/{P}` con payload válido | redirect/200; `P.landlord_id` sigue siendo A |
| F28 | Admin reasigna propiedad a otro landlord | Propiedad P de Landlord B; Admin autenticado (web); Landlord C existente | `PUT /admin/properties/{P}` con `landlord_id = C` | 200/redirect; `P.landlord_id` = C (feature preservada) |
| F29 | Admin no puede reasignar a usuario no-landlord | Propiedad P; Admin autenticado; usuario U con role `tenant` | `PUT /admin/properties/{P}` con `landlord_id = U` | 422 validación fallida; `P.landlord_id` sin cambio |
| F30 | API ignora `landlord_id` enviado en el payload | Propiedad P de Landlord A; Landlord A autenticado (Sanctum) | `PUT /api/landlord/properties/{P}` con `landlord_id = <otro>` en el body | 200; `P.landlord_id` sigue siendo A (campo no aceptado) |

### Tests de caracterización

| ID | Bug caracterizado | Fase que lo romperá |
|----|-------------------|---------------------|
| F25 | Seg-5 — `update()` sobrescribe `landlord_id` con `auth()->id()` (robo de ownership) | Fase 2 / 2.2 |

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `Api/AcnooPropertyController::update()` ya no incluye `'landlord_id' => auth()->id()`.
- [x] `Landlord/PropertyController::update()` ya no incluye `'landlord_id' => auth()->id()`.
- [x] `Admin/PropertyController::update()` mantiene la reasignación, pero valida `landlord_id` como usuario con `role = landlord`.
- [x] `store()` en las tres capas sigue asignando `landlord_id = auth()->id()` (sin regresión).
- [x] F25 convertido de `@group characterization` a test de bloqueo tras el fix (landlord_id permanece B).
- [x] F26, F27, F28, F30 en verde.
- [x] F29 en verde (si se acepta el endurecimiento de Q2).
- [x] `php artisan test tests/Feature/Idor/PropertyOwnershipSmokeTest.php` en verde.
- [x] `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.
- [x] `tests/README.md` actualizado: listar F25–F30 en la tabla de caracterización/ownership.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Otros controladores con el mismo patrón siguen reasignando ownership en `update()` | Medio | Documentado como fuera de alcance; spec futuro para sweep |
| `update_status()` (API) sigue sin `authorize()` → IDOR de status | Medio | Residual; candidato a follow-up de 2.1 (no incluido aquí) |
| Admin con permiso `properties-update` puede reasignar a cualquier landlord | Bajo | Validación de rol; permisos Spatie ya restringen el acceso |
| Tests HTTP con uploads requieren `Storage::fake()` y categorías válidas | Bajo | Payload mínimo (solo campos requeridos) + factory de Category |
| `Rule` no importado en Admin/PropertyController | Bajo | Añadir `use Illuminate\Validation\Rule;` |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling: variantes Landlord+API, endurecimiento Admin, scope solo Property, `update_status` fuera de alcance, tests HTTP en archivo nuevo).

---

## 9. Estimación

- Quitar reasignación en 2 controladores + validación Admin: 0.5 día.
- Tests F25–F30 + factories: 1–1.5 días.
- README + verificación CI: 0.25 día.
- Total: 1.75–2.25 días.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 2 / 2.2
- `.analisis/seguridad.md` → Seg-5
- `.agents/spec/spec-conventions.md`
- `.agents/spec-impl/spec-policies-fase2.md`
- `tests/README.md`
- `app/Http/Controllers/{Admin,Landlord,Api}/` (PropertyController / AcnooPropertyController)
- `app/Models/Property.php`
- `database/factories/PropertyFactory.php`