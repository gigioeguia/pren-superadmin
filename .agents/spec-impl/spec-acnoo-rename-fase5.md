---
id: spec-acnoo-rename-fase5
titulo: "Rename del prefijo `Acnoo` (92 controladores + familia `acnoo*` del trait), comentarios muertos (DT-B4) y de-branding de seed data (Fase 5 / 5.10)"
estado: Borrador
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.10, l.115) — .analisis/deuda-tecnica.md (DT-B1 l.95-96, DT-B4 l.104-105; DT-B7 l.113-114 ya cerrado en el cierre de Fase 4)
cobertura: Cerrar DT-B1/DT-B4 con el alcance real del inventario (2026-11-21): drop total del prefijo en 92 controladores + `AcnooLateFeeService` (0 colisiones verificadas sobre 137 controladores; 0 referencias dinámicas por string — todo `::class`), rename del trait `HasAcnooActions` → `HasTableActions` con su familia de 12 métodos + hook `deletingAcnooRow` (arrastra 56 strings de acción en rutas, 34 `only()`, 6 `LogicException`, 33 consumidores, 2 smoke tests), eliminación de ~25 marcadores de procedencia, baseline por replace mecánico con invariante 245 entradas / 447 errores, emails demo `*@acnoo.com` → `@example.com` (6 seeders + `UserFactory` + 3 test files), branding PropertyRent en `OptionTableSeeder`, DT-B4 completo (`.htaccess`/`composer.json`/`Kernel.php`), selector JS huérfano, typo `'meassage'` → `'message'` (registrado desde spec 5.3), arch-test anti-reincidencia 0 `acnoo` en 8 directorios. 2 PRs: impl (mecánico) + cleanup (semántico). Catálogo U1–U2 + F1 (3 casos nuevos; la suite existente es la red de comportamiento). README (DT-B7) documentado como ya-cerrado, sin re-auditoría.
---

# Spec — Rename del prefijo `Acnoo`, comentarios muertos y de-branding (Fase 5 / 5.10)

> Resumen de una línea: eliminar el heritage vendor del código (nombres de clase, familia de métodos del trait, comentarios de procedencia, seed data) y sellarlo con un arch-test de 0 `acnoo`, en dos PRs — rename mecánico puro y limpieza semántica.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.10, l.115) — `.analisis/deuda-tecnica.md` (DT-B1, l.95-96: *"Prefijo `Acnoo` en casi todos los controladores [...] heredado del vendor original. Sin valor, añade ruido."*; DT-B4, l.104-105: *"`.htaccess` con bloques comentados de cPanel; `composer.json` con `dont-discover: []` vacío; `Kernel.php` con `EnsureFrontendRequestsAreStateful` comentado. Ruido documental."*). DT-B7 (README) ya cerrado al cierre de Fase 4 (2026-11-21, fila 5.10).

---

## 1. Objetivo

Cerrar DT-B1/DT-B4 en su alcance real: no solo renombrar 92 controladores, sino la familia `acnoo*` completa del trait (~200 identificadores en el hot path: 56 strings de acción, 34 `only()`, hook `deletingAcnooRow`), los ~25 marcadores de procedencia, el seed data vendor (emails demo + branding visible) y los 3 ítems clásicos de ruido documental — sellado con un arch-test que hace la clase entera no-reincidente (0 `acnoo`, contenido + filenames, sin excepciones).

---

## 2. Alcance

### IN — PR1: rename mecánico (`spec/acnoo-rename-fase5-impl`)

