---
id: spec-seeders-firstorcreate-fase4
titulo: "Seeders sin IDs/rutas hardcodeadas"
estado: Aprobado
fecha: 2026-11-20
origen: .analisis/plan/plan-accion.md (Fase 4 / 4.10) + .analisis/deuda-tecnica.md (DT-M9)
cobertura: 24 seeders de DatabaseSeeder + DemoSeeder + demoReset(); idempotencia por clave natural; 0 IDs/rutas frágiles; seed seguro sobre DB ya poblada
---

# Spec — Seeders sin IDs/rutas hardcodeadas

> Resumen de una línea: eliminar los IDs numéricos literales y rutas de archivo inexistentes de todos los seeders, haciendo `db:seed` idempotente (por clave natural) y seguro sobre una DB ya poblada.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 4 / 4.10) — `DT-M9` en `.analisis/deuda-tecnica.md`.

---

## 1. Objetivo

Que cualquier entorno (fresco o ya poblado) pueda ejecutar `php artisan db:seed` sin duplicar datos, sin asumir auto-increment IDs (`landlord_id => 3`, `currency_id => 4`, `property_id => 1`…) y sin referenciar archivos que no existen en un clone limpio. Cierra DT-M9 y la cadena de fragilidad que arranca en `PermissionSeeder` (users 1–2 con `create()` no idempotente → `UserSeeder` asume ids 3–4 → 10+ seeders y `demoReset()` hardcodean `landlord_id = 3`).

---

## 2. Alcance

### IN

- Los **24 seeders** que llama `DatabaseSeeder` (barrido completo: auditar y corregir los afectados, documentar como verificados los ya limpios).
- `DemoSeeder` (fuera de `DatabaseSeeder`, invocado por `demoReset()`): landlord resuelto dinámicamente y dataset unificado con `PropertySeeder`/`IncomeSeeder`/`ExpenseSeeder`.
- `demoReset()` (`app/Helpers/Helper.php`): reemplaza `landlord_id = 3` fijo por resolución vía `is_demo`. **Supersede la limitación documentada en `spec-demo-reset-demomode-fase3` §"landlord_id = 3 no se hace configurable".**
- `PermissionSeeder`: users idempotentes (`firstOrCreate` por email) + eliminación del bloque muerto de `Landlord`/`plan_subscribe_id => 1`.
- Bug cross-tenant de `IncomeSeeder`/`ExpenseSeeder` (lote `landlord_id = null` referenciando categorías del landlord demo): el lote null pasa a crear y referenciar **sus propias** categorías.
- `GatewaySeeder`: lookup de `Currency` por `code` (hoy posicional), `env()` → `config()`, `firstOrCreate` por `name`.
- `FedaPayGatewaySeeder`: **eliminado** (huérfano, redundante con `GatewaySeeder`, con API key sandbox real embebida en git).
- `OptionTableSeeder`: per-row `firstOrCreate` por `key` (dispara observers del modelo cacheado, elimina los 4 `Artisan::call` de limpieza, dedupe `reminder-messages`).
- Imágenes: repuntar los **24 paths que no existen** (covers/floorplans de propiedades + avatar del landlord) a assets ya trackeados en `public/uploads/25/**` o al placeholder `backend/assets/img/no_image.jpg`. Los **39 paths que ya funcionan quedan intactos**.

### OUT

- Contraseñas demo no predecibles y `comand.txt` (Fase 5 / 5.1–5.2). Las credenciales demo conocidas (`superadmin`/`admin`/`landlord`/`tenant`) se conservan tal cual.
- Acoplamiento permiso→ruta de `DT-M7` (`LoginController` + `RedirectIfAuthenticated`): los módulos de `PermissionSeeder` siguen siendo contrato implícito con los nombres de rutas `admin.*`; este spec no toca módulos ni nombres.
- Media física de uploads (specs previos: 3.3 uploads normalizados, DT-M10 deleteAll media).
- Valores de datos observados pero no frágiles: `version => "2.1"` (duplicado con `routes/maintenance.php`; `ArtisanRoutesSmokeTest` lo asume) y `app_url` (link de Google Drive). Se conservan y se documentan.
- Creación de factories nuevas (ver decisión técnica D3): las 12 existentes (`UserFactory` con estados `landlord()`/`admin()`/`tenant()`/`superadmin()`/`demo()`, etc.) son suficientes.

