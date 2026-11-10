---
id: secure-coding
name: "Secure Coding Checklist"
description: "Checklist anti-reincidencia de las vulnerabilidades documentadas en .analisis/seguridad.md."
when_to_use: "Al escribir o modificar código que toque auth, pagos, datos de usuarios o controladores con update/destroy/show."
---

# Secure Coding Checklist

Lista de verificación para que **código nuevo no repita** las 26 vulnerabilidades de `.analisis/seguridad.md`. Aplicar antes de considerar terminado cualquier cambio que afecte auth, pagos, datos de usuarios o controladores resource.

## Pasos

### Autorización
1. **Todo `update`/`destroy`/`show`/`update_status` lleva Policy** con `authorize()` o `Gate::check()`. Nunca confiar en que el ID del request pertenece al usuario actual.
2. **No tomar `user_id`/`landlord_id`/`tenant_id` de parámetros de URL**. Usar `auth()->id()`. Si debe venir por URL, validar ownership y firmar la URL (`signed` middleware).
3. **Middleware `admin`/`landlord`**: usar los alias oficiales (`role:admin|superadmin`, `permission:...` de Spatie), no lógica custom con `&&`/`||` sin paréntesis.
4. **Rutas admin/landlord**: protegidas con `auth` + role/permission. Ninguna ruta mutativa pública.

### Auth y OTP
5. **Comparar OTP con `hash_equals($stored, $input)`**, nunca con `==` o `===` sueltos (timing attack).
6. **Reset de contraseña**: validar OTP/token **antes** de actualizar la contraseña. Nunca resetear solo con email+password.
7. **Registro**: el `role` **no** viene del request. Se asigna server-side (`landlord` por defecto). Quitar `role` de `$fillable` o del input válido.
8. **Política de contraseña**: `Password::min(8)->mixedCase()->numbers()->uncompromised()`. Nunca `min:4` o `min:6` sin complejidad.
9. **Throttle** en login, OTP send/resend, reset code: `throttle:5,1` (5 intentos/min). API general ≤ 60/min.

### Pagos
10. **Webhooks/callbacks de gateway**: validar firma del gateway (HMAC/firma propia). Nunca procesar un callback sin verificar origen.
11. **`/payment/success`**: tras `auth` + verificación de sesión/firma. Nunca público.
12. **`$gateway->namespace::make_payment`**: si se invoca clase desde BD, usar whitelist de clases permitidas, no string libre.
13. **Montos y user_id**: nunca desde input del cliente. Tomar de BD/session autenticada.

### Errores y exposición
14. **No devolver `$e->getMessage()`** en respuestas JSON. Mensaje genérico + log interno con stack trace.
15. **`APP_DEBUG=false`** en producción. Stack traces visibles = fuga de config/paths.

### Archivos y uploads
16. **Uploads**: generar nombre siempre (`now()->timestamp . '.' . $ext`), no preservar `getClientOriginalName()`. Validar `mimes` en **todos** los uploads. Normalizar extensión (rechazar `.phtml`, `.php5`).

### Modelo User
17. **`$fillable` restrictivo**: fuera de él `role`, `balance`, `is_setupped`, `plan_subscribe_id`, `will_expire`. Asignar esos solo server-side vía `$user->update([...])` explícito.
18. **`env()` fuera de `app/`**: usar `config()`. `env()` retorna `null` bajo `config:cache`.

### Rutas Artisan / mantenimiento
19. **Nunca rutas públicas que ejecuten `Artisan::call`** (`migrate`, `db:seed`, `cache:clear`). Tras `auth` + `permission:superadmin`, o solo por SSH/CLI.

### Sesión y cookies
20. **Session driver**: `database` o `redis` en producción (no `file`). Cookie `secure=true` tras HTTPS.

## Checklist de PR (copiar en la descripción)

- [ ] ¿Hay `authorize()`/Policy en cada `update`/`destroy`/`show`?
- [ ] ¿Algún `user_id`/`landlord_id` viene de URL sin firmar?
- [ ] ¿OTP comparado con `hash_equals`?
- [ ] ¿Webhook valida firma del gateway?
- [ ] ¿Respuestas de error exponen `$e->getMessage()`?
- [ ] ¿Uploads validan `mimes` y generan nombre?
- [ ] ¿`role` viene del request en algún flujo de registro?
- [ ] ¿Ruta nueva es pública y ejecuta Artisan o muta BD?

Si cualquier respuesta es "sí" (salvo la primera), **bloquear el PR**.

## Evitar

- Confiar en que "el ID del request es del usuario" sin verificar ownership.
- Usar `==` para comparar tokens/OTP/códigos.
- Exponer mensajes de excepción en respuestas API.
- Añadir `role`, `balance` o `is_setupped` a `$fillable` "por comodidad".
- Dejar rutas de pago/callback públicas sin verificación de firma.

## Relacionados

- `implementar-spec.md` — proceso de implementación (aplica este checklist).
- `tests-caracterizacion.md` — tests que documentan los bugs que este checklist previene.
- `testing-conventions.md` — cómo estructurar tests de seguridad.
- `.analisis/seguridad.md` — detalle de las 26 vulnerabilidades que motivan este checklist.
