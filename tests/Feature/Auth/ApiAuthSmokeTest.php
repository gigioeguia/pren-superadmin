<?php

namespace Tests\Feature\Auth;

use App\Mail\PasswordReset;
use App\Models\Option;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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
            'password' => 'Password123',
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

    public function test_api_sign_up_with_arbitrary_role_is_forced_to_landlord(): void
    {
        // Arrange (A5)
        Plan::factory()->create();

        // Act
        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'Mallory',
            'email' => 'mallory@example.test',
            'password' => 'Password123',
            'role' => 'superadmin',
        ]);

        // Assert
        $response->assertOk();
        $this->assertNotEmpty($response->json('token'));

        $this->assertDatabaseHas('users', [
            'email' => 'mallory@example.test',
            'role' => 'landlord',
        ]);
    }

    public function test_api_sign_up_without_role_creates_landlord(): void
    {
        // Arrange (A14)
        Plan::factory()->create();

        // Act
        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'NoRole User',
            'email' => 'norole@example.test',
            'password' => 'Password123',
        ]);

        // Assert
        $response->assertOk();
        $this->assertNotEmpty($response->json('token'));

        $this->assertDatabaseHas('users', [
            'email' => 'norole@example.test',
            'role' => 'landlord',
        ]);
    }

    public function test_api_sign_up_otp_on_ignores_arbitrary_role(): void
    {
        // Arrange (A15)
        Plan::factory()->create();

        // enable OTP
        Cache::forget('email-varification');
        Option::create([
            'key' => 'email-varification',
            'value' => ['otp_status' => 'on', 'otp_expiration_time' => 10, 'otp_duration_type' => 'minute'],
            'status' => 1,
        ]);

        Mail::fake();

        // Act
        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'Otp User',
            'email' => 'otpuser@example.test',
            'password' => 'Password123',
            'role' => 'admin',
        ]);

        // Assert
        $response->assertOk();
        $this->assertTrue((bool) $response->json('otp_required'));

        $this->assertDatabaseHas('users', [
            'email' => 'otpuser@example.test',
            'role' => 'landlord',
        ]);
    }

    // A18 — contraseña débil rechazada (política Password)
    public function test_api_sign_up_with_weak_password_returns_422(): void
    {
        // Arrange
        Plan::factory()->create();

        // Act
        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'Weak User',
            'email' => 'weak@example.test',
            'password' => 'password',
        ]);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
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

    // A15 — OTP expirado
    public function test_api_submit_otp_with_expired_token_returns_410(): void
    {
        // Arrange
        $user = User::factory()->landlord()->create([
            'email' => 'john@example.test',
            'remember_token' => '123456',
            'email_verified_at' => now()->subHour(),
        ]);

        // Act
        $response = $this->postJson('/api/v1/submit-otp', [
            'otp' => '123456',
            'email' => $user->email,
        ]);

        // Assert
        $response->assertStatus(410)
            ->assertJsonPath('error', __('The verification otp has been expired.'));
        $this->assertEmpty($response->json('token'));
    }

    // A16 — OTP inválido
    public function test_api_submit_otp_with_invalid_token_returns_400(): void
    {
        // Arrange
        $user = User::factory()->landlord()->create([
            'email' => 'john@example.test',
            'remember_token' => '123456',
            'email_verified_at' => now()->addHour(),
        ]);

        // Act
        $response = $this->postJson('/api/v1/submit-otp', [
            'otp' => '999999',
            'email' => $user->email,
        ]);

        // Assert
        $response->assertStatus(400)
            ->assertJsonPath('error', __('Invalid otp.'));
    }

    // A17 — bypass cerrado: remember_token null + otp '0000'
    public function test_api_submit_otp_with_null_token_and_zero_otp_returns_400(): void
    {
        // Arrange
        $user = User::factory()->landlord()->create([
            'email' => 'john@example.test',
            'remember_token' => null,
            'email_verified_at' => now()->addHour(),
        ]);

        // Act
        $response = $this->postJson('/api/v1/submit-otp', [
            'otp' => '0000',
            'email' => $user->email,
        ]);

        // Assert
        $response->assertStatus(400)
            ->assertJsonPath('error', __('Invalid otp.'));
        $this->assertNull($user->fresh()->remember_token);
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

    public function test_api_reset_password_without_otp_returns_422(): void
    {
        // Arrange
        $user = $this->createLandlord();

        // Act
        $response = $this->postJson('/api/v1/user-password-reset', [
            'email' => $user->email,
            'password' => 'HackedPass123',
        ]);

        // Assert
        $response->assertStatus(422);

        $this->assertFalse(Hash::check('HackedPass123', $user->fresh()->password));
    }

    public function test_api_reset_password_with_invalid_otp_returns_422(): void
    {
        // Arrange
        $user = $this->createLandlord();
        $user->update([
            'remember_token' => '123456',
            'email_verified_at' => now()->addMinutes(15),
        ]);

        // Act
        $response = $this->postJson('/api/v1/user-password-reset', [
            'email' => $user->email,
            'otp' => '999999',
            'password' => 'HackedPass123',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonPath('message', 'Invalid or expired OTP code.');

        $this->assertFalse(Hash::check('HackedPass123', $user->fresh()->password));
    }

    public function test_api_reset_password_with_expired_otp_returns_422(): void
    {
        // Arrange
        $user = $this->createLandlord();
        $user->update([
            'remember_token' => '123456',
            'email_verified_at' => now()->subMinute(),
        ]);

        // Act
        $response = $this->postJson('/api/v1/user-password-reset', [
            'email' => $user->email,
            'otp' => '123456',
            'password' => 'HackedPass123',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonPath('message', 'Invalid or expired OTP code.');

        $this->assertFalse(Hash::check('HackedPass123', $user->fresh()->password));
    }

    public function test_api_reset_password_with_valid_otp_changes_password(): void
    {
        // Arrange
        $user = $this->createLandlord();
        $user->update([
            'remember_token' => '123456',
            'email_verified_at' => now()->addMinutes(15),
        ]);

        // Act
        $response = $this->postJson('/api/v1/user-password-reset', [
            'email' => $user->email,
            'otp' => '123456',
            'password' => 'NewPassword123',
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('message', 'Your password has been changed!');

        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
        $this->assertNull($user->fresh()->remember_token);
    }

    // A19 — contraseña débil rechazada en reset API
    public function test_api_reset_password_with_weak_password_returns_422(): void
    {
        // Arrange
        $user = $this->createLandlord();
        $user->update([
            'remember_token' => '123456',
            'email_verified_at' => now()->addMinutes(15),
        ]);

        // Act
        $response = $this->postJson('/api/v1/user-password-reset', [
            'email' => $user->email,
            'otp' => '123456',
            'password' => 'password',
        ]);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
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

    // A20 — throttle: 6.º intento de sign-in devuelve 429
    public function test_api_sign_in_is_throttled_after_five_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/sign-in', [
                'email' => 'nobody@example.test',
                'password' => 'wrong-password',
            ])->assertUnauthorized();
        }

        $response = $this->postJson('/api/v1/sign-in', [
            'email' => 'nobody@example.test',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
        $this->assertTrue($response->headers->has('Retry-After'));
    }

    // A21 — throttle: 6.º send-reset-code devuelve 429
    public function test_api_send_reset_code_is_throttled_after_five_attempts(): void
    {
        // Arrange
        Mail::fake();
        $user = $this->createLandlord();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/send-reset-code', [
                'email' => $user->email,
            ])->assertOk();
        }

        $response = $this->postJson('/api/v1/send-reset-code', [
            'email' => $user->email,
        ]);

        $response->assertStatus(429);
        $this->assertTrue($response->headers->has('Retry-After'));
    }
}
