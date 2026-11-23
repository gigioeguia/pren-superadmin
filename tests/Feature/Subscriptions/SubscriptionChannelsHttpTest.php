<?php

namespace Tests\Feature\Subscriptions;

use App\Models\Gateway;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Notifications\SendNotification;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * P10–P12 del spec-service-payment-fase5: contrato HTTP del listing de
 * subscriptions por canal (admin/landlord/api) y de reject/paid/invoice/
 * file-download admin, congelado pre-migración. Los efectos DB de paid
 * (trío forceFill + invalidación) ya están congelados por
 * SubscriptionPaidTest (G8–G9); aquí se congela además el JSON shape y
 * la notificación post-commit.
 */
class SubscriptionChannelsHttpTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    private function adminWith(string $permission)
    {
        $admin = $this->createAdmin();
        $admin->givePermissionTo(
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web'])
        );

        return $admin;
    }

    public function test_p10a_admin_subscriptions_index_lists_rows(): void
    {
        // Arrange
        $admin = $this->adminWith('subscriptions-read');
        $gold = Plan::factory()->create(['subscriptionName' => 'Gold Plan']);
        $silver = Plan::factory()->create(['subscriptionName' => 'Silver Plan']);
        PlanSubscribe::factory()->create(['plan_id' => $gold->id, 'payment_status' => 'paid']);
        PlanSubscribe::factory()->create(['plan_id' => $silver->id, 'payment_status' => 'unpaid']);

        // Act
        $response = $this->actingAs($admin)->get('/admin/subscriptions');

        // Assert
        $response->assertOk()->assertViewIs('admin.subscriptions.index');
        $response->assertSee('Gold Plan')->assertSee('Silver Plan');
    }

    public function test_p10b_admin_subscriptions_filter_returns_rows_json(): void
    {
        // Arrange
        $admin = $this->adminWith('subscriptions-read');
        PlanSubscribe::factory()->count(2)->create();

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post('/admin/subscriptions/filter', [
                'search' => 'paid',
                'per_page' => 10,
            ], ['X-Requested-With' => 'XMLHttpRequest']);

        // Assert
        $response->assertOk()->assertJsonStructure(['data']);
    }

    public function test_p10c_landlord_subscriptions_lists_active_plans_only(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        Plan::factory()->create(['subscriptionName' => 'Active Plan', 'status' => 1]);
        Plan::factory()->create(['subscriptionName' => 'Disabled Plan', 'status' => 0]);

        // Act
        $response = $this->actingAs($landlord)->get('/landlord/subscriptions');

        // Assert
        $response->assertOk()->assertViewIs('landlord.subscription.index');
        $response->assertSee('Active Plan');
        $response->assertDontSee('Disabled Plan');
    }

    public function test_p10d_api_plans_lists_active_plans(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        Plan::factory()->create(['subscriptionName' => 'Api Plan', 'status' => 1]);
        Plan::factory()->create(['subscriptionName' => 'Hidden Plan', 'status' => 0]);

        // Act
        Sanctum::actingAs($landlord, ['*']);
        $response = $this->getJson('/api/v1/plans');

        // Assert
        $response->assertOk()
            ->assertJsonPath('message', 'Data fetched successfully.')
            ->assertJsonPath('data.0.subscriptionName', 'Api Plan');
        $this->assertCount(1, $response->json('data'));
    }

    public function test_p11a_admin_subscription_reject_updates_status_and_notes(): void
    {
        // Arrange
        $admin = $this->adminWith('subscriptions-update');
        $subscription = PlanSubscribe::factory()->create(['payment_status' => 'unpaid']);

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson("/admin/subscriptions/reject/{$subscription->id}", [
                'notes' => 'Wrong amount',
            ]);

        // Assert
        $response->assertOk()->assertJson([
            'message' => 'Subscription rejected successfully',
            'redirect' => route('admin.subscriptions.index'),
        ]);

        $this->assertSame('rejected', $subscription->fresh()->payment_status);
        $this->assertSame('Wrong amount', $subscription->fresh()->notes);
    }

    public function test_p11b_admin_subscription_reject_requires_notes(): void
    {
        // Arrange
        $admin = $this->adminWith('subscriptions-update');
        $subscription = PlanSubscribe::factory()->create(['payment_status' => 'unpaid']);

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson("/admin/subscriptions/reject/{$subscription->id}");

        // Assert
        $response->assertUnprocessable();
        $this->assertSame('unpaid', $subscription->fresh()->payment_status);
    }

    public function test_p11c_admin_subscription_paid_responds_json_and_notifies(): void
    {
        // Arrange
        Notification::fake();
        $admin = $this->adminWith('subscriptions-update');
        $landlord = $this->createLandlord();
        $subscription = PlanSubscribe::factory()->create([
            'user_id' => $landlord->id,
            'payment_status' => 'unpaid',
            'gateway_id' => Gateway::factory()->create()->id,
        ]);

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson("/admin/subscriptions/paid/{$subscription->id}", [
                'notes' => 'Manual approval',
            ]);

        // Assert
        $response->assertOk()->assertJson([
            'message' => 'Subscription paid successfully',
            'redirect' => route('admin.subscriptions.index'),
        ]);

        $this->assertSame('paid', $subscription->fresh()->payment_status);
        Notification::assertSentTo($landlord, SendNotification::class);
    }

    public function test_p11d_admin_subscription_paid_requires_notes(): void
    {
        // Arrange
        $admin = $this->adminWith('subscriptions-update');
        $subscription = PlanSubscribe::factory()->create(['payment_status' => 'unpaid']);

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson("/admin/subscriptions/paid/{$subscription->id}");

        // Assert
        $response->assertUnprocessable();
        $this->assertSame('unpaid', $subscription->fresh()->payment_status);
    }

    public function test_p12a_admin_subscription_invoice_renders(): void
    {
        // Arrange
        $admin = $this->adminWith('subscriptions-read');
        $subscription = PlanSubscribe::factory()->create(['payment_status' => 'paid']);

        // Act
        $response = $this->actingAs($admin)->get("/admin/subscriptions/get-invoice/{$subscription->id}");

        // Assert
        $response->assertOk()->assertViewIs('admin.subscriptions.invoice');
        $response->assertSee($subscription->user->name);
    }

    public function test_p12b_admin_subscription_file_download_streams_attachment(): void
    {
        // Arrange
        // HasUploader/Storage usan config('filesystems.default') (disco
        // 'public' con root '.'): fakearlo para no escribir en la raíz.
        Storage::fake(config('filesystems.default'));
        $admin = $this->adminWith('subscriptions-read');
        Storage::disk(config('filesystems.default'))->put('uploads/receipt.jpg', 'receipt-bytes');
        $subscription = PlanSubscribe::factory()->create([
            'notes' => ['attachment' => 'uploads/receipt.jpg'],
        ]);

        // Act
        $response = $this->actingAs($admin)->get("/admin/subscriptions/file-download/{$subscription->id}");

        // Assert
        $response->assertOk();
        $this->assertSame('receipt-bytes', $response->streamedContent());
    }

    public function test_p12c_admin_subscription_file_download_without_attachment_redirects_back(): void
    {
        // Arrange
        $admin = $this->adminWith('subscriptions-read');
        $subscription = PlanSubscribe::factory()->create([
            'notes' => ['manual_data' => 'Bank transfer'],
        ]);

        // Act
        $response = $this->actingAs($admin)->get("/admin/subscriptions/file-download/{$subscription->id}");

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('error', 'File not found');
    }
}
