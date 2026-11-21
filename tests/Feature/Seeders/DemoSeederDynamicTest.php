<?php

namespace Tests\Feature\Seeders;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Facility;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Property;
use App\Models\PropertyDetail;
use App\Models\User;
use Database\Seeders\AmenitySeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\FacilitySeeder;
use Database\Seeders\HouseTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederDynamicTest extends TestCase
{
    use RefreshDatabase;

    public function test_f5_demo_seeder_resolves_landlord_and_uses_shared_dataset(): void
    {
        $landlord = User::factory()->landlord()->create();
        $landlord->forceFill(['role' => 'landlord', 'is_demo' => true])->save();

        $this->seed([
            CategorySeeder::class,
            HouseTypeSeeder::class,
            FacilitySeeder::class,
            AmenitySeeder::class,
            DemoSeeder::class,
        ]);

        // resuelto dinámicamente, sin depender del histórico id 3
        $this->assertNotSame(3, $landlord->id);

        $propertyIds = Property::where('landlord_id', $landlord->id)->pluck('id');
        $this->assertSame(3, $propertyIds->count());

        // títulos del dataset compartido con PropertySeeder
        $titles = PropertyDetail::whereIn('property_id', $propertyIds)->get()
            ->map(fn (PropertyDetail $detail) => $detail->property_info['property_title'] ?? null)
            ->sort()
            ->values()
            ->all();

        $this->assertSame(['New Apartment', 'New Commercial Property', 'New House'], $titles);

        $this->assertSame(3, IncomeCategory::where('landlord_id', $landlord->id)->count());
        $this->assertSame(3, Income::where('landlord_id', $landlord->id)->count());
        $this->assertSame(3, ExpenseCategory::where('landlord_id', $landlord->id)->count());
        $this->assertSame(3, Expense::where('landlord_id', $landlord->id)->count());

        // facilities del detalle resueltas por nombre → ids del landlord demo
        $detail = PropertyDetail::whereIn('property_id', $propertyIds)->first();
        $this->assertNotNull($detail);
        $this->assertNotEmpty($detail->facilities);

        foreach ($detail->facilities as $facilityId) {
            $facility = Facility::find($facilityId);

            $this->assertNotNull($facility);
            $this->assertSame($landlord->id, $facility->landlord_id);
        }

        // idempotente: re-run no duplica nada
        $this->seed(DemoSeeder::class);

        $this->assertSame(3, Property::where('landlord_id', $landlord->id)->count());
        $this->assertSame(3, IncomeCategory::where('landlord_id', $landlord->id)->count());
        $this->assertSame(3, Income::where('landlord_id', $landlord->id)->count());
        $this->assertSame(3, ExpenseCategory::where('landlord_id', $landlord->id)->count());
        $this->assertSame(3, Expense::where('landlord_id', $landlord->id)->count());
    }
}
