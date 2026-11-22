<?php

namespace Tests\Unit\Hygiene;

use PHPUnit\Framework\TestCase;

/**
 * U6 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11):
 * el patrón `$request->except(...)` no se reintroduce en los controladores
 * que escriben `User` (migrados a arrays explícitos).
 */
class UserMassAssignmentArchTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private const USER_WRITING_FILES = [
        'app/Http/Controllers/Admin/UserController.php',
        'app/Http/Controllers/Auth/SignupController.php',
        'app/Http/Controllers/Api/Auth/RegistrationController.php',
        'app/Http/Controllers/Admin/ProfileController.php',
    ];

    public function test_u6_no_request_except_in_user_writers(): void
    {
        foreach (self::USER_WRITING_FILES as $relative) {
            $path = __DIR__.'/../../../'.$relative;

            $this->assertFileExists($path);

            $content = (string) file_get_contents($path);

            $this->assertStringNotContainsString(
                '$request->except(',
                $content,
                "Patrón except() reintroducido en {$relative} (spec 5.11, Q13)"
            );
        }
    }
}