### Cobertura objetivo

- 24/24 seeders de `DatabaseSeeder` auditados y corregidos/verificados.
- 0 literales numéricos en columnas referenciales dentro de `database/seeders/` (verificado por arch-test U1).
- 100 % de los paths de imagen sembrados resuelven a archivos existentes en un clone fresco (verificado por F3).

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | `Role`/`Permission` idempotentes por `name` en `PermissionSeeder` | Existe (`firstOrCreate` L135/L150) | Ninguna — solo falta idempotencia de users |
| P2 | `UserFactory` con estados de rol y `demo()` (`is_demo`) | Existe (fase 3.5) | Ninguna — se usa para los 4 users demo |
| P3 | Columna `users.is_demo` + guard `app()->environment('demo')` en `demoReset` | Existe (fase 3.5, spec 3.5) | Ninguna — se reutiliza el flag para resolver el landlord demo |
| P4 | `App\Support\CacheKeys::forgetModel()` + observers `#[ObservedBy]` en `Option`/`Language`/`Currency` | Existe (fase 4.9, spec 4.9) | Ninguna — la idempotencia per-row dispara observers automáticamente |
| P5 | Suite sin dependencia de `DatabaseSeeder` (tests usan factories + `CreatesUsers`) | Verificado (0 tests corren `DatabaseSeeder`) | Ninguna — la bola de tests es acotada y conocida |
| P6 | `config()` con keys de gateways disponibles (spec 4.3 `env()` → `config()`) | Existe (fase 4.3) | Ninguna — `GatewaySeeder` pasa a usarlas |

Todos los pre-requisitos existen: el spec es implementable directamente.

---

## 4. Estructura propuesta

```
database/seeders/           # 24 seeders refactorizados + DemoSeeder unificado
├── DatabaseSeeder.php      # orden de las 24 llamadas SIN cambios
├── PermissionSeeder.php    # users firstOrCreate por email; sin bloque muerto
├── CurrencySeeder.php      # insert → firstOrCreate por code
├── GatewaySeeder.php      # lookup por code, config(), firstOrCreate por name
├── UserSeeder.php          # UserFactory estados; UserDetail lookup dinámico
├── ... (18 restantes: firstOrCreate por clave natural + lookups)
├── OptionTableSeeder.php   # per-row firstOrCreate; sin Artisan::call
├── LanguageSeeder.php      # firstOrCreate por code (idempotente p/ ruta /update)
├── DemoSeeder.php          # dataset compartido + landlord dinámico
└── FedaPayGatewaySeeder.php  # ELIMINADO

app/Helpers/Helper.php      # demoReset(): landlord via is_demo
routes/console.php          # demo:reset (sin cambios de firma)
tests/Feature/Seeders/      # F1–F6
tests/Unit/Seeders/         # U1–U4
AGENTS.md                   # doc: convención de seeders + refs desactualizadas
```

### Tratamiento por seeder

