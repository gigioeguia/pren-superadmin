<?php

namespace App\Support;

use App\Models\Option;

class Options
{
    public static function array(string $key, array $default = []): array
    {
        $value = self::read($key);

        return is_array($value) ? $value : $default;
    }

    public static function string(string $key, string $default = ''): string
    {
        $value = self::read($key);

        return is_string($value) ? $value : $default;
    }

    /** Fila ausente, value null o JSON corrupto → null (el default tipado decide). */
    private static function read(string $key): mixed
    {
        return cache_remember(CacheKeys::option($key), fn () => Option::where('key', $key)->value('value'));
    }
}
