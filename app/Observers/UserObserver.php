<?php

namespace App\Observers;

use App\Models\User;
use App\Support\CacheKeys;

class UserObserver
{
    public function saved(User $user): void
    {
        // Sobre-invalidación aceptada (spec §8): un re-read trivial.
        CacheKeys::forgetModel($user);
    }

    public function deleted(User $user): void
    {
        CacheKeys::forgetModel($user);
    }
}
