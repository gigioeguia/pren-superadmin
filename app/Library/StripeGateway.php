<?php

namespace App\Library;

use Stripe\Stripe;
use App\Models\Gateway;
use Illuminate\Support\Facades\Session;

class StripeGateway
{
    public static function redirect_if_payment_success()
    {
        if (Session::has('fund_callback')) {
            return url(Session::get('fund_callback')['success_url']);
        } else {
            return url('payment/success');
        }
    }

    public static function redirect_if_payment_faild()
    {
        if (Session::has('fund_callback')) {
            return url(Session::get('fund_callback')['cancel_url']);
        } else {
            return url('payment/failed');
        }
    }

    public static function make_payment($array)
    {
        $amount = $array['pay_amount'];
        $gateway = Gateway::findOrFail($array['gateway_id']);

        // Checking Minimum/Maximum amount
        if ($gateway->min_amount > $amount) {
            session()->put('payment_msg', __('The minimum transaction amount is :amount', ['amount' => currency_format($gateway->min_amount, currency: $gateway->currency)]));
            return redirect(self::redirect_if_payment_faild());
        } elseif ($gateway->max_amount < $amount) {
            session()->put('payment_msg', __('The maximum transaction amount is :amount', ['amount' => currency_format($gateway->max_amount, currency: $gateway->currency)]));
            return redirect(self::redirect_if_payment_faild());
        }

        Stripe::setApiKey($gateway->data['stripe_secret']);

        $amount = $array['pay_amount'] * 100; // Amount In Cents

        $session = \Stripe\Checkout\Session::create([
            'line_items'  => [
                [
                    'price_data' => [
                        'currency'     => $array['currency'] ?? 'USD',
                        'product_data' => [
                            "name" => $array['billName'],
                        ],
                        'unit_amount'  => $amount,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => route('payment.success'),
            'cancel_url'  => route('payment.failed'),
        ]);

        return redirect()->away($session->url);
    }
}
