---
id: spec-php84-bump-fase5
titulo: "Bump de runtime PHP 8.3 → 8.4: floor ^8.4.1, Symfony 8.1 y evidencia de runtime (Fase 5 / 5.20)"
estado: Implementado
fecha: 2026-11-23
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.20) — residual del spec 5.15 (§8 riesgos / DoD: "Bump de PHP 8.3 → 8.4, fila nueva del plan")
cobertura: Bump de runtime 8.3 → 8.4 (contrato ^8.4.1 + platform pin 8.4.1 + CI single 8.4 + phpVersion 80400) con full update del lock (Symfony 7.4 → 8.1 familia unificada e ignition 2.12.1 → 2.13 como line-items explícitos — D1), 6º check HARD de ops:verify-deploy como mecanismo de evidencia del flip de producción, y cierre adelantado del residual del 5.15 (deadline holgado: security 8.3 hasta 2027-12-31)
---

# Spec — Bump de runtime PHP 8.3 → 8.4 (Fase 5 / 5.20)

> Resumen de una línea: llevar el stack a PHP 8.4 (floor formal `^8.4`, lock sobre Symfony 8 + ignition 2.13, CI y PHPStan alineados) cerrando el residual del 5.15 con ~14 meses de margen sobre el EOL de 8.3.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.20) — residual registrado en el spec `spec-laravel13-advisories-fase5.md` §8/DoD al cerrar 5.15/5.16.

**Diseño cerrado por grilling (Q1–Q13, 2026-11-23).** Este Borrador refleja las 13 decisiones asentadas; desviaciones en implementación → válvula §5.

---

## 1. Objetivo

Sacar el runtime de la línea con EOL de seguridad a la vista (8.3 termina security 2027-12-31; Laravel 13 sigue hasta 2028-03-17 → su última ventana correría sobre runtime EOL) llevándolo a **8.4** (security hasta 2028-12-31, cubre todo el ciclo de L13), desbloqueando `spatie/laravel-ignition` 2.13 (fijado en 2.12.1 por el platform pin) y dejando el flip del runtime de producción con mecanismo de evidencia propio (6º check HARD de `ops:verify-deploy`).

**Contexto de riesgo real:** el runtime local ya es 8.4.27 desde hace días (la suite corre de facto sobre 8.4; instalado como 8.3.37 en 4.1/P2, upgraded por el SO) — el pin `8.3` actual es una mentira creciente que solo fija la **resolución** de composer, no el runtime de desarrollo.

---

## 2. Alcance

### IN

- **`composer.json` — exactamente 2 líneas** (Q11, valores D1): `php: ^8.3 → ^8.4.1` y `config.platform.php: 8.3 → 8.4.1`. Cero edits de otros constraints de root. El floor es 8.4.**1** (no 8.4.0): `symfony/console` 8.x exige `^8.1.11` (collision/termwind no aceptan 8.0.x) y esa línea exige **PHP >= 8.4.1** — con pin 8.4.0 el solver parte la familia Symfony (ver §12/D1).
- **Lock regenerado con full update** (`composer update --with-all-dependencies`, precedente 5.15): Symfony 7.4.x → **8.1.x, familia unificada** (line-item explícito del DoD, Q4+D1 — framework 13.43 permite `^7.4.0 || ^8.0.0` y el pin 8.4.1 habilita la rama 8.1 de console; bug-support de Symfony 7.4 termina Nov-2026) y `spatie/laravel-ignition` 2.12.1 → **2.13.x** (exige `^8.4`, hoy excluido por el pin). El resto (framework 13.x latest, collision 8.9.x, phpunit 12.x, carbon 3.x, tinker 3.x) llega por resolución natural.
- **CI**: `php-version: '8.4'` en los 4 jobs (`tests`, `static-analysis`, `style`, `audit`), single version, sin matrix (Q3).
- **PHPStan**: `phpstan.neon.dist` → `phpVersion: 80400` + comentario actualizado (Q5). Regla de baseline vigente: 0 adiciones; saneo de entradas stale solo con justificación en el PR.
- **`ops:verify-deploy`**: **6º check HARD** `php version` — expected `>= 8.4.1`, effective `PHP_VERSION` runtime real, vía helper puro estático `phpFloorPasses(string $version): bool` (Q9, umbral D1). Enforcement igual que los 5 existentes: FAIL → exit 1 solo con `APP_ENV=production`; report-only en el resto. Descripción del comando actualizada (`LOG_LEVEL, session, TLS, PHP runtime`). Es el mecanismo de evidencia del flip de producción, mismo patrón que 5.9/5.5/5.6.
- **Docs**: `AGENTS.md` (l.3/l.5 header + l.95 Tooling), `README.md` (l.7 — corrige de paso el stale "Laravel 11.x" → 13.x —, l.13, l.60), `.analisis/plan/plan-accion.md` (fila 5.20 → Cerrado + métrica l.169 stale "Laravel 11 + PHP 8.3" → "Laravel 13 + PHP 8.4" + l.181), `.agents/README.md` (índice: fase5 18 → 19 + spec listado).

