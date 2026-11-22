---
id: spec-controladores-residuales-fase5
titulo: "Residuales < 200: 2ª pasada Property, DashboardService, trait RespondsJson, arch-test (Fase 5 / 5.13, hijo 6)"
estado: Aprobado
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.13) — DT-C4
cobertura: Última milla del criterio "< 200 estricto": Admin/PropertyController 225, Landlord/PropertyController 200, Api/LandlordDashboardController 228, Controller.php base 235, y activación del arch-test permanente
---

# Spec — Controladores residuales + arch-test (Fase 5 / 5.13, hijo 6)

> Cierra el criterio `< 200` estricto: los 4 controladores restantes sobre el umbral (incluido el ya migrado Property, que necesita 2ª pasada) y el `Controller.php` base. Al final activa `ControllersLineLimitArchTest` con las 2 reglas del paraguas — a partir de aquí el criterio es auto-enforced.

**Paraguas:** `spec-unificar-capas-services-fase5.md` (§5.2 define las reglas del arch-test).

## 1. Objetivo

`Admin/PropertyController` (225), `Landlord/PropertyController` (200), `Api/LandlordDashboardController` (228) y `Controller.php` (235) quedan < 200; el arch-test queda activo en CI.

## 2. Alcance

### IN
- 2ª pasada Property (los 2 controladores ya migrados en Fase 4; recorte de presentación → `PropertyService::loadForShow`/helpers, sin tocar contrato).
- `app/Services/DashboardService.php` (solo Api/LandlordDashboard: index, property_chart, account_chart).
- `app/Traits/RespondsJson.php`: los 15 helpers `respondWith*` + el array `$message` legado salen de `Controller.php`.
- `tests/Unit/Controllers/ControllersLineLimitArchTest.php` (2 reglas) + test de reflexión de rutas del paraguas.

### OUT
- `Admin/AdminDashboardController` (129) y `Landlord/DashboardController` (159) — < 200, quedan tal cual (duplicación residual documentada en el paraguas §2 OUT).
- `Admin/TranslationController` y el resto de controladores < 200.
- Cambios de contrato observable en Property/Dashboard.

### Cobertura objetivo
- 12/12 controladores < 200 (evidencia `wc -l`); arch-test activo; suite + PHPStan + Pint en verde.

## 3. Pre-requisitos

| # | Pre-requisito | Estado | Acción |
|---|---|---|---|
| P1 | Hijos 1–5 mergeados | Pendiente | Este hijo es el último |
| P2 | Rutas muertas del paraguas (U1–U3) limpiadas | Pendiente | El test de reflexión no puede pasar con rutas muertas |
| P3 | Red de Property | Verde (F1–F29 spec 4.2 + ownership) | Sin regresiones en la 2ª pasada |
| P4 | `$message` usado por `Api/UserController` (4 refs L157–171) | Verificado | El array se MUEVE al trait, no se elimina |

## 4. Estructura propuesta

```
app/Services/DashboardService.php
app/Traits/RespondsJson.php
app/Http/Controllers/Controller.php            # ~20 líneas
app/Http/Controllers/Api/LandlordDashboardController.php   # < 200
app/Http/Controllers/Admin/PropertyController.php          # < 200 (2ª pasada)
app/Http/Controllers/Landlord/PropertyController.php       # < 200 (recorte trivial)
tests/Unit/Controllers/ControllersLineLimitArchTest.php
tests/Feature/Dashboard/ApiLandlordDashboardHttpTest.php   # caracterización previa
```

## 5. Decisiones técnicas

- **2ª pasada Property = solo presentación:** mover enriquecimiento de vistas/shape JSON a `PropertyService` (o helpers privados del service); las rutas/vistas/JSON quedan idénticos. Red: F1–F29 del spec 4.2 + `PropertyOwnershipSmokeTest` + `IdorPolicySmokeTest`.
- **`DashboardService` mínimo:** solo `Api/LandlordDashboardController` (228) cruza el umbral; admin/landlord web quedan (paraguas §2 OUT).
- **Trait `RespondsJson`:** los 15 `respondWith*` + `$message` se mueven **tal cual** (comportamiento idéntico; `$message` NO se elimina — lo consume `Api/UserController`). Follow-up fuera de scope: reemplazar `$message[n]` por claves de traducción.
- **Arch-test (paraguas §5.2), sin grandfather list:**
  1. Todo `.php` en `app/Http/Controllers/` (recursivo) < 200 líneas (`count(fileLines)`, sin contar saltos finales).
  2. Cero `DB::beginTransaction` / `DB::transaction(` en `app/Http/Controllers/`.
  Estilo `SeedersArchTest` (lee archivos, sin DB).
- Test de reflexión rutas↔métodos (cierre permanente del bug de rutas muertas, paraguas §6).

## 6. Catálogo de casos (prefijo `F` HTTP, `U` unit)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Dashboard api index | Sanctum landlord con datos | GET /api/v1/landlord/dashboard | 200 JSON shape actual congelado |
| F2 | Dashboard charts | Sanctum landlord | GET .../dashboard/property-chart y account-chart | 200 shapes actuales congelados |
| F3–F6 | Property 2ª pasada | red F1–F29 del spec 4.2 | re-ejecutar suite Property | verde sin cambios (regresión pura) |

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | Límite global | árbol de controladores actual | correr arch-test | 100% archivos < 200 líneas |
| U2 | Sin tx en controladores | ídem | grep `DB::beginTransaction\|DB::transaction(` | 0 matches en `app/Http/Controllers/` |
| U3 | Rutas ↔ métodos | todas las rutas declaradas (6 archivos) | reflexión | todo route name apunta a método existente |
| U4 | `$message` intacto | `Api/UserController` | llamar endpoints que usan `$this->message[0]`/`[6]` | mensajes idénticos tras el trait |

> F1–F2 son caracterizaciones nuevas (hoyo actual de dashboard api); U1–U3 son los arch-tests; U4 congela la compatibilidad del trait.

## 7. Criterios de aceptación (Definition of Done)

- [ ] `wc -l`: Admin/Property < 200, Landlord/Property < 200, Api/LandlordDashboard < 200, `Controller.php` < 30 líneas.
- [ ] `ControllersLineLimitArchTest` (U1–U2) y reflexión de rutas (U3) activos y en verde en CI.
- [ ] Caracterización F1–F2 en verde antes de migrar dashboard; red Property (F3–F6) en verde tras la 2ª pasada.
- [ ] `Api/UserController` sin cambio (U4).
- [ ] Suite + PHPStan + Pint en verde; `plan-accion.md` 5.13 cerrado con evidencia.

## 8. Riesgos

| Riesgo | Mitigación |
|--------|------------|
| 2ª pasada de Property reintroduce duplicación | Solo mueve presentación al service ya existente; red F1–F29 completa |
| Arch-test frágil (líneas con espacios/CRLF) | Conteo simple de líneas tipo `SeedersArchTest`; umbral fijo 200 |
| Alguien "arregla" el arch-test excluyendo archivos | Regla del paraguas: sin grandfather list; tocarlo requiere desviación documentada |

## 9. Estimación

- 3–5 días (dashboard 1, property 2ª pasada 1, trait 0.5, arch-tests 0.5, validación 0.5–1).

## 10. Referencias

- `spec-unificar-capas-services-fase5.md` (§5.2, §6) · `spec-unificar-capas-services-fase4.md` (red F1–F29)
- `.analisis/plan/plan-accion.md` → Fase 5 / 5.13 · `tests/Unit/Seeders/SeedersArchTest.php` (estilo)
