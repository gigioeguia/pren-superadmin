<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserDetail;
use App\Support\DemoCredentials;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $landlord = User::whereEmail('landlord@example.com')->first();

        if (! $landlord) {
            $landlord = User::factory()->demoLandlord()->create();
            $this->announceGeneratedPassword('landlord@example.com');
        }

        $tenant = User::whereEmail('tenant@example.com')->first();

        if (! $tenant) {
            $tenant = User::factory()->demoTenant()->create(['landlord_id' => $landlord->id]);
            $this->announceGeneratedPassword('tenant@example.com');
        }

        UserDetail::updateOrCreate(['user_id' => $landlord->id], [
            'mykad' => [
                'front_image' => 'uploads/25/05-240/1747818654.svg',
                'back_image' => 'uploads/25/05-317/1747818712.svg',
            ],
            'gender' => 'Male',
            'birth_date' => '2007-07-08',
            'mykad_id' => '252354556',
            'lang' => 'en',
            'company_info' => ['company_name' => 'Tecno Facts', 'company_ssm_no' => '567545'],
            'address_info' => [
                'address_one' => 'Sherpur',
                'address_two' => 'Dhaka',
                'postal_code' => '893467',
                'city' => 'dhaka',
                'state' => 'dhaka',
                'country' => 'Bangladesh',
            ],
            'nominee_info' => null,
            'emergency_contact' => null,
            'workplace' => null,
            'vehicles_info' => null,
            'created_at' => '2024-12-10 06:17:29',
            'updated_at' => '2024-12-10 06:46:36',
        ]);

        UserDetail::updateOrCreate(['user_id' => $tenant->id], [
            'mykad' => ['front_image' => null, 'back_image' => null],
            'gender' => 'Male',
            'birth_date' => '1993-01-25',
            'mykad_id' => 'Fugit ex aspernatur',
            'lang' => 'en',
            'company_info' => ['company_name' => 'Donovan Mckee Associates', 'company_ssm_no' => '76'],
            'address_info' => [
                'address_one' => 'In ab aut laboriosam',
                'address_two' => 'Ratione deserunt tot',
                'postal_code' => '34',
                'city' => 'Quasi quas exercitat',
                'state' => 'Neque ab ut omnis ut',
                'country' => 'Lebanon',
            ],
            'nominee_info' => [
                'name' => 'Sarah Mathis',
                'email' => 'qasixicat@mailinator.com',
                'mobile_code' => '+350',
                'mobile_no' => '+1 (259) 851-6892',
            ],
            'emergency_contact' => [
                'relation_with' => 'Parent',
                'name' => 'Yoshio Mcdowell',
                'mobile_code' => '+47',
                'mobile_no' => '+1 (379) 723-5981',
            ],
            'workplace' => [
                'company_name' => 'Snow and Olson Associates',
                'address_one' => 'Corrupti deserunt a',
                'address_two' => 'Nisi quaerunt earum i',
                'postal_code' => '72',
                'city' => 'Rerum sequi quo opti',
                'state' => 'Quia est exercitati',
                'country' => 'Kenya',
                'office_phone' => '+1 (418) 254-4709',
                'mobile_code' => '+500',
            ],
            'vehicles_info' => [
                ['type' => 'Car', 'reg_no' => '72', 'brand' => 'Quo quae quod et rep'],
            ],
            'created_at' => '2025-09-23 10:00:13',
            'updated_at' => '2025-09-23 10:00:13',
        ]);
    }

    /**
     * Imprime el password demo solo si la cuenta se acaba de crear y el
     * valor fue generado (config vacía); re-seed silencioso (Fase 5 / 5.2).
     */
    private function announceGeneratedPassword(string $email): void
    {
        if ($generated = DemoCredentials::generatedFor($email)) {
            $this->command?->info("[demo] {$email} / {$generated} (password aleatorio: no se vuelve a mostrar)");
        }
    }
}
