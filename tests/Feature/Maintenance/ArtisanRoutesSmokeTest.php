<?php

namespace Tests\Feature\Maintenance;

use App\Models\Option;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ArtisanRoutesSmokeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @group characterization
     */
    public function test_public_update_route_runs_migrations_and_sets_version(): void
    {
        // Expected to fail after Phase 1 (Seg-1 fix) — GET /update must
        // require auth instead of running Artisan for anonymous visitors.
        // Arrange
        Artisan::shouldReceive('call')->once()->with('migrate')->andReturn(0);
        Artisan::shouldReceive('call')->andReturn(0); // db:seed, cache:clear, ...

        // Act
        $response = $this->get('/update');

        // Assert
        $response->assertRedirect('/');

        // Option value es JSON-cast: '"2.1"'
        $this->assertDatabaseHas('options', [
            'key' => 'version',
            'value' => '"2.1"',
        ]);
    }

    /**
     * @group characterization
     */
    public function test_public_cache_clear_route_clears_cache(): void
    {
        // Expected to fail after Phase 1 (Seg-1 fix) — GET /cache-clear must
        // require auth instead of running Artisan for anonymous visitors.
        // Arrange
        Artisan::shouldReceive('call')->once()->with('cache:clear')->andReturn(0);
        Artisan::shouldReceive('call')->andReturn(0); // config:clear, route:clear, ...

        // Act
        $response = $this->get('/cache-clear');

        // Assert
        $response->assertStatus(302);
    }
}
