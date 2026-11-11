<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            PermissionSeeder::class,
            CurrencySeeder::class,
            GatewaySeeder::class,
            UserSeeder::class,
            PlanSeeder::class,
            PlanSubscribeSeeder::class,
            CategorySeeder::class,
            HouseTypeSeeder::class,
            FacilitySeeder::class,
            AmenitySeeder::class,
            PropertySeeder::class,
            WithdrawMethodSeeder::class,
            OptionTableSeeder::class,
            RentSeeder::class,
            ReviewSeeder::class,
            BlogSeeder::class,
            FaqSeeder::class,
            FeatureSeeder::class,
            TestimonialSeeder::class,
            ClientSeeder::class,
            IncomeSeeder::class,
            ExpenseSeeder::class,
            HighlightSeeder::class,
            LanguageSeeder::class,
        ]);
    }
}
