---
id: spec-app-url-https-force-tls-fase5
titulo: "APP_URL https + forzar TLS (FORCE_HTTPS + TrustProxies) (Fase 5 / 5.6)"
estado: Implementado
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.6) — .analisis/seguridad.md (Seg-23, l.139)
cobertura: Cerrar Seg-23 shipeando la palanca repo-side: middleware global `ForceHttps` (302 http→https, todos los métodos, path+query preservados) tras una válvula explícita `FORCE_HTTPS` (default off, sin inferencia ni magia de entorno) + `TrustProxies` operable vía `TRUSTED_PROXIES` (CSV/`*` nativos del parent) + guía operacional en `.env.example`; el flip del `.env` de producción queda como residual (patrón 5.5/5.9). Tests U1–U6 + F1–F4, suite esperada 273+10=283. Decisiones selladas en grilling Q1–Q12 (2026-11-21).
---

# Spec — `APP_URL` https + forzar TLS (`FORCE_HTTPS` + `TrustProxies`) (Fase 5 / 5.6)

> Resumen de una línea: el repo gana su primera palanca TLS — middleware global que redirige http→https (302) tras una válvula explícita `FORCE_HTTPS` (default off), con `TrustProxies` cableado a config para no loop-ear detrás de un LB que termina TLS; el certificado/TLS real y el flip del `.env` de producción son residual pendiente de evidencia.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.6) — `.analisis/seguridad.md` (Seg-23, l.139: "`APP_URL` http (no HTTPS) — cookies de sesión transmitibles en claro si se sirve sin TLS").

---

## 1. Objetivo

Cerrar Seg-23: hoy el stack no fuerza TLS en ninguna capa — 0 usos de `URL::forceScheme`/`secure_url`, ningún middleware de redirect (14 en `app/Http/Middleware/`), sin reglas `RewriteCond %{HTTPS}` en `public/.htaccess`, sin HSTS — y `TrustProxies` está inertizado (`$proxies = null`, `app/Http/Middleware/TrustProxies.php` l.15), por lo que detrás de un proxy que termina TLS `request->secure()` siempre es `false`. Este spec añade la palanca repo-side: middleware redirect opt-in + soporte de proxies operable + guía; el TLS/cert real y el flip del `.env` de producción son residual operacional (patrón 5.5/5.9).

---

## 2. Alcance

### IN

- **`config/app.php`** — exactamente 2 keys tras `'asset_url'` (l.86):
  - `'force_https' => env('FORCE_HTTPS', false)`.
  - `'trusted_proxies' => env('TRUSTED_PROXIES')`.
- **`app/Http/Middleware/ForceHttps.php`** — NUEVO: válvula off → passthrough; on + `!$request->secure()` → `302` a https mismo path+query; on + secure → passthrough.
- **`app/Http/Kernel.php`** — 1 línea: registro en stack global (ll.48–56) inmediatamente tras `TrustProxies::class` (l.50).
- **`app/Http/Middleware/TrustProxies.php`** — override `proxies()` → `config('app.trusted_proxies')`; el parent ya resuelve `'*'` y CSV (vendor l.83–84/87–89, hook `proxies()` l.165–168).
- **`.env.example`** — comentario bajo `APP_URL` (ll.5–6: producción DEBE usar `https://<dominio-real>`) + bloque `# FORCE_HTTPS=` / `# TRUSTED_PROXIES=` comentadas con explicación, tras el bloque de sesión (l.32).
- **Tests** (6 unit + 4 feature): `tests/Unit/Config/ForceHttpsConfigTest.php` — U1–U6 (§6.1); `tests/Feature/Security/ForceHttpsSmokeTest.php` — F1–F4 (§6.2); ambas carpetas ya existen (5.4/5.5).
- **Conteos** (mecánicos): `README.md`, `tests/README.md` (árbol + mini-sección "## Force HTTPS (Fase 5 / 5.6)"), `AGENTS.md` → suite esperada 273 + 10 = **283**; `.agents/README.md` índice: fase5 → 6 specs, conteo 51 (commiteado con el draft).
- **Cierres documentales locales** (`.analisis/`, gitignored — edición en implementación, sin commit): `seguridad.md` #23 → Mitigado + residual; `plan-accion.md` fila 5.6 + Métricas de éxito.

### OUT

