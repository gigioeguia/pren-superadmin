<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Concerns\SeedsDemoData;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        $landlord = User::demoLandlords()->first();

        if (! $landlord) {
            $this->command?->warn('ExpenseSeeder skipped: no demo landlord found.');

            return;
        }

        // Dos scopes: set global (landlord_id null) y landlord demo.
        // Cada lote referencia sus PROPIAS categorías (fix cross-tenant, spec 4.10).
        $this->seedDemoExpenses(
            $landlord,
            array_keys(DemoDataset::expenseCategories()),
            array_keys(DemoDataset::expenses()),
            [null, $landlord->id],
        );
    }
}
