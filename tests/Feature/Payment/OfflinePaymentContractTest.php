<?php

namespace Tests\Feature\Payment;

use App\Models\Gateway;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\RentPayment;
use App\Models\SecurityDeposit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Contract tests F12–F13 (spec-tx-legadas-services-fase5, Fase 5 / 5.18).
 *
 * Congelan el contrato observable de Api/SecurityDeposit::offline_payment
 * ANTES de mover la tx a SecurityDepositService::offlinePayment. Deben
 * seguir en verde tras el move.
 */
class OfflinePaymentContractTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_f12_offline_subscription_creates_unpaid_plan_subscribe(): void
    {
        // Arrange — HasUploader escribe en config('filesystems.default')
        Storage::fake(config('filesystems.default'));

        $tenant = $this->createTenant();
        $plan = Plan::factory()->create();
        $gateway = Gateway::factory()->manual()->create();
        Sanctum::actingAs($tenant);

        // Act
        $response = $this->post("/api/v1/tenant/offline-payment/{$gateway->id}/{$plan->id}", [
            'payment_type' => 'subscription',
            'payment_note' => 'Bank transfer ACME 123',
            'payment_receipt' => UploadedFile::fake()->image('receipt.jpg'),
        ]);

        // Assert
        $response->assertOk()->assertJsonPath('message', 'New subscription purchased requested.');

        $subscribe = PlanSubscribe::where('plan_id', $plan->id)->where('user_id', $tenant->id)->first();
        $this->assertNotNull($subscribe);
        $this->assertSame('unpaid', $subscribe->payment_status);
        $this->assertSame($gateway->id, $subscribe->gateway_id);
        $this->assertSame('Bank transfer ACME 123', $subscribe->notes['manual_data'] ?? null);
        $this->assertNotEmpty($subscribe->notes['attachment'] ?? null);
    }

    public function test_f13_offline_payment_marks_deposit_and_rent_pending(): void
    {
        // Arrange — HasUploader escribe en config('filesystems.default')
        Storage::fake(config('filesystems.default'));

        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $gateway = Gateway::factory()->manual()->create();

        $deposit = SecurityDeposit::factory()->create([
            'tenant_id' => $tenant->id,
            'landlord_id' => $landlord->id,
        ]);

        $rentPayment = RentPayment::factory()->create();

        Sanctum::actingAs($tenant);

        // Act — rama security_deposit (con authorize offlinePayment)
        $response = $this->post("/api/v1/tenant/offline-payment/{$gateway->id}/{$deposit->id}", [
            'payment_type' => 'security_deposit',
            'payment_note' => 'Paid via bank',
            'payment_receipt' => UploadedFile::fake()->image('receipt.jpg'),
        ]);

        // Assert
        $response->assertOk()->assertJsonPath('message', 'Payment sent successfully');

        $freshDeposit = $deposit->fresh();
        $this->assertSame('pending', $freshDeposit->status);
        $this->assertSame($gateway->id, $freshDeposit->gateway_id);
        $this->assertSame(today()->toDateString(), substr((string) $freshDeposit->payment_date, 0, 10));
        $this->assertSame('Paid via bank', $freshDeposit->payment_data['payment_note'] ?? null);
        $this->assertNotEmpty($freshDeposit->payment_data['payment_receipt'] ?? null);

        // Act — rama rent_payment (el tenant dueño del rent de la factory)
        Sanctum::actingAs($rentPayment->rent->tenant);

        $response = $this->post("/api/v1/tenant/offline-payment/{$gateway->id}/{$rentPayment->id}", [
            'payment_type' => 'rent_payment',
            'payment_note' => 'Paid via bank',
            'payment_receipt' => UploadedFile::fake()->image('receipt.jpg'),
        ]);

        // Assert
        $response->assertOk()->assertJsonPath('message', 'Payment sent successfully');

        $freshRent = $rentPayment->fresh();
        $this->assertSame('pending', $freshRent->payment_status);
        $this->assertSame($gateway->id, $freshRent->gateway_id);
        $this->assertSame(today()->toDateString(), substr((string) $freshRent->payment_date, 0, 10));
        $this->assertSame('Paid via bank', $freshRent->payment_data['payment_note'] ?? null);
    }

    public function test_f13b_foreign_deposit_offline_payment_returns_500_today(): void
    {
        // Congelado (quirk): authorize corre dentro del try →
        // AuthorizationException → catch → 500 'Payment failed' (no 403).
        // El Gate bloquea la escritura; el status es el observable actual.
        Storage::fake(config('filesystems.default'));

        $landlord = $this->createLandlord();
        $owner = $this->createTenant(['landlord_id' => $landlord->id]);
        $intruder = $this->createTenant();
        $deposit = SecurityDeposit::factory()->create([
            'tenant_id' => $owner->id,
            'landlord_id' => $landlord->id,
        ]);
        $originalStatus = $deposit->status;
        $gateway = Gateway::factory()->manual()->create();
        Sanctum::actingAs($intruder);

        // Act
        $response = $this->post("/api/v1/tenant/offline-payment/{$gateway->id}/{$deposit->id}", [
            'payment_type' => 'security_deposit',
            'payment_note' => 'Not mine',
            'payment_receipt' => UploadedFile::fake()->image('receipt.jpg'),
        ]);

        // Assert — sin escritura sobre el depósito ajeno
        $response->assertStatus(500)->assertJsonPath('message', 'Payment failed');
        $this->assertSame($originalStatus, $deposit->fresh()->status);
    }
}
