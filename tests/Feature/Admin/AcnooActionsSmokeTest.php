<?php

namespace Tests\Feature\Admin;

use App\Models\Amenity;
use App\Models\Property;
use App\Models\PropertyDetail;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * G1–G8 del spec-trait-acnoo-fase0: comportamiento observable de los endpoints
 * de los 5 métodos duplicados, sobre controladores representativos (Amenity =
 * camino simple, Property = camino row-by-row con media).
 *
 * Escritos ANTES de la adopción del trait (PR 1 del stack apilado, D8 del
 * spec): en este punto fijan el comportamiento implementado por las copias
 * locales de los controladores. Tras los PR 2/3 (adopción Admin/Landlord) los
 * mismos endpoints son servidos por HasAcnooActions y estos tests deben seguir
 * en verde — esa continuidad es la prueba de que el refactor preserva el
 * comportamiento. La prueba estructural de que los métodos enrutados provienen
 * del trait se añade junto con la adopción.
 *
 * @group smoke
 */
class AcnooActionsSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles/permisos Spatie en la DB de testing (patrón de la suite smoke)
        $this->seed(PermissionSeeder::class);
    }

    public function test_filter_ajax_returns_rendered_rows(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        $landlord = $this->createLandlord();
        Amenity::create(['name' => 'Swimming Pool', 'landlord_id' => $landlord->id, 'status' => 1]);

        // Act
        $response = $this->actingAs($admin)->postJson('/admin/amenities/filter', [
            'search' => 'Swimming',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        // Assert
        $response->assertOk()->assertJsonStructure(['data']);
        $this->assertStringContainsString('Swimming Pool', $response->json('data'));
    }

    public function test_filter_without_ajax_redirects_back(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();

        // Act
        $response = $this->actingAs($admin)->post('/admin/amenities/filter', ['search' => 'x']);

        // Assert
        $response->assertRedirect();
    }

    public function test_status_updates_model_and_returns_current_message(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        $landlord = $this->createLandlord();
        $amenity = Amenity::create(['name' => 'Gym', 'landlord_id' => $landlord->id, 'status' => 0]);

        // Act
        $response = $this->actingAs($admin)->postJson("/admin/amenities/status/{$amenity->id}", ['status' => 1]);

        // Assert — el mensaje roto 'Amenity' se preserva tal cual (spec §5)
        $response->assertOk()->assertJson(['message' => 'Amenity']);
        $this->assertDatabaseHas('amenities', ['id' => $amenity->id, 'status' => 1]);
    }

    public function test_status_with_unknown_id_returns_404(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();

        // Act
        $response = $this->actingAs($admin)->postJson('/admin/amenities/status/999999', ['status' => 1]);

        // Assert
        $response->assertNotFound();
    }

    public function test_delete_all_bulk_deletes_selected_rows(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        $landlord = $this->createLandlord();
        $keep = Amenity::create(['name' => 'Keep', 'landlord_id' => $landlord->id, 'status' => 1]);
        $deleteA = Amenity::create(['name' => 'Del A', 'landlord_id' => $landlord->id, 'status' => 1]);
        $deleteB = Amenity::create(['name' => 'Del B', 'landlord_id' => $landlord->id, 'status' => 1]);

        // Act
        $response = $this->actingAs($admin)->postJson('/admin/amenities/delete-all', [
            'ids' => [$deleteA->id, $deleteB->id],
        ]);

        // Assert
        $response->assertOk()->assertJson(['message' => 'Selected Amenity Deleted Successfully']);
        $this->assertDatabaseHas('amenities', ['id' => $keep->id]);
        $this->assertDatabaseMissing('amenities', ['id' => $deleteA->id]);
        $this->assertDatabaseMissing('amenities', ['id' => $deleteB->id]);
    }

    public function test_delete_all_row_by_row_removes_media(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        $disk = config('filesystems.default');
        Storage::fake($disk);

        $property = Property::factory()->create([
            'cover_image' => ['properties/101/cover.jpg'],
        ]);
        PropertyDetail::factory()->create([
            'property_id' => $property->id,
            'floorplan_image' => 'properties/102/floorplan.jpg',
        ]);
        Storage::disk($disk)->put('properties/101/cover.jpg', 'img');
        Storage::disk($disk)->put('properties/102/floorplan.jpg', 'img');

        // Act
        $response = $this->actingAs($admin)->postJson('/admin/properties/delete-all', [
            'ids' => [$property->id],
        ]);

        // Assert — valida el camino row-by-row + hook deletingAcnooRow
        $response->assertOk();
        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
        Storage::disk($disk)->assertMissing('properties/101/cover.jpg');
        Storage::disk($disk)->assertMissing('properties/102/floorplan.jpg');
    }

    public function test_export_excel_downloads_xlsx(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();

        // Act
        $response = $this->actingAs($admin)->get('/admin/properties-excel');

        // Assert
        $response->assertOk();
        $this->assertStringContainsString('properties.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_export_csv_downloads_csv(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();

        // Act
        $response = $this->actingAs($admin)->get('/admin/properties-csv');

        // Assert
        $response->assertOk();
        $this->assertStringContainsString('properties.csv', $response->headers->get('content-disposition'));
    }
}
