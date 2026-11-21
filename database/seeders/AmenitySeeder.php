<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\User;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $landlord = User::demoLandlords()->first();

        if (! $landlord) {
            $this->command?->warn('AmenitySeeder skipped: no demo landlord found.');

            return;
        }

        $amenities = [
            ['name' => 'Air-Cond', 'status' => 1],
            ['name' => 'Cooking Allowed', 'status' => 1],
            ['name' => 'Near KTM/LRT', 'status' => 1],
            ['name' => 'Washing Machine', 'status' => 1],
            ['name' => 'Internet', 'status' => 1],
        ];

        foreach ($amenities as $amenity) {
            Amenity::firstOrCreate([
                'name' => $amenity['name'],
                'landlord_id' => $landlord->id,
            ], ['status' => $amenity['status']]);
        }
    }
}
