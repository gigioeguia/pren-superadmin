<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Única fuente del password de los usuarios demo (Fase 5 / 5.2).
 *
 * Devuelve el valor de config('demo.user_password') si el operador lo
 * fijó, o genera una aleatoria por email (estable durante la ejecución)
 * para que los seeders puedan imprimirla solo al crear la cuenta.
 */
class DemoCredentials
{
    /** @var array<string, string> */
    private static array $generated = [];

    /**
     * Password de la cuenta demo: valor de config o aleatoria por email.
     */
    public static function password(string $email): string
    {
        $configured = config('demo.user_password');

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return self::$generated[$email] ??= Str::password(20);
    }

    /**
     * Hash del password plano para persistir en users.password.
     */
    public static function hash(string $plain): string
    {
        return Hash::make($plain);
    }

    /**
     * Password generado para el email (null si usa el valor de config).
     */
    public static function generatedFor(string $email): ?string
    {
        return self::$generated[$email] ?? null;
    }

    /**
     * Limpia el registro de passwords generados (higiene de tests).
     */
    public static function flush(): void
    {
        self::$generated = [];
    }
}