- **`.htaccess` / rewrites de servidor** — Apache-only; el despliegue real (nginx/cPanel) es desconocido; la válvula de app cubre sin acoplarse al server (Q1).
- **`URL::forceScheme('https')`** — redundante: con el redirect el browser llega https y `url()`/`route()` toman scheme de la request; en console/queue la generación cae a `config('app.url')` que el flip de producción ya hace https (Q1).
- **HSTS (`Strict-Transport-Security`)** — solo tiene sentido con el dominio sirviendo https de forma estable y verificada; prematuro lock-ea browsers (y 5.4 documentó despliegues multi-dominio). Si se quiere, ítem propio (Q5).
- **Fixes al wizard `/install`** (`config/installer.php` l.71, `required|url`) **y `SystemSettingController`** (l.43, genera `.env` en runtime con `url('/')`) — edges aceptados y documentados (Q4): el wizard exige URL válida al operador y `SystemSetting` hereda el scheme real de la request.
- **Cookie `secure`** — ya inferida de `APP_URL` (spec 5.5, `config/session.php` l.171); este spec no toca `config/session.php`.
- **Flip del `.env` local** — el middleware se ejercita con runtime config en tests; válvula on local rompería `php artisan serve` (no sirve TLS).
- **`phpunit.xml`** — sin cambios (`APP_URL=http://localhost` l.23, sin `FORCE_HTTPS`/`TRUSTED_PROXIES` ⇒ suite aislada; F3 lo aserta).

### Cobertura objetivo

