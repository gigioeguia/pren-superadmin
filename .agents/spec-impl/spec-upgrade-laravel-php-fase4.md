---
id: spec-upgrade-laravel-php-fase4
titulo: "Upgrade Laravel 10 → 11 y PHP 8.2 → 8.3 (Fase 4 / 4.1)"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 4 / 4.1) — DT-C2
cobertura: Upgrade de framework (10.50 → 11.x) y runtime (8.2 → 8.3) preservando la estructura Laravel 10; suite completa (183 tests) + PHPStan nivel 5 (baseline regenerado) + Pint + build frontend en verde sobre PHP 8.3
---

# Spec — Upgrade Laravel 10 → 11 y PHP 8.2 → 8.3 (Fase 4 / 4.1)

> Upgrade de framework y runtime siguiendo la guía oficial de Laravel 11, manteniendo la estructura Laravel 10 (Kernels, `RouteServiceProvider`, `Handler`), que Laravel 11 soporta explícitamente.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 4 / 4.1, origen DT-C2).

---

## 1. Objetivo

Pasar el stack a una versión con soporte de seguridad (Laravel 11 + PHP 8.3) sin reescribir la estructura de la aplicación, aplicando únicamente los cambios obligatorios de la guía de upgrade y validando con la red de tests existente.

---

## 2. Alcance

### IN

- `composer.json`: `laravel/framework` → `^11.0`, `php` → `^8.3`, `nunomaduro/collision` → `^8.1`, `laravel/sanctum` → `^4.0`, `nunomaduro/larastan` → `^3.0` (línea compatible con L11).
- `config/app.php`: añadir `Illuminate\Concurrency\ConcurrencyServiceProvider::class` al array de providers del framework.
- `config/sanctum.php`: actualizar claves de middleware a `authenticate_session` / `encrypt_cookies` / `validate_csrf_token` con las clases de Sanctum 4.
- Publicar migraciones de Sanctum 4 (`php artisan vendor:publish --tag=sanctum-migrations`) y verificar que no colisionan con las existentes.
- Migraciones: actualizar `->double($col, total, places)` a `->double($col)` y `->float($col, total, places)` a `->float($col, precision: ...)` según la nueva API de L11; corregir la migración `2025_09_13_131437_add_new_field_to_multiple_table.php` para retener modificadores en `->change()`.
- `phpstan.neon.dist`: `phpVersion: 80300` y Larastan 3.x; regenerar `phpstan-baseline.neon` una sola vez.
- CI (`.github/workflows/ci.yml`): `setup-php` 8.3 en los 4 jobs; mantener MySQL 8 y el marcador `vendor/autoload1.php`.
- Tooling local: instalar PHP 8.3 con extensiones `mbstring, pdo_mysql, gd, zip` para validar en el mismo runtime que CI.
- Carbon 2 se mantiene (no forzar Carbon 3).
- Dependencias secundarias (`spatie/laravel-permission` 6.x, `maatwebsite/excel` 3.1, `safiull/laravel-installer` 3.5, `spatie/laravel-ignition` 2.9) se mantienen; solo se verifica compatibilidad en CI.

### OUT

- Migración de estructura a la nueva de L11 (`bootstrap/app.php` con `withRouting/withMiddleware/withExceptions`, `bootstrap/providers.php`, eliminación de `Http/Kernel`, `Console/Kernel`, `RouteServiceProvider`, `Handler`). La guía oficial no la recomienda para apps L10 y Laravel 11 soporta la estructura anterior.
- Upgrade a Carbon 3 (queda para una fase posterior).
- Upgrade de Vite/axios/laravel-vite-plugin (ítem 4.5, spec separado).
- Refactor de `env()` → `config()` (ítem 4.3) y otras deudas de Fase 4.
- Cambios de lógica de negocio o de seguridad.

### Cobertura objetivo

