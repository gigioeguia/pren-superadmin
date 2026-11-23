<?php

namespace Tests\Unit\Console;

use App\Console\Commands\VerifyDeploy;
use PHPUnit\Framework\TestCase;

/**
 * U22–U23 del spec-session-serialization-json-fase5: helper puro del 7º
 * check HARD (misma mecánica de VerifyDeployPhpFloorTest, 5.20) — la
 * rama FAIL no es simulable desde config() y se cubre aquí.
 */
class VerifyDeploySessionTest extends TestCase
{
    public function test_u22_json_serialization_passes(): void
    {
        $this->assertTrue(VerifyDeploy::sessionSerializationPasses('json'));
    }

    public function test_u23_php_or_missing_serialization_fails(): void
    {
        $this->assertFalse(VerifyDeploy::sessionSerializationPasses('php'));
        $this->assertFalse(VerifyDeploy::sessionSerializationPasses(''));
        $this->assertFalse(VerifyDeploy::sessionSerializationPasses(null));
    }
}
