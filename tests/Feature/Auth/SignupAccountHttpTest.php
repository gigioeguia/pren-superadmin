<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\VerifyCsrfToken;
use App\Mail\WelcomeMail;
use App\Models\Option;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Contrato HTTP del flujo de alta web (spec-service-landlord-fase5,
 * casos F7–F10 + F12 de SignupController).
 *
 * Tests de contrato, NO de caracterización (salvo F12): congelan el
 * comportamiento actual del split web — el User se crea en POST /signup
 * (con o sin OTP) pero el trío free-plan SOLO se activa en POST
 * /otp-submit cuando el plan de sesión es free. Deben seguir en verde
 * antes y después de la migración a LandlordService.
 *
 * F12 es la única caracterización: congela el gap de DemoMode (signup
 * público no bloqueado en APP_ENV=demo), registrado en
 * `.analisis/seguridad.md` fila 14.
 */
class SignupAccountHttpTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function tearDown(): void
    {
        app()->detectEnvironment(fn () => 'testing');

        parent::tearDown();
    }

    private function otpOption(string $status): void
    {
        Option::factory()->create([
            'key' => 'email-varification',
            'value' => [
                'otp_status' => $status,
                'otp_expiration_time' => 5,
                'otp_duration_type' => 'minute',
            ],
        ]);
    }

    public function test_f7_signup_otp_on_creates_user_without_trio(): void
    {
        // Arrange — el plan free existe, pero el trío NO se crea aquí
        $this->otpOption('on');
        Mail::fake();
        $free = Plan::factory()->free()->create();

        // Act
        $response = $this->postJson('/signup', [
            'name' => 'Jane Landlord',
            'email' => 'f7@example.test',
            'password' => 'Password123',
            'plan_id' => $free->id,
        ]);

        // Assert — User creado con OTP pendiente
        $response->assertOk()
            ->assertJsonPath('openModal', true);

        $user = User::where('email', 'f7@example.test')->first();
        $this->assertSame('landlord', $user->role);
        $this->assertSame(1, $user->is_setupped);
        $this->assertSame(6, strlen((string) $user->remember_token));
        $this->assertTrue($user->email_verified_at->isFuture());

        // El trío free-plan no se activa en /signup (split del canal web)
        $this->assertSame(0, PlanSubscribe::where('user_id', $user->id)->count());
        $this->assertNull($user->plan_subscribe_id);

        // OTP/WelcomeMail post-commit
        Mail::assertSent(WelcomeMail::class);
        $this->assertGuest();
    }

    public function test_f8_signup_otp_off_logs_in_and_redirects_to_payment(): void
    {
        // Arrange
        $this->otpOption('off');
        $paid = Plan::factory()->create();

        // Act
        $response = $this->postJson('/signup', [
            'name' => 'John Landlord',
            'email' => 'f8@example.test',
            'password' => 'Password123',
            'plan_id' => $paid->id,
        ]);

        // Assert — rama OTP=off: login + redirect signed a pago, sin trío
        $response->assertOk();

        $user = User::where('email', 'f8@example.test')->first();
        $this->assertAuthenticatedAs($user);

        $this->assertStringContainsString(
            "/payments-gateways/{$paid->id}/{$user->id}",
            $response->json('redirect')
        );
        $this->assertSame(0, PlanSubscribe::where('user_id', $user->id)->count());
    }

    public function test_f9_otp_submit_with_free_plan_activates_trio(): void
    {
        // Arrange
        $this->otpOption('on');
        Mail::fake();
        $free = Plan::factory()->free()->create();

        $this->postJson('/signup', [
            'name' => 'Free Landlord',
            'email' => 'f9@example.test',
            'password' => 'Password123',
            'plan_id' => $free->id,
        ]);

        $user = User::where('email', 'f9@example.test')->first();

        // Act — el trío web se activa AQUÍ, no en /signup
        $response = $this->postJson('/otp-submit', [
            'otp' => $user->remember_token,
            'email' => $user->email,
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('message', __('OTP verified successfully. Free plan activated!'))
            ->assertJsonPath('redirect', route('landlord.dashboard'));

        $this->assertAuthenticatedAs($user);

        // Trío free-plan con los 5 campos del canal web
        $subscribe = PlanSubscribe::where('user_id', $user->id)->first();
        $this->assertNotNull($subscribe);
        $this->assertSame($free->id, $subscribe->plan_id);
        $this->assertSame('Free Plan', $subscribe->plan_name);
        $this->assertSame($free->durationInDays(), (int) $subscribe->duration);
        $this->assertSame('paid', $subscribe->payment_status);

        $user = $user->fresh();
        $this->assertSame($subscribe->id, $user->plan_subscribe_id);
        $this->assertNotNull($user->subscriptionDate);
        $this->assertTrue(Carbon::parse($user->will_expire)->isFuture());
    }

    public function test_f10_otp_submit_with_paid_plan_redirects_to_payment(): void
    {
        // Arrange
        $this->otpOption('on');
        Mail::fake();
        $paid = Plan::factory()->create();

        $this->postJson('/signup', [
            'name' => 'Paid Landlord',
            'email' => 'f10@example.test',
            'password' => 'Password123',
            'plan_id' => $paid->id,
        ]);

        $user = User::where('email', 'f10@example.test')->first();

        // Act
        $response = $this->postJson('/otp-submit', [
            'otp' => $user->remember_token,
            'email' => $user->email,
        ]);

        // Assert — rama paid: sin trío, redirect signed a pago
        $response->assertOk()
            ->assertJsonPath('message', __('Your account is verified. Proceed to payment.'));

        $this->assertStringContainsString(
            "/payments-gateways/{$paid->id}/{$user->id}",
            $response->json('redirect')
        );
        $this->assertSame(0, PlanSubscribe::where('user_id', $user->id)->count());
    }

    /**
     * @group characterization
     */
    public function test_f12_demo_mode_does_not_block_signup(): void
    {
        // Expected to fail when signup is blocked in demo (gap registrado
        // en .analisis/seguridad.md fila 14) — update to secure behavior
        // when it fails: signup.store/apisign-up en las listas de DemoMode.
        // Arrange — sin CSRF: bajo env demo, runningUnitTests() ya no aplica
        // (patrón de DemoModeTest); el foco es el gap de DemoMode
        app()->detectEnvironment(fn () => 'demo');
        $this->otpOption('off');

        // Act
        $response = $this->withoutMiddleware(VerifyCsrfToken::class)
            ->postJson('/signup', [
                'name' => 'Demo Gap Landlord',
                'email' => 'f12@example.test',
                'password' => 'Password123',
                'plan_id' => 1,
            ]);

        // Assert — documenta el gap actual: la alta real NO se bloquea
        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'email' => 'f12@example.test',
            'role' => 'landlord',
        ]);
    }
}
