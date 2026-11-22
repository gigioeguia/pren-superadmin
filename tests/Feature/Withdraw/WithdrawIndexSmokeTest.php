<?php

namespace Tests\Feature\Withdraw;

use App\Models\Withdraw;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * @group smoke
 */
class WithdrawIndexSmokeTest extends TestCase
{
    use CreatesUsers, DatabaseTransactions;

    private function createWithdraw(int $userId): Withdraw
    {
        return Withdraw::create([
            'user_id' => $userId,
            'amount' => 10,
            'charge' => 0,
            'status' => 'pending',
        ]);
    }

    public function test_f1_index_respects_per_page(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        for ($i = 0; $i < 7; $i++) {
            $this->createWithdraw($landlord->id);
        }
        Sanctum::actingAs($landlord, ['*']);

        // Act
        $response = $this->getJson('/api/v1/landlord/withdraw?per_page=5');

        // Assert
        $response->assertOk()
            ->assertJsonCount(5, 'data.data')
            ->assertJsonPath('data.per_page', 5);
    }

    public function test_f2_index_filters_by_date_range(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        foreach (['2026-11-01', '2026-11-10', '2026-11-20'] as $date) {
            $withdraw = $this->createWithdraw($landlord->id);
            $withdraw->created_at = Carbon::parse($date);
            $withdraw->save();
        }
        Sanctum::actingAs($landlord, ['*']);

        // Act
        $response = $this->getJson('/api/v1/landlord/withdraw?from_date=2026-11-05&to_date=2026-11-15');

        // Assert
        $response->assertOk()
            ->assertJsonCount(1, 'data.data');
    }

    /**
     * @group security
     */
    public function test_f3_index_scopes_to_own_withdraws(): void
    {
        // Arrange
        $landlordA = $this->createLandlord();
        $landlordB = $this->createLandlord();
        $withdrawA = $this->createWithdraw($landlordA->id);
        $this->createWithdraw($landlordB->id);
        Sanctum::actingAs($landlordA, ['*']);

        // Act
        $response = $this->getJson('/api/v1/landlord/withdraw');

        // Assert
        $response->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.id', $withdrawA->id);
    }

    public function test_f4_index_message_key_is_well_formed(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        $this->createWithdraw($landlord->id);
        Sanctum::actingAs($landlord, ['*']);

        // Act
        $response = $this->getJson('/api/v1/landlord/withdraw');

        // Assert — key corregida desde 'meassage' (spec 5.10)
        $response->assertOk()
            ->assertJsonPath('message', 'Data fetched successfully')
            ->assertJsonMissingPath('meassage');
    }
}