| # | Seeder | Tratamiento |
|---|--------|-------------|
| 1 | PermissionSeeder | Users `firstOrCreate` por email; eliminar bloque muerto `Landlord` + `plan_subscribe_id => 1`; roles/permissions intactos |
| 2 | CurrencySeeder | `insert` → `firstOrCreate` por `code` (150 filas) |
| 3 | GatewaySeeder | `Currency` lookup por `code`; `env()` → `config()`; `firstOrCreate` por `name` |
| 4 | UserSeeder | `insert` → `UserFactory` estados con credenciales fijas + `firstOrCreate` por email; `UserDetail.user_id` por lookup; avatar `05-946` repuntado |
| 5 | PlanSeeder | `firstOrCreate` por `name` |
| 6 | PlanSubscribeSeeder | Lookups user/plan/gateway; `firstOrCreate` compuesto (`user_id`+`plan_id`) |
| 7 | CategorySeeder | `firstOrCreate` por `name` |
| 8 | HouseTypeSeeder | `firstOrCreate` por `name` |
| 9 | FacilitySeeder | Lookup landlord demo (`is_demo`); `firstOrCreate` (`name`+`landlord_id`) |
| 10 | AmenitySeeder | Ídem Facility |
| 11 | PropertySeeder | Lookups landlord/categorías/house_type; `facilities`/`amenities` por name; `firstOrCreate` por `puid`; 24 imágenes repuntadas |
| 12 | WithdrawMethodSeeder | `Currency` lookup; `firstOrCreate` por `name` |
| 13 | OptionTableSeeder | Per-row `firstOrCreate` por `key`; sin `Artisan::call`; dedupe `reminder-messages`; `version`/`app_url` intactos |
| 14 | RentSeeder | Lookups landlord/tenant/property |
| 15 | ReviewSeeder | Lookups property/tenant/rent |
| 16 | BlogSeeder | Lookup user superadmin; `firstOrCreate` por title (imágenes ya existen) |
| 17 | FaqSeeder | `firstOrCreate` (pregunta) |
| 18 | FeatureSeeder | `firstOrCreate` por title (imágenes ya existen) |
| 19 | TestimonialSeeder | `firstOrCreate` (imágenes ya existen) |
| 20 | ClientSeeder | `firstOrCreate` por name (imágenes ya existen) |
| 21 | IncomeSeeder | Categorías `firstOrCreate` (`name`+scope); lote null crea/referencia **sus propias** categorías |
| 22 | ExpenseSeeder | Ídem Income |
| 23 | HighlightSeeder | `firstOrCreate` (imágenes ya existen) |
| 24 | LanguageSeeder | `firstOrCreate` por `code` (101 filas; idempotente para la siembra runtime de `/update`) |
| + | DemoSeeder | Dataset compartido con Property/Income/Expense (mismos arrays fuente, counts distintos: 3 vs 8); landlord demo resuelto vía `is_demo` |
| + | demoReset() | Borra datos de los landlord(s) con `is_demo = 1` en vez de `landlord_id = 3` |
| − | FedaPayGatewaySeeder | **Eliminado** (huérfano; API key sandbox queda en git history — ver Riesgos) |

---

## 5. Decisiones técnicas

Fijadas en sesión de grilling (2026-11-20, Q1–Q18); no negociables salvo volver el spec a `Borrador`:

- **D1 — Mecanismo:** `firstOrCreate` por clave natural + lookups inline (`Currency::whereCode('USD')`, `User::whereEmail(...)`). Sin infraestructura nueva (sin traits/clases de resolución). Arrays deterministas: el dataset queda byte-equivalente al actual, salvo los 24 paths repuntados y las categorías del lote null.
- **D2 — Idempotencia:** re-ejecutar `db:seed` no duplica ni lanza FK errors. El "reset" del demo es rol de `demo:reset` (fase 3.5); el seeder no usurpa ese rol (nada de `truncate`).
- **D3 — 0 factories nuevas:** las 18 entidades sin factory (Facility, Amenity, HouseType, Rent, Income, Expense, Review…) migran sus arrays tal cual con `firstOrCreate` + lookups. El aleatoriedad de factories cambiaría el dataset demo, que debe ser predecible. Los 4 users van por `UserFactory` con estados de credenciales fijas.
- **D4 — Landlord demo:** `demoReset()` y `DemoSeeder` lo resuelven por `is_demo = 1` + rol `landlord`. El nombre de clase `DemoSeeder` se conserva: `DemoResetTest` mockea `Artisan::call('db:seed', ['--class' => 'DemoSeeder'])` y no se reescribe.
- **D5 — Claves naturales (contrato del lookup):**

  | Tabla | Clave natural |
  |---|---|
  | User | `email` |
  | Role / Permission | `name` (ya hoy) |
  | Currency / Language | `code` |
  | Gateway / Plan / Option | `name` / `name` / `key` |
  | Category / HouseType / Facility / Amenity | `name` |
  | Property | `puid` |
  | Income/ExpenseCategory | `name` + `landlord_id` (scope) |
  | PlanSubscribe | `user_id` + `plan_id` |
  | Rent / Review / Income / Expense / Blog / Faq / Feature / Testimonial / Client / Highlight | primer par único del dataset (title/name/pregunta); sin clave única real → primera columna distintiva, documentada por seeder |

