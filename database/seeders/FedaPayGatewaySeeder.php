<?php

namespace Database\Seeders;

use App\Models\Gateway;
use Illuminate\Database\Seeder;

class FedaPayGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gateways = [
            ['name' => 'FedaPay', 'currency_id' => 117, 'mode' => 'Sandbox', 'status' => '1', 'charge' => '2', 'image' => null, 'data' => '{"api_key":"sk_sandbox_wvtmI8ZxXDPtAdpSby0P3INz"}', 'manual_data' => null, 'is_manual' => '0', 'accept_img' => '0', 'namespace' => 'App\\Library\\FedaPayGateway', 'phone_required' => '0', 'instructions' => null, 'min_amount' => 0.1, 'max_amount' => 1000000, 'created_at' => now(), 'updated_at' => now()],
        ];

        Gateway::insert($gateways);
    }
}
