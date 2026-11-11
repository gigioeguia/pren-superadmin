<?php

namespace App\Http\Controllers;

use App\Helpers\HasUploader;
use App\Models\Gateway;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\RentPayment;
use App\Models\SecurityDeposit;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PaymentController extends Controller
{
    use HasUploader;

    // security deposit & rent payment gateway
    public function DepositRentPayment($id, $tenant_id)
    {
        if (request('payment_type') == 'security_deposit') {
            $plan = SecurityDeposit::findOrFail($id);
            session()->put('payment_type', 'security_deposit');
        } elseif (request('payment_type') == 'rent_payment') {
            $plan = RentPayment::findOrFail($id);
            session()->put('payment_type', 'rent_payment');
        } else {
            return back()->with('error', __('Wrong Payment type provided!'));
        }

        $gateways = Gateway::with('currency:id,code,rate,symbol,position')->where(['status' => 1, 'is_manual' => 0])->get();

        session()->put('platform', request('platform') ?? 'web');
        session()->put('user_id', $tenant_id);

        return view('payments.online_payment', compact('gateways', 'plan'));
    }

    // subscription payment gateways
    public function index(string $id, string $landlord_id)
    {
        $plan = Plan::findOrFail($id);

        if (request('platform') == 'app') {
            $gateways = Gateway::with('currency:id,code,rate,symbol,position')->where(['status' => 1, 'is_manual' => 0])->get();
        } else {
            $gateways = Gateway::with('currency:id,code,rate,symbol,position')->get();
        }

        session()->put('platform', request('platform') ?? 'web');
        session()->put('user_id', $landlord_id);
        session()->put('payment_type', 'subscription');

        return view('payments.index', compact('gateways', 'plan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function payment(Request $request, string $plan_id, string $gateway_id)
    {
        $request->validate([
            'phone' => 'max:15|min:5',
        ]);

        $payment_type = session('payment_type');
        if (! session('payment_type')) {
            return redirect(route('order.status', ['status' => 'failed']))->with('error', __('Transaction failed for invalid credentials, Please try again later or contact with admin.'));
        }

        $gateway = Gateway::findOrFail($gateway_id);
        $user = User::findOrFail(session('user_id'));

        if ($payment_type == 'security_deposit') {
            $plan = SecurityDeposit::with('tenant:id,name,email,phone')->findOrFail($plan_id);
            $amount = $plan->total_amount;
        } elseif ($payment_type == 'rent_payment') {
            $plan = RentPayment::with(['rent:id,tenant_id', 'rent.tenant:id,name,email,phone'])->findOrFail($plan_id);
            $amount = $plan->total_amount;
        } elseif ($payment_type == 'subscription') {
            $plan = Plan::findOrFail($plan_id);
            $amount = $plan->offerPrice ?? $plan->subscriptionPrice;
        } else {
            return redirect(route('order.status', ['status' => 'failed']))->with('error', __('Transaction failed for invalid credentials, Please try again later or contact with admin.'));
        }

        if ($gateway->is_manual) {
            $request->validate([
                'attachment' => 'required|mimes:jpg,jpeg,png|max:2048|file',
            ]);
            DB::beginTransaction();
            try {
                $attachment = $request->attachment ? $this->upload($request, 'attachment') : null;
                $duration = 0;
                // in days
                if ($plan->duration_type == 'month') {
                    $duration = $plan->duration * 30;
                } elseif ($plan->duration_type == 'year') {
                    $duration = $plan->duration * 365;
                } elseif ($plan->duration_type == 'day') {
                    $duration = $plan->duration;
                }

                PlanSubscribe::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'gateway_id' => $gateway_id,
                    'price' => $amount,
                    'duration' => $duration,
                    'payment_status' => 'unpaid',
                    'notes' => [
                        'manual_data' => $request->manual_data,
                        'attachment' => $attachment,
                    ],
                ]);

                DB::commit();

                session()->forget('user_id');

                return redirect(route('order.status', ['status' => 'success']))->with('message', __('New subscription purchased requested.'));
            } catch (\Exception $e) {
                DB::rollback();

                return redirect(route('order.status', ['status' => 'failed']))->with('message', __('Something went wrong!'));
            }
        }

        Session::put('fund_callback.success_url', '/payment/success');
        Session::put('fund_callback.cancel_url', '/payment/failed');

        $payment_data['currency'] = $gateway->currency->code ?? 'USD';
        $payment_data['email'] = $user->email ?? null;
        $payment_data['name'] = $user->name ?? null;
        $payment_data['phone'] = $request->phone ? $user->phone : null;
        $payment_data['billName'] = __('Make plan purchase payment');
        $payment_data['amount'] = $amount;
        $payment_data['mode'] = $gateway->mode;
        $payment_data['charge'] = $gateway->charge ?? 0;
        $payment_data['pay_amount'] = round(convert_money($amount, $gateway->currency) + $gateway->charge);
        $payment_data['gateway_id'] = $gateway->id;
        $payment_data['payment_type'] = $payment_type;
        $payment_data['request_from'] = 'merchant';
        $payment_data['plan_id'] = $plan_id;
        $payment_data['platform'] = session('platform');

        foreach ($gateway->data ?? [] as $key => $info) {
            $payment_data[$key] = $info;
        }

        session()->put('user', $user);
        session()->put('plan', $plan);
        session()->put('gateway_id', $gateway->id);
        session()->put('amount', $amount);

        $redirect = $gateway->namespace::make_payment($payment_data);

        return $redirect;
    }

    public function success()
    {
        DB::beginTransaction();
        try {
            $plan = session('plan');
            $user = session('user');
            $amount = session('amount');
            $payment_type = session('payment_type');
            $gateway_id = session('gateway_id');
            $gateway = Gateway::findOrFail($gateway_id);

            if (! $plan && ! session('plan_id') || ! session('payment_type')) {
                return redirect(route('order.status', ['status' => 'failed']))->with('error', __('Transaction failed for invalid credentials, Please try again later or contact with admin.'));
            }

            if (session('plan_id') && ! $plan) {
                if ($payment_type == 'security_deposit') {
                    $plan = SecurityDeposit::findOrFail(session('plan_id'));
                }
                if ($payment_type == 'rent_payment') {
                    $plan = RentPayment::findOrFail(session('plan_id'));
                }
                if ($payment_type == 'subscription') {
                    $plan = Plan::findOrFail(session('plan_id'));
                }
            }

            if (session('user_id') && ! $user) {
                $user = User::findOrFail(session('user_id'));
            }

            if ($payment_type == 'security_deposit') {
                $landlord = User::where('id', $plan->landlord_id)->first();

                $landlord->update([
                    'balance' => $landlord->balance + $plan->landlord_balance,
                ]);

                // security deposit
                $plan->update([
                    'payment_date' => today(),
                    'gateway_id' => $gateway_id,
                    'status' => 'paid',
                ]);

                Transaction::create([
                    'paid_by' => $user->id,
                    'received_by' => $landlord->id,
                    'gateway_id' => $gateway_id,
                    'security_deposit_id' => $plan->id,
                    'date' => today(),
                    'type' => 'debit',
                    'payment_method' => $gateway->name ?? null,
                    'payment_type' => 'security_deposit',
                    'amount' => $plan->landlord_balance ?? 0,
                    'charge' => $plan->charge ?? 0,
                ]);
            }

            if ($payment_type == 'rent_payment') {
                $landlord = User::where('id', $plan->rent->landlord_id ?? '')->first();

                $landlord->update([
                    'balance' => $landlord->balance + $plan->total_amount,
                ]);
                // rent payment
                $plan->update([
                    'payment_date' => today(),
                    'gateway_id' => $gateway_id,
                    'payment_status' => 'paid',
                ]);

                Transaction::create([
                    'paid_by' => $user->id,
                    'received_by' => $landlord->id,
                    'gateway_id' => $gateway_id,
                    'rent_payment_id' => $plan->id,
                    'date' => today(),
                    'amount' => $plan->subtotal_amount ?? 0,
                    'charge' => $plan->charge ?? 0,
                    'type' => 'debit',
                    'payment_method' => $gateway->name ?? null,
                    'payment_type' => 'rent_payment',
                ]);
            }

            if ($payment_type == 'subscription') {
                $admin = User::whereIn('role', ['superadmin', 'admin'])->first();
                $duration = 0;
                // in days
                if ($plan->duration_type == 'month') {
                    $duration = $plan->duration * 30;
                } elseif ($plan->duration_type == 'year') {
                    $duration = $plan->duration * 365;
                } elseif ($plan->duration_type == 'day') {
                    $duration = $plan->duration;
                }

                $subscribe = PlanSubscribe::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->subscriptionName,
                    'number_of_property' => $plan->number_of_property,
                    'number_of_tenant' => $plan->number_of_tenant,
                    'number_of_labor' => $plan->number_of_labor,
                    'duration' => $duration,
                    'price' => $amount,
                    'gateway_id' => $gateway_id,
                    'payment_status' => 'paid',
                ]);

                Transaction::create([
                    'paid_by' => $user->id,
                    'received_by' => $admin->id,
                    'gateway_id' => $gateway_id,
                    'plan_subscribe_id' => $subscribe->id,
                    'date' => today(),
                    'amount' => $amount,
                    'type' => 'credit',
                    'payment_method' => $gateway->name ?? null,
                    'payment_type' => 'subscription',
                ]);

                $user->update([
                    'subscriptionDate' => now(),
                    'plan_subscribe_id' => $subscribe->id,
                    'will_expire' => now()->addDays($duration),
                ]);
            }

            Cache::forget('plan-data-'.$user->id);
            session()->forget('plan');
            session()->forget('plan_id');
            session()->forget('user');
            session()->forget('user_id');
            session()->forget('gateway_id');
            session()->forget('amount');

            DB::commit();

            return redirect(route('order.status', ['status' => 'success']))->with('message', __('Payment successfully.'));
        } catch (\Exception $e) {
            DB::rollback();

            return redirect(route('order.status', ['status' => 'failed']))->with('message', __('Something went wrong!'));
        }
    }

    public function failed()
    {
        $payment_msg = session('payment_msg');
        session()->forget('payment_msg');

        if (session('platform') == 'web') {
            return redirect(route('landlord.subscriptions.index'))->with('error', $payment_msg ?? __('Transaction failed for invalid credentials, Please try again later or contact with admin.'));
        }

        return redirect(route('order.status', ['status' => 'failed', 'message' => $payment_msg ?? __('Transaction failed for invalid credentials, Please try again later or contact with admin.')]));
    }

    public function orderStatus()
    {
        if (session('platform') == 'web' && session('payment_type') == 'subscription') {
            return redirect(route('landlord.subscriptions.index'))->with('message', request('message') ?? __('New subscription order successfully.'));
        }

        session()->forget('platform');
        session()->forget('payment_type');

        return request('status');
    }
}
