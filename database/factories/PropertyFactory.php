<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'landlord_id' => User::factory()->landlord(),
            'building_name' => fake()->optional()->company(),
            'utility_deposit' => 0,
            'security_deposit' => 0,
            'status' => 1,
            'type' => 1,
            'rent_info' => [
                'property_rent' => 1000,
                'monthly_rent' => 1000,
                'min_rental_unit' => 1,
                'completion_year' => 2020,
            ],
            'address_info' => [
                'country' => 'USA',
                'city' => 'New York',
                'state' => 'NY',
                'address' => fake()->streetAddress(),
            ],
            'meta' => [],
        ];
    }
}
