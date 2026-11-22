<?php

namespace Tests\Feature\Admin;

use App\Models\PlanSubscribe;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * G6–G7 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11).
 *
 * G7 es la caracterización de Seg-28 (clave `plan_subscribe_id` crafted
 * en el store admin de landlords, nunca posteda por el form): documenta
 * el comportamiento inseguro vigente y se flipea en el mismo PR que
 * aplica el fix.
 */
class LandlordStoreSubscriptionTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    /**
     * @group characterization
     */
    public function test_g7_landlord_store_accepts_crafted_plan_subscribe_id(): void
    {
        // Expected to fail after spec-user-fillable-restrictivo-fase5 (Seg-28 fix).

        // Arrange — sin plan free: el bloque free-plan no sobrescribe la clave
        $admin = $this->createAdmin();
        $admin->givePermissionTo(
            Permission::firstOrCreate(['name' => 'landlords-create', 'guard_name' => 'web'])
        );
        $otherLandlord = $this->createLandlord();
        $foreignSub = PlanSubscribe::factory()->create(['user_id' => $otherLandlord->id]);

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->postJson('/admin/landlords', [
                'name' => 'Crafted Landlord',
                'email' => 'crafted@example.test',
                'password' => 'Password123',
                'phone' => ['mobile_code' => '+1', 'mobile_no' => '555'],
                'plan_subscribe_id' => $foreignSub->id,
            ]);

        // Assert — comportamiento inseguro actual: id ajeno persistido
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'crafted@example.test',
            'plan_subscribe_id' => $foreignSub->id,
        ]);
    }
}
