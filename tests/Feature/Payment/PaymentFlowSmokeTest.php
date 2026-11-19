<?php

namespace Tests\Feature\Payment;

use App\Library\Billplz;
use App\Library\FedaPayGateway;
use App\Library\Flutterwave;
use App\Library\Instamojo;
use App\Library\Mollie;
use App\Library\TapPayment;
use App\Library\Thawani;
use App\Library\Toyyibpay;
use App\Models\Gateway;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\Property;
use App\Models\PropertyDetail;
use App\Models\RentPayment;
use App\Models\SecurityDeposit;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
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
        $url = URL::temporarySignedRoute('payments-gateways.index', now()->addHours(2), [
            'plan_id' => $plan->id,
            'landlord_id' => $landlord->id,
        ]);
        $response = $this->get($url);

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
            'property_id' => Property::factory()->has(PropertyDetail::factory()),
        ]);
        Gateway::factory()->create();

        // Act
        $url = URL::temporarySignedRoute('online.payments-gateways.index', now()->addHours(2), [
            'payment_id' => $deposit->id,
            'payment_type' => 'security_deposit',
        ]);
        $response = $this->get($url);

        // Assert
        $response->assertOk()->assertViewIs('payments.online_payment');

        $this->assertEquals('security_deposit', session('payment_type'));
    }

    public function test_security_deposit_view_with_invalid_payment_type_redirects_with_error(): void
    {
        // Act
        $url = URL::temporarySignedRoute('online.payments-gateways.index', now()->addHours(2), [
            'payment_id' => 1,
            'payment_type' => 'xxx',
        ]);

        $response = $this->get($url);

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

    public function test_payment_success_processes_subscription_from_session(): void
    {
        // Arrange
        $plan = Plan::factory()->create(); // month => 30 days
        $landlord = $this->createLandlord(['will_expire' => now()->subDay()]);
        $gateway = Gateway::factory()->create();
        $this->createSuperadmin(); // received_by en Transaction

        // Act
        $response = $this->actingAs($landlord)->withSession([
            'payment_type' => 'subscription',
            'plan' => $plan,
            'user' => $landlord,
            'amount' => 8,
            'gateway_id' => $gateway->id,
            'platform' => 'web',
            'payment_info' => ['status' => 1],
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
        $response->assertRedirect(route('login'));
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
            '/payment/mollie' => Mollie::class,
            '/payment/flutterwave' => Flutterwave::class,
            '/payment/thawani' => Thawani::class,
            '/payment/instamojo' => Instamojo::class,
            '/payment/toyyibpay' => Toyyibpay::class,
            '/payment/billplz' => Billplz::class,
            '/fedapay/status' => FedaPayGateway::class,
            '/tap-payment/status' => TapPayment::class,
        ];

        foreach ($endpoints as $class) {
            $this->mock($class, fn ($mock) => $mock->shouldReceive('status')->andReturn(response('ok')));
        }

        // Act + Assert
        foreach ($endpoints as $uri => $class) {
            $this->get($uri)->assertOk();
        }
    }

    public function test_p2_unsigned_subscription_url_returns_403(): void
    {
        $plan = Plan::factory()->create();
        $landlord = $this->createLandlord();

        $response = $this->get("/payments-gateways/{$plan->id}/{$landlord->id}");

        $response->assertForbidden();
    }

    public function test_p3_signed_url_for_other_user_with_session_returns_403(): void
    {
        $plan = Plan::factory()->create();
        $landlordA = $this->createLandlord();
        $landlordB = $this->createLandlord();

        $url = URL::temporarySignedRoute('payments-gateways.index', now()->addHours(2), [
            'plan_id' => $plan->id,
            'landlord_id' => $landlordB->id,
        ]);

        $response = $this->actingAs($landlordA)->get($url);

        $response->assertForbidden();
    }

    public function test_p4_signed_deposit_url_renders_view(): void
    {
        $deposit = SecurityDeposit::factory()->create([
            'property_id' => Property::factory()->has(PropertyDetail::factory()),
        ]);
        Gateway::factory()->create();

        $url = URL::temporarySignedRoute('online.payments-gateways.index', now()->addHours(2), [
            'payment_id' => $deposit->id,
            'payment_type' => 'security_deposit',
        ]);
        $response = $this->get($url);

        $response->assertOk()->assertViewIs('payments.online_payment');
        $this->assertEquals($deposit->tenant_id, session('user_id'));
        $this->assertEquals('security_deposit', session('payment_type'));
    }

    public function test_p5_signed_rent_url_renders_view(): void
    {
        $rentPayment = RentPayment::factory()->create();
        Gateway::factory()->create();

        $url = URL::temporarySignedRoute('online.payments-gateways.index', now()->addHours(2), [
            'payment_id' => $rentPayment->id,
            'payment_type' => 'rent_payment',
        ]);
        $response = $this->get($url);

        $response->assertOk();
        $this->assertEquals($rentPayment->rent->tenant_id, session('user_id'));
        $this->assertEquals('rent_payment', session('payment_type'));
    }

    public function test_p6_unsigned_deposit_url_returns_403(): void
    {
        $deposit = SecurityDeposit::factory()->create();

        $response = $this->get("/online-payments-gateways/{$deposit->id}?payment_type=security_deposit");

        $response->assertForbidden();
    }

    public function test_p7_old_route_with_tenant_id_returns_404(): void
    {
        $deposit = SecurityDeposit::factory()->create();

        $response = $this->get("/online-payments-gateways/{$deposit->id}/{$deposit->tenant_id}?payment_type=security_deposit");

        $response->assertNotFound();
    }

    public function test_p9_api_payment_url_subscription(): void
    {
        $landlord = $this->createLandlord();
        $plan = Plan::factory()->create();
        Gateway::factory()->create();

        Sanctum::actingAs($landlord, ['*']);
        $response = $this->getJson('/api/v1/payment-url?payment_type=subscription&plan_id='.$plan->id);

        $response->assertOk()->assertJsonStructure(['url']);
        $url = $response->json('url');
        $this->assertStringContainsString('/payments-gateways/'.$plan->id.'/'.$landlord->id, $url);
    }

    public function test_p10_api_payment_url_rejects_foreign_deposit(): void
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();
        $deposit = SecurityDeposit::factory()->create(['tenant_id' => $tenantB->id]);

        Sanctum::actingAs($tenantA, ['*']);
        $response = $this->getJson('/api/v1/payment-url?payment_type=security_deposit&payment_id='.$deposit->id);

        $response->assertForbidden();
    }

    public function test_p11_api_payment_url_requires_auth(): void
    {
        $response = $this->getJson('/api/v1/payment-url');

        $response->assertUnauthorized();
    }

    public function test_p12_success_without_auth_redirects_to_login(): void
    {
        $plan = Plan::factory()->create();
        $landlord = $this->createLandlord();
        $gateway = Gateway::factory()->create();

        $response = $this->withSession([
            'payment_type' => 'subscription',
            'plan' => $plan,
            'user' => $landlord,
            'amount' => 8,
            'gateway_id' => $gateway->id,
            'platform' => 'web',
            'payment_info' => ['status' => 1],
        ])->get('/payment/success');

        $response->assertRedirect(route('login'));
    }

    public function test_p14_success_without_payment_info_redirects_failed(): void
    {
        $plan = Plan::factory()->create();
        $landlord = $this->createLandlord();
        $gateway = Gateway::factory()->create();

        $response = $this->actingAs($landlord)->withSession([
            'payment_type' => 'subscription',
            'plan' => $plan,
            'user' => $landlord,
            'amount' => 8,
            'gateway_id' => $gateway->id,
            'platform' => 'web',
        ])->get('/payment/success');

        $response->assertRedirect(route('order.status', ['status' => 'failed']));
        $this->assertDatabaseMissing('plan_subscribes', ['user_id' => $landlord->id]);
    }

    public function test_p16_success_identity_mismatch_returns_403(): void
    {
        $plan = Plan::factory()->create();
        $landlordA = $this->createLandlord();
        $landlordB = $this->createLandlord();
        $gateway = Gateway::factory()->create();

        $response = $this->actingAs($landlordA)->withSession([
            'payment_type' => 'subscription',
            'plan' => $plan,
            'user' => $landlordB,
            'amount' => 8,
            'gateway_id' => $gateway->id,
            'platform' => 'web',
            'payment_info' => ['status' => 1],
        ])->get('/payment/success');

        $response->assertForbidden();
    }

    public function test_p17_success_idempotent_second_call(): void
    {
        $plan = Plan::factory()->create();
        $landlord = $this->createLandlord(['will_expire' => now()->subDay()]);
        $gateway = Gateway::factory()->create();
        $this->createSuperadmin();

        $session = [
            'payment_type' => 'subscription',
            'plan' => $plan,
            'user' => $landlord,
            'amount' => 8,
            'gateway_id' => $gateway->id,
            'platform' => 'web',
            'payment_info' => ['status' => 1],
        ];

        $first = $this->actingAs($landlord)->withSession($session)->get('/payment/success');
        $first->assertRedirect(route('order.status', ['status' => 'success']));

        $countBefore = PlanSubscribe::where('user_id', $landlord->id)->count();

        // second call with processed flag still in session
        $second = $this->actingAs($landlord)->withSession(array_merge($session, ['payment_processed' => true]))->get('/payment/success');
        $second->assertRedirect(route('order.status', ['status' => 'success']));

        $countAfter = PlanSubscribe::where('user_id', $landlord->id)->count();
        $this->assertEquals($countBefore, $countAfter);
    }
}
