<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminLandlordWithdrawReportExport;
use App\Http\Controllers\Controller;
use App\Models\Withdraw;
use App\Traits\HasTableActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LandlordWithdrawReportController extends Controller
{
    use HasTableActions;

    public function __construct()
    {
        $this->middleware('permission:reports-read')->only('index', 'show', 'filter', 'exportExcel', 'exportCsv');
    }

    public function index()
    {
        $withdraws = Withdraw::with(['account_info:id,infos', 'user:id,name'])->latest()->paginate(10);

        return view('admin.reports.withdrawals.index', compact('withdraws'));
    }

    public function show(string $id)
    {

        $withdraw = Withdraw::with(['user:id,name,phone,email', 'user.user_details:user_id,address_info'])->findOrFail($id);
        $this->authorize('view', $withdraw);

        return view('admin.reports.withdrawals.invoice', compact('withdraw'));
    }

    protected function model(): string
    {
        return Withdraw::class;
    }

    protected function query(Request $request): Builder
    {

        $withdrawQuery = Withdraw::with(['account_info:id,infos', 'user:id,name']);

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $startDate = Carbon::parse($request->from_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->to_date)->format('Y-m-d');

            $withdrawQuery->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        if ($request->filled('search')) {
            $withdrawQuery->where(function ($q) use ($request) {
                $q->where('amount', 'like', '%'.$request->search.'%')
                    ->orWhere('charge', 'like', '%'.$request->search.'%')
                    ->orWhere('status', 'like', '%'.$request->search.'%')
                    ->orWhereHas('user', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('account_info', function ($q) use ($request) {
                        $q->where('infos->bank_name', 'like', '%'.$request->search.'%');
                    });
            });
        }

        return $withdrawQuery->latest();
    }

    protected function view(): string
    {
        return 'admin.reports.withdrawals.datas';
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
        return new AdminLandlordWithdrawReportExport;
    }

    protected function excelFilename(): string
    {
        return 'landlord-withdraw-report';
    }

    protected function csvFilename(): string
    {
        return 'landlord-withdraw-report';
    }
}
