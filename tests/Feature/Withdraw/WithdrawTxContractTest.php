<?php

namespace Tests\Feature\Withdraw;

use App\Models\PlanSubscribe;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserMethod;
use App\Models\Withdraw;
use App\Models\WithdrawMethod;
use App\Notifications\SendNotification;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Contract tests F1–F6 (spec-tx-legadas-services-fase5, Fase 5 / 5.18).
 *
 * Congelan el contrato observable de los 3 flujos con tx de withdraw
 * ANTES de moverlos a WithdrawService. Deben seguir en verde tras el
 * move; los casos [D-fix] se actualizan en el commit de su desviación.
 *
 * Divergencia congelada (spec §4): Api notifica dentro de la tx,
 * Landlord no notifica, Admin approve notifica tras el commit.
 */
class WithdrawTxContractTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    /**
     * Landlord autenticable con método de retiro (charge fixed).
     *
     * @return array{0: User, 1: UserMethod}
     */
    private function landlordWithMethod(float $balance, float $min = 0, float $max = 10000, float $charge = 2): array
    {
        $landlord = $this->createLandlord(['balance' => $balance]);

        $method = WithdrawMethod::create([
            'name' => 'Bank Transfer',
            'min_amount' => $min,
            'max_amount' => $max,
            'charge' => $charge,
            'charge_type' => 'fixed',
        ]);

        $userMethod = UserMethod::create([
            'user_id' => $landlord->id,
            'method_id' => $method->id,
        ]);

        return [$landlord, $userMethod];
    }

    /**
     * Landlord con plan vigente — exige ExpiredMiddleware (grupo landlord).
     *
     * @return array{0: User, 1: UserMethod}
     */
    private function landlordWithPlanAndMethod(float $balance, float $min = 0, float $max = 10000, float $charge = 2): array
    {
        [$landlord, $userMethod] = $this->landlordWithMethod($balance, $min, $max, $charge);

        $subscribe = PlanSubscribe::factory()->create(['user_id' => $landlord->id]);
        $landlord->forceFill([
            'plan_subscribe_id' => $subscribe->id,
            'will_expire' => now()->addMonth(),
        ])->save();

        return [$landlord->fresh(), $userMethod];
    }

    public function test_f1_api_store_creates_withdraw_debits_balance_and_notifies(): void
    {
        // Arrange
        [$landlord, $userMethod] = $this->landlordWithMethod(balance: 100);
        Sanctum::actingAs($landlord);

        // Act
        $response = $this->postJson('/api/v1/landlord/withdraw', [
            'user_method_id' => $userMethod->id,
            'amount' => '10',
        ]);

        // Assert
        $response->assertOk()->assertJsonPath('message', 'Balance withdraw request send successfully');

        $withdraw = Withdraw::where('user_id', $landlord->id)->first();
        $this->assertNotNull($withdraw);
        $this->assertSame(10.0, (float) $withdraw->amount);
        $this->assertSame(2.0, (float) $withdraw->charge);
        $this->assertSame($userMethod->id, $withdraw->user_method_id);
        $this->assertSame(88.0, (float) $landlord->fresh()->balance);

        // Divergencia Api: notificación dentro de la tx (canal database)
        $this->assertTrue(
            DB::table('notifications')
                ->where('type', SendNotification::class)
                ->where('notifiable_id', $landlord->id)
                ->exists()
        );
    }

    public function test_f2_web_store_creates_withdraw_without_notification(): void
    {
        // Arrange
        [$landlord, $userMethod] = $this->landlordWithPlanAndMethod(balance: 100);

        // Act
        $response = $this->actingAs($landlord)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson('/landlord/withdraw', [
                'user_method_id' => $userMethod->id,
                'amount' => '10',
            ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('message', 'withdraw request has been send')
            ->assertJsonPath('modal', true);

        $this->assertSame(1, Withdraw::where('user_id', $landlord->id)->count());
        $this->assertSame(88.0, (float) $landlord->fresh()->balance);

        // Divergencia Landlord: sin notificación
        $this->assertSame(0, DB::table('notifications')->where('type', SendNotification::class)->count());
    }

    public function test_f3_store_below_min_is_rejected_without_effects(): void
    {
        // Arrange
        [$apiLandlord, $apiMethod] = $this->landlordWithMethod(balance: 100, min: 50);
        Sanctum::actingAs($apiLandlord);

        [$webLandlord, $webMethod] = $this->landlordWithPlanAndMethod(balance: 100, min: 50);

        // Act
        $apiResponse = $this->postJson('/api/v1/landlord/withdraw', [
            'user_method_id' => $apiMethod->id,
            'amount' => '10',
        ]);

        $webResponse = $this->actingAs($webLandlord)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson('/landlord/withdraw', [
                'user_method_id' => $webMethod->id,
                'amount' => '10',
            ]);

        // Assert — contrato congelado: 400 + mensaje exacto, sin efectos DB
        $apiResponse->assertStatus(400)->assertJsonPath('message', 'Withdraw amount must be at least 50');
        $webResponse->assertStatus(400)->assertJsonPath('message', 'Withdraw amount must be at least 50');

        $this->assertSame(0, Withdraw::count());
        $this->assertSame(100.0, (float) $apiLandlord->fresh()->balance);
        $this->assertSame(100.0, (float) $webLandlord->fresh()->balance);
    }

    public function test_f4_admin_approve_creates_transaction_and_updates_status(): void
    {
        // Arrange
        $superadmin = $this->createSuperadmin();
        [$landlord, $userMethod] = $this->landlordWithMethod(balance: 0);

        $withdraw = Withdraw::create([
            'user_id' => $landlord->id,
            'user_method_id' => $userMethod->id,
            'amount' => 10,
            'charge' => 2,
            'status' => 'pending',
        ]);

        // Act
        $response = $this->actingAs($superadmin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson("/admin/withdraw-request/approve/{$withdraw->id}", [
                'reason' => 'Paid out',
            ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('message', 'Withdrawal Approve successfully')
            ->assertJsonPath('redirect', route('admin.withdraw-request.index'));

        $transaction = Transaction::where('withdraw_id', $withdraw->id)->first();
        $this->assertNotNull($transaction);
        $this->assertSame($superadmin->id, $transaction->paid_by);
        $this->assertSame($landlord->id, $transaction->received_by);
        $this->assertSame('debit', $transaction->type);
        $this->assertSame('withdraw', $transaction->payment_type);
        $this->assertSame(10.0, (float) $transaction->amount);
        $this->assertSame(2.0, (float) $transaction->charge);
        $this->assertSame('Bank Transfer', $transaction->payment_method);

        $this->assertSame('approved', $withdraw->fresh()->status);
        $this->assertSame('Paid out', $withdraw->fresh()->reason);

        // Divergencia Admin: notificación tras el commit
        $this->assertTrue(
            DB::table('notifications')
                ->where('type', SendNotification::class)
                ->where('notifiable_id', $landlord->id)
                ->exists()
        );

        // Approve no toca el balance
        $this->assertSame(0.0, (float) $landlord->fresh()->balance);
    }

    public function test_f5_admin_reject_refunds_balance(): void
    {
        // Arrange
        $superadmin = $this->createSuperadmin();
        [$landlord, $userMethod] = $this->landlordWithMethod(balance: 0);

        $withdraw = Withdraw::create([
            'user_id' => $landlord->id,
            'user_method_id' => $userMethod->id,
            'amount' => 10,
            'charge' => 2,
            'status' => 'pending',
        ]);

        // Act
        $response = $this->actingAs($superadmin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson("/admin/withdraw-request/reject/{$withdraw->id}", [
                'reason' => 'Invalid account',
            ]);

        // Assert — [D-FIX-reject] tras el move: ambas escrituras atómicas
        $response->assertOk()
            ->assertJsonPath('message', 'Withdrawal rejected successfully')
            ->assertJsonPath('redirect', route('admin.withdraw-request.index'));

        $this->assertSame('rejected', $withdraw->fresh()->status);
        $this->assertSame('Invalid account', $withdraw->fresh()->reason);
        $this->assertSame(12.0, (float) $landlord->fresh()->balance);
        $this->assertSame(0, Transaction::where('withdraw_id', $withdraw->id)->count());

        // Reject no notifica hoy — congelado
        $this->assertSame(0, DB::table('notifications')->where('type', SendNotification::class)->count());
    }

    public function test_f6_non_admin_cannot_approve_foreign_withdraw(): void
    {
        // Arrange — Policy + middleware: approve/reject son false para no-admins
        [$landlord, $userMethod] = $this->landlordWithMethod(balance: 0);

        $withdraw = Withdraw::create([
            'user_id' => $landlord->id,
            'user_method_id' => $userMethod->id,
            'amount' => 10,
            'charge' => 2,
            'status' => 'pending',
        ]);

        // Act
        $response = $this->actingAs($landlord)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson("/admin/withdraw-request/approve/{$withdraw->id}", [
                'reason' => 'Own request',
            ]);

        // Assert — congelado: AdminMiddleware redirige a login (no aborta 403)
        $response->assertRedirect(route('login'));
        $this->assertSame('pending', $withdraw->fresh()->status);
        $this->assertSame(0, Transaction::count());
    }

    public function test_f7_api_store_rejects_foreign_user_method(): void
    {
        // Arrange
        [$owner, $ownerMethod] = $this->landlordWithMethod(balance: 100);
        [$attacker] = $this->landlordWithMethod(balance: 100);
        Sanctum::actingAs($attacker);

        // Act
        $response = $this->postJson('/api/v1/landlord/withdraw', [
            'user_method_id' => $ownerMethod->id,
            'amount' => '10',
        ]);

        // Assert — 5.23: lookup scopeado, sin efectos colaterales
        $response->assertNotFound();
        $this->assertSame(0, Withdraw::count());
        $this->assertSame(100.0, (float) $attacker->fresh()->balance);
        $this->assertSame(0, DB::table('notifications')->where('type', SendNotification::class)->count());
    }

    public function test_f8_web_store_rejects_foreign_user_method(): void
    {
        // Arrange
        [$owner, $ownerMethod] = $this->landlordWithPlanAndMethod(balance: 100);
        [$attacker] = $this->landlordWithPlanAndMethod(balance: 100);

        // Act
        $response = $this->actingAs($attacker)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson('/landlord/withdraw', [
                'user_method_id' => $ownerMethod->id,
                'amount' => '10',
            ]);

        // Assert — 5.23: lookup scopeado, sin efectos colaterales
        $response->assertNotFound();
        $this->assertSame(0, Withdraw::count());
        $this->assertSame(100.0, (float) $attacker->fresh()->balance);
    }
}
