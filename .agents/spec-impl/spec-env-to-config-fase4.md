---
id: spec-env-to-config-fase4
titulo: "`env()` → `config()` (Fase 4 / 4.3)"
estado: Implementado
fecha: 2026-11-20
origen: .analisis/plan/plan-accion.md (Fase 4 / 4.3) — DT-A2
cobertura: Migración de todos los usos de `env()`/`getenv()` en `app/` a `config()` (+2 blade de mail), llegando a 0 usos en `app/`
---

# Spec — `env()` → `config()` (Fase 4 / 4.3)

> Resumen: eliminar los usos de `env()`/`getenv()` en código de aplicación (`app/`) moviéndolos a claves de `config/`, de modo que la app funcione correctamente bajo `php artisan config:cache` (donde `env()` devuelve `null`). Incluye un fix latente de casting booleano de `QUEUE_MAIL`.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 4 / 4.3) — `DT-A2`.

---

## 1. Objetivo

Hacer que toda lectura de configuración de entorno en `app/` pase por `config()`, garantizando que `config:cache` no rompa el comportamiento (mail, OTP, cache, settings) y cumpliendo la métrica de éxito "0 usos de `env()` en `app/`". De paso, corregir el bug latente por el que `QUEUE_MAIL="false"` (string) se evaluaba como truthy.

## 2. Alcance

### IN
- Todos los `env()`/`getenv()` en `app/` (9 archivos).
- Nuevas claves de config en archivos existentes: `config/mail.php`, `config/auth.php`, `config/cache.php`, `config/app.php`.
- `app/Http/Controllers/Admin/SystemSettingController.php`: lectura de `APP_KEY`/`DB_*`/`SITE_KEY`/`AUTHORIZED_KEY` vía `config()`.
- `resources/views/{admin/,}mail/welcome.blade.php`: `env('APP_NAME')` → `config('app.name')` (excepción acotada por cache-safety del mail).
- `tests/Feature/Auth/WebSignupSmokeTest.php`: S8 migrado a `config()` + nuevo test de ramas `QUEUE_MAIL`.
- `phpunit.xml`: declarar `QUEUE_MAIL` y `CACHE_LIFETIME` explícitamente.

### OUT
- `resources/views/admin/settings/system.blade.php` (editor de `.env`): sigue leyendo el archivo/entorno directamente; documentado como editor.
- `database/seeders/GatewaySeeder.php`: queda para Fase 4.10 (refactor de seeders).
- Defaults inseguros hardcodeados en el editor (`APP_ENV=local`, `LOG_LEVEL=debug`): Fase 5 (higiene).
- Normalización de unidades de `OTP_VISIBILITY_TIME` (minutos vs segundos): deuda pendiente (Fase 4.7/5.x).
- Cambios en `.github/workflows/ci.yml`.

### Cobertura objetivo
- 0 ocurrencias de `env(` / `getenv(` en `app/` (verificable por grep).
- Suite de tests en verde + PHPStan sin nuevos errores + Pint limpio.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Config files existentes | `config/mail.php`, `auth.php`, `cache.php`, `app.php` | Añadir keys |
| P2 | `config/gateways.php` whitelist | Existe | Ninguna (fuera de alcance) |
| P3 | phpunit.xml con `MAIL_USERNAME` | Existe | Añadir `QUEUE_MAIL`, `CACHE_LIFETIME` |
| P4 | Tests smoke (S8) | Existe, manipula env en runtime | Migrar a `config()` |

## 4. Estructura propuesta

```
config/
├── app.php          # MODIFICADO: + site_key, authorized_key
├── auth.php         # MODIFICADO: + otp_visibility_time (nullable)
├── cache.php        # MODIFICADO: + lifetime
└── mail.php         # MODIFICADO: + username, queue

app/
├── Helpers/Helper.php                              # MODIFICADO
├── Mail/PasswordReset.php                          # MODIFICADO
├── Mail/WelcomeMail.php                            # MODIFICADO
├── Services/TenantService.php                      # MODIFICADO
└── Http/Controllers/
    ├── Api/Auth/RegistrationController.php         # MODIFICADO
    ├── Api/Auth/SigninController.php               # MODIFICADO
    ├── Api/Auth/ForgotPasswordController.php       # MODIFICADO
    ├── Auth/SignupController.php                   # MODIFICADO
    └── Admin/SystemSettingController.php           # MODIFICADO

resources/views/
├── admin/mail/welcome.blade.php                    # MODIFICADO
└── mail/welcome.blade.php                          # MODIFICADO

tests/Feature/Auth/WebSignupSmokeTest.php           # MODIFICADO
phpunit.xml                                         # MODIFICADO
```

