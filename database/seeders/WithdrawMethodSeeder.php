<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\WithdrawMethod;
use Illuminate\Database\Seeder;

class WithdrawMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usd = Currency::where('code', 'USD')->value('id');

        if (! $usd) {
            $this->command?->warn('WithdrawMethodSeeder skipped: USD currency not found.');

            return;
        }

        WithdrawMethod::firstOrCreate(['name' => 'Bank'], [
            'currency_id' => $usd,
            'min_amount' => '500',
            'max_amount' => '10000',
            'charge' => '20',
            'charge_type' => 'fixed',
            'instructions' => 'sdsd',
            'meta' => [
                'label' => ['Bank Name', 'Account Holder Name', 'Swift Code', 'Branch'],
                'input' => ['bank_name', 'account_name', 'swift_code', 'branch'],
            ],
            'status' => '1',
            'created_at' => '2025-01-11 05:47:58',
            'updated_at' => '2025-01-11 05:47:58',
        ]);
    }
}
