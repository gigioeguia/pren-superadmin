<?php

namespace App\Support;

use App\Models\Gateway;
use App\Models\Plan;
use App\Models\RentPayment;
use App\Models\SecurityDeposit;
use App\Models\User;

/**
 * Resolución tipada del intento de pago (PaymentService::resolvePayment):
 * gateway whitelisted + user + plan con su monto según payment_type.
 */
final class PaymentContext
{
    /**
     * @param  SecurityDeposit|RentPayment|Plan  $plan
     * @param  int|float  $amount
     */
    public function __construct(
        public readonly User $user,
        public readonly Gateway $gateway,
        public readonly SecurityDeposit|RentPayment|Plan $plan,
        public readonly int|float $amount,
        public readonly string $paymentType,
    ) {}
}
