<?php

namespace Tests\Feature\Payment;

use Tests\TestCase;

/**
 * P8–P9 del spec-service-payment-fase5: contrato HTTP de las rutas
 * order-status y payment/failed (ramas web y passthrough) congelado
 * pre-migración del PaymentController. La rama passthrough no-web de
 * /payment/failed ya está congelada por PaymentFlowSmokeTest.
 */
class OrderStatusHttpTest extends TestCase
{
    public function test_p8a_order_status_web_subscription_redirects_with_custom_message(): void
    {
        // Act
        $response = $this->withSession([
            'platform' => 'web',
            'payment_type' => 'subscription',
        ])->get('/order-status?status=success&message=Custom+message');

        // Assert
        $response->assertRedirect(route('landlord.subscriptions.index'));
        $response->assertSessionHas('message', 'Custom message');
    }

    public function test_p8b_order_status_web_subscription_redirects_with_default_message(): void
    {
        // Act
        $response = $this->withSession([
            'platform' => 'web',
            'payment_type' => 'subscription',
        ])->get('/order-status?status=success');

        // Assert
        $response->assertRedirect(route('landlord.subscriptions.index'));
        $response->assertSessionHas('message', __('New subscription order successfully.'));
    }

    public function test_p8c_order_status_passthrough_returns_status_and_forgets_session(): void
    {
        // Act
        $response = $this->withSession([
            'platform' => 'app',
            'payment_type' => 'rent_payment',
        ])->get('/order-status?status=success');

        // Assert
        $response->assertOk();
        $this->assertSame('success', $response->getContent());
        $this->assertNull(session('platform'));
        $this->assertNull(session('payment_type'));
    }

    public function test_p9a_payment_failed_web_redirects_with_payment_msg(): void
    {
        // Act
        $response = $this->withSession([
            'platform' => 'web',
            'payment_msg' => 'Insufficient funds',
        ])->get('/payment/failed');

        // Assert
        $response->assertRedirect(route('landlord.subscriptions.index'));
        $response->assertSessionHas('error', 'Insufficient funds');
        $this->assertNull(session('payment_msg'));
    }

    public function test_p9b_payment_failed_non_web_redirects_with_payment_msg(): void
    {
        // Act
        $response = $this->withSession([
            'platform' => 'app',
            'payment_msg' => 'Insufficient funds',
        ])->get('/payment/failed');

        // Assert
        $response->assertRedirect(route('order.status', [
            'status' => 'failed',
            'message' => 'Insufficient funds',
        ]));
    }
}
