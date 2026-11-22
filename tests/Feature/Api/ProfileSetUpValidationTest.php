<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * H3 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11).
 *
 * Caracterización de la falta de validación de `profile_type` en
 * profileSetUp (Q16-2): documenta el comportamiento actual y se flipea
 * en el mismo PR que aplica el fix.
 */
class ProfileSetUpValidationTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    /**
     * @group characterization
     */
    public function test_h3_profile_set_up_accepts_unvalidated_profile_type(): void
    {
        // Expected to fail after spec-user-fillable-restrictivo-fase5 (Q16-2 fix).

        // Arrange
        $tenant = $this->createTenant();
        Sanctum::actingAs($tenant);

        // Act — profile_type no-string: sin regla de validación hoy
        $response = $this->postJson('/api/v1/profile-setup', [
            'name' => 'Tenant Name',
            'email' => $tenant->email,
            'profile_type' => 123,
        ]);

        // Assert — comportamiento actual: se persiste crudo
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $tenant->id,
            'is_setupped' => 1,
            'profile_type' => '123',
        ]);
    }
}
