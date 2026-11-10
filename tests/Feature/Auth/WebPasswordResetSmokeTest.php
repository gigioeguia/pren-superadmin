<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class WebPasswordResetSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_forgot_password_view_renders(): void
    {
        // Act
        $response = $this->get('/forgot-password');

        // Assert
        $response->assertOk()->assertViewIs('auth.forgot-password');
    }

    public function test_forgot_password_with_existing_email_sends_reset_link(): void
    {
        // Arrange
        Notification::fake();
        $user = $this->createLandlord();

        // Act
        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        // Assert
        $response->assertStatus(302)->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_with_unknown_email_returns_validation_error(): void
    {
        // Act
        $response = $this->post('/forgot-password', [
            'email' => 'nobody@example.test',
        ]);

        // Assert
        $response->assertStatus(302)->assertSessionHasErrors('email');
    }

    public function test_reset_password_view_renders_with_token(): void
    {
        // Act
        $response = $this->get('/reset-password/any-token');

        // Assert
        $response->assertOk()->assertViewIs('auth.reset-password');
    }

    public function test_reset_password_with_valid_token_updates_password(): void
    {
        // Arrange
        Notification::fake();
        $user = $this->createLandlord();

        $this->post('/forgot-password', ['email' => $user->email]);

        $token = Notification::sent($user, ResetPassword::class)->first()->token;

        // Act
        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        // Assert
        $response->assertStatus(302)->assertSessionHas('status');

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }
}
