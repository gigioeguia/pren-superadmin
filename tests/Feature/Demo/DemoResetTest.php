<?php

namespace Tests\Feature\Demo;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class DemoResetTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        app()->detectEnvironment(fn () => 'testing');

        parent::tearDown();
    }

    public function test_u1_noop_outside_demo_environment(): void
    {
        app()->detectEnvironment(fn () => 'testing');

        Artisan::shouldReceive('call')->never();

        $result = demoReset();

        $this->assertFalse($result);
    }

    public function test_u2_runs_in_demo_environment(): void
    {
        app()->detectEnvironment(fn () => 'demo');

        Artisan::shouldReceive('call')
            ->once()
            ->with('db:seed', ['--class' => 'DemoSeeder'])
            ->andReturn(0);

        $result = demoReset();

        $this->assertTrue($result);
    }

    public function test_u3_logs_warning_outside_demo(): void
    {
        app()->detectEnvironment(fn () => 'testing');

        Artisan::shouldReceive('call')->never();
        Log::shouldReceive('warning')->once();

        $result = demoReset();

        $this->assertFalse($result);
    }

    public function test_u4_repeated_calls_in_demo_both_run(): void
    {
        app()->detectEnvironment(fn () => 'demo');

        Artisan::shouldReceive('call')
            ->twice()
            ->with('db:seed', ['--class' => 'DemoSeeder'])
            ->andReturn(0);

        $this->assertTrue(demoReset());
        $this->assertTrue(demoReset());
    }

    public function test_u6_is_demo_cast_to_boolean(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['is_demo' => 1])->save();

        $fresh = $user->fresh();

        $this->assertTrue($fresh->is_demo);
        $this->assertIsBool($fresh->is_demo);
    }
}
