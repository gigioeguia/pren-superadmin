<?php

namespace App\Http\Controllers\Landlord;

use App\Exports\LandlordWithdrawMethodExport;
use App\Http\Controllers\Controller;
use App\Models\UserMethod;
use App\Models\Withdraw;
use App\Models\WithdrawMethod;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcnooWithdrawController extends Controller
{
    use HasAcnooActions;

    public function index()
    {
        $withdraws = Withdraw::with('account_info.withdraw_method:id,name,charge_type')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('landlord.withdraw-request.index', compact('withdraws'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_method_id' => 'required|integer',
            'amount' => 'required|string',
            'status' => 'nullable|string',
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
                'message' => 'Withdraw amount must be at least '.$method->min_amount,
            ], 400);
        }

        if ($request->amount > $method->max_amount) {
            return response()->json([
                'message' => 'Withdraw amount cannot exceed '.$method->max_amount,
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
                        'message' => 'Insufficient balance',
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
        $withdraw = Withdraw::with(['user:id,name,phone,email', 'user.user_details:user_id,address_info', 'account_info:id,method_id', 'account_info.withdraw_method:id,charge_type'])->findOrFail($id);

        return view('landlord.withdraw-request.invoice', compact('withdraw'));
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return Withdraw::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return Withdraw::with(['account_info.withdraw_method:id,name'])
            ->where('user_id', auth()->id())
            ->when(request('search'), function ($q) {
                $search = request('search');

                $q->where('amount', 'like', '%'.$search.'%')
                    ->orWhere('charge', 'like', '%'.$search.'%')
                    ->orWhere('status', 'like', '%'.$search.'%')
                    ->orWhereHas('account_info.withdraw_method', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('account_info', function ($q) use ($search) {
                        $q->where('infos->bank_name', 'like', '%'.$search.'%');
                    });
            })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'landlord.withdraw-request.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['withdraws' => $rows];
    }

    protected function acnooPerPage(): int
    {
        return 20;
    }

    protected function acnooExport(Request $request): object
    {
        return new LandlordWithdrawMethodExport;
    }

    protected function acnooExcelFilename(): string
    {
        return 'landlord-withdraw-method';
    }

    protected function acnooCsvFilename(): string
    {
        return 'landlord-withdraw-method';
    }
}
