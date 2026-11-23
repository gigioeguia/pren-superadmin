<?php

namespace Tests\Feature\Security;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Tests\TestCase;

/**
 * Regresión Seg-23 (Fase 5 / 5.6): redirect 302 http→https tras la válvula
 * FORCE_HTTPS y proxies confiables vía TRUSTED_PROXIES.
 *
 * Mismo patrón que TrustHostsSmokeTest (5.4): la request se construye con
 * SymfonyRequest::create (el cliente de test normaliza URIs absolutas) y
 * pasa por el kernel real (stack global incluido).
 */
#[Group('smoke')]
#[Group('security')]
class ForceHttpsSmokeTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        // Los proxies confiables son static en Symfony Request: sin reset
        // contaminarían el resto de la suite (lección 5.4, TrustHosts).
        Request::setTrustedProxies([], Request::HEADER_X_FORWARDED_FOR);

        parent::tearDown();
    }

    private function requestViaKernel(string $uri, array $server = []): TestResponse
    {
        $symfonyRequest = SymfonyRequest::create($uri, 'GET', [], [], [], $server);

        return TestResponse::fromBaseResponse(
            $this->app->make(HttpKernel::class)->handle($this->createTestRequest($symfonyRequest))
        );
    }

    public function test_valve_on_http_request_redirects_to_https_with_path_and_query(): void
    {
        // Arrange
        config(['app.force_https' => true]);

        // Act
        $response = $this->requestViaKernel('http://localhost/login?foo=bar');

        // Assert
        $response->assertStatus(302);
        $this->assertSame('https://localhost/login?foo=bar', $response->headers->get('Location'));
    }

    public function test_valve_on_https_request_passes_through(): void
    {
        // Arrange
        config(['app.force_https' => true]);

        // Act
        $response = $this->requestViaKernel('https://localhost/login');

        // Assert
        $response->assertOk();
    }

    public function test_valve_off_leaves_http_requests_untouched(): void
    {
        // Arrange — sin override: defaults de phpunit.xml (válvula off).

        // Act
        $response = $this->requestViaKernel('http://localhost/login');

        // Assert
        $response->assertOk();
    }

    public function test_valve_on_with_trusted_proxy_and_forwarded_proto_does_not_loop(): void
    {
        // Arrange — proxy confiado: request->secure() true vía header, sin redirect infinito.
        config(['app.force_https' => true]);
        config(['app.trusted_proxies' => '*']);

        // Act
        $response = $this->requestViaKernel('http://localhost/login', ['HTTP_X_FORWARDED_PROTO' => 'https']);

        // Assert
        $response->assertOk();
    }
}
