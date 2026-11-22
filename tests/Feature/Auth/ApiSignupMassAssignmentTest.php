<?php

namespace Tests\Feature\Auth;

use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * A1–A4 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11).
 *
 * A1 cerraba Seg-28 (inyección masiva en la API pública de sign-up):
 * nació como caracterización y se flipeó con el fix del mismo PR.
 * A2–A4 son regresión del comportamiento legítimo vía forceFill.
 */
class ApiSignupMassAssignmentTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_a1_api_sign_up_ignores_injected_columns(): void
    {
        // Arrange — plan pago: el bloque free-plan no interfiere
        Plan::factory()->create();
        $landlord = $this->createLandlord();

        // Act
        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'Api Landlord',
            'email' => 'api-injected@example.test',
            'password' => 'Password123',
            'balance' => 500,
            'will_expire' => '2099-12-31',
            'landlord_id' => $landlord->id,
            'is_setupped' => 1,
        ]);

        // Assert — Seg-28 cerrado (is_setupped queda en el default 0 del
        // esquema: la API no lo escribe hasta profileSetUp)
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'api-injected@example.test',
            'balance' => 0,
            'will_expire' => null,
            'landlord_id' => null,
            'is_setupped' => 0,
        ]);
    }

    public function test_a2_api_sign_up_legitimate_keeps_api_semantics(): void
    {
        // Arrange — sin plan: no hay free-plan que asignar
        // Act
        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'Api Landlord',
            'email' => 'api-legit@example.test',
            'password' => 'Password123',
        ]);

        // Assert — comportamiento preservado: is_setupped queda en 0 hasta profileSetUp
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'api-legit@example.test',
            'role' => 'landlord',
            'is_setupped' => 0,
        ]);
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_a3_api_submit_otp_valid_verifies_user(): void
    {
        // Arrange
        $user = $this->createLandlord([
            'remember_token' => '123456',
            'email_verified_at' => now()->addMinutes(10),
        ]);

        // Act
        $response = $this->postJson('/api/v1/submit-otp', [
            'otp' => '123456',
            'email' => $user->email,
        ]);

        // Assert
        $response->assertOk();

        $user = $user->fresh();
        $this->assertNull($user->remember_token);
        $this->assertTrue($user->email_verified_at->lte(now()));
    }

    public function test_a4_api_sign_up_with_free_plan_assigns_subscription_trio(): void
    {
        // Arrange
        Plan::factory()->free()->create();

        // Act
        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'Api Landlord',
            'email' => 'api-free@example.test',
            'password' => 'Password123',
        ]);

        // Assert — trío persistido vía forceFill (no fillable)
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'api-free@example.test',
        ]);

        $user = User::where('email', 'api-free@example.test')->first();
        $this->assertNotNull($user->plan_subscribe_id);
        $this->assertNotNull($user->subscriptionDate);
        $this->assertTrue(Carbon::parse($user->will_expire)->isFuture());
    }
}
