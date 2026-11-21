<?php

namespace Tests\Feature\Cache;

use App\Models\Currency;
use App\Models\Language;
use App\Support\CacheKeys;
use App\Support\Currency as CurrencySupport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class CacheAdminInvalidationTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_g1_currency_update_refreshes_money_immediately(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        $currency = Currency::factory()->create([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'rate' => 1,
            'position' => 'left',
            'status' => 1,
            'is_default' => 1,
        ]);
        cache([CacheKeys::defaultCurrency() => 'stale']);

        // Act
        $response = $this->actingAs($admin)->putJson("/admin/currencies/{$currency->id}", [
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '€',
            'rate' => 1,
            'position' => 'left',
            'status' => 1,
            'is_default' => 1,
        ]);

        // Assert — gap G2: sin 30 min de dinero mal formateado
        $response->assertOk();
        $this->assertNull(cache(CacheKeys::defaultCurrency()));
        $this->assertSame('€ 100.00', CurrencySupport::format(100));
    }

    public function test_g2_currency_store_with_default_invalidates(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        cache([CacheKeys::defaultCurrency() => 'stale']);

        // Act
        $response = $this->actingAs($admin)->postJson('/admin/currencies', [
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
            'rate' => 1,
            'position' => 'left',
            'status' => 1,
            'is_default' => 1,
        ]);

        // Assert — gap G2
        $response->assertOk();
        $this->assertNull(cache(CacheKeys::defaultCurrency()));
    }

    public function test_g3_language_is_default_invalidates_via_mass_exception(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        Language::create([
            'name' => 'English',
            'code' => 'zz',
            'order' => 1,
            'status' => 1,
            'is_default' => 1,
        ]);
        $target = Language::create([
            'name' => 'French',
            'code' => 'zy',
            'order' => 2,
            'status' => 1,
            'is_default' => 0,
        ]);
        cache([CacheKeys::languages() => 'stale']);

        // Act — mass-op: la excepción Q15a invalida tras el commit
        $response = $this->actingAs($admin)->postJson("/admin/languages/default/{$target->id}");

        // Assert — gap G3
        $response->assertOk();
        $this->assertNull(cache(CacheKeys::languages()));
    }

    public function test_g4_language_status_destroy_delete_all_invalidate(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();

        $language = Language::create([
            'name' => 'English',
            'code' => 'zz',
            'order' => 1,
            'status' => 1,
            'is_default' => 0,
        ]);

        // status
        cache([CacheKeys::languages() => 'stale']);
        $this->actingAs($admin)->postJson("/admin/languages/status/{$language->id}", ['status' => 0])->assertOk();
        $this->assertNull(cache(CacheKeys::languages()));

        // destroy
        cache([CacheKeys::languages() => 'stale']);
        $this->actingAs($admin)->deleteJson("/admin/languages/{$language->id}")->assertOk();
        $this->assertNull(cache(CacheKeys::languages()));

        // delete-all (row-by-row: cada delete dispara el observer)
        $batch = Language::create([
            'name' => 'French',
            'code' => 'zy',
            'order' => 2,
            'status' => 1,
            'is_default' => 0,
        ]);
        cache([CacheKeys::languages() => 'stale']);
        $this->actingAs($admin)->postJson('/admin/languages/delete-all', ['ids' => [$batch->id]])->assertOk();
        $this->assertNull(cache(CacheKeys::languages()));
    }
}
