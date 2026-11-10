<?php

namespace App\Http\Controllers\Admin;

use App\Models\Withdraw;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdminLandlordWithdrawReportExport;
use Illuminate\Support\Carbon;

class AcnooLandlordWithdrawReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:reports-read')->only('index', 'show');
    }

    public function index()
    {
        $withdraws = Withdraw::with(['account_info:id,infos', 'user:id,name'])->latest()->paginate(10);
        return view('admin.reports.withdrawals.index', compact('withdraws'));
    }

    public function acnooFilter(Request $request)
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
                $q->where('amount', 'like', '%' . $request->search . '%')
                    ->orWhere('charge', 'like', '%' . $request->search . '%')
                    ->orWhere('status', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('account_info', function ($q) use ($request) {
                        $q->where('infos->bank_name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $perPage = $request->input('per_page', 20);
        $withdraws = $withdrawQuery->latest()->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.reports.withdrawals.datas', compact('withdraws'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function show(string $id)
    {

        $withdraw = Withdraw::with(['user:id,name,phone,email', 'user.user_details:user_id,address_info'])->findOrFail($id);

        return view('admin.reports.withdrawals.invoice', compact('withdraw'));
    }

    public function exportExcel()
    {
        return Excel::download(new AdminLandlordWithdrawReportExport, 'landlord-withdraw-report.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new AdminLandlordWithdrawReportExport, 'landlord-withdraw-report.csv');
    }
}
