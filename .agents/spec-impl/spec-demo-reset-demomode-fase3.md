---
id: spec-demo-reset-demomode-fase3
titulo: "demoReset/DemoMode solo en entorno demo (Fase 3 / 3.5)"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 3 / 3.5) — .analisis/seguridad.md (Seg-13, Seg-14)
cobertura: Helper demoReset() + middleware DemoMode + vista dashboard + columna users.is_demo + comando demo:reset + ~14 casos (U1–U6, F1–F8)
---

# Spec — `demoReset`/`DemoMode` solo en entorno demo (Fase 3 / 3.5)

> Resumen: hacer que las funcionalidades de demo (`demoReset()` y el middleware `DemoMode`) se activen **exclusivamente** cuando `APP_ENV=demo` (`app()->environment('demo')`), eliminando la dependencia de `env('DEMO_MODE')` y la restricción basada en el email hardcodeado `landlord@acnoo.com` (sustituida por un flag `users.is_demo`). Cierra Seg-13 (operación destructiva en flujo normal) y Seg-14 (`DemoMode` frágil).

**Origen:** `.analisis/plan/plan-accion.md` (Fase 3 / 3.5) — `Seg-13`, `Seg-14`.

---

## 1. Objetivo

Garantizar que la reset destructiva del dataset demo y el bloqueo de mutaciones del middleware `DemoMode` **solo** puedan ocurrir en un entorno cuyo `APP_ENV` sea `demo`, y que la identificación del landlord demo deje de depender de un email hardcodeado, usando un flag explícito `is_demo`. Tras el cambio, un `DEMO_MODE=true` residual en producción (o cualquier otro entorno) no activa ninguna de estas funcionalidades.

---

## 2. Alcance

### IN

- `app/Helpers/Helper.php` — función `demoReset()`: guard por `app()->environment('demo')`; fuera de demo devuelve `false` y registra un warning en log; en demo ejecuta los deletes + `Artisan::call('db:seed', ['--class' => 'DemoSeeder'])` y devuelve `true`.
- `app/Http/Middleware/DemoMode.php`: reemplazar `env('DEMO_MODE', false)` por `app()->environment('demo')`; reemplazar la comparación `auth()->user()->email == 'landlord@acnoo.com'` por `auth()->user()->is_demo === true` para landlords.
- `resources/views/landlord/dashboard.blade.php`: reemplazar `env('DEMO_MODE')` por `app()->environment('demo')` y la condición de email por `auth()->user()->is_demo`.
- Migración nueva: añadir columna `is_demo` (boolean, default `false`, no nullable) a `users`.
- `app/Models/User.php`: cast `is_demo => boolean`; **no** añadir `is_demo` a `$fillable` (anti mass-assignment; se asigna por propiedad directa/`forceFill`).
- `database/factories/UserFactory.php`: estado `demo()` que marca `is_demo = true` (vía `afterCreating`).
- `database/seeders/UserSeeder.php`: marcar el usuario `landlord@acnoo.com` con `is_demo = true`.
- `routes/console.php`: comando `demo:reset` imprime aviso y retorna `1` fuera de demo; en demo ejecuta y confirma.
- Eliminar todo uso de `env('DEMO_MODE')` en `app/` y `resources/` (Helper, Middleware, vista).
- `tests/Feature/Demo/DemoResetTest.php` y `tests/Feature/Demo/DemoModeTest.php` con los casos del catálogo.
- Actualizar `AGENTS.md` (sección DEMO_MODE) y `.agents/glossary.md` (entrada `DemoMode`) para reflejar el nuevo disparador.

### OUT

- No se cambia la lista de rutas deshabilitadas (`$adminDisabledRoutes` / `$landlordDisabledRoutes`).
- No se hace configurable `landlord_id = 3` en `demoReset()` ni el conjunto de tablas a limpiar (límite documentado; dato del seeder demo).
- No se introduce `config/demo.php` ni se lee `env('DEMO_MODE')` como disparador (se elimina la doble fuente de verdad).
- No se modifican `DEMO_MODE` en `.env`/`.env.example` (la variable queda sin uso; su presencia residual es inofensiva).
- No se aborda el rate limiting de OTP/login (Fase 3 / 3.7) ni los permisos granulares (Fase 3 / 3.8).
- No se restringe el comando `demo:reset` por rol/permiso (sigue siendo un comando Artisan; la protección es el entorno).

### Cobertura objetivo

