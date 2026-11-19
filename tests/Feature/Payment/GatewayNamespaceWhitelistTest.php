<?php

namespace Tests\Feature\Payment;

use App\Library\Billplz;
use App\Library\FedaPayGateway;
use App\Library\Flutterwave;
use App\Library\Instamojo;
use App\Library\Mollie;
use App\Library\Paystack;
use App\Library\Razorpay;
use App\Library\StripeGateway;
use App\Library\TapPayment;
use App\Library\Thawani;
use App\Library\Toyyibpay;
use App\Models\Gateway;
use Tests\Stubs\StubGateway;
use Tests\TestCase;

class GatewayNamespaceWhitelistTest extends TestCase
{
    public function test_is_allowed_namespace_accepts_production_gateways_and_stub(): void
    {
        $allowed = [
            StripeGateway::class,
            Mollie::class,
            FedaPayGateway::class,
            Paystack::class,
            Razorpay::class,
            Instamojo::class,
            Toyyibpay::class,
            Flutterwave::class,
            Thawani::class,
            TapPayment::class,
            Billplz::class,
            StubGateway::class,
        ];

        foreach ($allowed as $namespace) {
            $this->assertTrue(
                Gateway::isAllowedNamespace($namespace),
                "Expected {$namespace} to be allowed"
            );
        }
    }

    public function test_is_allowed_namespace_rejects_invalid_values(): void
    {
        foreach ([null, '', 'App\\Library\\Nope', 'App\\Models\\User', 'Foo\\Bar'] as $namespace) {
            $this->assertFalse(
                Gateway::isAllowedNamespace($namespace),
                'Expected '.var_export($namespace, true).' to be rejected'
            );
        }
    }

    public function test_allowed_namespaces_includes_stub_only_when_class_exists(): void
    {
        $list = Gateway::allowedNamespaces();

        $this->assertContains(StripeGateway::class, $list);
        $this->assertContains(StubGateway::class, $list);
    }
}
