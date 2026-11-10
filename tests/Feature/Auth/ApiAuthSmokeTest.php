<?php

namespace Tests\Feature\Auth;

use App\Mail\PasswordReset;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class ApiAuthSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_api_sign_in_landlord_returns_token(): void
    {
        // Arrange
        $landlord = $this->createLandlord();

        // Act
        $response = $this->postJson('/api/v1/sign-in', [
            'email' => $landlord->email,
            'password' => 'password',
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonStructure(['message', 'data' => ['token', 'role']]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_api_sign_in_with_invalid_credentials_returns_401(): void
    {
        // Act
        $response = $this->postJson('/api/v1/sign-in', [
            'email' => 'nobody@example.test',
            'password' => 'wrong-password',
        ]);

        // Assert
        $response->assertUnauthorized();
    }

    public function test_api_sign_in_as_admin_returns_406(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act
        $response = $this->postJson('/api/v1/sign-in', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        // Assert
        $response->assertStatus(406);
    }

    public function test_api_sign_up_creates_landlord_and_returns_token(): void
    {
        // Arrange
        // get_option('email-varification') sin registro => otp_status 'off'
        Plan::factory()->create();

        // Act
        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'John Landlord',
            'email' => 'john@example.test',
            'password' => 'password',
            'role' => 'landlord',
        ]);

        // Assert
        // Nota: el controller responde 200 (sin codigo explicito), no 201 (desviacion D1 del spec)
        $response->assertOk();

        $this->assertNotEmpty($response->json('token'));

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.test',
            'role' => 'landlord',
        ]);
    }

    /**
     * @group characterization
     */
    public function test_api_sign_up_accepts_arbitrary_role(): void
    {
        // Expected to fail after Phase 1 (Seg-4 fix) — API registration
        // must not accept arbitrary roles like superadmin. Update to
        // secure behavior (403/422 or role forced to landlord) when it fails.
        // Arrange
        Plan::factory()->create();

        // Act
        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'Mallory',
            'email' => 'mallory@example.test',
            'password' => 'password',
            'role' => 'superadmin',
        ]);

        // Assert
        $response->assertOk();
        $this->assertNotEmpty($response->json('token'));

        $this->assertDatabaseHas('users', [
            'email' => 'mallory@example.test',
            'role' => 'superadmin',
        ]);
    }

    public function test_api_submit_otp_with_valid_token_returns_token(): void
    {
        // Arrange
        $user = User::factory()->landlord()->create([
            'email' => 'john@example.test',
            'remember_token' => '123456',
            'email_verified_at' => now()->addHour(),
        ]);

        // Act
        $response = $this->postJson('/api/v1/submit-otp', [
            'otp' => '123456',
            'email' => $user->email,
        ]);

        // Assert
        $response->assertOk();
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_api_send_reset_code_sends_mail_and_stores_code(): void
    {
        // Arrange
        Mail::fake();
        $user = $this->createLandlord();

        // Act
        $response = $this->postJson('/api/v1/send-reset-code', [
            'email' => $user->email,
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('message', 'Password reset code has been sent to your email.');

        $this->assertEquals(6, strlen((string) $user->fresh()->remember_token));

        Mail::assertSent(PasswordReset::class);
    }

    /**
     * @group characterization
     */
    public function test_api_reset_password_without_otp_changes_password(): void
    {
        // Expected to fail after Phase 1 (Seg-3 fix) — userResetPassword
        // must require a valid OTP code, not just email + password.
        // Arrange
        $user = $this->createLandlord();

        // Act
        $response = $this->postJson('/api/v1/user-password-reset', [
            'email' => $user->email,
            'password' => 'hacked-password',
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('message', 'Your password has been changed!');

        $this->assertTrue(Hash::check('hacked-password', $user->fresh()->password));
    }

    public function test_api_refresh_token_returns_new_token(): void
    {
        // Arrange
        $user = $this->createLandlord();
        Sanctum::actingAs($user, ['*']);

        // Act
        $response = $this->postJson('/api/v1/refresh-token');

        // Assert
        $response->assertOk();
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_api_sign_out_returns_200(): void
    {
        // Arrange
        $user = $this->createLandlord();
        Sanctum::actingAs($user, ['*']);

        // Act
        $response = $this->getJson('/api/v1/sign-out');

        // Assert
        $response->assertOk()
            ->assertJsonPath('message', __('Sign out successfully'));
    }
}
