<?php

namespace Tests\Feature\Maintenance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class ArtisanRoutesSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_update_route_is_blocked_for_anonymous_visitors(): void
    {
        // Arrange
        Artisan::shouldReceive('call')->never();

        // Act
        $response = $this->get('/update');

        // Assert
        $response->assertRedirect(route('login'));
    }

    public function test_cache_clear_route_is_blocked_for_anonymous_visitors(): void
    {
        // Arrange
        Artisan::shouldReceive('call')->never();

        // Act
        $response = $this->get('/cache-clear');

        // Assert
        $response->assertRedirect(route('login'));
    }

    public function test_update_route_is_blocked_for_non_superadmin_users(): void
    {
        // Arrange
        $admin = $this->createAdmin();
        Artisan::shouldReceive('call')->never();

        // Act
        $response = $this->actingAs($admin)->get('/update');

        // Assert
        $response->assertStatus(403);
    }

    public function test_cache_clear_route_is_blocked_for_non_superadmin_users(): void
    {
        // Arrange
        $admin = $this->createAdmin();
        Artisan::shouldReceive('call')->never();

        // Act
        $response = $this->actingAs($admin)->get('/cache-clear');

        // Assert
        $response->assertStatus(403);
    }

    public function test_update_route_runs_for_superadmin_and_sets_version(): void
    {
        // Arrange
        $superadmin = $this->createSuperadmin();

        Artisan::shouldReceive('call')->once()->with('migrate')->andReturn(0);
        Artisan::shouldReceive('call')->andReturn(0); // db:seed, cache:clear, ...

        // Act
        $response = $this->actingAs($superadmin)->get('/update');

        // Assert
        $response->assertRedirect('/');

        $this->assertDatabaseHas('options', [
            'key' => 'version',
            'value' => '"2.1"',
        ]);
    }

    public function test_cache_clear_route_runs_for_superadmin(): void
    {
        // Arrange
        $superadmin = $this->createSuperadmin();

        Artisan::shouldReceive('call')->once()->with('cache:clear')->andReturn(0);
        Artisan::shouldReceive('call')->andReturn(0); // config:clear, route:clear, ...

        // Act
        $response = $this->actingAs($superadmin)->get('/cache-clear');

        // Assert
        $response->assertStatus(302);
    }
}
