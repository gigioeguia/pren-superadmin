<?php

namespace Tests\Feature\Payment;

use App\Models\Gateway;
use App\Models\PlanSubscribe;
use App\Models\SecurityDeposit;
use App\Models\User;
use App\Models\UserMethod;
use App\Models\WithdrawMethod;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * P1–P3 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11):
 * el balance se acredita/debita vía forceFill tras salir de `$fillable`.
 */
class BalanceForceFillTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    /**
     * El landlord necesita plan vigente para pasar ExpiredMiddleware.
     */
    private function landlordWithPlan(array $attributes = []): User
    {
        $landlord = $this->createLandlord($attributes);
        $subscribe = PlanSubscribe::factory()->create(['user_id' => $landlord->id]);
        $landlord->forceFill([
            'plan_subscribe_id' => $subscribe->id,
            'will_expire' => now()->addMonth(),
        ])->save();

        return $landlord;
    }

    public function test_p1_payment_success_credits_landlord_balance(): void
    {
        // Arrange
        $landlord = $this->createLandlord(['balance' => 0]);
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $deposit = SecurityDeposit::factory()->create([
            'tenant_id' => $tenant->id,
            'landlord_id' => $landlord->id,
            'landlord_balance' => 90,
            'status' => 'pending',
        ]);
        $gateway = Gateway::factory()->create();

        // Act
        $response = $this->actingAs($tenant)->withSession([
            'payment_type' => 'security_deposit',
            'plan' => $deposit,
            'user' => $tenant,
            'amount' => $deposit->total_amount,
            'gateway_id' => $gateway->id,
            'platform' => 'web',
            'payment_info' => ['status' => 1],
        ])->get('/payment/success');

        // Assert — balance acreditado vía forceFill
        $response->assertRedirect(route('order.status', ['status' => 'success']));

        $this->assertSame(90.0, (float) $landlord->fresh()->balance);
        $this->assertSame('paid', $deposit->fresh()->status);
    }

    public function test_p2_web_withdraw_debits_landlord_balance(): void
    {
        // Arrange
        $landlord = $this->landlordWithPlan(['balance' => 100]);
        $method = WithdrawMethod::create([
            'name' => 'Bank Transfer',
            'min_amount' => 0,
            'max_amount' => 10000,
            'charge' => 0,
            'charge_type' => 'fixed',
        ]);
        $userMethod = UserMethod::create([
            'user_id' => $landlord->id,
            'method_id' => $method->id,
        ]);

        // Act
        $response = $this->actingAs($landlord)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson('/landlord/withdraw', [
                'user_method_id' => $userMethod->id,
                'amount' => '10',
            ]);

        // Assert — balance debitado vía forceFill
        $response->assertOk();

        $this->assertSame(90.0, (float) $landlord->fresh()->balance);
    }

    public function test_p3_api_withdraw_debits_landlord_balance(): void
    {
        // Arrange
        $landlord = $this->createLandlord(['balance' => 100]);
        $method = WithdrawMethod::create([
            'name' => 'Bank Transfer',
            'min_amount' => 0,
            'max_amount' => 10000,
            'charge' => 0,
            'charge_type' => 'fixed',
        ]);
        $userMethod = UserMethod::create([
            'user_id' => $landlord->id,
            'method_id' => $method->id,
        ]);
        Sanctum::actingAs($landlord);

        // Act
        $response = $this->postJson('/api/v1/landlord/withdraw', [
            'user_method_id' => $userMethod->id,
            'amount' => '10',
        ]);

        // Assert — balance debitado vía forceFill
        $response->assertOk()->assertJsonPath('message', 'Balance withdraw request send successfully');

        $this->assertSame(90.0, (float) $landlord->fresh()->balance);
    }
}
