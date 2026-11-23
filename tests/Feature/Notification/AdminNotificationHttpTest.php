<?php

namespace Tests\Feature\Notification;

use App\Models\Notification;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * G1–G5 del spec-typos-rutas-fase5: contrato HTTP de las rutas de
 * notifications del canal admin (post-tidy: filter sin segmento
 * redundante ni slash final).
 */
#[Group('smoke')]
class AdminNotificationHttpTest extends TestCase
{
    use CreatesUsers, DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_g1_admin_notifications_index_returns_view(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act
        $response = $this->actingAs($admin)->get('/admin/notifications');

        // Assert
        $response->assertOk();
        $response->assertViewIs('admin.notifications.index');
    }

    public function test_g2_admin_notifications_filter_returns_data(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act
        $response = $this->actingAs($admin)->post('/admin/notifications/filter');

        // Assert
        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }

    public function test_g3_old_admin_filter_uri_is_gone(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act
        $response = $this->actingAs($admin)->post('/admin/notifications/notification/filter');

        // Assert
        $response->assertNotFound();
    }

    public function test_g4_admin_read_all_marks_notifications_read(): void
    {
        // Arrange
        $admin = $this->createAdmin();
        $admin->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test-notification',
            'data' => ['message' => 'unread'],
        ]);

        // Act
        $response = $this->actingAs($admin)->get('/admin/notifications/view/all');

        // Assert
        $response->assertRedirect();
        $this->assertSame(0, $admin->unreadNotifications()->count());
    }

    public function test_g5_admin_view_marks_notification_read_and_redirects(): void
    {
        // Arrange
        $admin = $this->createAdmin();
        $notification = $admin->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test-notification',
            'data' => ['admin_url' => '/admin/plans'],
        ]);

        // Act
        $response = $this->actingAs($admin)->get("/admin/notifications/{$notification->id}");

        // Assert
        $response->assertRedirect('/admin/plans');
        $this->assertNotNull(Notification::find($notification->id)->read_at);
    }
}
