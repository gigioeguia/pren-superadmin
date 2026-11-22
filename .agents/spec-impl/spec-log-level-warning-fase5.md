---
id: spec-log-level-warning-fase5
titulo: "LOG_LEVEL=warning safe-by-default + patrón de evidencia operacional ops:verify-deploy (Fase 5 / 5.9)"
estado: Revisado
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.9, l.114) — .analisis/seguridad.md (Seg-26, l.145)
cobertura: Cerrar Seg-26 con la palanca repo-side: fallback de config `'debug'`→`'warning'` en 6 canales de logging (safe-by-default, idem patrón 5.5) + comentario guía en `.env.example`; deprecaciones confirmadas cerradas (canal `null`, l.34 + pin l.10). Además fija el **patrón 5.9** de evidencia operacional que las filas 5.5/5.6 tienen pendiente: comando artisan `ops:verify-deploy` (5 checks HARD + 3 WARN, tabla esperado/efectivo/resultado, exit code binario, enforcement gateado por `APP_ENV=production`) que verifica config efectivo (flip + re-cache) y cuya salida pegada en `.analisis/` es la evidencia. Tests U1–U2 (arch) + F1–F4 (consola), suite esperada 325+6=331. Decisiones selladas en grilling Q1–Q14 (2026-11-21). Auditado (revisar-spec, 2026-11-21): H1/H2 bajos corregidos en la rama (refs `phpunit.xml` l.25/l.37; footer del sketch siempre visible per Q12); sin hallazgos altos.
---

# Spec — `LOG_LEVEL=warning` safe-by-default + patrón de evidencia operacional `ops:verify-deploy` (Fase 5 / 5.9)

> Resumen de una línea: el fallback de `LOG_LEVEL` deja de ser `debug` (safe-by-default en config, no solo documentación en `.env.example`) y el repo gana el mecanismo que convierte los residuales operacionales de 5.9/5.5/5.6 ("flip del `.env` de producción pendiente de evidencia") en un check binario: `php artisan ops:verify-deploy` → tabla PASS/FAIL/WARN + exit code; la captura en producción sigue siendo residual.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.9, l.114: "`LOG_LEVEL=warning` | Seg-26 | **Parcial (2026-11-21):** default ya fijado en `.env.example`; falta evidencia del `.env` operativo") — `.analisis/seguridad.md` (Seg-26, l.145: "Logging de deprecaciones y `LOG_LEVEL=debug` → ruido/almacenamiento de info sensible en logs"). Las filas 5.5 (l.110) y 5.6 (l.111) difieren su residual a "**patrón 5.9**": este spec es donde ese patrón se define.

---

## 1. Objetivo

Cerrar la brecha repo-side de Seg-26 — hoy el único artefacto commiteado es `.env.example:11` (documentación), mientras el fallback **efectivo** de `config/logging.php` sigue siendo `'debug'` (6 canales): un deploy con `.env` sin la key loguea a nivel debug — y fijar el patrón de evidencia operacional (Q1b): el mecanismo versionable y testeable que cierra los residuales "flip del `.env` de producción + re-cache pendientes de evidencia" de 5.9, 5.5 (`SESSION_DRIVER`) y 5.6 (`APP_URL`/`FORCE_HTTPS`).

---

## 2. Alcance

### IN

