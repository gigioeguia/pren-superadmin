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
 * Regresión Seg-21 (Fase 5 / 5.4): validación del Host header por `TrustHosts`.
 *
 * El cliente de test normaliza la URI vía `url()` (`prepareUrlForRequest`) y
 * `Request::create` sobrescribe `HTTP_HOST` con el host de esa URI absoluta —
 * por eso el Host custom se inyecta construyendo la request Symfony con URI
 * relativa y pasando por el kernel real (stack global incluido).
 */
#[Group('smoke')]
#[Group('security')]
class TrustHostsSmokeTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        // Los patterns de hosts confiables son static en Symfony Request:
        // sin reset contaminarían el resto de la suite.
        Request::setTrustedHosts([]);

        parent::tearDown();
    }

    private function getWithHost(string $uri, string $host): TestResponse
    {
        $symfonyRequest = SymfonyRequest::create($uri, 'GET', [], [], [], ['HTTP_HOST' => $host]);

        return TestResponse::fromBaseResponse(
            $this->app->make(HttpKernel::class)->handle($this->createTestRequest($symfonyRequest))
        );
    }

    public function test_f1_foreign_host_is_rejected(): void
    {
        // Act
        $response = $this->getWithHost('/login', 'evil.example');

        // Assert
        $response->assertStatus(400);
    }

    public function test_f2_app_url_host_is_accepted(): void
    {
        // Act
        $response = $this->getWithHost('/login', 'localhost');

        // Assert
        $response->assertOk();
    }

    public function test_f3_app_url_subdomain_is_accepted(): void
    {
        // Act
        $response = $this->getWithHost('/login', 'foo.localhost');

        // Assert
        $response->assertOk();
    }

    public function test_f4_dev_host_is_accepted_in_testing(): void
    {
        // Act
        $response = $this->getWithHost('/login', '127.0.0.1');

        // Assert
        $response->assertOk();
    }
}
