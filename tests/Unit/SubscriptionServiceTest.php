<?php

namespace Tests\Unit;

use App\Models\Gateway;
use App\Models\PlanSubscribe;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * U4 del spec-service-payment-fase5: trío de SubscriptionService::paid.
 * Congela además la divergencia intencional respecto de
 * PaymentService::success (rama subscription): will_expire usa
 * `plan->duration` crudo en vez de `durationInDays()` (spec §5, "no
 * unificar a ciegas"). La invalidación de cache la dispara UserObserver
 * (congelada a nivel HTTP por G9).
 */
class SubscriptionServiceTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_u4_subscription_paid_trio_and_transaction(): void
    {
        // Arrange — el service no notifica: lo hace el controlador (4.8)
        Notification::fake();
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord();
        $subscription = PlanSubscribe::factory()->create([
            'user_id' => $landlord->id,
            'payment_status' => 'unpaid',
            'price' => 10,
            'gateway_id' => Gateway::factory()->create(['name' => 'Stripe'])->id,
        ]);

        // Act
        (new SubscriptionService)->paid($subscription, 'Manual approval', $admin->id);

        // Assert — fila aprobada + notas
        $fresh = $subscription->fresh();
        $this->assertSame('paid', $fresh->payment_status);
        $this->assertSame('Manual approval', $fresh->notes);

        // Trío forceFill — QUIRK preservado: will_expire usa plan->duration
        // crudo (1 para un plan mensual) y NO durationInDays() (30).
        $user = $landlord->fresh();
        $this->assertSame($subscription->id, $user->plan_subscribe_id);
        $this->assertNotNull($user->subscriptionDate);
        $this->assertSame(
            now()->addDays(1)->toDateString(),
            Carbon::parse($user->will_expire)->toDateString(),
        );

        // Transaction credit recibida por el admin aprobador
        $this->assertDatabaseHas('transactions', [
            'plan_subscribe_id' => $subscription->id,
            'paid_by' => $landlord->id,
            'received_by' => $admin->id,
            'amount' => 10,
            'type' => 'credit',
            'payment_type' => 'subscription',
            'payment_method' => 'Stripe',
        ]);

        Notification::assertNothingSent();
    }
}
