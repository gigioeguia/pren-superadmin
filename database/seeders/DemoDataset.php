<?php

namespace Database\Seeders;

/**
 * Dataset demo compartido (spec 4.10 / Q12): fuente única consumida por
 * PropertySeeder, IncomeSeeder, ExpenseSeeder y DemoSeeder.
 *
 * Referencias por clave natural: categoría y house_type por nombre,
 * facilities/amenities por nombre (los seeders las resuelven a ids en runtime).
 * Las imágenes apuntan solo a assets trackeados en git (pool de fotos + svgs).
 */
class DemoDataset
{
    /** Pool de fotos de propiedades (git-tracked, public/uploads/25/09). */
    public static function propertyImages(): array
    {
        return [
            'uploads/25/09/1758521567_68d0e8dfd7681.jpg',
            'uploads/25/09/1758521567_68d0e8dfd99e7.jpg',
            'uploads/25/09/1758521567_68d0e8dfd9e42.jpg',
            'uploads/25/09/1758521567_68d0e8dfda264.jpg',
            'uploads/25/09/1758521567_68d0e8dfdf96c.jpg',
            'uploads/25/09/1758521567_68d0e8dfe0267.jpg',
            'uploads/25/09/1758521567_68d0e8dfe151a.jpg',
            'uploads/25/09/1758521567_68d0e8dfe1ca1.jpg',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function properties(): array
    {
        $images = self::propertyImages();

        return [
            ['category' => 'Unit / Flat', 'house_type' => null, 'building_name' => null, 'utility_deposit' => '0', 'security_deposit' => '0', 'sale_amount' => '0.00', 'late_fee' => '0.00', 'late_fee_after_days' => '0', 'cover_image' => [$images[0], $images[1], $images[2], $images[3], $images[4]], 'room_info' => ['floor_rang' => null, 'bedroom' => null, 'bathroom' => null, 'kitchen' => null, 'balcony' => null, 'condition' => null, 'room_size' => null], 'status' => '1', 'type' => 'rent', 'rent_info' => ['completion_year' => '2023'], 'address_info' => ['address' => 'Mirpur', 'country' => 'Bangladesh', 'state' => 'Dhaka', 'city' => 'Dhaka', 'postcode' => '1217'], 'meta' => null, 'created_at' => '2025-10-13 09:32:22', 'updated_at' => '2025-11-02 09:03:36'],
            ['category' => 'Apartment / Condominium', 'house_type' => null, 'building_name' => 'Haji Mansion', 'utility_deposit' => '2500', 'security_deposit' => '12500', 'sale_amount' => '0.00', 'late_fee' => '1000.00', 'late_fee_after_days' => '5', 'cover_image' => [$images[5], $images[6], $images[7], $images[0], $images[1]], 'room_info' => ['floor_rang' => '3rd', 'bedroom' => '3', 'bathroom' => '3', 'kitchen' => '1', 'balcony' => '3', 'condition' => 'Renovated'], 'status' => '1', 'type' => 'rent', 'rent_info' => ['completion_year' => '2016', 'monthly_rent' => '24500', 'min_rental_value' => '1', 'min_rental_unit' => 'months', 'security_deposit_value' => '2', 'security_deposit_unit' => 'months'], 'address_info' => ['address' => 'Mirpur', 'country' => 'Bangladesh', 'state' => 'Dhaka', 'city' => 'Dhaka', 'postcode' => '1217'], 'meta' => null, 'created_at' => '2025-10-13 11:03:10', 'updated_at' => '2025-11-02 09:26:51'],
            ['category' => 'House', 'house_type' => 'Semi-Detached House', 'building_name' => null, 'utility_deposit' => '4500', 'security_deposit' => '35000', 'sale_amount' => '0.00', 'late_fee' => '1000.00', 'late_fee_after_days' => '5', 'cover_image' => [$images[2], $images[3], $images[4], $images[5], $images[6]], 'room_info' => ['bedroom' => '3', 'bathroom' => '3', 'kitchen' => '1', 'balcony' => '3', 'condition' => 'Renovated'], 'status' => '1', 'type' => 'rent', 'rent_info' => ['completion_year' => '2022', 'monthly_rent' => '150000', 'min_rental_value' => '1', 'min_rental_unit' => 'months', 'security_deposit_value' => '3', 'security_deposit_unit' => 'months'], 'address_info' => ['address' => 'Mirpur', 'country' => 'Bangladesh', 'state' => 'Dhaka', 'city' => 'Dhaka', 'postcode' => '1217'], 'meta' => null, 'created_at' => '2025-10-13 11:39:43', 'updated_at' => '2025-11-02 09:27:16'],
            ['category' => 'Commercial Property', 'house_type' => null, 'building_name' => null, 'utility_deposit' => '4000', 'security_deposit' => '17500', 'sale_amount' => '0.00', 'late_fee' => '1000.00', 'late_fee_after_days' => '5', 'cover_image' => [$images[7], $images[0], $images[1], $images[2], $images[3]], 'room_info' => ['condition' => 'New'], 'status' => '1', 'type' => 'rent', 'rent_info' => ['completion_year' => '2023', 'monthly_rent' => '35000', 'min_rental_value' => '6', 'min_rental_unit' => 'months', 'security_deposit_value' => '25', 'security_deposit_unit' => 'months'], 'address_info' => ['address' => 'Mirpur', 'country' => 'Bangladesh', 'state' => 'Dhaka', 'city' => 'Dhaka', 'postcode' => '1217'], 'meta' => null, 'created_at' => '2025-10-13 11:56:13', 'updated_at' => '2025-11-02 09:30:10'],
            ['category' => 'Land', 'house_type' => null, 'building_name' => null, 'utility_deposit' => '0', 'security_deposit' => '17500', 'sale_amount' => '0.00', 'late_fee' => '1000.00', 'late_fee_after_days' => '5', 'cover_image' => [$images[4], $images[5], $images[6], $images[7], $images[0]], 'status' => '1', 'type' => 'rent', 'rent_info' => ['completion_year' => '2025', 'monthly_rent' => '35000', 'min_rental_value' => '1', 'min_rental_unit' => 'years', 'security_deposit_value' => '3', 'security_deposit_unit' => 'months'], 'address_info' => ['address' => 'Mirpur', 'country' => 'Bangladesh', 'state' => 'Dhaka', 'city' => 'Dhaka', 'postcode' => '1217'], 'meta' => null, 'created_at' => '2025-10-13 12:05:33', 'updated_at' => '2025-11-02 09:27:41'],
            ['category' => 'Room', 'house_type' => null, 'building_name' => null, 'utility_deposit' => '0', 'security_deposit' => '8500', 'sale_amount' => '0.00', 'late_fee' => '1000.00', 'late_fee_after_days' => '5', 'cover_image' => [$images[1], $images[2], $images[3], $images[4], $images[5]], 'room_info' => ['balcony' => '2', 'condition' => 'New', 'room_size' => '1250'], 'status' => '1', 'type' => 'rent', 'rent_info' => ['completion_year' => '2023', 'monthly_rent' => '17500', 'min_rental_value' => '1', 'min_rental_unit' => 'years', 'security_deposit_value' => '4', 'security_deposit_unit' => 'months'], 'address_info' => ['address' => 'Mirpur', 'country' => 'Bangladesh', 'state' => 'Dhaka', 'city' => 'Dhaka', 'postcode' => '1217'], 'meta' => null, 'created_at' => '2025-10-13 16:50:27', 'updated_at' => '2025-11-02 09:28:21'],
            ['category' => 'Plote', 'house_type' => null, 'building_name' => null, 'utility_deposit' => '0', 'security_deposit' => '700', 'sale_amount' => '0.00', 'late_fee' => '500.00', 'late_fee_after_days' => '5', 'cover_image' => [$images[6], $images[7], $images[0], $images[1], $images[2]], 'status' => '1', 'type' => 'rent', 'rent_info' => ['completion_year' => '2022', 'monthly_rent' => '2500', 'min_rental_value' => '2', 'min_rental_unit' => 'months', 'security_deposit_value' => '1', 'security_deposit_unit' => 'months'], 'address_info' => ['address' => 'Mirpur', 'country' => 'Bangladesh', 'state' => 'Dhaka', 'city' => 'Dhaka', 'postcode' => '1217'], 'meta' => null, 'created_at' => '2025-10-30 09:19:37', 'updated_at' => '2025-11-02 09:31:03'],
            ['category' => 'Bungalow', 'house_type' => null, 'building_name' => null, 'utility_deposit' => '0', 'security_deposit' => '340', 'sale_amount' => '0.00', 'late_fee' => '560.00', 'late_fee_after_days' => '5', 'cover_image' => [$images[3], $images[4], $images[5], $images[6], $images[7]], 'room_info' => ['bedroom' => '2', 'bathroom' => '3', 'condition' => 'good'], 'status' => '1', 'type' => 'rent', 'rent_info' => ['completion_year' => '2021', 'monthly_rent' => '4588', 'min_rental_value' => '2', 'min_rental_unit' => 'months', 'security_deposit_value' => '35', 'security_deposit_unit' => 'months'], 'address_info' => ['address' => 'Mirpur', 'country' => 'Bangladesh', 'state' => 'Dhaka', 'city' => 'Dhaka', 'postcode' => '1217'], 'meta' => null, 'created_at' => '2025-10-30 09:42:31', 'updated_at' => '2025-11-02 09:32:11'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function propertyDetails(): array
    {
        $images = self::propertyImages();

        $allFacilities = ['Parking', 'Security', 'Lift', 'Swimming Pool', 'Playground', 'Gymnasium', 'Sauna', 'Barbeque area', 'Minimart', 'Multipurpose hall', 'Club house', 'Tennis Court', 'Squash Court'];
        $allAmenities = ['Air-Cond', 'Cooking Allowed', 'Near KTM/LRT', 'Washing Machine', 'Internet'];

        return [
            ['furnishing' => 'Fully Furnished', 'parking_lot' => null, 'full_name' => 'Shakil', 'phone' => ['mobile_code' => '+880', 'mobile_no' => '51235123'], 'email' => 'shakil@gmail.com', 'floorplan_image' => $images[0], 'residential_type' => null, 'property_type' => null, 'property_info' => ['property_title' => 'New Unit Property', 'property_description' => 'New Property', 'unit_number' => null, 'property_size' => null, 'land_size' => null, 'lot_number' => null], 'created_at' => '2025-10-13 09:32:22', 'updated_at' => '2025-11-02 09:03:36'],
            ['furnishing' => 'Fully Furnished', 'facilities' => ['Parking', 'Security', 'Lift', 'Playground', 'Club house', 'Squash Court'], 'parking_lot' => '1', 'amenities' => ['Air-Cond', 'Near KTM/LRT', 'Internet'], 'full_name' => 'Shakil', 'phone' => ['mobile_code' => '+880', 'mobile_no' => '123123123'], 'email' => 'shakil@gmail.com', 'floorplan_image' => $images[1], 'residential_type' => 'Apartment', 'property_type' => null, 'property_info' => ['property_title' => 'New Apartment', 'property_description' => 'New Apartment', 'unit_number' => '3F', 'property_size' => '1600', 'lot_number' => '6BX1'], 'tenant_preference' => ['male', 'couple'], 'created_at' => '2025-10-13 11:03:10', 'updated_at' => '2025-11-02 09:26:51'],
            ['furnishing' => 'Fully Furnished', 'facilities' => ['Parking', 'Security', 'Swimming Pool', 'Playground', 'Barbeque area', 'Club house'], 'parking_lot' => '3', 'amenities' => $allAmenities, 'full_name' => 'Shakil', 'phone' => ['mobile_code' => '+880', 'mobile_no' => '123123123'], 'email' => 'shakil@gmail.com', 'floorplan_image' => $images[2], 'residential_type' => null, 'property_type' => null, 'property_info' => ['property_title' => 'New House', 'property_description' => 'New House', 'property_size' => '2500', 'lot_number' => '6XBH1'], 'tenant_preference' => ['male', 'female'], 'created_at' => '2025-10-13 11:39:43', 'updated_at' => '2025-11-02 09:27:16'],
            ['furnishing' => null, 'facilities' => ['Parking', 'Security', 'Lift', 'Barbeque area', 'Minimart'], 'parking_lot' => '1', 'amenities' => ['Air-Cond', 'Near KTM/LRT', 'Internet'], 'full_name' => 'Shakil', 'phone' => ['mobile_code' => '+880', 'mobile_no' => '123125123'], 'email' => 'shakil@gmail.com', 'floorplan_image' => $images[3], 'residential_type' => null, 'property_type' => 'Retail space', 'property_info' => ['property_title' => 'New Commercial Property', 'property_description' => 'New Commercial Property', 'unit_number' => 'G01', 'property_size' => '2000', 'lot_number' => 'UBX01'], 'tenant_preference' => ['male', 'female', 'couple'], 'created_at' => '2025-10-13 11:56:13', 'updated_at' => '2025-11-02 09:30:10'],
            ['furnishing' => null, 'parking_lot' => null, 'amenities' => ['Air-Cond', 'Cooking Allowed', 'Near KTM/LRT'], 'full_name' => 'Shakil', 'phone' => ['mobile_code' => '+880', 'mobile_no' => '12312142123'], 'email' => 'shakil@gmail.com', 'floorplan_image' => $images[4], 'residential_type' => null, 'property_type' => 'Residential', 'property_info' => ['property_title' => 'New Land Property', 'property_description' => 'New Land Property', 'land_size' => '20', 'lot_number' => '86BX1'], 'tenant_preference' => ['female', 'couple'], 'created_at' => '2025-10-13 12:05:33', 'updated_at' => '2025-11-02 09:27:41'],
            ['furnishing' => 'Fully Furnished', 'facilities' => ['Lift', 'Playground', 'Barbeque area', 'Multipurpose hall'], 'parking_lot' => '1', 'amenities' => ['Air-Cond', 'Cooking Allowed', 'Internet'], 'full_name' => 'Shakil', 'phone' => ['mobile_code' => '+880', 'mobile_no' => '5781957151'], 'email' => 'shakil@gmail.com', 'floorplan_image' => $images[5], 'residential_type' => 'Apartment / Flat', 'property_type' => null, 'property_info' => ['property_title' => 'New Room Property', 'property_description' => 'New Room Property'], 'tenant_preference' => ['male', 'female'], 'created_at' => '2025-10-13 16:50:27', 'updated_at' => '2025-11-02 09:28:21'],
            ['furnishing' => null, 'parking_lot' => null, 'amenities' => ['Air-Cond', 'Cooking Allowed', 'Near KTM/LRT', 'Internet'], 'full_name' => 'Shakil', 'phone' => ['mobile_code' => '+880', 'mobile_no' => '123123123'], 'email' => 'shakil@gmail.com', 'floorplan_image' => $images[6], 'residential_type' => null, 'property_type' => 'Residential', 'property_info' => ['property_title' => 'New Plote', 'property_description' => 'New Plote', 'land_size' => '1200', 'lot_number' => '6ZBH1'], 'tenant_preference' => ['male', 'female'], 'created_at' => '2025-10-30 09:19:37', 'updated_at' => '2025-11-02 09:31:03'],
            ['furnishing' => null, 'facilities' => ['Parking', 'Security', 'Lift', 'Swimming Pool', 'Gymnasium', 'Sauna', 'Barbeque area', 'Tennis Court', 'Squash Court'], 'parking_lot' => '3', 'amenities' => ['Cooking Allowed', 'Near KTM/LRT', 'Internet'], 'full_name' => 'Shakil', 'phone' => ['mobile_code' => '+880', 'mobile_no' => '123125123'], 'email' => 'shakil@gmail.com', 'floorplan_image' => $images[7], 'residential_type' => null, 'property_type' => 'Luxury', 'property_info' => ['property_title' => 'New Bungalow', 'property_description' => 'My New Bungalow', 'property_size' => '750'], 'tenant_preference' => ['male', 'female', 'couple'], 'created_at' => '2025-10-30 09:42:31', 'updated_at' => '2025-11-02 09:32:11'],
        ];
    }

    /**
     * Unidades del dataset: pertenecen a la propiedad key 0.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function units(): array
    {
        $images = self::propertyImages();

        $allFacilities = ['Parking', 'Security', 'Lift', 'Swimming Pool', 'Playground', 'Gymnasium', 'Sauna', 'Barbeque area', 'Minimart', 'Multipurpose hall', 'Club house', 'Tennis Court', 'Squash Court'];
        $allAmenities = ['Air-Cond', 'Cooking Allowed', 'Near KTM/LRT', 'Washing Machine', 'Internet'];

        return [
            ['unit_name' => 'A', 'floor_range' => '2nd', 'bedroom' => '12', 'bathroom' => '1', 'kitchen' => '1', 'balcony' => '1', 'condition' => 'New', 'parking_lot' => '1', 'unit_size' => '1250', 'facilities' => $allFacilities, 'amenities' => $allAmenities, 'tenant_preference' => ['female', 'couple'], 'description' => 'South Side', 'images' => [$images[5], $images[6]], 'monthly_rent' => '17500.00', 'sale_amount' => '0.00', 'min_rental_value' => '1', 'min_rental_unit' => 'years', 'security_deposit' => '10500.00', 'security_deposit_value' => '5', 'security_deposit_unit' => 'days', 'utility_deposit' => '2500.00', 'late_fee' => '1000.00', 'late_fee_after_days' => '5', 'created_at' => '2025-10-13 09:32:22', 'updated_at' => '2025-11-02 09:03:36'],
            ['unit_name' => 'C', 'floor_range' => '3rd', 'bedroom' => '2', 'bathroom' => '2', 'kitchen' => '1', 'balcony' => '2', 'condition' => 'New', 'parking_lot' => '1', 'unit_size' => '1650', 'facilities' => $allFacilities, 'amenities' => $allAmenities, 'tenant_preference' => ['female', 'couple'], 'description' => 'Front Side', 'images' => [$images[7], $images[0]], 'monthly_rent' => '17500.00', 'sale_amount' => '0.00', 'min_rental_value' => '1', 'min_rental_unit' => 'years', 'security_deposit' => '10500.00', 'security_deposit_value' => '5', 'security_deposit_unit' => 'days', 'utility_deposit' => '2500.00', 'late_fee' => '1000.00', 'late_fee_after_days' => '5', 'created_at' => '2025-10-14 09:01:30', 'updated_at' => '2025-11-02 09:03:36'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function incomeCategories(): array
    {
        return [
            ['name' => 'Rent Payment', 'description' => 'Monthly rental income from tenants.', 'created_at' => '2025-10-15 12:04:01'],
            ['name' => 'Security Deposit', 'description' => 'Refundable or non-refundable deposit collected at lease signing', 'created_at' => '2025-10-15 12:04:16'],
            ['name' => 'Maintenance Fees', 'description' => 'Additional charges for property upkeep or repairs.', 'created_at' => '2025-10-15 12:04:28'],
            ['name' => 'Advertising Income', 'description' => 'Earnings from billboard or sign placements on the property.', 'created_at' => '2025-10-15 12:04:42'],
            ['name' => 'Miscellaneous Income', 'description' => 'Other property-related revenue sources not listed above.', 'created_at' => '2025-10-15 12:05:44'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function incomes(): array
    {
        return [
            ['category' => 'Rent Payment', 'amount' => '1200', 'income_for' => 'Tenant rent', 'payment_type' => 'card', 'reference_no' => 'TNR-3353', 'note' => null, 'income_date' => '2025-10-10 00:00:00', 'created_at' => '2025-10-15 12:07:47', 'updated_at' => '2025-10-15 12:08:46'],
            ['category' => 'Security Deposit', 'amount' => '800', 'income_for' => 'Rent for Security Deposit', 'payment_type' => 'cash', 'reference_no' => 'SDP-3465', 'note' => null, 'income_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:09:26', 'updated_at' => '2025-10-15 12:09:26'],
            ['category' => 'Maintenance Fees', 'amount' => '200.5', 'income_for' => 'House maintenance', 'payment_type' => 'mobile_payment', 'reference_no' => 'HMT-6674', 'note' => null, 'income_date' => '2025-10-16 00:00:00', 'created_at' => '2025-10-15 12:10:13', 'updated_at' => '2025-10-15 12:10:13'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function expenseCategories(): array
    {
        return [
            ['name' => 'Maintenance', 'description' => 'Costs for building upkeep, plumbing, electrical, and general repairs.', 'created_at' => '2025-10-15 12:16:15'],
            ['name' => 'Utility Expenses', 'description' => 'Payments for water, electricity, gas, and internet for common areas.', 'created_at' => '2025-10-15 12:16:29'],
            ['name' => 'Property Taxes', 'description' => 'Annual or monthly government property tax payments.', 'created_at' => '2025-10-15 12:16:42'],
            ['name' => 'Salary', 'description' => 'Payments for property managers, security, janitors, and other staff.', 'created_at' => '2025-10-15 12:17:02'],
            ['name' => 'Cleaning & Sanitation', 'description' => 'Expenses for cleaning services, garbage disposal, and pest control.', 'created_at' => '2025-10-15 12:17:16'],
            ['name' => 'Repairs', 'description' => 'Costs for fixing or replacing appliances and furnishings.', 'created_at' => '2025-10-15 12:17:31'],
            ['name' => 'Security Services', 'description' => 'Payments for security personnel, CCTV maintenance, and alarm systems.', 'created_at' => '2025-10-15 12:18:00'],
            ['name' => 'Labor Salaries & Wages', 'description' => 'Monthly rental expense from tenants.', 'created_at' => '2025-10-15 12:18:28'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function expenses(): array
    {
        return [
            ['category' => 'Maintenance', 'amount' => '1200', 'expense_for' => 'Property Maintenance', 'payment_type' => 'mobile_payment', 'reference_no' => 'PYM-3452', 'note' => null, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:19:40', 'updated_at' => '2025-10-15 12:19:40'],
            ['category' => 'Utility Expenses', 'amount' => '800', 'expense_for' => 'Property Utility Expenses', 'payment_type' => 'cash', 'reference_no' => 'PUE-8732', 'note' => null, 'expense_date' => '2025-10-10 00:00:00', 'created_at' => '2025-10-15 12:20:20', 'updated_at' => '2025-10-15 12:20:20'],
            ['category' => 'Property Taxes', 'amount' => '250', 'expense_for' => 'Property Tax', 'payment_type' => 'cash', 'reference_no' => 'PTM-3452', 'note' => null, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:20:55', 'updated_at' => '2025-10-15 12:20:55'],
            ['category' => 'Labor Salaries & Wages', 'amount' => '1200', 'expense_for' => 'Labor salary', 'payment_type' => 'bank', 'reference_no' => 'LSW-3453', 'note' => null, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:24:55', 'updated_at' => '2025-10-15 12:24:55'],
            ['category' => 'Cleaning & Sanitation', 'amount' => '700', 'expense_for' => 'Property cleaning', 'payment_type' => 'due', 'reference_no' => 'PCS-4623', 'note' => null, 'expense_date' => '2025-10-15 00:00:00', 'created_at' => '2025-10-15 12:25:31', 'updated_at' => '2025-10-15 12:25:31'],
        ];
    }
}
