<?php

namespace Tests\Feature\Landlord;

use App\Models\PlanSubscribe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class LandlordGuardSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_anonymous_user_is_redirected_to_login(): void
    {
        // Act
        $response = $this->get('/landlord');

        // Assert
        $response->assertRedirect(route('login'));
    }

    public function test_landlord_with_active_subscription_renders_dashboard(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        PlanSubscribe::factory()->create(['user_id' => $landlord->id]);

        // Act
        $response = $this->actingAs($landlord)->get('/landlord');

        // Assert
        $response->assertOk();
    }

    public function test_tenant_is_redirected_to_login(): void
    {
        // Arrange
        $tenant = $this->createTenant();

        // Act
        $response = $this->actingAs($tenant)->get('/landlord');

        // Assert
        $response->assertRedirect(route('login'));
    }

    public function test_landlord_with_expired_subscription_is_redirected(): void
    {
        // Arrange
        $landlord = $this->createLandlord(['will_expire' => now()->subDay()]);
        PlanSubscribe::factory()->create(['user_id' => $landlord->id]);

        // Act
        $response = $this->actingAs($landlord)->get('/landlord');

        // Assert
        $response->assertRedirect(route('landlord.subscriptions.index'));
    }
}
