<?php

namespace Tests\Feature\Payment;

use App\Models\Gateway;
use App\Models\Plan;
use App\Models\RentPayment;
use App\Models\SecurityDeposit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * P7 del spec-service-payment-fase5: contrato HTTP del endpoint api
 * payment-url (mint de URLs firmadas para los 3 payment types) congelado
 * pre-migración del PaymentController.
 */
class PaymentUrlHttpTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_p7a_payment_url_subscription_mints_signed_url(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        $plan = Plan::factory()->create();
        Gateway::factory()->create();

        // Act
        Sanctum::actingAs($landlord, ['*']);
        $response = $this->getJson('/api/v1/payment-url?payment_type=subscription&plan_id='.$plan->id);

        // Assert
        $response->assertOk()->assertJsonStructure(['url']);
        $this->assertStringContainsString(
            '/payments-gateways/'.$plan->id.'/'.$landlord->id,
            $response->json('url')
        );

        // La URL firmada es funcional: renderiza la vista de gateways.
        $this->get($response->json('url'))->assertOk()->assertViewIs('payments.index');
    }

    public function test_p7b_payment_url_security_deposit_mints_signed_url(): void
    {
        // Arrange
        $tenant = $this->createTenant();
        $deposit = SecurityDeposit::factory()->create(['tenant_id' => $tenant->id]);

        // Act
        Sanctum::actingAs($tenant, ['*']);
        $response = $this->getJson('/api/v1/payment-url?payment_type=security_deposit&payment_id='.$deposit->id);

        // Assert
        $response->assertOk()->assertJsonStructure(['url']);
        $this->assertStringContainsString('/online-payments-gateways/'.$deposit->id, $response->json('url'));
    }

    public function test_p7c_payment_url_rent_payment_mints_signed_url(): void
    {
        // Arrange
        $rentPayment = RentPayment::factory()->create();
        $tenant = User::findOrFail($rentPayment->rent->tenant_id);

        // Act
        Sanctum::actingAs($tenant, ['*']);
        $response = $this->getJson('/api/v1/payment-url?payment_type=rent_payment&payment_id='.$rentPayment->id);

        // Assert
        $response->assertOk()->assertJsonStructure(['url']);
        $this->assertStringContainsString('/online-payments-gateways/'.$rentPayment->id, $response->json('url'));
    }

    public function test_p7d_payment_url_rejects_foreign_rent_payment(): void
    {
        // Arrange
        $otherTenant = $this->createTenant();
        $rentPayment = RentPayment::factory()->create();

        // Act
        Sanctum::actingAs($otherTenant, ['*']);
        $response = $this->getJson('/api/v1/payment-url?payment_type=rent_payment&payment_id='.$rentPayment->id);

        // Assert
        $response->assertForbidden();
    }

    public function test_p7e_payment_url_rejects_missing_or_invalid_type(): void
    {
        // Arrange
        Sanctum::actingAs($this->createLandlord(), ['*']);

        // Act + Assert
        $this->getJson('/api/v1/payment-url')->assertUnprocessable();
        $this->getJson('/api/v1/payment-url?payment_type=bogus')->assertUnprocessable();
    }

    public function test_p7f_payment_url_rejects_missing_or_nonexistent_id(): void
    {
        // Arrange
        Sanctum::actingAs($this->createLandlord(), ['*']);

        // Act + Assert
        $this->getJson('/api/v1/payment-url?payment_type=subscription')->assertUnprocessable();
        $this->getJson('/api/v1/payment-url?payment_type=subscription&plan_id=999999')->assertUnprocessable();
    }
}