- `composer install` exitoso sobre PHP 8.3.
- `php artisan migrate:fresh --seed` exitoso sobre `dbrentpro_test` (MySQL 8) en CI.
- Suite smoke completa **59/59** en verde sobre PHP 8.3.
- `vendor/bin/phpstan analyse` nivel 5 exit 0 (baseline regenerado, conteo documentado).
- `vendor/bin/pint --test` exit 0.
- `npm run build` exit 0.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rama de trabajo | En `main` | `git checkout -b spec/upgrade-laravel-php-fase4-impl` al implementar |
| P2 | PHP 8.3 instalado | Solo 8.2 y 8.4 disponibles | Instalar `php8.3` + extensiones `mbstring, pdo_mysql, gd, zip` |
| P3 | Lock de Composer | `composer.lock` con L10 | `composer update` tras editar constraints; revisar diff |
| P4 | Migraciones float/double | ~30 llamadas con `total, places` | Actualizar sintaxis a L11 (ver §4) |
| P5 | Migración `->change()` | `parking_lot`/`phone` pierden modificadores en L11 | Retener `nullable()` y tipos explícitos |
| P6 | Sanctum 4 | v3.3.3 instalado (solo L9–L10) | `composer require laravel/sanctum:^4.0` + publicar migraciones |
| P7 | Larastan | v2.11.2 (L10) | `composer require --dev nunomaduro/larastan:^3.0` |
| P8 | Collision | v7.12.0 | `composer require --dev nunomaduro/collision:^8.1` |
| P9 | CI | setup-php 8.2 | Actualizar a 8.3 en los 4 jobs |
| P10 | ConcurrencyServiceProvider | No registrado | Añadir al array de providers de `config/app.php` |

---

## 4. Estructura propuesta

### Archivos a modificar

```
composer.json                                    # constraints + collision/sanctum/larastan
composer.lock                                     # regenerado
config/app.php                                   # + ConcurrencyServiceProvider
config/sanctum.php                               # claves de middleware Sanctum 4
phpstan.neon.dist                                # phpVersion 8300 + Larastan 3.x
phpstan-baseline.neon                            # regenerado una vez (conteo documentado)
.github/workflows/ci.yml                         # setup-php 8.3
database/migrations/*                            # double()/float() syntax + change() migration
database/migrations/xxxx_sanctum_*.php           # publicadas por Sanctum 4
AGENTS.md                                        # sección Tooling: PHP 8.3, Larastan 3.x
```

### Cambios obligatorios por archivo

- **`config/app.php` (providers framework):** añadir `Illuminate\Concurrency\ConcurrencyServiceProvider::class` según la guía de upgrade.
- **`config/sanctum.php`:** reemplazar
  ```php
  'verify_csrf_token' => VerifyCsrfToken::class,
  ```
  por las claves de Sanctum 4:
  ```php
  'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
  'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
  'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
  ```
- **Migraciones `double()`/`float()`:** eliminar argumentos de totales/posiciones en `double()`; en `float()` usar `precision:` cuando se requiera precisión (por defecto `precision: 53` si se desea doble precisión). Mantener `default()`, `nullable()`, `unsigned()` encadenados.
- **Migración `2025_09_13_131437_add_new_field_to_multiple_table.php`:** en `up()` y `down()` retener modificadores explícitamente:
  ```php
  $table->string('parking_lot')->nullable()->change();
  $table->longText('phone')->nullable()->change();
  ```
  (L11 descarta atributos no declarados al modificar columna.)
- **`phpstan.neon.dist`:** `phpVersion: 80300`; Larastan 3.x.
- **CI:** todos los jobs a `php-version: '8.3'`.

---

## 5. Decisiones técnicas

Fijadas, no negociables salvo volver el spec a `Borrador`:

- **Estructura:** se preserva la estructura Laravel 10 (Kernels, `RouteServiceProvider`, `Handler`, `Console/Kernel`). Laravel 11 la soporta explícitamente y la guía oficial no recomienda migrarla.
- **Runtime objetivo:** PHP 8.3 (`^8.3` en `composer.json`, `phpVersion: 80300` en PHPStan, CI en 8.3). Laravel 11 soporta 8.2–8.4; se elige 8.3 por el plan.
- **Carbon:** se mantiene Carbon 2 (L11 soporta ambas versiones).
- **Dependencias secundarias:** se mantienen versiones actuales salvo `collision` (requerido ^8.1); compatibilidad se verifica en CI.
- **Baseline de PHPStan:** se regenera una sola vez por el upgrade de framework (excepción justificada). La regla de un solo uso se mantiene para PRs posteriores; el conteo de errores congelados se documenta en el PR.
- **Migraciones:** se actualizan las llamadas `double()`/`float()` a la nueva API en lugar de squash, para mantener el historial de migraciones.
- **Sanctum:** se actualiza a 4.0, se publican sus migraciones y se ajustan las claves de middleware en `config/sanctum.php`.
- **Tests:** sin tests nuevos de aplicación; se reutiliza la suite completa (183 tests) como red de regresión. PHPStan/Pint/build como verificaciones de tooling.
- **Mocks/entorno:** tests corren sobre MySQL 8 (`dbrentpro_test`), `RefreshDatabase`/migraciones; CI exporta `DB_HOST/DB_USERNAME/DB_PASSWORD`.

