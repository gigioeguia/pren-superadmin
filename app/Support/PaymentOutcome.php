<?php

namespace App\Support;

/**
 * Resultado observable del procesamiento de un pago confirmado
 * (spec-service-payment-fase5): status del redirect order.status,
 * mensaje y flash key replican 1:1 los strings actuales del
 * PaymentController.
 */
enum PaymentOutcome
{
    case Success;
    case InvalidCredentials;
    case SomethingWrong;

    public function status(): string
    {
        return $this === self::Success ? 'success' : 'failed';
    }

    public function message(): string
    {
        return match ($this) {
            self::Success => 'Payment successfully.',
            self::SomethingWrong => 'Something went wrong!',
            self::InvalidCredentials => 'Transaction failed for invalid credentials, Please try again later or contact with admin.',
        };
    }

    public function flashKey(): string
    {
        return $this === self::InvalidCredentials ? 'error' : 'message';
    }
}