1. **Drop total del prefijo** en 92 controladores (42 Admin, 31 Api, 19 Landlord — p. ej. `Admin\AcnooTenantController` → `Admin\TenantController`, `Api\AcnooPropertyController` → `Api\PropertyController`, `Landlord\AcnooRentInvitationController` → `Landlord\RentInvitationController`) + `AcnooLateFeeService` → `LateFeeService` (call site: `app/Console/Commands/ApplyLateFees.php`). **0 colisiones** verificadas (2026-11-21): los no-prefijados existentes (`Admin/{User,Notification,Property,Permission,Plan,Role,ServiceCharge,Setting,SystemSetting,Translation}`, `Landlord/{Dashboard,Notification,Property,Subscription}`, `Api/User`, `Api/Auth/*`) no chocan con ningún `Acnoo*` del mismo namespace.
2. **Trait + familia completa**: `HasAcnooActions` → `HasTableActions` con el mapeo de §4 (12 métodos + hook `deletingAcnooRow` → `deletingRow`). `deleteAll`/`status`/`exportExcel`/`exportCsv` intactos (ya no-prefijados). Los 33 consumidores (`use HasTableActions`) y los ~18 overrides locales de `acnooFilter` migran con el mismo mapeo.
3. **Rutas** (`routes/admin.php` 169 líneas, `routes/landlord.php` 91, `routes/api.php` 53): FQCN por `::class` en los 3 archivos, 56 strings de acción `'acnooFilter'` → `'filter'` (36 admin, 20 landlord), 34 strings `->only('index', 'acnooFilter', ...)`. **Nombres y URIs de rutas NO cambian** — solo action methods (guard: `RouteNamesSmokeTest` + suite smoke).
4. **6 mensajes `LogicException`** del trait (embeben nombres de método: *"debe definir `acnooRedirectRoute()`..."* → `redirectRoute()`).
5. **~25 marcadores de procedencia** `// Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.` **eliminados aquí** (Q15: son naming-coupled — dejarlos publicaría comentarios que citan nombres inexistentes). El docblock del trait queda como única documentación del contrato; la spec histórica `spec-trait-acnoo-fase0.md` es el registro.
6. **Smoke tests renombrados**: `AcnooActionsSmokeTest` → `TableActionsSmokeTest` (`tests/Feature/Admin/`), `LandlordAcnooActionsSmokeTest` → `LandlordTableActionsSmokeTest` (`tests/Feature/Landlord/`); asserts actualizados: `'acnooFilter'` → `'filter'` (Landlord :217), map `AcnooAmenityController::class => ['filter', 'deleteAll', 'status']` (:229-230), `class_uses_recursive(HasTableActions::class)` (:234). Los docblocks G1–G8/H1–H4 mantienen la referencia a `spec-trait-acnoo-fase0` (histórica, intacta).
7. **Arch-test U1** (nuevo `tests/Unit/Hygiene/VendorPrefixArchTest.php`): 0 `acnoo` case-insensitive en `app/` + `routes/` (contenido + filenames).
8. **Baseline** `phpstan-baseline.neon`: replace mecánico de los 128 paths con `Acnoo` **y de los mensajes congelados** (embeben FQCN). Invariante: **245 entradas / 447 errores exactos**. No es regeneración (regla AGENTS.md Tooling); diff justificado en el PR.
9. **Docs naming-coupled mínimos**: `AGENTS.md` ("Most controllers prefixed Acnoo" fuera, `HasAcnooActions` → `HasTableActions` en Controller layout/Gotchas), `tests/README.md` (:6-7 trait, :68 clase renombrada en tabla de caracterizaciones).

### IN — PR2: limpieza semántica (`spec/acnoo-rename-fase5-cleanup`)

10. **Emails demo** `*@acnoo.com` → `@example.com`: `UserSeeder` (:17-28), `PermissionSeeder` (:166, patrón `$generated_role.'@example.com'`), `PlanSubscribeSeeder` (:15), `RentSeeder` (:15), `ReviewSeeder` (:15), `BlogSeeder` (:13), `UserFactory` (:94-120 — email + argumento de `DemoCredentials::password()`); tests: `DemoUserPasswordTest` (:23-26), `SeedOverExistingDataTest` (:34), `DatabaseSeederIdempotencyTest` (:55, :106-107).
11. **Branding `OptionTableSeeder`** (filas `general` :13 y `manage-pages` :21) per tabla §4: `copy_right`, `footer_link_text`/`footer_link`, `footer_short_title`, `footer_socials_links` (4× `acnooteam`), `get_app_play_store_link` (`com.acnoo.rentpro`), `slider_description`, `app_email` (`acnoteam@gmail.com` → `support@example.com`).
12. **DT-B4 completo**: `composer.json` :71 (`"dont-discover": []` fuera — sin `dont-discover`, mismo comportamiento), `app/Http/Kernel.php` :79 (línea comentada `EnsureFrontendRequestsAreStateful` fuera — se elimina el comentario, no se habilita el middleware), `.htaccess` :9-14 (bloque cPanel `ea-php70`, íntegramente comentado) **y** :16-22 (bloque `FilesMatch` comentado) fuera; el shim rewrite :1-7 intacto.
13. **Selector JS huérfano**: `public/backend/assets/web/js/custom.js` :6 (`.acnoo-dashboard-container` — clase inexistente en cualquier blade, vestigio vendor).
14. **Typo `'meassage'` → `'message'`**: `Api/WithdrawController:27` (post-rename; los endpoints hermanos `invoice`/`summary` ya usan `'message'`; los tests existentes no asertan la key — spec 5.3 lo dejó deliberadamente OUT). Caso F1 nuevo.
15. **Arch-test U2** (extiende `VendorPrefixArchTest`): 0 `acnoo` case-insensitive en las 8 dirs (`app/`, `routes/`, `config/`, `database/`, `tests/`, `resources/`, `lang/`, `public/`), contenido + filenames, **sin excepciones** (los emails dejaron de serla al entrar el ítem 10).
16. **Docs vivos**: `README.md` (:40 emails demo, :56 conteo real de la suite), `AGENTS.md` (emails, conteo, convención de controladores final), `tests/README.md` (:144 nota `'meassage'`, conteo), `.agents/README.md` (índice 54→55, fase5 9→10), y cierres locales gitignored (§N notas de implementación).

