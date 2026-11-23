<?php

namespace App\Support;

use App\Models\Currency as CurrencyModel;

class Currency
{
    public static function format(float|int $amount, string $type = 'icon', int $decimals = 2, ?CurrencyModel $currency = null): string
    {
        $amount = number_format($amount, $decimals);
        $currency = $currency ?? self::default(null, null);

        $symbolOrCode = ($type === 'icon' || $type === 'symbol') ? $currency->symbol : $currency->code;

        return $currency->position === 'right'
            ? "{$amount} {$symbolOrCode}"
            : "{$symbolOrCode} {$amount}";
    }

    public static function currency(): object
    {
        $defaultCurrency = CurrencyModel::where('is_default', 1)->first();

        if ($defaultCurrency) {
            return (object) [
                'name' => $defaultCurrency->name,
                'rate' => $defaultCurrency->rate,
                'code' => $defaultCurrency->code,
                'symbol' => $defaultCurrency->symbol,
                'position' => $defaultCurrency->position,
            ];
        }

        return self::default();
    }

    public static function convert(float|int $amount, CurrencyModel $currency, bool $multiply = true): float|int
    {
        if ($currency->code === self::default('code')) {
            return $amount;
        }

        return $multiply ? $amount * $currency->rate : $amount / $currency->rate;
    }

    public static function convertToDefault(float|int $amount, CurrencyModel $currency): float|int
    {
        return $amount * $currency->rate;
    }

    public static function default(?string $key = null, ?CurrencyModel $currency = null): mixed
    {
        $currency ??= self::defaultCurrency();

        return $key ? $currency->{$key} : $currency;
    }

    /**
     * D2 (spec 5.15): el store cachea la fila como array (`serializable_classes`
     * = false); la lectura rehidrata el modelo o construye el fallback USD
     * cuando no existe fila default.
     */
    private static function defaultCurrency(): object
    {
        $buildRows = fn (): ?array => CurrencyModel::where('is_default', 1)->first()?->getAttributes();

        $rows = cache_remember(CacheKeys::defaultCurrency(), $buildRows);

        // Payload stale/venenado (formato pre-D2): recomputar sin escribir.
        if (! is_array($rows)) {
            $rows = $buildRows();
        }

        if ($rows === null) {
            return (object) [
                'name' => 'US Dollar',
                'code' => 'USD',
                'rate' => 1,
                'symbol' => '$',
                'position' => 'left',
                'status' => true,
                'is_default' => true,
            ];
        }

        return CurrencyModel::hydrate([$rows])->first();
    }
}
