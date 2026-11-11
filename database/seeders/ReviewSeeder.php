<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = [
            ['property_id' => '3', 'tenant_id' => '4', 'rent_id' => '1', 'review' => '4.4', 'comment' => 'Good Apartment', 'status' => '1', 'created_at' => '2025-10-09 08:47:30', 'updated_at' => '2025-10-09 08:47:30'],
            ['property_id' => '3', 'tenant_id' => '4', 'rent_id' => '1', 'review' => '4.4', 'comment' => 'Landlord service was good', 'status' => '1', 'created_at' => '2025-10-09 08:47:59', 'updated_at' => '2025-10-09 08:47:59'],
        ];

        Review::insert($reviews);
    }
}
