<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $landlord = User::demoLandlords()->first();

        if (! $landlord) {
            $this->command?->warn('FacilitySeeder skipped: no demo landlord found.');

            return;
        }

        $facilities = [
            ['name' => 'Parking', 'status' => 1],
            ['name' => 'Security', 'status' => 1],
            ['name' => 'Lift', 'status' => 1],
            ['name' => 'Swimming Pool', 'status' => 1],
            ['name' => 'Playground', 'status' => 1],
            ['name' => 'Gymnasium', 'status' => 1],
            ['name' => 'Sauna', 'status' => 1],
            ['name' => 'Barbeque area', 'status' => 1],
            ['name' => 'Minimart', 'status' => 1],
            ['name' => 'Multipurpose hall', 'status' => 1],
            ['name' => 'Club house', 'status' => 1],
            ['name' => 'Tennis Court', 'status' => 1],
            ['name' => 'Squash Court', 'status' => 1],
        ];

        foreach ($facilities as $facility) {
            Facility::firstOrCreate([
                'name' => $facility['name'],
                'landlord_id' => $landlord->id,
            ], ['status' => $facility['status']]);
        }
    }
}