### OUT

- **PHP 8.5** — nota de contexto en §8, **sin fila nueva** (Q12): security de 8.4 llega a 2028-12-31 y cubre todo el ciclo de L13; el salto se decide junto al próximo framework mayor.
- **Flip del runtime de producción** — residual con dueño (fila 5.20) y mecanismo de evidencia (el check nuevo); patrón 5.9: la captura bajo `.analisis/` cierra.
- **Session `serialization: json`** (fila 5.21) — precondición `PaymentController:96-97` independiente de este spec.
- **Sync de configs del skeleton** — nada que sincronizar para un bump de runtime.
- **Barrido cosmético de deprecations de 8.4 en `app/`** — verificado de hecho: 0 implicit-nullables (25 hits con `?Type` explícito) y `phpunit.xml` sin `failOnDeprecation`. Solo se corrige lo que suite/PHPStan exijan (fix delgado = desviación D-x).
- **Matrix de CI** — app, no librería; el floor `^8.4` descarta 8.3 y no hay consumidor del rango.

### Cobertura objetivo

- Suite completa en verde **sobre CI 8.4 real** (run link como evidencia — el guard del bump es el run, no tests nuevos del bump).
- `composer audit --locked` → **0 advisories** sobre el lock regenerado (Symfony 8.0.x incluido).
- PHPStan 80400 exit 0 con 0 adiciones al baseline.
- `ops:verify-deploy` con la fila `php version` PASS en local (8.4.27).

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rama de trabajo | En rama draft del spec (`spec/php84-bump-fase5-draft`) | `git checkout -b spec/php84-bump-fase5-impl` al implementar |
| P2 | Runtime local 8.4 | **Ya instalado (8.4.27, Ubuntu)** — la suite corre de facto sobre 8.4 | Ninguna (de-risked; a diferencia de 4.1/P2 no hay que instalar nada) |
| P3 | Lock resuelto sobre pin 8.3 | `composer.lock` con Symfony 7.4.x + ignition 2.12.1 | Editar las 2 líneas (Q11) + `composer update --with-all-dependencies`; el content-hash del lock queda inválido al tocar `composer.json` → regen obligatoria |
| P4 | CI en 8.3 | `php-version: '8.3'` ×4 jobs | Flip a `'8.4'` ×4 |
| P5 | PHPStan targeteando 8.3 | `phpVersion: 80300` + comentario | Bump a `80400` + comentario |
| P6 | `ops:verify-deploy` con 5 HARD | Check de versión PHP inexistente | 6º HARD `php version >= 8.4.0` + helper puro + tests F5/U20–U21 |
| P7 | Docs con "8.3"/stale | `AGENTS.md` ×3, `README.md` ×3 (+ l.7 "Laravel 11.x"), `plan-accion.md` fila 5.20 + métrica l.169 stale | Actualizar en el mismo PR (specs históricos intactos — registro inmutable) |

---

## 4. Estructura propuesta

### Archivos a modificar

