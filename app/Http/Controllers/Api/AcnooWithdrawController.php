<?php

namespace App\Http\Controllers\Api;

use App\Models\Withdraw;
use App\Models\UserMethod;
use Illuminate\Http\Request;
use App\Models\WithdrawMethod;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Transaction;

class AcnooWithdrawController extends Controller
{
    public function index()
    {
        $withdraws = Withdraw::select('id', 'user_method_id', 'status', 'created_at', 'amount')->with('account_info:id,method_id', 'account_info.withdraw_method:id,name')->where('user_id', auth()->id())
            ->when(request('from_date') && request('to_date'), function ($q) {
                $q->whereDate('created_at', '>=', request('from_date'));
                $q->whereDate('created_at', '<=', request('to_date'));
            })
            ->paginate($request->per_page ?? 10);

        return response([
            'meassage' => 'Data fetched successfully',
            'data' => $withdraws
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_method_id' => 'required|integer',
            'amount' => 'required|string',
            'status' => 'nullable|string'
        ]);

        $user = auth()->user();
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
                'message' => 'Withdraw amount must be at least ' . $method->min_amount
            ], 400);
        }

        if ($request->amount > $method->max_amount) {
            return response()->json([
                'message' => 'Withdraw amount cannot exceed ' . $method->max_amount
            ], 400);
        }

        DB::beginTransaction();
        try {

            if ($user->balance >= $total_amount) {

                $withdraw = Withdraw::create([
                    'user_id' => $user->id,
                    'amount' => $request->amount,
                    'user_method_id' => $user_method->id,
                    'charge' => $charge,
                ]);

                $user->update([
                    'balance' => $user->balance - $total_amount,
                ]);

                sendNotification($withdraw->id, notify_users([$withdraw->user->id]), landlord_url: route('landlord.withdraw.index', ['id' => $withdraw->id]),  admin_msg: __('A new withdrawal request has been submitted. Please review and process it.'), landlord_msg: __('Your withdrawal request has been submitted successfully. Awaiting approval.'));

                DB::commit();
                return response()->json([
                    'message' => 'Balance withdraw request send successfully',
                    'data' => $withdraw
                ]);
            } else {
                return response()->json(
                    [
                        'message' => 'Insufficient balance'
                    ],
                    400
                );
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Transaction failed: ' . $e->getMessage()], 500);
        }
    }

    public function invoice(string $id)
    {
        $withdraw = Withdraw::with(['user:id,name,phone,email', 'user.user_details:user_id,address_info', 'account_info:id,method_id', 'account_info.withdraw_method:id,charge_type'])->findOrFail($id);

        $errors = session()->get('errors', new \Illuminate\Support\ViewErrorBag);
        $html = view('api-invoice.withdraw', compact('withdraw', 'errors'))->render();


        return response()->json([
            'message' => 'Data fetched successfully.',
            'data' => [
                'id' => $withdraw->id,
                'amount' => $withdraw->amount
            ],
            'html' => $html
        ]);
    }

    public function summary(string $id)
    {
        $withdraw = Withdraw::findOrFail($id);
        $summary = Transaction::select('id', 'withdraw_id', 'uid', 'payment_type')->with('withdraw:id,user_method_id,amount,charge,status,created_at', 'withdraw.account_info:id,method_id', 'withdraw.account_info.withdraw_method:id,name')->where('withdraw_id', $withdraw->id)->first();

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $summary
        ]);
    }
}
