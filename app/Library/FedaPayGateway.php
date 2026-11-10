<?php

namespace App\Library;

use FedaPay\FedaPay;
use App\Models\Gateway;
use FedaPay\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class FedaPayGateway
{
    public static function redirect_if_payment_success()
    {
        if (Session::has('fund_callback')) {
            return url(Session::get('fund_callback')['success_url']);
        } else {
            return url('payment/success');
        }
    }

    public static function redirect_if_payment_failed()
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
            return redirect(self::redirect_if_payment_failed());
        } elseif ($gateway->max_amount < $amount) {
            session()->put('payment_msg', __('The maximum transaction amount is :amount', ['amount' => currency_format($gateway->max_amount, currency: $gateway->currency)]));
            return redirect(self::redirect_if_payment_failed());
        }

        try {
            // Setup API key and mode
            FedaPay::setApiKey($array['api_key']);
            FedaPay::setEnvironment($array['mode'] === 'Live' ? 'live' : 'sandbox');

            $tx = Transaction::create([
                'description' => $data['billName'] ?? 'Make plan purchase payment',
                'amount' => (int)$amount,
                'currency' => ['iso' => $array['currency'] ?? 'XOF'],
                'callback_url' => route('fedapay.status'),
                'customer' => [
                    'firstname' => $array['name'] ?? '',
                    'lastname' => $array['lastname'] ?? 'Demo',
                    'email' => $array['email'] ?? '',
                ],
            ]);

            // 3) Generate token / checkout URL and return it
            $token = $tx->generateToken();
            return redirect($token->url);
        } catch (\Exception $e) {
            session()->put('payment_msg', $e->getMessage());
            return redirect(self::redirect_if_payment_failed());
        }
    }

    public function status(Request $request)
    {
        try {
            if ($request->status === 'approved') {
                return redirect(self::redirect_if_payment_success());
            } else {
                session()->put('payment_msg', __('Payment failed'));
                return redirect(self::redirect_if_payment_failed());
            }
        } catch (\Exception $e) {
            session()->put('payment_msg', $e->getMessage());
            return redirect(self::redirect_if_payment_failed());
        }
    }
}