### OUT (documentado, sin fix aquí)

- **`.htaccess` :3-6**: `RewriteCond`+`RewriteRule` duplicado — código vivo, no comentario (Q17 fuera por disciplina de scope; observación registrada).
- **Hallazgos laterales del inventario en `OptionTableSeeder`** (no-acnoo, misma fila `general`): `app_name:"Rentpro"` (nombre vendor, cf. `com.acnoo.rentpro`), `app_url` = link de descarga de Google Drive (junk manifiesto; no se renderiza en vistas — solo lo valida `Admin/SettingController:34`), `app_address:"Dhaka"`/`app_phone` (contacto vendor), y filas `term-condition`/`privacy-policy` (:14-15) con boilerplate de **QUIZYS.IN** (otro producto). Registrados como candidatos a fila nueva del plan o absorción en revisión — no bloquean este spec.
- **README re-audit** (DT-B7): ya cerrado en el cierre de Fase 4 (Q1). Solo se toca por efecto colateral (emails + conteo).
- **37 specs-impl históricas** (424 líneas con `acnoo`): intactas — registro de lo que fue cierto en su momento (Q11).
- **12 controladores >200 líneas + duplicación 3 capas** de entidades no migradas: deuda 5.13. **GET mutante**: residual 5.8. **`POST /blogs`**: fila 5.14.
- **`npm run build`**: no requerido — el JS tocado (`public/backend/`) está fuera del pipeline de Vite.

### Cobertura objetivo

- U1 (PR1): 0 `acnoo` en `app/` + `routes/`; U2 (PR2): 0 `acnoo` en las 8 dirs (contenido + filenames).
- Suite completa verde con **conteo idéntico al base verificado contra la salida real** (lección 5.3: `AGENTS.md` dice 331, `README.md` dice 283 — P2 resuelve la discrepancia antes de empezar).
- `composer analyse` [OK] con baseline 245/447 exactos; `composer lint` verde; `RouteNamesSmokeTest` verde (0 nombres/URIs cambiadas).

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Ramas de trabajo | Draft creado (`spec/acnoo-rename-fase5-draft`) | Impl en `spec/acnoo-rename-fase5-impl` (PR1) y `spec/acnoo-rename-fase5-cleanup` (PR2), secuencial |
| P2 | Suite base en verde + conteo real | Discrepancia documentada: AGENTS.md 331 (sync en cierre 5.9, commit `47cb73e`) vs README.md 283 (stale) | `php artisan test` y fijar el conteo real **antes** de empezar; usarlo como invariante de PR1 y para el sync de Q16 |
| P3 | Inventario Acnoo | Hecho (2026-11-21): 97 archivos, 0 refs dinámicas, 0 colisiones, 34 `use HasAcnooActions`, 56+34 strings de rutas, baseline 128 paths | Ninguna: base del catálogo y del DoD |
| P4 | Colisiones de nombres de método | Falta verificar | Pre-check antes del rename: `ReflectionClass::getMethods()` sobre los 33 consumidores vs {filter, view, model, query, viewData, redirectRoute, deleteMessage, export, perPage, excelFilename, csvFilename, statusMessage, deletingRow}; resultado (esperado: vacío) anexo al PR1 |
| P5 | Estrategia de baseline | Definida aquí (§2 IN.8) | Replace mecánico paths+mensajes; invariante 245/447; diff revisado en el PR |
| P6 | Orden PR1 → PR2 | — | PR2 aplica sobre nombres renombrados; no paralelizar |
| P7 | Inventario de seed data | Hecho (2026-11-21): 6 seeders + `UserFactory` + 3 test files con emails; keys de branding catalogadas (tabla §4) | Ninguna |

---

## 4. Estructura propuesta

