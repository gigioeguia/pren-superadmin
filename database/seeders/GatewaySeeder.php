<?php

namespace Database\Seeders;

use App\Models\Gateway;
use Illuminate\Database\Seeder;

class GatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'Stripe', 
                'currency_id' => '4', 
                'mode' => 'Sandbox', 
                'status' => '1', 
                'charge' => '2', 
                'image' => null, 
                'data' => json_encode([
                    'stripe_key' => env('STRIPE_KEY', 'pk_test_default'),
                    'stripe_secret' => env('STRIPE_SECRET', 'sk_test_default')
                ]), 
                'manual_data' => null, 
                'is_manual' => '0', 
                'accept_img' => '0', 
                'namespace' => 'App\\Library\\StripeGateway', 
                'phone_required' => '0', 
                'instructions' => null, 
                'min_amount' => 0.1, 
                'max_amount' => 10000, 
                'created_at' => now(), 
                'updated_at' => now()
            ],

            [
                'name' => 'Mollie', 
                'currency_id' => '4', 
                'mode' => 'Sandbox', 
                'status' => '1', 
                'charge' => '2', 
                'image' => null, 
                'data' => json_encode([
                    'api_key' => env('MOLLIE_API_KEY', 'test_default')
                ]), 
                'manual_data' => null, 
                'is_manual' => '0', 
                'accept_img' => '0', 
                'namespace' => 'App\\Library\\Mollie', 
                'phone_required' => '0', 
                'instructions' => null, 
                'min_amount' => 0.1, 
                'max_amount' => 10000, 
                'created_at' => now(), 
                'updated_at' => now()
            ],

            [
                'name' => 'FedaPay', 
                'currency_id' => 117, 
                'mode' => 'Sandbox', 
                'status' => '1', 
                'charge' => '2', 
                'image' => null, 
                'data' => json_encode([
                    'api_key' => env('FEDAPAY_API_KEY', 'sk_sandbox_default')
                ]), 
                'manual_data' => null, 
                'is_manual' => '0', 
                'accept_img' => '0', 
                'namespace' => 'App\\Library\\FedaPayGateway', 
                'phone_required' => '0', 
                'instructions' => null, 
                'min_amount' => 0.1, 
                'max_amount' => 1000000, 
                'created_at' => now(), 
                'updated_at' => now()
            ],

            [
                'name' => 'Paystack', 
                'currency_id' => '98', 
                'mode' => 'Sandbox', 
                'status' => '1', 
                'charge' => '2', 
                'image' => null, 
                'data' => json_encode([
                    'public_key' => env('PAYSTACK_PUBLIC_KEY', 'pk_test_default'),
                    'secret_key' => env('PAYSTACK_SECRET_KEY', 'sk_test_default')
                ]), 
                'manual_data' => null, 
                'is_manual' => '0', 
                'accept_img' => '0', 
                'namespace' => 'App\\Library\\Paystack', 
                'phone_required' => '0', 
                'instructions' => null, 
                'min_amount' => 0.1, 
                'max_amount' => 2000, 
                'created_at' => now(), 
                'updated_at' => now()
            ],

            [
                'name' => 'Razorpay', 
                'currency_id' => '60', 
                'mode' => 'Sandbox', 
                'status' => '1', 
                'charge' => '2', 
                'image' => null, 
                'data' => json_encode([
                    'key_id' => env('RAZORPAY_KEY_ID', 'rzp_test_default'),
                    'key_secret' => env('RAZORPAY_KEY_SECRET', 'default_secret')
                ]), 
                'manual_data' => null, 
                'is_manual' => '0', 
                'accept_img' => '0', 
                'namespace' => 'App\\Library\\Razorpay', 
                'phone_required' => '0', 
                'instructions' => null, 
                'min_amount' => 0.1, 
                'max_amount' => 10000, 
                'created_at' => now(), 
                'updated_at' => now()
            ],

            [
                'name' => 'Instamojo', 
                'currency_id' => '60', 
                'mode' => 'Sandbox', 
                'status' => '1', 
                'charge' => '2', 
                'image' => null, 
                'data' => json_encode([
                    'x_api_key' => env('INSTAMOJO_API_KEY', 'test_default'),
                    'x_auth_token' => env('INSTAMOJO_AUTH_TOKEN', 'test_default')
                ]), 
                'manual_data' => null, 
                'is_manual' => '0', 
                'accept_img' => '0', 
                'namespace' => 'App\\Library\\Instamojo', 
                'phone_required' => '1', 
                'instructions' => null, 
                'min_amount' => 0.1, 
                'max_amount' => 5000, 
                'created_at' => now(), 
                'updated_at' => now()
            ],

            [
                'name' => 'Toyyibpay', 
                'currency_id' => '82', 
                'mode' => 'Sandbox', 
                'status' => '1', 
                'charge' => '2', 
                'image' => null, 
                'data' => json_encode([
                    'user_secret_key' => env('TOYYIBPAY_SECRET_KEY', 'default_key'),
                    'cateogry_code' => env('TOYYIBPAY_CATEGORY_CODE', 'default_code')
                ]), 
                'manual_data' => null, 
                'is_manual' => '0', 
                'accept_img' => '0', 
                'namespace' => 'App\\Library\\Toyyibpay', 
                'phone_required' => '1', 
                'instructions' => null, 
                'min_amount' => 0.1, 
                'max_amount' => 1000, 
                'created_at' => now(), 
                'updated_at' => now()
            ],

            [
                'name' => 'Flutterwave', 
                'currency_id' => '98', 
                'mode' => 'Sandbox', 
                'status' => '1', 
                'charge' => '2', 
                'image' => null, 
                'data' => json_encode([
                    'public_key' => env('FLUTTERWAVE_PUBLIC_KEY', 'FLWPUBK_TEST-default'),
                    'secret_key' => env('FLUTTERWAVE_SECRET_KEY', 'FLWSECK_TEST-default'),
                    'encryption_key' => env('FLUTTERWAVE_ENCRYPTION_KEY', 'FLWSECK_TEST-default'),
                    'payment_options' => 'card'
                ]), 
                'manual_data' => null, 
                'is_manual' => '0', 
                'accept_img' => '0', 
                'namespace' => 'App\\Library\\Flutterwave', 
                'phone_required' => '0', 
                'instructions' => null, 
                'min_amount' => 0.1, 
                'max_amount' => 2000, 
                'created_at' => now(), 
                'updated_at' => now()
            ],

            [
                'name' => 'Thawani', 
                'currency_id' => '101', 
                'mode' => 'Sandbox', 
                'status' => '1', 
                'charge' => '2', 
                'image' => null, 
                'data' => json_encode([
                    'secret_key' => env('THAWANI_SECRET_KEY', 'default_key'),
                    'publishable_key' => env('THAWANI_PUBLISHABLE_KEY', 'default_key')
                ]), 
                'manual_data' => null, 
                'is_manual' => '0', 
                'accept_img' => '0', 
                'namespace' => 'App\\Library\\Thawani', 
                'phone_required' => '1', 
                'instructions' => null, 
                'min_amount' => 0.1, 
                'max_amount' => 5000, 
                'created_at' => now(), 
                'updated_at' => now()
            ],

            [
                'name' => 'Tap Payment', 
                'currency_id' => 116, 
                'mode' => 'Sandbox', 
                'status' => '1', 
                'charge' => 2, 
                'image' => null, 
                'data' => json_encode([
                    'secret_key' => env('TAP_SECRET_KEY', 'sk_test_default'),
                    'currency' => 'SAR'
                ]), 
                'manual_data' => null, 
                'is_manual' => '0', 
                'accept_img' => '0', 
                'namespace' => 'App\\Library\\TapPayment', 
                'phone_required' => 0, 
                'instructions' => null, 
                'min_amount' => 0.1, 
                'max_amount' => 10000, 
                'created_at' => now(), 
                'updated_at' => now()
            ],

            [
                'name' => 'Billplz', 
                'currency_id' => '82', 
                'mode' => 'Sandbox', 
                'status' => '1', 
                'charge' => 2, 
                'image' => null, 
                'data' => json_encode([
                    'api_key' => env('BILLPLZ_API_KEY', 'default_key'),
                    'collection_id' => env('BILLPLZ_COLLECTION_ID', 'default_id')
                ]), 
                'manual_data' => null, 
                'is_manual' => '0', 
                'accept_img' => '0', 
                'namespace' => 'App\\Library\\Billplz', 
                'phone_required' => '0', 
                'instructions' => null, 
                'min_amount' => 0.1, 
                'max_amount' => 5000, 
                'created_at' => now(), 
                'updated_at' => now()
            ],

            [
                'name' => 'Offline', 
                'currency_id' => '4', 
                'mode' => 'Sandbox', 
                'status' => '1', 
                'charge' => '0', 
                'image' => null, 
                'data' => '', 
                'manual_data' => '{"bank_info":{"bank_name":"May Bank","acc_holder":"FIRDAUS GROUP SDN BHD","acc_number":"562548543851"},"label":["Payment Note"],"is_required":["0"]}', 
                'is_manual' => '1', 
                'accept_img' => '1', 
                'namespace' => null, 
                'phone_required' => '0', 
                'instructions' => 'The payment require manual approve by the admin within 24~48 hours.', 
                'min_amount' => 0.1, 
                'max_amount' => 100000, 
                'created_at' => now(), 
                'updated_at' => '2025-01-04 07:29:37'
            ],
        ];

        Gateway::insert($gateways);
    }
}