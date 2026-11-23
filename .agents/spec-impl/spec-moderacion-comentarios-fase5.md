---
id: spec-moderacion-comentarios-fase5
titulo: "Moderación de comentarios de blogs (hide/re-approve admin)"
estado: Aprobado
fecha: 2026-11-23
origen: .analisis/plan/plan-accion.md Fase 5 / 5.19 (Seg-30)
cobertura: Ruta admin de toggle de status de comentarios (post-moderación) + permiso comments-update + listador per-blog sin filtro de status + D-FIX-1/D-FIX-2 declarados; 8 tests (F43, G1–G6, Pub8)
---

# Spec — Moderación de comentarios de blogs

> Resumen: `comments.status` default 1 auto-publica y ninguna vista/ruta admin puede ver o cambiar ese status (el listador filtra `whereStatus(1)`) → el spam público es permanente aunque rate-limited (Seg-30). Se entrega post-moderación: el admin oculta/re-aprueba desde el listador per-blog existente.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.19) — `Seg-30` (hallazgo lateral del spec 5.14, §2 OUT). Diseño cerrado en ronda de decisiones 2026-11-23 (Q1–Q8 + catálogo + naming).

---

## 1. Objetivo

Cerrar Seg-30: dar al admin capacidad de ocultar (`1→0`) y re-aprobar (`0→1`) comentarios del blog, con visibilidad completa del inventario (incluidos los ocultos) en el listador admin.

---

## 2. Alcance

### IN

- Ruta nueva `POST admin/comments/status/{id}` → `admin.comments.status` (`routes/admin.php`, bloque comments l.242–244) vía `HasTableActions::status()` **1:1, sin editar el trait**.
- Permiso `comments-update` (letra `u`): `'comments' => 'r,d'` → `'r,u,d'` en `PermissionSeeder` (`:68` Super Admin, `:124` Admin); gate `permission:comments-update` → `status` en el constructor de `CommentController` (espejo de `blogs-update` → `status`, `Admin/BlogController:23-27`).
- `status` añadido a `Comment::$fillable` (`app/Models/Comment.php:13` — sin él el trait hace **silent no-op** por mass assignment).
- Hook `statusMessage(): 'Comment'` en `CommentController` (requisito del trait; sin él `LogicException`).
- Lister per-blog `admin.blogs.filter.comment` (`Admin/BlogController::filterComment:107-113`): **sin `whereStatus(1)`** (l.110) y pasando `$blog` a la vista (`compact('comments', 'blog')` — hoy no se pasa, l.112).
- Vistas `resources/views/admin/blogs/comments/`: columna **Status** (header en `index.blade.php`, l.71–75) + celda toggle en `datas.blade.php` — espejo verbatim del toggle de `admin/blogs/datas.blade.php:14-18` (`<label class="switch"><input class="status" data-url="{{ route('admin.comments.status', $comment->id) }}">`) + badge visual para ocultos; hidden `blog_id` en los forms search/per_page (l.18, 40).
- **D-FIX-1 (declarado):** scope por `blog_id` opcional en `CommentController::query()` (l.37–47 hoy sin scope → el search del listador devuelve comentarios de **todos** los blogs). Con input `blog_id` → scope; sin él → contrato actual preservado.
- **D-FIX-2 (declarado):** `destroy` con `Comment::findOrFail($id)->delete()` (hoy `where('id')->delete()`, l.24 → id inexistente devuelve **200 success** silent no-op). Nuevo contrato: id inexistente → **404**.
- `DemoMode`: `'admin.comments.status'` en `ADMIN_DISABLED_ROUTES` (`app/Http/Middleware/DemoMode.php:13-33`, junto a `admin.comments.{delete-all,destroy}`).
- Tests: F43, G1–G6, Pub8 (§6).

### OUT