```
PR1 — spec/acnoo-rename-fase5-impl (mecánico: git mv + replace, cero lógica nueva)
app/Http/Controllers/Admin/Acnoo*.php   (42)              # RENOMBRADOS: drop prefijo
app/Http/Controllers/Api/Acnoo*.php     (31)              # RENOMBRADOS
app/Http/Controllers/Landlord/Acnoo*.php (19)            # RENOMBRADOS
app/Services/AcnooLateFeeService.php                     # RENOMBRADO → LateFeeService.php
app/Traits/HasAcnooActions.php                           # RENOMBRADO → HasTableActions.php
                                                            #   (+ familia §4.1, + 6 LogicException)
app/Console/Commands/ApplyLateFees.php                   # MODIFICADO (FQCN del servicio)
routes/admin.php · routes/landlord.php · routes/api.php  # MODIFICADOS: FQCN + 56 action strings
                                                            #   + 34 only(); nombres/URIs intactos
phpstan-baseline.neon                                    # MODIFICADO: 128 paths + mensajes FQCN
                                                            #   (invariante 245 entradas / 447 errores)
tests/Feature/Admin/AcnooActionsSmokeTest.php            # RENOMBRADO → TableActionsSmokeTest.php
tests/Feature/Landlord/LandlordAcnooActionsSmokeTest.php # RENOMBRADO → LandlordTableActionsSmokeTest.php
tests/Unit/Hygiene/VendorPrefixArchTest.php              # NUEVO: U1
AGENTS.md · tests/README.md                              # MODIFICADOS: refs naming-coupled mínimas

PR2 — spec/acnoo-rename-fase5-cleanup (semántico)
database/seeders/UserSeeder.php · PermissionSeeder.php · PlanSubscribeSeeder.php ·
  RentSeeder.php · ReviewSeeder.php · BlogSeeder.php · OptionTableSeeder.php   # emails + branding
database/factories/UserFactory.php                       # emails (+ argumento DemoCredentials::password)
tests/Feature/Seeders/DemoUserPasswordTest.php · SeedOverExistingDataTest.php ·
  DatabaseSeederIdempotencyTest.php                      # emails @example.com
tests/Feature/Withdraw/WithdrawIndexSmokeTest.php        # EXTENDIDO: F1 (key 'message')
tests/Unit/Hygiene/VendorPrefixArchTest.php              # EXTENDIDO: U2 (8 dirs)
composer.json · app/Http/Kernel.php · .htaccess         # DT-B4
public/backend/assets/web/js/custom.js                   # :6 selector huérfano fuera
app/Http/Controllers/Api/WithdrawController.php          # 'meassage' → 'message'
README.md · AGENTS.md · tests/README.md · .agents/README.md   # docs vivos
.analisis/ (gitignored — edición local al cierre, sin commit)
  deuda-tecnica.md                                        # DT-B1/B4 cerrados
  plan/plan-accion.md                                     # fila 5.10 cerrada + Métricas de éxito
```

### 4.1 Mapeo de la familia del trait (Q4 — verificación de colisiones en P4)

| Actual | Nuevo |
|--------|-------|
| `HasAcnooActions` | `HasTableActions` |
| `acnooFilter` | `filter` |
| `acnooView` | `view` |
| `acnooModel` | `model` |
| `acnooQuery` | `query` |
| `acnooViewData` | `viewData` |
| `acnooRedirectRoute` | `redirectRoute` |
| `acnooDeleteMessage` | `deleteMessage` |
| `acnooExport` | `export` |
| `acnooPerPage` | `perPage` |
| `acnooExcelFilename` | `excelFilename` |
| `acnooCsvFilename` | `csvFilename` |
| `acnooStatusMessage` | `statusMessage` |
| `deletingAcnooRow` (hook, spec 2.6) | `deletingRow` |
| `deleteAll` · `status` · `exportExcel` · `exportCsv` | intactos (ya no-prefijados) |

### 4.2 Branding `OptionTableSeeder` (Q12 — defaults honestos, no links muertos a dominios ajenos)

| Key (fila) | Actual | Nuevo |
|------------|--------|-------|
| `general.copy_right` | `COPYRIGHT © 2024 Acnoo, All rights Reserved` | `COPYRIGHT © 2026 PropertyRent, All rights Reserved` |
| `general.footer_link_text` | `acnoo` | `PropertyRent` |
| `general.footer_link` | `https://acnoo.com/` | `#` |
| `general.app_email` | `acnoteam@gmail.com` | `support@example.com` |
| `manage-pages.slider_description` | `Acnoo is a fully featured dashboard and admin template comes with tones of Awesome designed.` | `PropertyRent is a fully featured property rental management platform with admin, landlord and tenant panels.` |
| `manage-pages.footer_short_title` | `Acnoo - We Are Theme, Web, Plugin and App Development Agency` | `PropertyRent — Property Rental Management` |
| `manage-pages.footer_socials_links` (twitter/instagram/facebook/youtube) | `.../acnooteam...` (4) | `#` cada una |
| `manage-pages.get_app_play_store_link` | `https://play.google.com/store/apps/details?id=com.acnoo.rentpro&hl=es_419` | `""` (no hay app real) |

Intactos (no vendor): `general.footer_text` ("Development By"), `get_app_apple_store_link` (genérico). Nota: `firstOrCreate` no actualiza filas existentes — las DBs ya sembradas conservan los valores viejos (residual, patrón 5.2); los defaults son editables desde `SystemSetting`.

---

## 5. Decisiones técnicas

Fijadas por grilling Q1–Q18 (2026-11-21), no negociables salvo volver el spec a `Borrador`:

