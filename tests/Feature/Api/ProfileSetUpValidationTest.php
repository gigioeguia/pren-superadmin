<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * H3 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11).
 *
 * Cerraba la falta de validación de `profile_type` en profileSetUp
 * (Q16-2): nació como caracterización y se flipeó con el fix del
 * mismo PR.
 */
class ProfileSetUpValidationTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_h3_profile_set_up_rejects_non_string_profile_type(): void
    {
        // Arrange
        $tenant = $this->createTenant(['profile_type' => 'personal']);
        Sanctum::actingAs($tenant);

        // Act — profile_type no-string: ahora validado (Q16-2)
        $response = $this->postJson('/api/v1/profile-setup', [
            'name' => 'Tenant Name',
            'email' => $tenant->email,
            'profile_type' => 123,
        ]);

        // Assert
        $response->assertStatus(422)->assertJsonValidationErrors('profile_type');

        $this->assertDatabaseHas('users', [
            'id' => $tenant->id,
            'profile_type' => 'personal',
        ]);
    }

    public function test_h3_profile_set_up_accepts_valid_profile_type(): void
    {
        // Arrange
        $tenant = $this->createTenant();
        Sanctum::actingAs($tenant);

        // Act
        $response = $this->postJson('/api/v1/profile-setup', [
            'name' => 'Tenant Name',
            'email' => $tenant->email,
            'profile_type' => 'company',
        ]);

        // Assert
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $tenant->id,
            'profile_type' => 'company',
            'is_setupped' => 1,
        ]);
    }
}
