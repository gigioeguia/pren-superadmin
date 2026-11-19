<?php

namespace Tests\Feature\Demo;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\PlanSubscribe;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class DemoModeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    protected function tearDown(): void
    {
        app()->detectEnvironment(fn () => 'testing');

        parent::tearDown();
    }

    public function test_f1_noop_outside_demo_for_admin(): void
    {
        app()->detectEnvironment(fn () => 'testing');

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->withoutMiddleware(VerifyCsrfToken::class)->deleteJson('/admin/blogs/1');

        $this->assertNotEquals(499, $response->getStatusCode());
    }

    public function test_f2_admin_blocked_in_demo(): void
    {
        app()->detectEnvironment(fn () => 'demo');

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->withoutMiddleware(VerifyCsrfToken::class)->deleteJson('/admin/blogs/1');

        $response->assertStatus(499);
        $response->assertJson(['message' => 'This action is disabled in demo mode.']);
    }

    public function test_f3_landlord_demo_blocked_in_demo(): void
    {
        app()->detectEnvironment(fn () => 'demo');

        $landlord = User::factory()->landlord()->demo()->create();

        $response = $this->actingAs($landlord)->withoutMiddleware(VerifyCsrfToken::class)->putJson('/landlord/properties/1');

        $response->assertStatus(499);
        $response->assertJson(['message' => 'This action is disabled in demo account. Please sign up your own account for full access.']);
    }

    public function test_f4_landlord_real_not_blocked_in_demo(): void
    {
        app()->detectEnvironment(fn () => 'demo');

        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)->withoutMiddleware(VerifyCsrfToken::class)->putJson('/landlord/properties/1');

        $this->assertNotEquals(499, $response->getStatusCode());
    }

    public function test_f5_anonymous_not_blocked_by_demomode(): void
    {
        app()->detectEnvironment(fn () => 'demo');

        $response = $this->withoutMiddleware(VerifyCsrfToken::class)->putJson('/landlord/properties/1');

        $this->assertNotEquals(499, $response->getStatusCode());
    }

    public function test_f6_disabled_routes_intact(): void
    {
        $source = file_get_contents(app_path('Http/Middleware/DemoMode.php'));

        $this->assertStringContainsString('admin.blogs.destroy', $source);
        $this->assertStringContainsString('landlord.properties.update', $source);
    }

    public function test_f7_banner_visible_in_demo_for_demo_landlord(): void
    {
        app()->detectEnvironment(fn () => 'demo');

        $landlord = User::factory()->landlord()->demo()->create();
        PlanSubscribe::factory()->create(['user_id' => $landlord->id]);

        $response = $this->actingAs($landlord)->get('/landlord');

        $response->assertOk();
        $response->assertSee('demo account', false);
    }

    public function test_f8_no_banner_outside_demo(): void
    {
        app()->detectEnvironment(fn () => 'testing');

        $landlord = $this->createLandlord();
        PlanSubscribe::factory()->create(['user_id' => $landlord->id]);

        $response = $this->actingAs($landlord)->get('/landlord');

        $response->assertOk();
        $response->assertDontSee('This is a demo account', false);
    }
}
