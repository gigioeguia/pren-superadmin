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

    /*
    |--------------------------------------------------------------------------
    | Seeded sandbox credentials
    |--------------------------------------------------------------------------
    |
    | Credenciales sandbox/test que GatewaySeeder siembra en la columna
    | `gateways.data`. env() solo es legal en archivos de config (Fase 4 / 4.3).
    |
    */
    'credentials' => [
        'stripe' => [
            'stripe_key' => env('STRIPE_KEY', 'pk_test_default'),
            'stripe_secret' => env('STRIPE_SECRET', 'sk_test_default'),
        ],
        'mollie' => [
            'api_key' => env('MOLLIE_API_KEY', 'test_default'),
        ],
        'fedapay' => [
            'api_key' => env('FEDAPAY_API_KEY', 'sk_sandbox_default'),
        ],
        'paystack' => [
            'public_key' => env('PAYSTACK_PUBLIC_KEY', 'pk_test_default'),
            'secret_key' => env('PAYSTACK_SECRET_KEY', 'sk_test_default'),
        ],
        'razorpay' => [
            'key_id' => env('RAZORPAY_KEY_ID', 'rzp_test_default'),
            'key_secret' => env('RAZORPAY_KEY_SECRET', 'default_secret'),
        ],
        'instamojo' => [
            'x_api_key' => env('INSTAMOJO_API_KEY', 'test_default'),
            'x_auth_token' => env('INSTAMOJO_AUTH_TOKEN', 'test_default'),
        ],
        'toyyibpay' => [
            'user_secret_key' => env('TOYYIBPAY_SECRET_KEY', 'default_key'),
            // typo original preservada: la librería Toyyibpay lee esta key
            'cateogry_code' => env('TOYYIBPAY_CATEGORY_CODE', 'default_code'),
        ],
        'flutterwave' => [
            'public_key' => env('FLUTTERWAVE_PUBLIC_KEY', 'FLWPUBK_TEST-default'),
            'secret_key' => env('FLUTTERWAVE_SECRET_KEY', 'FLWSECK_TEST-default'),
            'encryption_key' => env('FLUTTERWAVE_ENCRYPTION_KEY', 'FLWSECK_TEST-default'),
        ],
        'thawani' => [
            'secret_key' => env('THAWANI_SECRET_KEY', 'default_key'),
            'publishable_key' => env('THAWANI_PUBLISHABLE_KEY', 'default_key'),
        ],
        'tap' => [
            'secret_key' => env('TAP_SECRET_KEY', 'sk_test_default'),
        ],
        'billplz' => [
            'api_key' => env('BILLPLZ_API_KEY', 'default_key'),
            'collection_id' => env('BILLPLZ_COLLECTION_ID', 'default_id'),
        ],
    ],
];
