<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminWithdrawRequestExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Withdraw;
use App\Services\WithdrawService;
use App\Support\ErrorMessage;
use App\Traits\HasTableActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class WithdrawRequestController extends Controller
{
    use HasTableActions;

    public function __construct(protected WithdrawService $service)
    {
        $this->middleware('permission:withdraw-request-read')->only('index', 'show', 'filter', 'exportExcel', 'exportCsv');
        $this->middleware('permission:withdraw-request-update')->only('store', 'approve', 'reject');
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

        if ($withdraw) {
            $this->authorize('view', $withdraw);
        }

        return view('admin.withdrawals.view', compact('withdraw'));
    }

    public function reject(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $withdraw_reject = Withdraw::with('user:id,name')->findOrFail($id);
        $this->authorize('reject', $withdraw_reject);

        // D-FIX-reject (spec 5.2): status + refund ahora atómicos en el service
        $this->service->reject($withdraw_reject, $request->reason);

        return response()->json([
            'message' => 'Withdrawal rejected successfully',
            'redirect' => route('admin.withdraw-request.index'),
        ]);
    }

    public function approve(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $withdraw = Withdraw::with('user:id,name')->findOrFail($id);
        $this->authorize('approve', $withdraw);

        // Tipado explícito: auth()->user() es union de guards con modelos
        // fantasma en config (spec 5.11 — writes sobre User tipado).
        $admin = User::findOrFail(auth()->id());

        try {
            $this->service->approve($admin, $withdraw, $request->reason, $request->notes);

            // Notificación tras el commit, como el original
            sendNotification($withdraw->id, notify_users([$withdraw->user->id]), admin_url: route('admin.withdraw-request.index', ['id' => $withdraw->id]), landlord_url: route('landlord.withdraw.index', ['id' => $withdraw->id]), admin_msg: __('A withdrawal request has been approved.'), landlord_msg: __('Your withdrawal request has been approved.'));

            return response()->json([
                'message' => 'Withdrawal Approve successfully',
                'redirect' => route('admin.withdraw-request.index'),
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['error' => __(ErrorMessage::TRANSACTION)], 500);
        }
    }

    protected function model(): string
    {
        return Withdraw::class;
    }

    protected function query(Request $request): Builder
    {
        return Withdraw::with(['account_info:id,method_id', 'account_info.withdraw_method:id,name,charge_type', 'user:id,name'])
            ->when($request->input('search'), function ($q) use ($request) {
                $q->where('amount', 'like', '%'.$request->input('search').'%')
                    ->orWhere('charge', 'like', '%'.$request->input('search').'%')
                    ->orWhere('status', 'like', '%'.$request->input('search').'%');

                $q->orWhereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->input('search').'%');
                });
            })
            ->when($request->id, function ($query) use ($request) {
                $query->where('id', $request->id);
            })
            ->latest();
    }

    protected function view(): string
    {
        return 'admin.withdrawals.datas';
    }

    protected function viewData(LengthAwarePaginator $rows): array
    {
        return ['withdraws' => $rows];
    }

    protected function perPage(): int
    {
        return 20;
    }

    protected function export(Request $request): object
    {
        return new AdminWithdrawRequestExport;
    }

    protected function excelFilename(): string
    {
        return 'withdraw-request';
    }

    protected function csvFilename(): string
    {
        return 'withdraw-request';
    }
}
