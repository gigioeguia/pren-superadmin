<?php

namespace App\Services;

use App\Exceptions\InvalidPaymentTypeException;
use App\Helpers\HasUploader;
use App\Models\Gateway;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\RentPayment;
use App\Models\SecurityDeposit;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Currency;
use App\Support\PaymentContext;
use App\Support\PaymentOutcome;
use App\Support\PaymentState;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Stripe;

/**
 * Dueño único de la lógica de pago (spec-service-payment-fase5): 3
 * payment types + 11 gateways + success idempotente. El controlador
 * posee la sesión y el armado de respuestas; este service es
 * agnóstico de HTTP (no lee auth()/request()/session()).
 *
 * Postura Fase 4 / 4.8 preservada literalmente: Stripe verify +
 * Cache::lock + make_payment ocurren FUERA de la transacción; la tx
 * solo envuelve escrituras; el upload del attachment va antes de
 * abrirla. Idempotencia (Fase 2 / 2.3–2.4) y whitelist de namespaces
 * (Fase 2 / 2.5, fail-closed) intactas.
 */
class PaymentService
{
    use HasUploader;

    /**
     * Contexto de la vista de gateways para security_deposit/rent_payment
     * (URL firmada; el controlador hace el defense-in-depth contra el
     * tenant_id resuelto aquí).
     *
     * @return array{plan: SecurityDeposit|RentPayment, tenant_id: int|string|null, payment_type: string, gateways: Collection<int, Gateway>}
     */
    public function depositRentPayment(int $paymentId, ?string $paymentType): array
    {
        if ($paymentType === 'security_deposit') {
            $plan = SecurityDeposit::findOrFail($paymentId);
            $tenantId = $plan->tenant_id;
        } elseif ($paymentType === 'rent_payment') {
            $plan = RentPayment::findOrFail($paymentId);
            $tenantId = $plan->rent()->first()?->getAttribute('tenant_id');
        } else {
            throw new InvalidPaymentTypeException((string) $paymentType);
        }

        return [
            'plan' => $plan,
            'tenant_id' => $tenantId,
            'payment_type' => (string) $paymentType,
            'gateways' => $this->activeGateways(),
        ];
    }

    /**
     * Contexto de la vista de gateways para subscription (URL firmada):
     * web lista TODOS los gateways (comportamiento actual), app solo los
     * automáticos activos.
     *
     * @return array{plan: Plan, gateways: Collection<int, Gateway>}
     */
    public function gatewayIndex(int $planId, ?string $platform): array
    {
        $plan = Plan::findOrFail($planId);

        $gateways = $platform === 'app'
            ? $this->activeGateways()
            : Gateway::with('currency:id,code,rate,symbol,position')->get();

        return ['plan' => $plan, 'gateways' => $gateways];
    }

    /**
     * Resolución del intento de pago POST /payments: gateway whitelisted
     * (403 fail-closed, Fase 2 / 2.5) + user + plan con su monto.
     */
    public function resolvePayment(string $paymentType, int $planId, int $gatewayId, int $userId): PaymentContext
    {
        $gateway = Gateway::findOrFail($gatewayId);

        // Fase 2 / 2.5 (Seg-6): only whitelisted gateway classes may be
        // invoked dynamically from the DB-stored namespace (fail-closed).
        if (! Gateway::isAllowedNamespace($gateway->namespace)) {
            Log::warning('Blocked gateway namespace', [
                'gateway_id' => $gateway->id,
                'namespace' => $gateway->namespace,
            ]);

            throw new AuthorizationException('Gateway namespace is not allowed.');
        }

        $user = User::findOrFail($userId);

        $plan = match ($paymentType) {
            'security_deposit' => SecurityDeposit::with('tenant:id,name,email,phone')->findOrFail($planId),
            'rent_payment' => RentPayment::with(['rent:id,tenant_id', 'rent.tenant:id,name,email,phone'])->findOrFail($planId),
            'subscription' => Plan::findOrFail($planId),
            default => throw new InvalidPaymentTypeException($paymentType),
        };

        $amount = $plan instanceof Plan
            ? $plan->offerPrice ?? $plan->subscriptionPrice
            : $plan->total_amount;

        return new PaymentContext($user, $gateway, $plan, $amount, $paymentType);
    }