## 5. Decisiones técnicas

- **Framework/config:** Laravel 11, `config()` con casting explícito al tipo consumido (phpstan nivel 5).
- **Nuevas keys:** en archivos existentes por convención (no crear archivo "cajón de sastre").
- **Casting bool:** `QUEUE_MAIL` se castea a `(bool)` en config, corrigiendo el bug latente de truthiness del string `"false"`.
- **OTP nullable:** `otp_visibility_time` es nullable (`env('OTP_VISIBILITY_TIME')` sin default) para preservar los fallbacks por call-site (`?? 3` minutos, `?? 60` segundos). No se normalizan unidades en este spec.
- **`MAIL_USERNAME` flag:** nueva key top-level `config('mail.username')` (string|null), además de la existente `config('mail.mailers.smtp.username')`.
- **SystemSettingController:** lee `APP_KEY`/`DB_*`/`SITE_KEY`/`AUTHORIZED_KEY` desde `config()` (conexión por defecto para DB).
- **phpunit.xml:** se añaden `QUEUE_MAIL=false` y `CACHE_LIFETIME=1800`. `OTP_VISIBILITY_TIME` se omite intencionalmente para no alterar el fallback de 60 s del path de segundos (Q16).
- **Tests:** S8 migra a `config(['mail.username' => null])` y restaura en `tearDown`. Se añade test de ramas `QUEUE_MAIL` (queued vs sent).

## 6. Catálogo de casos

### 6.1 Configuración (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Sin `env()`/`getenv()` en `app/` | Repo migrado | `grep -rn "env(\|getenv(" app/` | 0 resultados |
| F2 | `config/mail.php` con `username` y `queue` | Archivo modificado | Inspección | Keys presentes; `queue` casteado a `(bool) env('QUEUE_MAIL', false)`; `username` = `env('MAIL_USERNAME')` |
| F3 | `config/auth.php` con `otp_visibility_time` | Archivo modificado | Inspección | Key presente, nullable (`env('OTP_VISIBILITY_TIME')`) |
| F4 | `config/cache.php` con `lifetime` | Archivo modificado | Inspección | Key presente, `(int) env('CACHE_LIFETIME', 1800)` |
| F5 | `config/app.php` con `site_key`/`authorized_key` | Archivo modificado | Inspección | Keys presentes, `env('SITE_KEY')`/`env('AUTHORIZED_KEY')` |
| F6 | Mail classes usan `config()` | `PasswordReset`/`WelcomeMail` | Inspección | `config('mail.from.address')` y `config('mail.from.name')`, sin `env()` |
| F7 | `cache_remember` usa `config()` | Helper | Inspección | `(int) config('cache.lifetime', $ttl)` |
| F8 | Controllers de auth usan `config()` | 4 controllers | Inspección | `config('mail.queue')`, `config('auth.otp_visibility_time')`, `config('mail.username')`; sin `env()`/`getenv()` |
| F9 | `TenantService` usa `config()` | Servicio | Inspección | `config('auth.otp_visibility_time')` y `config('mail.queue')`; sin `getenv()` |
| F10 | `SystemSettingController` usa `config()` | Controller | Inspección | `config('app.key')`, `config('database.default')`, `config("database.connections.{$conn}.*")`, `config('app.site_key')`, `config('app.authorized_key')`; sin `env()` |
| F11 | Blade de mail usa `config()` | 2 blade | Inspección | `config('app.name')`; sin `env()` |
| F12 | phpunit.xml actualizado | Archivo | Inspección | `QUEUE_MAIL=false`, `CACHE_LIFETIME=1800` presentes |
| F13 | S8 migrado y en verde | Test | `php artisan test tests/Feature/Auth/WebSignupSmokeTest.php` | Pasa |
| F14 | Test de ramas `QUEUE_MAIL` | Test | Ejecución | Con `config('mail.queue', true)` → `Mail::assertQueued`; con `false` → `Mail::assertSent` y `assertNotQueued` |
| F15 | PHPStan sin nuevos errores | CI/local | `composer analyse` | Sin regresiones vs baseline (910) |
| F16 | Pint limpio | CI/local | `composer lint` | Sin violaciones |
| F17 | `config:cache` manual | Local | `php artisan config:cache && php artisan config:clear` | App arranca y flujos críticos (login, signup) funcionan |

