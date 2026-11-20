<?php

namespace Tests\Unit\Support;

use App\Models\Currency as CurrencyModel;
use App\Support\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_u1_format_left_icon(): void
    {
        CurrencyModel::factory()->create([
            'position' => 'left',
            'symbol' => '$',
            'is_default' => 1,
        ]);

        $this->assertSame('$ 100.50', Currency::format(100.5, 'icon', 2));
    }

    public function test_u2_format_right_code(): void
    {
        CurrencyModel::factory()->create([
            'position' => 'right',
            'code' => 'EUR',
            'symbol' => '€',
            'is_default' => 1,
        ]);

        $this->assertSame('100.50 EUR', Currency::format(100.5, 'code', 2));
    }

    public function test_u3_decimals_configurable(): void
    {
        CurrencyModel::factory()->create([
            'position' => 'left',
            'symbol' => '$',
            'is_default' => 1,
        ]);

        $this->assertSame('$ 10', Currency::format(10, 'icon', 0));
    }

    public function test_u4_default_cached(): void
    {
        $currency = CurrencyModel::factory()->create(['is_default' => 1]);

        $this->assertSame($currency->code, Currency::default('code'));
        $this->assertSame($currency->code, Currency::default('code'));
    }

    public function test_u5_default_fallback(): void
    {
        $this->assertSame('USD', Currency::default('code'));
    }

    public function test_u6_default_key(): void
    {
        CurrencyModel::factory()->create(['name' => 'Euro', 'code' => 'EUR', 'is_default' => 1]);

        $this->assertSame('EUR', Currency::default('code'));
    }

    public function test_u7_convert_same_currency(): void
    {
        $currency = CurrencyModel::factory()->create(['code' => 'USD', 'rate' => 2, 'is_default' => 1]);

        $this->assertEquals(100, Currency::convert(100, $currency));
    }

    public function test_u8_convert_multiply(): void
    {
        CurrencyModel::factory()->create(['code' => 'USD', 'rate' => 1, 'is_default' => 1]);
        $eur = CurrencyModel::factory()->create(['name' => 'Euro', 'code' => 'EUR', 'rate' => 2, 'is_default' => 0]);

        $this->assertEquals(200, Currency::convert(100, $eur, true));
    }

    public function test_u9_convert_divide(): void
    {
        CurrencyModel::factory()->create(['code' => 'USD', 'rate' => 1, 'is_default' => 1]);
        $eur = CurrencyModel::factory()->create(['name' => 'Euro', 'code' => 'EUR', 'rate' => 2, 'is_default' => 0]);

        $this->assertEquals(50, Currency::convert(100, $eur, false));
    }

    public function test_u10_convert_to_default(): void
    {
        $eur = CurrencyModel::factory()->create(['name' => 'Euro', 'code' => 'EUR', 'rate' => 2, 'is_default' => 0]);
        CurrencyModel::factory()->create(['code' => 'USD', 'rate' => 1, 'is_default' => 1]);

        $this->assertEquals(200, Currency::convertToDefault(100, $eur));
    }
}
