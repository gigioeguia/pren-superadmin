<?php

namespace App\Http\Controllers\Landlord;

use App\Exports\LandlordExpensesExport;
use App\Models\Expense;
use Illuminate\Http\Request;
use App\Models\ExpenseCategory;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class AcnooExpenseController extends Controller
{
    public function index()
    {
        $expense_categories = ExpenseCategory::with('landlord:id,name')->whereStatus(1)->where('landlord_id', auth()->id())->latest()->get();
        $expenses = Expense::with(['category:id,name', 'landlord:id,name'])->where('landlord_id', auth()->id())->latest()->paginate(10);
        $total_expense = Expense::where('landlord_id', auth()->id())->sum('amount');
        return view('landlord.expenses.index', compact('expenses', 'expense_categories', 'total_expense'));
    }

    public function acnooFilter(Request $request)
    {
        $expenses = Expense::with(['category:id,name', 'landlord:id,name'])
            ->where('landlord_id', auth()->id());

        if ($request->filled('search')) {
            $expenses->where(function ($query) use ($request) {
                $query->where('amount', 'like', '%' . $request->search . '%')
                    ->orWhere('expense_for', 'like', '%' . $request->search . '%')
                    ->orWhere('payment_type', 'like', '%' . $request->search . '%')
                    ->orWhere('reference_no', 'like', '%' . $request->search . '%')
                    ->orWhere('expense_date', 'like', '%' . $request->search . '%')
                    ->orWhereHas('category', function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        if ($request->filled('custom_days') && $request->custom_days !== 'custom_date' && $request->custom_days !== 'all') {
            switch ($request->custom_days) {
                case 'today':
                    $expenses->whereDate('expense_date', \Carbon\Carbon::today());
                    break;
                case 'yesterday':
                    $expenses->whereDate('expense_date', \Carbon\Carbon::yesterday());
                    break;
                case 'last_seven_days':
                    $expenses->whereBetween('expense_date', [\Carbon\Carbon::now()->subDays(6)->startOfDay(), \Carbon\Carbon::now()->endOfDay()]);
                    break;
                case 'last_thirty_days':
                    $expenses->whereBetween('expense_date', [\Carbon\Carbon::now()->subDays(29)->startOfDay(), \Carbon\Carbon::now()->endOfDay()]);
                    break;
                case 'current_month':
                    $expenses->whereMonth('expense_date', \Carbon\Carbon::now()->month)
                        ->whereYear('expense_date', \Carbon\Carbon::now()->year);
                    break;
                case 'last_month':
                    $expenses->whereMonth('expense_date', \Carbon\Carbon::now()->subMonth()->month)
                        ->whereYear('expense_date', \Carbon\Carbon::now()->subMonth()->year);
                    break;
                case 'current_year':
                    $expenses->whereYear('expense_date', \Carbon\Carbon::now()->year);
                    break;
            }
        }

        if ($request->custom_days === 'custom_date' && $request->filled(['from_date', 'to_date'])) {
            $expenses->whereBetween('expense_date', [$request->from_date, $request->to_date]);
        }

        $expenses->latest();

        $total_expense = $expenses->sum('amount');

        $expenses = $expenses->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.expenses.datas', compact('expenses'))->render(),
                'total_expense' => currency_format($total_expense)
            ]);
        }

        return redirect(url()->previous());
    }


    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'payment_type' => 'required|string',
            'expense_for' => 'required|string',
            'reference_no' => 'nullable|string',
            'expense_date' => 'nullable|string',
            'note' => 'nullable|string',
            'expense_category_id' => 'required|exists:expense_categories,id',
        ]);

        Expense::create([
            'amount' => $request->amount,
            'expense_for' => $request->expense_for,
            'reference_no' => $request->reference_no,
            'expense_date' => $request->expense_date,
            'payment_type' => $request->payment_type,
            'note' => $request->note,
            'expense_category_id' => $request->expense_category_id,
            'landlord_id' => auth()->id()
        ]);

        return response()->json([
            'message' => __('Expense created successfully.'),
            'redirect' => route('landlord.expenses.index')
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'payment_type' => 'required|string',
            'expense_for' => 'required|string',
            'reference_no' => 'nullable|string',
            'expense_date' => 'nullable|string',
            'note' => 'nullable|string',
            'expense_category_id' => 'required|exists:expense_categories,id',
        ]);

        $expense = Expense::findOrFail($id);

        $expense->update([
            'amount' => $request->amount,
            'expense_for' => $request->expense_for,
            'reference_no' => $request->reference_no,
            'expense_date' => $request->expense_date,
            'payment_type' => $request->payment_type,
            'note' => $request->note,
            'expense_category_id' => $request->expense_category_id,
            'landlord_id' => auth()->id()
        ]);

        return response()->json([
            'message' => __('Expense updated successfully.'),
            'redirect' => route('landlord.expenses.index')
        ]);
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return response()->json([
            'message' => 'Expense Deleted Successfully',
            'redirect' => route('landlord.expenses.index'),
        ]);
    }

    public function deleteAll(Request $request)
    {
        Expense::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Selected Expense Deleted Successfully',
            'redirect' => route('landlord.expenses.index'),
        ]);
    }


    public function exportExcel()
    {
        return Excel::download(new LandlordExpensesExport, 'landlord-expenses.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new LandlordExpensesExport, 'landlord-expenses.csv');
    }
}
