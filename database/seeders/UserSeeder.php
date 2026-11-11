<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            ['landlord_id' => null, 'plan_subscribe_id' => null, 'unique_id' => '687324', 'name' => 'Alex', 'role' => 'landlord', 'email' => 'landlord@acnoo.com', 'image' => 'uploads/25/05-946/1748665555.jpeg', 'phone' => '{"mobile_code":"+880","mobile_no":"01933445566"}', 'password' => bcrypt('landlord'), 'is_setupped' => '1', 'balance' => '1000', 'profile_type' => null, 'subscriptionDate' => now(), 'will_expire' => now()->addYears(5), 'email_verified_at' => null, 'remember_token' => null, 'created_at' => now(), 'updated_at' => now()],
            ['landlord_id' => '3', 'plan_subscribe_id' => null, 'unique_id' => '000004', 'name' => 'Britanni Edwards', 'role' => 'tenant', 'email' => 'tenant@acnoo.com', 'image' => null, 'phone' => '{"mobile_code":"+260","mobile_no":"34"}', 'password' => '$2y$10$Ptk1xPUe6hg6Brstl8.XkOkL7arOFQv8N0dd4eDrv1MIF9ZXNW3rW', 'is_setupped' => '1', 'balance' => '0', 'profile_type' => 'company', 'subscriptionDate' => null, 'will_expire' => null, 'email_verified_at' => null, 'remember_token' => null, 'created_at' => '2025-09-23 10:00:13', 'updated_at' => '2025-09-23 10:00:13'],

        ];

        User::insert($users);

        $user_details = [
            ['user_id' => '3', 'mykad' => '{"front_image":"uploads\/25\/05-778\/1748665556.jpeg","back_image":"uploads\/25\/05-903\/1748665556.png"}', 'gender' => 'Male', 'birth_date' => '2007-07-08', 'mykad_id' => '252354556', 'lang' => 'en', 'company_info' => '{"company_name":"Tecno Facts","company_ssm_no":"567545"}', 'address_info' => '{"address_one":"Sherpur","address_two":"Dhaka","postal_code":"893467","city":"dhaka","state":"dhaka","country":"Bangladesh"}', 'nominee_info' => null, 'emergency_contact' => null, 'workplace' => null, 'vehicles_info' => null, 'created_at' => '2024-12-10 06:17:29', 'updated_at' => '2024-12-10 06:46:36'],
            ['user_id' => '4', 'mykad' => '{"front_image":null,"back_image":null}', 'gender' => 'Male', 'birth_date' => '1993-01-25', 'mykad_id' => 'Fugit ex aspernatur', 'lang' => 'en', 'company_info' => '{"company_name":"Donovan Mckee Associates","company_ssm_no":"76"}', 'address_info' => '{"address_one":"In ab aut laboriosam","address_two":"Ratione deserunt tot","postal_code":"34","city":"Quasi quas exercitat","state":"Neque ab ut omnis ut","country":"Lebanon"}', 'nominee_info' => '{"name":"Sarah Mathis","email":"qasixicat@mailinator.com","mobile_code":"+350","mobile_no":"+1 (259) 851-6892"}', 'emergency_contact' => '{"relation_with":"Parent","name":"Yoshio Mcdowell","mobile_code":"+47","mobile_no":"+1 (379) 723-5981"}', 'workplace' => '{"company_name":"Snow and Olson Associates","address_one":"Corrupti deserunt a","address_two":"Nisi quaerat earum i","postal_code":"72","city":"Rerum sequi quo opti","state":"Quia est exercitati","country":"Kenya","office_phone":"+1 (418) 254-4709","mobile_code":"+500"}', 'vehicles_info' => '[{"type":"Car","reg_no":"72","brand":"Quo quae quod et rep"}]', 'created_at' => '2025-09-23 10:00:13', 'updated_at' => '2025-09-23 10:00:13'],

        ];

        UserDetail::insert($user_details);
    }
}
