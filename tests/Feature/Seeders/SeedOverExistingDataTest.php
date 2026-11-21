<?php

namespace Tests\Feature\Seeders;

use App\Models\Category;
use App\Models\Currency;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedOverExistingDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_f2_seed_works_over_a_populated_database(): void
    {
        // entorno "ya poblado": landlord real, categoría y moneda propios
        $realLandlord = User::factory()->landlord()->create(['email' => 'real-landlord@example.com']);
        $realProperty = Property::factory()->create(['landlord_id' => $realLandlord->id]);
        $realCategory = Category::firstOrCreate(['name' => 'Custom Villa'], ['status' => 1]);
        $realCurrency = Currency::factory()->create(['code' => 'ZZZ', 'name' => 'Probe Zed']);

        $this->seed(DatabaseSeeder::class);

        // filas previas intactas
        $this->assertDatabaseHas('users', ['id' => $realLandlord->id, 'email' => 'real-landlord@example.com']);
        $this->assertDatabaseHas('properties', ['id' => $realProperty->id, 'landlord_id' => $realLandlord->id]);
        $this->assertDatabaseHas('categories', ['id' => $realCategory->id, 'name' => 'Custom Villa']);
        $this->assertDatabaseHas('currencies', ['id' => $realCurrency->id, 'code' => 'ZZZ']);

        // sin duplicados del dataset demo
        $this->assertSame(1, User::whereEmail('landlord@acnoo.com')->count());
        $this->assertSame(1, Currency::where('code', 'USD')->count());

        // dataset demo completo, colgado del landlord demo (is_demo), no del real
        $demo = User::demoLandlords()->first();
        $this->assertNotNull($demo);
        $this->assertNotSame($realLandlord->id, $demo->id);

        $this->assertSame(8, Property::where('landlord_id', $demo->id)->count());
        $this->assertSame(1, Property::where('landlord_id', $realLandlord->id)->count());
    }
}