    /**
     * Pago manual (transferencia): attachment subido ANTES de abrir la
     * transacción; la tx solo envuelve la escritura (Fase 4 / 4.8).
     */
    public function payManual(PaymentContext $context, ?string $manualData, ?UploadedFile $attachment): void
    {
        if (! $context->plan instanceof Plan) {
            // Inalcanzable por UI (las vistas deposit/rent solo listan
            // gateways automáticos): antes \Error no capturado (500).
            throw new InvalidPaymentTypeException($context->paymentType);
        }

        $attachmentPath = $attachment instanceof UploadedFile ? $this->uploadFile($attachment) : null;

        DB::beginTransaction();

        try {
            PlanSubscribe::create([
                'user_id' => $context->user->id,
                'plan_id' => $context->plan->id,
                'gateway_id' => $context->gateway->id,
                'price' => $context->amount,
                'duration' => $context->plan->durationInDays(),
                'payment_status' => 'unpaid',
                'notes' => [
                    'manual_data' => $manualData,
                    'attachment' => $attachmentPath,
                ],
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Pago por gateway: ensambla payment_data e invoca make_payment FUERA
     * de cualquier transacción (Fase 4 / 4.8). El controlador stashea la
     * sesión (user/plan/gateway_id/amount + fund_callback) antes de llamar.
     *
     * @return mixed redirect/respuesta del gateway.
     */
    public function payGateway(PaymentContext $context, ?string $phone, ?string $platform, bool $expectsJson): mixed
    {
        if (! $context->gateway->currency) {
            // Antes: ErrorException no capturado durante el ensamblado (500).
            throw new \RuntimeException('Gateway currency is missing.');
        }

        $paymentData = [
            'currency' => $context->gateway->currency->code ?? 'USD',
            'email' => $context->user->email ?? null,
            'name' => $context->user->name ?? null,
            'phone' => $phone ? $context->user->phone : null,
            'billName' => __('Make plan purchase payment'),
            'amount' => $context->amount,
            'mode' => $context->gateway->mode,
            'charge' => $context->gateway->charge ?? 0,
            'pay_amount' => round(Currency::convert($context->amount, $context->gateway->currency) + $context->gateway->charge),
            'gateway_id' => $context->gateway->id,
            'payment_type' => $context->paymentType,
            'request_from' => 'merchant',
            'plan_id' => $context->plan->id,
            'platform' => $platform,
        ];

        foreach ($context->gateway->data ?? [] as $key => $info) {
            $paymentData[$key] = $info;
        }

        return $context->gateway->namespace::make_payment($paymentData, $expectsJson);
    }

    /**
     * Pago confirmado (GET /payment/success): idempotente por lock de
     * sesión (Fase 2 / 2.3–2.4). La verificación del gateway (Stripe
     * HTTP / payment_info) ocurre ANTES de abrir la transacción; la tx
     * solo envuelve las 3 ramas de escritura (Fase 4 / 4.8).
     */
    public function success(PaymentState $state): PaymentOutcome
    {
        $lock = Cache::lock('payment-success:'.$state->sessionId, 10);
        if (! $lock->get()) {
            return PaymentOutcome::InvalidCredentials;
        }

        try {
            $plan = $state->plan;
            $user = $state->user;

            if (! $plan && ! $state->planId || ! $state->paymentType) {
                return PaymentOutcome::InvalidCredentials;
            }

            if ($state->planId && ! $plan) {
                $plan = match ($state->paymentType) {
                    'security_deposit' => SecurityDeposit::findOrFail($state->planId),
                    'rent_payment' => RentPayment::findOrFail($state->planId),
                    'subscription' => Plan::findOrFail($state->planId),
                    default => null,
                };
            }

            if ($state->userId && ! $user) {
                $user = User::findOrFail($state->userId);
            }

            if (! $state->gatewayId) {
                return PaymentOutcome::InvalidCredentials;
            }

            $gateway = Gateway::findOrFail($state->gatewayId);

            // Payment verification (gateway must have confirmed).
            if ($state->stripeSessionId) {
                Stripe::setApiKey($gateway->data['stripe_secret'] ?? '');
                $stripeSession = StripeCheckoutSession::retrieve($state->stripeSessionId);
                if (($stripeSession->payment_status ?? null) !== 'paid') {
                    return PaymentOutcome::InvalidCredentials;
                }
            } elseif (is_array($state->paymentInfo)) {
                $status = $state->paymentInfo['status'] ?? $state->paymentInfo['payment_status'] ?? null;
                if ((int) $status !== 1) {
                    return PaymentOutcome::InvalidCredentials;
                }
            } else {
                return PaymentOutcome::InvalidCredentials;
            }

            // Sesión sin payer resuelto: antes ErrorException dentro de
            // la rama ($user->id) → catch.
            if (! $user instanceof User) {
                throw new \RuntimeException('Payment session user is missing.');
            }

            // Verificación de pago completada: la transacción envuelve
            // únicamente las escrituras DB (Fase 4 / 4.8).
            DB::beginTransaction();

            if ($state->paymentType === 'security_deposit') {
                $this->markSecurityDepositPaid($plan, $user, $gateway);
            }

            if ($state->paymentType === 'rent_payment') {
                $this->markRentPaymentPaid($plan, $user, $gateway);
            }

            if ($state->paymentType === 'subscription') {
                $this->activateSubscription($plan, $user, $gateway, $state->amount);
            }

            DB::commit();

            return PaymentOutcome::Success;
        } catch (\Exception) {
            DB::rollBack();

            return PaymentOutcome::SomethingWrong;
        } finally {
            $lock->release();
        }
    }

    /**
     * URL firmada del flujo de pago de suscripción (mint para el app).
     */
    public function subscriptionUrl(int $planId, int $landlordId): string
    {
        $plan = Plan::findOrFail($planId);

        return URL::temporarySignedRoute(
            'payments-gateways.index',
            now()->addHours(2),
            ['plan_id' => $plan->id, 'landlord_id' => $landlordId],
        );
    }

    /**
     * URL firmada del pago de un security deposit (owner: el tenant del depósito).
     */
    public function depositUrl(int $depositId, int $actorId): string
    {
        $deposit = SecurityDeposit::findOrFail($depositId);

        if ($actorId !== (int) $deposit->tenant_id) {
            throw new AuthorizationException;
        }

        return URL::temporarySignedRoute(
            'online.payments-gateways.index',
            now()->addHours(2),
            ['payment_id' => $deposit->id],
        );
    }

    /**
     * URL firmada del pago de una rent payment (owner: el tenant del rent).
     */
    public function rentUrl(int $rentPaymentId, int $actorId): string
    {
        $rentPayment = RentPayment::findOrFail($rentPaymentId);
        $rent = $rentPayment->rent()->first();

        if (! $rent || $actorId !== (int) $rent->getAttribute('tenant_id')) {
            throw new AuthorizationException;
        }

        return URL::temporarySignedRoute(
            'online.payments-gateways.index',
            now()->addHours(2),
            ['payment_id' => $rentPayment->id],
        );
    }

    /**
     * Gateways automáticos activos (excluye manuales, igual que hoy).
     *
     * @return Collection<int, Gateway>
     */
    private function activeGateways(): Collection
    {
        return Gateway::with('currency:id,code,rate,symbol,position')
            ->where(['status' => 1, 'is_manual' => 0])
            ->get();
    }

    /**
     * Rama security_deposit: acredita landlord_balance al landlord vía
     * forceFill (spec 5.11) y registra el débito.
     */
    private function markSecurityDepositPaid(SecurityDeposit|RentPayment|Plan|null $plan, User $payer, Gateway $gateway): void
    {
        if (! $plan instanceof SecurityDeposit) {
            // Mismatch tipo/plan solo alcanzable con sesión crafteda:
            // antes ErrorException → catch.
            throw new \RuntimeException('Payment type/plan mismatch.');
        }

        $landlord = User::where('id', $plan->landlord_id)->first();

        if ($plan->status !== 'paid') {
            if (! $landlord) {
                throw new \RuntimeException('Deposit landlord not found.');
            }

            $landlord->forceFill([
                'balance' => $landlord->balance + $plan->landlord_balance,
            ])->save();

            $plan->update([
                'payment_date' => today(),
                'gateway_id' => $gateway->id,
                'status' => 'paid',
            ]);

            Transaction::create([
                'paid_by' => $payer->id,
                'received_by' => $landlord->id,
                'gateway_id' => $gateway->id,
                'security_deposit_id' => $plan->id,
                'date' => today(),
                'type' => 'debit',
                'payment_method' => $gateway->name ?? null,
                'payment_type' => 'security_deposit',
                'amount' => $plan->landlord_balance ?? 0,
                'charge' => $plan->charge ?? 0,
            ]);
        }
    }

    /**
     * Rama rent_payment: acredita total_amount al landlord vía forceFill
     * (spec 5.11) y registra el débito.
     */
    private function markRentPaymentPaid(SecurityDeposit|RentPayment|Plan|null $plan, User $payer, Gateway $gateway): void
    {
        if (! $plan instanceof RentPayment) {
            throw new \RuntimeException('Payment type/plan mismatch.');
        }

        $landlord = User::where('id', $plan->rent?->landlord_id ?? '')->first();

        if ($plan->payment_status !== 'paid') {
            if (! $landlord) {
                throw new \RuntimeException('Rent payment landlord not found.');
            }

            $landlord->forceFill([
                'balance' => $landlord->balance + $plan->total_amount,
            ])->save();

            $plan->update([
                'payment_date' => today(),
                'gateway_id' => $gateway->id,
                'payment_status' => 'paid',
            ]);

            Transaction::create([
                'paid_by' => $payer->id,
                'received_by' => $landlord->id,
                'gateway_id' => $gateway->id,
                'rent_payment_id' => $plan->id,
                'date' => today(),
                'amount' => $plan->subtotal_amount ?? 0,
                'charge' => $plan->charge ?? 0,
                'type' => 'debit',
                'payment_method' => $gateway->name ?? null,
                'payment_type' => 'rent_payment',
            ]);
        }
    }

    /**
     * Rama subscription: crea la suscripción paid + Transaction credit +
     * trío forceFill. Divergencia con SubscriptionService::paid
     * preservada por diseño (spec §5): aquí will_expire usa
     * durationInDays() y received_by es el primer superadmin/admin.
     */
    private function activateSubscription(SecurityDeposit|RentPayment|Plan|null $plan, User $user, Gateway $gateway, mixed $amount): void
    {
        if (! $plan instanceof Plan) {
            throw new \RuntimeException('Payment type/plan mismatch.');
        }

        $admin = User::whereIn('role', ['superadmin', 'admin'])->first();

        if (! $admin) {
            throw new \RuntimeException('No admin user available to receive the transaction.');
        }

        $duration = $plan->durationInDays();

        $subscribe = PlanSubscribe::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'plan_name' => $plan->subscriptionName,
            'number_of_property' => $plan->number_of_property,
            'number_of_tenant' => $plan->number_of_tenant,
            'number_of_labor' => $plan->number_of_labor,
            'duration' => $duration,
            'price' => $amount,
            'gateway_id' => $gateway->id,
            'payment_status' => 'paid',
        ]);

        Transaction::create([
            'paid_by' => $user->id,
            'received_by' => $admin->id,
            'gateway_id' => $gateway->id,
            'plan_subscribe_id' => $subscribe->id,
            'date' => today(),
            'amount' => $amount,
            'type' => 'credit',
            'payment_method' => $gateway->name ?? null,
            'payment_type' => 'subscription',
        ]);

        $user->forceFill([
            'subscriptionDate' => now(),
            'plan_subscribe_id' => $subscribe->id,
            'will_expire' => now()->addDays($duration),
        ])->save();
    }
}
