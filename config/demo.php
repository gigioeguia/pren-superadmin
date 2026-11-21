<?php

return [

    /*
    |----------------------------------------------------------------------
    | Demo (Fase 5 / 5.2 — spec-demo-user-passwords-fase5)
    |----------------------------------------------------------------------
    | Password compartida por los 4 usuarios demo cuando se fija en el
    | .env local (solo evaluación, nunca producción). Vacía: se genera una
    | aleatoria por usuario y se imprime por consola al crear la cuenta.
    */

    'user_password' => env('DEMO_USER_PASSWORD'),

];
