<?php

namespace Tests\Feature\Cache;

use App\Models\Option;
use App\Models\Plan;
use App\Models\User;
use App\Support\CacheKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class CacheSignupInvalidationTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_s1_signup_free_plan_invalidates_plan_data(): void
    {
        // Arrange
        Option::factory()->create([
            'key' => 'email-varification',
            'value' => [
                'otp_status' => 'on',
                'otp_expiration_time' => 5,
                'otp_duration_type' => 'minute',
            ],
        ]);
        $plan = Plan::factory()->create(['offerPrice' => 0, 'subscriptionPrice' => 0]);
        Mail::fake();

        $this->postJson('/signup', [
            'name' => 'John Landlord',
            'email' => 'john@example.test',
            'password' => 'Password123',
            'plan_id' => $plan->id,
        ])->assertOk()->assertJsonPath('openModal', true);

        $user = User::where('email', 'john@example.test')->first();

        // Stale plantado en la clave del landlord nuevo (gap G1)
        cache([CacheKeys::planData($user->id) => 'stale']);

        // Act — el OTP enviado es remember_token del usuario no verificado
        $response = $this->postJson('/otp-submit', [
            'otp' => (string) $user->remember_token,
            'email' => $user->email,
        ]);

        // Assert
        $response->assertOk();
        $this->assertNull(cache(CacheKeys::planData($user->id)));
        $this->assertSame($plan->subscriptionName, plan_data((string) $user->id)->plan_name);
    }
}
