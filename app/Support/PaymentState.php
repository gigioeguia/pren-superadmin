<?php

namespace App\Support;

use App\Models\Plan;
use App\Models\RentPayment;
use App\Models\SecurityDeposit;
use App\Models\User;

/**
 * Snapshot del estado de pago leído de la sesión web por el
 * PaymentController (spec-service-payment-fase5): el PaymentService es
 * agnóstico de HTTP/sesión y recibe este estado ya tipado; el
 * controlador mapea el PaymentOutcome a la respuesta.
 *
 * `fromSession` es la única fuente del contrato de sesión del flujo de
 * pago web (payment_type, plan, plan_id, user, user_id, amount,
 * gateway_id, payment_info, session id): replica 1:1 las lecturas del
 * PaymentController original, tratando cualquier valor de sesión no
 * escalar/modelo como ausente.
 */
final class PaymentState
{
    /**
     * @param  SecurityDeposit|RentPayment|Plan|null  $plan  modelo stasheado en sesión por payment()
     * @param  array<string, mixed>|null  $paymentInfo  confirmación del gateway (session('payment_info'))
     */
    public function __construct(
        public readonly int|string|null $paymentType,
        public readonly SecurityDeposit|RentPayment|Plan|null $plan,
        public readonly int|string|null $planId,
        public readonly ?User $user,
        public readonly int|string|null $userId,
        public readonly mixed $amount,
        public readonly int|string|null $gatewayId,
        public readonly ?array $paymentInfo,
        public readonly string $sessionId,
        public readonly ?string $stripeSessionId,
    ) {}

    /**
     * @param  string|null  $stripeSessionId  query session_id (verificación HTTP de Stripe).
     */
    public static function fromSession(?string $stripeSessionId = null): self
    {
        $user = session('user') instanceof User ? session('user') : null;
        $userId = self::sessionScalar('user_id');

        if ($userId && ! $user) {
            $user = User::find($userId);
        }

        return new self(
            paymentType: self::sessionScalar('payment_type'),
            plan: self::sessionModel('plan'),
            planId: self::sessionScalar('plan_id'),
            user: $user,
            userId: $userId,
            amount: session('amount'),
            gatewayId: self::sessionScalar('gateway_id'),
            paymentInfo: self::sessionArray('payment_info'),
            sessionId: session()->getId(),
            stripeSessionId: $stripeSessionId,
        );
    }

    /**
     * Valor escalar de sesión (int|string): el flujo guarda valores
     * crudos (ids de URL como string, ids de modelo como int); cualquier
     * otro tipo se trata como ausente.
     */
    private static function sessionScalar(string $key): int|string|null
    {
        $value = session($key);

        return is_int($value) || is_string($value) ? $value : null;
    }

    private static function sessionArray(string $key): ?array
    {
        $value = session($key);

        return is_array($value) ? $value : null;
    }

    private static function sessionModel(string $key): SecurityDeposit|RentPayment|Plan|null
    {
        $model = session($key);

        return $model instanceof SecurityDeposit || $model instanceof RentPayment || $model instanceof Plan
            ? $model
            : null;
    }
}
