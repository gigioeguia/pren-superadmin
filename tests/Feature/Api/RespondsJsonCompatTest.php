<?php

namespace Tests\Feature\Api;

use App\Models\Amenity;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * U4 del spec-controladores-residuales-fase5: congela el contrato de los
 * endpoints que consumen el array $message legado a través del trait
 * RespondsJson. Tras mover los 15 helpers + $message del Controller base
 * al trait, estos mensajes deben seguir siendo byte-idénticos.
 *
 * @group smoke
 */
class RespondsJsonCompatTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles/permisos Spatie en la DB de testing (patrón de la suite smoke)
        $this->seed(PermissionSeeder::class);
    }

    public function test_amenity_endpoint_keeps_legacy_message_contract(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        Amenity::create(['name' => 'Pool', 'landlord_id' => $landlord->id, 'status' => 1]);

        // Act
        $response = $this->getJson('/api/v1/amenity');

        // Assert — $message[0] se pasa como array completo (shape anidado legado)
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => ['message' => 'Response Successfully!', 'alert-type' => 'success'],
            ])
            ->assertJsonStructure(['data' => ['amenities']]);
    }

    public function test_facility_endpoint_empty_keeps_legacy_not_found_contract(): void
    {
        // Act
        $response = $this->getJson('/api/v1/facility');

        // Assert — $message[6] ídem: array completo dentro del campo message
        $response->assertStatus(404)
            ->assertJson([
                'error' => true,
                'message' => ['message' => 'Sorry!! Data Not Found!', 'alert-type' => 'error'],
            ]);
    }
}
