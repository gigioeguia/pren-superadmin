---
id: spec-admin-middleware-fase1
titulo: "AdminMiddleware lógica rota — Fase 1 / 1.2"
estado: Revisado
fecha: 2026-11-18
origen: .analisis/plan/plan-accion.md (Fase 1 / 1.2) — Seg-2
cobertura: 5 casos (G1–G5) sobre el guard admin (/admin)
---

# Spec — `AdminMiddleware` lógica rota

> Resumen: corregir la precedencia de operadores en `AdminMiddleware` para que solo usuarios con rol `admin` o `superadmin` (Spatie) accedan a `/admin/*`; el resto se redirige a `/login`.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 1 / 1.2) — `Seg-2`.

---

## 1. Objetivo

Eliminar la escalada de privilegios en el panel admin: `AdminMiddleware` deja pasar a landlords y tenants por un bug de precedencia (`&&` antes que `||`). Tras el fix, solo `admin`/`superadmin` pasan; anónimos, landlords y tenants se redirigen a `/login`.

## 2. Alcance

### IN
- `app/Http/Middleware/AdminMiddleware.php`: condición corregida.
- Todas las rutas bajo `/admin/*` (grupo definido en `RouteServiceProvider`).
- Conversión de G2/G4 (caracterización) a tests de redirección.
- Nuevo caso G5 para `superadmin`.

### OUT
- No se tocan roles/permisos Spatie ni seeders.
- No se cambia `LandlordMiddleware` ni `RedirectIfAuthenticated`.
- No se eliminan las comprobaciones `permission:` de los controladores.
- No se modifica el alias `admin` en `Kernel.php`.

### Cobertura objetivo
- 5 casos (G1–G5), 100 % de los flujos del guard admin cubiertos.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | `AdminGuardSmokeTest` G1–G4 existentes | Existen, G2/G4 marcados `@group characterization` | Convertir G2/G4 a tests de redirección (ver §6) |
| P2 | `PermissionSeeder` crea roles/permisos | Crea roles `superadmin` y `admin` con `dashboard-read` | Seed en `setUp()` (como ya hace el test) |
| P3 | Trait `CreatesUsers` | Provee `createAdmin()`/`createSuperadmin()` con rol Spatie | Usar en los tests |
| P4 | `RefreshDatabase` | Usado por el test | Mantener |
| P5 | `User` usa `HasRoles` | Sí | Necesario para `hasAnyRole()` |

## 4. Estructura propuesta

```
app/Http/Middleware/AdminMiddleware.php          # MODIFICADO: condición corregida
tests/Feature/Admin/AdminGuardSmokeTest.php     # MODIFICADO: G2/G4 convertidos + G5
tests/README.md                                # MODIFICADO: Seg-2 sin casos de caracterización
```

## 5. Decisiones técnicas

- **Framework:** Laravel 10, PHPUnit 10, `RefreshDatabase`.
- **DB:** MySQL `dbrentpro_test`.
- **Auth en tests:** `$this->actingAs($user)`.
- **Middleware corregido:**
  ```php
  if (auth()->check() && auth()->user()->hasAnyRole(['admin', 'superadmin'])) {
      return $next($request);
  }

  return redirect()->route('login');
  ```
  Se usa `hasAnyRole()` (Spatie) en lugar de la columna `role` para mantener una única fuente de verdad de autorización.
- **No-admin autenticado:** redirect 302 a `route('login')` (no 403), consistente con `LandlordMiddleware` y con el comentario G2/G4 del smoke test.
- **Admin/superadmin:** el middleware deja pasar; la comprobación `permission:dashboard-read` del controlador determina el 200 final.

## 6. Catálogo de casos

### 6.1 Guard admin (`G`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| G1 | Anónimo redirigido a login | Sin sesión | `GET /admin` | 302 redirect a `/login` |
| G2 | Landlord redirigido a login | Usuario autenticado con rol `landlord` | `GET /admin` | 302 redirect a `/login` |
| G3 | Admin con permiso accede al dashboard | Usuario autenticado con rol `admin` y `dashboard-read` | `GET /admin` | 200 OK |
| G4 | Tenant redirigido a login | Usuario autenticado con rol `tenant` | `GET /admin` | 302 redirect a `/login` |
| G5 | Superadmin con permiso accede al dashboard | Usuario autenticado con rol `superadmin` y `dashboard-read` | `GET /admin` | 200 OK |

> Tras el fix, G2 y G4 dejan de ser `@group characterization`; Seg-2 queda remediado.

### Tests de caracterización

Ninguno: Seg-2 queda remediado por este spec; G2/G4 se convierten a tests de redirección.

## 7. Criterios de aceptación (Definition of Done)

- [ ] `AdminMiddleware::handle` usa `auth()->check() && hasAnyRole(['admin','superadmin'])`.
- [ ] `GET /admin` devuelve 302 a `/login` para anónimos.
- [ ] `GET /admin` devuelve 302 a `/login` para landlords y tenants autenticados.
- [ ] `GET /admin` devuelve 200 para admin y superadmin con `dashboard-read`.
- [ ] G2 y G4 ya no llevan `@group characterization`.
- [ ] `php artisan test tests/Feature/Admin/AdminGuardSmokeTest.php` en verde.
- [ ] `tests/README.md` actualizado: Seg-2 sin casos de caracterización.
- [ ] `composer lint` y `composer analyse` sin nuevas violaciones en archivos tocados.

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Un usuario con columna `role='admin'` pero sin rol Spatie `admin` queda bloqueado | Medio | Correcto: la autorización se basa en Spatie; los seeders/factories asignan ambos. |
| El middleware sigue permitiendo el paso a cualquier admin sin verificar permisos finos | Bajo | La comprobación `permission:dashboard-read` del controlador sigue aplicando tras el middleware. |
| `hasAnyRole()` puede lanzar si el modelo no usa `HasRoles` | Bajo | `User` ya usa `HasRoles`; los tests lo verifican. |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

## 9. Estimación

- Middleware + tests: 0.5 día.
- Ajuste de `tests/README.md` y verificación: 0.5 día.
- Total: 1 día.

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 1 / 1.2
- `.analisis/seguridad.md` → Seg-2
- `.agents/spec/spec-conventions.md`
- `.agents/spec-impl/spec-tests-smoke-fase0.md` (G1–G4 originales)
- `tests/README.md`
---