<?php

namespace Tests\Feature\Payment;

use App\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Http;
use ReflectionClass;
use Tests\TestCase;

class WebhookSignatureSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Evitar llamadas HTTP reales en los casos de verificación.
        Http::fake();
    }

    // P11 — la exención CSRF huérfana /billplz-callback se eliminó.
    public function test_p11_csrf_exemption_removed(): void
    {
        $reflection = new ReflectionClass(PreventRequestForgery::class);
        $property = $reflection->getProperty('except');
        $defaults = $property->getDefaultValue();

        $this->assertIsArray($defaults);
        $this->assertNotContains('/billplz-callback', $defaults);
    }

    // P12 — Billplz no acredita sin sesión/credenciales.
    public function test_p12_billplz_forged_without_session_returns_404(): void
    {
        $response = $this->get('/payment/billplz?billplz[paid]=1&billplz[id]=forged');

        $response->assertStatus(404);
    }

    // P13 — Instamojo no acredita pago forjado.
    public function test_p13_instamojo_forged_payment_does_not_credit(): void
    {
        $response = $this->withSession([
            'instamojo_credentials' => [
                'mode' => false,
                'x_api_key' => 'test-key',
                'x_auth_token' => 'test-token',
                'gateway_id' => 1,
                'main_amount' => 10,
                'charge' => 0,
            ],
        ])->get('/payment/instamojo?payment_status=Credit&payment_id=forged');

        $response->assertRedirect();
        $this->assertStringContainsString('payment/failed', (string) $response->headers->get('Location'));
        $this->assertNotSame(1, session('payment_info.status') ?? null);
    }

    // P14 — Toyyibpay no acredita pago forjado.
    public function test_p14_toyyibpay_forged_payment_does_not_credit(): void
    {
        $response = $this->withSession([
            'toyyibpay_credentials' => [
                'mode' => false,
                'gateway_id' => 1,
                'main_amount' => 10,
                'charge' => 0,
            ],
        ])->get('/payment/toyyibpay?status_id=1&billcode=forged');

        $response->assertRedirect();
        $this->assertStringContainsString('payment/failed', (string) $response->headers->get('Location'));
    }

    // P15 — FedaPay no acredita sin sesión/credenciales.
    public function test_p15_fedapay_forged_without_session_returns_404(): void
    {
        $response = $this->get('/fedapay/status?status=approved');

        $response->assertStatus(404);
    }

    // P16 — Mollie no acredita pago inexistente.
    public function test_p16_mollie_invalid_payment_redirects_to_failed(): void
    {
        $response = $this->withSession([
            'pay_id' => 'forged',
            'mollie_credentials' => [
                'api_key' => 'test',
                'gateway_id' => 1,
                'payment_type' => 'subscription',
                'main_amount' => 10,
                'charge' => 0,
                'is_fallback' => false,
            ],
        ])->get('/payment/mollie');

        $response->assertRedirect();
        $this->assertStringContainsString('payment/failed', (string) $response->headers->get('Location'));
    }

    // P17 — Paystack no acredita con respuesta no exitosa.
    public function test_p17_paystack_invalid_reference_redirects_to_failed(): void
    {
        $response = $this->withSession([
            'paystack_credentials' => [
                'secret_key' => 'test',
                'gateway_id' => 1,
                'amount' => 1000,
                'main_amount' => 10,
                'charge' => 0,
                'is_fallback' => false,
            ],
        ])->post('/payment/paystack', [
            'ref_id' => 'forged',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('payment/failed', (string) $response->headers->get('Location'));
    }

    // P18 — Razorpay rechaza firma inválida.
    public function test_p18_razorpay_invalid_signature_redirects_to_failed(): void
    {
        $response = $this->withSession([
            'razorpay_credentials' => [
                'key_id' => 'test-key',
                'key_secret' => 'test-secret',
                'gateway_id' => 1,
                'amount' => 10,
                'billName' => 'Plan',
                'charge' => 0,
                'payment_type' => 'subscription',
                'is_fallback' => false,
            ],
        ])->post('/razorpay/status', [
            'rzp_signature' => 'invalid',
            'rzp_paymentid' => 'pay_invalid',
            'rzp_orderid' => 'order_invalid',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('payment/failed', (string) $response->headers->get('Location'));
    }

    // P19 — Flutterwave rechaza transacción inválida.
    public function test_p19_flutterwave_invalid_transaction_redirects_to_failed(): void
    {
        $response = $this->withSession([
            'flutterwave_credentials' => [
                'secret_key' => 'test',
                'gateway_id' => 1,
                'main_amount' => 10,
                'charge' => 0,
            ],
        ])->get('/payment/flutterwave?transaction_id=forged');

        $response->assertRedirect();
        $this->assertStringContainsString('payment/failed', (string) $response->headers->get('Location'));
    }

    // P20 — Thawani rechaza invoice inválido.
    public function test_p20_thawani_invalid_invoice_redirects_to_failed(): void
    {
        $response = $this->withSession([
            'thawani_credentials' => [
                'mode' => 0,
                'secret_key' => 'test',
                'gateway_id' => 1,
                'main_amount' => 10,
                'charge' => 0,
                'response' => ['data' => ['invoice' => 'forged']],
            ],
        ])->get('/payment/thawani');

        $response->assertRedirect();
        $this->assertStringContainsString('payment/failed', (string) $response->headers->get('Location'));
    }

    // P21 — Tap rechaza charge no capturado.
    public function test_p21_tap_invalid_charge_redirects_to_failed(): void
    {
        $response = $this->withSession([
            'secret_key' => 'test',
        ])->get('/tap-payment/status?tap_id=forged');

        $response->assertRedirect();
        $this->assertStringContainsString('payment/failed', (string) $response->headers->get('Location'));
    }

    // P22 — Sin sesión, ningún status explota (404/400, nunca 500).
    public function test_p22_status_without_session_does_not_500(): void
    {
        $checks = [
            ['GET', '/payment/mollie'],
            ['POST', '/payment/paystack'],
            ['POST', '/razorpay/status'],
            ['GET', '/payment/flutterwave'],
            ['GET', '/payment/thawani'],
            ['GET', '/payment/instamojo'],
            ['GET', '/payment/toyyibpay'],
            ['GET', '/payment/billplz'],
            ['GET', '/tap-payment/status'],
            ['GET', '/fedapay/status'],
        ];

        foreach ($checks as [$method, $uri]) {
            $response = $method === 'GET'
                ? $this->get($uri)
                : $this->post($uri);

            $this->assertNotSame(500, $response->getStatusCode(), "500 en {$method} {$uri}");
            $this->assertTrue(
                in_array($response->getStatusCode(), [400, 404, 302], true),
                "Status inesperado {$response->getStatusCode()} en {$method} {$uri}"
            );
        }
    }
}
