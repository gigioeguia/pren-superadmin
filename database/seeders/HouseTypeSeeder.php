<?php

namespace Database\Seeders;

use App\Models\HouseType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HouseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $house_types = array(
            array('name' => 'Bungalow House', 'status' => '1', 'created_at' => '2024-12-10 16:43:08', 'updated_at' => '2024-12-10 16:43:08'),
            array('name' => 'Link Bungalow', 'status' => '1', 'created_at' => '2024-12-11 08:56:35', 'updated_at' => '2024-12-11 08:56:35'),
            array('name' => 'Zero-Lot Bungalow', 'status' => '1', 'created_at' => '2024-12-11 08:56:35', 'updated_at' => '2024-12-11 08:56:35'),
            array('name' => 'Semi-Detached House', 'status' => '1', 'created_at' => '2024-12-11 08:57:44', 'updated_at' => '2024-12-11 08:57:44')
        );

        HouseType::insert($house_types);
    }
}
