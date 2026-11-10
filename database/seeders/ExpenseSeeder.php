<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $expense_categories = array(
            array('landlord_id' => '3', 'name' => 'Maintenance', 'description' => 'Costs for building upkeep, plumbing, electrical, and general repairs.', 'status' => '1', 'created_at' => '2025-10-15 12:16:15', 'updated_at' => '2025-10-15 12:16:15'),
            array('landlord_id' => '3', 'name' => 'Utility Expenses', 'description' => 'Payments for water, electricity, gas, and internet for common areas.', 'status' => '1', 'created_at' => '2025-10-15 12:16:29', 'updated_at' => '2025-10-15 12:16:29'),
            array('landlord_id' => '3', 'name' => 'Property Taxes', 'description' => 'Annual or monthly government property tax payments.', 'status' => '1', 'created_at' => '2025-10-15 12:16:42', 'updated_at' => '2025-10-15 12:16:42'),
            array('landlord_id' => '3', 'name' => 'Salary', 'description' => 'Payments for property managers, security, janitors, and other staff.', 'status' => '1', 'created_at' => '2025-10-15 12:17:02', 'updated_at' => '2025-10-15 12:17:02'),
            array('landlord_id' => '3', 'name' => 'Cleaning & Sanitation', 'description' => 'Expenses for cleaning services, garbage disposal, and pest control.', 'status' => '1', 'created_at' => '2025-10-15 12:17:16', 'updated_at' => '2025-10-15 12:17:16'),
            array('landlord_id' => '3', 'name' => 'Repairs', 'description' => 'Costs for fixing or replacing appliances and furnishings.', 'status' => '1', 'created_at' => '2025-10-15 12:17:31', 'updated_at' => '2025-10-15 12:17:31'),
            array('landlord_id' => '3', 'name' => 'Security Services', 'description' => 'Payments for security personnel, CCTV maintenance, and alarm systems.', 'status' => '1', 'created_at' => '2025-10-15 12:18:00', 'updated_at' => '2025-10-15 12:18:00'),
            array('landlord_id' => '3', 'name' => 'Labor Salaries & Wages', 'description' => 'Monthly rental expense from tenants.', 'status' => '1', 'created_at' => '2025-10-15 12:18:28', 'updated_at' => '2025-10-15 12:18:28'),

            array('landlord_id' => NULL, 'name' => 'Maintenance', 'description' => 'Costs for building upkeep, plumbing, electrical, and general repairs.', 'status' => '1', 'created_at' => '2025-10-15 12:16:15', 'updated_at' => '2025-10-15 12:16:15'),
            array('landlord_id' => NULL, 'name' => 'Utility Expenses', 'description' => 'Payments for water, electricity, gas, and internet for common areas.', 'status' => '1', 'created_at' => '2025-10-15 12:16:29', 'updated_at' => '2025-10-15 12:16:29'),
            array('landlord_id' => NULL, 'name' => 'Property Taxes', 'description' => 'Annual or monthly government property tax payments.', 'status' => '1', 'created_at' => '2025-10-15 12:16:42', 'updated_at' => '2025-10-15 12:16:42'),
            array('landlord_id' => NULL, 'name' => 'Salary', 'description' => 'Payments for property managers, security, janitors, and other staff.', 'status' => '1', 'created_at' => '2025-10-15 12:17:02', 'updated_at' => '2025-10-15 12:17:02'),
            array('landlord_id' => NULL, 'name' => 'Cleaning & Sanitation', 'description' => 'Expenses for cleaning services, garbage disposal, and pest control.', 'status' => '1', 'created_at' => '2025-10-15 12:17:16', 'updated_at' => '2025-10-15 12:17:16'),
            array('landlord_id' => NULL, 'name' => 'Repairs', 'description' => 'Costs for fixing or replacing appliances and furnishings.', 'status' => '1', 'created_at' => '2025-10-15 12:17:31', 'updated_at' => '2025-10-15 12:17:31'),
            array('landlord_id' => NULL, 'name' => 'Security Services', 'description' => 'Payments for security personnel, CCTV maintenance, and alarm systems.', 'status' => '1', 'created_at' => '2025-10-15 12:18:00', 'updated_at' => '2025-10-15 12:18:00'),
            array('landlord_id' => NULL, 'name' => 'Labor Salaries & Wages', 'description' => 'Monthly rental expense from tenants.', 'status' => '1', 'created_at' => '2025-10-15 12:18:28', 'updated_at' => '2025-10-15 12:18:28')
        );

        ExpenseCategory::insert($expense_categories);

        $expenses = array(
            array('amount' => '1200', 'expense_category_id' => '1', 'landlord_id' => '3', 'expense_for' => 'Property Maintenance', 'payment_type' => 'mobile_payment', 'reference_no' => 'PYM-3452', 'note' => NULL, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:19:40', 'updated_at' => '2025-10-15 12:19:40'),
            array('amount' => '800', 'expense_category_id' => '2', 'landlord_id' => '3', 'expense_for' => 'Property Utility Expenses', 'payment_type' => 'cash', 'reference_no' => 'PUE-8732', 'note' => NULL, 'expense_date' => '2025-10-10 00:00:00', 'created_at' => '2025-10-15 12:20:20', 'updated_at' => '2025-10-15 12:20:20'),
            array('amount' => '250', 'expense_category_id' => '3', 'landlord_id' => '3', 'expense_for' => 'Property Tax', 'payment_type' => 'cash', 'reference_no' => 'PTM-3452', 'note' => NULL, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:20:55', 'updated_at' => '2025-10-15 12:20:55'),
            array('amount' => '1200', 'expense_category_id' => '8', 'landlord_id' => '3', 'expense_for' => 'Labor salary', 'payment_type' => 'bank', 'reference_no' => 'LSW-3453', 'note' => NULL, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:24:55', 'updated_at' => '2025-10-15 12:24:55'),
            array('amount' => '700', 'expense_category_id' => '5', 'landlord_id' => '3', 'expense_for' => 'Property cleaning', 'payment_type' => 'due', 'reference_no' => 'PCS-4623', 'note' => NULL, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:25:31', 'updated_at' => '2025-10-15 12:25:31'),

            array('amount' => '1200', 'expense_category_id' => '1', 'landlord_id' => NULL, 'expense_for' => 'Property Maintenance', 'payment_type' => 'mobile_payment', 'reference_no' => 'PYM-3452', 'note' => NULL, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:19:40', 'updated_at' => '2025-10-15 12:19:40'),
            array('amount' => '800', 'expense_category_id' => '2', 'landlord_id' => NULL, 'expense_for' => 'Property Utility Expenses', 'payment_type' => 'cash', 'reference_no' => 'PUE-8732', 'note' => NULL, 'expense_date' => '2025-10-10 00:00:00', 'created_at' => '2025-10-15 12:20:20', 'updated_at' => '2025-10-15 12:20:20'),
            array('amount' => '250', 'expense_category_id' => '3', 'landlord_id' => NULL, 'expense_for' => 'Property Tax', 'payment_type' => 'cash', 'reference_no' => 'PTM-3452', 'note' => NULL, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:20:55', 'updated_at' => '2025-10-15 12:20:55'),
            array('amount' => '1200', 'expense_category_id' => '8', 'landlord_id' => NULL, 'expense_for' => 'Labor salary', 'payment_type' => 'bank', 'reference_no' => 'LSW-3453', 'note' => NULL, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:24:55', 'updated_at' => '2025-10-15 12:24:55'),
            array('amount' => '700', 'expense_category_id' => '5', 'landlord_id' => NULL, 'expense_for' => 'Property cleaning', 'payment_type' => 'due', 'reference_no' => 'PCS-4623', 'note' => NULL, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:25:31', 'updated_at' => '2025-10-15 12:25:31')
        );

        Expense::insert($expenses);
    }
}
