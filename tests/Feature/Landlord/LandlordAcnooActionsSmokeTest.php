<?php

namespace Tests\Feature\Landlord;

use App\Http\Controllers\Landlord\AcnooAmenityController;
use App\Http\Controllers\Landlord\PropertyController;
use App\Models\Amenity;
use App\Models\PlanSubscribe;
use App\Models\Property;
use App\Models\PropertyDetail;
use App\Models\Rent;
use App\Models\User;
use App\Traits\HasAcnooActions;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use ReflectionClass;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * H1–H4 del spec-trait-acnoo-fase0: comportamiento observable de los endpoints
 * de los 5 métodos duplicados en la capa landlord (Amenity como representante).
 * H3 y H4 son caracterizaciones: documentan bugs conocidos que se espera
 * rompan en Fase 2 (Seg-5) y Fase 3.6 (Seg-15) respectivamente.
 *
 * Escritos ANTES de la adopción del trait (PR 1 del stack apilado, D8 del
 * spec): en este punto fijan el comportamiento implementado por las copias
 * locales. Tras el PR 3 (adopción Landlord) los mismos endpoints son servidos
 * por HasAcnooActions y estos tests deben seguir en verde — esa continuidad es
 * la prueba de que el refactor preserva el comportamiento.
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

    public function test_landlord_delete_all_cleans_media_after_delete(): void
    {
        // Arrange
        $landlord = $this->landlordWithSubscription();
        $disk = config('filesystems.default');
        Storage::fake($disk);

        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'cover_image' => ['properties/201/cover.jpg'],
        ]);
        PropertyDetail::factory()->create([
            'property_id' => $property->id,
            'floorplan_image' => 'properties/202/floorplan.jpg',
        ]);
        Storage::disk($disk)->put('properties/201/cover.jpg', 'img');
        Storage::disk($disk)->put('properties/202/floorplan.jpg', 'img');

        // Act
        $response = $this->actingAs($landlord)->postJson('/landlord/properties/delete-all', [
            'ids' => [$property->id],
        ]);

        // Assert
        $response->assertOk();
        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
        Storage::disk($disk)->assertMissing('properties/201/cover.jpg');
        Storage::disk($disk)->assertMissing('properties/202/floorplan.jpg');
    }

    public function test_landlord_delete_all_preserves_media_when_rented(): void
    {
        // Arrange
        $landlord = $this->landlordWithSubscription();
        $tenant = $this->createTenant();
        $disk = config('filesystems.default');
        Storage::fake($disk);

        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'cover_image' => ['properties/301/cover.jpg'],
        ]);
        PropertyDetail::factory()->create([
            'property_id' => $property->id,
            'floorplan_image' => 'properties/302/floorplan.jpg',
        ]);
        Storage::disk($disk)->put('properties/301/cover.jpg', 'img');
        Storage::disk($disk)->put('properties/302/floorplan.jpg', 'img');

        Rent::create([
            'landlord_id' => $landlord->id,
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'monthly_rent' => 100,
            'total_amount' => 100,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 1,
        ]);

        // Act
        $response = $this->actingAs($landlord)->postJson('/landlord/properties/delete-all', [
            'ids' => [$property->id],
        ]);

        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseHas('properties', ['id' => $property->id]);
        Storage::disk($disk)->assertExists('properties/301/cover.jpg');
        Storage::disk($disk)->assertExists('properties/302/floorplan.jpg');
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

    public function test_landlord_properties_filter_does_not_delete(): void
    {
        // H5: tras el fix de Seg-15, filtrar no borra.
        // Arrange
        $landlord = $this->landlordWithSubscription();
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        PropertyDetail::factory()->create(['property_id' => $property->id]);

        // Act
        $response = $this->actingAs($landlord)->postJson('/landlord/properties/filter', [
            'search' => '',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        // Assert — el filter no borra
        $response->assertOk();
        $this->assertDatabaseHas('properties', ['id' => $property->id]);
    }

    public function test_landlord_property_route_names_map_to_correct_methods(): void
    {
        // H8: regresión de nombres de ruta.
        $filterRoute = app('router')->getRoutes()->getByName('landlord.properties.filter');
        $deleteAllRoute = app('router')->getRoutes()->getByName('landlord.properties.delete-all');

        $this->assertNotNull($filterRoute);
        $this->assertNotNull($deleteAllRoute);
        $this->assertStringContainsString('acnooFilter', $filterRoute->getActionName());
        $this->assertStringContainsString('deleteAll', $deleteAllRoute->getActionName());
        $this->assertStringContainsString('properties/filter', $filterRoute->uri());
        $this->assertStringContainsString('properties/delete-all', $deleteAllRoute->uri());
    }

    public function test_routed_actions_are_provided_by_the_trait(): void
    {
        // Arrange — verificación estructural (respuesta al review del PR 1):
        // tras la adopción Landlord, los métodos enrutados salen del trait y
        // el override documentado (§12) sigue definido localmente.
        $fromTrait = [
            AcnooAmenityController::class => ['acnooFilter', 'deleteAll', 'status'],
        ];

        // Act & Assert
        foreach ($fromTrait as $controller => $actions) {
            $this->assertContains(HasAcnooActions::class, class_uses_recursive($controller));

            $file = file_get_contents((new ReflectionClass($controller))->getFileName());
            foreach ($actions as $action) {
                $this->assertStringNotContainsString(
                    "function {$action}(",
                    $file,
                    "{$controller} debe servir {$action} desde HasAcnooActions, no como copia local."
                );
            }
        }

        $propertyController = file_get_contents((new ReflectionClass(PropertyController::class))->getFileName());
        $this->assertStringContainsString(
            'function deleteAll(',
            $propertyController,
            'Landlord\PropertyController::deleteAll es un override documentado (§12): debe seguir local.'
        );
    }
}
