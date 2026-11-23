<?php

namespace Tests\Feature\Withdraw;

use App\Models\User;
use App\Models\UserMethod;
use App\Models\WithdrawMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Regresión ownership Api WithdrawMethod (spec-ownership-withdraw-store-fase5, Fase 5 / 5.23 extensión).
 *
 * Cubre update/destroy de /api/v1/landlord/withdraw-methods/{id}: el recurso
 * debe resolverse scopeado por user_id del autenticado. No había tests previos.
 */
class ApiWithdrawMethodOwnershipTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    /**
     * @return array{0: User, 1: UserMethod, 2: WithdrawMethod}
     */
    private function landlordWithUserMethod(array $userAttributes = []): array
    {
        $landlord = $this->createLandlord($userAttributes);

        $withdrawMethod = WithdrawMethod::create([
            'name' => 'Bank Transfer',
            'min_amount' => 0,
            'max_amount' => 10000,
            'charge' => 2,
            'charge_type' => 'fixed',
            'status' => 1,
        ]);

        $userMethod = UserMethod::create([
            'user_id' => $landlord->id,
            'method_id' => $withdrawMethod->id,
            'account_no' => 'ACC-OWNER',
            'infos' => ['holder' => 'Owner'],
        ]);

        return [$landlord, $userMethod, $withdrawMethod];
    }

    public function test_f9_api_update_rejects_foreign_user_method(): void
    {
        // Arrange
        [$owner, $ownerMethod, $ownerWithdrawMethod] = $this->landlordWithUserMethod();
        $attacker = $this->createLandlord();
        Sanctum::actingAs($attacker);

        $original = $ownerMethod->only(['user_id', 'method_id', 'account_no', 'infos']);

        // Act
        $response = $this->putJson("/api/v1/landlord/withdraw-methods/{$ownerMethod->id}", [
            'method_id' => $ownerWithdrawMethod->id,
            'account_no' => 'ACC-HACKED',
        ]);

        // Assert
        $response->assertNotFound();
        $ownerMethod->refresh();
        $this->assertSame($original['user_id'], $ownerMethod->user_id);
        $this->assertSame($original['method_id'], $ownerMethod->method_id);
        $this->assertSame($original['account_no'], $ownerMethod->account_no);
        $this->assertSame($original['infos'], $ownerMethod->infos);
    }

    public function test_f10_api_destroy_rejects_foreign_user_method(): void
    {
        // Arrange
        [$owner, $ownerMethod] = $this->landlordWithUserMethod();
        $attacker = $this->createLandlord();
        Sanctum::actingAs($attacker);

        // Act
        $response = $this->deleteJson("/api/v1/landlord/withdraw-methods/{$ownerMethod->id}");

        // Assert
        $response->assertNotFound();
        $this->assertDatabaseHas('user_methods', [
            'id' => $ownerMethod->id,
            'user_id' => $owner->id,
        ]);
    }

    public function test_f11_api_update_happy_path_owner(): void
    {
        // Arrange
        [$owner, $ownerMethod] = $this->landlordWithUserMethod();
        Sanctum::actingAs($owner);

        $newWithdrawMethod = WithdrawMethod::create([
            'name' => 'Mobile Money',
            'min_amount' => 0,
            'max_amount' => 5000,
            'charge' => 1,
            'charge_type' => 'fixed',
            'status' => 1,
        ]);

        // Act
        $response = $this->putJson("/api/v1/landlord/withdraw-methods/{$ownerMethod->id}", [
            'method_id' => $newWithdrawMethod->id,
            'account_no' => 'ACC-UPDATED',
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('message', __('Withdraw method setup successfully.'));

        $ownerMethod->refresh();
        $this->assertSame($owner->id, $ownerMethod->user_id);
        $this->assertSame($newWithdrawMethod->id, $ownerMethod->method_id);
        $this->assertSame('ACC-UPDATED', $ownerMethod->account_no);
    }

    public function test_f12_api_destroy_happy_path_owner(): void
    {
        // Arrange
        [$owner, $ownerMethod] = $this->landlordWithUserMethod();
        Sanctum::actingAs($owner);

        // Act
        $response = $this->deleteJson("/api/v1/landlord/withdraw-methods/{$ownerMethod->id}");

        // Assert
        $response->assertOk()
            ->assertJsonPath('message', __('Withdraw method deleted successfully.'));
        $this->assertDatabaseMissing('user_methods', ['id' => $ownerMethod->id]);
    }
}
