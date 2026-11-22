<?php

namespace Tests\Feature\Seeders;

use App\Models\User;
use App\Support\DemoCredentials;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

/**
 * Fase 5 / 5.2 — spec-demo-user-passwords-fase5.
 * U1-U2: helper App\Support\DemoCredentials. F1-F3: seeders.
 */
class DemoUserPasswordTest extends TestCase
{
    use RefreshDatabase;

    private const DEMO_EMAILS = [
        'superadmin@example.com',
        'admin@example.com',
        'landlord@example.com',
        'tenant@example.com',
    ];

    private const LEGACY_PASSWORDS = ['superadmin', 'admin', 'landlord', 'tenant'];

    protected function setUp(): void
    {
        parent::setUp();

        // Aislamiento: cada test define su propio valor de config.
        config(['demo.user_password' => null]);
        DemoCredentials::flush();
    }

    public function test_u1_configured_password_is_returned_verbatim(): void
    {
        // Arrange
        config(['demo.user_password' => 'DemoPass123']);

        // Act
        $password = DemoCredentials::password('user@example.com');
        $repeat = DemoCredentials::password('user@example.com');

        // Assert
        $this->assertSame('DemoPass123', $password);
        $this->assertSame($password, $repeat);
        $this->assertNull(DemoCredentials::generatedFor('user@example.com'));
    }

    public function test_u2_generated_passwords_are_random_and_pass_policy(): void
    {
        // Arrange
        // (config demo.user_password en null por setUp)

        // Act
        $first = DemoCredentials::password('one@example.com');
        $sameEmail = DemoCredentials::password('one@example.com');
        $second = DemoCredentials::password('two@example.com');

        // Assert
        $this->assertSame($first, $sameEmail, 'mismo email debe ser estable en la ejecución');
        $this->assertNotEquals($first, $second);
        foreach ([$first, $second] as $password) {
            $this->assertNotContains($password, self::LEGACY_PASSWORDS);
            $this->assertTrue(
                validator(
                    ['password' => $password],
                    ['password' => ['required', Password::min(8)->mixedCase()->numbers()]]
                )->passes()
            );
        }
    }

    public function test_f1_seeded_demo_passwords_are_not_predictable(): void
    {
        // Arrange
        // (config demo.user_password en null por setUp)

        // Act
        $this->seed(PermissionSeeder::class);
        $this->seed(UserSeeder::class);

        // Assert
        foreach (self::DEMO_EMAILS as $email) {
            $user = User::whereEmail($email)->firstOrFail();
            $legacy = str($email)->before('@')->value();

            $this->assertFalse(Hash::check($legacy, $user->password), "{$email} aún usa el password del rol");

            if ($generated = DemoCredentials::generatedFor($email)) {
                $this->assertTrue(Hash::check($generated, $user->password), "{$email}: el password impreso no abre la cuenta");
            }
        }
    }

    public function test_f2_configured_password_is_applied_to_all_demo_users(): void
    {
        // Arrange
        config(['demo.user_password' => 'DemoPass123']);

        // Act
        $this->seed(PermissionSeeder::class);
        $this->seed(UserSeeder::class);

        // Assert
        foreach (self::DEMO_EMAILS as $email) {
            $user = User::whereEmail($email)->firstOrFail();

            $this->assertTrue(Hash::check('DemoPass123', $user->password), $email);
        }
    }

    public function test_f3_reseed_does_not_change_existing_demo_passwords(): void
    {
        // Arrange
        config(['demo.user_password' => 'DemoPass123']);
        $this->seed(PermissionSeeder::class);
        $this->seed(UserSeeder::class);
        $hashes = User::whereIn('email', self::DEMO_EMAILS)->pluck('password', 'email');
        $this->assertCount(4, $hashes);

        // Act (re-seed con config vacía: las filas existentes no se tocan)
        config(['demo.user_password' => null]);
        DemoCredentials::flush();
        $this->seed(PermissionSeeder::class);
        $this->seed(UserSeeder::class);

        // Assert
        foreach ($hashes as $email => $hash) {
            $this->assertSame($hash, User::whereEmail($email)->value('password'), "{$email} cambió su hash");
            $this->assertSame(1, User::whereEmail($email)->count(), "{$email} duplicado");
        }
    }
}
