<?php

namespace App\Support;

use App\Models\Currency as CurrencyModel;
use App\Models\Gateway;

class Payment
{
    public function __construct(protected Currency $currency) {}

    public static function payable(float|int $amount, Gateway $gateway): float|int
    {
        $gatewayCurrency = CurrencyModel::findOrFail($gateway->currency_id);

        if ($gatewayCurrency->code === Currency::default('code')) {
            return $amount + (float) $gateway->charge;
        }

        return (Currency::convertToDefault((float) $gateway->charge, $gatewayCurrency) * (float) $gatewayCurrency->rate) + (float) $gateway->charge;
    }
}
