<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanSubscribe>
 */
class PlanSubscribeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->landlord(),
            'plan_id' => Plan::factory(),
            'plan_name' => 'Standard Plan',
            'price' => 10,
            'duration' => 30,
            'number_of_property' => -1,
            'number_of_tenant' => -1,
            'number_of_labor' => -1,
            'payment_status' => 'paid',
        ];
    }
}