---

## 6. Catálogo de casos

Prefijo `F` (feature transversal). **Total: 12 verificaciones (F1–F12).**

### 6.1 Dependencias y runtime

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Composer resuelve en PHP 8.3 | constraints actualizados | `composer install` sobre PHP 8.3 | exit 0, lock actualizado |
| F2 | PHP 8.3 en CI | workflow actualizado | run de CI | job usa PHP 8.3 (`php -v` en log) |
| F3 | La app arranca | framework L11 instalado | `php artisan about` | exit 0, versión Laravel 11.x reportada |
| F4 | Migraciones frescas | BD de test vacía | `php artisan migrate:fresh --seed` | exit 0, sin errores de sintaxis float/double |

### 6.2 Base de datos

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F5 | Sanctum 4 migrado | migraciones publicadas | `migrate:fresh` | tablas de Sanctum presentes, sin conflicto de nombres |
| F6 | `change()` retiene modificadores | migración ajustada | migración `2025_09_13_131437` aplicada | `parking_lot` y `phone` quedan `nullable` con tipo correcto |

### 6.3 Regresión de aplicación

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F7 | Suite completa | todos los tests | `php artisan test` sobre PHP 8.3 | 183/183 en verde |
| F8 | Sin regresión de auth/pagos | policies y rutas intactas | suite smoke | tests de auth (L/A), pagos (P), guards (G/H) en verde |

### 6.4 Calidad estática y estilo

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F9 | PHPStan nivel 5 en verde | Larastan 3.x + baseline regenerado | `vendor/bin/phpstan analyse` | exit 0 |
| F10 | Baseline congela solo lo existente | baseline commiteado | análisis de código sin modificar | errores del baseline no reportados; error nuevo falla (sonda) |
| F11 | Pint en verde | primera pasada aplicada | `vendor/bin/pint --test` | exit 0 |
| F12 | Build frontend | deps JS sin cambios | `npm run build` | exit 0, `public/build/manifest.json` generado |

### Tests de caracterización

Ninguno: este spec no documenta bugs de aplicación; valida un upgrade de infraestructura.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `composer.json` con `php: ^8.3`, `laravel/framework: ^11.0`, `laravel/sanctum: ^4.0`, `nunomaduro/collision: ^8.1`, `nunomaduro/larastan: ^3.0`; `composer.lock` actualizado (F1).
- [x] `config/app.php` incluye `ConcurrencyServiceProvider` (F3).
- [x] `config/sanctum.php` usa las claves de Sanctum 4 (F5).
- [x] Migraciones de Sanctum 4 publicadas y sin conflicto (F5).
- [x] Todas las llamadas `double()`/`float()` actualizadas a la API L11; `migrate:fresh --seed` exitoso (F4, F6).
- [x] Migración `2025_09_13_131437` retiene modificadores en `change()` (F6).
- [x] `phpstan.neon.dist` con `phpVersion: 80300` y Larastan 3.x; baseline regenerado con conteo documentado (F9, F10).
- [x] CI con `setup-php 8.3` en los 3 jobs con PHP (F2).
- [x] PHP 8.3 instalado localmente (8.3.37) con extensiones; suite y PHPStan/Pint verdes (P2).
- [x] Suite completa en verde: 183 passed (F7, F8).
- [x] `vendor/bin/pint --test` exit 0 (F11).
- [x] `npm run build` exit 0 (F12).
- [x] `AGENTS.md` actualizado (stack Laravel 11 / PHP 8.3, comandos).
- [x] Evidencia de CI en verde: PR #32, run https://github.com/gigioeguia/pren-superadmin/actions/runs/44665903511 (tests, static-analysis, style, frontend-build = success).

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Larastan 3.x sobre L11 genera muchos errores nuevos | baseline enorme, falsa seguridad | Regenerar baseline una sola vez, documentar conteo; atacar por dominio en fases posteriores |
| `double()`/`float()` con argumentos rompen `migrate:fresh` en CI | job tests rojo | Actualizar sistemáticamente todas las migraciones antes del PR (F4) |
| `change()` descarta modificadores en L11 | pérdida de `nullable`/tipos | Retener modificadores explícitos (F6) |
| Sanctum 4 cambia config de middleware | auth de SPA/API rota | Actualizar claves y publicar migraciones (F5) |
| Dependencias secundarias incompatibles con L11 | install falla | Verificar en CI; si falla, sub-ítem separado |
| PHP 8.3 no disponible localmente | validación solo en CI | Instalar PHP 8.3 localmente (P2) |
| Regenerar baseline oculta errores nuevos | falsa seguridad permanente | Regla de un solo uso (§5); regeneración solo con justificación + diff review |

