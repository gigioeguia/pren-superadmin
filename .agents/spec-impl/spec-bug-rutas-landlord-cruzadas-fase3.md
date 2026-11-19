---
id: spec-bug-rutas-landlord-cruzadas-fase3
titulo: "Bug rutas landlord.php cruzadas (properties/filter ↔ delete-all) (Fase 3 / 3.6)"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 3 / 3.6) — .analisis/seguridad.md (Seg-15)
cobertura: Alinear URI/método/nombre del par properties en routes/landlord.php; actualizar tests smoke/medios que dependían de la ruta cruzada; verificar nombres de ruta
---

# Spec — Bug rutas `landlord.php` cruzadas (Fase 3 / 3.6)

> Resumen: corregir el único par cruzado de `routes/landlord.php` (líneas 15–16) para que `properties/filter` invoque `acnooFilter` (nombre `properties.filter`) y `properties/delete-all` invoque `deleteAll` (nombre `properties.delete-all`), igual que `admin.php` y el resto de landlord. Cierra Seg-15.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 3 / 3.6) — `.analisis/seguridad.md` (Seg-15).

---

## 1. Objetivo

Garantizar que en `routes/landlord.php` la URI, el método del controlador y el nombre de ruta coincidan para el par `properties`, eliminando el cruce actual (`properties/filter` → `deleteAll` con nombre `properties.delete-all`; `properties/delete-all` → `acnooFilter` con nombre `properties.filter`). Tras el cambio, el comportamiento observable por nombres de ruta (usados por vistas y middleware) se mantiene, pero las URIs dejan de estar invertidas.

---

## 2. Alcance

### IN
- `routes/landlord.php` líneas 15–16: intercambiar método y `->name()` para alinear URI/método/nombre con el patrón de `admin.php`.
- Auditoría documentada: `admin.php` y el resto de `landlord.php` verificados sin cruces (solo `properties` afectado).
- Tests:
  - `tests/Feature/Landlord/LandlordAcnooActionsSmokeTest.php`:
    - `test_landlord_delete_all_cleans_media_after_delete` y `test_landlord_delete_all_preserves_media_when_rented`: cambiar `/landlord/properties/filter` por `/landlord/properties/delete-all` (o `route('landlord.properties.delete-all')`).
    - `test_filter_route_runs_delete_all_due_to_crossed_route` (caracterización): reemplazar por aserción del comportamiento corregido (filter filtra, no borra) o eliminar y añadir casos nuevos.
  - Añadir caso de regresión de nombres de ruta (`landlord.properties.filter` → `acnooFilter`, `landlord.properties.delete-all` → `deleteAll`).
  - Casos explícitos: `POST /landlord/properties/filter` → 200 con filas filtradas; `POST /landlord/properties/delete-all` → borrado + media limpiada.
- `tests/README.md`: actualizar/retirar la entrada de caracterización Seg-15.

### OUT
- No se tocan otros controladores, rutas ni el trait `HasAcnooActions`.
- No se añade validación automatizada global de rutas.
- No se modifican middleware (`ExpiredMiddleware`, `DemoMode`) porque referencian por nombre de ruta.

### Cobertura objetivo
- 0 rutas cruzadas en `routes/landlord.php` (par `properties` alineado).
- Tests de landlord en verde (`php artisan test tests/Feature/Landlord/`).
- Caracterización Seg-15 retirada/actualizada y README sincronizado.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rutas cruzadas identificadas (`landlord.php:15–16`) | Existe | Sin acción |
| P2 | Smoke tests de landlord (media delete/filter) | Existen | Actualizar URIs a `delete-all` |
| P3 | Vistas usan nombres de ruta (`landlord.properties.filter`/`delete-all`) | Existe | Sin cambio (nombres se mantienen) |
| P4 | Middleware referencian por nombre de ruta | Existe | Sin cambio |

---

## 4. Estructura propuesta

