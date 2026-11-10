<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subscriptionName' => 'Standard Plan',
            'duration' => 1,
            'duration_type' => 'month',
            'offerPrice' => 8,
            'subscriptionPrice' => 10,
            'number_of_property' => -1,
            'number_of_tenant' => -1,
            'number_of_labor' => -1,
            'features' => [],
            'status' => 1,
        ];
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscriptionName' => 'Free Plan',
            'offerPrice' => 0,
            'subscriptionPrice' => 0,
        ]);
    }
}
