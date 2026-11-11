<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run()
    {
        $facilities = [
            [
                'landlord_id' => 3,
                'name' => 'Parking',
                'status' => 1,
            ],
            [
                'landlord_id' => 3,
                'name' => 'Security',
                'status' => 1,
            ],
            [
                'landlord_id' => 3,
                'name' => 'Lift',
                'status' => 1,
            ],
            [
                'landlord_id' => 3,
                'name' => 'Swimming Pool',
                'status' => 1,
            ],
            [
                'landlord_id' => 3,
                'name' => 'Playground',
                'status' => 1,
            ],
            [
                'landlord_id' => 3,
                'name' => 'Gymnasium',
                'status' => 1,
            ],
            [
                'landlord_id' => 3,
                'name' => 'Sauna',
                'status' => 1,
            ],
            [
                'landlord_id' => 3,
                'name' => 'Barbeque area',
                'status' => 1,
            ],
            [
                'landlord_id' => 3,
                'name' => 'Minimart',
                'status' => 1,
            ],
            [
                'landlord_id' => 3,
                'name' => 'Multipurpose hall',
                'status' => 1,
            ],
            [
                'landlord_id' => 3,
                'name' => 'Club house',
                'status' => 1,
            ],
            [
                'landlord_id' => 3,
                'name' => 'Tennis Court',
                'status' => 1,
            ],
            [
                'landlord_id' => 3,
                'name' => 'Squash Court',
                'status' => 1,
            ],
        ];
        Facility::insert($facilities);
    }
}
