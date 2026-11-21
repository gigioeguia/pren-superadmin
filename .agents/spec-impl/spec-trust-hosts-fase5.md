---
id: spec-trust-hosts-fase5
titulo: "Habilitar `TrustHosts` global: validación del Host header contra `APP_URL` (Fase 5 / 5.4)"
estado: Implementado
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.4) — .analisis/seguridad.md (Seg-21)
cobertura: Cerrar Seg-21 descomentando `TrustHosts` en el stack global del Kernel y customizando su allowlist (`APP_URL` + subdominios; dev hosts solo en local/testing), con override de testabilidad que habilita el catálogo HTTP F1–F4 (host ajeno → 400), notas de despliegue (`.env.example`, README, AGENTS Gotchas) y conteos de suite verificados contra salida real (esperados 262+4=266). Sin válvula `TRUSTED_HOSTS` (decisión Q1): despliegues multi-dominio requieren editar `hosts()`
---

# Spec — Habilitar `TrustHosts` global: validación del Host header contra `APP_URL` (Fase 5 / 5.4)

> Resumen de una línea: descomentar el middleware `TrustHosts` comentado (Seg-21), ajustar su allowlist con dev-hosts solo para local/testing y un override de testabilidad, y blindarlo con el catálogo HTTP F1–F4 — si alguien vuelve a comentar el Kernel, `F1` (host ajeno → 400) se rompe.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.4) — `.analisis/seguridad.md` (Seg-21, l.135).

---

## 1. Objetivo

Cerrar Seg-21: el middleware `TrustHosts` existe como clase pero está comentado en `Kernel.php` (l.48) → Host header injection posible. Este spec lo habilita en el stack global, define su allowlist (`APP_URL` + subdominios; dev hosts solo en entornos no-productivos) y aporta la anti-regresión HTTP que hoy es imposible (el vendor lo salta bajo PHPUnit).

---

## 2. Alcance

### IN

- **Habilitar el middleware**: descomentar `\App\Http\Middleware\TrustHosts::class` en `app/Http/Kernel.php` (l.48, primera posición del stack global).
- **Customizar la clase** `app/Http/Middleware/TrustHosts.php`:
  - `hosts()` = `[allSubdomainsOfApplicationUrl()]` + dev hosts (`localhost`, `127.0.0.1`, `::1`) **solo** con `environment('local', 'testing')`.
  - Override `shouldSpecifyTrustedHosts()` = `! environment('local')` — quita el skip de `runningUnitTests()` para que el catálogo HTTP pueda ejercitarlo en `testing` (Q6a).
- **Determinismo de tests**: `APP_URL=http://localhost` en `phpunit.xml`.
- **Test de regresión** `tests/Feature/Security/TrustHostsSmokeTest.php` (carpeta nueva `Security/`): F1–F4 (§6), con `tearDown` que resetea el static de Symfony (`Request::setTrustedHosts([])`).
- **Notas de despliegue** (Q7):
  - `.env.example`: comentario junto a `APP_URL` — debe matchear el host que sirve la app; el Host header se valida (TrustHosts, Seg-21).
  - `README.md` (raíz): nota en "Notas críticas del setup" — local dev ⇒ `APP_ENV=local` (o `APP_URL` = host del serve).
  - `AGENTS.md`: bullet en "Gotchas" — middleware global activo, dependencia de `APP_URL`, `local` = no-op, residuo multi-dominio.
- **Conteos de suite** (esperados 262 → 266, **verificados contra salida real** — lección 5.3): `README.md`, `tests/README.md` (nota + snapshot + línea `Security/` en el árbol + mini-sección "## TrustHosts (Fase 5 / 5.4)"), `AGENTS.md`.
- **Cierre documental local** (`.analisis/`, gitignored): `seguridad.md` #21 cerrado; `plan-accion.md` fila 5.4 "Cerrado" + línea en "Métricas de éxito".

### OUT

- **Válvula `TRUSTED_HOSTS`** (config/`env`) — descartada en Q1 ("sin b"): los despliegues multi-dominio (apex+www, aliases) requieren editar `hosts()` — residuo declarado (§8).
- **5.5** (session driver `database`/`redis` + cookie `secure`) y **5.6** (`APP_URL` https + TLS) — ítems hermanos con specs propios (Q4a: 5.4 primero con la nota de despliegue como mitigación).
- **Otros middleware del stack** (`TrustProxies`, `HandleCors`, resto) — intocados.
- **Comportamiento en producción** — no cambia más allá de la validación: `local` conserva el no-op del framework; el override solo activa en `testing`.

