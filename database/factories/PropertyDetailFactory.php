<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyDetail>
 */
class PropertyDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'property_info' => [
                'property_title' => fake()->company().' Residence',
            ],
        ];
    }
}
