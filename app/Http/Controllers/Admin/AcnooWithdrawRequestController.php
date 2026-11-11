<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminWithdrawRequestExport;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdraw;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcnooWithdrawRequestController extends Controller
{
    use HasAcnooActions;

    public function __construct()
    {
        $this->middleware('permission:withdraw-request-read')->only('index');
        $this->middleware('permission:withdraw-request-update')->only('approve', 'reject');
    }

    public function index(Request $request)
    {
        $withdraws = Withdraw::with(['account_info:id,method_id', 'account_info.withdraw_method:id,name,charge_type', 'user:id,name'])->latest()->paginate(20);

        return view('admin.withdrawals.index', compact('withdraws'));
    }

    public function show(string $id)
    {
        $withdraw = Withdraw::with(['user:id,name', 'account_info.withdraw_method:id,name'])
            ->where('id', $id)
            ->latest()
            ->first();

        return view('admin.withdrawals.view', compact('withdraw'));
    }

    public function reject(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $withdraw_reject = Withdraw::with('user:id,name')->findOrFail($id);

        if ($withdraw_reject) {

            $totalRefund = $withdraw_reject->amount + $withdraw_reject->charge;

            $withdraw_reject->update([
                'status' => 'rejected',
                'reason' => $request->reason,
            ]);

            $withdraw_reject->user->increment('balance', $totalRefund);

            return response()->json([
                'message' => 'Withdrawal rejected successfully',
                'redirect' => route('admin.withdraw-request.index'),
            ]);
        } else {
            return response()->json(['message' => 'Withdrawal request not found'], 404);
        }
    }

    public function approve(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $withdraw = Withdraw::with('user:id,name')->findOrFail($id);
        $landlord = User::where('id', $withdraw->user->id)->first();

        if ($withdraw) {

            DB::beginTransaction();
            try {
                Transaction::create([
                    'paid_by' => auth()->user()->id,
                    'received_by' => $withdraw->user_id,
                    'withdraw_id' => $withdraw->id,
                    'date' => today(),
                    'type' => 'debit',
                    'payment_type' => 'withdraw',
                    'amount' => $withdraw->amount,
                    'charge' => $withdraw->charge,
                    'payment_method' => $withdraw->account_info->withdraw_method->name ?? '',
                    'notes' => $request->notes,
                ]);

                $withdraw->update([
                    'status' => 'approved',
                    'reason' => $request->reason,
                ]);

                DB::commit();

                sendNotification($withdraw->id, notify_users([$withdraw->user->id]), admin_url: route('admin.withdraw-request.index', ['id' => $withdraw->id]), landlord_url: route('landlord.withdraw.index', ['id' => $withdraw->id]), admin_msg: __('A withdrawal request has been approved.'), landlord_msg: __('Your withdrawal request has been approved.'));

                return response()->json([
                    'message' => 'Withdrawal Approve successfully',
                    'redirect' => route('admin.withdraw-request.index'),
                ]);
            } catch (\Exception $e) {
                DB::rollback();

                return response()->json(['error' => 'Transaction failed: '.$e->getMessage()], 500);
            }
        } else {
            return response()->json(['message' => 'Withdrawal request not found'], 404);
        }
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return Withdraw::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return Withdraw::with(['account_info:id,method_id', 'account_info.withdraw_method:id,name,charge_type', 'user:id,name'])
            ->when(request('search'), function ($q) {
                $q->where('amount', 'like', '%'.request('search').'%')
                    ->orWhere('charge', 'like', '%'.request('search').'%')
                    ->orWhere('status', 'like', '%'.request('search').'%');

                $q->orWhereHas('user', function ($q) {
                    $q->where('name', 'like', '%'.request('search').'%');
                });
            })
            ->when($request->id, function ($query) use ($request) {
                $query->where('id', $request->id);
            })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.withdrawals.datas';
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
        return new AdminWithdrawRequestExport;
    }

    protected function acnooExcelFilename(): string
    {
        return 'withdraw-request';
    }

    protected function acnooCsvFilename(): string
    {
        return 'withdraw-request';
    }
}
