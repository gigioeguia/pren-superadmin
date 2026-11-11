<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'US Dollar',
            'code' => 'USD',
            'rate' => 1,
            'symbol' => '$',
            'position' => 'left',
            'status' => 1,
            'is_default' => 1,
        ];
    }
}
