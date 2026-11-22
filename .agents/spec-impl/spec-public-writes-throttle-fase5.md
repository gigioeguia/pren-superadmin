---
id: spec-public-writes-throttle-fase5
titulo: "Throttle y validación de los writes públicos del sitio (blogs + contacto)"
estado: Aprobado
fecha: 2026-11-22
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.14) + .analisis/seguridad.md Seg-27
cobertura: "throttle:5,1 + validación endurecida en POST /blogs y POST /contact/store; DemoMode bloquea ambos en demo; guard arch-test; sin moderación (hallazgo lateral #28)"
---

# Spec — Throttle y validación de los writes públicos del sitio (blogs + contacto)

> Resumen de una línea: cerrar Seg-27 aplicando `throttle:5,1` y validación a los **dos** writes públicos sin auth del sitio (`POST /blogs` y `POST /contact/store`), eliminando el vector de spam anónimo ilimitado y los 500 por input junk.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.14) + `.analisis/seguridad.md` Seg-27 (hallazgo lateral del inventario del spec 5.8).

---

## 1. Objetivo

Que ningún visitante anónimo pueda escribir en la DB pública sin límite de tasa ni validación de integridad: rate limit por IP + reglas de validación completas + escritura con arrays explícitos, sin cambiar el contrato JSON de los formularios.

---

## 2. Alcance

### IN

- `POST /blogs` (`BlogController::store`): `throttle:5,1`, `blog_id => required|integer|exists:blogs,id`, `email => max:255`, redirect derivado server-side, array explícito.
- `POST /contact/store` (`ContactController::store`): `throttle:5,1`, `email`/`message` `max:255`, array explícito.
- `DemoMode::ADMIN_DISABLED_ROUTES`: añadir `blogs.store` + `contact.store` (bloqueo en `APP_ENV=demo`, 499 JSON).
- Vista `resources/views/web/blog/show.blade.php`: eliminar el hidden input `blog_slug`.
- `database/factories/CommentFactory.php`: nueva.
- Tests `Pub1`–`Pub7` en `tests/Feature/Public/PublicRoutesSmokeTest.php`.
- Guard `tests/Unit/Hygiene/PublicWritesArchTest.php` (espejo del U6 del spec 5.11).

### OUT

- **Moderación de comentarios** (approve/hide/delete admin): el admin hoy solo tiene un GET listador; construir moderación es una feature, no higiene. Se registra como hallazgo **#28** + fila **5.19** en `plan-accion.md`. Mantener `comments.status` default 1 (auto-publicación): un default 0 sin moderación mataría la feature (nadie vería jamás un comentario).
- **Recaptcha/honeypot**: no elegido; el throttle es la mitigación primaria de esta fase.
- **Named rate limiter con 429 JSON**: descartado por precedente 3.7 (inline, shorthand); el JS del form (`ajaxform_instant_reload`) ya degrada el 429 HTML a `Notify("error", e)`.
- `GET /blogs`, `GET /blogs/{slug}`, `GET /filter-blogs-by-tag` y el resto de rutas públicas de lectura: sin cambios.
- Flip del `.env` de producción (`TRUSTED_PROXIES`): dueño operativo = spec 5.6; este spec solo documenta la dependencia.
- Los 41 sitios `except(` restantes fuera de estos 2 controladores: residual 5.11, audit-pending.

### Cobertura objetivo

- 100 % de los writes públicos web sin auth (2/2 rutas: `blogs.store`, `contact.store`).

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Ruta `blogs.store` con nombre estable | Existe (`Route::resource('blogs')->only(index,show,store)`, `routes/web.php:7`) | Partir la declaración preservando URI y nombre (ver §5) |
| P2 | `BlogFactory` | Existe (`database/factories/BlogFactory.php`) | Ninguna |
| P3 | `CommentFactory` | Falta | Crear (`blog_id` FK + name/email/comment/status) |
| P4 | Suite pública con `setUp` de Option `manage-pages` | Existe (`PublicRoutesSmokeTest`, `RefreshDatabase`) | Extender con Pub1–Pub7 |
| P5 | Mecánica de test de entorno demo | Existe (`app()->detectEnvironment(fn () => 'demo')`, `DemoModeTest`) | Reutilizar en Pub7 |

---

## 4. Estructura propuesta

```
routes/web.php                                      # MODIFICADO: split resource + throttle en store; throttle en contact.store
app/Http/Controllers/BlogController.php             # MODIFICADO: store (validación blog_id/email, redirect derivado, array explícito)
app/Http/Controllers/ContactController.php          # MODIFICADO: store (max email/message, array explícito)
app/Http/Middleware/DemoMode.php                    # MODIFICADO: ADMIN_DISABLED_ROUTES + blogs.store + contact.store
resources/views/web/blog/show.blade.php             # MODIFICADO: quitar hidden input blog_slug
database/factories/CommentFactory.php               # NUEVO
tests/Feature/Public/PublicRoutesSmokeTest.php       # MODIFICADO: Pub1–Pub7
tests/Unit/Hygiene/PublicWritesArchTest.php         # NUEVO (guard anti-reincidencia)
```