```
composer.json                                  # 2 líneas: php ^8.4.1, platform.php 8.4.1 (Q11 + D1)
composer.lock                                  # full regen: Symfony 8.1.x familia unificada, ignition 2.13.x, framework 13.x latest
phpstan.neon.dist                              # phpVersion: 80400 + comentario del contrato
.github/workflows/ci.yml                       # php-version '8.3' → '8.4' (×4 jobs)
app/Console/Commands/VerifyDeploy.php         # 6º HARD check php version + helper estático phpFloorPasses + descripción
tests/Feature/Console/VerifyDeployTest.php     # F5: fila visible + PASS bajo postura de producción
tests/Unit/Console/VerifyDeployPhpFloorTest.php # U20–U21: helper puro (rama FAIL no simulable por config)
AGENTS.md                                      # header (l.3/l.5) + Tooling (l.95): PHP 8.4, phpVersion 80400
README.md                                      # l.7 (+ Laravel 11.x→13.x stale), l.13, l.60
.agents/README.md                              # índice: fase5 (18) → 19, spec-php84-bump-fase5
.analisis/plan/plan-accion.md                  # fila 5.20 → Cerrado; métrica l.169 + l.181 (local, gitignored)
```

### Diseño del check (Q9)

```php
// VerifyDeploy::hardChecks() += fila
$this->row('php version', '>= 8.4.1', PHP_VERSION, self::phpFloorPasses(PHP_VERSION)),

// Helper puro, unit-testeable sin mock ni seam (PHP_VERSION es runtime,
// no config: la rama FAIL no es simulable por config())
public static function phpFloorPasses(string $version): bool
{
    return version_compare($version, '8.4.1', '>=');
}
```

**Nota sobre la rama FAIL:** inalcanzable por feature test — el floor `^8.4` impide que la suite misma instale en un runtime < 8.4. Por eso la cobertura de la rama FAIL vive en el helper puro (U20), no en `HARD_DEVIATIONS` (F2 queda con sus 5 checks config-simulables). F1/F3/F4 existentes no se rompen: la fila nueva pasa en todo entorno donde los tests pueden correr.

---

## 5. Decisiones técnicas

Fijadas por grilling (Q1–Q13, 2026-11-23), no negociables salvo volver el spec a `Borrador`:

- **Target 8.4, no 8.5** (Q1): matchea el runtime local instalado (8.4.27, cero cambio de máquina), desbloquea ignition 2.13, security hasta 2028-12-31 > EOL L13. 8.5 = nota §8, sin fila.
- **Floor `^8.4.1`, no `^8.3` + pin alto** (Q2 + D1): el lock regenerado contendrá paquetes que no instalan en 8.4.0 (`symfony/console 8.1.11` exige PHP >= 8.4.1) ni en 8.3 — mantener `^8.3`/pin 8.4.0 declararía un soporte que el lock ya no da. Platform pin se queda (5.15 demostró que gana su keep).
- **CI single 8.4** (Q3): app, no librería; matrix mantendría vivo un runtime descartado por el floor.
- **Full update con Symfony 8 explícito** (Q4): `composer update --with-all-dependencies` (precedente 5.15); Symfony 7.4 → 8.0 es line-item del DoD con diff verificado (estilo P7 del 5.15). Retener 7.4 exigiría constraints artificiales y su bug-support termina Nov-2026. La matriz resultante es la que un install fresco sobre 8.4 daría = la soportada.
- **composer.json en 2 líneas** (Q11): Symfony 8 e ignition 2.13 llegan por resolución natural; constraints de root intactos = diff auditable.
- **`phpVersion: 80400`** (Q5): el comentario de `phpstan.neon.dist` targetea el contrato de composer.json; 80300 sería un mismatch documentado.
- **Check HARD, no WARN** (Q9): invariante de deploy — el lock `^8.4` no instala en < 8.4; FAIL en production es exactamente la señal de que el flip no se hizo. Report-only en el resto (filosofía 5.9 intacta).
- **Válvula de escape** (Q10, mirror 5.15 §9/§11): fixes delgados (suite/PHPStan por semántica 8.4 o Symfony 8) = desviación D-x documentada, se sigue; **decisión no contemplada** (ignition 2.13 sin constraint `^13` / compatible con Symfony 8, paquete del grafo sin rama Symfony 8, advisory sin fix) = volver a `Borrador`.
- **Tests**: 0 tests para el bump mismo (el guard es el run de CI sobre 8.4 real, estilo F13 del 5.15); 3 tests para el check nuevo (F5 + U20–U21) — **ajuste documentado vs Q13**, la regla del repo exige cobertura para código nuevo (precedente 5.9: cada check con sus tests).
- **Docs**: specs históricos intactos (5.15/4.1 mencionan 8.3 como registro); métrica stale l.169 del plan corregida de paso.

