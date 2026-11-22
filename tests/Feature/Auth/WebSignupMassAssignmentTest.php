<?php

namespace Tests\Feature\Auth;

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
 * S1–S6 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11).
 *
 * S1/S2 cerraban Seg-28 (inyección masiva + override por merge-order en
 * la ruta pública de signup): nacieron como caracterización y se flipearon
 * con el fix del mismo PR. S3–S6 son regresión del comportamiento legítimo
 * persistido vía forceFill.
 */
class WebSignupMassAssignmentTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

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

    public function test_s1_signup_ignores_injected_mass_assignment_columns(): void
    {
        // Arrange
        $this->otpOption('off');
        $subscribe = PlanSubscribe::factory()->create();
        $landlord = $this->createLandlord();

        // Act
        $response = $this->postJson('/signup', [
            'name' => 'John Landlord',
            'email' => 'injected@example.test',
            'password' => 'Password123',
            'plan_id' => 1,
            'balance' => 999,
            'will_expire' => '2099-12-31 00:00:00',
            'plan_subscribe_id' => $subscribe->id,
            'landlord_id' => $landlord->id,
            'profile_type' => 'injected',
        ]);

        // Assert — Seg-28 cerrado: solo se persisten las claves del array explícito
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'injected@example.test',
            'balance' => 0,
            'will_expire' => null,
            'plan_subscribe_id' => null,
            'landlord_id' => null,
            'profile_type' => null,
        ]);
    }

    public function test_s2_signup_server_otp_prevails_over_client_keys(): void
    {
        // Arrange
        $this->otpOption('on');
        Mail::fake();

        // Act
        $response = $this->postJson('/signup', [
            'name' => 'Jane Landlord',
            'email' => 'merge@example.test',
            'password' => 'Password123',
            'remember_token' => '000111',
            'email_verified_at' => '2001-01-01 00:00:00',
        ]);

        // Assert — Seg-28 cerrado: el cliente no pisa el OTP del servidor
        $response->assertOk()->assertJsonPath('openModal', true);

        $user = User::where('email', 'merge@example.test')->first();
        $this->assertNotSame('000111', $user->remember_token);
        $this->assertSame(6, strlen((string) $user->remember_token));
        $this->assertTrue($user->email_verified_at->isFuture());
    }

    public function test_s3_signup_otp_off_persists_server_side_values(): void
    {
        // Arrange
        $this->otpOption('off');

        // Act
        $response = $this->postJson('/signup', [
            'name' => 'John Landlord',
            'email' => 'legit@example.test',
            'password' => 'Password123',
            'plan_id' => 1,
        ]);

        // Assert
        $response->assertOk();

        $user = User::where('email', 'legit@example.test')->first();
        $this->assertSame(1, $user->is_setupped);
        $this->assertNull($user->remember_token);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('landlord', $user->role);
    }

    public function test_s4_signup_otp_on_persists_server_otp(): void
    {
        // Arrange
        $this->otpOption('on');
        Mail::fake();

        // Act
        $response = $this->postJson('/signup', [
            'name' => 'Jane Landlord',
            'email' => 'otpon@example.test',
            'password' => 'Password123',
        ]);

        // Assert
        $response->assertOk();

        $user = User::where('email', 'otpon@example.test')->first();
        $this->assertSame(6, strlen((string) $user->remember_token));
        $this->assertTrue($user->email_verified_at->isFuture());
    }

    public function test_s5_otp_submit_clears_token_and_verifies(): void
    {
        // Arrange
        $user = User::factory()->landlord()->create([
            'email' => 'pending@example.test',
            'remember_token' => '123456',
            'email_verified_at' => now()->addHour(),
        ]);

        // Act
        $response = $this->postJson('/otp-submit', [
            'otp' => '123456',
            'email' => $user->email,
        ]);

        // Assert
        $response->assertOk();

        $user = $user->fresh();
        $this->assertNull($user->remember_token);
        $this->assertTrue($user->email_verified_at->lte(now()));
    }

    public function test_s6_otp_submit_with_free_plan_assigns_subscription_trio(): void
    {
        // Arrange
        $plan = Plan::factory()->free()->create();
        $user = User::factory()->landlord()->create([
            'email' => 'freeplan@example.test',
            'remember_token' => '123456',
            'email_verified_at' => now()->addHour(),
        ]);

        // Act
        $response = $this->withSession(['plan_id' => $plan->id])
            ->postJson('/otp-submit', [
                'otp' => '123456',
                'email' => $user->email,
            ]);

        // Assert — trío persistido vía forceFill (no fillable)
        $response->assertOk()->assertJsonPath('message', __('OTP verified successfully. Free plan activated!'));

        $user = $user->fresh();
        $this->assertNotNull($user->plan_subscribe_id);
        $this->assertNotNull($user->subscriptionDate);
        $this->assertTrue(Carbon::parse($user->will_expire)->isFuture());
    }
}
