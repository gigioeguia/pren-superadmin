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

    public static function make_payment(array $data)
    {
        static::$lastPaymentData = $data;

        return redirect('https://gateway-stub.example/checkout');
    }

    public function status()
    {
        return response('Stub gateway status: ok');
    }
}