- **Q1 — README ya-cerrado**: DT-B7 quedó cerrado al cierre de Fase 4; este spec lo documenta con evidencia, sin re-auditoría. Se toca solo por efecto colateral (emails :40, conteo :56).
- **Q2 — Rename completo**: clases **y** familia del trait. Dejar la familia conservaría ~200+ identificadores vendor en el hot path — exactamente el "ruido sin valor" de DT-B1. Mismo esfuerzo mecánico, mismo PR.
- **Q3 — Drop total del prefijo**: cero colisiones verificadas (P3); un prefijo neutral perpetuaría el anti-patrón (prefijo = namespace redundante).
- **Q4 — Mapeo §4.1**: nombres obvios sin magia; P4 verifica que ningún consumidor tenga ya un método con esos nombres.
- **Q5 — De-branding de seed data dentro**: emails `@example.com` + branding PropertyRent. Residual `firstOrCreate` documentado (DBs ya sembradas no se actualizan; re-semebrar una DB poblada crea usuarios demo duplicados — DBs demo desechables, patrón 5.2).
- **Q6 — `'meassage'` hard fix**: API interna, consumidor móvil hipotético (precedente residual 5.8); los tests existentes no asertan la key (spec 5.3 lo dejó OUT deliberadamente) — F1 la aserta desde ahora.
- **Q7 — Comentarios**: DT-B4 (3 ítems, verificados vigentes tras el upgrade L11: `composer.json:71`, `Kernel.php:79`, `.htaccess:9-14` + bloque extra :16-22) + **eliminación** (no actualización) de los ~25 marcadores de procedencia + selector JS huérfano. El contrato del trait vive en su docblock; las specs-impl son el registro histórico.
- **Q8 — Baseline por replace mecánico**: paths **y** mensajes (los congelados embeben FQCN); invariante 245 entradas / 447 errores; diff justificado en el PR. Cualquier error "nuevo" que el replace no explique → desviación documentada (spec vuelve a Borrador), prohibido expandir la baseline para pasar.
- **Q9 — 2 PRs**: rename mecánico revisable como diff puro (~100 archivos, cero lógica) vs limpieza semántica (branding, comentarios, typo).
- **Q10/Q13 — Arch-test sin excepciones**: U1 (app/+routes/) aterriza en PR1; U2 (8 dirs, contenido + filenames, case-insensitive) en PR2 — tras PR1 aún quedan emails/branding en `database/`.
- **Q11 — Docs**: 37 specs-impl históricas intactas; vivos actualizados (AGENTS.md, README.md, tests/README.md, .agents/README.md, plan-accion.md, deuda-tecnica.md).
- **Q12 — Branding con nombre de producto real** (PropertyRent, per README) + `#`/`""` para links inexistentes: placeholders honestos.
- **Q14 — 1 spec, 2 ramas**, smoke tests → `TableActionsSmokeTest`/`LandlordTableActionsSmokeTest`.
- **Q15 — Marcadores de procedencia eliminados en PR1** (naming-coupled): así PR1 deja `app/`+`routes/` en 0 `acnoo` y PR2 no re-visita 92 controladores.
- **Q16 — Sync del conteo de suite** en README.md/AGENTS.md con la salida real al momento del merge (hoy 283 vs 331, al menos uno miente).
- **Q17 — `.htaccess` :3-6 (dup vivo) fuera**: disciplina de scope; registrado en §2 OUT.
- **Framework**: PHPUnit `^10.1` (Laravel 11); MySQL `dbrentpro_test`; arch tests `PHPUnit\Framework\TestCase` puro con `glob`/`file_get_contents` (patrón `SeedersArchTest`); `WithdrawIndexSmokeTest` mantiene su infra existente (Sanctum); sin mocks nuevos.
- **Nombres/URIs de rutas congelados**: el rename toca FQCN y action methods, nunca `->name()` ni URIs (`RouteNamesSmokeTest` como guard).
- **Baseline rule** (AGENTS.md Tooling): la edición de §2 IN.8 es la única permitida — replace mecánico justificado, no regeneración.

---

## 6. Catálogo de casos

### 6.1 Hygiene guards (`U`) — `tests/Unit/Hygiene/VendorPrefixArchTest.php` (nuevo)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | 0 `acnoo` tras el rename (PR1) | repo en `spec/acnoo-rename-fase5-impl` | iterar `app/` + `routes/` (contenido + nombres de archivo, case-insensitive, needle dinámico `'ac'.'noo'`) | 0 matches en cualquier archivo (php/js/blade) y 0 filenames |
| U2 | 0 `acnoo` global (PR2) | repo en `spec/acnoo-rename-fase5-cleanup` | iterar las 8 dirs (`app/`, `routes/`, `config/`, `database/`, `tests/`, `resources/`, `lang/`, `public/`), extensiones de texto (php/js/css/md/json), self-exclusión del propio archivo del guard | 0 matches, **sin lista de excepciones** (emails y branding ya migrados) |

