<?php

namespace Tests\Stubs;

/**
 * Stub de gateway para tests de pagos.
 *
 * Se asigna a Gateway->namespace para evitar HTTP real y llamadas
 * estaticas a las clases App\Library\*.
 */
class StubGateway
{
    /** Datos del ultimo make_payment recibido (para asserts). */
    public static array $lastPaymentData = [];

    /** Nivel de transaccion DB observado al invocar make_payment (U3). */
    public static int $transactionLevelAtCall = -1;

    public static function make_payment(array $data, bool $expectsJson = false)
    {
        static::$lastPaymentData = $data;
        static::$transactionLevelAtCall = \DB::transactionLevel();

        return redirect('https://gateway-stub.example/checkout');
    }

    public function status()
    {
        return response('Stub gateway status: ok');
    }
}
