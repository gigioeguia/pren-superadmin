<?php

namespace Tests\Feature\Idor;

use App\Models\PlanSubscribe;
use App\Models\Property;
use App\Models\PropertyDetail;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class PropertyOwnershipSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->landlordA = $this->createLandlord();
        $this->landlordB = $this->createLandlord();
        $this->landlordC = $this->createLandlord();
        $this->tenantA = $this->createTenant();
        $this->admin = $this->createAdmin();
        $this->superadmin = $this->createSuperadmin();
    }

    private function createProperty(int $landlordId): Property
    {
        $property = Property::factory()->create(['landlord_id' => $landlordId]);
        PropertyDetail::factory()->create(['property_id' => $property->id]);

        return $property;
    }

    private function payload(Property $property): array
    {
        return [
            'category_id' => $property->category_id,
            'full_name' => 'Test Owner',
            'phone' => ['1234567890'],
        ];
    }

    public function test_f25_api_superadmin_does_not_steal_ownership_on_update(): void
    {
        $property = $this->createProperty($this->landlordB->id);
        Sanctum::actingAs($this->superadmin, ['*']);

        $response = $this->putJson('/api/v1/landlord/properties/'.$property->id, $this->payload($property));

        $response->assertOk();
        $this->assertSame($this->landlordB->id, $property->fresh()->landlord_id);
    }

    public function test_f26_api_landlord_updates_own_property_without_changing_owner(): void
    {
        $property = $this->createProperty($this->landlordA->id);
        Sanctum::actingAs($this->landlordA, ['*']);

        $response = $this->putJson('/api/v1/landlord/properties/'.$property->id, $this->payload($property));

        $response->assertOk();
        $this->assertSame($this->landlordA->id, $property->fresh()->landlord_id);
    }

    public function test_f27_web_landlord_updates_own_property_without_changing_owner(): void
    {
        $property = $this->createProperty($this->landlordA->id);
        PlanSubscribe::factory()->create(['user_id' => $this->landlordA->id]);

        $response = $this->actingAs($this->landlordA)->putJson('/landlord/properties/'.$property->id, $this->payload($property));

        $response->assertOk();
        $this->assertSame($this->landlordA->id, $property->fresh()->landlord_id);
    }

    public function test_f28_admin_reassigns_property_to_another_landlord(): void
    {
        $property = $this->createProperty($this->landlordB->id);

        $payload = $this->payload($property);
        $payload['landlord_id'] = $this->landlordC->id;

        $response = $this->actingAs($this->admin)->putJson('/admin/properties/'.$property->id, $payload);

        $response->assertOk();
        $this->assertSame($this->landlordC->id, $property->fresh()->landlord_id);
    }

    public function test_f29_admin_cannot_reassign_to_non_landlord_user(): void
    {
        $property = $this->createProperty($this->landlordB->id);

        $payload = $this->payload($property);
        $payload['landlord_id'] = $this->tenantA->id;

        $response = $this->actingAs($this->admin)->putJson('/admin/properties/'.$property->id, $payload);

        $response->assertUnprocessable();
        $this->assertSame($this->landlordB->id, $property->fresh()->landlord_id);
    }

    public function test_f30_api_ignores_landlord_id_sent_in_payload(): void
    {
        $property = $this->createProperty($this->landlordA->id);
        Sanctum::actingAs($this->landlordA, ['*']);

        $payload = $this->payload($property);
        $payload['landlord_id'] = $this->landlordB->id;

        $response = $this->putJson('/api/v1/landlord/properties/'.$property->id, $payload);

        $response->assertOk();
        $this->assertSame($this->landlordA->id, $property->fresh()->landlord_id);
    }
}
