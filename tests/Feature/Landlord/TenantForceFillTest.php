<?php

namespace Tests\Feature\Landlord;

use App\Models\PlanSubscribe;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * H1–H2 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11):
 * `landlord_id` (tenancy) y el par OTP/verificación persisten vía
 * forceFill tras salir de `$fillable`.
 */
class TenantForceFillTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    /**
     * El landlord necesita plan vigente para pasar ExpiredMiddleware.
     */
    private function landlordWithPlan(): User
    {
        $landlord = $this->createLandlord();
        $subscribe = PlanSubscribe::factory()->create(['user_id' => $landlord->id]);
        $landlord->forceFill([
            'plan_subscribe_id' => $subscribe->id,
            'will_expire' => now()->addMonth(),
        ])->save();

        return $landlord;
    }

    public function test_h1_landlord_creates_tenant_with_landlord_id(): void
    {
        // Arrange
        $landlord = $this->landlordWithPlan();

        // Act
        $response = $this->actingAs($landlord)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson('/landlord/tenants', [
                'name' => 'Tenant User',
                'email' => 'tenant-created@example.test',
                'password' => 'Password123',
            ]);

        // Assert — columna de tenacy persistida vía forceFill
        $response->assertOk();

        $tenant = User::where('email', 'tenant-created@example.test')->first();
        $this->assertSame($landlord->id, $tenant->landlord_id);
        $this->assertSame('tenant', $tenant->role);
        $this->assertSame(1, $tenant->is_setupped);
    }

    public function test_h2_api_tenant_store_persists_otp(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        Sanctum::actingAs($landlord);
        Mail::fake();

        // Act
        $response = $this->postJson('/api/v1/landlord/tenant', [
            'name' => 'Api Tenant',
            'email' => 'api-tenant@example.test',
            'password' => 'Password123',
        ]);

        // Assert — OTP + TTL persistidos vía forceFill (canal Api)
        $response->assertOk();

        $tenant = User::where('email', 'api-tenant@example.test')->first();
        $this->assertSame($landlord->id, $tenant->landlord_id);
        $this->assertSame(6, strlen((string) $tenant->remember_token));
        $this->assertTrue($tenant->email_verified_at->isFuture());
        $this->assertSame(0, $tenant->is_setupped);
    }
}