- U1–U6 + F1–F4 en verde; suite completa esperada **283 passed** (273 + 10, verificada contra salida real).
- La suite actual (273) queda intacta: sin overrides de env en `phpunit.xml`.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Base de conteos = 273 (5.5 mergeado, PR #52) | Existe — `AGENTS.md`/`README.md`/`tests/README.md` marcan 273 | Verificar 273+10=283 contra salida real al cerrar el PR (lección 5.3) |
| P2 | Stack global con `TrustProxies` | Existe — `app/Http/Kernel.php` ll.48–56 (`TrustProxies` l.50) | Ninguna (inserción de 1 línea tras él) |
| P3 | Parent resuelve `'*'`/CSV + hook `proxies()` | Verificado (2026-11-21) — vendor `TrustProxies` l.83–84 (`'*'`), l.87–89 (explode CSV), l.165–168 (`proxies()`) | Ninguna; override de 3 líneas |
| P4 | Headers con `X_FORWARDED_PROTO` | Existe — `app/Http/Middleware/TrustProxies.php` ll.22–27 | Ninguna |
| P5 | Fallback `config('trustedproxy.proxies')` inerte | Verificado — no existe `config/trustedproxy.php`; el fallback del parent (vendor l.69) nunca dispara | Ninguna; key en `config/app.php` + override (Q9) |
| P6 | Carpeta `tests/Unit/Config/` + patrón `$_ENV` | Existe (5.5 `SessionConfigTest`: mutación + `require` fresco) | Reutilizar patrón |
| P7 | Carpeta `tests/Feature/Security/` + patrón kernel real | Existe (5.4 `TrustHostsSmokeTest`: `SymfonyRequest::create` + `createTestRequest` de `MakesHttpRequests`, vendor l.743) | Reutilizar; añadir reset de estáticos de proxies en `tearDown` |
| P8 | Suite aislada del middleware nuevo | `phpunit.xml` sin `FORCE_HTTPS`/`TRUSTED_PROXIES` ⇒ defaults off/null | Ninguna; F3 lo aserta |

---

## 4. Estructura propuesta

```
config/app.php                                            # MODIFICADO: 2 keys tras 'asset_url' (l.86)
app/Http/Middleware/ForceHttps.php                        # NUEVO
app/Http/Kernel.php                                       # MODIFICADO: 1 línea en stack global (tras TrustProxies, l.50)
app/Http/Middleware/TrustProxies.php                       # MODIFICADO: override proxies() (3 líneas)
.env.example                                               # MODIFICADO: comentario APP_URL + bloque FORCE_HTTPS/TRUSTED_PROXIES comentado

tests/Unit/Config/ForceHttpsConfigTest.php                # NUEVO: U1–U6
tests/Feature/Security/ForceHttpsSmokeTest.php            # NUEVO: F1–F4

README.md                                                  # MODIFICADO: conteo 283
tests/README.md                                            # MODIFICADO: conteo, árbol, mini-sección "## Force HTTPS (Fase 5 / 5.6)"
AGENTS.md                                                  # MODIFICADO: conteo 283
.agents/README.md                                          # MODIFICADO: índice — fase5 (6 specs), conteo 51 (commiteado con el draft)

.analisis/ (gitignored — edición local, sin commit)
  seguridad.md                                             # MODIFICADO: #23 → Mitigado + residual
  plan/plan-accion.md                                      # MODIFICADO: fila 5.6 + Métricas de éxito
```

Sin cambios en `routes/`, `bootstrap/`, `phpunit.xml`, `config/` fuera de `app.php`.

---

## 5. Decisiones técnicas

Fijadas (grilling Q1–Q12, 2026-11-21), no negociables salvo volver el spec a `Borrador`:

- **Válvula explícita (Q6):** `FORCE_HTTPS` env → `config('app.force_https')`, default `false`, único gate, sin inferencia ni exenciones por entorno. Asimetría deliberada con 5.5: la cookie `secure` es un flag **pasivo** topología-agnóstico (inferir de `APP_URL` es seguro); el redirect es una acción **activa** topología-dependiente — inferir loop-earía detrás de un proxy no confiado por el mero hecho de fijar `APP_URL=https` (que 5.5 ya empuja). El modo de fallo de "ops olvidó la válvula" está acotado: la cookie `Secure` de 5.5 ya protege la sesión de facto (el browser no la transmite sobre http).
- **Redirect 302 (Q7):** todos los métodos; `Location` preserva path+query; temporal y reversible mientras ops verifica. Alternativas descartadas: 301 (cache agresivo, difícil de deshacer), 307/308 (los webhooks de gateways no re-POSTean igual y las URLs generadas tras el flip ya nacen https).
- **Stack global tras `TrustProxies` (Q8):** `request->secure()` solo es fiable una vez resuelto el proxy trust; global cubre web + api + maintenance (los tokens bearer de la API también viajan en claro sin esto). Clase `ForceHttps` (estilo framework, sin sufijo, como `TrustHosts`).
- **Proxies vía config (Q9):** `env('TRUSTED_PROXIES')` → `config/app.php`; formato CSV de IPs/CIDR o `'*'` — nativos del parent (vendor l.83–89); override `proxies()`, sin `env()` en `app/` (regla 4.3).
- **`.env.example` mantiene `http://localhost` (Q2):** default https rompería login local (cookie `Secure` inferida true no persiste sobre http con `php artisan serve`); la guía de producción viaja como comentario + residual.
- **Estado objetivo Mitigado + residual (Q3):** repo completo; flip de producción pendiente de evidencia (patrón 5.9).
- **HSTS OUT (Q5); wizard/`SystemSetting` edges aceptados sin fix (Q4).**
- **Framework:** PHPUnit `^10.1` (Laravel 11); AAA comentado; `@group smoke` + `@group security` en la clase feature; `DatabaseTransactions` (patrón 5.4) — F1–F4 ejercitan `GET /login` público, sin dependencia de schema.
- **Mecanismo U (nota 6):** mutar `$_ENV` directo + `require base_path('config/app.php')` fresco + restore en `tearDown` (patrón probado en `SessionConfigTest`, 5.5 — incl. el cast `'true'`/`'false'` de `Env::get` sellado por sus U3/U4).
- **Mecanismo F (nota 7):** kernel real + `SymfonyRequest::create` + `createTestRequest` (framework, `MakesHttpRequests` l.743) + overrides de config en runtime (patrón `TrustHostsSmokeTest`).

---

## 6. Catálogo de casos

### 6.1 Config: mapeo env → force_https/trusted_proxies (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | default de la válvula | `FORCE_HTTPS` ausente de `$_ENV` | `require config/app.php` (evaluación fresca) | `['force_https'] === false` |
| U2 | `'true'` cast a bool | `$_ENV['FORCE_HTTPS'] = 'true'` | idem | `['force_https'] === true` |
| U3 | `'false'` string no truthy | `$_ENV['FORCE_HTTPS'] = 'false'` | idem | `['force_https'] === false` |
| U4 | proxies default | `TRUSTED_PROXIES` ausente de `$_ENV` | idem | `['trusted_proxies'] === null` |
| U5 | wildcard `'*'` | `$_ENV['TRUSTED_PROXIES'] = '*'` | idem | `['trusted_proxies'] === '*'` |
| U6 | lista CSV pasa intacta | `$_ENV['TRUSTED_PROXIES'] = '10.0.0.5,10.0.0.6'` | idem | `['trusted_proxies'] === '10.0.0.5,10.0.0.6'` |

`tearDown`: unset de las keys inyectadas (los defaults de `phpunit.xml` quedan intactos).

### 6.2 HTTP: redirect y proxies reales (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | válvula on: http → 302 | `config(['app.force_https' => true])` en runtime | GET `http://localhost/login?foo=bar` vía kernel real | 302 y `Location: https://localhost/login?foo=bar` (path+query preservados) |
| F2 | válvula on: https pasa | `config(['app.force_https' => true])` en runtime | GET `https://localhost/login` vía kernel real | 200, sin redirect |
| F3 | válvula off: aislación de suite | sin override (defaults de `phpunit.xml`) | GET `http://localhost/login` vía kernel real | 200, sin redirect — la suite existente no se entera del middleware |
| F4 | proxy confiado no loop-ea | `config(['app.force_https' => true])` + `config(['app.trusted_proxies' => '*'])` en runtime; request http con `HTTP_X_FORWARDED_PROTO=https` | GET `http://localhost/login` vía kernel real | 200, sin redirect — `request->secure()` true vía header confiado |

### Tests de caracterización

Ninguno: no hay bug vigente que se espere romper — la palanca ES este spec.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `config/app.php`: exactamente 2 keys nuevas tras `'asset_url'` (l.86) — inspección: diff de 2 líneas + comentario breve.
- [x] `app/Http/Middleware/ForceHttps.php`: válvula off → passthrough; on + `!secure` → 302 a https mismo path+query; on + secure → passthrough; sin `env()` ni `request()` (reglas 4.3/4.4).
- [x] `app/Http/Kernel.php`: `ForceHttps::class` en stack global inmediatamente tras `TrustProxies::class` (l.50); grupos web/api/maintenance sin cambios.
- [x] `app/Http/Middleware/TrustProxies.php`: override `proxies()` devolviendo `config('app.trusted_proxies')`; headers intactos (ll.22–27).
- [x] `.env.example`: comentario bajo `APP_URL` (producción DEBE usar `https://<dominio-real>`); `# FORCE_HTTPS=` y `# TRUSTED_PROXIES=` comentadas con una línea de explicación cada una.
- [x] U1–U6 en verde (focal: 6 passed); `$_ENV` restaurado en `tearDown`.
- [x] F1–F4 en verde (focal: 4 passed); estáticos de Symfony reseteados en `tearDown` (patrón `TrustHostsSmokeTest`).
- [x] Suite completa: **283 passed** — exacto 273+10, verificados contra salida real de `php artisan test`.
- [x] `phpunit.xml` sin cambios.
- [x] `composer analyse` sin nuevas entradas vs baseline; `composer lint` limpio.
- [x] Conteos **283** en `README.md`, `tests/README.md`, `AGENTS.md`; `tests/README.md` con `ForceHttpsSmokeTest` en el árbol + mini-sección "## Force HTTPS (Fase 5 / 5.6)"; `.agents/README.md` índice fase5 (6 specs, conteo 51 — commiteado con el draft).
- [x] Cierres locales `.analisis/` (gitignored): `seguridad.md` #23 → Mitigado + residual; `plan-accion.md` fila 5.6 + métrica (redacciones de la nota 8).
- [x] Sin flip del `.env` local (válvula on rompería `php artisan serve`, que no sirve TLS).
- [x] Rama `spec/app-url-https-force-tls-fase5-impl` + PR (squash, 1 aprobación + CI); sin push a `main`.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Válvula on detrás de proxy sin `TRUSTED_PROXIES` ⇒ redirect infinito (sitio caído) | Alto | Válvula explícita default off (Q6) + par documentado en `.env.example` + F4 documenta el camino correcto |
| Links firmados emitidos pre-flip mueren en 403 al re-entrar por https (la firma incluye el scheme) | Medio | Aceptado transicional: TTL 2h de `temporarySignedRoute` (`Auth/SignupController` l.84/194, `PaymentController` l.404/417/431) acota la ventana |
| Webhooks de pagos de transacciones previas al flip llamando http | Medio | Idem transicional: las URLs regeneradas tras el flip nacen https; verificación server-to-server ya endurecida (spec 1.6) |
| `'false'` string truthy si alguien implementara el gate con env directo en `app/` | Medio | Regla 4.3 (solo `config()` en app) + U3 sella el cast del mapeo |
| `config:cache` congela la evaluación de env | Medio | Nota operacional: re-ejecutar `config:cache` tras el flip (idem residual de 5.5) |
| Estáticos de Symfony (`Request::setTrustedProxies`) contaminando la suite | Bajo | `tearDown` con reset (lección 5.4: `TrustHostsSmokeTest` resetea `setTrustedHosts([])`); el middleware re-establece por request, el reset protege requests no-kernel |
| Wizard `/install` y `SystemSettingController` escriben `APP_URL` por fuera de `.env.example` | Bajo | Edges aceptados y documentados (Q4, §2 OUT) |

**Residual operacional (Q3/Q12):** en producción setear `APP_URL=https://<dominio>` + `FORCE_HTTPS=true` (+ `TRUSTED_PROXIES` solo si hay LB/proxy que termina TLS) y re-ejecutar `config:cache`; el certificado/TLS real es infraestructura. Evidencia pendiente (patrón 5.9).

**Pendiente de decisión:** ninguno (Q1–Q12 del grilling 2026-11-21).

---

## 9. Estimación

- Código (config 2 keys + middleware + Kernel + override + env): 0.25 día.
- Tests U1–U6 + F1–F4 + corrida focal: 0.25 día.
- Docs + conteos + cierres documentales + PR: 0.25 día.
- Total: ~0.75 día.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.6 (l.111)
- `.analisis/seguridad.md` → Seg-23 (l.139); contexto Seg-22 (l.137, cookie inferida por 5.5) y Seg-21 (l.135, TrustHosts 5.4)
- `config/app.php` → l.84 (`url`), l.86 (`asset_url`)
- `app/Http/Kernel.php` → ll.48–56 (stack global; `TrustProxies` l.50)
- `app/Http/Middleware/TrustProxies.php` → l.15 (`$proxies`), ll.22–27 (headers con `X_FORWARDED_PROTO`)
- `vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php` → l.69 (fallback `trustedproxy.proxies`), ll.83–84 (`'*'`), ll.87–89 (CSV), ll.165–168 (hook `proxies()`)
- `vendor/laravel/framework/src/Illuminate/Foundation/Testing/Concerns/MakesHttpRequests.php` → l.743 (`createTestRequest`)
- `.env.example` → ll.5–6 (`APP_URL` + comentario TrustHosts), ll.30–32 (válvula de sesión, 5.5)
- `phpunit.xml` → l.23 (`APP_URL=http://localhost`)
- `config/installer.php` → l.71 (wizard `required|url`); `app/Http/Controllers/Admin/SystemSettingController.php` → l.43 (edge aceptado Q4)
- `.agents/spec-impl/spec-session-database-secure-fase5.md` → precedente directo: válvula comentada, patrón U (`$_ENV` + `require`), conteos verificados, redacciones documentales, residual 5.9
- `.agents/spec-impl/spec-trust-hosts-fase5.md` → precedente del patrón F (kernel real + `SymfonyRequest::create`), reset de estáticos en `tearDown`
- `.agents/spec/spec-conventions.md` · `.agents/spec/spec-template.md`
- `.agents/skills/testing-conventions.md` · `.agents/skills/workflow-ramas.md`
- `AGENTS.md` → "Commands" (suite 273 → 283)

---

### Notas de implementación

1. **Cambios exactos en `config/app.php`** (tras `'asset_url'`, l.86):
   ```php
   // Seg-23 (Fase 5 / 5.6): redirect 302 http→https; proxies detrás de TLS-terminating LB.
   'force_https' => env('FORCE_HTTPS', false),
   'trusted_proxies' => env('TRUSTED_PROXIES'),
   ```
   `env()` sigue legal en `config/` (regla 4.3); el cast de `'true'`/`'false'` lo hace `Env::get` (probado por U2/U3 del spec 5.5).
2. **`ForceHttps` (sketch):**
   ```php
   class ForceHttps
   {
       public function handle(Request $request, Closure $next): mixed
       {
           if (config('app.force_https') && ! $request->secure()) {
               return redirect()->to(
                   str_replace('http://', 'https://', $request->fullUrl())
               );
           }

           return $next($request);
       }
   }
   ```
   302 es el default del redirector; `str_replace` solo toca el scheme del `fullUrl()` absoluto (path+query intactos). Sin `env()`, sin `request()` — lee config y la request inyectada.
3. **Override en `TrustProxies` (3 líneas):**
   ```php
   protected function proxies()
   {
       return config('app.trusted_proxies');
   }
   ```
   El parent ya resuelve `'*'` (vendor l.83–84) y CSV (l.87–89); `static::$alwaysTrustProxies` queda para uso del framework.
4. **`Kernel`:** insertar `ForceHttps::class,` inmediatamente tras `TrustProxies::class,` (l.50) — `request->secure()` depende del proxy trust ya resuelto.
5. **`.env.example`:** bajo `APP_URL` (ll.5–6) añadir la línea de guía; tras el bloque de sesión (l.32) añadir:
   ```
   # Redirect 302 http→https. Encender solo cuando la app se sirve con TLS (válvula explícita, no APP_URL).
   # FORCE_HTTPS=
   # Solo detrás de un LB/proxy que termina TLS: CSV de IPs/CIDR o * (nunca * en despliegues multi-hop).
   # TRUSTED_PROXIES=
   ```
6. **Mecanismo U1–U6:** mutar `$_ENV` directo (`$_ENV['FORCE_HTTPS'] = 'true'`) y `require base_path('config/app.php')` — el reader de dotenv consulta `$_ENV` en cada `env()` (mecanismo probado por `SessionConfigTest`, 5.5). `tearDown`: unset de `FORCE_HTTPS`/`TRUSTED_PROXIES`. Correr la focal **antes** que la suite completa.
7. **Mecanismo F1–F4:** helper estilo `getWithHost` de `TrustHostsSmokeTest` — `SymfonyRequest::create($uri, 'GET', [], [], [], $server)` + `$this->app->make(HttpKernel::class)->handle($this->createTestRequest($symfonyRequest))`. F1: URI absoluta `http://localhost/login?foo=bar`; F2: `https://localhost/login` (`create` deriva scheme de la URI); F4: server con `HTTP_X_FORWARDED_PROTO=https` + `config(['app.trusted_proxies' => '*'])` (el parent confía el calling IP → header confiado → `secure()` true). `tearDown`: reset de estáticos Symfony (`Request::setTrustedProxies([], <headerSet del middleware>)`) — verificación real en implementación, lección 5.4.
8. **Redacciones documentales (patrón trust-hosts/session):**
   - `seguridad.md` #23 → añadir al final: "— **mitigado (2026-11-21, spec `spec-app-url-https-force-tls-fase5`)**: middleware `ForceHttps` (302 http→https, válvula `FORCE_HTTPS` default off) + `TrustProxies` operable vía `TRUSTED_PROXIES` (CSV/`*`); `.env.example` documenta el flip; tests U1–U6 + F1–F4. Residual operacional: `APP_URL=https` + `FORCE_HTTPS=true` (+ `TRUSTED_PROXIES` si hay LB) + `config:cache` en producción pendientes de evidencia."
   - `plan-accion.md` fila 5.6 → "**Mitigado (2026-11-21):** spec `spec-app-url-https-force-tls-fase5` — middleware `ForceHttps` (302, válvula `FORCE_HTTPS` explícita default off), `TrustProxies` vía `TRUSTED_PROXIES` (CSV/`*`), `.env.example` con guía; U1–U6 + F1–F4; residual: flip del `.env` de producción (`APP_URL` https + `FORCE_HTTPS` + re-cache) pendiente de evidencia (patrón 5.9)."
   - `plan-accion.md` Métricas de éxito → línea: "✅ Redirect http→https opt-in (`FORCE_HTTPS`) + `TrustProxies` operable; `APP_URL` https documentado como requisito de producción (Fase 5 / 5.6 — spec-app-url-https-force-tls-fase5, 2026-11-21; residual operativo: `.env` de producción)."
9. **Verificación rápida:**
   ```bash
   php artisan test tests/Unit/Config/ForceHttpsConfigTest.php tests/Feature/Security/ForceHttpsSmokeTest.php
   composer analyse && composer lint && php artisan test
   ```