- **`config/logging.php`** — 6 líneas: fallback `env('LOG_LEVEL', 'debug')` → `env('LOG_LEVEL', 'warning')` en single (l.63), daily (l.69), papertrail (l.83), stderr (l.94), syslog (l.104), errorlog (l.109). Slack (l.78) **intacto** en `'critical'` (umbral de notificación, no verbosity — Q2); deprecaciones (ll.33–36) intactas (Q4).
- **`.env.example`** — comentario guía sobre `LOG_LEVEL` (l.11): producción usa `warning` por default de config; dev puede pinear `debug` localmente (flip del `.env`, no commiteado).
- **`app/Console/Commands/VerifyDeploy.php`** — NUEVO, firma `ops:verify-deploy` (convención `dominio:acción` de `rent:`/`lang:`, Q9a): patrón 5.9 de evidencia operacional (detalle en §5).
- **Tests** (6): `tests/Unit/Config/LoggingArchTest.php` — U1–U2 (§6.1, arch string-based, TestCase puro estilo `SeedersArchTest`); `tests/Feature/Console/VerifyDeployTest.php` — F1–F4 (§6.2, carpeta nueva).
- **Conteos** (mecánicos): `AGENTS.md` (línea Tooling del comando + corrección del conteo stale de suite: dice 301, real 325 → **331**), `tests/README.md` (conteo 331, árbol, mini-sección "## Verificación de deploy (Fase 5 / 5.9)"), `.agents/README.md` índice: fase5 → 9 specs, conteo 54 (commiteado con el draft).
- **`.env` local** (gitignored, Q3a): pinear `LOG_LEVEL=debug` explícito — dev conserva verbosidad; el default de config gobierna producción.
- **Cierres documentales locales** (`.analisis/`, gitignored — edición en implementación, sin commit): `seguridad.md` #26 → Mitigado + residual; `plan-accion.md` fila 5.9 + residuales de 5.5/5.6 referenciando el mecanismo + Métricas de éxito.

### OUT

- **Rotación de logs (`LOG_CHANNEL=daily`)** — estilo de deploy, no riesgo de seguridad; `days=14` (l.70) intacto. Observación, no cambio.
- **Canal de deprecaciones** — ya cerrado por partida doble (default config `'null'` l.34 + pin `.env.example:10`): este spec solo lo declara cerrado (Q4). Dev puede reactivarlo localmente si quiere visibilidad de upgrades — flip local de una línea, no material de spec.
- **Niveles de slack/papertrail** — solo cambia el fallback de `LOG_LEVEL`; slack conserva `'critical'`.
- **Flip del `.env` de producción** — residual operacional (Q7): el spec shipea el mecanismo de evidencia; la captura es post-deploy, humana, gitignored.
- **`phpunit.xml`** — sin cambios: la suite ya está aislada (`APP_ENV=testing` l.21, `SESSION_DRIVER=array` l.37, sin `LOG_LEVEL` ⇒ los tests del comando fuerzan config en runtime, no env).
- **Rutas HTTP de verificación** — el comando corre por SSH/CLI, sin exposición HTTP (lección `maintenance.php`, Fase 1 / 1.1).
- **`$_ENV` + `require` fresco (patrón U de 5.5/5.6)** — no aplica: U1–U2 son guards de contenido de archivo (string-based), no mapeo env→config.

### Cobertura objetivo

