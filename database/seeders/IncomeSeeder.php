<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Concerns\SeedsDemoData;
use Illuminate\Database\Seeder;

class IncomeSeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        $landlord = User::demoLandlords()->first();

        if (! $landlord) {
            $this->command?->warn('IncomeSeeder skipped: no demo landlord found.');

            return;
        }

        // Dos scopes: set global (landlord_id null) y landlord demo.
        // Cada lote referencia sus PROPIAS categorías (fix cross-tenant, spec 4.10).
        $this->seedDemoIncomes(
            $landlord,
            array_keys(DemoDataset::incomeCategories()),
            array_keys(DemoDataset::incomes()),
            [null, $landlord->id],
        );
    }
}