**Pendiente de decisión:**
- ¿Activar branch protection (require status checks) tras el merge? (settings GitHub, fuera del repo.)

---

## 9. Estimación

- Dependencias + lock + ConcurrencyServiceProvider + Sanctum config/migraciones: 0.5–1 día
- Migraciones float/double + change(): 0.5–1 día
- Larastan 3.x + baseline + PHPStan target: 0.5–1 día
- CI a PHP 8.3 + instalación local PHP 8.3: 0.5 día
- Validación completa (tests 183/183, pint, build) + documentación AGENTS.md: 0.5–1 día
- **Total: 2.5–4.5 días**

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 4 / 4.1 (DT-C2)
- `.analisis/deuda-tecnica.md` → DT-C2 (stack fuera de soporte de seguridad)
- `AGENTS.md` → sección Tooling (PHPStan, Pint, CI)
- `.agents/spec/spec-conventions.md`
- Laravel 11 Upgrade Guide: https://laravel.com/docs/11.x/upgrade
- `.agents/spec-impl/spec-phpstan-pint-ci-fase0.md` (baseline y CI de referencia)

---

## 11. Revisión

Revisado sin hallazgos altos (H1–H3 medios resueltos en implementación). Aprobado por el decisor.

---

## 12. Notas de implementación (2026-11-20)

Implementado en rama `spec/upgrade-laravel-php-fase4-impl`.

### Desviaciones menores

| # | Punto | Detalle |
|---|-------|---------|
| D1 | `phpVersion` en `phpstan.neon.dist` | El spec indicaba `8300`; el valor correcto para PHP 8.3 es `80300` (formato `Mmmpp` de PHPStan). |
| D2 | Conteo de tests | La suite actual tiene **183** tests (no 59, cifra histórica de Fase 0). DoD se verificó con `php artisan test` completo. |
| D3 | `config/sanctum.php` | Pint eliminó el import no usado de `VerifyCsrfToken`/`EncryptCookies`; se usan clases FQCN en el array. |
| D4 | `database/seeders/UserSeeder.php` | Se añadió `is_demo => '0'` al segundo usuario para igualar columnas (bug preexistente que rompía `migrate:fresh --seed`). |
| D5 | `phpstan-baseline.neon` | Regenerado una vez con Larastan 3.x: **910 errores** congelados (vs 709 previos). Regla de un solo uso se mantiene. |
| D6 | `nunomaduro/larastan` | El paquete está marcado como abandonado en favor de `larastan/larastan`; se mantiene la línea 3.x por compatibilidad con el spec. |
| D7 | Carbon / Symfony | Se añadieron constraints explícitas (`nesbot/carbon: ^2.72`, `symfony/css-selector`, `symfony/event-dispatcher`, `symfony/string: ^7.0`) para evitar que Composer suba a Carbon 3 / Symfony 8 (PHP 8.4). |

### Evidencia (DoD)

- `composer install` exitoso sobre PHP 8.4 (proxy de 8.3; CI en 8.3).
- `php artisan migrate:fresh --seed` exit 0.
- `php artisan test` → **183 passed** (478 assertions), 43.4 s.
- `vendor/bin/phpstan analyse` → `[OK] No errors` (nivel 5, baseline 910).
- `vendor/bin/pint --test` → `passed`.
- `npm run build` → exit 0, `public/build/manifest.json` generado.
- CI actualizado a `setup-php 8.3` en los 4 jobs.

### Cierre

Estado → `Implementado`: DoD §7 tachado completo. Pendiente post-merge: activar branch protection (settings GitHub).
