<?php

namespace Tests\Feature\Admin;

use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * G6–G7 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11).
 *
 * G7 cerraba Seg-28 (clave `plan_subscribe_id` crafted en el store admin
 * de landlords, nunca posteda por el form): nació como caracterización y
 * se flipeó con el fix del mismo PR. G6 es regresión del flujo legítimo.
 */
class LandlordStoreSubscriptionTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    private function adminWithLandlordsCreate(): User
    {
        $admin = $this->createAdmin();
        $admin->givePermissionTo(
            Permission::firstOrCreate(['name' => 'landlords-create', 'guard_name' => 'web'])
        );

        return $admin;
    }

    public function test_g6_landlord_store_with_free_plan_assigns_subscription_trio(): void
    {
        // Arrange
        $admin = $this->adminWithLandlordsCreate();
        Plan::factory()->free()->create();

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson('/admin/landlords', [
                'name' => 'Legit Landlord',
                'email' => 'legit-landlord@example.test',
                'password' => 'Password123',
                'phone' => ['mobile_code' => '+1', 'mobile_no' => '555'],
            ]);

        // Assert — trío persistido vía forceFill (no fillable)
        $response->assertOk();

        $user = User::where('email', 'legit-landlord@example.test')->first();
        $this->assertNotNull($user->plan_subscribe_id);
        $this->assertNotNull($user->subscriptionDate);
        $this->assertTrue(Carbon::parse($user->will_expire)->isFuture());
    }

    public function test_g7_landlord_store_ignores_crafted_plan_subscribe_id(): void
    {
        // Arrange — sin plan free: el bloque free-plan no asigna nada
        $admin = $this->adminWithLandlordsCreate();
        $otherLandlord = $this->createLandlord();
        $foreignSub = PlanSubscribe::factory()->create(['user_id' => $otherLandlord->id]);

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson('/admin/landlords', [
                'name' => 'Crafted Landlord',
                'email' => 'crafted@example.test',
                'password' => 'Password123',
                'phone' => ['mobile_code' => '+1', 'mobile_no' => '555'],
                'plan_subscribe_id' => $foreignSub->id,
            ]);

        // Assert — Seg-28 cerrado: clave eliminada del array de create
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'crafted@example.test',
            'plan_subscribe_id' => null,
        ]);
    }
}
