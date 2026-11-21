<?php

namespace Database\Seeders;

use App\Models\PropertyDetail;
use App\Models\Rent;
use App\Models\User;
use Illuminate\Database\Seeder;

class RentSeeder extends Seeder
{
    public function run(): void
    {
        $landlord = User::demoLandlords()->first();
        $tenant = User::whereEmail('tenant@acnoo.com')->first();
        $propertyId = PropertyDetail::where('property_info->property_title', 'New House')->value('property_id');

        if (! $landlord || ! $tenant || ! $propertyId) {
            $this->command?->warn('RentSeeder skipped: demo landlord, demo tenant or property "New House" not found.');

            return;
        }

        Rent::firstOrCreate([
            'landlord_id' => $landlord->id,
            'tenant_id' => $tenant->id,
            'property_id' => $propertyId,
        ], [
            'unit_id' => null,
            'monthly_rent' => '5000.00',
            'utility_bill' => '1000.00',
            'total_amount' => '6000.00',
            'start_date' => '2025-10-09',
            'end_date' => '2025-10-31',
            'landlord_agreement' => null,
            'tenant_agreement' => null,
            'status' => 'active',
            'cancel_info' => null,
            'created_at' => '2025-10-09 08:45:40',
            'updated_at' => '2025-10-09 08:45:40',
        ]);
    }
}
