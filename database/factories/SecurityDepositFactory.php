<?php

namespace Database\Factories;

use App\Models\SecurityDeposit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SecurityDeposit>
 */
class SecurityDepositFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => User::factory()->tenant(),
            'landlord_id' => User::factory()->landlord(),
            'deposit_amount' => 100,
            'rent_advance' => 0,
            'utility_advance' => 0,
            'total_amount' => 100,
            'charge' => 0,
            'landlord_balance' => 90,
            'status' => 'pending',
        ];
    }
}