### Tests de caracterización

Ninguno nuevo: el cambio de casting bool de `QUEUE_MAIL` se aplica ya y se cubre con F14 (test normal, no characterization).

## 7. Criterios de aceptación (Definition of Done)

- [ ] 0 ocurrencias de `env(` o `getenv(` en `app/` (F1).
- [ ] Nuevas keys presentes en config con los defaults acordados (F2–F5).
- [ ] Mail classes, helper, controllers, servicio y editor usan `config()` (F6–F10).
- [ ] Blade de mail migrado (F11).
- [ ] `phpunit.xml` actualizado (F12).
- [ ] `WebSignupSmokeTest` (incl. S8) en verde (F13).
- [ ] Test de ramas `QUEUE_MAIL` en verde (F14).
- [ ] `composer analyse` sin nuevos errores (F15).
- [ ] `composer lint` limpio (F16).
- [ ] Verificación manual de `config:cache` documentada y exitosa (F17).
- [ ] `php artisan test` completo en verde.

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| `config:cache` desactualizado tras editar `.env` desde el panel | Medio | Documentar `config:clear` en el flujo del admin; fuera de alcance automatizar |
| `SITE_KEY`/`AUTHORIZED_KEY` null en cache si no estaban en `.env` | Bajo | Default null preserva comportamiento actual |
| Casting bool cambia `QUEUE_MAIL="false"` de truthy a false | Bajo (fix) | Cubierto por F14 |
| Unidades de OTP inconsistentes (min vs seg) | Bajo | Diferido (Q5/Q16); nullable + fallbacks por call-site preservan comportamiento |
| PHPStan `mixed` de `config()` | Medio | Casting explícito en cada lectura (Q14) |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

## 9. Estimación

- Config + app/ (9 archivos): 0.5 día.
- SystemSettingController + blade: 0.25 día.
- Tests (S8 + ramas): 0.25 día.
- Verificación (phpstan, pint, config:cache): 0.25 día.
- Total: ~1.25 días.

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 4 / 4.3
- `.analisis/deuda-tecnica.md` → DT-A2
- `.analisis/seguridad.md` → (métrica "0 usos de `env()` en `app/`")
- `.agents/spec/spec-conventions.md`
- `.agents/spec-impl/spec-env-inseguro-fase1.md` (precedente de spec de entorno)

---

### Notas de implementación

1. **`config/auth.php`**: añadir `'otp_visibility_time' => env('OTP_VISIBILITY_TIME'),` (nullable).
2. **Call-sites de OTP**:
   - minutos: `now()->addMinutes((int) (config('auth.otp_visibility_time') ?? 3))`
   - segundos (otpResend): `now()->addSeconds((int) (config('auth.otp_visibility_time') ?? 60))`
3. **`config/mail.php`**: añadir `'username' => env('MAIL_USERNAME'),` y `'queue' => (bool) env('QUEUE_MAIL', false),` (junto al bloque `from`).
4. **`config/cache.php`**: `'lifetime' => (int) env('CACHE_LIFETIME', 1800),` antes del cierre.
5. **`config/app.php`**: `'site_key' => env('SITE_KEY'),` y `'authorized_key' => env('AUTHORIZED_KEY'),` junto a `'key'`.
6. **`SystemSettingController`**:
   ```php
   $conn = config('database.default');
   // DB_CONNECTION=$conn
   // DB_HOST=config("database.connections.{$conn}.host")
   // DB_PORT=config("database.connections.{$conn}.port")
   // DB_DATABASE=config("database.connections.{$conn}.database")
   // DB_USERNAME=config("database.connections.{$conn}.username")
   // DB_PASSWORD=config("database.connections.{$conn}.password")
   // APP_KEY=config('app.key')
   // SITE_KEY=config('app.site_key')
   // AUTHORIZED_KEY=config('app.authorized_key')
   ```
7. **S8**:
   ```php
   private function withoutMailUsername(): void
   {
       config(['mail.username' => null]);
   }
   private function restoreMailUsernameEnv(): void
   {
       config(['mail.username' => self::MAIL_USERNAME]);
   }
   ```
   (eliminar `putenv`/`$_ENV`/`$_SERVER`).
8. **Nuevo test** (en `WebSignupSmokeTest` o archivo nuevo) cubriendo `config('mail.queue', true/false)` con `Mail::fake()` y `assertQueued`/`assertSent`+`assertNotQueued`.