```
routes/landlord.php                                             # MODIFICADO: líneas 15–16
tests/Feature/Landlord/LandlordAcnooActionsSmokeTest.php        # MODIFICADO: URIs + caracterización → comportamiento corregido
tests/README.md                                                # MODIFICADO: entrada Seg-15
```

---

## 5. Decisiones técnicas

- **Framework:** Laravel 10, PHPUnit 10, `RefreshDatabase` donde aplica.
- **Fix mínimo y simétrico a `admin.php`:**
  - `Route::post('properties/filter', [Landlord\PropertyController::class, 'acnooFilter'])->name('properties.filter');`
  - `Route::post('properties/delete-all', [Landlord\PropertyController::class, 'deleteAll'])->name('properties.delete-all');`
- **Nombres de ruta se mantienen** para no romper vistas (`resources/views/landlord/properties/index.blade.php`) ni middleware (`ExpiredMiddleware`, `DemoMode`).
- **Caracterización Seg-15** se retira/actualiza porque el comportamiento buggy deja de existir; no se mantienen aserciones del bug.
- **Auditoría:** solo `properties` está cruzado; `admin.php` y el resto de `landlord.php` están bien formados.

---

## 6. Catálogo de casos (prefijo `H` — landlord)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| H5 | Filter filtra (no borra) | Landlord autenticado con 2 properties | `POST /landlord/properties/filter` (sin `ids`) | `200` JSON con filas filtradas; ambas properties siguen en BD |
| H6 | Delete-all borra y limpia media | Landlord autenticado, 1 property sin renta, archivos en disco fake | `POST /landlord/properties/delete-all` con `ids=[id]` | `200`; property ausente en BD; archivos de cover/floorplan eliminados |
| H7 | Delete-all bloqueado si rentado | Landlord autenticado, property con renta activa | `POST /landlord/properties/delete-all` con `ids=[id]` | `400` JSON con mensaje de renta; property y archivos intactos |
| H8 | Regresión de nombres de ruta | — | Inspección de `routes/landlord.php` | `landlord.properties.filter` → `acnooFilter`; `landlord.properties.delete-all` → `deleteAll` |

### Tests de caracterización
Ninguno tras el fix: la caracterización `test_filter_route_runs_delete_all_due_to_crossed_route` (Seg-15) se retira/actualiza porque el comportamiento buggy deja de existir. Los casos H5–H8 documentan el comportamiento seguro esperado.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `routes/landlord.php` líneas 15–16 alineadas con `admin.php` (URI/método/nombre).
- [x] `php artisan test tests/Feature/Landlord/` en verde.
- [x] Caracterización Seg-15 retirada/actualizada y `tests/README.md` sincronizado.
- [x] `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.
- [x] Auditoría documentada: solo `properties` estaba cruzado.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Tests que usaban la URI cruzada deben actualizarse; si se olvida alguno, fallará por diseño | Bajo | Actualizar explícitamente los 3 tests listados en §2 |
| Frontend podría depender de URIs invertidas (no de nombres) | Bajo | Vistas usan `route(...)` por nombre; verificado en `resources/views/landlord/properties/index.blade.php` |
| Middleware podría referenciar URIs | Bajo | `ExpiredMiddleware` y `DemoMode` usan nombres de ruta; verificado |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

---

## 9. Estimación

- Ruta: 0.1 día.
- Tests (actualización + nuevos casos): 0.3 día.
- Documentación (README): 0.1 día.
- Total: ~0.5 día.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 3 / 3.6
- `.analisis/seguridad.md` → Seg-15
- `.agents/spec/spec-conventions.md`
- `.agents/skills/tests-caracterizacion.md`
- `routes/admin.php` (patrón de referencia, líneas 21–22)
- `routes/landlord.php` (líneas 15–16)
- `tests/Feature/Landlord/LandlordAcnooActionsSmokeTest.php`
- `tests/README.md`
- `app/Http/Middleware/ExpiredMiddleware.php`
- `app/Http/Middleware/DemoMode.php`
- `resources/views/landlord/properties/index.blade.php`
