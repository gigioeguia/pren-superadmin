---
id: spec-rutas-artisan-publicas-fase1
titulo: "Rutas Artisan públicas (/update, /cache-clear) — Fase 1 / 1.1"
estado: Implementado
fecha: 2026-11-18
origen: .analisis/plan/plan-accion.md (Fase 1 / 1.1) — Seg-1
cobertura: 6 casos (M1–M6) sobre /update y /cache-clear: anónimo, no-superadmin y superadmin
---

# Spec — Rutas Artisan públicas (`/update`, `/cache-clear`)

> Resumen: remediar Seg-1 moviendo las dos rutas Artisan (`GET /update`, `GET /cache-clear`) tras `auth` + `role:superadmin` (Spatie), preservando su comportamiento para superadmin y bloqueando a anónimos y demás roles. Convertir los tests de caracterización M1/M2 en tests de bloqueo.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 1 / 1.1) — `Seg-1`.

---

## 1. Objetivo

Eliminar la ejecución pública de comandos Artisan (`migrate`, `db:seed`, `cache:clear`, `config:clear`, `route:clear`, `view:clear`) restringiendo `/update` y `/cache-clear` a usuarios autenticados con rol `superadmin`, manteniendo el flujo de actualización por navegador para ese rol.

## 2. Alcance

### IN
- `GET /update`: migraciones + seed de `LanguageSeeder` (si no hay versión) + `Option version=2.1` + limpieza de cachés.
- `GET /cache-clear`: limpieza de cachés.
- Restricción de acceso por autenticación y rol `superadmin`.
- Conversión de `M1`/`M2` (caracterización) a tests de bloqueo + casos para no-superadmin y superadmin.

### OUT
- No se toca `AdminMiddleware` (Seg-2, Fase 1 / 1.2) ni otras rutas.
- No se cambia la lógica interna de los comandos Artisan ni el modelo `Option`.
- No se introducen permisos Spatie nuevos (se usa el rol `superadmin` existente).
- No se elimina el archivo `routes/maintenance.php` ni su `require` en `web.php`.
- No se añaden nombres de ruta nuevos.

### Cobertura objetivo
- 6 casos (M1–M6), 100 % de los endpoints afectados (`/update`, `/cache-clear`), con camino feliz y bloqueo cubiertos.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Tests smoke M1/M2 existentes | Existen en `tests/Feature/Maintenance/ArtisanRoutesSmokeTest.php` como `@group characterization` | Convertirlos a tests de bloqueo (ver §6) |
| P2 | Middleware `auth` y `role` (Spatie) registrados | Aliases en `app/Http/Kernel.php` | Ninguna |
| P3 | Rol Spatie `superadmin` y usuario de prueba | `PermissionSeeder` crea rol `superadmin` (guard `web`) | En tests, crear `User` y asignar rol `superadmin` |
| P4 | `RefreshDatabase` en tests | Usado por `ArtisanRoutesSmokeTest` | Mantener |
| P5 | Mock de `Artisan` facade | Usado en M1/M2 | Mantener para el camino feliz |

## 4. Estructura propuesta

```
routes/maintenance.php                                    # MODIFICADO: wrap en middleware
tests/Feature/Maintenance/ArtisanRoutesSmokeTest.php       # MODIFICADO: M1–M6
tests/README.md                                           # MODIFICADO: tabla de caracterización (Seg-1 sin casos)
```

## 5. Decisiones técnicas

- **Framework:** Laravel 10, PHPUnit 10, `RefreshDatabase`.
- **DB:** MySQL `dbrentpro_test` en CI (como el resto de la suite); tests no ejecutan migraciones reales.
- **Auth en tests:** `$this->actingAs($user)` con `User` cuya columna `role = 'superadmin'` y rol Spatie `superadmin` asignado.
- **Middleware aplicado:** `Route::middleware(['auth', 'role:superadmin'])->group(...)` dentro de `routes/maintenance.php`. La middleware `web` ya la aporta el `require` en `routes/web.php`.
- **No-superadmin de referencia:** un `User` con rol `admin` (Spatie + columna). El mismo bloqueo aplica a `landlord` y `tenant` por el middleware `role:superadmin`.
- **Mock obligatorio:** `Artisan` facade con `shouldReceive` en M5/M6 para no ejecutar migraciones/seeds/clear reales; en M1–M4 se mockea o se verifica `shouldNotReceive('call')`.
- **Anónimo:** redirect 302 a `route('login')` (`/login`) por `auth` middleware; `Artisan::call` no se invoca.
- **No-superadmin:** 403 por Spatie `RoleMiddleware`; `Artisan::call` no se invoca.
- **CSRF:** no aplica (rutas `GET`).

