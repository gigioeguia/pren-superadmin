<?php

namespace Tests\Unit;

use App\Enums\Channel;
use App\Exceptions\PlanLimitExceededException;
use App\Exceptions\PropertyRentedException;
use App\Models\Category;
use App\Models\Facility;
use App\Models\Amenity;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\Property;
use App\Models\PropertyDetail;
use App\Models\Rent;
use App\Models\Unit;
use App\Services\PropertyService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class PropertyServiceTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected PropertyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->service = app(PropertyService::class);
    }

    public function test_u1_list_admin_returns_all(): void
    {
        $landlord = $this->createLandlord();
        Property::factory()->count(3)->create(['landlord_id' => $landlord->id]);
        $admin = $this->createAdmin();

        $result = $this->service->listForChannel(Channel::Admin, $admin, []);

        $this->assertCount(3, $result->items());
    }

    public function test_u2_list_landlord_filters_by_actor(): void
    {
        $a = $this->createLandlord();
        $b = $this->createLandlord();
        Property::factory()->count(2)->create(['landlord_id' => $a->id]);
        Property::factory()->create(['landlord_id' => $b->id]);

        $result = $this->service->listForChannel(Channel::Landlord, $a, []);

        $this->assertCount(2, $result->items());
    }

    public function test_u3_list_api_tenant_only_published(): void
    {
        $landlord = $this->createLandlord();
        Property::factory()->create(['landlord_id' => $landlord->id, 'status' => 0]);
        Property::factory()->create(['landlord_id' => $landlord->id, 'status' => 1]);
        $tenant = $this->createTenant();

        $result = $this->service->listForChannel(Channel::Api, $tenant, []);

        $this->assertCount(1, $result->items());
    }

    public function test_u4_store_admin_status_one_and_landlord_from_data(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord();

        $property = $this->service->store([
            'category_id' => Category::factory()->create()->id,
            'full_name' => 'Owner',
            'phone' => ['123'],
            'landlord_id' => $landlord->id,
        ], Channel::Admin, $admin, $landlord->id);

        $this->assertSame($landlord->id, $property->landlord_id);
        $this->assertSame('1', (string) $property->status);
    }

    public function test_u5_store_landlord_status_zero_and_owner_actor(): void
    {
        $landlord = $this->createLandlord();

        $property = $this->service->store([
            'category_id' => Category::factory()->create()->id,
            'full_name' => 'Owner',
            'phone' => ['123'],
        ], Channel::Landlord, $landlord);

        $this->assertSame($landlord->id, $property->landlord_id);
        $this->assertSame('0', (string) $property->status);
    }

    public function test_u6_store_landlord_plan_limit_throws(): void
    {
        $landlord = $this->createLandlord();
        $plan = Plan::factory()->create(['number_of_property' => 0]);
        PlanSubscribe::factory()->create(['user_id' => $landlord->id, 'plan_id' => $plan->id]);

        $this->expectException(PlanLimitExceededException::class);

        $this->service->store([
            'category_id' => Category::factory()->create()->id,
            'full_name' => 'Owner',
            'phone' => ['123'],
        ], Channel::Landlord, $landlord);
    }

    public function test_u7_store_api_same_as_landlord(): void
    {
        $landlord = $this->createLandlord();

        $property = $this->service->store([
            'category_id' => Category::factory()->create()->id,
            'full_name' => 'Owner',
            'phone' => ['123'],
        ], Channel::Api, $landlord);

        $this->assertSame($landlord->id, $property->landlord_id);
        $this->assertSame('0', (string) $property->status);
    }

    public function test_u8_update_landlord_does_not_change_owner(): void
    {
        $landlord = $this->createLandlord();
        $other = $this->createLandlord();
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        PropertyDetail::factory()->create(['property_id' => $property->id]);

        $updated = $this->service->update($property, [
            'category_id' => $property->category_id,
            'full_name' => 'Owner',
            'phone' => ['123'],
            'landlord_id' => $other->id,
        ], Channel::Landlord, $landlord);

        $this->assertSame($landlord->id, $updated->landlord_id);
    }

    public function test_u9_update_admin_can_change_owner(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord();
        $other = $this->createLandlord();
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        PropertyDetail::factory()->create(['property_id' => $property->id]);

        $updated = $this->service->update($property, [
            'category_id' => $property->category_id,
            'full_name' => 'Owner',
            'phone' => ['123'],
            'landlord_id' => $other->id,
        ], Channel::Admin, $admin);

        $this->assertSame($other->id, $updated->landlord_id);
    }

    public function test_u10_delete_landlord_throws_if_rented(): void
    {
        $landlord = $this->createLandlord();
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        PropertyDetail::factory()->create(['property_id' => $property->id]);
        Rent::create([
            'property_id' => $property->id,
            'landlord_id' => $landlord->id,
            'tenant_id' => $this->createTenant()->id,
            'monthly_rent' => 1000,
            'total_amount' => 1000,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 'active',
        ]);

        $this->expectException(PropertyRentedException::class);

        $this->service->delete($property, Channel::Landlord);
    }

    public function test_u11_delete_cleans_media_and_row(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord();
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        PropertyDetail::factory()->create(['property_id' => $property->id]);

        $this->service->delete($property, Channel::Admin);

        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
    }

    public function test_u12_delete_many_respects_ownership(): void
    {
        $a = $this->createLandlord();
        $b = $this->createLandlord();
        $pa = Property::factory()->create(['landlord_id' => $a->id]);
        PropertyDetail::factory()->create(['property_id' => $pa->id]);
        $pb = Property::factory()->create(['landlord_id' => $b->id]);
        PropertyDetail::factory()->create(['property_id' => $pb->id]);

        $count = $this->service->deleteMany([$pa->id, $pb->id], Channel::Landlord, $a);

        $this->assertSame(1, $count);
        $this->assertDatabaseMissing('properties', ['id' => $pa->id]);
        $this->assertDatabaseHas('properties', ['id' => $pb->id]);
    }

    public function test_u13_delete_many_throws_if_rented(): void
    {
        $landlord = $this->createLandlord();
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        PropertyDetail::factory()->create(['property_id' => $property->id]);
        Rent::create([
            'property_id' => $property->id,
            'landlord_id' => $landlord->id,
            'tenant_id' => $this->createTenant()->id,
            'monthly_rent' => 1000,
            'total_amount' => 1000,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 'active',
        ]);

        $this->expectException(PropertyRentedException::class);

        $this->service->deleteMany([$property->id], Channel::Landlord, $landlord);
    }

    public function test_u14_approve_sets_status_one(): void
    {
        $property = Property::factory()->create(['status' => '0']);
        PropertyDetail::factory()->create(['property_id' => $property->id]);

        $result = $this->service->approve($property);

        $this->assertSame('1', (string) $result->status);
    }

    public function test_u15_reject_sets_status_three_and_reason(): void
    {
        $property = Property::factory()->create(['status' => '1']);
        PropertyDetail::factory()->create(['property_id' => $property->id]);

        $result = $this->service->reject($property, 'motivo');

        $this->assertSame('3', (string) $result->status);
        $this->assertSame('motivo', $result->meta['reason'] ?? null);
    }

    public function test_u16_update_status_api(): void
    {
        $property = Property::factory()->create(['status' => '0']);
        PropertyDetail::factory()->create(['property_id' => $property->id]);

        $result = $this->service->updateStatus($property, true);

        $this->assertSame('1', (string) $result->status);
    }

    public function test_u17_decorate_for_show_sets_unit_names_for_unit_category(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        $category = new Category(['name' => 'Condominium', 'status' => 1]);
        $category->id = 6; // la rama unit del decorado exige category_id == 6
        $category->save();
        $property = Property::factory()->create(['landlord_id' => $landlord->id, 'category_id' => $category->id]);
        $facility = Facility::create(['name' => 'Lift', 'landlord_id' => $landlord->id, 'status' => 1]);
        $amenity = Amenity::create(['name' => 'Wifi', 'landlord_id' => $landlord->id, 'status' => 1]);
        Unit::create([
            'property_id' => $property->id,
            'unit_name' => 'A-1',
            'facilities' => [$facility->id],
            'amenities' => [$amenity->id],
        ]);

        // Act
        $decorated = $this->service->decorateForShow($property->load('units'));

        // Assert
        $unit = $decorated->units->first();
        $this->assertSame([$facility->id => 'Lift'], $unit->facility_names->toArray());
        $this->assertSame([$amenity->id => 'Wifi'], $unit->amenity_names->toArray());
    }

    public function test_u18_decorate_for_show_sets_property_names_for_single_category(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        $category = new Category(['name' => 'Apartment', 'status' => 1]);
        $category->id = 1; // categoría simple (distinta de la rama unit 6)
        $category->save();
        $property = Property::factory()->create(['landlord_id' => $landlord->id, 'category_id' => $category->id]);
        $facility = Facility::create(['name' => 'Pool', 'landlord_id' => $landlord->id, 'status' => 1]);
        $amenity = Amenity::create(['name' => 'Gym', 'landlord_id' => $landlord->id, 'status' => 1]);
        PropertyDetail::factory()->create([
            'property_id' => $property->id,
            'facilities' => [$facility->id],
            'amenities' => [$amenity->id],
        ]);

        // Act
        $decorated = $this->service->decorateForShow($property->load('propertyDetail'));

        // Assert
        $this->assertSame([$facility->id => 'Pool'], $decorated->facilities->toArray());
        $this->assertSame([$amenity->id => 'Gym'], $decorated->amenities->toArray());
    }
}
