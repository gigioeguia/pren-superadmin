<?php

namespace Tests\Feature\Auth;

use App\Mail\WelcomeMail;
use App\Models\Option;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class WebSignupSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    /** Valor de phpunit.xml; se restaura en tearDown tras el test S8. */
    private const MAIL_USERNAME = 'smoke@test.local';

    protected function tearDown(): void
    {
        $this->restoreMailUsernameEnv();
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

    /**
     * env('MAIL_USERNAME') lee $_ENV/$_SERVER/getenv en runtime:
     * se unsetean para simular la ausencia del dato (S8).
     */
    private function withoutMailUsername(): void
    {
        unset($_ENV['MAIL_USERNAME'], $_SERVER['MAIL_USERNAME']);
        putenv('MAIL_USERNAME');
    }

    private function restoreMailUsernameEnv(): void
    {
        $_ENV['MAIL_USERNAME'] = self::MAIL_USERNAME;
        $_SERVER['MAIL_USERNAME'] = self::MAIL_USERNAME;
        putenv('MAIL_USERNAME='.self::MAIL_USERNAME);
    }

    public function test_signup_with_otp_off_creates_landlord_and_logs_in(): void
    {
        // Arrange
        $this->otpOption('off');

        // Act
        $response = $this->postJson('/signup', [
            'name' => 'John Landlord',
            'email' => 'john@example.test',
            'password' => 'password',
            'plan_id' => 1,
        ]);

        // Assert
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.test',
            'role' => 'landlord',
            'is_setupped' => 1,
        ]);

        $this->assertAuthenticated();
    }

    public function test_signup_with_otp_on_requires_otp_and_does_not_login(): void
    {
        // Arrange
        $this->otpOption('on');
        Mail::fake();

        // Act
        $response = $this->postJson('/signup', [
            'name' => 'John Landlord',
            'email' => 'john@example.test',
            'password' => 'password',
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('openModal', true);

        $this->assertGuest();

        $user = User::where('email', 'john@example.test')->first();
        $this->assertNotNull($user->remember_token);
        $this->assertEquals(6, strlen((string) $user->remember_token));
        $this->assertTrue($user->email_verified_at->isFuture());

        Mail::assertSent(WelcomeMail::class);
    }

    public function test_otp_submit_with_valid_token_logs_in(): void
    {
        // Arrange
        $user = User::factory()->landlord()->create([
            'email' => 'john@example.test',
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
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->fresh()->remember_token);
    }

    public function test_otp_submit_with_expired_token_returns_410(): void
    {
        // Arrange
        $user = User::factory()->landlord()->create([
            'email' => 'john@example.test',
            'remember_token' => '123456',
            'email_verified_at' => now()->subHour(),
        ]);

        // Act
        $response = $this->postJson('/otp-submit', [
            'otp' => '123456',
            'email' => $user->email,
        ]);

        // Assert
        $response->assertStatus(410);
        $this->assertGuest();
    }

    public function test_otp_submit_with_invalid_token_returns_400(): void
    {
        // Arrange
        $user = User::factory()->landlord()->create([
            'email' => 'john@example.test',
            'remember_token' => '123456',
            'email_verified_at' => now()->addHour(),
        ]);

        // Act
        $response = $this->postJson('/otp-submit', [
            'otp' => '999999',
            'email' => $user->email,
        ]);

        // Assert
        $response->assertStatus(400);
        $this->assertGuest();
    }

    public function test_signup_with_existing_setupped_email_returns_406(): void
    {
        // Arrange
        $this->otpOption('off');
        $user = $this->createLandlord(['email' => 'taken@example.test']);

        // Act
        $response = $this->postJson('/signup', [
            'name' => 'John Landlord',
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Assert
        $response->assertStatus(406)
            ->assertJsonPath('message', 'This email is already exists.');
    }

    public function test_signup_without_name_returns_422(): void
    {
        // Act
        $response = $this->postJson('/signup', [
            'email' => 'john@example.test',
            'password' => 'password',
        ]);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_signup_without_mail_username_returns_406(): void
    {
        // Arrange
        $this->otpOption('off');
        $this->withoutMailUsername();

        // Act
        $response = $this->postJson('/signup', [
            'name' => 'John Landlord',
            'email' => 'john@example.test',
            'password' => 'password',
        ]);

        // Assert
        $response->assertStatus(406)
            ->assertJsonPath('message', __('Mail service is not configured. Please contact your administrator.'));
    }
}