## 6. Catálogo de casos

### 6.1 Maintenance (`M`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| M1 | `/update` bloqueado a anónimo | Sin sesión | `GET /update` | 302 redirect a `/login`; `Artisan::call` no invocado |
| M2 | `/cache-clear` bloqueado a anónimo | Sin sesión | `GET /cache-clear` | 302 redirect a `/login`; `Artisan::call` no invocado |
| M3 | `/update` bloqueado a no-superadmin | Usuario autenticado con rol `admin` | `GET /update` | 403; `Artisan::call` no invocado |
| M4 | `/cache-clear` bloqueado a no-superadmin | Usuario autenticado con rol `admin` | `GET /cache-clear` | 403; `Artisan::call` no invocado |
| M5 | `/update` ejecutado por superadmin | Usuario autenticado con rol `superadmin`; `Artisan` mockeado | `GET /update` | 302 redirect a `/`; `Artisan::call('migrate')` invocado; `Option version=2.1` persistido (`assertDatabaseHas`) |
| M6 | `/cache-clear` ejecutado por superadmin | Usuario autenticado con rol `superadmin`; `Artisan` mockeado | `GET /cache-clear` | 302 (back); `Artisan::call('cache:clear')` invocado |

> Tras el fix, M1 y M2 dejan de ser `@group characterization`; Seg-1 queda remediado y no aparece en la lista de caracterizaciones.

### Tests de caracterización

Ninguno: Seg-1 queda remediado por este spec; los casos M1/M2 se convierten a tests de bloqueo (ya no documentan comportamiento inseguro).

## 7. Criterios de aceptación (Definition of Done)

- [x] `routes/maintenance.php` envuelve ambas rutas en `Route::middleware(['auth', 'role:superadmin'])`.
- [x] `GET /update` y `GET /cache-clear` devuelven 302 a `/login` para anónimos sin invocar `Artisan::call`.
- [x] `GET /update` y `GET /cache-clear` devuelven 403 para usuarios autenticados no-superadmin sin invocar `Artisan::call`.
- [x] Un superadmin puede ejecutar ambas rutas con `Artisan` mockeado; M5 verifica `Option version=2.1`.
- [x] `php artisan test tests/Feature/Maintenance/ArtisanRoutesSmokeTest.php` en verde.
- [x] Los casos M1/M2 ya no llevan `@group characterization`.
- [x] `tests/README.md` actualizado: Seg-1 sin casos de caracterización.
- [x] `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| El instalador (`safiull/laravel-installer`) depende de `/update` | Medio | Verificado: `UpdateController@updateProcess` no invoca `/update`; el flujo de actualización del instalador es independiente. |
| Ejecutar `migrate` desde web sigue siendo riesgoso aunque autenticado | Medio | Mitigado por restricción a `superadmin`; opción futura (Fase 4) mover a CLI/queue. |
| `role:superadmin` requiere rol Spatie asignado en tests | Bajo | Crear/asignar rol en el test; no depender de seeders. |
| `back()` en `/cache-clear` puede fallar en tests sin referer | Bajo | Mockear `Artisan` y aceptar 302 con `Location` vacío o usar `from('/')`. |

**Pendiente de decisión:** ninguno (todas las decisiones fijadas en grilling).

## 9. Estimación

- Rutas + tests: 0.5–1 día.
- Ajuste de `tests/README.md` y verificación CI: 0.5 día.
- Total: 1–1.5 días.

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 1 / 1.1
- `.analisis/seguridad.md` → Seg-1
- `.agents/spec/spec-conventions.md`
- `.agents/spec-impl/spec-tests-smoke-fase0.md` (M1, M2 originales)
- `tests/README.md`
