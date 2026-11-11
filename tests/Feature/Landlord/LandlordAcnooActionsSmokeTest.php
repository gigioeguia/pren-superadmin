<?php

namespace Tests\Feature\Landlord;

use App\Models\Amenity;
use App\Models\PlanSubscribe;
use App\Models\Property;
use App\Models\PropertyDetail;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * H1–H4 del spec-trait-acnoo-fase0: comportamiento observable de los métodos
 * de HasAcnooActions en la capa landlord (Amenity como representante).
 * H3 y H4 son caracterizaciones: documentan bugs conocidos que se espera
 * rompan en Fase 2 (Seg-5) y Fase 3.6 (Seg-15) respectivamente.
 *
 * @group smoke
 */
class LandlordAcnooActionsSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles/permisos Spatie en la DB de testing (patrón de la suite smoke)
        $this->seed(PermissionSeeder::class);
    }

    private function landlordWithSubscription(): User
    {
        $landlord = $this->createLandlord();
        PlanSubscribe::factory()->create(['user_id' => $landlord->id]);

        return $landlord;
    }

    public function test_filter_ajax_scopes_to_own_amenities(): void
    {
        // Arrange
        $landlordA = $this->landlordWithSubscription();
        $landlordB = $this->createLandlord();
        Amenity::create(['name' => 'Own Gym', 'landlord_id' => $landlordA->id, 'status' => 1]);
        Amenity::create(['name' => 'Own Pool', 'landlord_id' => $landlordA->id, 'status' => 1]);
        Amenity::create(['name' => 'Foreign Sauna', 'landlord_id' => $landlordB->id, 'status' => 1]);

        // Act
        $response = $this->actingAs($landlordA)->postJson('/landlord/amenities/filter', [
            'search' => '',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        // Assert — el scoping landlord_id actual se preserva vía acnooQuery
        $response->assertOk();
        $this->assertStringContainsString('Own Gym', $response->json('data'));
        $this->assertStringContainsString('Own Pool', $response->json('data'));
        $this->assertStringNotContainsString('Foreign Sauna', $response->json('data'));
    }

    public function test_delete_all_removes_own_amenities(): void
    {
        // Arrange
        $landlord = $this->landlordWithSubscription();
        $keep = Amenity::create(['name' => 'Keep', 'landlord_id' => $landlord->id, 'status' => 1]);
        $deleteA = Amenity::create(['name' => 'Del A', 'landlord_id' => $landlord->id, 'status' => 1]);
        $deleteB = Amenity::create(['name' => 'Del B', 'landlord_id' => $landlord->id, 'status' => 1]);

        // Act
        $response = $this->actingAs($landlord)->postJson('/landlord/amenities/delete-all', [
            'ids' => [$deleteA->id, $deleteB->id],
        ]);

        // Assert
        $response->assertOk()->assertJson(['message' => 'Selected Amenity Deleted Successfully']);
        $this->assertDatabaseHas('amenities', ['id' => $keep->id]);
        $this->assertDatabaseMissing('amenities', ['id' => $deleteA->id]);
        $this->assertDatabaseMissing('amenities', ['id' => $deleteB->id]);
    }

    /**
     * @group characterization
     */
    public function test_delete_all_deletes_foreign_amenities_without_ownership_check(): void
    {
        // Expected to fail after Phase 2 (Seg-5 fix) — update to secure behavior
        // when it fails: deleteAll no debe poder borrar filas de otros landlords
        // (ownership scoping/Policies aplicado sobre el trait).
        // Arrange
        $landlordA = $this->landlordWithSubscription();
        $landlordB = $this->createLandlord();
        $foreign = Amenity::create(['name' => 'Foreign', 'landlord_id' => $landlordB->id, 'status' => 1]);

        // Act
        $response = $this->actingAs($landlordA)->postJson('/landlord/amenities/delete-all', [
            'ids' => [$foreign->id],
        ]);

        // Assert — documenta el IDOR actual: la fila ajena se borra
        $response->assertOk();
        $this->assertDatabaseMissing('amenities', ['id' => $foreign->id]);
    }

    /**
     * @group characterization
     */
    public function test_filter_route_runs_delete_all_due_to_crossed_route(): void
    {
        // Expected to fail after Phase 3.6 (Seg-15 fix) — update to secure
        // behavior when it fails: routes/landlord.php:15 cruza
        // properties/filter -> deleteAll, por lo que "filtrar" BORRA. El fix
        // de rutas hará que este POST filtre en lugar de borrar.
        // Arrange
        $landlord = $this->landlordWithSubscription();
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        PropertyDetail::factory()->create(['property_id' => $property->id]);

        // Act
        $response = $this->actingAs($landlord)->postJson('/landlord/properties/filter', [
            'ids' => [$property->id],
        ]);

        // Assert — documenta la ruta cruzada: el filter borra
        $response->assertOk();
        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
    }
}
