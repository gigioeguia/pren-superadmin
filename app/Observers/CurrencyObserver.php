<?php

namespace App\Observers;

use App\Models\Currency;
use App\Support\CacheKeys;

class CurrencyObserver
{
    public function saved(Currency $currency): void
    {
        CacheKeys::forgetModel($currency);
    }

    public function deleted(Currency $currency): void
    {
        CacheKeys::forgetModel($currency);
    }
}
