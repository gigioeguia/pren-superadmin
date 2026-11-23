<?php

namespace Tests\Unit\Support;

use App\Support\PaymentState;
use Tests\TestCase;

/**
 * U2–U3 del spec-session-serialization-json-fase5: el contrato de
 * sesión del flujo de pago es 100% escalar (serialization=json) —
 * PaymentState espeja scalares y trata cualquier valor no-escalar
 * como ausente, sin crash.
 */
class PaymentStateTest extends TestCase
{
    public function test_u2_from_session_maps_scalar_contract(): void
    {
        // Arrange
        session([
            'payment_type' => 'subscription',
            'plan_id' => '7',
            'user_id' => 3,
            'gateway_id' => 5,
            'amount' => 8,
            'payment_info' => ['status' => 1],
        ]);

        // Act
        $state = PaymentState::fromSession('cs_test_123');

        // Assert
        $this->assertSame('subscription', $state->paymentType);
        $this->assertSame('7', $state->planId);
        $this->assertSame(3, $state->userId);
        $this->assertSame(5, $state->gatewayId);
        $this->assertSame(['status' => 1], $state->paymentInfo);
        $this->assertSame('cs_test_123', $state->stripeSessionId);
        $this->assertNotSame('', $state->sessionId);
    }

    public function test_u3_non_scalar_session_values_are_treated_as_absent(): void
    {
        // Arrange — sesión crafteda: basura en cada clave escalar.
        session([
            'payment_type' => ['crafted'],
            'plan_id' => ['array'],
            'user_id' => [],
            'gateway_id' => 'still-valid',
            'payment_info' => 'garbage',
            'amount' => 'kept-as-is',
        ]);

        // Act
        $state = PaymentState::fromSession();

        // Assert — sin crash, las claves basura se leen como ausentes.
        $this->assertNull($state->paymentType);
        $this->assertNull($state->planId);
        $this->assertNull($state->userId);
        $this->assertSame('still-valid', $state->gatewayId);
        $this->assertNull($state->paymentInfo);
        $this->assertSame('kept-as-is', $state->amount);
    }
}
