<?php

namespace Tests\Unit\Support;

use App\Models\Currency as CurrencyModel;
use App\Models\Gateway;
use App\Support\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_u11_payable_same_currency(): void
    {
        $currency = CurrencyModel::factory()->create([
            'code' => 'USD',
            'rate' => 1,
            'is_default' => 1,
        ]);

        $gateway = Gateway::factory()->create([
            'currency_id' => $currency->id,
            'charge' => 5,
        ]);

        $this->assertEquals(105, Payment::payable(100, $gateway));
    }

    public function test_u12_payable_different_currency(): void
    {
        CurrencyModel::factory()->create(['code' => 'USD', 'rate' => 1, 'is_default' => 1]);
        $eur = CurrencyModel::factory()->create(['name' => 'Euro', 'code' => 'EUR', 'rate' => 2, 'is_default' => 0]);

        $gateway = Gateway::factory()->create([
            'currency_id' => $eur->id,
            'charge' => 10,
        ]);

        $this->assertEquals(50, Payment::payable(100, $gateway));
    }

    public function test_u13_helper_delegation(): void
    {
        $currency = CurrencyModel::factory()->create(['code' => 'USD', 'rate' => 1, 'is_default' => 1]);
        $gateway = Gateway::factory()->create(['currency_id' => $currency->id, 'charge' => 7]);

        $this->assertEquals(
            Payment::payable(100, $gateway),
            app(Payment::class)->payable(100, $gateway)
        );
    }
}
