<?php

namespace App\Observers;

use App\Models\Option;
use App\Support\CacheKeys;

class OptionObserver
{
    public function saved(Option $option): void
    {
        CacheKeys::forgetModel($option);
    }

    public function deleted(Option $option): void
    {
        CacheKeys::forgetModel($option);
    }
}
