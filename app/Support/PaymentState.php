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
}
