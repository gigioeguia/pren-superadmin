<?php

namespace Database\Seeders;

use App\Models\Rent;
use Illuminate\Database\Seeder;

class RentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rents = array(
            array('landlord_id' => '3','tenant_id' => '4','property_id' => '3','unit_id' => NULL,'monthly_rent' => '5000.00','utility_bill' => '1000.00','total_amount' => '6000.00','start_date' => '2025-10-09','end_date' => '2025-10-31','landlord_agreement' => NULL,'tenant_agreement' => NULL,'status' => 'active','cancel_info' => NULL,'created_at' => '2025-10-09 08:45:40','updated_at' => '2025-10-09 08:45:40')
          );

          Rent::insert($rents);
    }
}
