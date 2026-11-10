<?php

namespace Database\Seeders;

use App\Models\PlanSubscribe;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PlanSubscribeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plan_subscribes = array(
            array('user_id' => '3', 'plan_id' => '3', 'gateway_id' => 1, 'plan_name' => 'Economy', 'price' => '0', 'payment_status' => 'unpaid', 'duration' => '365', 'notes' => NULL, 'created_at' => now(), 'updated_at' => now()),
        );

        PlanSubscribe::insert($plan_subscribes);
    }
}
