<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = array(
            array('subscriptionName' => 'Free', 'duration' => '17', 'duration_type' => 'day', 'offerPrice' => '0.00', 'subscriptionPrice' => '0.00', 'number_of_property' => '5', 'number_of_tenant' => '5', 'number_of_labor' => '5', 'features' => '[{"name":"Lifetime Free Update","status":1},{"name":"6 months technical support","status":1},{"name":"WhatsApp & Skype support","status":1},{"name":"Live support","status":1},{"name":"Free installation","status":0},{"name":"Free installation Cpanel","status":0},{"name":"Advance Remote Support","status":0}]', 'image' => NULL, 'status' => '1', 'created_at' => now(), 'updated_at' => now()),
            array('subscriptionName' => 'Standard', 'duration' => '1', 'duration_type' => 'month', 'offerPrice' => '50.00', 'subscriptionPrice' => '60.00', 'number_of_property' => '20', 'number_of_tenant' => '20', 'number_of_labor' => '20', 'features' => '[{"name":"Lifetime Free Update","status":1},{"name":"6 months technical support","status":1},{"name":"WhatsApp & Skype support","status":1},{"name":"Live support","status":1},{"name":"Free installation","status":1},{"name":"Free installation Cpanel","status":0},{"name":"Advance Remote Support","status":0}]', 'image' => NULL, 'status' => '1', 'created_at' => now(), 'updated_at' => now()),
            array('subscriptionName' => 'Economy', 'duration' => '1', 'duration_type' => 'year', 'offerPrice' => '100.00', 'subscriptionPrice' => '120.00', 'number_of_property' => '-1', 'number_of_tenant' => '-1', 'number_of_labor' => '-1', 'features' => '[{"name":"Lifetime Free Update","status":1},{"name":"6 months technical support","status":1},{"name":"WhatsApp & Skype support","status":1},{"name":"Live support","status":1},{"name":"Free installation","status":1},{"name":"Free installation Cpanel","status":1},{"name":"Advance Remote Support","status":1}]', 'image' => NULL, 'status' => '1', 'created_at' => now(), 'updated_at' => now())
        );

        Plan::insert($plans);
    }
}