- **D6 — Contratos que no se tocan:** nombres de roles/permisos/emails demo (8 tests seedean `PermissionSeeder`), clase `DemoSeeder` + firma del `Artisan::call` (`DemoResetTest`), option `version = "2.1"` (`ArtisanRoutesSmokeTest`, `routes/maintenance.php`), orden de las 24 llamadas de `DatabaseSeeder` (documentado en AGENTS.md).
- **D7 — Framework/DB de tests:** PHPUnit sobre `dbrentpro_test` + `RefreshDatabase`; credenciales demo en texto plano en los arrays de credenciales del seeder (no en tests).
- **D8 — Caché:** la migración per-row dispara los observers de 4.9 (`Option`, `Language`, `Currency`) automáticamente; costo de N llamadas a `forgetModel()` aceptado (contexto de seeding). No se reintroducen `Artisan::call` de limpieza en seeders.

---

## 6. Catálogo de casos

### 6.1 Arquitectura y unit (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | Sin FKs literales en seeders | repo en HEAD | escanear `database/seeders/*.php` con patrón `(landlord_id|tenant_id|user_id|currency_id|category_id|house_type_id|property_id|plan_id|gateway_id|income_category_id|expense_category_id|rent_id)\s*=>\s*['"]?\d+` | 0 matches |
| U2 | Sin `env()` en seeders | repo en HEAD | escanear `database/seeders/*.php` buscando `env(` | 0 matches |
| U3 | Sin credenciales embebidas | repo en HEAD | escanear `database/` buscando `sk_sandbox_`/`sk_live_`/`api_key.*=.*['"][A-Za-z0-9]{20,}` | 0 matches y `FedaPayGatewaySeeder.php` no existe |
| U4 | `demoReset` resuelve por `is_demo` | user demo (`is_demo=1`, rol `landlord`) con id arbitrario (p. ej. 7); user landlord real con id 3 | llamar `demoReset()` en entorno `demo` | borra filas del landlord 7; **no** borra nada del landlord 3; re-siembra vía `DemoSeeder` |

### 6.2 Feature (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Idempotencia doble-run | DB fresca (`RefreshDatabase`) | `$this->seed(DatabaseSeeder::class)` dos veces | Sin excepciones; counts estables en 2ª pasada (users 4, currencies 150, languages 101, gateways 12, properties 8, options sin keys duplicadas) |
| F2 | Seed sobre DB ya poblada | DB con un user/landlord/categoría previos no-demo (IDs ≠ 1–4) | `$this->seed(DatabaseSeeder::class)` | Sin duplicados, sin FK errors, filas previas intactas; dataset demo completo |
| F3 | Paths de imagen resuelven | DB sembrada | recolectar todo path `uploads/…` de filas sembradas y `file_exists(public/…)` | 100 % existen en clone fresco (39 actuales + 24 repuntados) |
| F4 | Lote null self-consistente | DB sembrada | consultar incomes/expenses con `landlord_id = null` | Referencian categorías con `landlord_id = null` — **no** categorías del landlord demo |
| F5 | `DemoSeeder` dinámico y unificado | landlord demo con id arbitrario ≠ 3; `DemoSeeder` ejecutado | re-siembra 3 propiedades con títulos del dataset compartido con `PropertySeeder` | Filas colgadas del landlord resuelto; 0 ids literales en el seeder |
| F6 | `PermissionSeeder` idempotente en users | DB fresca | seed `PermissionSeeder` dos veces | 2 users (emails únicos), mismos roles/permissions; sin `QueryException` de unique |

### Tests de caracterización

No aplica: los bugs actuales cubiertos (cross-tenant F4, imágenes inexistentes F3, `landlord_id = 3` U4) se **corrigen** en este mismo spec; no quedan comportamientos inseguros que se esperen romper en fases posteriores.

### Constraints de tests existentes (sin edición)

`DemoResetTest` (mock `Artisan::call` → `DemoSeeder`), `ArtisanRoutesSmokeTest` (`version = "2.1"`), los 8 archivos que seedean `PermissionSeeder`, `CreatesUsers::ensureSpatieRole()`.

---

## 7. Criterios de aceptación (Definition of Done)

