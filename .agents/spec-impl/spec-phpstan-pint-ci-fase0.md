---
id: spec-phpstan-pint-ci-fase0
titulo: "PHPStan (Larastan) nivel 5 + Pint + CI"
estado: Aprobado
fecha: 2026-11-10
origen: .analisis/plan/plan-accion.md (Fase 0 / 0.2) — DT-A4
cobertura: Análisis estático nivel 5 con baseline, estilo Pint y pipeline CI de 4 jobs (tests, phpstan, pint, frontend build) sobre la suite smoke existente
---

# Spec — PHPStan (Larastan) nivel 5 + Pint + CI

> Instalar la capa de calidad automática (análisis estático, estilo y CI) para que los bugs tipográficos y de tipo se detecten antes de mergear, y como criterio de salida de Fase 0 ("CI en verde").

**Origen:** `.analisis/plan/plan-accion.md` (Fase 0 / 0.2, origen DT-A4).

---

## 1. Objetivo

Que todo push/PR a `main` ejecute automáticamente tests + PHPStan nivel 5 + Pint + build frontend, de modo que ningún error de tipo, estilo o build llegue a `main` sin ser detectado.

---

## 2. Alcance

### IN
- Larastan 2.x en `require-dev` + `phpstan.neon.dist` (nivel 5, paths `app/`, `phpVersion: 80200`).
- Baseline `phpstan-baseline.neon` que congela los errores existentes (nivel 5 en verde **hoy**).
- `pint.json` (preset `laravel`) + primera pasada completa de formato en commit mecánico aislado.
- Workflow GitHub Actions `.github/workflows/ci.yml` con 4 jobs: `tests` (MySQL service), `static-analysis`, `style`, `frontend-build`.
- Composer scripts de atajo (`analyse`, `lint`, `lint:fix`).
- Documentación de los comandos en `AGENTS.md` (sección Tooling).

### OUT
- Subir nivel de PHPStan por encima de 5 (progresión a 6+ queda para Fase 4).
- Corregir los errores congelados en el baseline (se atacan por dominio en fases posteriores).
- Refactors de estilo manual más allá de lo que Pint aplica automáticamente.
- Branch protection en settings de GitHub (fuera del repo; queda como recomendación post-merge).