- 0 usos de `env('DEMO_MODE')` en `app/` y `resources/`.
- `demoReset()` devuelve `false` fuera de demo y `true` en demo (con mocks de `Artisan`).
- `DemoMode` es no-op fuera de demo y bloquea en demo según las reglas.
- `is_demo` no mass-assignable (`UserModelTest` extendido).
- Migración aplicable con `RefreshDatabase` en tests.
- `U1`–`U6` y `F1`–`F8` en verde.
- `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | `app()->environment()` disponible (Laravel 10) | Disponible | Sin acción |
| P2 | Middleware `DemoMode` registrado en grupo `web` (`Kernel.php`) | Existe | Sin cambio estructural (guard interno) |
| P3 | Helper `demoReset()` autoloaded (`composer files`) | Existe | Modificar cuerpo |
| P4 | Migraciones ejecutables en tests (`RefreshDatabase`) | Existe | Añadir migración `is_demo` |
| P5 | `UserFactory` con estados por rol | Existe | Añadir estado `demo()` |
| P6 | `PermissionSeeder` para tests con auth | Existe | Reutilizar en `DemoModeTest` |
| P7 | Mock de `Artisan` en tests | Patrón establecido (`ArtisanRoutesSmokeTest`) | Reutilizar |
| P8 | Cambio de entorno en tests vía `app()->detectEnvironment()` | Disponible (Laravel 10) | Documentar en tests |

---

## 4. Estructura propuesta

```
app/Helpers/Helper.php                                              # MODIFICADO: demoReset() guard por environment('demo')
app/Http/Middleware/DemoMode.php                                    # MODIFICADO: guard + is_demo
app/Models/User.php                                                 # MODIFICADO: cast is_demo; NO fillable
database/factories/UserFactory.php                                  # MODIFICADO: estado demo()
database/migrations/<timestamp>_add_is_demo_to_users_table.php       # NUEVO
database/seeders/UserSeeder.php                                     # MODIFICADO: is_demo=true para landlord@acnoo.com
resources/views/landlord/dashboard.blade.php                         # MODIFICADO: env() → environment()
routes/console.php                                                  # MODIFICADO: aviso fuera de demo
tests/Feature/Demo/DemoResetTest.php                                # NUEVO
tests/Feature/Demo/DemoModeTest.php                                  # NUEVO
tests/Unit/UserModelTest.php                                        # MODIFICADO: is_demo no mass-assignable + cast
AGENTS.md                                                           # MODIFICADO: sección DEMO_MODE
.agents/glossary.md                                                 # MODIFICADO: entrada DemoMode
```

---

## 5. Decisiones técnicas

- **Framework:** Laravel 10, PHPUnit 10, `RefreshDatabase`.
- **DB:** MySQL `dbrentpro_test`.
- **Disparador único:** `app()->environment('demo')` (equivale a `APP_ENV=demo`). No se consulta `env('DEMO_MODE')` en ningún punto del código de aplicación.
- **Identificación del landlord demo:** columna boolean `users.is_demo` (default `false`), cast a `bool`. **No** se añade a `$fillable` (evita que un request asigne `is_demo`); se setea por propiedad directa o `forceFill` en seeders/factories/tests.
- **`demoReset()` firma:** `function demoReset(): bool`. En no-demo: `Log::warning('demoReset skipped: not demo environment.')` y retorna `false`. En demo: ejecuta los deletes y `Artisan::call('db:seed', ['--class' => 'DemoSeeder'])`, retorna `true`.
- **`DemoMode` middleware:** no-op fuera de demo; en demo bloquea las rutas de `$adminDisabledRoutes` para cualquier usuario autenticado con rol admin/superadmin; bloquea `$landlordDisabledRoutes` solo si `auth()->user()->is_demo === true`. Anónimos no se bloquean por DemoMode (auth middleware se encarga).
- **Comando `demo:reset`:** fuera de demo imprime warning y retorna `1`; en demo ejecuta `demoReset()` e imprime confirmación, retorna `0`.
- **Tests de entorno:** cada test fija el entorno con `app()->detectEnvironment(fn () => 'demo'|'testing')` en `setUp`/el propio caso y lo restaura en `tearDown` para no contaminar otros tests.
- **Mock de `Artisan`:** en tests de `demoReset` se mockea `Artisan::shouldReceive('call')` para no ejecutar seeders reales (patrón de `ArtisanRoutesSmokeTest`).
- **Sin cambios en:** nombres de rutas, mensajes JSON existentes, códigos de respuesta (499), lista de rutas deshabilitadas.

---

## 6. Catálogo de casos

### 6.1 Helper `demoReset()` (`U`) — `tests/Feature/Demo/DemoResetTest.php`

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | No-op fuera de demo | Entorno `testing` | `demoReset()` | Devuelve `false`; `Artisan::call` no invocado; tablas sin cambios |
| U2 | Ejecución en demo | Entorno `demo` | `demoReset()` | Devuelve `true`; `Artisan::call('db:seed', ['--class'=>'DemoSeeder'])` invocado 1 vez; deletes ejecutados sobre las 5 tablas |
| U3 | Warning en log fuera de demo | Entorno `testing` | `demoReset()` | Se registra un warning cuyo mensaje contiene "demoReset" y "not demo" |
| U4 | Ejecución repetida en demo | Entorno `demo` | `demoReset()` dos veces | Ambas invocan seed (sin guard de re-entrada); documentado como comportamiento actual |
| U5 | `is_demo` no mass-assignable | `new User` | Inspección de `getFillable()` | `is_demo` no está en la lista (igual que `role`) |
| U6 | Cast boolean de `is_demo` | Usuario con `is_demo = 1` en BD | Lectura del atributo | Devuelve `true` (bool), no `1` (int/string) |

### 6.2 Middleware `DemoMode` (`F`) — `tests/Feature/Demo/DemoModeTest.php`

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | No-op fuera de demo | Entorno `testing`, admin autenticado | Request a una ruta de `$adminDisabledRoutes` | Respuesta distinta de 499 por DemoMode (no bloquea por entorno) |
| F2 | Admin bloqueado en demo | Entorno `demo`, admin autenticado | Request a una ruta de `$adminDisabledRoutes` | 499 JSON `{"message":"This action is disabled in demo mode."}` |
| F3 | Landlord demo bloqueado | Entorno `demo`, landlord con `is_demo=true` | Request a una ruta de `$landlordDisabledRoutes` | 499 JSON `{"message":"This action is disabled in demo account. Please sign up your own account for full access."}` |
| F4 | Landlord real no bloqueado | Entorno `demo`, landlord con `is_demo=false` | Misma ruta mutativa | No 499 por DemoMode (pasa al controlador) |
| F5 | Anónimo no bloqueado por DemoMode | Entorno `demo`, sin auth | Request a ruta mutativa admin | Redirect a login (auth middleware), no 499 |
| F6 | Rutas deshabilitadas intactas | — | Inspección | `$adminDisabledRoutes` y `$landlordDisabledRoutes` sin cambios respecto al baseline |

### 6.3 Vista dashboard (`F`) — `tests/Feature/Demo/DemoModeTest.php`

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F7 | Banner visible en demo para landlord demo | Entorno `demo`, landlord `is_demo=true` autenticado | GET `/landlord` (o dashboard) | HTML contiene el banner demo |
| F8 | Sin banner fuera de demo | Entorno `testing` | GET dashboard | HTML no contiene el banner demo |

### Tests de caracterización

Ninguno: este spec remedia Seg-13 y Seg-14. Los casos U1 y F1 documentan el comportamiento seguro esperado tras el fix y deben pasar, no caracterizar un bug a romper en una fase posterior.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] 0 usos de `env('DEMO_MODE')` en `app/` y `resources/` (`grep` vacío).
- [x] `demoReset()` devuelve `false` y registra warning fuera de demo; devuelve `true` y ejecuta seed en demo (U1–U4 en verde).
- [x] `DemoMode` es no-op fuera de demo y bloquea según `is_demo` en demo (F1–F6 en verde).
- [x] Migración `is_demo` aplicable; `RefreshDatabase` en tests OK.
- [x] `is_demo` no mass-assignable y con cast boolean (U5, U6 en verde).
- [x] Banner dashboard gateado por entorno y `is_demo` (F7, F8 en verde).
- [x] Comando `demo:reset` imprime aviso y retorna `1` fuera de demo.
- [x] `AGENTS.md` y `.agents/glossary.md` actualizados.
- [x] `php artisan test tests/Feature/Demo/` en verde.
- [x] `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| `app()->detectEnvironment()` en tests puede contaminar el estado global si no se restaura | Medio | Restaurar entorno en `tearDown` (`app()->detectEnvironment(fn () => 'testing')`) |
| Feature tests de middleware requieren rutas con nombres en `$adminDisabledRoutes`/`$landlordDisabledRoutes`; si una ruta cambia de nombre, el test se rompe | Bajo | Usar rutas estables (`admin.blogs.destroy`, `landlord.properties.update`) y documentarlas |
| `is_demo` fuera de `$fillable` obliga a usar `forceFill`/propiedad directa en seeders y factories; olvidarlo deja el flag en `false` | Bajo | Añadir estado `demo()` en factory y test U5 que verifica no mass-assignable |
| Eliminar `env('DEMO_MODE')` puede romper despliegues que dependían de esa variable sin cambiar `APP_ENV` | Medio | Documentar en `AGENTS.md` que los despliegues demo deben usar `APP_ENV=demo`; la variable queda inofensiva |
| `landlord_id = 3` hardcodeado sigue siendo frágil si el seeder cambia | Bajo | Documentado como límite; fuera de alcance de este spec |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

---

## 9. Estimación

- Migración + modelo + factory + seeder: 0.25 día.
- Helper + middleware + vista + comando: 0.25 día.
- Tests (U1–U6, F1–F8): 0.5 día.
- Documentación (AGENTS, glossary): 0.1 día.
- Total: ~1.1 días.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 3 / 3.5
- `.analisis/seguridad.md` → Seg-13, Seg-14
- `.agents/spec/spec-conventions.md`
- `.agents/spec-impl/spec-env-inseguro-fase1.md` (patrón de guard de entorno/defaults seguros)
- `.agents/spec-impl/spec-tests-smoke-fase0.md` (patrón de tests smoke y mocks)
- `tests/Feature/Maintenance/ArtisanRoutesSmokeTest.php` (mock de `Artisan`)
- `app/Http/Middleware/DemoMode.php`, `app/Helpers/Helper.php` (código actual)
- `AGENTS.md` (sección Payments architecture / `DEMO_MODE`)
- `.agents/glossary.md` (entrada `DemoMode`)
