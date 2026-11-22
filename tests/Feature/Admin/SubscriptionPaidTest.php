<?php

namespace Tests\Feature\Admin;

use App\Models\Gateway;
use App\Models\PlanSubscribe;
use App\Models\User;
use App\Support\CacheKeys;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * G8–G9 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11).
 *
 * `paid()` escribe el trío de suscripción sobre el user vía
 * `forceFill` (Q14): instancia tipada que dispara UserObserver →
 * invalidación estructural de plan-data-{user_id} (gap §3 P5 de 4.9).
 */
class SubscriptionPaidTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    private function adminWithSubscriptionsUpdate(): User
    {
        $admin = $this->createAdmin();
        $admin->givePermissionTo(
            Permission::firstOrCreate(['name' => 'subscriptions-update', 'guard_name' => 'web'])
        );

        return $admin;
    }

    public function test_g8_subscription_paid_updates_user_trio(): void
    {
        // Arrange
        $admin = $this->adminWithSubscriptionsUpdate();
        $landlord = $this->createLandlord();
        $subscribe = PlanSubscribe::factory()->create([
            'user_id' => $landlord->id,
            'payment_status' => 'unpaid',
            'gateway_id' => Gateway::factory()->create()->id,
        ]);

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->postJson("/admin/subscriptions/paid/{$subscribe->id}", [
                'notes' => 'Manual approval',
            ]);

        // Assert — trío persistido vía forceFill
        $response->assertOk();

        $this->assertDatabaseHas('plan_subscribes', [
            'id' => $subscribe->id,
            'payment_status' => 'paid',
        ]);

        $user = $landlord->fresh();
        $this->assertSame($subscribe->id, $user->plan_subscribe_id);
        $this->assertNotNull($user->subscriptionDate);
        $this->assertTrue(Carbon::parse($user->will_expire)->isFuture());
    }

    public function test_g9_subscription_paid_invalidates_plan_data_cache(): void
    {
        // Arrange — stale plantado en la clave del landlord
        $admin = $this->adminWithSubscriptionsUpdate();
        $landlord = $this->createLandlord();
        $subscribe = PlanSubscribe::factory()->create([
            'user_id' => $landlord->id,
            'payment_status' => 'unpaid',
            'gateway_id' => Gateway::factory()->create()->id,
        ]);
        $key = CacheKeys::planData($landlord->id);
        Cache::put($key, ['stale' => true], 300);
        $this->assertTrue(Cache::has($key));

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->postJson("/admin/subscriptions/paid/{$subscribe->id}", [
                'notes' => 'Manual approval',
            ]);

        // Assert — cierre del gap P5 de 4.9: la clave se invalida
        $response->assertOk();
        $this->assertFalse(Cache::has($key));
    }
}
