<?php

use App\Library\Billplz;
use App\Library\FedaPayGateway;
use App\Library\Flutterwave;
use App\Library\Instamojo;
use App\Library\Mollie;
use App\Library\Paystack;
use App\Library\Razorpay;
use App\Library\StripeGateway;
use App\Library\TapPayment;
use App\Library\Thawani;
use App\Library\Toyyibpay;

return [
    /*
    |--------------------------------------------------------------------------
    | Allowed gateway namespaces
    |--------------------------------------------------------------------------
    |
    | Whitelist of fully-qualified class names that may be invoked dynamically
    | from the `gateways.namespace` column. Any value not listed here (including
    | non-existent classes) is rejected at invocation time (fail-closed), which
    | prevents RCE if the `gateways` table is compromised (Seg-6 / Fase 2.5).
    |
    | Adding a new gateway requires updating this list alongside the class file
    | and the database row (see AGENTS.md).
    |
    */
    'allowed_namespaces' => [
        StripeGateway::class,
        Mollie::class,
        FedaPayGateway::class,
        Paystack::class,
        Razorpay::class,
        Instamojo::class,
        Toyyibpay::class,
        Flutterwave::class,
        Thawani::class,
        TapPayment::class,
        Billplz::class,
    ],
];