- **Pre-moderación** (flip default a 0): decidido **post-moderación** — continuidad con la decisión explícita del spec 5.14 ("un default 0 sin moderación mataría la feature"), sin migración, Pub1 (`status => 1`) intacto, UX del comentarista legítimo sin cambio. Trade-off aceptado: el spam es visible hasta que el admin actúe (throttle `5,1` del 5.14 limita el flujo).
- **Índice global de comentarios + menú propio del sidebar**: no requerido por Seg-30 (la entrada existente blogs → icono `fa-comment` ya da acceso al listador). Mejora lateral posible, no en este spec.
- **Redirect de `destroy`/`deleteAll` → `admin.blogs.index`** (`CommentController:28,:61`) en vez de volver al listador del blog: lateral preexistente (UX), sin relación con moderación.
- **Guard del dropdown "CMS Manage"** (`@canany(['term-conditions-read','privacy-policy-read'])` sin `blogs-read`, `layouts/admin/partials/sidebar.blade.php:588`): lateral preexistente (visibilidad de menú; las rutas siguen protegidas por middleware).
- **`$request->status` sin validar en `HasTableActions::status()`** (l.42): verbatim del trait, mismo hueco que `blogs.status`/`faqs.status`/`clients.status` (familia Seg-5). No se edita el trait compartido.
- **Respuestas anidadas**: `comment_id` existe en migración/casts pero es código muerto (grep en `app/` → solo el cast). No se construye replies; columna solo documentada.
- **Paginación/limit-3 del front** (`BlogController::show:25`): fuera.
- **Notificación a admins al recibir comentario**: fuera.
- **Migración de Comment a Services**: deuda 3-capas lateral del paraguas 5.13 (`spec-unificar-capas-services-fase5` §OUT).
- **Policy para Comment**: patrón del módulo = middleware `permission:` (como Blog); sin IDOR — entidad admin-global del CMS.
- **i18n**: reuso de keys (`"Status"` existe, `lang/en.json:133`); `statusMessage()` devuelve `'Comment'` crudo (espejo del `'Blog'` verbatim, sin `__()` — key "Comment" existe igualmente, `en.json:137`). Los 104 locales restan sin sync: residual aceptado, sin precedente (`lang/en.json` intacto desde el commit inicial).
- **Errata documental**: `spec-permission-middleware-admin-fase3.md:118` dice "comments es r,d porque sus rutas son index/destroy/filter/delete-all" — **stale** (la ruta index ya no existe; ahora `r,u,d`). Se anota aquí; el spec viejo Implementado no se edita (patrón errata del spec 5.8).

### Cobertura objetivo

- 100% de las rutas admin mutantes de comentarios (`status`, `destroy`, `delete-all`) cubiertas por test de permiso o comportamiento; efecto público del hide cubierto por Pub8.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Trait `HasTableActions::status()` | Existe (`app/Traits/HasTableActions.php:40-51`) | Ninguna — adopción 1:1 |
| P2 | `CommentController::model()` | Existe (`:32-35`, `Comment::class`) | Ninguna |
| P3 | Hook `statusMessage()` | Falta (`LogicException` si se invoca `status()`, trait l.143-146) | Añadir → `'Comment'` |
| P4 | `status` en `Comment::$fillable` | Falta (`:13` — update mass assignment lo descarta: silent no-op) | Añadir |
| P5 | Permiso `comments-update` | Falta (`'comments' => 'r,d'`, PermissionSeeder `:68`/`:124`) | Cambiar a `'r,u,d'` (idempotente, firstOrCreate) |
| P6 | Lister sin filtro de status | Falta (`filterComment` `whereStatus(1)`, `BlogController:110`) | Quitar filtro + `compact('comments','blog')` |
| P7 | Ruta `admin.comments.status` | Falta (`routes/admin.php:242-244`) | Declarar `Route::post('comments/status/{id}', …)->name('comments.status')` |
| P8 | Bloqueo DemoMode de la nueva ruta | Falta (`ADMIN_DISABLED_ROUTES`, `DemoMode:13-33`) | Añadir `'admin.comments.status'` |

---

## 4. Estructura propuesta

```
routes/admin.php                                        +1 ruta (comments.status, tras l.244)
app/Models/Comment.php                                  $fillable += 'status'
app/Http/Controllers/Admin/CommentController.php        constructor (+status), destroy (D-FIX-2),
                                                        query (D-FIX-1), +statusMessage()
app/Http/Controllers/Admin/BlogController.php          filterComment: sin whereStatus(1) + $blog a la vista
app/Http/Middleware/DemoMode.php                        ADMIN_DISABLED_ROUTES += 'admin.comments.status'
database/seeders/PermissionSeeder.php                   'comments' => 'r,u,d' (×2 roles)
resources/views/admin/blogs/comments/index.blade.php    columna Status + hidden blog_id en forms
resources/views/admin/blogs/comments/datas.blade.php    celda toggle .status + badge oculto
tests/Feature/Admin/CommentModerationTest.php           G1–G6 (nuevo)
tests/Feature/Admin/PermissionDenialSmokeTest.php       +F43
tests/Feature/Public/PublicRoutesSmokeTest.php          +Pub8
```

