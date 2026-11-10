<?php

namespace App\Http\Controllers\Landlord;

use App\Exports\LandlordWithdrawMethodExport;
use App\Models\Withdraw;
use App\Models\UserMethod;
use Illuminate\Http\Request;
use App\Models\WithdrawMethod;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class AcnooWithdrawController extends Controller
{
    public function index()
    {
        $withdraws = Withdraw::with('account_info.withdraw_method:id,name,charge_type')
                                ->where('user_id', auth()->id())
                                ->latest()
                                ->paginate(10);
        return view('landlord.withdraw-request.index', compact('withdraws'));
    }

    public function acnooFilter(Request $request)
    {
        $withdraws = Withdraw::with(['account_info.withdraw_method:id,name'])
            ->where('user_id', auth()->id())
            ->when(request('search'), function ($q) {
                $search = request('search');

                $q->where('amount', 'like', '%' . $search . '%')
                    ->orWhere('charge', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhereHas('account_info.withdraw_method', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('account_info', function ($q) use ($search) {
                        $q->where('infos->bank_name', 'like', '%' . $search . '%');
                    });
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.withdraw-request.datas', compact('withdraws'))->render(),
            ]);
        }

        return redirect(url()->previous());
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

                Withdraw::create([
                    'user_id' => $user->id,
                    'amount' => $request->amount,
                    'user_method_id' => $request->user_method_id,
                    'charge' => $charge,
                ]);

                $user->update([
                    'balance' => $user->balance - $total_amount,
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'withdraw request has been send',
                    'modal' => true,
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
            return response()->json(['error' => 'Transaction failed'], 500);
        }
    }

    public function show(string $id)
    {
        $withdraw = Withdraw::with(['user:id,name,phone,email', 'user.user_details:user_id,address_info','account_info:id,method_id', 'account_info.withdraw_method:id,charge_type'])->findOrFail($id);

        return view('landlord.withdraw-request.invoice', compact('withdraw'));
    }

    public function exportExcel()
    {
        return Excel::download(new LandlordWithdrawMethodExport, 'landlord-withdraw-method.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new LandlordWithdrawMethodExport, 'landlord-withdraw-method.csv');
    }
}
