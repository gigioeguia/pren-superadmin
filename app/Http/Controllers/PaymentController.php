<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidPaymentTypeException;
use App\Services\PaymentService;
use App\Support\PaymentOutcome;
use App\Support\PaymentState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $payments) {}

    // security deposit & rent payment gateway
    public function DepositRentPayment(Request $request, string $id)
    {
        $paymentType = $request->input('payment_type');

        try {
            $context = $this->payments->depositRentPayment((int) $id, is_string($paymentType) ? $paymentType : null);
        } catch (InvalidPaymentTypeException) {
            return back()->with('error', __('Wrong Payment type provided!'));
        }

        session()->put('payment_type', $context['payment_type']);

        // Defense in depth: if a web session exists, the authenticated user must be the payer.
        if (auth()->check() && $context['tenant_id'] && auth()->id() !== (int) $context['tenant_id']) {
            abort(403);
        }

        session()->put('platform', $request->input('platform') ?? 'web');
        session()->put('user_id', $context['tenant_id']);

        return view('payments.online_payment', ['gateways' => $context['gateways'], 'plan' => $context['plan']]);
    }

    // subscription payment gateways
    public function index(Request $request, string $id, string $landlord_id)
    {
        $platform = $request->input('platform');

        ['plan' => $plan, 'gateways' => $gateways] = $this->payments->gatewayIndex((int) $id, is_string($platform) ? $platform : null);

        // The URL is signed; still, when a web session exists the authenticated
        // user must match the landlord encoded in the signed URL.
        if (auth()->check() && auth()->id() !== (int) $landlord_id) {
            abort(403);
        }

        session()->put('platform', $platform ?? 'web');
        session()->put('user_id', $landlord_id);
        session()->put('payment_type', 'subscription');

        return view('payments.index', compact('gateways', 'plan'));
    }

    public function payment(Request $request, string $plan_id, string $gateway_id)
    {
        $request->validate(['phone' => 'max:15|min:5']);

        if (! session('payment_type')) {
            return $this->invalidCredentials();
        }

        try {
            $context = $this->payments->resolvePayment(
                (string) session('payment_type'),
                (int) $plan_id,
                (int) $gateway_id,
                (int) session('user_id'),
            );
        } catch (InvalidPaymentTypeException) {
            return $this->invalidCredentials();
        }

        if ($context->gateway->is_manual) {
            $request->validate(['attachment' => 'required|file|mimes:jpg,jpeg,png|max:2048']);

            try {
                $this->payments->payManual($context, $request->input('manual_data'), $request->file('attachment'));
            } catch (\Exception) {
                return redirect(route('order.status', ['status' => 'failed']))->with('message', __('Something went wrong!'));
            }

            session()->forget('user_id');

            return redirect(route('order.status', ['status' => 'success']))->with('message', __('New subscription purchased requested.'));
        }

        session()->put('fund_callback.success_url', '/payment/success');
        session()->put('fund_callback.cancel_url', '/payment/failed');

        session()->put('user', $context->user);
        session()->put('plan', $context->plan);
        session()->put('gateway_id', $context->gateway->id);
        session()->put('amount', $context->amount);

        $phone = $request->input('phone');

        return $this->payments->payGateway($context, is_string($phone) ? $phone : null, is_string(session('platform')) ? session('platform') : null, $request->expectsJson());
    }

    public function success(Request $request)
    {
        $stripeSessionId = $request->query('session_id');
        $state = PaymentState::fromSession(is_string($stripeSessionId) ? $stripeSessionId : null);

        if (auth()->guest()) {
            return redirect(route('login'));
        }

        // Identity check: the authenticated user must be the session payer.
        if ($state->user && auth()->id() !== (int) $state->user->id && auth()->id() !== (int) $state->userId) {
            abort(403);
        }

        // Idempotency: already processed in this session.
        if (session('payment_processed')) {
            session()->forget(['plan', 'plan_id', 'user', 'user_id', 'gateway_id', 'amount', 'payment_info', 'payment_processed']);

            return redirect(route('order.status', ['status' => 'success']))->with('message', __('Payment successfully.'));
        }

        $outcome = $this->payments->success($state);

        if ($outcome === PaymentOutcome::Success) {
            session()->put('payment_processed', true);
            session()->forget(['plan', 'plan_id', 'user', 'user_id', 'gateway_id', 'amount', 'payment_info']);
        }

        return redirect(route('order.status', ['status' => $outcome->status()]))
            ->with($outcome->flashKey(), __($outcome->message()));
    }

    /**
     * Mint a signed payment URL for clients without a web session (mobile app).
     */
    public function paymentUrl(Request $request)
    {
        $request->validate(['payment_type' => 'required|string|in:subscription,security_deposit,rent_payment']);

        $actorId = (int) auth()->id();
        $type = $request->payment_type;

        if ($type === 'subscription') {
            $request->validate(['plan_id' => 'required|exists:plans,id']);
            $url = $this->payments->subscriptionUrl((int) $request->plan_id, $actorId);
        } elseif ($type === 'security_deposit') {
            $request->validate(['payment_id' => 'required|exists:security_deposits,id']);
            $url = $this->payments->depositUrl((int) $request->payment_id, $actorId);
        } else {
            $request->validate(['payment_id' => 'required|exists:rent_payments,id']);
            $url = $this->payments->rentUrl((int) $request->payment_id, $actorId);
        }

        return response()->json(['url' => $url]);
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

    public function orderStatus(Request $request)
    {
        if (session('platform') == 'web' && session('payment_type') == 'subscription') {
            return redirect(route('landlord.subscriptions.index'))->with('message', $request->input('message') ?? __('New subscription order successfully.'));
        }

        session()->forget('platform');
        session()->forget('payment_type');

        return $request->input('status');
    }

    private function invalidCredentials(): RedirectResponse
    {
        return redirect(route('order.status', ['status' => 'failed']))
            ->with('error', __('Transaction failed for invalid credentials, Please try again later or contact with admin.'));
    }
}