### 6.2 Withdraw — typo fix (`F`) — `tests/Feature/Withdraw/WithdrawIndexSmokeTest.php` (extensión; F1–F3 existentes intactos)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | key de mensaje correcta | patrón setup de F1–F3 existente (Sanctum landlord) | `GET /api/v1/landlord/withdraw` | **200** + JSON con key `'message'` (sin `'meassage'`) |

Actualizaciones a tests existentes (no son casos nuevos; la suite los cubre): 2 smoke tests renombrados + asserts (PR1); `DemoUserPasswordTest`/`SeedOverExistingDataTest`/`DatabaseSeederIdempotencyTest` migrados a `@example.com` (PR2).

### Tests de caracterización

Ninguno nuevo: no caracterizamos — reparamos. La caracterización viva (`LandlordTableActionsSmokeTest::test_delete_all_deletes_foreign_amenities_without_ownership_check`, Seg-5, `tests/README.md:68`) solo cambia de archivo/clase en PR1; el `@group characterization` y su aserción quedan intactos.

---

## 7. Criterios de aceptación (Definition of Done)

**PR1 — rename mecánico:**

- [ ] 92 controladores + `LateFeeService` renombrados (drop del prefijo); 0 `Acnoo` en filenames de `app/`
- [ ] `HasTableActions` + mapeo §4.1 aplicado (12 métodos + hook); 0 identificadores `acnoo*` en `app/`; `deleteAll`/`status`/`exportExcel`/`exportCsv` intactos
- [ ] P4 ejecutado sin colisiones de método (resultado anexo al PR)
- [ ] `routes/`: FQCN nuevos en los 3 archivos, 56 `'acnooFilter'`→`'filter'`, 34 `only()` actualizados; 0 `acnoo` en `routes/` (U1)
- [ ] 6 `LogicException` del trait actualizados; ~25 marcadores de procedencia eliminados
- [ ] Smoke tests renombrados con asserts/maps/docblocks actualizados; suite completa verde con conteo idéntico al fijado en P2
- [ ] `composer analyse` [OK] con baseline **245 entradas / 447 errores exactos** (diff de paths+mensajes justificado en el PR); `composer lint` verde
- [ ] `RouteNamesSmokeTest` verde — 0 nombres/URIs de rutas cambiadas
- [ ] `AGENTS.md` + `tests/README.md` refs naming-coupled actualizadas (Controller layout, trait, :6-7/:68)
- [ ] Rama `spec/acnoo-rename-fase5-impl` + PR squash + CI verde; sin push a `main`

**PR2 — limpieza semántica:**

- [ ] Emails `@example.com` en 6 seeders + `UserFactory` (+ argumentos de `DemoCredentials::password()`) + 3 test files; suite verde
- [ ] Branding `OptionTableSeeder` per tabla §4.2; 0 `Acnoo`/`acnoo` en el seeder
- [ ] DT-B4: `composer.json:71` fuera, `Kernel.php:79` fuera, `.htaccess:9-14` y `:16-22` fuera (shim :1-7 intacto)
- [ ] `custom.js:6` selector huérfano fuera
- [ ] `'meassage'` → `'message'` (`Api/WithdrawController:27`) + F1 verde
- [ ] U2 verde: 0 `acnoo` en las 8 dirs, sin excepciones (needle dinámico + self-exclusión)
- [ ] Docs vivos: `README.md` (:40 emails + conteo real), `AGENTS.md` (emails + conteo + convención final), `tests/README.md` (:144 + conteo), `.agents/README.md` (índice 54→55, fase5 9→10)
- [ ] Cierres locales gitignored: `deuda-tecnica.md` DT-B1/DT-B4 cerrados, `plan-accion.md` fila 5.10 cerrada + Métricas de éxito
- [ ] Residuales documentados: DBs ya sembradas conservan emails/branding viejos (re-seed crea duplicados — patrón 5.2); typo-fix visible solo para consumidor API hipotético (precedente 5.8)
- [ ] Rama `spec/acnoo-rename-fase5-cleanup` + PR squash + CI verde; DoD tachado antes de pedir merge

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Diff de ~100 archivos en PR1 | Medio | PR1 exclusivamente mecánico (`git mv` + replace por tabla de pares); cero cambios semánticos; review con `git diff -M` |
| Baseline: replace incompleto deja errores "nuevos" (mensajes embeben FQCN) | Medio | Invariante 245/447; cualquier error no explicable por el rename → desviación documentada, spec vuelve a Borrador; prohibido expandir baseline para pasar |
| Algún consumidor define ya `filter`/`view`/`query`/... como método propio | Bajo | P4 pre-check `ReflectionClass` antes del rename; resultado anexo al PR |
| Referencia string no inventariada (instantiación dinámica) | Bajo | Inventario 2026-11-21: 0 refs dinámicas (todo `::class`); U1/U2 como red de captura |
| `firstOrCreate` por email crea usuarios demo duplicados en DBs ya sembradas | Bajo | Residual documentado (patrón 5.2); DBs demo desechables; passwords no predecibles ya implementados (5.2) |
| Consumidor API hipotético nota `'meassage'`→`'message'` | Bajo | Hard fix (Q6, precedente 5.8); residual documentado |
| El arch-test se auto-marca (needle dentro de `tests/`) | Bajo | Needle dinámico `'ac'.'noo'` + self-exclusión del archivo del guard (§N notas) |
| Branding con `#`/`""` como defaults | Bajo | Placeholders honestos; editables desde `SystemSetting`; `firstOrCreate` preserva customizaciones de DBs existentes |

