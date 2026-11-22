<?php

namespace Tests\Feature\Auth;

use App\Mail\WelcomeMail;
use App\Models\Option;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Contrato HTTP de Api/Auth/RegistrationController (spec-service-landlord-fase5,
 * caso F11 — granularidad f11a/f11b, mismo criterio que hijo 3 D2).
 *
 * Tests de contrato, NO de caracterización: congelan el comportamiento
 * actual del canal api — User y trío free-plan en el MISMO endpoint
 * POST /api/v1/sign-up, con los 3 campos del canal (payment_status queda
 * en su default 'unpaid', quirk frente a admin/web que setean 'paid').
 * Deben seguir en verde antes y después de la migración a LandlordService.
 *
 * Quirk congelado (f11b): con OTP=on el trío se asigna ANTES de verificar
 * el email — divergencia documentada en la tabla §4 del spec.
 */
class RegistrationAccountHttpTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_f11a_api_sign_up_otp_off_creates_user_and_trio_with_api_fields(): void
    {
        // Arrange
        $free = Plan::factory()->free()->create();
        Notification::fake();

        // Act — crafted keys: el role jamás viene del request (Seg-28)
        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'Api Landlord',
            'email' => 'f11a@example.test',
            'password' => 'Password123',
            'role' => 'superadmin',
            'plan_subscribe_id' => 999,
            'balance' => 500,
        ]);

        // Assert — JSON shape de la rama OTP=off
        $response->assertOk()
            ->assertJsonPath('message', 'Account created successfully. You are now logged in.')
            ->assertJsonPath('user.email', 'f11a@example.test');

        $user = User::where('email', 'f11a@example.test')->first();
        $this->assertSame('landlord', $user->role);
        $this->assertSame(0, $user->is_setupped);
        $this->assertSame(0.0, (float) $user->balance);

        // Token Sanctum emitido en la respuesta
        $this->assertNotEmpty($response->json('token'));
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'api-token',
        ]);

        // Trío con los 3 campos del canal api (sin payment_status, quirk)
        $subscribe = PlanSubscribe::where('user_id', $user->id)->first();
        $this->assertNotNull($subscribe);
        $this->assertSame($free->id, $subscribe->plan_id);
        $this->assertSame($free->durationInDays(), (int) $subscribe->duration);
        $this->assertNull($subscribe->plan_name);
        // Quirk api: sin payment_status en el create → default de columna 'unpaid'
        // (admin/web lo setean 'paid' explícitamente)
        $this->assertSame('unpaid', $subscribe->payment_status);

        $user = $user->fresh();
        $this->assertSame($subscribe->id, $user->plan_subscribe_id);
        $this->assertNotNull($user->subscriptionDate);
        $this->assertTrue(Carbon::parse($user->will_expire)->isFuture());
    }

    public function test_f11b_api_sign_up_otp_on_assigns_trio_before_verification(): void
    {
        // Arrange
        $this->otpOption('on');
        Mail::fake();
        Notification::fake();
        Plan::factory()->free()->create();

        // Act
        $response = $this->postJson('/api/v1/sign-up', [
            'name' => 'Api Otp Landlord',
            'email' => 'f11b@example.test',
            'password' => 'Password123',
        ]);

        // Assert — JSON shape de la rama OTP=on
        $response->assertOk()
            ->assertJsonPath('otp_required', true)
            ->assertJsonPath('email', 'f11b@example.test');

        $user = User::where('email', 'f11b@example.test')->first();
        $this->assertSame('landlord', $user->role);
        $this->assertSame(0, $user->is_setupped);
        $this->assertSame(6, strlen((string) $user->remember_token));
        $this->assertTrue($user->email_verified_at->isFuture());

        // Quirk congelado: el trío YA está asignado antes de verificar el OTP
        $subscribe = PlanSubscribe::where('user_id', $user->id)->first();
        $this->assertNotNull($subscribe);
        $this->assertSame($subscribe->id, $user->plan_subscribe_id);

        // OTP/WelcomeMail + notificación post-commit
        Mail::assertSent(WelcomeMail::class);
        $this->assertGuest();
    }
}
