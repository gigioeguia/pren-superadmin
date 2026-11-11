<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Property;
use App\Models\PropertyDetail;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $current_date = now();

        $categories = [
            ['name' => 'Apartment / Condominium', 'status' => 1],
            ['name' => 'House', 'status' => 1],
            ['name' => 'Commercial Property', 'status' => 1],
        ];

        $properties = [
            ['puid' => '000001', 'landlord_id' => '3', 'house_type_id' => null, 'building_name' => 'Haji Mansion', 'utility_deposit' => '2500', 'security_deposit' => '12500', 'sale_amount' => '0.00', 'late_fee' => '1000.00', 'late_fee_after_days' => '5', 'cover_image' => ['uploads/25/11/1762053428_6906cd34c0fef.png', 'uploads/25/11/1762053428_6906cd34c170a.png', 'uploads/25/11/1762053428_6906cd34c1c2d.jpg', 'uploads/25/11/1762053428_6906cd34c1fde.png', 'uploads/25/11/1762054011_6906cf7b4b484.png'], 'room_info' => ['floor_rang' => '3rd', 'bedroom' => '3', 'bathroom' => '3', 'kitchen' => '1', 'balcony' => '3', 'condition' => 'Renovated'], 'status' => '1', 'type' => 'rent', 'rent_info' => ['completion_year' => '2016', 'monthly_rent' => '24500', 'min_rental_value' => '1', 'min_rental_unit' => 'months', 'security_deposit_value' => '2', 'security_deposit_unit' => 'months'], 'address_info' => ['address' => 'Mirpur', 'country' => 'Bangladesh', 'state' => 'Dhaka', 'city' => 'Dhaka', 'postcode' => '1217'], 'meta' => null, 'created_at' => $current_date, 'updated_at' => $current_date],
            ['puid' => '000002', 'landlord_id' => '3', 'house_type_id' => '4', 'building_name' => null, 'utility_deposit' => '4500', 'security_deposit' => '35000', 'sale_amount' => '0.00', 'late_fee' => '1000.00', 'late_fee_after_days' => '5', 'cover_image' => ['uploads/25/11/1762053947_6906cf3b04e83.png', 'uploads/25/11/1762053947_6906cf3b05521.png', 'uploads/25/11/1762053947_6906cf3b05a85.png', 'uploads/25/11/1762053947_6906cf3b05f34.png', 'uploads/25/11/1762054036_6906cf94b8b02.png'], 'room_info' => ['bedroom' => '3', 'bathroom' => '3', 'kitchen' => '1', 'balcony' => '3', 'condition' => 'Renovated'], 'status' => '1', 'type' => 'rent', 'rent_info' => ['completion_year' => '2022', 'monthly_rent' => '150000', 'min_rental_value' => '1', 'min_rental_unit' => 'months', 'security_deposit_value' => '3', 'security_deposit_unit' => 'months'], 'address_info' => ['address' => 'Mirpur', 'country' => 'Bangladesh', 'state' => 'Dhaka', 'city' => 'Dhaka', 'postcode' => '1217'], 'meta' => null, 'created_at' => $current_date, 'updated_at' => $current_date],
            ['puid' => '000003', 'landlord_id' => '3', 'house_type_id' => null, 'building_name' => null, 'utility_deposit' => '4000', 'security_deposit' => '17500', 'sale_amount' => '0.00', 'late_fee' => '1000.00', 'late_fee_after_days' => '5', 'cover_image' => ['uploads/25/11/1762054210_6906d042af11d.png', 'uploads/25/11/1762054210_6906d042afeaf.png', 'uploads/25/11/1762054210_6906d042b07e1.png', 'uploads/25/11/1762054210_6906d042b11c6.jpg', 'uploads/25/11/1762054210_6906d042b180b.jpg'], 'room_info' => ['condition' => 'New'], 'status' => '1', 'type' => 'rent', 'rent_info' => ['completion_year' => '2023', 'monthly_rent' => '35000', 'min_rental_value' => '6', 'min_rental_unit' => 'months', 'security_deposit_value' => '25', 'security_deposit_unit' => 'months'], 'address_info' => ['address' => 'Mirpur', 'country' => 'Bangladesh', 'state' => 'Dhaka', 'city' => 'Dhaka', 'postcode' => '1217'], 'meta' => null, 'created_at' => $current_date, 'updated_at' => $current_date],
        ];

        $property_details = [
            ['furnishing' => 'Fully Furnished', 'facilities' => ['1', '2', '3', '5', '11', '13'], 'parking_lot' => '1', 'amenities' => ['1', '3', '5'], 'full_name' => 'Shakil', 'phone' => ['mobile_code' => '+880', 'mobile_no' => '123123123'], 'email' => 'shakil@gmail.com', 'floorplan_image' => 'uploads/25/11-651/1762053428.png', 'residential_type' => 'Apartment', 'property_type' => null, 'property_info' => ['property_title' => 'New Apartment', 'property_description' => 'New Apartment', 'unit_number' => '3F', 'property_size' => '1600', 'lot_number' => '6BX1'], 'tenant_preference' => ['male', 'couple'], 'created_at' => $current_date, 'updated_at' => $current_date],
            ['furnishing' => 'Fully Furnished', 'facilities' => ['1', '2', '4', '5', '8', '11'], 'parking_lot' => '3', 'amenities' => ['1', '2', '3', '4', '5'], 'full_name' => 'Shakil', 'phone' => ['mobile_code' => '+880', 'mobile_no' => '123123123'], 'email' => 'shakil@gmail.com', 'floorplan_image' => 'uploads/25/11-712/1762053947.png', 'residential_type' => null, 'property_type' => null, 'property_info' => ['property_title' => 'New House', 'property_description' => 'New House', 'property_size' => '2500', 'lot_number' => '6XBH1'], 'tenant_preference' => ['male', 'female'], 'created_at' => $current_date, 'updated_at' => $current_date],
            ['furnishing' => null, 'facilities' => ['1', '2', '3', '8', '9'], 'parking_lot' => '1', 'amenities' => ['1', '3', '5'], 'full_name' => 'Shakil', 'phone' => ['mobile_code' => '+880', 'mobile_no' => '123125123'], 'email' => 'shakil@gmail.com', 'floorplan_image' => 'uploads/25/11-834/1762054210.png', 'residential_type' => null, 'property_type' => 'Retail space', 'property_info' => ['property_title' => 'New Commercial Property', 'property_description' => 'New Commercial Property', 'unit_number' => 'G01', 'property_size' => '2000', 'lot_number' => 'UBX01'], 'tenant_preference' => ['male', 'female', 'couple'], 'created_at' => $current_date, 'updated_at' => $current_date],
        ];

        foreach ($categories as $key => $categoryData) {

            $category = Category::create($categoryData);

            $property = Property::create($properties[$key] + [
                'category_id' => $category->id,
            ]);

            PropertyDetail::create($property_details[$key] + [
                'property_id' => $property->id,
            ]);
        }

        $income_categories = [
            ['landlord_id' => '3', 'name' => 'Rent Payment', 'description' => 'Monthly rental income from tenants.', 'status' => '1', 'created_at' => $current_date, 'updated_at' => $current_date],
            ['landlord_id' => '3', 'name' => 'Security Deposit', 'description' => 'Refundable or non-refundable deposit collected at lease signing', 'status' => '1', 'created_at' => $current_date, 'updated_at' => $current_date],
            ['landlord_id' => '3', 'name' => 'Maintenance Fees', 'description' => 'Additional charges for property upkeep or repairs.', 'status' => '1', 'created_at' => $current_date, 'updated_at' => $current_date],
        ];

        $incomes = [
            ['amount' => '1200', 'landlord_id' => '3', 'income_for' => 'Tenant rent', 'payment_type' => 'card', 'reference_no' => 'TNR-3353', 'note' => null, 'income_date' => '2025-10-10 00:00:00', 'created_at' => $current_date, 'updated_at' => $current_date],
            ['amount' => '800', 'landlord_id' => '3', 'income_for' => 'Rent for Security Deposit', 'payment_type' => 'cash', 'reference_no' => 'SDP-3465', 'note' => null, 'income_date' => '2025-10-15 00:00:00', 'created_at' => $current_date, 'updated_at' => $current_date],
            ['amount' => '200.5', 'landlord_id' => '3', 'income_for' => 'House maintenance', 'payment_type' => 'mobile_payment', 'reference_no' => 'HMT-6674', 'note' => null, 'income_date' => '2025-10-16 00:00:00', 'created_at' => $current_date, 'updated_at' => $current_date],
        ];

        foreach ($income_categories as $key => $income_category) {
            $incomes_category = IncomeCategory::create($income_category);
            Income::create($incomes[$key] + [
                'income_category_id' => $incomes_category->id,
            ]);
        }

        $expense_categories = [
            ['landlord_id' => '3', 'name' => 'Maintenance', 'description' => 'Costs for building upkeep, plumbing, electrical, and general repairs.', 'status' => '1', 'created_at' => $current_date, 'updated_at' => $current_date],
            ['landlord_id' => '3', 'name' => 'Utility Expenses', 'description' => 'Payments for water, electricity, gas, and internet for common areas.', 'status' => '1', 'created_at' => $current_date, 'updated_at' => $current_date],
            ['landlord_id' => '3', 'name' => 'Property Taxes', 'description' => 'Annual or monthly government property tax payments.', 'status' => '1', 'created_at' => $current_date, 'updated_at' => $current_date],
        ];

        $expenses = [
            ['amount' => '1200', 'landlord_id' => '3', 'expense_for' => 'Property Maintenance', 'payment_type' => 'mobile_payment', 'reference_no' => 'PYM-3452', 'note' => null, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => $current_date, 'updated_at' => $current_date],
            ['amount' => '800', 'landlord_id' => '3', 'expense_for' => 'Property Utility Expenses', 'payment_type' => 'cash', 'reference_no' => 'PUE-8732', 'note' => null, 'expense_date' => '2025-10-10 00:00:00', 'created_at' => $current_date, 'updated_at' => $current_date],
            ['amount' => '250', 'landlord_id' => '3', 'expense_for' => 'Property Tax', 'payment_type' => 'cash', 'reference_no' => 'PTM-3452', 'note' => null, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => $current_date, 'updated_at' => $current_date],
        ];

        foreach ($expense_categories as $key => $expense_category) {
            $expenses_category = ExpenseCategory::create($expense_category);
            Expense::create($expenses[$key] + [
                'expense_category_id' => $expenses_category->id,
            ]);
        }
    }
}
