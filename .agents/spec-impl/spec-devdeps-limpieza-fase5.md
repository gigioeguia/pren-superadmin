---
id: spec-devdeps-limpieza-fase5
titulo: "Limpieza de dependencias no usadas: eliminación del pipeline frontend y stray composer (Fase 5 / 5.12)"
estado: Aprobado
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.12) — .analisis/deuda-tecnica.md (DT-B5, DT-B6; DT-A5 vía supersede de 4.5)
cobertura: Eliminación del pipeline frontend npm completo (5 devDeps + archivos + job de CI) por ser código muerto demostrable (0 directivas @vite), remoción de laravel/sail del require-dev y de 4 paquetes symfony/* no usados del require; conservación documentada de mockery/collision/faker/guzzle (claims de DT-B5 obsoletos) + barrido documental coherente en AGENTS.md, README.md, plan-accion.md y deuda-tecnica.md
---

# Spec — Limpieza de dependencias no usadas: eliminación del pipeline frontend y stray composer (Fase 5 / 5.12)

> Resumen de una línea: cerrar 5.12 (DT-B5/B6) eliminando las dependencias demostrablemente muertas — el pipeline Vite/npm completo (que compila assets que ninguna vista carga), `laravel/sail` y 4 `symfony/*` sin uso directo — conservando las que sí se usan, corrigiendo los claims obsoletos de DT-B5 y dejando la documentación operativa coherente; esto **supersede** la mitigación de 4.5 (DT-A5): la remediación pasa de "stack actualizado" a "stack eliminado".

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.12) — `.analisis/deuda-tecnica.md` (DT-B5, DT-B6, DT-A5 vía 4.5).

---

## 1. Objetivo

Cerrar el ítem 5.12: dejar el manifiesto de dependencias sin entradas muertas, con la evidencia de uso verificada en el propio spec y la documentación (commiteada y local) contando la misma historia.

---

## 2. Alcance

### IN

**Frontend — eliminación total del pipeline npm (decisión Q1=B, grilling 2026-11-21):**

- `package.json` + `package-lock.json` (las 5 devDeps: `axios`, `lodash`, `vite`, `laravel-vite-plugin`, `postcss`) — el bloque `allowScripts` de esbuild desaparece con el archivo.
- `vite.config.js`.
- `resources/js/` completo (`app.js`, `bootstrap.js`) y `resources/css/app.css` — únicos archivos trackeados en esos directorios.
- `public/build/` local (no trackeado) y las entradas huérfanas de `.gitignore`: `/node_modules`, `/public/build`, `/public/hot`, `npm-debug.log`, `yarn-error.log`.
- Job `frontend-build` del CI (`.github/workflows/ci.yml`) + su `setup-node`; CI queda en 3 jobs.
- `.env.example`: 5 líneas `VITE_PUSHER_*` (l.70–74) — scaffold muerto de broadcasting.

**Composer:**

- `laravel/sail` fuera de `require-dev` (0 referencias: sin docker-compose, sin CI, sin docs).
- `symfony/css-selector`, `symfony/event-dispatcher`, `symfony/string`, `symfony/yaml` fuera de `require` (0 usos directos verificados en app/tests/database/config/routes; el framework los provee transitivamente si algo los necesita).

**Documentación commiteada (viaja en el PR):**

- `AGENTS.md`: quitar `npm install`/`npm run dev`/`npm run build` del bloque Commands (incluida la nota de `allowScripts`), y el job `frontend-build` de la sección Tooling.
- `README.md`: quitar la línea de Frontend con stack Vite/axios (l.8), el prerequisito "Node 18+ y npm" (l.15), los pasos npm (l.22, 27), y las notas `allowScripts`/`npm audit` (l.33, 65).

**Documentación local `.analisis/` (gitignored, no viaja en el PR):**

- `plan-accion.md`: fila 5.12 (l.117) → Cerrado; fila 4.5 (l.89) → **anotación supersede** (decisión Q1=R2-a): se conserva la historia de la migración Vite 3→6 y se añade "→ superseded por 5.12 (spec-devdeps-limpieza-fase5): pipeline eliminado; DT-A5 remediado por eliminación".
- `deuda-tecnica.md`: DT-A5 (l.51–52, hoy sin línea de estado) → Cerrado (2026-11-XX) por eliminación del pipeline; DT-B5 (l.108–109) y DT-B6 (l.111–112) → Cerrados, con **corrección de claims obsoletos**: mockery y collision sí se usan, faker sí, y guzzle tiene uso directo.

### OUT

- **Dependencias que se conservan** (uso directo verificado, ver F7): `mockery/mockery` (`tests/Feature/Payment/PaymentFlowSmokeTest.php:240`), `nunomaduro/collision` (provee `php artisan test`: `vendor/nunomaduro/collision/src/Adapters/Laravel/Commands/TestCommand.php`), `fakerphp/faker` (factories), `laravel/pint`, `nunomaduro/larastan`, `phpunit/phpunit`, `spatie/laravel-ignition`, `guzzlehttp/guzzle` (`app/Library/Billplz.php`, `app/Library/TapPayment.php` — uso directo ⇒ declaración explícita; la sugerencia de DT-B5 de quitarlo se rechaza).
- **`laravel/tinker` en `require`**: se usa; reclasificarlo a require-dev es cosmético y fuera del espíritu "no usadas" (Q2 R2).
- **`SystemSettingController.php` l.132–136** (escribe `VITE_PUSHER_*` en `.env`): código funcional del editor de settings; residual cosmético sin impacto — se documenta como tal, no se toca.
- **Re-scaffolding futuro de Vite**: YAGNI consciente (Q1=B); `laravel new` lo trae de vuelta trivialmente si algún día se moderniza el frontend.
- **Guard/arch-test anti-reintroducción**: innecesario (Q3 R2) — las deps desaparecen; reintroducirlas sería deliberado, no un accidente de convención.
- **Registros históricos**: `spec-vite-axios-fase4.md` y el índice de `.agents/README.md` (l.26) se conservan como historial; **sin history rewrite** — los blobs de los archivos eliminados quedan en git history (precedente 4.10; aquí no hay secretos, solo código muerto).

### Cobertura objetivo

- 0 devDeps npm trackeadas; 0 archivos del pipeline Vite en HEAD.
- 0 referencias a npm/vite/Node en docs operativas commiteadas (AGENTS.md, README.md, ci.yml, .env.example, .gitignore).
- `require-dev` sin sail; `require` sin los 4 `symfony/*` huérfanos; suite completa (371 casos), `composer analyse` y `composer lint` en verde.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rama de trabajo (no `main`) | `spec/devdeps-limpieza-fase5-draft` creada | Sin acción |
| P2 | Evidencia de "muerte" verificada | Verificada en grilling 2026-11-21 (§6: greps con 0 coincidencias) | Sin acción |
| P3 | Convenciones de specs | Presentes (`.agents/spec/spec-conventions.md`) | Sin acción |
| P4 | Locks commiteados como base | `composer.lock` + `package-lock.json` en HEAD | Regenerar `composer.lock` tras los removes; `package-lock.json` se elimina junto al pipeline |
| P5 | CI de partida en verde | `main` en verde (4 jobs) | Los fallos posteriores son atribuibles a este spec |

---

## 4. Estructura propuesta

```
# Eliminados (git rm)
package.json                          package-lock.json
vite.config.js                         resources/js/app.js
resources/js/bootstrap.js              resources/css/app.css
public/build/                          (local, untracked — rm)

# Editados (commiteados)
composer.json                          # -sail (require-dev), -4 symfony/* (require)
composer.lock                          # regenerado por composer remove
.github/workflows/ci.yml               # job frontend-build + setup-node fuera
.gitignore                             # 5 entradas huérfanas fuera
.env.example                           # 5 líneas VITE_PUSHER_* fuera
AGENTS.md                              # Commands + Tooling
README.md                              # stack, prereqs, pasos npm, notas

# Locales (.analisis/, gitignored — actualización in-place)
.analisis/plan/plan-accion.md          # fila 5.12 cerrada + fila 4.5 superseded
.analisis/deuda-tecnica.md             # DT-A5/B5/B6 cerrados, claims corregidos

# Este spec (commiteado)
.agents/spec-impl/spec-devdeps-limpieza-fase5.md
```

---

## 5. Decisiones técnicas

Fijadas (grilling 2026-11-21, Q1–Q5 R1 + Q1–Q4 R2), no negociables salvo volver el spec a `Borrador`:

- **Eliminación total del pipeline, no poda parcial (Q1=B):** 0 directivas `@vite` en todo `resources/views/` ⇒ el output del build no lo carga nadie. Código muerto actualizado (4.5 lo hizo ayer) sigue siendo código muerto; mantenerlo cuesta surface de `npm audit`, `allowScripts`, un job de CI y docs engañosas.
- **Supersede documentado, no reescritura (Q1 R2 = a):** la fila 4.5 conserva su historia y gana la anotación; `spec-vite-axios-fase4.md` queda como registro histórico. DT-A5 se cierra por eliminación (remediación más fuerte que el upgrade).
- **`guzzlehttp/guzzle` se conserva:** uso directo en dos gateways ⇒ declaración explícita es la práctica correcta, no redundancia.
- **Los 4 `symfony/*` salen de `require`:** 0 usos directos; si algún paquete tercero los necesita, composer los resuelve transitivamente — la suite verde lo prueba.
- **Sin guard anti-reintroducción (Q3 R2):** la ausencia de un build pipeline no es una convención que alguien viole por accidente.
- **Docs commiteadas viajan en el PR; docs de `.analisis/` se actualizan in-place** con líneas citadas (patrón `spec-comand-txt-credenciales-fase5`).
- **Sin history rewrite:** los archivos salen de HEAD; los blobs quedan en history sin riesgo (no hay secretos, solo código muerto).

---

## 6. Catálogo de verificaciones (prefijo `F` — documental/transversal)

Este spec no cubre flujos HTTP; el catálogo adapta `Dado/Cuando/Entonces` a verificaciones, siguiendo el patrón de `spec-comand-txt-credenciales-fase5` / `spec-reconciliacion-analisis-fase0-3.md`. **Total: 10 verificaciones (F1–F10)**.

Evidencia base (grilling 2026-11-21, todos con 0 coincidencias):

```
grep -rn "@vite\|Vite::" resources/views --include='*.blade.php'   # 0
grep -rnE "window\.axios|\baxios\.|window\._" resources              # solo bootstrap.js (definición, 0 consumo)
grep -rnE "CssSelector|EventDispatcher|Component\\\\String|Component\\\\Yaml" app tests database config routes  # 0
grep -rn "sail" .github .env.example README.md                      # 0
```

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Pipeline npm fuera de HEAD | 6 archivos del pipeline trackeados (2 manifests + 3 js/css + 1 config) | `git rm` de los 6 archivos | `git ls-files` sin `package.json`, `package-lock.json`, `vite.config.js`, `resources/js/*`, `resources/css/*`; `public/build/` local (untracked) borrado |
| F2 | CI sin job frontend | `ci.yml` con 4 jobs | Editar el workflow | 3 jobs (tests, static-analysis, style); 0 refs a `setup-node`/npm |
| F3 | `.gitignore` sin huérfanos | Entradas npm/vite presentes | Editar | 0 entradas entre `/node_modules`, `/public/build`, `/public/hot`, `npm-debug.log`, `yarn-error.log` |
| F4 | `.env.example` sin scaffold Vite | 5 líneas `VITE_PUSHER_*` (l.70–74) | Editar | 0 coincidencias `VITE_` en el archivo |
| F5 | `sail` fuera de require-dev | `composer.json` require-dev con sail | `composer remove --dev laravel/sail` | require-dev sin sail; `composer.lock` regenerado; `composer install` OK |
| F6 | `symfony/*` huérfanas fuera | 4 symfony/* en `require` | `composer remove symfony/css-selector symfony/event-dispatcher symfony/string symfony/yaml` | require sin las 4; `composer install` OK; suite verde (framework las resuelve transitivamente si algo las consume) |
| F7 | Dependencias usadas conservadas | Claims obsoletos de DT-B5 | Aplicar los removes de F5/F6 | `mockery`, `collision`, `faker`, `guzzle` siguen instalados; greps de uso (PaymentFlowSmokeTest:240, TestCommand de collision, factories, Billplz/TapPayment) siguen en verde |
| F8 | Docs commiteadas coherentes | AGENTS.md/README.md documentan comandos npm | Actualizar ambos | 0 refs a `npm`/`vite`/`Node`/`allowScripts`/`npm audit` en AGENTS.md y README.md (excluye líneas históricas de specs/índice) |
| F9 | Docs locales coherentes | plan-accion.md fila 5.12 abierta; fila 4.5 "Mitigado" sin nota; deuda-tecnica.md DT-A5 sin estado, DT-B5/B6 abiertos | Actualización in-place | Fila 5.12 Cerrado con ref a este spec; fila 4.5 con anotación supersede; DT-A5/B5/B6 cerrados con claims corregidos (mockery/collision/faker/guzzle) |
| F10 | Suite y calidad en verde | Cambios F1–F7 aplicados | `composer install && php artisan test && composer analyse && composer lint` | 371 casos OK; PHPStan 0 errores nuevos (baseline intacto, single-use); Pint OK |

---

## 7. Criterios de aceptación (Definition of Done)

- [ ] F1–F6 aplicados: 0 archivos npm/Vite en HEAD (`git ls-files`), `composer.json` sin sail ni los 4 `symfony/*`, `composer.lock` regenerado, `composer install` OK.
- [ ] `mockery`, `collision`, `faker` y `guzzle` permanecen instalados con sus usos intactos (F7): PaymentFlowSmokeTest:240, `TestCommand` de collision, factories, Billplz/TapPayment.
- [ ] `php artisan test` en verde (371 casos) y `composer analyse`/`composer lint` en verde — sin entradas nuevas al baseline (regla single-use vigente).
- [ ] CI en 3 jobs (`tests`, `static-analysis`, `style`), verde en el PR.
- [ ] AGENTS.md y README.md sin referencias npm/vite/Node/allowScripts/npm audit (F8).
- [ ] `.env.example` sin `VITE_*` (F4) y `.gitignore` sin entradas huérfanas (F3).
- [ ] `plan-accion.md`: fila 5.12 Cerrado + fila 4.5 con anotación supersede conservando su historia (F9).
- [ ] `deuda-tecnica.md`: DT-A5, DT-B5 y DT-B6 cerrados con fecha y ref a este spec; claims obsoletos de DT-B5 corregidos (mockery/collision usados, guzzle con uso directo) (F9).
- [ ] PR con spec + implementación; `Refs: .agents/spec-impl/spec-devdeps-limpieza-fase5.md`; DoD tachado en la descripción.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Un paquete tercero requiere directamente uno de los 4 `symfony/*` | Bajo | Composer lo resuelve transitivamente; `composer install` + suite verde (F6/F10) lo prueban |
| Referencias npm/vite residuales en docs operativas | Medio | Greps de F8 con 0 coincidencias como criterio medible |
| Despliegue que sirva `public/build/` manualmente | Bajo | El directorio no está trackeado y ninguna vista lo referencia (0 `@vite`); se borra localmente |
| Reintroducción deliberada del pipeline en el futuro | Bajo | Anotación supersede en 4.5 registra que fue decisión consciente; re-scaffold trivial si se moderniza el frontend |
| `SystemSettingController` sigue escribiendo `VITE_PUSHER_*` en `.env` | Bajo | Residual cosmético documentado (§2 OUT); sin impacto funcional |

**Pendiente de decisión:** ninguna — Q1–Q5 (R1) y Q1–Q4 (R2) del grilling 2026-11-21 están cerradas.

---

## 9. Estimación

- Implementación (git rm + composer remove + CI yml + gitignore + .env.example): < 0.5 día
- Barrido documental (AGENTS.md, README.md commiteados + 2 docs locales) + PR: < 0.5 día

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.12 (l.117); Fase 4 / 4.5 (l.89, supersede)
- `.analisis/deuda-tecnica.md` → DT-A5 (l.51–52), DT-B5 (l.108–109), DT-B6 (l.111–112)
- `.agents/spec-impl/spec-vite-axios-fase4.md` → spec superseded (registro histórico)
- `.agents/spec-impl/spec-comand-txt-credenciales-fase5.md` → patrón de spec documental (prefijo `F`, docs locales in-place)
- `.agents/spec/spec-conventions.md`
- Grilling 2026-11-21: Q1=B (eliminación total), Q2=sail only, Q3=4 symfony/*, Q4=barrido doc, Q5=DoD; R2: supersede anotado, tinker fuera, sin guard, nombre de spec