---

## 6. Catálogo de casos

> **Numeración (H1 de revisión):** `F5` continúa el F1–F4 del spec 5.9 (mismo archivo `VerifyDeployTest`, método `test_f5`); `U20–U21` continúa tras el máximo vigente (U19, spec 5.14). Renumeración para evitar colisión con IDs citados en la fila 5.9 del plan (precedente 5.19: F43→F52).

### 6.1 Evidencia de runtime (`ops:verify-deploy`, prefijo `F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F5 | Fila `php version` visible y PASS | postura de producción completa (`PRODUCTION_POSTURE`) | `Artisan::call('ops:verify-deploy')` | exit 0; salida contiene fila `php version` con expected `>= 8.4.1`, effective = `PHP_VERSION` real, `PASS`; sin `FAIL` en salida |

### 6.2 Helper puro del floor (`prefijo U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U20 | Versiones bajo el floor | `phpFloorPasses('8.3.29')`, `phpFloorPasses('8.3.99')`, `phpFloorPasses('8.4.0')` | invocación directa del helper estático | `false` (única cobertura de la rama FAIL — no simulable por config; 8.4.0 bajo el floor por D1) |
| U21 | Versiones en/sobre el floor | `phpFloorPasses('8.4.1')`, `phpFloorPasses('8.4.27')`, `phpFloorPasses('8.5.3')`, `phpFloorPasses('8.10.0')` | invocación directa del helper estático | `true` |

### Tests de caracterización

