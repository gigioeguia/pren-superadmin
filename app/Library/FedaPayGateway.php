<?php

namespace App\Library;

use App\Models\Gateway;
use FedaPay\FedaPay;
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

            Session::put('fedapay_credentials', [
                'api_key' => $array['api_key'],
                'mode' => $array['mode'],
            ]);

            $tx = Transaction::create([
                'description' => $data['billName'] ?? 'Make plan purchase payment',
                'amount' => (int) $amount,
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
        if (! Session::has('fedapay_credentials')) {
            return abort(404);
        }

        $creds = Session::get('fedapay_credentials');
        $tx_id = $request->input('id') ?? $request->input('transaction_id');

        try {
            FedaPay::setApiKey($creds['api_key']);
            FedaPay::setEnvironment(($creds['mode'] ?? '') === 'Live' ? 'live' : 'sandbox');

            if ($tx_id) {
                $tx = Transaction::retrieve($tx_id);
                $txArray = json_decode(json_encode($tx), true);
                if (is_array($txArray) && ($txArray['status'] ?? null) === 'approved') {
                    Session::forget('fedapay_credentials');

                    return redirect(self::redirect_if_payment_success());
                }
            }
        } catch (\Throwable $e) {
            Log::warning('FedaPay status verification failed: '.$e->getMessage());
        }

        session()->put('payment_msg', __('Payment failed'));
        Session::forget('fedapay_credentials');

        return redirect(self::redirect_if_payment_failed());
    }
}