- U1–U2 + F1–F4 en verde; suite completa esperada **331 passed** (325 + 6, verificada contra salida real — lección 5.3).
- `ops:verify-deploy` ejecutable en local (report-only) como smoke manual del mecanismo.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Suite base en verde | **325 casos** según `tests/README.md` (verificado por 5.8 contra salida real: 325 passed, 1743 assertions) | `php artisan test` y verificar 325 contra salida real antes de empezar (lección 5.3) |
| P2 | Conteo stale en `AGENTS.md` l.15 | Dice "301 casos"; real = 325 (5.8 actualizó `tests/README.md` pero no `AGENTS.md`) | Corregir a **331** como parte de los conteos de este spec (fix documental, no código) |
| P3 | Carpeta `tests/Unit/Config/` | Existe (5.5 `SessionConfigTest`, 5.6 `ForceHttpsConfigTest`) | Ninguna; U1–U2 usan TestCase puro (arch string-based, sin boot) |
| P4 | Carpeta `tests/Feature/Console/` | No existe | Crear (PSR-4 `Tests\Feature\` ya cubre por directorio) |
| P5 | Precedente de comandos custom | Existe — `rent:apply-late-fee`, `rent:payment-generate`, `lang:sync` (firma `dominio:acción`) | Ninguna; `ops:` sigue la convención (Q9a) |
| P6 | Fila 5.9 "Parcial" | `.env.example:11` ya fija `LOG_LEVEL=warning` | Ninguna para el example (solo comentario guía); el spec ataca el fallback de config y el patrón de evidencia |
| P7 | Config de 5.5/5.6 disponible para el comando | `SESSION_DRIVER` default `database` (5.5), `app.force_https`/`app.trusted_proxies` (5.6) | Ninguna; los checks HARD/WARN leen esas keys tal cual |
| P8 | `phpstan-baseline.neon` intocado | 245 entradas congeladas | Comando nuevo debe salir limpio sin tocar baseline (regla single-use) |

---

## 4. Estructura propuesta

```
config/logging.php                                          # MODIFICADO: 6 fallbacks debug→warning
.env.example                                                # MODIFICADO: comentario guía sobre LOG_LEVEL (l.11)
app/Console/Commands/VerifyDeploy.php                      # NUEVO: ops:verify-deploy (patrón 5.9)

tests/Unit/Config/LoggingArchTest.php                       # NUEVO: U1–U2 (arch, TestCase puro)
tests/Feature/Console/VerifyDeployTest.php                  # NUEVO: F1–F4 (carpeta nueva)

AGENTS.md                                                   # MODIFICADO: Tooling (comando) + conteo 301→331
tests/README.md                                             # MODIFICADO: conteo 331, árbol, mini-sección "## Verificación de deploy"
.agents/README.md                                           # MODIFICADO: índice — fase5 (9 specs), conteo 54 (commiteado con el draft)

.env                                                        # MODIFICADO, gitignored: LOG_LEVEL=debug (Q3a, sin commit)
.analisis/ (gitignored — edición local, sin commit)
  seguridad.md                                              # MODIFICADO: #26 → Mitigado + residual
  plan/plan-accion.md                                       # MODIFICADO: fila 5.9 + refs 5.5/5.6 + Métricas de éxito
```

Sin cambios en `routes/`, `bootstrap/`, `phpunit.xml`, `config/` fuera de `logging.php`.

---

## 5. Decisiones técnicas

Fijadas (grilling Q1–Q14, 2026-11-21), no negociables salvo volver el spec a `Borrador`:

- **Alcance = patrón, no solo LOG_LEVEL (Q1b):** 5.9 define el mecanismo de evidencia que cierra su propio residual y el de 5.5/5.6. Sin esto, el spec solo re-declararía "pendiente de evidencia".
- **Safe-by-default en config (Q2):** el fix real es el fallback de `config/logging.php`, no el example file — idem decisión 5.5 (`SESSION_DRIVER=database` como default de config): un `.env` sin la key deja de loguear a debug. 6 canales; slack conserva `'critical'`.
- **`.env` local pinea `LOG_LEVEL=debug` (Q3a):** dev conserva verbosidad (útil, `APP_ENV=local` no es riesgo); el "camino real" de producción lo cubre el default de config. Divergencia deliberada con la Q4 de 5.5: el driver de sesión cambia infraestructura, la verbosidad solo utilidad local.
- **Deprecaciones cerradas as-is (Q4):** default config `'null'` + pin `.env.example:10`; la mitad de Seg-26 ya estaba resuelta de facto.
- **Comando `ops:verify-deploy` (Q6b/Q9a):** la evidencia debe ser binaria y auditable (exit code), no comparación manual de `config:show`. Sirve a 5.9 + 5.5 + 5.6 a la vez.
- **Catálogo de checks (Q10/Q14a):**
  - **HARD (FAIL, exit 1):** `APP_DEBUG=false`, `LOG_LEVEL` efectivo `warning`, `SESSION_DRIVER=database`, `APP_URL` empieza con `https://`, `FORCE_HTTPS=true`.
  - **WARN (reporta, no falla):** config cache vigente (`bootstrap/cache/config.php` existe — correr sin cache es estilo de deploy válido; los HARD leen config efectivo, que es la verdad runtime), `TRUSTED_PROXIES` vacío (solo relevante detrás de LB), `APP_KEY` set (**sin imprimir el valor**).
  - **INFO:** `APP_ENV` como línea de contexto — **no** es check HARD: gatear enforcement por `APP_ENV` lo haría tautológico (Q14a).
- **Enforcement gateado por `APP_ENV=production` (Q11a):** en otros entornos es report-only (exit 0 siempre, valores visibles); sin flags. Riesgo aceptado: una box malconfigurada (`APP_ENV` != production en el server real) escapa al enforcement — mitigado porque la evidencia es human-reviewed y la tabla muestra todos los valores.
- **`LOG_LEVEL` efectivo (detalle sellado, ronda 3):** no existe `logging.level` global en Laravel 11 — el level es por canal. El comando resuelve el canal default (`config('logging.default')`), perfora los canales apilados si es `stack` y exige `level === 'warning'` en cada hoja con level (sigue a `LOG_CHANNEL=daily` sin cambios; canal `null` → trivially PASS).
- **Output sin secretos (Q12):** tabla `check | esperado | efectivo | resultado` (`$this->table()`), footer "paste this output as evidence under .analisis/" (gitignored), exit `SUCCESS` solo sin FAIL con enforcement activo. Nunca imprime credenciales ni el valor de `APP_KEY` (solo `set`/`missing`).
- **Numeración:** grilling Q13 llamó "U1–U4" a los casos del comando; el catálogo oficial los fija como **F1–F4** (prefijo `F` = feature transversal, `tests/Feature/`) y reserva `U` para el arch-test (§ tabla de prefijos de `spec-conventions.md`).
- **Framework:** PHPUnit `^10.1` (Laravel 11); tests del comando via `Artisan::call('ops:verify-deploy', [], $buffered)` + `Symfony BufferedOutput` (exit code como return + contenido como `$buffered->fetch()`), config forzada en runtime (`config([...])`, patrón `ForceHttpsSmokeTest`); arch-test con `PHPUnit\Framework\TestCase` puro + `file_get_contents` (patrón `SeedersArchTest`, sin boot).
- **Sin `env()` en `app/`** (regla 4.3): el comando lee exclusivamente `config()` / `is_file()`.

---

## 6. Catálogo de casos

### 6.1 Arch: defaults de logging no reintroducibles (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | example file guía el nivel seguro | `.env.example` leído como string | aserción de contenido | contiene `LOG_LEVEL=warning` y `LOG_DEPRECATIONS_CHANNEL=null` |
| U2 | config no reintroduce fallback verbose | `config/logging.php` leído como string | aserción de contenido | NO contiene `env('LOG_LEVEL', 'debug')` y SÍ contiene `env('LOG_LEVEL', 'warning')` (6 canales) |

### 6.2 Consola: `ops:verify-deploy` (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | postura producción completa → PASS | `config(['app.env' => 'production'])` + todos los valores esperados forzados (`app.debug=false`, `logging.channels.single.level=warning`, `session.driver=database`, `app.url=https://...`, `app.force_https=true`) | `Artisan::call('ops:verify-deploy', [], $buffered)` | exit `SUCCESS`, tabla sin `FAIL`, línea INFO `APP_ENV=production` |
| F2 | cada HARD roto → FAIL | ídem F1 pero **una** desviación por corrida, iterando 5 variantes (`app.debug=true`, `single.level=debug`, `session.driver=file`, `app.url=http://...`, `app.force_https=false`) | idem | exit `FAILURE` en cada variante + fila `FAIL` del check correspondiente visible en `$buffered->fetch()` |
| F3 | no-producción es report-only | `config(['app.env' => 'local'])` + desvíos de dev (`app.debug=true`, `single.level=debug`) | idem | exit `SUCCESS` pese a las desviaciones; valores visibles en la tabla (Q11a/Q3a: dev desvía legítimamente) |
| F4 | salida sin secretos | `APP_KEY` fijado por `phpunit.xml` (l.25) | idem F1 | `$buffered->fetch()` NO contiene el valor de `config('app.key')` — solo `set`/`missing` |

### Tests de caracterización

Ninguno: el estado inseguro (fallback `debug`) se **elimina en este mismo spec** — U2 es guard anti-reincidencia, no caracterización de comportamiento que se espere romper más adelante.

---

## 7. Criterios de aceptación (Definition of Done)

- [ ] `config/logging.php`: exactamente 6 fallbacks `'debug'`→`'warning'` (single l.63, daily l.69, papertrail l.83, stderr l.94, syslog l.104, errorlog l.109); slack l.78 `'critical'` intacto; deprecaciones ll.33–36 intactas.
- [ ] `.env.example`: comentario guía sobre `LOG_LEVEL` (producción hereda `warning` del default de config; dev puede pinear `debug` localmente).
- [ ] `app/Console/Commands/VerifyDeploy.php`: firma `ops:verify-deploy`; 5 HARD + 3 WARN + línea INFO `APP_ENV`; enforcement solo con `APP_ENV=production` (report-only en el resto); tabla esperado/efectivo/resultado; footer de evidencia `.analisis/`; sin `env()` ni `request()` (reglas 4.3/4.4); sin impresión de secretos.
- [ ] U1–U2 en verde (focal: 2 passed, TestCase puro).
- [ ] F1–F4 en verde (focal: 4 passed; `Artisan::call` + `BufferedOutput`).
- [ ] Suite completa: **331 passed** — exacto 325+6, verificados contra salida real de `php artisan test`.
- [ ] `composer analyse` sin nuevas entradas vs baseline; `composer lint` limpio.
- [ ] `.env` local (gitignored): `LOG_LEVEL=debug` pineado — sin commit.
- [ ] `AGENTS.md`: línea en Tooling documentando `ops:verify-deploy` + conteo de suite corregido **301 → 331** (stale desde 5.8, P2).
- [ ] `tests/README.md`: conteo 331, árbol con `LoggingArchTest` + `VerifyDeployTest`, mini-sección "## Verificación de deploy (Fase 5 / 5.9)".
- [ ] `.agents/README.md`: índice fase5 (9 specs), conteo 54 (commiteado con el draft).
- [ ] Cierres locales `.analisis/` (gitignored): `seguridad.md` #26 → Mitigado + residual; `plan-accion.md` fila 5.9 + refs del mecanismo en residuales 5.5/5.6 + métrica (redacciones exactas en §Notas 8).
- [ ] Rama `spec/log-level-warning-fase5-impl` + PR (squash, 1 aprobación + CI); sin push a `main`.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Box malconfigurada (`APP_ENV` != production en el server real) escapa al enforcement → exit 0 falso | Medio | Q14a aceptado: la tabla muestra todos los valores, la evidencia es human-reviewed y el footer aclara que las expectativas aplican a producción |
| `config:cache` congela la evaluación de env — flip sin re-cache no visible en `.env` | Medio | El comando lee config **efectivo** (verdad runtime): con cache stale verifica los valores viejos y FAILlea; nota operacional: flip + re-ejecutar `config:cache` antes de correr el comando |
| Tests forzando `app.env=production` en runtime contaminan la suite | Bajo | `config([...])` muere con cada test (patrón `ForceHttpsSmokeTest`); sin mutación de `$_ENV` |
| WARN de config-cache varía según máquina (dev corrió `config:cache` o no) | Bajo | Es WARN por diseño (Q10): no afecta exit code; en CI `bootstrap/cache/config.php` no existe |
| El comando crece hasta validar todo el despliegue (scope creep futuro) | Bajo | Allowlist fija de 8 keys + 1 archivo (§5); ampliar el catálogo = spec propio |
| PHPStan level 5 sobre el comando | Bajo | `handle(): int` tipado, colecciones tipadas; salir limpio sin tocar baseline (P8) |

**Residual operacional (Q7):** en producción fijar `LOG_LEVEL=warning` (o confiar en el default de config tras re-cache), `SESSION_DRIVER=database`, `APP_URL=https://<dominio>` + `FORCE_HTTPS=true` (+ `TRUSTED_PROXIES` solo si hay LB), re-ejecutar `config:cache` y correr `php artisan ops:verify-deploy` hasta exit 0; la salida pegada bajo `.analisis/` (gitignored) es la evidencia que mueve las filas 5.9/5.5/5.6 a **Cerrado/Mitigado**. El DoD de este spec cubre el **mecanismo**, no la captura (post-deploy, humana).

**Pendiente de decisión:** ninguno (Q1–Q14 del grilling 2026-11-21).

---

## 9. Estimación

- Código (config 6 líneas + `.env.example` + comando ~80 líneas): 0.25 día.
- Tests U1–U2 + F1–F4 + corrida focal: 0.25 día.
- Docs + conteos + cierres documentales + PR: 0.25 día.
- Total: ~0.75 día.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.9 (l.114); residuales que usan el patrón: 5.5 (l.110), 5.6 (l.111)
- `.analisis/seguridad.md` → Seg-26 (l.145)
- `config/logging.php` → ll.33–36 (deprecaciones), l.63 (single), l.69 (daily), l.78 (slack `critical`), l.83 (papertrail), l.94 (stderr), l.104 (syslog), l.109 (errorlog)
- `.env.example` → ll.9–11 (`LOG_CHANNEL`/`LOG_DEPRECATIONS_CHANNEL`/`LOG_LEVEL`)
- `phpunit.xml` → l.21 (`APP_ENV=testing`), l.25 (`APP_KEY` fija), l.37 (`SESSION_DRIVER=array`)
- `app/Console/Commands/` → `ApplyLateFees` (`rent:apply-late-fee`), `SyncTranslations` (`lang:sync`) — precedente de firma
- `tests/Unit/Seeders/SeedersArchTest.php` → patrón arch string-based (TestCase puro + `file_get_contents`)
- `tests/Feature/Security/ForceHttpsSmokeTest.php` → patrón de config forzada en runtime (5.6)
- `.agents/spec-impl/spec-session-database-secure-fase5.md` → precedente del safe-by-default en config + "patrón 5.9" como residual
- `.agents/spec-impl/spec-app-url-https-force-tls-fase5.md` → keys `app.force_https`/`app.trusted_proxies` que el comando verifica; estructura de este spec
- `.agents/spec/spec-conventions.md` → tabla de prefijos (`U`, `F`) · `.agents/spec/spec-template.md`
- `.agents/skills/testing-conventions.md` · `.agents/skills/workflow-ramas.md`
- `AGENTS.md` → "Commands" (conteo stale 301, P2), "Tooling" (destino de la línea del comando)

---

### Notas de implementación

1. **Dif exacto en `config/logging.php`** (6 líneas, un solo patrón):
   ```php
   'level' => env('LOG_LEVEL', 'warning'),
   ```
   en single (l.63), daily (l.69), papertrail (l.83), stderr (l.94), syslog (l.104) y errorlog (l.109). Slack (l.78) y deprecaciones (ll.33–36) quedan intactos. `env()` sigue legal en `config/` (regla 4.3).
2. **`.env.example`** — sobre `LOG_LEVEL=warning` (l.11):
   ```
   # Producción hereda warning del default de config (Seg-26); dev puede pinear debug localmente (flip del .env, no commiteado).
   ```
3. **`VerifyDeploy` (sketch):**
   ```php
   class VerifyDeploy extends Command
   {
       protected $signature = 'ops:verify-deploy';
       protected $description = 'Verify effective deploy posture (LOG_LEVEL, session, TLS) — evidence for Fase 5 residuals';

       public function handle(): int
       {
           $enforce = config('app.env') === 'production';
           $failed = 0;

           $this->info('APP_ENV: '.config('app.env').($enforce ? ' — enforcing' : ' — report-only (expectations apply to production)'));

           // hard checks: [label, expected, effective, pass]
           $this->table(['check', 'expected', 'effective', 'result'], $rows);

           // footer siempre (Q12): la salida completa es la evidencia, PASS o FAIL
           $this->line('Paste this output as evidence under .analisis/ (gitignored).');

           return ($enforce && $failed > 0) ? self::FAILURE : self::SUCCESS;
       }
   }
   ```
   - HARD: `config('app.debug') === false`; **LOG_LEVEL efectivo**: resolver `config('logging.default')`, si el canal es `stack` iterar `channels`, exigir `level === 'warning'` en cada hoja con level (canal `null` → PASS); `config('session.driver') === 'database'`; `str_starts_with((string) config('app.url'), 'https://')`; `config('app.force_https') === true`.
   - WARN: `is_file(base_path('bootstrap/cache/config.php'))`; `config('app.trusted_proxies')` vacío (solo detrás de LB); `config('app.key')` no vacío (imprimir `set`/`missing`, jamás el valor).
   - `handle(): int` tipado (PHPStan level 5); sin `env()` ni `request()`.
4. **`LoggingArchTest`** (TestCase puro, sin boot): `file_get_contents(base_path('.env.example'))` → `assertStringContainsString('LOG_LEVEL=warning')` + `assertStringContainsString('LOG_DEPRECATIONS_CHANNEL=null')` (U1); `file_get_contents(base_path('config/logging.php'))` → `assertStringNotContainsString("env('LOG_LEVEL', 'debug')")` + `assertStringContainsString("env('LOG_LEVEL', 'warning')")` (U2). El negative check no colisiona con slack: su fallback es `'critical'`.
5. **`VerifyDeployTest`** (carpeta nueva `tests/Feature/Console/`):
   ```php
   $code = Artisan::call('ops:verify-deploy', [], $buffered = new BufferedOutput);
   $out = $buffered->fetch();
   ```
   - F1: `config(['app.env' => 'production', 'app.debug' => false, 'logging.channels.single.level' => 'warning', 'session.driver' => 'database', 'app.url' => 'https://prod.example.com', 'app.force_https' => true])` → `assertSame(Command::SUCCESS, $code)`, `assertStringContainsString('PASS', $out)`, `assertStringNotContainsString('FAIL', $out)`.
   - F2: base F1 + una desviación por variante (5 corridas): `app.debug=true` / `single.level=debug` / `session.driver=file` / `app.url=http://...` / `app.force_https=false` → `Command::FAILURE` + fila FAIL del check correspondiente en `$out`.
   - F3: `config(['app.env' => 'local', 'app.debug' => true, 'logging.channels.single.level' => 'debug'])` → `Command::SUCCESS` (report-only, Q11a/Q3a).
   - F4: `assertStringNotContainsString((string) config('app.key'), $out)`.
6. **Corrida focal antes que la suite completa.**
7. **Conteos:** suite **331** = 325 real (P1) + 6. `AGENTS.md` l.15 "301 casos" → "331 casos" (corrige el stale P2) + una línea en Tooling: "`php artisan ops:verify-deploy` — verifica postura operacional efectiva (LOG_LEVEL/session/TLS; patrón de evidencia 5.9; enforcement solo en `APP_ENV=production`)".
8. **Redacciones documentales (patrón trust-hosts/session/app-url):**
   - `seguridad.md` #26 → añadir al final: "— **mitigado (2026-11-21, spec `spec-log-level-warning-fase5`)**: fallback de config `warning` en 6 canales (safe-by-default; `.env.example` con guía), deprecaciones confirmadas en canal `null`; comando `ops:verify-deploy` fija el patrón de evidencia (5 HARD + 3 WARN, exit code, enforcement solo `APP_ENV=production`). Residual operacional: flip del `.env` de producción + re-cache + captura del comando pendientes (filas 5.5/5.6 usan el mismo mecanismo)."
   - `plan-accion.md` fila 5.9 → "**Mitigado (2026-11-21):** spec `spec-log-level-warning-fase5` — fallback config `warning` (6 canales, safe-by-default) + comando `ops:verify-deploy` como patrón de evidencia (5 HARD + 3 WARN, exit code); U1–U2 + F1–F4; residual: captura en producción pendiente (mecanismo compartido con 5.5/5.6)."
   - `plan-accion.md` filas 5.5/5.6 → añadir al final del residual: "mecanismo de evidencia: `ops:verify-deploy` (spec 5.9)."
   - `plan-accion.md` Métricas de éxito → línea: "✅ `LOG_LEVEL` safe-by-default (`warning`) + patrón de evidencia operacional `ops:verify-deploy` (Fase 5 / 5.9 — spec-log-level-warning-fase5, 2026-11-21; residual operativo: captura en producción)."
9. **Verificación rápida:**
   ```bash
   php artisan test tests/Unit/Config/LoggingArchTest.php tests/Feature/Console/VerifyDeployTest.php
   php artisan ops:verify-deploy   # local: report-only, smoke manual del mecanismo
   composer analyse && composer lint && php artisan test
   ```
