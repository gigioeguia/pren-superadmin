<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $expense_categories = [
            ['landlord_id' => '3', 'name' => 'Maintenance', 'description' => 'Costs for building upkeep, plumbing, electrical, and general repairs.', 'status' => '1', 'created_at' => '2025-10-15 12:16:15', 'updated_at' => '2025-10-15 12:16:15'],
            ['landlord_id' => '3', 'name' => 'Utility Expenses', 'description' => 'Payments for water, electricity, gas, and internet for common areas.', 'status' => '1', 'created_at' => '2025-10-15 12:16:29', 'updated_at' => '2025-10-15 12:16:29'],
            ['landlord_id' => '3', 'name' => 'Property Taxes', 'description' => 'Annual or monthly government property tax payments.', 'status' => '1', 'created_at' => '2025-10-15 12:16:42', 'updated_at' => '2025-10-15 12:16:42'],
            ['landlord_id' => '3', 'name' => 'Salary', 'description' => 'Payments for property managers, security, janitors, and other staff.', 'status' => '1', 'created_at' => '2025-10-15 12:17:02', 'updated_at' => '2025-10-15 12:17:02'],
            ['landlord_id' => '3', 'name' => 'Cleaning & Sanitation', 'description' => 'Expenses for cleaning services, garbage disposal, and pest control.', 'status' => '1', 'created_at' => '2025-10-15 12:17:16', 'updated_at' => '2025-10-15 12:17:16'],
            ['landlord_id' => '3', 'name' => 'Repairs', 'description' => 'Costs for fixing or replacing appliances and furnishings.', 'status' => '1', 'created_at' => '2025-10-15 12:17:31', 'updated_at' => '2025-10-15 12:17:31'],
            ['landlord_id' => '3', 'name' => 'Security Services', 'description' => 'Payments for security personnel, CCTV maintenance, and alarm systems.', 'status' => '1', 'created_at' => '2025-10-15 12:18:00', 'updated_at' => '2025-10-15 12:18:00'],
            ['landlord_id' => '3', 'name' => 'Labor Salaries & Wages', 'description' => 'Monthly rental expense from tenants.', 'status' => '1', 'created_at' => '2025-10-15 12:18:28', 'updated_at' => '2025-10-15 12:18:28'],

            ['landlord_id' => null, 'name' => 'Maintenance', 'description' => 'Costs for building upkeep, plumbing, electrical, and general repairs.', 'status' => '1', 'created_at' => '2025-10-15 12:16:15', 'updated_at' => '2025-10-15 12:16:15'],
            ['landlord_id' => null, 'name' => 'Utility Expenses', 'description' => 'Payments for water, electricity, gas, and internet for common areas.', 'status' => '1', 'created_at' => '2025-10-15 12:16:29', 'updated_at' => '2025-10-15 12:16:29'],
            ['landlord_id' => null, 'name' => 'Property Taxes', 'description' => 'Annual or monthly government property tax payments.', 'status' => '1', 'created_at' => '2025-10-15 12:16:42', 'updated_at' => '2025-10-15 12:16:42'],
            ['landlord_id' => null, 'name' => 'Salary', 'description' => 'Payments for property managers, security, janitors, and other staff.', 'status' => '1', 'created_at' => '2025-10-15 12:17:02', 'updated_at' => '2025-10-15 12:17:02'],
            ['landlord_id' => null, 'name' => 'Cleaning & Sanitation', 'description' => 'Expenses for cleaning services, garbage disposal, and pest control.', 'status' => '1', 'created_at' => '2025-10-15 12:17:16', 'updated_at' => '2025-10-15 12:17:16'],
            ['landlord_id' => null, 'name' => 'Repairs', 'description' => 'Costs for fixing or replacing appliances and furnishings.', 'status' => '1', 'created_at' => '2025-10-15 12:17:31', 'updated_at' => '2025-10-15 12:17:31'],
            ['landlord_id' => null, 'name' => 'Security Services', 'description' => 'Payments for security personnel, CCTV maintenance, and alarm systems.', 'status' => '1', 'created_at' => '2025-10-15 12:18:00', 'updated_at' => '2025-10-15 12:18:00'],
            ['landlord_id' => null, 'name' => 'Labor Salaries & Wages', 'description' => 'Monthly rental expense from tenants.', 'status' => '1', 'created_at' => '2025-10-15 12:18:28', 'updated_at' => '2025-10-15 12:18:28'],
        ];

        ExpenseCategory::insert($expense_categories);

        $expenses = [
            ['amount' => '1200', 'expense_category_id' => '1', 'landlord_id' => '3', 'expense_for' => 'Property Maintenance', 'payment_type' => 'mobile_payment', 'reference_no' => 'PYM-3452', 'note' => null, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:19:40', 'updated_at' => '2025-10-15 12:19:40'],
            ['amount' => '800', 'expense_category_id' => '2', 'landlord_id' => '3', 'expense_for' => 'Property Utility Expenses', 'payment_type' => 'cash', 'reference_no' => 'PUE-8732', 'note' => null, 'expense_date' => '2025-10-10 00:00:00', 'created_at' => '2025-10-15 12:20:20', 'updated_at' => '2025-10-15 12:20:20'],
            ['amount' => '250', 'expense_category_id' => '3', 'landlord_id' => '3', 'expense_for' => 'Property Tax', 'payment_type' => 'cash', 'reference_no' => 'PTM-3452', 'note' => null, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:20:55', 'updated_at' => '2025-10-15 12:20:55'],
            ['amount' => '1200', 'expense_category_id' => '8', 'landlord_id' => '3', 'expense_for' => 'Labor salary', 'payment_type' => 'bank', 'reference_no' => 'LSW-3453', 'note' => null, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:24:55', 'updated_at' => '2025-10-15 12:24:55'],
            ['amount' => '700', 'expense_category_id' => '5', 'landlord_id' => '3', 'expense_for' => 'Property cleaning', 'payment_type' => 'due', 'reference_no' => 'PCS-4623', 'note' => null, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:25:31', 'updated_at' => '2025-10-15 12:25:31'],

            ['amount' => '1200', 'expense_category_id' => '1', 'landlord_id' => null, 'expense_for' => 'Property Maintenance', 'payment_type' => 'mobile_payment', 'reference_no' => 'PYM-3452', 'note' => null, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:19:40', 'updated_at' => '2025-10-15 12:19:40'],
            ['amount' => '800', 'expense_category_id' => '2', 'landlord_id' => null, 'expense_for' => 'Property Utility Expenses', 'payment_type' => 'cash', 'reference_no' => 'PUE-8732', 'note' => null, 'expense_date' => '2025-10-10 00:00:00', 'created_at' => '2025-10-15 12:20:20', 'updated_at' => '2025-10-15 12:20:20'],
            ['amount' => '250', 'expense_category_id' => '3', 'landlord_id' => null, 'expense_for' => 'Property Tax', 'payment_type' => 'cash', 'reference_no' => 'PTM-3452', 'note' => null, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:20:55', 'updated_at' => '2025-10-15 12:20:55'],
            ['amount' => '1200', 'expense_category_id' => '8', 'landlord_id' => null, 'expense_for' => 'Labor salary', 'payment_type' => 'bank', 'reference_no' => 'LSW-3453', 'note' => null, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:24:55', 'updated_at' => '2025-10-15 12:24:55'],
            ['amount' => '700', 'expense_category_id' => '5', 'landlord_id' => null, 'expense_for' => 'Property cleaning', 'payment_type' => 'due', 'reference_no' => 'PCS-4623', 'note' => null, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:25:31', 'updated_at' => '2025-10-15 12:25:31'],
        ];

        Expense::insert($expenses);
    }
}
