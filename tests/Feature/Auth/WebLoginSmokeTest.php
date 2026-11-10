<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class WebLoginSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_landlord_login_returns_200_with_dashboard_redirect(): void
    {
        // Arrange
        $landlord = $this->createLandlord();

        // Act
        $response = $this->postJson('/login/store', [
            'email' => $landlord->email,
            'password' => 'password',
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('redirect', route('landlord.dashboard'));

        $this->assertAuthenticatedAs($landlord);
    }

    public function test_login_with_invalid_credentials_returns_401(): void
    {
        // Act
        $response = $this->postJson('/login/store', [
            'email' => 'nobody@example.test',
            'password' => 'wrong-password',
        ]);

        // Assert
        $response->assertUnauthorized()
            ->assertJsonPath('message', __('Invalid Email or Password!'));
    }

    public function test_tenant_login_is_blocked_with_403(): void
    {
        // Arrange
        $tenant = $this->createTenant();

        // Act
        $response = $this->postJson('/login/store', [
            'email' => $tenant->email,
            'password' => 'password',
        ]);

        // Assert
        $response->assertForbidden()
            ->assertJsonPath('message', __('You cannot log in as a tenant.'));

        $this->assertGuest();
    }

    public function test_login_without_email_returns_422(): void
    {
        // Act
        $response = $this->postJson('/login/store', [
            'password' => 'password',
        ]);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_logout_redirects_to_login(): void
    {
        // Arrange
        $user = $this->createLandlord();

        // Act
        $response = $this->actingAs($user)->get('/logout');

        // Assert
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_login_view_renders(): void
    {
        // Act
        $response = $this->get('/login');

        // Assert
        $response->assertOk()->assertViewIs('auth.login');
    }
}