### Cobertura objetivo

- F1–F4 en verde por HTTP real (el 400 de F1 solo es posible con el middleware registrado y activo en `testing`).
- Ningún documento lista Seg-21 como hallazgo vivo; ningún conteo queda en 262.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Clase `TrustHosts` (skeleton) | Existe — `hosts()` = `[allSubdomainsOfApplicationUrl()]` | Customizar `hosts()` + override (§5) |
| P2 | Registro comentado en Kernel | Existe — l.48, stack global | Descomentar |
| P3 | Cadena del 400 verificada en vendor | Verificada (2026-11-21): `Symfony Request::getHost()` l.1205 strip de puerto / l.1217-1234 match→`SuspiciousOperationException`; `Handler` l.653 la mapea a `BadRequestHttpException` | Ninguna; F1 la valida de extremo a extremo |
| P4 | Skip del vendor en tests | Verificado — `shouldSpecifyTrustedHosts()` (vendor l.100-104) salta `local` + `runningUnitTests()` | Override de testabilidad (Q6a) |
| P5 | `APP_URL` determinista en tests | Falta — `phpunit.xml` no la fija; el cliente de test manda `Host: localhost` | Añadir `APP_URL=http://localhost` a `phpunit.xml` |
| P6 | Base de conteos = 262 (5.3 mergeado, PR #50) | Existe — verificado en `README`/`AGENTS`/`tests/README` | Verificar contra salida real al cerrar el PR |

---

## 4. Estructura propuesta

```
app/Http/Middleware/TrustHosts.php              # MODIFICADO: hosts() + override de testabilidad
app/Http/Kernel.php                             # MODIFICADO: l.48 descomentado (posición 1 del stack global)
phpunit.xml                                     # MODIFICADO: APP_URL=http://localhost
tests/Feature/Security/TrustHostsSmokeTest.php  # NUEVO: F1–F4 (carpeta Security/ nueva)

.env.example                                    # MODIFICADO: comentario junto a APP_URL
README.md                                       # MODIFICADO: nota local dev (Notas críticas) + conteo 266
tests/README.md                                 # MODIFICADO: conteos → 266, línea Security/ en el árbol
                                                #              + mini-sección "## TrustHosts (Fase 5 / 5.4)"
AGENTS.md                                       # MODIFICADO: bullet Gotchas + conteo 266
.agents/README.md                               # MODIFICADO: índice — fase5 (4 specs), conteo 48 → 49

.analisis/ (gitignored — edición local, sin commit)
  seguridad.md                                  # MODIFICADO: #21 → cerrado
  plan/plan-accion.md                           # MODIFICADO: fila 5.4, Métricas de éxito
```

Sin cambios en `routes/`, `config/`, `database/`.

---

## 5. Decisiones técnicas

- **Skeleton + override de testabilidad (Q6a):** el vendor salta el middleware bajo PHPUnit (`runningUnitTests()`); sin override, "solo descomentar" deja el fix sin cobertura posible. El override solo altera `testing`; `local` conserva el no-op del framework y producción valida.
- **Sin válvula `TRUSTED_HOSTS` (Q1):** allowlist = `APP_URL` (regex `^(.+\.)?<host>$`) + dev hosts condicionales. Despliegues multi-dominio ⇒ editar `hosts()` — residuo declarado, mitigado con documentación (Q7).
- **Orden vs 5.6 (Q4a):** 5.4 se implementa ahora; el riesgo de `APP_URL` desalineado (sitio en 400) se mitiga con la nota de `.env.example` y queda saneado por 5.6 (ítem propio).
- **Framework:** PHPUnit (`^10.1`, Laravel 11); trait `DatabaseTransactions`; AAA comentado; `@group smoke` + `@group security` en la clase (dominio íntegro de seguridad).
- **Limpieza de static (crítico):** `Symfony Request::$trustedHosts*` es static y persiste entre tests del mismo proceso — `tearDown` con `Request::setTrustedHosts([])` obligatorio para no contaminar el resto de la suite.
- **Un solo PR (Q5a, lección 5.3):** rama `spec/trust-hosts-fase5-impl`; el spec nace `Borrador` aquí, esta conversación hace de revisión → `Aprobado` → implementación → DoD tachado + `estado: Implementado` **antes** de pedir merge. Nada se mergea en Borrador.

---

## 6. Catálogo de casos

### 6.1 Validación de Host header (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | host ajeno rechazado | middleware activo en `testing` (override) | `GET /login` con `Host: evil.example` | **400** (la anti-regresión de Seg-21: sin middleware registrado, no hay 400) |
| F2 | host de `APP_URL` aceptado | idem | `GET /login` con `Host: localhost` | 200 (render del login) |
| F3 | subdominio de `APP_URL` aceptado | idem | `GET /login` con `Host: foo.localhost` | 200 (vía regex `^(.+\.)?localhost$`) |
| F4 | dev host aceptado en testing | idem (rama dev de `hosts()`) | `GET /login` con `Host: 127.0.0.1` | 200 |

Nota: el 400 no lo emite el middleware en línea — lo dispara `$request->getHost()` (generación de URLs/render) validando contra los patterns estáticos; `SuspiciousOperationException` → `BadRequestHttpException` en `Handler` l.653. F1 verifica la cadena completa.

### Tests de caracterización

Ninguno: no hay bug vigente que se espere romper — el fix ES este spec.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `Kernel.php`: `TrustHosts` descomentado, primera posición del stack global (inspección; import FQCN → `use` corregido por Pint).
- [x] `hosts()` y override según §5 (inspección; la suite los ejercita en verde).
- [x] `phpunit.xml` con `APP_URL=http://localhost`.
- [x] F1–F4 en verde: focal 4 passed (4 assertions); suite completa **266 passed (818 assertions)** — exacto 262+4, verificado contra salida real.
- [x] `tearDown` presente con `Request::setTrustedHosts([])` en la clase de test.
- [x] `composer analyse` sin nuevas entradas vs baseline ([OK]); `composer lint` limpio (tras auto-fix de `fully_qualified_strict_types` en Kernel).
- [x] `.env.example` con comentario en `APP_URL`; `README.md` con nota local dev; `AGENTS.md` con bullet Gotchas.
- [x] Conteos **266** en `README.md`, `tests/README.md`, `AGENTS.md`; `tests/README.md` con línea `Security/` en el árbol y mini-sección F1–F4.
- [x] `.agents/README.md` índice: fase5 (4 specs), conteo 49 (commiteado con el spec).
- [x] Cierres locales: `seguridad.md` #21 cerrado; `plan-accion.md` fila 5.4 + métrica (gitignored).
- [x] Rama `spec/trust-hosts-fase5-impl` + PR review (squash, 1 aprobación + CI); sin push a `main`; **nada mergeado en Borrador** (Q5a).

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Static de Symfony persiste entre tests del mismo proceso | Alto (contamina la suite) | `tearDown` con `Request::setTrustedHosts([])` — obligatorio, verificado en DoD |
| El 400 depende de que el ciclo llame `getHost()` (render de vistas), no del middleware en línea | Medio | F1 sobre `GET /login` (render con generación de URLs); si no disparara, elegir otra ruta que renderice |
| `APP_URL` desalineado en producción → sitio en 400 hasta corrección | Medio | Nota en `.env.example` (Q7); 5.6 sana `APP_URL` (Q4a) |
| Multi-dominio (apex+www/aliases) sin válvula de config | Bajo | Residuo aceptado (Q1 "sin b"): editar `hosts()` — documentado en AGENTS Gotchas |
| Override altera comportamiento en `testing` (activo vs skip del vendor) | Bajo | Deliberado (Q6a): solo `testing`; `local` conserva no-op; producción intacta |
| Conteos desincronizados si se mergean otros tests antes de este PR | Bajo | Verificar contra salida real de `php artisan test` al cerrar el PR (lección 5.3) |

**Pendiente de decisión:** ninguno (Q1–Q10 del grilling 2026-11-21).

---

## 9. Estimación

- Código (clase + Kernel + phpunit.xml): 0.25 día.
- Tests F1–F4 + corrida focal: 0.25 día.
- Docs + conteos + cierres documentales: 0.25 día.
- Verificación (analyse + lint + suite + PR): incluida en cada parte.
- Total: ~0.75 día.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.4
- `.analisis/seguridad.md` → Seg-21 (l.135)
- `app/Http/Kernel.php` → l.48 (registro comentado)
- `vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustHosts.php` → l.75-80 (handle), l.100-104 (skip), l.111-116 (`allSubdomainsOfApplicationUrl`)
- `vendor/symfony/http-foundation/Request.php` → l.1205 (strip de puerto), l.1217-1234 (validación → `SuspiciousOperationException`), l.664 (`setTrustedHosts`)
- `vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/Handler.php` → l.153, l.653 (mapeo a `BadRequestHttpException`)
- `.agents/spec-impl/spec-withdraw-index-request-fase5.md` → precedente de flujo y lección de conteos
- `.agents/spec/spec-conventions.md` · `.agents/spec/spec-template.md`
- `.agents/skills/testing-conventions.md` · `.agents/skills/workflow-ramas.md`
- `AGENTS.md` → sección "Gotchas"

---

### Notas de implementación

1. **Clase final** (`app/Http/Middleware/TrustHosts.php`):
   ```php
   public function hosts(): array
   {
       return [
           $this->allSubdomainsOfApplicationUrl(),
           ...($this->app->environment('local', 'testing') ? ['localhost', '127.0.0.1', '::1'] : []),
       ];
   }

   protected function shouldSpecifyTrustedHosts(): bool
   {
       return ! $this->app->environment('local');
   }
   ```
2. **Envío del Host en tests:** `$this->get($uri, ['Host' => 'evil.example'])` **no funciona** — `prepareUrlForRequest()` (vendor) envuelve la URI con `url()` y `Request::create` sobrescribe `HTTP_HOST` con el host de esa URI absoluta. Solución implementada: helper `getWithHost()` que construye la request Symfony con URI relativa (`SymfonyRequest::create($uri, 'GET', [], [], [], ['HTTP_HOST' => $host])`) y la pasa por el kernel real (stack global incluido). `tearDown`: `\Illuminate\Http\Request::setTrustedHosts([])`.
3. **Cadena del 400** (verificada en vendor, P3): middleware → `Request::setTrustedHosts(patterns)` (static) → `$request->getHost()` durante render → sin match → `SuspiciousOperationException` → `Handler` l.653 → `BadRequestHttpException` → 400.
4. **Redacciones documentales (patrón 5.3, grilling Q1–Q10):**
   - `seguridad.md` #21 → añadir al final: "— **cerrado (2026-11-21, spec `spec-trust-hosts-fase5`)**: middleware global descomentado (`hosts()` = `APP_URL` + subdominios; dev hosts solo local/testing); test HTTP F1–F4 (`TrustHostsSmokeTest`)."
   - `plan-accion.md` fila 5.4 → "**Cerrado (2026-11-21):** spec `spec-trust-hosts-fase5` — Kernel global descomentado, `hosts()` `APP_URL`+subdominios (+dev hosts local/testing), override de testabilidad, F1–F4; residuo: multi-dominio requiere editar `hosts()`."
   - `plan-accion.md` Métricas de éxito → línea nueva tras la de Seg-20: "✅ `TrustHosts` activo con validación de Host header (Fase 5 / 5.4 — spec-trust-hosts-fase5, 2026-11-21)". Encabezado "(reconciliadas 2026-11-19)" intacto; encabezado l.7 y "Ver también" sin cambios.
   - `AGENTS.md` Gotchas → bullet: "`TrustHosts` activo en el stack **global** (Seg-21 / 5.4): el Host header se valida contra `APP_URL` + subdominios (`hosts()` en `app/Http/Middleware/TrustHosts.php`); `local` es no-op del framework; despliegues multi-dominio requieren editar `hosts()` (sin válvula de config). `APP_URL` desalineado ⇒ sitio en 400."
   - `.env.example` junto a `APP_URL` → comentario: "debe matchear el host que sirve la app; el Host header se valida (TrustHosts, Seg-21)".
   - `README.md` "Notas críticas del setup" → bullet: "local dev ⇒ `APP_ENV=local` (o `APP_URL` = host donde sirves): `TrustHosts` valida el Host header en entornos no-local."
5. **Verificación rápida:**
   ```bash
   php artisan test tests/Feature/Security/TrustHostsSmokeTest.php
   composer analyse && composer lint && php artisan test
   ```
