<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserModelTest extends TestCase
{
    public function test_role_is_not_mass_assignable(): void
    {
        $user = new User;

        $this->assertNotContains('role', $user->getFillable());
    }

    public function test_is_demo_is_not_mass_assignable(): void
    {
        $user = new User;

        $this->assertNotContains('is_demo', $user->getFillable());
    }
}
