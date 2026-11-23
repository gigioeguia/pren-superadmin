<?php

namespace Tests\Unit\Console;

use App\Console\Commands\VerifyDeploy;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * U20–U21 del spec 5.20 (PHP 8.4): helper puro del floor de runtime.
 * La rama FAIL del check no es simulable por config() (PHP_VERSION es
 * verdad del runtime), por eso vive aquí y no en VerifyDeployTest.
 */
#[Group('smoke')]
class VerifyDeployPhpFloorTest extends TestCase
{
    public function test_u20_versions_below_floor_fail(): void
    {
        $this->assertFalse(VerifyDeploy::phpFloorPasses('8.3.29'));
        $this->assertFalse(VerifyDeploy::phpFloorPasses('8.3.99'));
        $this->assertFalse(VerifyDeploy::phpFloorPasses('8.4.0'));
    }

    public function test_u21_versions_at_or_above_floor_pass(): void
    {
        $this->assertTrue(VerifyDeploy::phpFloorPasses('8.4.1'));
        $this->assertTrue(VerifyDeploy::phpFloorPasses('8.4.27'));
        $this->assertTrue(VerifyDeploy::phpFloorPasses('8.5.3'));
        $this->assertTrue(VerifyDeploy::phpFloorPasses('8.10.0'));
    }
}
