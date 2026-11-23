<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

#[Group('smoke')]
#[Group('security')]
class SessionSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_session_cookie_carries_secure_httponly_and_samesite_flags(): void
    {
        // Arrange
        config(['session.secure' => true]);

        // Act
        $response = $this->get('/login');

        // Assert
        $response->assertOk();

        $cookie = $this->sessionCookie($response);
        $this->assertTrue($cookie->isSecure(), 'La cookie de sesión debe llevar el flag Secure.');
        $this->assertTrue($cookie->isHttpOnly(), 'La cookie de sesión debe llevar el flag HttpOnly.');
        $this->assertSame('lax', $cookie->getSameSite());
    }

    public function test_login_session_persists_with_database_driver(): void
    {
        // Arrange
        config(['session.driver' => 'database']);
        $landlord = $this->createLandlord();

        // Act
        $login = $this->postJson('/login/store', [
            'email' => $landlord->email,
            'password' => 'password',
        ]);

        // Assert
        $login->assertOk()
            ->assertJsonPath('redirect', route('landlord.dashboard'));

        $this->assertDatabaseHas('sessions', ['user_id' => $landlord->id]);

        // Segunda request replicando la cookie de sesión del login (como un
        // browser): si el handler DB no persistiera/leyera, llegaría como
        // guest y LandlordMiddleware redirigiría a login, no a suscripciones.
        $cookie = $this->sessionCookie($login);
        $dashboard = $this->withCookie($cookie->getName(), $cookie->getValue())
            ->get(route('landlord.dashboard'));

        // Landlord autenticado sin plan activo: ExpiredMiddleware redirige
        // a la página de suscripciones — prueba que la sesión se leyó de DB.
        $dashboard->assertRedirect(route('landlord.subscriptions.index'));
    }

    private function sessionCookie(TestResponse $response): Cookie
    {
        $sessionCookies = collect($response->headers->getCookies())
            ->filter(fn (Cookie $cookie) => $cookie->getName() === config('session.cookie'));

        $this->assertCount(
            1,
            $sessionCookies,
            'La respuesta debe setear exactamente una cookie de sesión.'
        );

        return $sessionCookies->first();
    }
}