Trait compartido: `HasTableActions` — **se adopta, no se edita**.

---

## 5. Decisiones técnicas

Fijadas, no negociables salvo volver el spec a `Borrador`:

- **Post-moderación**: `comments.status` default 1 intacto, **sin migración**. El spam se oculta a posteriori; Pub1 (`status => 1` en DB tras POST `/blogs`) queda sin tocar.
- **Trait 1:1**: la ruta usa `HasTableActions::status()` tal cual. Contrato observable (fijado por hechos del JS `custom-ajax.js:379-394`): `POST {status: 0|1}` → `200` JSON `{message: 'Comment'}` — sin `redirect` (`statusRedirectRoute()` default null); toastr muestra "Comment status published/unpublished".
- **`statusMessage()`**: string crudo `'Comment'`, espejo verbatim de `'Blog'` (`Admin/BlogController:161-164`).
- **Permiso**: `comments-update` (espejo del patrón `blogs-update` → `status` del mismo módulo CMS). La letra `s` del `permissionMap` queda documentada-sin-uso (no se activa para un solo caso).
- **Fillable**: `status` en `Comment::$fillable`. Riesgo mass-assignment nulo: el único writer público (`BlogController::store`) usa arrays explícitos y `PublicWritesArchTest` (U19) prohibe `$request->except/all` en writers públicos.
- **Verbatim/D-FIX**: todo cambio de contrato observable existente queda limitado a D-FIX-1 y D-FIX-2, pre-declarados (patrón D-FIX del spec 5.18).
- **Framework**: PHPUnit 12 (`#[Group]`), DB `dbrentpro_test`, auth en tests vía `actingAs` + permisos Spatie reales.
- **Rama**: `spec/moderacion-comentarios-fase5-impl` (workflow-ramas; PR a `main`, nunca push directo).

---

## 6. Catálogo de casos

### 6.1 Guard admin — moderación (`G`) — nuevo `tests/Feature/Admin/CommentModerationTest.php`

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| G1 | Ocultar comentario | admin con `comments-update`; comentario `status=1` | `POST /admin/comments/status/{id}` body `{status: 0}` | 200 JSON `{message: 'Comment'}`; DB `status=0` |
| G2 | Re-aprobar comentario | comentario `status=0` | `POST /admin/comments/status/{id}` body `{status: 1}` | 200; DB `status=1` |
| G3 | Lister muestra ocultos (regresión) | blog con comentario `status=0` | `GET /admin/blogs/comments/{blog}` | respuesta contiene el comentario oculto (hoy: filtrado por `whereStatus(1)`) |
| G4 | D-FIX-1: scope del filter | comentarios en blogs A y B | `POST /admin/comments/filter` ajax, con `{blog_id: A}` | `{data}` renderiza solo comentarios de A; **sin** `blog_id` → sin scope (contrato actual preservado) |
| G5 | D-FIX-2: destroy inexistente | id `999999` no existe | `DELETE /admin/comments/999999` | **404** (antes: 200 success silent no-op) |
| G6 | DemoMode bloquea toggle | `APP_ENV=demo` | `POST /admin/comments/status/{id}` | **499** (espejo Pub7) |

### 6.2 Denial (`F`) — `tests/Feature/Admin/PermissionDenialSmokeTest.php`

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F43 | Toggle sin permiso | admin **sin** `comments-update` | `POST /admin/comments/status/{id}` | **403** (implementado como **F52** — el ID F43 estaba ocupado por `faqs-read`; ver §11/D1) |

### 6.3 Efecto público (`Pub`) — `tests/Feature/Public/PublicRoutesSmokeTest.php`

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| Pub8 | Oculto no renderiza en front | blog con comentarios `status=1` y `status=0` | `GET /blogs/{slug}` | renderiza solo el `status=1` (el `whereStatus(1)` del front, `BlogController:25`, se vuelve el contrato de moderación) |

### Tests de caracterización