Ninguno — spec de infraestructura (como 5.15/4.1); no documenta comportamiento inseguro vigente.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `composer.json`: exactamente 2 líneas editadas — `php: ^8.4.1`, `config.platform.php: "8.4.1"` (D1); resto de constraints intactos (Q11 auditable en el diff: commits `81a40bb`+`f37be72`).
- [x] `composer update --with-all-dependencies` exit 0; **diff del lock verificado**: Symfony 7.4.x → **8.1.x familia unificada** (console 8.1.11, http-kernel/http-foundation/routing 8.1.11, mime 8.1.10), `spatie/laravel-ignition` 2.12.1 → **2.13.0**, `laravel/framework` 13.43.0, `nunomaduro/collision` 8.9.6, `phpunit/phpunit` 12.5.45, `nesbot/carbon` 3.15.1, `laravel/tinker` 3.2.0.
- [x] CI: `php-version: '8.4'` en los 4 jobs; **run 4/4 success** — https://github.com/gigioeguia/pren-superadmin/actions/runs/45190685231 (PR #72; cierra el proxy inverso del 5.15/F13).
- [x] Suite completa en verde sobre 8.4: **561/561** (558 + F5 + U20 + U21; 6254 assertions, 78.4s local + job `tests` en CI).
- [x] PHPStan `phpVersion: 80400` exit 0; **0 adiciones al baseline** — `[OK] No errors` (0 saneos necesarios).
- [x] Pint exit 0 — `composer lint` passed.
- [x] `composer audit --locked` → **0 advisories** (job `audit` verde sobre el lock con Symfony 8.1).
- [x] `php artisan ops:verify-deploy` local: fila `php version | >= 8.4.1 | 8.4.27 | PASS` — evidencia en `.analisis/verify-deploy-php84-bump-2026-11-23.md` (gitignored).
- [x] Docs actualizadas: `AGENTS.md` (l.3/l.5/l.95), `README.md` (l.7 "Laravel 11.x"→"13.x" corregido de paso, l.13, l.60), `plan-accion.md` (fila 5.20 → Cerrado + métrica l.169 stale corregida + l.181), `.agents/README.md` (índice regenerado completo: fase5 25, total 70), comentario `phpstan.neon.dist`.
- [x] Specs históricos intactos (5.15, 4.1): 0 edits a `.agents/spec-impl/` preexistente.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Ignition 2.13 sin constraint `^13` de framework o sin rama Symfony 8 (hereda el riesgo medio del 5.15 §8) | medio | Válvula Q10: resolución lo decide; si no resuelve → Borrador (o quedarse en 2.12.x si es compatible con el grafo, documentado como desviación). **No materializado**: 2.13.0 resuelve con L13 + Symfony 8.1 |
| Symfony 7.4 → 8.1 = mayor simultáneo al bump de runtime | medio | Suite 561 + PHPStan + audit en el mismo PR; fixes delgados = D-x; cambios estructurales no contemplados = Borrador. **Materializado sin sorpresa estructural** (D1: familia unifica con pin 8.4.1) |
| Behavioral changes de Symfony 8.1 (removals de deprecations 7.x) | medio | Red de 558 casos existentes; suite completa sobre 8.4 en CI antes del merge |
| Advisory sin fix en Symfony 8.0.x recién salido | bajo | Válvula `audit.ignore` documentada (AGENTS.md / spec 5.15 F13) con justificación en el PR, o re-target de versión |
| `phpVersion: 80400` surfice errores PHPStan nuevos | bajo | Regla de baseline vigente: fallo del PR, no regeneración |
| Producción despliega el lock `^8.4` sobre runtime 8.3 | bajo | `composer install` falla antes de servir; el check HARD nuevo es la señal de evidencia |
| Runtime local ya 8.4.27 | — | De-risked: ninguna acción (P2); la suite ya corre de facto sobre 8.4 |

**Pendiente de decisión:** ninguna — diseño cerrado por grilling (Q1–Q13, 2026-11-23). Residuales con dueño: flip del runtime de producción (fila 5.20, mecanismo = check nuevo, patrón 5.9); PHP 8.5 como nota de contexto, sin fila nueva (security 8.4 → 2028-12-31 cubre todo el ciclo de L13; se decide junto al próximo framework mayor).

---

## 9. Estimación

- Mecánica (composer 2 líneas + full update + diff del lock + CI ×4 + phpstan): 0.5–1 día
- Check `ops:verify-deploy` + tests F5/U20–U21: 0.25 día
- Docs (AGENTS/README/plan-accion/índice) + PR: 0.25 día

**Total: ~1 día.**

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.20 (residual del spec 5.15 §8/DoD)
- `.agents/spec-impl/spec-laravel13-advisories-fase5.md` → §2 (P7 diff del lock), §5 (runtime/decisions), §8 (riesgo medio ignition), §12 (F13 proxy CI)
- `.agents/spec-impl/spec-log-level-warning-fase5.md` → patrón de evidencia `ops:verify-deploy` (5 HARD + 3 WARN, enforcement solo production)
- `.agents/spec-impl/spec-upgrade-laravel-php-fase4.md` → precedente de bump de runtime (4.2 → 4.3, P2/P9)
- `.agents/spec/spec-conventions.md` → estados, frontmatter, prefijos de casos
- `.agents/skills/workflow-ramas.md` → rama `spec/php84-bump-fase5-impl` (nunca `main`)
- Laravel support policy: 13 hasta 2028-03-17, PHP 8.3–8.5 — https://laravel.com/framework/docs/13.x/releases
- PHP support timeline: 8.3 security → 2027-12-31; 8.4 security → 2028-12-31 — https://www.php.net/supported-versions.php (contexto del deadline "4T-2027" de la fila 5.20)

---

## 11. Revisión (2026-11-23)

