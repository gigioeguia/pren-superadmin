<?php

namespace Tests\Feature\Notification;

use App\Models\Notification;
use App\Models\PlanSubscribe;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * H1–H5 del spec-typos-rutas-fase5: contrato HTTP de las rutas de
 * notifications del canal landlord (post-tidy: filter sin segmento
 * redundante ni slash final).
 */
#[Group('smoke')]
class LandlordNotificationHttpTest extends TestCase
{
    use CreatesUsers, DatabaseTransactions;

    /**
     * Landlord con plan activo: ExpiredMiddleware redirige las rutas
     * notificadas si el plan falta o expiró — will_expire vive en users
     * (plan_data() lo mergea sobre PlanSubscribe, l.122).
     */
    private function landlordWithActivePlan(): User
    {
        $landlord = $this->createLandlord(['will_expire' => now()->addDays(30)]);
        PlanSubscribe::factory()->create(['user_id' => $landlord->id]);

        return $landlord;
    }

    public function test_h1_landlord_notifications_index_returns_view(): void
    {
        // Arrange
        $landlord = $this->landlordWithActivePlan();

        // Act
        $response = $this->actingAs($landlord)->get('/landlord/notifications');

        // Assert
        $response->assertOk();
        $response->assertViewIs('landlord.notifications.index');
    }

    public function test_h2_landlord_notifications_filter_returns_data(): void
    {
        // Arrange
        $landlord = $this->landlordWithActivePlan();

        // Act
        $response = $this->actingAs($landlord)->post('/landlord/notifications/filter');

        // Assert
        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }

    public function test_h3_old_landlord_filter_uri_is_gone(): void
    {
        // Arrange
        $landlord = $this->landlordWithActivePlan();

        // Act
        $response = $this->actingAs($landlord)->post('/landlord/notifications/notification/filter');

        // Assert
        $response->assertNotFound();
    }

    public function test_h4_landlord_read_all_marks_notifications_read(): void
    {
        // Arrange
        $landlord = $this->landlordWithActivePlan();
        $landlord->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test-notification',
            'data' => ['message' => 'unread'],
        ]);

        // Act
        $response = $this->actingAs($landlord)->get('/landlord/notifications/view/all');

        // Assert
        $response->assertRedirect();
        $this->assertSame(0, $landlord->unreadNotifications()->count());
    }

    public function test_h5_landlord_view_marks_notification_read_and_redirects(): void
    {
        // Arrange
        $landlord = $this->landlordWithActivePlan();
        $notification = $landlord->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test-notification',
            'data' => ['landlord_url' => '/landlord/properties'],
        ]);

        // Act
        $response = $this->actingAs($landlord)->get("/landlord/notifications/{$notification->id}");

        // Assert
        $response->assertRedirect('/landlord/properties');
        $this->assertNotNull(Notification::find($notification->id)->read_at);
    }
}
