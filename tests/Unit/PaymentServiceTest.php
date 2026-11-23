<?php

namespace Tests\Unit;

use App\Models\Gateway;
use App\Models\Plan;
use App\Models\SecurityDeposit;
use App\Services\PaymentService;
use App\Support\PaymentOutcome;
use App\Support\PaymentState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\CreatesUsers;
use Tests\Stubs\StubGateway;
use Tests\TestCase;

/**
 * U1–U3 del spec-service-payment-fase5: ramas de PaymentService a nivel
 * unitario. La red HTTP (P1–P9) vive en tests/Feature/Payment/.
 */
class PaymentServiceTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    private PaymentService $payments;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payments = new PaymentService;
        StubGateway::$lastPaymentData = [];
        StubGateway::$transactionLevelAtCall = -1;
    }

    public function test_u1_success_security_deposit_branch_credits_landlord_balance(): void
    {
        // Arrange
        $landlord = $this->createLandlord(['balance' => 0]);
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $deposit = SecurityDeposit::factory()->create([
            'tenant_id' => $tenant->id,
            'landlord_id' => $landlord->id,
            'landlord_balance' => 90,
            'total_amount' => 100,
            'charge' => 0,
            'status' => 'pending',
        ]);
        $gateway = Gateway::factory()->create();

        $state = new PaymentState(
            paymentType: 'security_deposit',
            planId: $deposit->id,
            userId: $tenant->id,
            amount: 100,
            gatewayId: $gateway->id,
            paymentInfo: ['status' => 1],
            sessionId: 'u1-session',
            stripeSessionId: null,
        );

        // Act
        $outcome = $this->payments->success($state);

        // Assert — balance acreditado vía forceFill (spec 5.11)
        $this->assertSame(PaymentOutcome::Success, $outcome);
        $this->assertSame(90.0, (float) $landlord->fresh()->balance);
        $this->assertSame('paid', $deposit->fresh()->status);

        $this->assertDatabaseHas('transactions', [
            'security_deposit_id' => $deposit->id,
            'paid_by' => $tenant->id,
            'received_by' => $landlord->id,
            'gateway_id' => $gateway->id,
            'type' => 'debit',
            'payment_type' => 'security_deposit',
            'amount' => 90,
        ]);
    }

    public function test_u2a_success_with_spent_session_state_writes_nothing(): void
    {
        // Arrange — sesión ya gastada por un success previo: el
        // controlador olvidó plan_id/user_id/amount/gateway_id/payment_info
        // y solo queda payment_type (que no está en la lista de forget).
        $gateway = Gateway::factory()->create();

        $state = new PaymentState(
            paymentType: 'subscription',
            planId: null,
            userId: null,
            amount: null,
            gatewayId: null,
            paymentInfo: null,
            sessionId: 'u2a-session',
            stripeSessionId: null,
        );

        // Act
        $outcome = $this->payments->success($state);

        // Assert
        $this->assertSame(PaymentOutcome::InvalidCredentials, $outcome);
        $this->assertDatabaseMissing('transactions', ['gateway_id' => $gateway->id]);
        $this->assertDatabaseCount('plan_subscribes', 0);
    }

    public function test_u2b_success_with_busy_lock_writes_nothing(): void
    {
        // Arrange — lock de la sesión ya tomado (worker concurrente)
        $lock = Cache::lock('payment-success:u2b-session', 10);
        $this->assertTrue($lock->get());

        $landlord = $this->createLandlord();
        $this->createSuperadmin();
        $plan = Plan::factory()->create();
        $gateway = Gateway::factory()->create();

        $state = new PaymentState(
            paymentType: 'subscription',
            planId: $plan->id,
            userId: $landlord->id,
            amount: 8,
            gatewayId: $gateway->id,
            paymentInfo: ['status' => 1],
            sessionId: 'u2b-session',
            stripeSessionId: null,
        );

        // Act
        $outcome = $this->payments->success($state);

        // Assert
        $this->assertSame(PaymentOutcome::InvalidCredentials, $outcome);
        $this->assertDatabaseMissing('plan_subscribes', [
            'user_id' => $landlord->id,
            'plan_id' => $plan->id,
        ]);

        $lock->release();
    }

    public function test_u3_pay_gateway_invokes_make_payment_outside_transaction(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        $plan = Plan::factory()->create(); // offerPrice 8
        $gateway = Gateway::factory()->create(); // namespace => StubGateway

        $context = $this->payments->resolvePayment('subscription', $plan->id, $gateway->id, $landlord->id);

        // RefreshDatabase envuelve el test en una tx: el baseline es el
        // nivel al momento del act.
        $baseline = \DB::transactionLevel();

        // Act
        $result = $this->payments->payGateway($context, phone: '55123', platform: 'web', expectsJson: false);

        // Assert — make_payment invocado sin abrir NINGUNA transacción
        $this->assertSame($baseline, StubGateway::$transactionLevelAtCall);
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame('https://gateway-stub.example/checkout', $result->getTargetUrl());

        // payment_data congelado (contrato con los 11 gateways)
        $data = StubGateway::$lastPaymentData;
        // offerPrice vuelve de MySQL como float (8.0): freeze por valor.
        $this->assertEquals(8, $data['amount']);
        $this->assertSame('subscription', $data['payment_type']);
        $this->assertSame($plan->id, $data['plan_id']);
        $this->assertSame($gateway->id, $data['gateway_id']);
        $this->assertSame('merchant', $data['request_from']);
        $this->assertSame('web', $data['platform']);
        $this->assertSame('sandbox', $data['mode']);
        $this->assertSame(0, $data['charge']);
        $this->assertArrayHasKey('pay_amount', $data);
        $this->assertArrayHasKey('currency', $data);
        $this->assertArrayHasKey('billName', $data);

        // Quirk preservado: con phone en el request se envía el phone
        // del user, no el del request.
        $this->assertSame($landlord->phone, $data['phone']);

        $this->assertDatabaseMissing('plan_subscribes', ['user_id' => $landlord->id]);
    }
}
