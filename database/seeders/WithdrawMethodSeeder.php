<?php

namespace Database\Seeders;

use App\Models\WithdrawMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WithdrawMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $withdraw_methods = array(
            array('name' => 'Bank', 'currency_id' => '4', 'min_amount' => '500', 'max_amount' => '10000', 'charge' => '20', 'charge_type' => 'fixed', 'instructions' => 'sdsd', 'meta' => '{"label":["Bank Name","Account Holder Name","Swift Code","Branch"],"input":["bank_name","account_name","swift_code","branch"]}', 'status' => '1', 'created_at' => '2025-01-11 05:47:58', 'updated_at' => '2025-01-11 05:47:58'),
        );

        WithdrawMethod::insert($withdraw_methods);
    }
}
