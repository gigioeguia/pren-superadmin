<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run()
    {
        $amenities = [
            [
                'landlord_id' => 3,
                'name' => 'Air-Cond',
                'status' => 1,
            ],
            [
                'landlord_id' => 3,
                'name' => 'Cooking Allowed',
                'status' => 1,
            ],
            [
                'landlord_id' => 3,
                'name' => 'Near KTM/LRT',
                'status' => 1,
            ],
            [
                'landlord_id' => 3,
                'name' => 'Washing Machine',
                'status' => 1,
            ],
            [
                'landlord_id' => 3,
                'name' => 'Internet',
                'status' => 1,
            ],
        ];

        Amenity::insert($amenities);
    }
}