### Cobertura objetivo
- `vendor/bin/phpstan analyse` nivel 5 → **exit 0** (0 errores fuera del baseline).
- `vendor/bin/pint --test` → **exit 0**.
- CI ejecuta los 4 jobs en verde sobre un PR de prueba, incluyendo la suite smoke completa (60 tests).

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rama de trabajo | En `main` | `git checkout -b spec/phpstan-pint-ci-fase0-impl` al implementar (este draft se crea en `spec/phpstan-pint-ci-fase0-draft`) |
| P2 | Larastan en `require-dev` | No instalado | `composer require --dev nunomaduro/larastan:^2.0` (compatible Laravel 10 / phpstan 1.x; NO larastan 3.x — requiere Laravel 11) |
| P3 | `phpstan.neon.dist` + baseline | No existen | Config nivel 5 + `phpVersion: 80200` (CLI local es 8.4) + `--generate-baseline` |
| P4 | `pint.json` | No existe (Pint instalado, nunca ejecutado) | `{"preset": "laravel"}` |
| P5 | Workflow CI | No existe `.github/workflows/` | Crear `ci.yml` con 4 jobs |
| P6 | GitHub Actions operativo | Asumido (PR #1 funcionó en origin) | Verificar tab Actions del repo antes de confiar en el DoD de CI |

**Orden crítico:** la primera pasada de Pint (commit mecánico) debe integrarse **antes** del spec 0.3 (trait `acnooFilter`) para que el refactor no conflictúe con un diff de formato masivo.

---

## 4. Estructura propuesta

```
.github/workflows/ci.yml        # on: push(main) + PR; jobs: tests, static-analysis, style, frontend-build
phpstan.neon.dist               # level 5, paths [app], phpVersion 80200, incluye baseline
phpstan-baseline.neon           # generado una vez, errores existentes congelados
pint.json                       # {"preset": "laravel"}
composer.json                   # + nunomaduro/larastan; + scripts analyse/lint/lint:fix
AGENTS.md                       # sección Tooling actualizada con los comandos
```

Job `tests` (el más delicado):
```yaml
services:
  mysql:
    image: mysql:8.0
    env: { MYSQL_ROOT_PASSWORD: root, MYSQL_DATABASE: dbrentpro_test }
    ports: ['3306:3306']
    options: --health-cmd="mysqladmin ping" --health-interval=10s --health-retries=5
steps:
  - composer install
  - echo '{"marker":"installer"}' > vendor/autoload1.php   # ¡OBLIGATORIO! (ver §8)
  - run: php artisan test
    env: { DB_HOST: 127.0.0.1, DB_USERNAME: root, DB_PASSWORD: root }
```

*(Fragmento ilustrativo — la estructura completa del job, con `actions/checkout` y `setup-php`, se define en la implementación.)*

---

## 5. Decisiones técnicas

Fijadas, no negociables salvo volver el spec a `Borrador`:

- **PHPStan:** nivel **5** con baseline. Sin baseline es imposible llegar a 5 sobre un codebase legacy jamás analizado; el baseline permite verde hoy y falla ante cualquier error **nuevo o en línea modificada**. La métrica del plan ("PHPStan nivel 5 en verde") se cumple con baseline.
- **Baseline de un solo uso:** se genera una vez (F3). Regenerarlo para "hacer pasar" un PR está **prohibido**; solo con justificación documentada y diff review del archivo en el PR. El conteo de errores congelados se documenta al implementar.
- **`phpVersion: 80200`:** el análisis targetea PHP 8.2 (contrato `composer.json`) aunque el CLI local/CI sea 8.4, evitando falsos positivos/negativos por versión de runtime.
- **Pint preset `laravel`** (default de Laravel, consistente con `.editorconfig` existente).
- **Pint full-run como commit mecánico aislado** (`style: pint full run`): el diff tocará cientos de ficheros vendor-heritage; mezclarlo con cambios lógicos contaminaría los diffs de review.
- **Larastan 2.x** (phpstan 1.x): única línea compatible con Laravel 10. Larastan 3.x exige Laravel 11 (Fase 4).
- **CI sin `.env`:** el workflow exporta `DB_HOST/DB_USERNAME/DB_PASSWORD` a nivel de step. `phpunit.xml` **no** se modifica (no hardcodear credenciales en un archivo versionado); ya setea `DB_DATABASE=dbrentpro_test` y `APP_KEY`.
- **Frontend job:** `npm ci` (exige `package-lock.json` trackeado — ya está desde la rama `chore/npm-allowscripts-readme`) + `npm run build`. Valida `allowScripts`/esbuild y el pipeline en cada PR. Las 2 advisory dev-only de `npm audit` **no** bloquean este job (el build no usa dev server).
- **Mocks/nada de tests nuevos:** este spec no añade tests de aplicación; los jobs de CI ejecutan la suite existente (`spec-tests-smoke-fase0`).

---

## 6. Catálogo de casos

Este spec no cubre flujos HTTP: el catálogo adapta `Dado/Cuando/Entonces` a verificaciones de tooling (prefijo `F` — feature transversal, según `spec-conventions.md`). **Total: 13 verificaciones (F1–F13)** — 4 de análisis estático, 2 de estilo, 7 de CI.

### 6.1 Análisis estático (PHPStan)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Nivel 5 en verde | `phpstan-baseline.neon` generado y commiteado | `vendor/bin/phpstan analyse` | exit 0, `[OK] No errors` |
| F2 | Detecta error nuevo | fichero temporal en `app/` con `$user->nonexistent_prop` | `vendor/bin/phpstan analyse` | exit 1, error con fichero y línea |
| F3 | Baseline congela solo lo existente | baseline commiteado | análisis de código existente sin modificar | errores del baseline no reportados; cualquier línea nueva/modificada con error **sí** falla (verificación F2) |
| F4 | Targetea PHP 8.2 | `phpVersion: 80200` en `phpstan.neon.dist` | análisis en CLI PHP 8.4 | mismo resultado que en 8.2 (sin reportes por versión de runtime) |

### 6.2 Estilo (Pint)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F5 | Pint en verde | primera pasada aplicada (commit mecánico) | `vendor/bin/pint --test` | exit 0 |
| F6 | Pint corrige fichero mal formateado | fichero temporal con indentación/llaves fuera de preset | `vendor/bin/pint <fichero>` | fichero reformateado según preset laravel |

### 6.3 CI (GitHub Actions)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F7 | Workflow dispara | `ci.yml` commiteado | push a `main` o apertura de PR | workflow run visible en la tab Actions |
| F8 | Job `tests` verde | MySQL 8 service + marcador `vendor/autoload1.php` + env DB del workflow | run de CI | suite completa (60 tests) pasa en CI |
| F9 | Job `static-analysis` verde | nivel 5 + baseline | run de CI | job en verde |
| F10 | Job `style` verde | Pint aplicado | run de CI | job en verde |
| F11 | Job `frontend-build` verde | `package-lock.json` trackeado (`npm ci` funcional) | run de CI | `npm run build` exitoso, `public/build/manifest.json` generado |
| F12 | CI bloquea errores | PR que introduce un error de tipo (F2) o de estilo | run de CI sobre el PR | job correspondiente en rojo |
| F13 | Marcador autoload1 protege vendor | step `echo '{"marker":"installer"}' > vendor/autoload1.php` tras `composer install` | feature tests en CI | `vendor/laravel` intacto (sin `CheckToken` borrándolo) — ver §8 |

### Tests de caracterización

Ninguno: este spec añade tooling, no documenta bugs de aplicación.

---

## 7. Criterios de aceptación (Definition of Done)

- [ ] `composer require --dev nunomaduro/larastan:^2.0` en `composer.json` (lock actualizado).
- [ ] `phpstan.neon.dist` con level 5, paths `app/`, `phpVersion: 80200`.
- [ ] `phpstan-baseline.neon` commiteado, con **conteo de errores congelados documentado** en el PR (p. ej. "N errores, M ficheros").
- [ ] F1 verificado: `vendor/bin/phpstan analyse` exit 0 local.
- [ ] F2 verificado manualmente: error de tipo inyectado → phpstan falla; se retira el error.
- [ ] `pint.json` con preset laravel y primera pasada aplicada en **commit mecánico aislado** (sin cambios de lógica en el diff).
- [ ] F5 verificado: `vendor/bin/pint --test` exit 0.
- [ ] `.github/workflows/ci.yml` con los 4 jobs disparando en push a `main` y PRs.
- [ ] Job `tests` incluye MySQL 8 service, el marcador `vendor/autoload1.php` y exporta `DB_HOST/DB_USERNAME/DB_PASSWORD`.
- [ ] CI en verde en un PR de prueba con los 4 jobs (evidencia: link al run). El propio PR de implementación sirve: GitHub Actions ejecuta el workflow del PR que lo introduce.
- [ ] Suite smoke completa (60 tests) pasa dentro del job `tests` en CI.
- [ ] `composer analyse` / `composer lint` / `composer lint:fix` funcionan como atajos.
- [ ] `AGENTS.md` (sección Tooling) documenta los comandos y la regla del baseline.
- [ ] Recomendación de branch protection ("require CI green before merge") anotada para post-merge en settings de GitHub.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Diff masivo de Pint (cientos de ficheros vendor-heritage) | Review contaminada, conflictos con ramas abiertas | Commit mecánico aislado `style:` + integrar **antes** del spec 0.3; review con `--stat` |
| Regenerar el baseline esconde errores nuevos | Falsa seguridad permanente | Regla de un solo uso (§5); regeneración solo con justificación + diff review en PR |
| Larastan 2.x corriendo sobre CLI PHP 8.4 | Falsos positivos/negativos | `phpVersion: 80200` fija el target; si larastan 2.x no arranca en 8.4, pinnear versión concreta que soporte 8.4 |
| Sin `.env` en CI: credenciales DB ausentes | Job tests falla con connection refused | El workflow exporta `DB_*` (phpunit.xml intacto, sin credenciales versionadas) |
| **Falta `vendor/autoload1.php` en CI** → `CheckToken` borra `vendor/laravel` en los feature tests | Job tests revienta de forma catastrófica y confusa | Step obligatorio tras `composer install` (F13); documentado también en `tests/README.md` |
| GitHub Actions deshabilitado o sin minutos | DoD de CI no verificable | Verificar settings (P6) antes; alternativa pendiente: self-hosted runner |
| Baseline enorme (miles de errores) desanima mejoras futuras | Nivel 5 se percibe inalcanzable sin baseline | Documentar conteo y atacar por dominio en Fase 3–4 (errores de auth/pagos primero) |

**Pendiente de decisión:**
- ¿Activar branch protection (require status checks) al merge? (settings GitHub, fuera del repo).
- ¿Badge de CI en el README? (cosmético; en el PR de implementación o aparte).

---

## 9. Estimación

- Larastan + config + baseline (P2–P3, F1–F4): 0.5–1 día
- Pint config + full run + verificación (P4, F5–F6): 0.5 día
- Workflow CI 4 jobs + ajustes de service/env (P5, F7–F13): 1–1.5 días
- PR de prueba + correcciones + documentación AGENTS.md: 0.5–1 día
- **Total: 2.5–4 días**

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 0 / 0.2 (criterio de salida de Fase 0: "CI en verde")
- `.analisis/deuda-tecnica.md` → DT-A4 (sin análisis estático/CI)
- `.agents/spec-impl/spec-tests-smoke-fase0.md` → suite que el job `tests` debe ejecutar
- `tests/README.md` → precondiciones (DB test, marcador `vendor/autoload1.php`)
- `AGENTS.md` → sección Tooling (a actualizar)
- `.agents/spec/spec-conventions.md`

---

## 11. Revisión (2026-11-11)

Auditoría según skill `revisar-spec`: frontmatter 6/6, prefijos F oficiales correlativos sin huecos, formato de catálogo, caracterizaciones (ninguna, declarado), referencias vigentes e idioma ✓.

| H# | Hallazgo | Severidad | Resolución |
|----|----------|-----------|------------|
| H1 | Typo "se percieve inalcanzable" (§8) | bajo | Corregido |
| H2 | Badge de CI en OUT y a la vez como pendiente — ambigüedad de alcance | medio | Retirado de OUT; queda solo como decisión pendiente (§8) |
| H3 | Conteo total de casos no declarado explícito | bajo | Añadido "13 verificaciones (F1–F13)" en §6 |
| H4 | DoD "PR de prueba" sin aclarar que puede ser el propio PR de implementación | bajo | Aclarado en el ítem del DoD |
| H5 | Snippet YAML §4 parcial sin aclarar que es ilustrativo | bajo | Anotado como fragmento ilustrativo |

**Decisión:** 0 hallazgos altos, 1 medio y 4 bajos (todos resueltos en esta revisión) → estado pasa a `Revisado`. Pendiente de `Aprobado` por el decisor.

**Aprobación (2026-11-11):** el decisor aprueba el spec tras revisión sin hallazgos altos. Listo para implementar en `spec/phpstan-pint-ci-fase0-impl`; las decisiones pendientes de §8 se resuelven en implementación o post-merge.