**Pendiente de decisión:** ninguna (grilling Q1–Q18 cerrado el 2026-11-21). Los hallazgos laterales del inventario (`app_name:"Rentpro"`, `app_url` Drive junk, `app_address`/`app_phone`, boilerplate QUIZYS en `term-condition`/`privacy-policy`) quedan registrados en §2 OUT — absorbibles en la revisión del spec o como fila nueva del plan, sin bloquear la implementación.

---

## 9. Estimación

- PR1 (rename 100 archivos + baseline + smoke tests + U1 + corridas): ~1 día.
- PR2 (seed data + DT-B4 + typo + U2 + docs): ~0.5 día.
- Cierres documentales + residuales: ~0.25 día.
- Total: ~1.75 días.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.10 (l.115)
- `.analisis/deuda-tecnica.md` → DT-B1 (l.95-96), DT-B4 (l.104-105), DT-B7 (l.113-114, ya cerrado)
- `AGENTS.md` → "Controller layout" (prefijo Acnoo, `HasAcnooActions`, 33 consumidores), "Seeders" (`*@acnoo.com`), "Tooling" (regla del baseline)
- `app/Traits/HasAcnooActions.php` → familia `acnoo*`, 6 `LogicException`, hook `deletingAcnooRow` (spec 2.6)
- `app/Services/AcnooLateFeeService.php` · `app/Console/Commands/ApplyLateFees.php` (call site)
- `routes/admin.php` (169 líneas Acnoo) · `routes/landlord.php` (91) · `routes/api.php` (53; `withdraw` apiResource :55)
- `phpstan-baseline.neon` → 128 paths con `Acnoo`
- `database/seeders/OptionTableSeeder.php` → :13 (`general`), :21 (`manage-pages`), :14-15 (QUIZYS, OUT)
- `database/seeders/UserSeeder.php:17-28` · `PermissionSeeder.php:166` · `PlanSubscribeSeeder.php:15` · `RentSeeder.php:15` · `ReviewSeeder.php:15` · `BlogSeeder.php:13` · `database/factories/UserFactory.php:94-120`
- `tests/Feature/Seeders/DemoUserPasswordTest.php:23-26` · `SeedOverExistingDataTest.php:34` · `DatabaseSeederIdempotencyTest.php:55,106-107`
- `tests/Feature/Admin/AcnooActionsSmokeTest.php` (:22 G1–G8, :230 map) · `tests/Feature/Landlord/LandlordAcnooActionsSmokeTest.php` (:217 assert, :229 map, :212-213 patrón router)
- `tests/Feature/Withdraw/WithdrawIndexSmokeTest.php` (F1–F3 del spec 5.3; se extiende con F1' de este spec)
- `tests/Unit/Seeders/SeedersArchTest.php` → patrón de arch-test (TestCase puro + glob)
- `composer.json:71` · `app/Http/Kernel.php:79` · `.htaccess:9-14,16-22` (DT-B4, verificados vigentes 2026-11-21)
- `public/backend/assets/web/js/custom.js:6` (selector huérfano)
- `app/Http/Controllers/Api/AcnooWithdrawController.php:27` (`'meassage'`; renombrado a `Api/WithdrawController` en PR1)
- `.agents/spec-impl/spec-trait-acnoo-fase0.md` → origen de la familia (histórico, intacto; §12 inventario)
- `.agents/spec-impl/spec-withdraw-index-request-fase5.md` → registro del typo `'meassage'` (histórico, intacto)
- `.agents/spec-impl/spec-typos-rutas-fase5.md` → precedente: hallazgos del inventario + guards de higiene + redacciones de cierre
- `.agents/README.md` → índice (54 total, fase5 9 → pasarán a 55/10)
- `.agents/spec/spec-conventions.md` · `.agents/spec/spec-template.md`
- `.agents/skills/crear-spec.md` · `.agents/skills/workflow-ramas.md` · `.agents/skills/testing-conventions.md`

---

### Notas de implementación

1. **Estrategia del rename (PR1):** `git mv` por par (preserva history) + replace del nombre corto y del FQCN en el mismo commit. Orden sugerido: trait primero (familia §4.1 + 6 `LogicException`), controladores (imports `use HasTableActions` + overrides locales + FQCN propios), `routes/` (FQCN + action strings + `only()`), comando `ApplyLateFees`, smoke tests, baseline (último, con `composer analyse` validando el invariante). Los ~25 marcadores de procedencia se eliminan en el mismo pase.
2. **Guard (esqueleto, patrón `SeedersArchTest`):**
   ```php
   private function assertNoVendorPrefix(array $dirs): void
   {
       $needle = 'ac'.'noo';                    // dinámico: el propio guard no contiene el literal
       foreach ($this->textFilesIn($dirs) as $file) {
           if ($file === __FILE__) { continue; } // self-exclusión (U2 escanea tests/)
           $this->assertStringNotContainsString($needle, (string) file_get_contents($file), "Vendor prefix in {$file}");
           $this->assertStringNotContainsString($needle, basename($file), "Vendor prefix in filename {$file}");
       }
   }
   // textFilesIn: extensiones php/js/css/md/json, case-insensitive vía strtolower($content)
   ```
   U1 llama con `['app', 'routes']` (PR1); U2 añade `config`, `database`, `tests`, `resources`, `lang`, `public` (PR2). `strtolower(file_get_contents(...))` cubre `Acnoo`/`acnoo`/`ACNOO`.
3. **Baseline (PR1):** `grep -c 'message:' phpstan-baseline.neon` = 245 antes y después; `composer analyse` [OK] con el conteo de errores congelados (447) idéntico — solo cambian paths y FQCN dentro de los mensajes. El diff del baseline se justifica en la descripción del PR (regla AGENTS.md: edición puntual, no regeneración).
4. **`UserFactory` (PR2):** cambiar email y el argumento de `DemoCredentials::password()` en el mismo edit (la derivación usa el email; :94/:97 y :118/:120).
5. **Redacciones de cierre (patrón 5.8, aplicadas al verificar el DoD):**
   - `deuda-tecnica.md` DT-B1 → "— **cerrado (2026-11-XX, spec `spec-acnoo-rename-fase5`)**: prefijo eliminado en 92 controladores + familia del trait (`HasTableActions`, mapeo §4.1); guard U1/U2 (0 `acnoo` en 8 dirs)."
   - `deuda-tecnica.md` DT-B4 → "— **cerrado (2026-11-XX, spec `spec-acnoo-rename-fase5`)**: `.htaccess` (ambos bloques), `composer.json` (`dont-discover`) y `Kernel.php` limpios; ~25 marcadores de procedencia y selector JS huérfano fuera."
   - `plan-accion.md` fila 5.10 → "**Cerrado (2026-11-XX):** spec `spec-acnoo-rename-fase5` — drop del prefijo (92 controladores + servicio) + trait `HasTableActions` con familia completa, marcadores de procedencia fuera, emails demo `@example.com` + branding PropertyRent, DT-B4 completo, `'meassage'`→`'message'`, arch-test U1/U2 sin excepciones; residual: DBs ya sembradas conservan datos viejos (patrón 5.2) + consumidor API hipotético (precedente 5.8); hallazgos laterales (app_name Rentpro/app_url junk/QUIZYS) registrados en el spec §2 OUT."
   - `plan-accion.md` Métricas de éxito → "✅ 0 `acnoo` en `app/`, `routes/`, `config/`, `database/`, `tests/`, `resources/`, `lang/`, `public/` (guard `tests/Unit/Hygiene/VendorPrefixArchTest.php` — Fase 5 / 5.10, 2026-11-XX)"
   - `AGENTS.md` → "Controller layout": quitar "Most controllers prefixed `Acnoo` (vendor heritage)", `HasAcnooActions` → `HasTableActions` en todo el archivo (Controller layout, 0.3 si aplica), "Seeders": `*@acnoo.com` → `*@example.com`.
   - `.agents/README.md` → índice 54→55, fase5 (9→10) con `spec-acnoo-rename-fase5`.
6. **Verificación rápida:**
   ```bash
   php artisan test tests/Unit/Hygiene/VendorPrefixArchTest.php
   php artisan test tests/Feature/Admin/TableActionsSmokeTest.php tests/Feature/Landlord/LandlordTableActionsSmokeTest.php
   php artisan test tests/Feature/Withdraw/WithdrawIndexSmokeTest.php
   composer analyse && composer lint && php artisan test
   grep -ric acnoo app/ routes/ | grep -v ':0' ; grep -ric acnoo config/ database/ tests/ resources/ lang/ public/ | grep -v ':0'   # PR2: vacío
   ```
7. **No-ops explícitos:** `npm run build` no se requiere (el JS tocado está fuera de Vite); `public/build/` no se regenera; `.phpunit.result.cache` se regenera solo (contiene nombres de clases viejos hasta la próxima corrida — no es fuente).