Ninguno — el spec no congela comportamiento actual: lo corrige directamente.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] Ruta `admin.comments.status` declarada con `->name()` propio y único (`RouteNamesSmokeTest` verde)
- [x] `CommentController::status` resuelto por el trait; método existe (`RouteMethodsReflectionTest` verde)
- [x] `comments-update` creado y sincronizado a `superadmin` + `admin`; re-run de `PermissionSeeder` idempotente (firstOrCreate, DT-M9)
- [x] `status` presente en `Comment::$fillable`
- [x] `filterComment` sin `whereStatus(1)` y `$blog` pasada a la vista; toggle + badge operativos en el listador
- [x] D-FIX-1 y D-FIX-2 implementados y cubiertos (G4, G5)
- [x] `'admin.comments.status'` en `ADMIN_DISABLED_ROUTES` (G6 verde)
- [x] F43, G1–G6, Pub8 en verde; suite completa **558/558** (550 + 8)
- [x] PHPStan nivel 5: 0 adiciones al baseline (156 entradas intactas)
- [x] Pint verde (`composer lint`)
- [x] `ControllersLineLimitArchTest` verde (CommentController < 200 líneas)
- [x] Residual documentado: DBs ya sembradas requieren re-seed de `PermissionSeeder` para obtener `comments-update` (patrón 5.2/5.10)
- [x] Al verificar el DoD: flip de la fila 5.19 (`plan-accion.md`) a Cerrado y de `Seg-30` (`seguridad.md`) a remediado

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| DBs ya sembradas: roles sin `comments-update` → 403 en el toggle tras deploy | Medio | Residual documentado + seeder idempotente (re-run seguro) |
| `$request->status` sin validar (trait verbatim) | Bajo | Ruta admin-only + columna boolean (coerción/1366 en estricto); lateral familia Seg-5 documentado en §2 OUT |
| Spam visible hasta que el admin actúe (post-moderación) | Medio | Aceptado por decisión de diseño (Q1); throttle `5,1` del 5.14 limita el flujo |
| D-FIX-1 cambia el resultado del search del listador (cross-blog → scoped con `blog_id`) | Bajo | Declarado + G4 cubre ambos contratos; sin input, comportamiento actual exacto |

**Pendiente de decisión:** ninguna — la ronda de diseño del 2026-11-23 cerró modelo de moderación (Q1), superficie (Q2), permiso (Q3), mecánica (Q4), D-FIX-1/D-FIX-2 (Q5/Q6), DemoMode + laterales OUT (Q7/Q8), catálogo de tests y naming/lifecycle.

---

## 9. Estimación

- Implementación + tests: **1–2 días** (feature delgada: 1 ruta + hooks de trait + 2 vistas + seeder + 8 tests; sin migración).

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.19
- `.analisis/seguridad.md` → Seg-30
- `.agents/spec-impl/spec-public-writes-throttle-fase5.md` → 5.14, §2 OUT (origen del hallazgo; decisión "default 1" que este spec mantiene)
- `.agents/spec-impl/spec-permission-middleware-admin-fase3.md:118` → errata anotada en §2 OUT
- `.agents/spec-impl/spec-tx-legadas-services-fase5.md` → patrón D-FIX pre-declarado
- `.agents/spec/spec-conventions.md`; `.agents/skills/workflow-ramas.md` (rama `spec/moderacion-comentarios-fase5-impl`)

---

## 11. Desviaciones de implementación (pendientes de aceptación del decisor)

Documentadas al implementar (2026-11-23); ambas menores, sin cambio de contrato:

| # | Desviación | Motivo |
|---|-----------|--------|
| D1 | El caso de denial se implementa como **F52**, no F43 | El ID F43 ya estaba ocupado (`test_f43_admin_without_faqs_read_receives_403`): la serie F avanzó hasta F51 con las adiciones del spec 5.17. Renumeración mecánica; mismo escenario y aserciones (403 sin `comments-update` en `POST /admin/comments/status/{id}`). |
| D2 | El badge de ocultos usa `__('Hidden')` vía JSON-fallback (key **no** añadida a ningún archivo `lang/`) | `lang/en.json` no tiene key "Hidden"; el fallback de traducción renderiza el string inglés tal cual (patrón existente de keys sin definir). Honra el OUT "sin editar lang files / 104 locales sin sync" — el badge text materializa por fallback. |
