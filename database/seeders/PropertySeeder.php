<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Concerns\SeedsDemoData;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        $landlord = User::demoLandlords()->first();

        if (! $landlord) {
            $this->command?->warn('PropertySeeder skipped: no demo landlord found.');

            return;
        }

        $created = $this->seedDemoProperties($landlord, array_keys(DemoDataset::properties()));

        if (isset($created[0])) {
            $this->seedDemoUnits($created[0]);
        }
    }
}
