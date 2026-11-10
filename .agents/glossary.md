# Glosario del dominio

Términos recurrentes del código que un agente debe entender antes de tocar lógica de negocio. Para overview del stack y rutas, ver `AGENTS.md`.

## Roles (`User->role`)

| Rol | Qué hace |
|-----|----------|
| `superadmin` | Acceso total al sistema (panel admin). |
| `admin` | Gestiona condominios, usuarios, configuración (panel admin). |
| `landlord` | Propietario de propiedades; gestiona inquilinos, rentas, retiros (panel landlord + API). |
| `tenant` | Inquilino; consulta avisos, paga rentas/depósitos, reporta incidencias (API). |
| (mantenimiento) | Personal que atiende tareas/incidencias asignadas. |

> El rol se guarda como **string plano** en `User->role` Y además `User` usa el trait `HasRoles` de Spatie. Dos sistemas paralelos — cuidar consistencia.

## Conceptos clave

- **`landlord_id`** (FK en `users` y varias tablas): identifica al propietario dueño de un registro. Propiedades, inquilinos, retiros, gastos, etc. cuelgan de un landlord.
- **`plan_subscribe` / `PlanSubscribe`**: suscripción activa del landlord a un plan. Determina límites (nº propiedades, inquilinos, labores) y `will_expire`.
- **`will_expire`** (fecha en `User`): cuándo caduca la suscripción del landlord. Lo revisa el middleware `expired` para bloquear acceso al panel landlord tras vencimiento.
- **`is_setupped`** (bool en `User`): si el usuario completó el setup inicial (OTP verificado + perfil). Login/registro lo verifican.
- **`unique_id`** (string único en `User`): ID legible de 6 dígitos generado en `User::boot()` con `str_pad(User::max('id')+1, 6, '0', STR_PAD_LEFT)`. **Frágil en DB recién migrada** — setear explícito en factories/tests.
- **`security_deposit`** vs **`rent_payment`**: dos flujos de pago distintos. `SecurityDeposit` = depósito de garantía al inicio del alquiler. `RentPayment` = cuota periódica de renta. Ambos pasan por `PaymentController`.
- **`expired` (middleware)**: bloquea rutas `/landlord/*` si la suscripción del landlord está vencida.
- **`payment_type`** (en sesión y requests): distingue `subscription` (compra de plan), `security_deposit`, `rent_payment`. Lo setea `PaymentController` en sesión para enrutar el callback de success.
- **`Gateway->namespace`** (columna en BD): FQCN de la clase que implementa el gateway (`App\Library\StripeGateway`, etc.). Se invoca dinámicamente `$gateway->namespace::make_payment($data)`. **Editar un gateway = actualizar archivo Y fila en BD.**
- **`DemoMode` (middleware)**: si `DEMO_MODE=true`, bloquea rutas mutativas listadas en `$adminDisabledRoutes` / `$landlordDisabledRoutes`. Solo bloquea al email exacto `landlord@acnoo.com` para landlords.
- **`@group characterization`**: marca tests que documentan comportamiento actual (inseguro) que se espera romper tras una fase del plan-accion. Ver skill `tests-caracterizacion.md`.

## Pasarelas de pago (11)

Stripe, Mollie, Razorpay, FedaPay, Paystack, Flutterwave, Instamojo, Toyyibpay, Thawani, Tap, Billplz. Cada una en `app/Library/<Name>.php` con métodos `make_payment(array $data)` y `status()`. Configuradas vía `Gateway` model (BD) + env vars (ver `AGENTS.md`).

## Estados de specs

`Borrador → Revisado → Aprobado → Obsoleto`. Ver `spec/spec-conventions.md`.