---

## 5. Decisiones técnicas

Fijadas en grilling (Q1–Q12, 2026-11-22). No negociables salvo volver el spec a `Borrador`:

- **Throttle inline `throttle:5,1`** en ambas rutas (Q4): shorthand por ruta, sin limiters nombrados — precedente `spec-throttle-otp-login-fase3` y `.agents/skills/secure-coding.md` punto 9. 429 con página HTML por defecto, aceptado.
- **Split del resource** (Q7): `Route::resource('blogs', …)->only('index','show')` + `Route::post('blogs', [BlogController::class, 'store'])->name('blogs.store')->middleware('throttle:5,1')`. URI y nombre `blogs.store` intactos (los usa el JS del form y `DemoMode`); los GET públicos quedan sin throttle. `contact.store` ya es ruta suelta: solo añadir el middleware.
- **Validación** (Q5/Q6): `blog_id => required|integer|exists:blogs,id` (elimina el 500 actual por FK junk); `email => required|email|max:255` (blogs y contacto); `message => required|string|max:255` (contacto, límite real de columna `string(255)`); `comment => required|string|max:255` ya presente.
- **Redirect derivado server-side** (Q5): cargar el blog validado (`Blog::findOrFail($validated['blog_id'])`, inalcanzable como 404 tras `exists`) y construir `route('blogs.show', $blog->slug)`. `blog_slug` sale del contrato: ni del controlador ni del hidden input de la vista. Ningún dato del cliente construye el redirect.
- **Arrays explícitos** (Q6): `Comment::create([...])` y `Message::create([...])` con claves explícitas; jamás `$request->except()`/`$request->all()` — espíritu de la convención 5.11 extendido a estos dos controladores. `$fillable` de ambos modelos ya contiene la asignación masiva (aquí es higiene, no vulnerabilidad).
- **`comments.status` default 1 se mantiene** (Q2): auto-publicación; moderación OUT (hallazgo #28 + fila 5.19).
- **DemoMode** (Q3): `blogs.store` + `contact.store` en `ADMIN_DISABLED_ROUTES` — esa lista no exige `auth()`, así que bloquea a cualquiera (incluido admin) en demo, 499 JSON con mensaje estándar.
- **Tests** (Q8): extender `PublicRoutesSmokeTest` (los Pub viven ahí; precedente "throttle se testea en el smoke del dominio": L7 en `WebLoginSmokeTest`, A20/A21 en `ApiAuthSmokeTest`), `RefreshDatabase`, `setUp` con Option `manage-pages` heredado, patrón de 429 con `Retry-After` (L7), demo vía `app()->detectEnvironment(fn () => 'demo')`.
- **Framework:** PHPUnit sobre MySQL `dbrentpro_test` (ADR-0001); sin mocks nuevos (no hay Mail ni HTTP externo en juego).
- **Guard** (Q12): `PublicWritesArchTest` espejo de `UserMassAssignmentArchTest` (U6) — allowlist de los 2 controladores, `assertStringNotContainsString` contra `$request->except(` **y** `$request->all(`.

---

## 6. Catálogo de casos

### 6.1 Writes públicos (`Pub`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| Pub1 | Comentario válido | Blog activo en DB; sin sesión | POST /blogs {name, email, comment, blog_id, blog_slug basura} | 200 JSON {message, redirect=/blogs/{slug real del blog}}; fila `comments` con `blog_id` correcto y `status` 1 |
| Pub2 | Throttle de comentarios | Sin sesión; misma IP | 6.º POST /blogs válido < 1 min | 429 + header `Retry-After` |
| Pub3 | blog_id inexistente | Blog con id=999 no existe | POST /blogs {blog_id: 999, …} | 422 con error de validación (hoy: 500 `QueryException` por FK) |
| Pub4 | Campos ausentes | Sin sesión | POST /blogs {} | 422 con errores de name/email/comment/blog_id |
| Pub5 | Email sobre límite de columna | Sin sesión | POST /blogs {email: 256 chars, …} | 422 |
| Pub6 | Throttle de contacto | Sin sesión; misma IP | 6.º POST /contact/store válido < 1 min | 429 + header `Retry-After` |
| Pub7 | Demo bloquea ambos writes | `APP_ENV=demo` (`app()->detectEnvironment`) | POST /blogs y POST /contact/store con payload válido | 499 JSON {message: "This action is disabled in demo mode."} en ambos; 0 filas creadas |

> Notas: el caso válido de contacto ya existe como smoke (`PublicRoutesSmokeTest::test_contact_store_creates_message`) y no se duplica. Pub3–Pub5 son peticiones únicas: el throttle (middleware) no interfiere porque el contador se resetea entre tests (`RefreshDatabase` limpia la tabla de cache).

### Tests de caracterización

Ninguno: el comportamiento inseguro actual se corrige dentro de este mismo spec; no hay caso cuya rotura se posponga a una fase posterior.

---

## 7. Criterios de aceptación (Definition of Done)

- [ ] `routes/web.php`: `POST /blogs` y `POST /contact/store` llevan `throttle:5,1`; `GET /blogs`, `GET /blogs/{slug}` y `GET /filter-blogs-by-tag` quedan sin throttle.
- [ ] El nombre de ruta `blogs.store` se preserva tras el split (guards existentes `RouteNamesSmokeTest` + `RouteMethodsReflectionTest` en verde, sin cambios en sus allowlists).
- [ ] `BlogController::store`: `blog_id` validado `required|integer|exists:blogs,id`; `email` con `max:255`; redirect construido exclusivamente con el slug del blog cargado server-side; `blog_slug` no se lee.
- [ ] `ContactController::store`: `email` y `message` con `max:255`.
- [ ] 0 `$request->except(` / `$request->all(` en `BlogController` y `ContactController` — `PublicWritesArchTest` en verde.
- [ ] `resources/views/web/blog/show.blade.php` sin hidden input `blog_slug`.
- [ ] `DemoMode::ADMIN_DISABLED_ROUTES` incluye `blogs.store` y `contact.store`.
- [ ] `CommentFactory` creada; Pub1–Pub7 en verde sobre `dbrentpro_test` (suite 488 + 7 = 495).
- [ ] Suite completa en verde; PHPStan nivel 5 sin regresiones de baseline; Pint en verde.
- [ ] Cierres documentales: `seguridad.md` #27 corregido (dos writes públicos, ambos cerrados) + **#28 "sin moderación de comentarios"** nuevo; `plan-accion.md` fila 5.14 cerrada con referencia a este spec + fila **5.19** nueva (dueño: spec futuro de moderación).

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| IP compartida detrás de LB/CDN sin `TRUSTED_PROXIES` → bucket colectivo: el sitio entero comparte 5 writes/min | Medio | Residual **documentado** (Q10): el flip del `.env` de 5.6 (`TRUSTED_PROXIES`) es el dueño operativo; evidencia vía mecanismo `ops:verify-deploy` (5.9). Sin cambios en ese comando en este spec |
| Spammer con IPs rotativas: `throttle:5,1` por IP no le frena | Medio | Aceptado en fase de higiene (Q2): moderación (fila 5.19) y/o recaptcha son follow-up si el spam real lo exige |
| El split del resource altera la declaración de ruta | Bajo | URI y nombre preservados; los guards de nombres/métodos de ruta existentes corren en suite |
| La demo pierde la evaluación del formulario público | Bajo | Aceptado (Q3): 499 con mensaje claro; filosofía demo = no writes |
| 429 HTML sobre form AJAX | Bajo | Aceptado (Q4): `ajaxform_instant_reload` ya degrada cualquier error a `Notify("error", e)` |

**Pendiente de decisión:** ninguna — Q1–Q12 cerradas en grilling (2026-11-22).

---

## 9. Estimación

- Implementación + tests + guard: 0.5–1 día.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.14 (y fila 5.19 nueva al cierre)
- `.analisis/seguridad.md` → Seg-27 (+ #28 nuevo al cierre)
- `.agents/spec-impl/spec-throttle-otp-login-fase3.md` → precedente de `throttle:5,1` inline y patrón de test 429 + `Retry-After` (L7/A20/A21)
- `.agents/spec-impl/spec-typos-rutas-fase5.md` → registró Seg-27 como hallazgo lateral (Q11(A))
- `.agents/spec-impl/spec-user-fillable-restrictivo-fase5.md` → convención de arrays explícitos y arch-test espejo (U6)
- `.agents/spec-impl/spec-demo-reset-demomode-fase3.md` → `DemoMode` por `APP_ENV=demo`
- `.agents/spec-impl/spec-app-url-https-force-tls-fase5.md` → `TRUSTED_PROXIES` (dueño del residual)
- `.agents/spec-impl/spec-log-level-warning-fase5.md` → `ops:verify-deploy` (mecanismo de evidencia del residual)
- `.agents/spec/spec-conventions.md`
- `AGENTS.md` → Throttle (Fase 3 / 3.7), Gotchas de `User::$fillable` y DemoMode