Auditoría `revisar-spec` contra `.agents/spec/spec-conventions.md` + plantilla. Sin hallazgos **altos** → pasa a `Revisado`.

| H# | Hallazgo | Severidad | Resolución |
|----|----------|-----------|------------|
| H1 | Colisión de IDs: `F1`/`U1–U2` ya viven en `spec-log-level-warning-fase5` (fila 5.9 del plan, mismo archivo `VerifyDeployTest`); e inconsistencia interna F1 (catálogo/estimación) vs F5 (§3–§5) | medio | Renumeración F5 + U20–U21 con nota de numeración en §6 (precedente 5.19: F43→F52) |
| H2 | Conteo inconsistente: §5 declaraba "2 tests" con 3 filas de catálogo; DoD y §8 decían 560 (558+3=561) | medio | Alineado a 3 tests (F5 + U20 + U21) → 561/561 en §5/DoD/§8 |
| H3 | Referencia php.net no canónica (`/eol.php` redirige) | bajo | → `https://www.php.net/supported-versions.php` |
| H4 | Typo "PAssea" (§4) | bajo | Corregido |

---

## 12. Desviaciones

- **D1 — floor 8.4.0 → 8.4.1 (válvula Q10, decidido por el decisor 2026-11-23, opción A).** El primer full update con pin `"8.4"` (≡ 8.4.0) partió la familia Symfony: `console/http-foundation/http-kernel/mime/routing` quedaron en 7.4.x mientras el resto subía a 8.0.x — el line-item DoD "Symfony → 8.0.x" era inseguible tal cual. Causa raíz: `nunomaduro/collision 8.9.6` y `nunomaduro/termwind 2.4.1` exigen `symfony/console ^7.4.23 || ^8.1.11` (la rama 8.0.x no existe para ellos), y `symfony/console v8.1.11` exige **PHP >= 8.4.1**. Nota forense: `composer why-not` no lo reporta porque solo mira constraints de dependientes, no floors de PHP del propio candidato; el bloqueo real salió del dry-run `composer update symfony/console:v8.1.11 ...`. Enmienda aplicada (misma línea, valor distinto — Q11 intacto): require `php: ^8.4.1`, platform pin `8.4.1`, check HARD `>= 8.4.1` (umbral de `phpFloorPasses` y F5), line-item DoD "Symfony 7.4.x → **8.1.x familia unificada**" (mejora del 8.0.x prometido). Verificado con dry-run doble (pin 8.4.1 y 8.4.27 → misma resolución: console/http-kernel/http-foundation/routing 8.1.11, mime 8.1.10). Mismo criterio de honestidad del Q2: el lock con console 8.1.11 no instala en 8.4.0 → declarar `^8.4` sería la trampa que Q2 rechazó para `^8.3`. Runtime: CI `setup-php '8.4'` instala el último patch (>= 8.4.27 hoy) ✓; local 8.4.27 ✓; residual prod exige >= 8.4.1 (cualquier instalación 2026-era lo cumple).

---

## 13. Cierre (2026-11-23)

DoD verificado íntegro (§7 tachado con evidencia). Rama `spec/php84-bump-fase5-impl` → **PR #72**, CI 4/4 sobre PHP 8.4: [run 45190685231](https://github.com/gigioeguia/pren-superadmin/actions/runs/45190685231). Suite 561/561, PHPStan `80400` [OK] (0 adiciones), Pint, `composer audit --locked` 0, `ops:verify-deploy` local PASS (evidencia `.analisis/verify-deploy-php84-bump-2026-11-23.md`). Fila 5.20 del plan → Cerrado (código+CI).

**Residuales con dueño (operacionales, patrón 5.9):**

- **Flip del runtime de producción** a >= 8.4.1: la captura del check `php version` sobre `APP_ENV=production` es la evidencia que cierra (mismo mecanismo que 5.5/5.6/5.9 comparten).
- **PHP 8.5**: sin fila (security 8.4 → 2028-12-31 cubre todo el ciclo de L13; se decide junto al próximo framework mayor).
