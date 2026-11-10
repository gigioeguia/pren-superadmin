<?php

namespace Database\Seeders;

use App\Models\Income;
use App\Models\IncomeCategory;
use Illuminate\Database\Seeder;

class IncomeSeeder extends Seeder
{
    public function run(): void
    {
        $income_categories = array(
            array('landlord_id' => '3', 'name' => 'Rent Payment', 'description' => 'Monthly rental income from tenants.', 'status' => '1', 'created_at' => '2025-10-15 12:04:01', 'updated_at' => '2025-10-15 12:04:01'),
            array('landlord_id' => '3', 'name' => 'Security Deposit', 'description' => 'Refundable or non-refundable deposit collected at lease signing', 'status' => '1', 'created_at' => '2025-10-15 12:04:16', 'updated_at' => '2025-10-15 12:04:16'),
            array('landlord_id' => '3', 'name' => 'Maintenance Fees', 'description' => 'Additional charges for property upkeep or repairs.', 'status' => '1', 'created_at' => '2025-10-15 12:04:28', 'updated_at' => '2025-10-15 12:04:28'),
            array('landlord_id' => '3', 'name' => 'Advertising Income', 'description' => 'Earnings from billboard or sign placements on the property.', 'status' => '1', 'created_at' => '2025-10-15 12:04:42', 'updated_at' => '2025-10-15 12:04:42'),
            array('landlord_id' => '3', 'name' => 'Miscellaneous Income', 'description' => 'Other property-related revenue sources not listed above.', 'status' => '1', 'created_at' => '2025-10-15 12:05:44', 'updated_at' => '2025-10-15 12:05:44'),

            array('landlord_id' => NULL, 'name' => 'Rent Payment', 'description' => 'Monthly rental income from tenants.', 'status' => '1', 'created_at' => '2025-10-15 12:04:01', 'updated_at' => '2025-10-15 12:04:01'),
            array('landlord_id' => NULL, 'name' => 'Security Deposit', 'description' => 'Refundable or non-refundable deposit collected at lease signing', 'status' => '1', 'created_at' => '2025-10-15 12:04:16', 'updated_at' => '2025-10-15 12:04:16'),
            array('landlord_id' => NULL, 'name' => 'Maintenance Fees', 'description' => 'Additional charges for property upkeep or repairs.', 'status' => '1', 'created_at' => '2025-10-15 12:04:28', 'updated_at' => '2025-10-15 12:04:28'),
            array('landlord_id' => NULL, 'name' => 'Advertising Income', 'description' => 'Earnings from billboard or sign placements on the property.', 'status' => '1', 'created_at' => '2025-10-15 12:04:42', 'updated_at' => '2025-10-15 12:04:42'),
            array('landlord_id' => NULL, 'name' => 'Miscellaneous Income', 'description' => 'Other property-related revenue sources not listed above.', 'status' => '1', 'created_at' => '2025-10-15 12:05:44', 'updated_at' => '2025-10-15 12:05:44')
        );

        IncomeCategory::insert($income_categories);

        $incomes = array(
            array('amount' => '1200', 'income_category_id' => '1', 'landlord_id' => '3', 'income_for' => 'Tenant rent', 'payment_type' => 'card', 'reference_no' => 'TNR-3353', 'note' => NULL, 'income_date' => '2025-10-10 00:00:00', 'created_at' => '2025-10-15 12:07:47', 'updated_at' => '2025-10-15 12:08:46'),
            array('amount' => '800', 'income_category_id' => '2', 'landlord_id' => '3', 'income_for' => 'Rent for Security Deposit', 'payment_type' => 'cash', 'reference_no' => 'SDP-3465', 'note' => NULL, 'income_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:09:26', 'updated_at' => '2025-10-15 12:09:26'),
            array('amount' => '200.5', 'income_category_id' => '3', 'landlord_id' => '3', 'income_for' => 'House maintenance', 'payment_type' => 'mobile_payment', 'reference_no' => 'HMT-6674', 'note' => NULL, 'income_date' => '2025-10-16 00:00:00', 'created_at' => '2025-10-15 12:10:13', 'updated_at' => '2025-10-15 12:10:13'),
            
            array('amount' => '1200', 'income_category_id' => '1', 'landlord_id' => NULL, 'income_for' => 'Tenant rent', 'payment_type' => 'card', 'reference_no' => 'TNR-3353', 'note' => NULL, 'income_date' => '2025-10-10 00:00:00', 'created_at' => '2025-10-15 12:07:47', 'updated_at' => '2025-10-15 12:08:46'),
            array('amount' => '800', 'income_category_id' => '2', 'landlord_id' => NULL, 'income_for' => 'Rent for Security Deposit', 'payment_type' => 'cash', 'reference_no' => 'SDP-3465', 'note' => NULL, 'income_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:09:26', 'updated_at' => '2025-10-15 12:09:26'),
            array('amount' => '200.5', 'income_category_id' => '3', 'landlord_id' => NULL, 'income_for' => 'House maintenance', 'payment_type' => 'mobile_payment', 'reference_no' => 'HMT-6674', 'note' => NULL, 'income_date' => '2025-10-16 00:00:00', 'created_at' => '2025-10-15 12:10:13', 'updated_at' => '2025-10-15 12:10:13')
        );

        Income::insert($incomes);
    }
}
