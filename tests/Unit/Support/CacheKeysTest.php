<?php

namespace Tests\Unit\Support;

use App\Models\Currency;
use App\Models\Language;
use App\Models\Option;
use App\Models\PlanSubscribe;
use App\Models\User;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use ReflectionFunction;
use Tests\TestCase;
use TypeError;

class CacheKeysTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_u1_option_key_intact(): void
    {
        $this->assertSame('general', CacheKeys::option('general'));
    }

    public function test_u2_plan_data_requires_user_id(): void
    {
        $this->assertSame('plan-data-7', CacheKeys::planData(7));

        $this->expectException(TypeError::class);
        CacheKeys::planData();
    }

    public function test_u3_fixed_keys_intact(): void
    {
        $this->assertSame('languages', CacheKeys::languages());
        $this->assertSame('default_currency', CacheKeys::defaultCurrency());
    }

    public function test_u4_forget_model_option(): void
    {
        cache([CacheKeys::option('general') => 'stale']);

        forgetModel(new Option(['key' => 'general']));

        $this->assertNull(cache(CacheKeys::option('general')));
    }

    public function test_u5_forget_model_plan_subscribe(): void
    {
        cache([CacheKeys::planData(7) => 'stale']);

        forgetModel(new PlanSubscribe(['user_id' => 7]));

        $this->assertNull(cache(CacheKeys::planData(7)));
    }

    public function test_u6_forget_model_user(): void
    {
        cache([CacheKeys::planData(7) => 'stale']);

        forgetModel((new User)->forceFill(['id' => 7]));

        $this->assertNull(cache(CacheKeys::planData(7)));
    }

    public function test_u7_forget_model_language(): void
    {
        cache([CacheKeys::languages() => 'stale']);

        forgetModel(new Language);

        $this->assertNull(cache(CacheKeys::languages()));
    }

    public function test_u8_forget_model_currency(): void
    {
        cache([CacheKeys::defaultCurrency() => 'stale']);

        forgetModel(new Currency);

        $this->assertNull(cache(CacheKeys::defaultCurrency()));
    }

    public function test_u9_cache_remember_signature_and_store(): void
    {
        $this->assertCount(2, (new ReflectionFunction('cache_remember'))->getParameters());

        $calls = 0;
        $remember = function () use (&$calls) {
            return ++$calls;
        };

        $this->assertSame(1, cache_remember('u9-key', $remember));
        $this->assertSame(1, cache_remember('u9-key', $remember));
        $this->assertSame(1, $calls);
    }
}
