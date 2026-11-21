<?php

namespace App\Observers;

use App\Models\PlanSubscribe;
use App\Support\CacheKeys;

class PlanSubscribeObserver
{
    public function saved(PlanSubscribe $planSubscribe): void
    {
        CacheKeys::forgetModel($planSubscribe);
    }

    public function deleted(PlanSubscribe $planSubscribe): void
    {
        CacheKeys::forgetModel($planSubscribe);
    }
}
