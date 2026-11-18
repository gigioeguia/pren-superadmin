<?php

namespace Tests\Feature\Auth;

use App\Mail\PasswordReset;
use App\Models\Option;
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

    public function test_api_sign_up_ignores_arbitrary_role(): void
    {
        // A14
        Plan::factory()->create();

        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'Mallory',
            'email' => 'mallory@example.test',
            'password' => 'password',
            'role' => 'superadmin',
        ]);

        $response->assertOk();
        $this->assertNotEmpty($response->json('token'));

        $this->assertDatabaseHas('users', [
            'email' => 'mallory@example.test',
            'role' => 'landlord',
        ]);
    }

    public function test_api_sign_up_without_role_creates_landlord(): void
    {
        // A15
        Plan::factory()->create();

        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'No Role',
            'email' => 'norole@example.test',
            'password' => 'password',
        ]);

        $response->assertOk();
        $this->assertNotEmpty($response->json('token'));

        $this->assertDatabaseHas('users', [
            'email' => 'norole@example.test',
            'role' => 'landlord',
        ]);
    }

    public function test_api_sign_up_otp_on_ignores_arbitrary_role(): void
    {
        // A16
        Plan::factory()->create();
        Option::factory()->create([
            'key' => 'email-varification',
            'value' => [
                'otp_status' => 'on',
                'otp_expiration_time' => 5,
                'otp_duration_type' => 'minute',
            ],
        ]);

        Mail::fake();

        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'Mallory OTP',
            'email' => 'malloryotp@example.test',
            'password' => 'password',
            'role' => 'superadmin',
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('otp_required'));

        $this->assertDatabaseHas('users', [
            'email' => 'malloryotp@example.test',
            'role' => 'landlord',
        ]);
    }

    public function test_api_sign_up_setupped_email_does_not_change_role(): void
    {
        // A17
        Plan::factory()->create();
        $existing = User::factory()->superadmin()->create([
            'email' => 'mallory@example.test',
            'is_setupped' => 1,
        ]);

        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'Mallory',
            'email' => 'mallory@example.test',
            'password' => 'password',
            'role' => 'superadmin',
        ]);

        $response->assertStatus(406);

        $this->assertSame('superadmin', $existing->fresh()->role);
    }

    public function test_api_sign_up_reregistration_forces_landlord(): void
    {
        // A18
        Plan::factory()->create();
        User::factory()->create([
            'email' => 'mallory@example.test',
            'is_setupped' => 0,
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'Mallory',
            'email' => 'mallory@example.test',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'mallory@example.test',
            'role' => 'landlord',
        ]);
    }

    public function test_user_mass_assignment_does_not_persist_role(): void
    {
        // U1
        $user = User::factory()->create();

        $user->fill(['role' => 'superadmin']);
        $user->save();

        $this->assertSame('landlord', $user->fresh()->role);

        $user->role = 'superadmin';
        $user->save();

        $this->assertSame('superadmin', $user->fresh()->role);
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

    public function test_api_reset_password_without_otp_returns_422(): void
    {
        // Arrange
        $user = $this->createLandlord();

        // Act
        $response = $this->postJson('/api/v1/user-password-reset', [
            'email' => $user->email,
            'password' => 'hacked-password',
        ]);

        // Assert
        $response->assertStatus(422);

        $this->assertFalse(Hash::check('hacked-password', $user->fresh()->password));
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
            'password' => 'hacked-password',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonPath('message', 'Invalid or expired OTP code.');

        $this->assertFalse(Hash::check('hacked-password', $user->fresh()->password));
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
            'password' => 'hacked-password',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonPath('message', 'Invalid or expired OTP code.');

        $this->assertFalse(Hash::check('hacked-password', $user->fresh()->password));
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
            'password' => 'new-secure-password',
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('message', 'Your password has been changed!');

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
        $this->assertNull($user->fresh()->remember_token);
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
