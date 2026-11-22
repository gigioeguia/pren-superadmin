<?php

namespace Tests\Feature\Auth;

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * A1–A4 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11).
 *
 * A1 es caracterización de Seg-28 (inyección masiva en la API pública
 * de sign-up): documenta el comportamiento inseguro vigente y se flipea
 * en el mismo PR que aplica el fix.
 */
class ApiSignupMassAssignmentTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    /**
     * @group characterization
     */
    public function test_a1_api_sign_up_injection_is_persisted(): void
    {
        // Expected to fail after spec-user-fillable-restrictivo-fase5 (Seg-28 fix).

        // Arrange — plan pago: el bloque free-plan no interfiere; landlord real (FK)
        Plan::factory()->create();
        $landlord = $this->createLandlord();

        // Act
        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'Api Landlord',
            'email' => 'api-injected@example.test',
            'password' => 'Password123',
            'balance' => 500,
            'will_expire' => '2099-12-31 00:00:00',
            'landlord_id' => $landlord->id,
            'is_setupped' => 1,
        ]);

        // Assert — comportamiento inseguro actual
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'api-injected@example.test',
            'balance' => 500,
            'will_expire' => '2099-12-31 00:00:00',
            'landlord_id' => $landlord->id,
            'is_setupped' => 1,
        ]);
    }
}
