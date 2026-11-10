<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name' => 'Apartment / Condominium',
                'status' => 1
            ],
            [
                'name' => 'House',
                'status' => 1
            ],
            [
                'name' => 'Commercial Property',
                'status' => 1
            ],
            [
                'name' => 'Land',
                'status' => 1
            ],
            [
                'name' => 'Room',
                'status' => 1
            ],
            [
                'name' => 'Unit / Flat',
                'status' => 1
            ],
            [
                'name' => 'Bungalow',
                'status' => 1
            ],
             [
                'name' => 'Plote',
                'status' => 1
            ]

        ];
        Category::insert($categories);
    }
}
