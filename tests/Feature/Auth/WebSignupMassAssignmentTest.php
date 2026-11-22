<?php

namespace Tests\Feature\Auth;

use App\Models\Option;
use App\Models\PlanSubscribe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * S1–S6 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11).
 *
 * S1 y S2 son caracterizaciones de Seg-28 (inyección masiva en la ruta
 * pública de signup): documentan el comportamiento inseguro vigente y se
 * flipean en el mismo PR que aplica el fix.
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

    /**
     * @group characterization
     */
    public function test_s1_signup_mass_assignment_injection_is_persisted(): void
    {
        // Expected to fail after spec-user-fillable-restrictivo-fase5 (Seg-28 fix).

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

        // Assert — comportamiento inseguro actual: todas las claves inyectadas pasan
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'injected@example.test',
            'balance' => 999,
            'will_expire' => '2099-12-31 00:00:00',
            'plan_subscribe_id' => $subscribe->id,
            'landlord_id' => $landlord->id,
            'profile_type' => 'injected',
        ]);
    }

    /**
     * @group characterization
     */
    public function test_s2_signup_merge_order_overrides_server_otp(): void
    {
        // Expected to fail after spec-user-fillable-restrictivo-fase5 (Seg-28 fix).

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

        // Assert — la unión `+` da precedencia al request: el cliente pisa el OTP
        $response->assertOk()->assertJsonPath('openModal', true);

        $user = User::where('email', 'merge@example.test')->first();
        $this->assertSame('000111', $user->remember_token);
        $this->assertSame('2001-01-01 00:00:00', $user->email_verified_at->format('Y-m-d H:i:s'));
    }
}
