<?php

namespace App\Library;

use App\Models\Gateway;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class Billplz
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
        $amount = $array['pay_amount'] * 100; // Amount in cents
        $gateway = Gateway::findOrFail($array['gateway_id']);

        // Checking Minimum/Maximum amount
        if ($gateway->min_amount > $amount) {
            session()->put('payment_msg', __('The minimum transaction amount is :amount', ['amount' => currency_format($gateway->min_amount, currency: $gateway->currency)]));

            return redirect(self::redirect_if_payment_faild());
        } elseif ($gateway->max_amount < $amount) {
            session()->put('payment_msg', __('The maximum transaction amount is :amount', ['amount' => currency_format($gateway->max_amount, currency: $gateway->currency)]));

            return redirect(self::redirect_if_payment_faild());
        }

        $apiKey = $array['api_key'];
        $collectionId = $array['collection_id'];
        $callbackUrl = url('/payment/billplz');
        Session::put('billplz_credentials', [
            'api_key' => $apiKey,
            'mode' => $array['mode'],
        ]);
        $client = new Client;

        try {
            $response = $client->post($array['mode'] == 'Live' ? 'https://www.billplz.com/api/v3/bills' : 'https://www.billplz-sandbox.com/api/v3/bills', [
                'auth' => [$apiKey, ''],
                'form_params' => [
                    'collection_id' => $collectionId,
                    'email' => $array['email'],
                    'name' => $array['name'],
                    'amount' => $amount,
                    'callback_url' => $callbackUrl,
                    'description' => $array['payment_type'],
                    'redirect_url' => $callbackUrl,
                ],
            ]);

            $bill = json_decode($response->getBody(), true);

            return redirect($bill['url']);
        } catch (\Exception $e) {
            session()->put('payment_msg', $e->getMessage());

            return redirect(Billplz::redirect_if_payment_faild());
        }
    }

    public function status(Request $request)
    {
        if (! Session::has('billplz_credentials')) {
            return abort(404);
        }

        $creds = Session::get('billplz_credentials');
        $data = $request->all();

        try {
            if (isset($data['billplz']) && isset($data['billplz']['id'])) {
                $bill_id = $data['billplz']['id'];
                $base = ($creds['mode'] ?? '') == 'Live'
                    ? 'https://www.billplz.com'
                    : 'https://www.billplz-sandbox.com';

                $response = Http::withBasicAuth($creds['api_key'], '')
                    ->get($base.'/api/v3/bills/'.$bill_id);

                $bill = $response->json();

                if (is_array($bill) && ($bill['paid'] ?? false) === true) {
                    Log::info("Payment successful for Bill ID: {$bill_id}");

                    Session::put('payment_info', ['status' => 1]);
                    Session::forget('billplz_credentials');

                    return redirect(Billplz::redirect_if_payment_success());
                }

                Log::info("Payment failed for Bill ID: {$bill_id}");
                session()->put('payment_msg', __('Payment failed'));
                Session::forget('billplz_credentials');

                return redirect(Billplz::redirect_if_payment_faild());
            }
        } catch (\Throwable $e) {
            Log::warning('Billplz status verification failed: '.$e->getMessage());
            session()->put('payment_msg', __('Payment failed'));
            Session::forget('billplz_credentials');

            return redirect(Billplz::redirect_if_payment_faild());
        }

        Session::forget('billplz_credentials');

        return redirect(Billplz::redirect_if_payment_faild());
    }
}
