<?php

namespace Database\Seeders;

use App\Models\PropertyDetail;
use App\Models\Rent;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = User::whereEmail('tenant@example.com')->first();
        $propertyId = PropertyDetail::where('property_info->property_title', 'New House')->value('property_id');
        $rent = Rent::where('property_id', $propertyId)->where('tenant_id', $tenant?->id)->first();

        if (! $tenant || ! $propertyId || ! $rent) {
            $this->command?->warn('ReviewSeeder skipped: demo tenant, rent or property "New House" not found.');

            return;
        }

        $reviews = [
            ['review' => '4.4', 'comment' => 'Good Apartment', 'status' => '1', 'created_at' => '2025-10-09 08:47:30', 'updated_at' => '2025-10-09 08:47:30'],
            ['review' => '4.4', 'comment' => 'Landlord service was good', 'status' => '1', 'created_at' => '2025-10-09 08:47:59', 'updated_at' => '2025-10-09 08:47:59'],
        ];

        foreach ($reviews as $review) {
            Review::firstOrCreate(['comment' => $review['comment']], $review + [
                'property_id' => $propertyId,
                'tenant_id' => $tenant->id,
                'rent_id' => $rent->id,
            ]);
        }
    }
}
