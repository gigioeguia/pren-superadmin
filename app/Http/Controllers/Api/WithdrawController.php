<?php

namespace App\Http\Controllers\Api;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserMethod;
use App\Models\Withdraw;
use App\Models\WithdrawMethod;
use App\Services\WithdrawService;
use App\Support\ErrorMessage;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;

class WithdrawController extends Controller
{
    public function __construct(protected WithdrawService $service) {}

    public function index(Request $request)
    {
        $withdraws = Withdraw::select('id', 'user_method_id', 'status', 'created_at', 'amount')->with('account_info:id,method_id', 'account_info.withdraw_method:id,name')->where('user_id', auth()->id())
            ->when($request->input('from_date') && $request->input('to_date'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->input('from_date'));
                $q->whereDate('created_at', '<=', $request->input('to_date'));
            })
            ->paginate($request->per_page ?? 10);

        return response([
            'message' => 'Data fetched successfully',
            'data' => $withdraws,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_method_id' => 'required|integer',
            'amount' => 'required|string',
            'status' => 'nullable|string',
        ]);

        // Tipado explícito: auth()->user() es union de guards con modelos
        // fantasma en config (spec 5.11 — writes sobre User tipado).
        $user = User::findOrFail(auth()->id());
        $user_method = UserMethod::findOrFail($request->user_method_id);
        $method = WithdrawMethod::findOrFail($user_method->method_id);

        // Calculate charge
        if ($method->charge_type === 'percentage') {
            $charge = ($request->amount / 100) * $method->charge;
        } elseif ($method->charge_type === 'fixed') {
            $charge = $method->charge;
        } else {
            $charge = 0;
        }

        $total_amount = $request->amount + $charge;

        // Check method min and max amount
        if ($request->amount < $method->min_amount) {
            return response()->json([
                'message' => 'Withdraw amount must be at least '.$method->min_amount,
            ], 400);
        }

        if ($request->amount > $method->max_amount) {
            return response()->json([
                'message' => 'Withdraw amount cannot exceed '.$method->max_amount,
            ], 400);
        }

        // Lectura fuera de la tx (Fase 4/4.8): la unidad atómica vive en el service
        if ($user->balance < $total_amount) {
            return response()->json([
                'message' => 'Insufficient balance',
            ], 400);
        }

        try {
            $withdraw = $this->service->store($user, $user_method, $request->amount, $charge, Channel::Api);

            return response()->json([
                'message' => 'Balance withdraw request send successfully',
                'data' => $withdraw,
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['error' => __(ErrorMessage::TRANSACTION)], 500);
        }
    }

    public function invoice(string $id)
    {
        $withdraw = Withdraw::with(['user:id,name,phone,email', 'user.user_details:user_id,address_info', 'account_info:id,method_id', 'account_info.withdraw_method:id,charge_type'])->findOrFail($id);
        $this->authorize('invoice', $withdraw);

        $errors = session()->get('errors', new ViewErrorBag);
        $html = view('api-invoice.withdraw', compact('withdraw', 'errors'))->render();

        return response()->json([
            'message' => 'Data fetched successfully.',
            'data' => [
                'id' => $withdraw->id,
                'amount' => $withdraw->amount,
            ],
            'html' => $html,
        ]);
    }

    public function summary(string $id)
    {
        $withdraw = Withdraw::findOrFail($id);
        $this->authorize('summary', $withdraw);
        $summary = Transaction::select('id', 'withdraw_id', 'uid', 'payment_type')->with('withdraw:id,user_method_id,amount,charge,status,created_at', 'withdraw.account_info:id,method_id', 'withdraw.account_info.withdraw_method:id,name')->where('withdraw_id', $withdraw->id)->first();

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $summary,
        ]);
    }
}
