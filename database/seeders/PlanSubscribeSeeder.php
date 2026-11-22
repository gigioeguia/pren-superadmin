<?php

namespace Database\Seeders;

use App\Models\Gateway;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlanSubscribeSeeder extends Seeder
{
    public function run(): void
    {
        $landlord = User::whereEmail('landlord@example.com')->first();
        $plan = Plan::where('subscriptionName', 'Economy')->first();
        $gateway = Gateway::where('name', 'Stripe')->first();

        if (! $landlord || ! $plan || ! $gateway) {
            $this->command?->warn('PlanSubscribeSeeder skipped: demo landlord, Economy plan or Stripe gateway not found.');

            return;
        }

        PlanSubscribe::firstOrCreate([
            'user_id' => $landlord->id,
            'plan_id' => $plan->id,
        ], [
            'gateway_id' => $gateway->id,
            'plan_name' => 'Economy',
            'price' => '0',
            'payment_status' => 'unpaid',
            'duration' => '365',
            'notes' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
