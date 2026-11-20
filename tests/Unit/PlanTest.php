<?php

namespace Tests\Unit;

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_u1_duration_in_months(): void
    {
        $plan = Plan::factory()->make(['duration' => 2, 'duration_type' => 'month']);

        $this->assertSame(60, $plan->durationInDays());
    }

    public function test_u2_duration_in_years(): void
    {
        $plan = Plan::factory()->make(['duration' => 1, 'duration_type' => 'year']);

        $this->assertSame(365, $plan->durationInDays());
    }

    public function test_u3_duration_in_days(): void
    {
        $plan = Plan::factory()->make(['duration' => 15, 'duration_type' => 'day']);

        $this->assertSame(15, $plan->durationInDays());
    }

    public function test_u4_unknown_type_returns_zero(): void
    {
        $plan = Plan::factory()->make(['duration' => 2, 'duration_type' => 'week']);

        $this->assertSame(0, $plan->durationInDays());
    }

    public function test_u5_zero_duration(): void
    {
        $plan = Plan::factory()->make(['duration' => 0, 'duration_type' => 'month']);

        $this->assertSame(0, $plan->durationInDays());
    }
}