- [ ] U1–U4 y F1–F6 en verde; suite completa (244 + ~10 nuevos) en verde, **sin editar tests existentes**.
- [ ] `php artisan db:seed` ejecutado 2× sobre una DB con datos previos: sin excepciones, sin duplicados (counts idempotentes verificados por F1/F2).
- [ ] 0 matches del patrón U1 y de `env(` en `database/seeders/` (verificado por arch-test en CI).
- [ ] 100 % de los paths de imagen sembrados existen en un clone fresco (F3).
- [ ] Nombres de roles/permisos/emails demo sin cambios; clase `DemoSeeder` y firma del `Artisan::call` sin cambios (D6).
- [ ] `FedaPayGatewaySeeder` eliminado; 0 secretos en `database/` (U3).
- [ ] `php artisan demo:reset` en entorno demo funciona con el landlord demo en cualquier ID (U4).
- [ ] PHPStan nivel 5 sin errores nuevos (baseline 909 sin crecer); `composer lint` (Pint) en verde.
- [ ] AGENTS.md actualizado: ref a `comand.txt` (ya eliminado) fuera; convención de seeders documentada ("idempotente por clave natural; prohibido `landlord_id => N` y paths a archivos no trackeados"); nota de la credencial sandbox en git history.
- [ ] Tests nuevos añaden < 15 s a la suite.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Dataset demo cambia visiblemente (24 imágenes repuntadas: covers/floorplans/avatar) | Bajo | Es data demo; F3 garantiza que todo path resuelve |
| `demoReset` con **múltiples** landlords `is_demo` en un entorno demo viejo | Medio | Borra datos de todos los demo landlords; documentar en AGENTS.md que el seed crea exactamente uno |
| `firstOrCreate` "adopta" filas previas de un entorno existente con la misma clave natural | Medio | Claves con scope landlord donde aplica (D5); F2 cubre el caso |
| API key sandbox de FedaPay queda en git history | Medio | Eliminación del archivo + nota en AGENTS.md; rotación de la key fuera de alcance (recordatorio de seguridad) |
| Contrato permiso→ruta (DT-M7) sigue latente al editar `PermissionSeeder` | Medio | D6: nombres intactos; DT-M9 ≠ DT-M7 |
| 150 currencies × 2 runs + 101 languages × observers en F1 puede ser lento | Bajo | DoD: < 15 s adicionales; si excede, `withoutObservers` no — aceptar el costo, no saltarse invalidación |
| `get_option('reminder-messages')` tras dedupe podría cambiar valor retornado | Bajo | La primera fila se conserva (orden del array original); verificar con los tests de options existentes |

**Pendiente de decisión:**

- Ninguna. Todas las decisiones cerradas en la sesión de grilling (2026-11-20): alcance total (Q1), idempotencia (Q2/Q9), DB poblada (Q4), tests (Q5), OUT (Q6/Q16), proceso (Q7), `demoReset` (Q8), `PermissionSeeder` (Q10), cross-tenant (Q11), unificación `DemoSeeder` (Q12), imágenes (Q13), gateways (Q14), `OptionTableSeeder` (Q15), claves naturales (Q17–Q18: doc + contrato).

---

## 9. Estimación

- Refactor de 24 seeders + `DemoSeeder` + `demoReset()`: 2–3 días
- Tests U1–U4, F1–F6 + verificación de suite/PHPStan/Pint: 1–2 días
- Total: **3–5 días**

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 4 / 4.10 (DT-M9)
- `.analisis/deuda-tecnica.md` → DT-M9, DT-M7 (relacionado, OUT)
- `.agents/spec-impl/spec-demo-reset-demomode-fase3.md` → limitación `landlord_id = 3` supersede por D4
- `.agents/spec-impl/spec-cache-invalidacion-centralizada-fase4.md` → observers/`forgetModel` (D8)
- `.agents/spec-impl/spec-env-to-config-fase4.md` → precedente `env()` → `config()` aplicado a `GatewaySeeder`
- `.agents/spec-impl/spec-unificar-capas-services-fase4.md` → patrón de fases 4 con spec + DoD
- `.agents/skills/testing-conventions.md` → "no depender de IDs fijos como hacen los seeders (DT-M9)"
- `AGENTS.md` → sección Seeders (a actualizar, DoD)
- `.agents/spec/spec-conventions.md`
