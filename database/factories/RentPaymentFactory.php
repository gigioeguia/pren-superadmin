<?php

namespace Database\Factories;

use App\Models\Rent;
use App\Models\RentPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentPayment>
 */
class RentPaymentFactory extends Factory
{
    protected $model = RentPayment::class;

    public function definition(): array
    {
        $landlord = User::factory()->landlord()->create();
        $tenant = User::factory()->tenant()->create();

        $rent = Rent::create([
            'landlord_id' => $landlord->id,
            'tenant_id' => $tenant->id,
            'monthly_rent' => 100,
            'utility_bill' => 0,
            'total_amount' => 100,
            'status' => 'active',
        ]);

        return [
            'rent_id' => $rent->id,
            'monthly_rent' => 100,
            'utility_bill' => 0,
            'charge' => 0,
            'subtotal_amount' => 100,
            'total_amount' => 100,
            'late_fee' => 0,
            'rent_month' => now()->format('F'),
            'rent_year' => now()->year,
            'payment_status' => 'pending',
        ];
    }
}
