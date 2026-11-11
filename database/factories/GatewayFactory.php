<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Gateway;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Stubs\StubGateway;

/**
 * @extends Factory<Gateway>
 */
class GatewayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Fake Gateway',
            'mode' => 'sandbox',
            'data' => [],
            'status' => 1,
            'charge' => 0,
            'is_manual' => 0,
            'namespace' => StubGateway::class,
            'min_amount' => 0,
            'max_amount' => 100000,
            'currency_id' => Currency::factory(),
        ];
    }

    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Bank Transfer',
            'is_manual' => 1,
            'instructions' => 'Transfer to ACME bank account',
            'manual_data' => ['account' => ['Bank' => 'ACME', 'Account No' => '123456789']],
        ]);
    }
}
