<?php

namespace Database\Seeders\Concerns;

use App\Models\Amenity;
use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Facility;
use App\Models\HouseType;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Property;
use App\Models\PropertyDetail;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\DemoDataset;
use Illuminate\Support\Collection;

/**
 * Lógica de siembra del dataset demo compartida por PropertySeeder,
 * IncomeSeeder, ExpenseSeeder y DemoSeeder (spec 4.10 / Q12).
 */
trait SeedsDemoData
{
    /**
     * Siembra propiedades + detalles del dataset para el landlord demo.
     *
     * @param  array<int>  $keys  índices de DemoDataset::properties()
     * @return array<int, Property> propiedades creadas, key por índice
     */
    private function seedDemoProperties(User $landlord, array $keys): array
    {
        if (Property::where('landlord_id', $landlord->id)->exists()) {
            return [];
        }

        $categoryIds = Category::pluck('id', 'name');
        $houseTypeIds = HouseType::pluck('id', 'name');
        $facilityIds = Facility::where('landlord_id', $landlord->id)->pluck('id', 'name');
        $amenityIds = Amenity::where('landlord_id', $landlord->id)->pluck('id', 'name');

        $properties = DemoDataset::properties();
        $details = DemoDataset::propertyDetails();
        $created = [];

        foreach ($keys as $key) {
            $propertyData = $properties[$key];
            $categoryName = $propertyData['category'];
            $houseTypeName = $propertyData['house_type'];
            unset($propertyData['category'], $propertyData['house_type']);

            $created[$key] = Property::create($propertyData + [
                'landlord_id' => $landlord->id,
                'category_id' => $categoryIds[$categoryName] ?? null,
                'house_type_id' => $houseTypeName ? ($houseTypeIds[$houseTypeName] ?? null) : null,
            ]);

            $detailData = $details[$key];
            $this->resolveNameIds($detailData, 'facilities', $facilityIds);
            $this->resolveNameIds($detailData, 'amenities', $amenityIds);

            PropertyDetail::create($detailData + ['property_id' => $created[$key]->id]);
        }

        return $created;
    }

    private function seedDemoUnits(Property $property): void
    {
        if (Unit::where('property_id', $property->id)->exists()) {
            return;
        }

        $facilityIds = Facility::where('landlord_id', $property->landlord_id)->pluck('id', 'name');
        $amenityIds = Amenity::where('landlord_id', $property->landlord_id)->pluck('id', 'name');

        foreach (DemoDataset::units() as $unitData) {
            $this->resolveNameIds($unitData, 'facilities', $facilityIds);
            $this->resolveNameIds($unitData, 'amenities', $amenityIds);

            Unit::create($unitData + ['property_id' => $property->id]);
        }
    }

    /**
     * Categorías + incomes del dataset. Cada scope referencia SUS PROPIAS
     * categorías (fix cross-tenant, spec 4.10).
     *
     * @param  array<int>  $categoryKeys  índices de DemoDataset::incomeCategories()
     * @param  array<int>  $incomeKeys  índices de DemoDataset::incomes()
     * @param  array<int|null>  $scopes  landlord_ids (null = set global)
     */
    private function seedDemoIncomes(User $landlord, array $categoryKeys, array $incomeKeys, array $scopes): void
    {
        $categoriesByScope = [];

        foreach ($scopes as $scope) {
            foreach ($categoryKeys as $key) {
                $categoryData = DemoDataset::incomeCategories()[$key];

                $categoriesByScope[$scope][$categoryData['name']] = IncomeCategory::firstOrCreate(
                    ['landlord_id' => $scope, 'name' => $categoryData['name']],
                    [
                        'description' => $categoryData['description'],
                        'status' => '1',
                        'created_at' => $categoryData['created_at'],
                        'updated_at' => $categoryData['created_at'],
                    ],
                );
            }
        }

        foreach ($scopes as $scope) {
            foreach ($incomeKeys as $incomeKey) {
                $income = DemoDataset::incomes()[$incomeKey];
                $category = $categoriesByScope[$scope][$income['category']];

                Income::firstOrCreate(
                    ['landlord_id' => $scope, 'income_for' => $income['income_for']],
                    [
                        'income_category_id' => $category->id,
                        'amount' => $income['amount'],
                        'payment_type' => $income['payment_type'],
                        'reference_no' => $income['reference_no'],
                        'note' => $income['note'],
                        'income_date' => $income['income_date'],
                        'created_at' => $income['created_at'],
                        'updated_at' => $income['updated_at'],
                    ],
                );
            }
        }
    }

    /**
     * @param  array<int>  $categoryKeys  índices de DemoDataset::expenseCategories()
     * @param  array<int>  $expenseKeys  índices de DemoDataset::expenses()
     * @param  array<int|null>  $scopes  landlord_ids (null = set global)
     */
    private function seedDemoExpenses(User $landlord, array $categoryKeys, array $expenseKeys, array $scopes): void
    {
        $categoriesByScope = [];

        foreach ($scopes as $scope) {
            foreach ($categoryKeys as $key) {
                $categoryData = DemoDataset::expenseCategories()[$key];

                $categoriesByScope[$scope][$categoryData['name']] = ExpenseCategory::firstOrCreate(
                    ['landlord_id' => $scope, 'name' => $categoryData['name']],
                    [
                        'description' => $categoryData['description'],
                        'status' => '1',
                        'created_at' => $categoryData['created_at'],
                        'updated_at' => $categoryData['created_at'],
                    ],
                );
            }
        }

        foreach ($scopes as $scope) {
            foreach ($expenseKeys as $expenseKey) {
                $expense = DemoDataset::expenses()[$expenseKey];
                $category = $categoriesByScope[$scope][$expense['category']];

                Expense::firstOrCreate(
                    ['landlord_id' => $scope, 'expense_for' => $expense['expense_for']],
                    [
                        'expense_category_id' => $category->id,
                        'amount' => $expense['amount'],
                        'payment_type' => $expense['payment_type'],
                        'reference_no' => $expense['reference_no'],
                        'note' => $expense['note'],
                        'expense_date' => $expense['expense_date'],
                        'created_at' => $expense['created_at'],
                        'updated_at' => $expense['updated_at'],
                    ],
                );
            }
        }
    }

    /**
     * Traduce una columna de nombres (facilities/amenities) a ids vía lookup.
     *
     * @param  array<string, mixed>  $rowData
     */
    private function resolveNameIds(array &$rowData, string $field, Collection $nameIds): void
    {
        if (isset($rowData[$field])) {
            $rowData[$field] = collect($rowData[$field])->map(fn ($name) => $nameIds[$name])->values()->all();
        }
    }
}
