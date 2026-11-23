<?php

namespace Tests\Feature\Cache;

use App\Models\Currency;
use App\Models\Language;
use App\Models\PlanSubscribe;
use App\Support\CacheKeys;
use App\Support\Currency as CurrencySupport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

#[Group('smoke')]
class CacheSerializablePayloadTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    // F9 — el store guarda arrays, no objetos/colecciones (D2, spec 5.15).
    public function test_f9_plan_data_stores_array_payload(): void
    {
        $landlord = $this->createLandlord(['will_expire' => now()->addDays(30)->format('Y-m-d H:i:s')]);
        PlanSubscribe::factory()->create(['user_id' => $landlord->id]);

        plan_data((string) $landlord->id);

        $payload = cache(CacheKeys::planData($landlord->id));

        $this->assertIsArray($payload);
        $this->assertIsArray($payload['plan_subscribe']);
        $this->assertArrayHasKey('will_expire', $payload);
    }

    public function test_f9_languages_stores_array_payload(): void
    {
        Language::create(['name' => 'Alpha', 'code' => 'aa', 'order' => 1, 'status' => 1, 'is_default' => 1]);

        languages();

        $rows = cache(CacheKeys::languages());

        $this->assertIsArray($rows);
        $this->assertIsArray($rows[0]);
    }

    public function test_f9_default_currency_stores_array_payload(): void
    {
        Currency::factory()->create(['code' => 'EUR', 'is_default' => 1]);

        CurrencySupport::default();

        $rows = cache(CacheKeys::defaultCurrency());

        $this->assertIsArray($rows);
        $this->assertSame('EUR', $rows['code']);
    }

    // F10 — el contrato publico es invariante tras la rehidratacion.
    public function test_f10_plan_data_contract_is_preserved(): void
    {
        $landlord = $this->createLandlord(['will_expire' => now()->addDays(30)->format('Y-m-d H:i:s')]);
        $planSubscribe = PlanSubscribe::factory()->create(['user_id' => $landlord->id]);

        $result = plan_data((string) $landlord->id);

        $this->assertInstanceOf(PlanSubscribe::class, $result);
        $this->assertSame($planSubscribe->plan_id, $result->plan->id);
        $this->assertSame($landlord->fresh()->will_expire, $result->will_expire);
    }

    public function test_f10_plan_data_null_without_subscription(): void
    {
        $landlord = $this->createLandlord();

        $this->assertNull(plan_data((string) $landlord->id));
    }

    public function test_f10_languages_contract_is_preserved(): void
    {
        Language::create(['name' => 'Alpha', 'code' => 'aa', 'order' => 1, 'status' => 1, 'is_default' => 1]);

        $result = languages();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertInstanceOf(Language::class, $result->where('code', 'aa')->first());
    }

    public function test_f10_default_currency_contract_is_preserved(): void
    {
        Currency::factory()->create(['code' => 'EUR', 'is_default' => 1]);

        $this->assertSame('EUR', CurrencySupport::default('code'));
        $this->assertInstanceOf(Currency::class, CurrencySupport::default());
    }

    public function test_f10_default_currency_fallback_without_row(): void
    {
        $fallback = CurrencySupport::default();

        $this->assertSame('USD', $fallback->code);
        $this->assertSame('$', $fallback->symbol);
    }
}
