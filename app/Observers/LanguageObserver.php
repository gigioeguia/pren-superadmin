<?php

namespace App\Observers;

use App\Models\Language;
use App\Support\CacheKeys;

class LanguageObserver
{
    public function saved(Language $language): void
    {
        CacheKeys::forgetModel($language);
    }

    public function deleted(Language $language): void
    {
        CacheKeys::forgetModel($language);
    }
}
