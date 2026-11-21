<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Concerns\SeedsDemoData;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    use SeedsDemoData;

    /**
     * Re-siembra el subset demo tras demoReset(). El landlord demo se
     * resuelve por is_demo (spec 4.10 / D4), nunca por id fijo.
     */
    public function run(): void
    {
        $landlord = User::demoLandlords()->first();

        if (! $landlord) {
            $this->command?->warn('DemoSeeder skipped: no demo landlord found.');

            return;
        }

        $this->seedDemoProperties($landlord, [1, 2, 3]);
        $this->seedDemoIncomes($landlord, [0, 1, 2], [0, 1, 2], [$landlord->id]);
        $this->seedDemoExpenses($landlord, [0, 1, 2], [0, 1, 2], [$landlord->id]);
    }
}
