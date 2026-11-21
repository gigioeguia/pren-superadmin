<?php

namespace Tests\Feature\Cache;

use App\Models\Currency;
use App\Models\Language;
use App\Models\Option;
use App\Models\PlanSubscribe;
use App\Support\CacheKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class CacheObserverTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_f1_option_observer_invalidates_on_mutations(): void
    {
        // created
        cache([CacheKeys::option('general') => 'stale']);
        $option = Option::factory()->create(['key' => 'general']);
        $this->assertNull(cache(CacheKeys::option('general')));

        // updated
        cache([CacheKeys::option('general') => 'stale']);
        $option->update(['value' => ['site' => 'x']]);
        $this->assertNull(cache(CacheKeys::option('general')));

        // deleted
        cache([CacheKeys::option('general') => 'stale']);
        $option->delete();
        $this->assertNull(cache(CacheKeys::option('general')));
    }

    public function test_f2_language_observer_invalidates_on_mutations(): void
    {
        // created
        cache([CacheKeys::languages() => 'stale']);
        $language = Language::create([
            'name' => 'English',
            'code' => 'zz',
            'order' => 1,
            'status' => 1,
            'is_default' => 1,
        ]);
        $this->assertNull(cache(CacheKeys::languages()));

        // updated
        cache([CacheKeys::languages() => 'stale']);
        $language->update(['status' => 0]);
        $this->assertNull(cache(CacheKeys::languages()));

        // deleted
        cache([CacheKeys::languages() => 'stale']);
        $language->delete();
        $this->assertNull(cache(CacheKeys::languages()));
    }

    public function test_f3_currency_observer_invalidates_on_mutations(): void
    {
        // created
        cache([CacheKeys::defaultCurrency() => 'stale']);
        $currency = Currency::factory()->create(['is_default' => 1]);
        $this->assertNull(cache(CacheKeys::defaultCurrency()));

        // updated
        cache([CacheKeys::defaultCurrency() => 'stale']);
        $currency->update(['symbol' => '£']);
        $this->assertNull(cache(CacheKeys::defaultCurrency()));

        // deleted
        cache([CacheKeys::defaultCurrency() => 'stale']);
        $currency->delete();
        $this->assertNull(cache(CacheKeys::defaultCurrency()));
    }

    public function test_f4_plan_subscribe_observer_invalidates_on_mutations(): void
    {
        $landlord = $this->createLandlord();

        // created — incluye ramas unpaid (gap G4)
        cache([CacheKeys::planData($landlord->id) => 'stale']);
        $subscribe = PlanSubscribe::factory()->create([
            'user_id' => $landlord->id,
            'payment_status' => 'unpaid',
        ]);
        $this->assertNull(cache(CacheKeys::planData($landlord->id)));

        // updated
        cache([CacheKeys::planData($landlord->id) => 'stale']);
        $subscribe->update(['payment_status' => 'paid']);
        $this->assertNull(cache(CacheKeys::planData($landlord->id)));

        // deleted
        cache([CacheKeys::planData($landlord->id) => 'stale']);
        $subscribe->delete();
        $this->assertNull(cache(CacheKeys::planData($landlord->id)));
    }

    public function test_f5_user_observer_invalidates_on_save(): void
    {
        $landlord = $this->createLandlord();

        cache([CacheKeys::planData($landlord->id) => 'stale']);

        $landlord->update(['name' => 'Jane Doe']);

        $this->assertNull(cache(CacheKeys::planData($landlord->id)));
    }
}
