<?php

namespace Tests\Feature\Payment;

use App\Models\Gateway;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\SecurityDeposit;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesUsers;
use Tests\Stubs\StubGateway;
use Tests\TestCase;

class PaymentFlowSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        StubGateway::$lastPaymentData = [];
    }

    public function test_subscription_gateways_view_renders_and_sets_session(): void
    {
        // Arrange
        $plan = Plan::factory()->create();
        $landlord = $this->createLandlord();
        Gateway::factory()->create();

        // Act
        $response = $this->get("/payments-gateways/{$plan->id}/{$landlord->id}");

        // Assert
        $response->assertOk()->assertViewIs('payments.index');

        $this->assertEquals($landlord->id, session('user_id'));
        $this->assertEquals('subscription', session('payment_type'));
    }

    public function test_security_deposit_gateways_view_renders(): void
    {
        // Arrange
        // online_payment usa $plan->property->propertyDetail (no null-safe)
        $deposit = SecurityDeposit::factory()->create([
            'property_id' => \App\Models\Property::factory()->has(\App\Models\PropertyDetail::factory()),
        ]);
        Gateway::factory()->create();

        // Act
        $response = $this->get(
            "/online-payments-gateways/{$deposit->id}/{$deposit->tenant_id}?payment_type=security_deposit"
        );

        // Assert
        $response->assertOk()->assertViewIs('payments.online_payment');

        $this->assertEquals('security_deposit', session('payment_type'));
    }

    public function test_security_deposit_view_with_invalid_payment_type_redirects_with_error(): void
    {
        // Act
        $response = $this->get('/online-payments-gateways/1/1?payment_type=xxx');

        // Assert
        $response->assertStatus(302)->assertSessionHas('error');
    }

    public function test_manual_payment_creates_unpaid_subscription(): void
    {
        // Arrange
        // HasUploader escribe en config('filesystems.default'), que en este
        // repo es el disco 'public' con root '.' (raiz del repo): fakearlo.
        Storage::fake(config('filesystems.default'));

        $plan = Plan::factory()->create();
        $landlord = $this->createLandlord();
        $gateway = Gateway::factory()->manual()->create();

        // Act
        $response = $this->withSession([
            'payment_type' => 'subscription',
            'user_id' => $landlord->id,
            'platform' => 'web',
        ])->post("/payments/{$plan->id}/{$gateway->id}", [
            'manual_data' => 'Bank transfer ACME 123456789',
            'attachment' => UploadedFile::fake()->image('receipt.jpg'),
        ]);

        // Assert
        $response->assertRedirect(route('order.status', ['status' => 'success']));

        $this->assertDatabaseHas('plan_subscribes', [
            'user_id' => $landlord->id,
            'plan_id' => $plan->id,
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_automatic_payment_redirects_to_gateway(): void
    {
        // Arrange
        $plan = Plan::factory()->create(); // offerPrice 8
        $landlord = $this->createLandlord();
        $gateway = Gateway::factory()->create(); // namespace => StubGateway

        // Act
        $response = $this->withSession([
            'payment_type' => 'subscription',
            'user_id' => $landlord->id,
            'platform' => 'web',
        ])->post("/payments/{$plan->id}/{$gateway->id}");

        // Assert
        $response->assertRedirect('https://gateway-stub.example/checkout');

        $this->assertEquals(8, StubGateway::$lastPaymentData['amount']);

        $this->assertEquals($plan->id, session('plan')->id);
        $this->assertEquals($landlord->id, session('user')->id);
        $this->assertEquals($gateway->id, session('gateway_id'));
    }

    /**
     * @group characterization
     */
    public function test_payment_success_processes_subscription_from_session(): void
    {
        // Expected to fail after Phase 2 (Seg-6 fix) — /payment/success is
        // public and processes payments from a forgeable session. It must
        // require auth + payment signature verification.
        // Arrange
        $plan = Plan::factory()->create(); // month => 30 days
        $landlord = $this->createLandlord(['will_expire' => now()->subDay()]);
        $gateway = Gateway::factory()->create();
        $this->createSuperadmin(); // received_by en Transaction

        // Act
        $response = $this->withSession([
            'payment_type' => 'subscription',
            'plan' => $plan,
            'user' => $landlord,
            'amount' => 8,
            'gateway_id' => $gateway->id,
            'platform' => 'web',
        ])->get('/payment/success');

        // Assert
        $response->assertRedirect(route('order.status', ['status' => 'success']));

        $this->assertDatabaseHas('plan_subscribes', [
            'user_id' => $landlord->id,
            'plan_id' => $plan->id,
            'payment_status' => 'paid',
        ]);

        $this->assertDatabaseHas('transactions', [
            'paid_by' => $landlord->id,
            'gateway_id' => $gateway->id,
            'payment_type' => 'subscription',
            'amount' => 8,
        ]);

        $this->assertSame(
            now()->addDays(30)->toDateString(),
            $landlord->fresh()->will_expire
        );
    }

    public function test_payment_success_without_session_redirects_to_failed(): void
    {
        // Act
        $response = $this->get('/payment/success');

        // Assert
        $response->assertRedirect(route('order.status', ['status' => 'failed']));
    }

    public function test_payment_failed_redirects_to_order_status(): void
    {
        // Act
        $response = $this->get('/payment/failed');

        // Assert
        $response->assertRedirect(route('order.status', [
            'status' => 'failed',
            'message' => __('Transaction failed for invalid credentials, Please try again later or contact with admin.'),
        ]));
    }

    public function test_order_status_returns_requested_status(): void
    {
        // Act
        $response = $this->get('/order-status?status=success');

        // Assert
        $response->assertOk();
        $this->assertSame('success', $response->getContent());
    }

    public function test_public_gateway_status_endpoints_return_200(): void
    {
        // Arrange
        $endpoints = [
            '/payment/mollie' => \App\Library\Mollie::class,
            '/payment/flutterwave' => \App\Library\Flutterwave::class,
            '/payment/thawani' => \App\Library\Thawani::class,
            '/payment/instamojo' => \App\Library\Instamojo::class,
            '/payment/toyyibpay' => \App\Library\Toyyibpay::class,
            '/payment/billplz' => \App\Library\Billplz::class,
            '/fedapay/status' => \App\Library\FedaPayGateway::class,
            '/tap-payment/status' => \App\Library\TapPayment::class,
        ];

        foreach ($endpoints as $class) {
            $this->mock($class, fn ($mock) => $mock->shouldReceive('status')->andReturn(response('ok')));
        }

        // Act + Assert
        foreach ($endpoints as $uri => $class) {
            $this->get($uri)->assertOk();
        }
    }
}
