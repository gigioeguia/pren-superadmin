<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminExportIncome;
use App\Models\Income;
use Illuminate\Http\Request;
use App\Models\IncomeCategory;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class AcnooIncomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:incomes-read')->only('index');
        $this->middleware('permission:incomes-create')->only('store');
        $this->middleware('permission:incomes-update')->only('update');
        $this->middleware('permission:incomes-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $income_categories = IncomeCategory::whereStatus(1)->whereNull('landlord_id')->latest()->get();
        $incomes = Income::with('category:id,name')->whereNull('landlord_id')->latest()->paginate(10);
        $total_income = Income::whereNull('landlord_id')->sum('amount');

        return view('admin.incomes.index', compact('incomes', 'income_categories', 'total_income'));
    }

    public function acnooFilter(Request $request)
    {
        $incomes = Income::with('category:id,name')
            ->whereNull('landlord_id');

        if ($request->filled('search')) {
            $incomes->where(function ($query) use ($request) {
                $query->where('amount', 'like', '%' . $request->search . '%')
                    ->orWhere('income_for', 'like', '%' . $request->search . '%')
                    ->orWhere('payment_type', 'like', '%' . $request->search . '%')
                    ->orWhere('reference_no', 'like', '%' . $request->search . '%')
                    ->orWhere('income_date', 'like', '%' . $request->search . '%')
                    ->orWhereHas('category', function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        if ($request->filled('custom_days') && $request->custom_days !== 'custom_date' && $request->custom_days !== 'all') {
            switch ($request->custom_days) {
                case 'today':
                    $incomes->whereDate('income_date', \Carbon\Carbon::today());
                    break;
                case 'yesterday':
                    $incomes->whereDate('income_date', \Carbon\Carbon::yesterday());
                    break;
                case 'last_seven_days':
                    $incomes->whereBetween('income_date', [\Carbon\Carbon::now()->subDays(6)->startOfDay(), \Carbon\Carbon::now()->endOfDay()]);
                    break;
                case 'last_thirty_days':
                    $incomes->whereBetween('income_date', [\Carbon\Carbon::now()->subDays(29)->startOfDay(), \Carbon\Carbon::now()->endOfDay()]);
                    break;
                case 'current_month':
                    $incomes->whereMonth('income_date', \Carbon\Carbon::now()->month)
                        ->whereYear('income_date', \Carbon\Carbon::now()->year);
                    break;
                case 'last_month':
                    $incomes->whereMonth('income_date', \Carbon\Carbon::now()->subMonth()->month)
                        ->whereYear('income_date', \Carbon\Carbon::now()->subMonth()->year);
                    break;
                case 'current_year':
                    $incomes->whereYear('income_date', \Carbon\Carbon::now()->year);
                    break;
            }
        }

        if ($request->custom_days === 'custom_date' && $request->filled(['from_date', 'to_date'])) {
            $incomes->whereBetween('income_date', [$request->from_date, $request->to_date]);
        }

        $incomes->latest();

        $total_income = $incomes->sum('amount');

        $incomes = $incomes->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.incomes.datas', compact('incomes'))->render(),
                'total_income' => currency_format($total_income)
            ]);
        }

        return redirect(url()->previous());
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'payment_type' => 'required|string',
            'income_for' => 'required|string',
            'reference_no' => 'nullable|string',
            'income_date' => 'nullable|string',
            'note' => 'nullable|string',
            'income_category_id' => 'required|exists:income_categories,id',
        ]);

        Income::create([
            'amount' => $request->amount,
            'income_for' => $request->income_for,
            'reference_no' => $request->reference_no,
            'income_date' => $request->income_date,
            'payment_type' => $request->payment_type,
            'note' => $request->note,
            'income_category_id' => $request->income_category_id,
        ]);

        return response()->json([
            'message' => __('Income created successfully.'),
            'redirect' => route('admin.incomes.index')
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'payment_type' => 'required|string',
            'income_for' => 'required|string',
            'reference_no' => 'nullable|string',
            'income_date' => 'nullable|string',
            'note' => 'nullable|string',
            'income_category_id' => 'required|exists:income_categories,id',
        ]);

        $income = Income::findOrFail($id);

        $income->update([
            'amount' => $request->amount,
            'income_for' => $request->income_for,
            'reference_no' => $request->reference_no,
            'income_date' => $request->income_date,
            'payment_type' => $request->payment_type,
            'note' => $request->note,
            'income_category_id' => $request->income_category_id,
        ]);


        return response()->json([
            'message' => __('Income updated successfully.'),
            'redirect' => route('admin.incomes.index')
        ]);
    }

    public function destroy(string $id)
    {
        $income = Income::findOrFail($id);
        $income->delete();

        return response()->json([
            'message' => 'Income deleted successfully',
            'redirect' => route('admin.incomes.index'),
        ]);
    }

    public function deleteAll(Request $request)
    {
        Income::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Selected income deleted successfully',
            'redirect' => route('admin.incomes.index'),
        ]);
    }

    public function exportExcel()
    {
        return Excel::download(new AdminExportIncome, 'admin-incomes.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new AdminExportIncome, 'admin-incomes.csv');
    }
}
