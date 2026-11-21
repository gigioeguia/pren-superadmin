<?php

namespace Tests\Feature\Notification;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * A1–A4 del spec-typos-rutas-fase5: contrato HTTP de las rutas de
 * notifications del canal API — URI renombrada read-notifications y
 * la typo'd fuera.
 *
 * @group smoke
 */
class ApiNotificationHttpTest extends TestCase
{
    use CreatesUsers, DatabaseTransactions;

    public function test_a1_api_notifications_index_returns_data(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        Sanctum::actingAs($landlord, ['*']);

        // Act
        $response = $this->getJson('/api/v1/get-notifications');

        // Assert
        $response->assertOk();
        $response->assertJson(['message' => 'Data fetched successfully']);
        $response->assertJsonStructure(['data']);
    }

    public function test_a2_read_notifications_marks_all_read(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        $landlord->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test-notification',
            'data' => ['message' => 'unread'],
        ]);
        Sanctum::actingAs($landlord, ['*']);

        // Act
        $response = $this->getJson('/api/v1/read-notifications');

        // Assert
        $response->assertOk();
        $response->assertJson(['message' => 'Mark all read successfully']);
        $this->assertSame(0, $landlord->unreadNotifications()->count());
    }

    public function test_a3_typo_uri_is_gone(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        Sanctum::actingAs($landlord, ['*']);

        // Act
        $response = $this->getJson('/api/v1/read-nitifications');

        // Assert
        $response->assertNotFound();
    }

    public function test_a4_read_notifications_requires_auth(): void
    {
        // Act
        $response = $this->getJson('/api/v1/read-notifications');

        // Assert
        $response->assertUnauthorized();
    }
}
