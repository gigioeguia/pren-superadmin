<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminExpensesExport;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Traits\HasAcnooActions;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AcnooExpenseController extends Controller
{
    use HasAcnooActions {
        status as protected;
    }

    public function __construct()
    {
        $this->middleware('permission:expenses-read')->only('index');
        $this->middleware('permission:expenses-create')->only('store');
        $this->middleware('permission:expenses-update')->only('update');
        $this->middleware('permission:expenses-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $expense_categories = ExpenseCategory::whereStatus(1)->whereNull('landlord_id')->latest()->get();
        $expenses = Expense::with('category:id,name')->whereNull('landlord_id')->latest()->paginate(10);
        $total_expense = Expense::whereNull('landlord_id')->sum('amount');

        return view('admin.expenses.index', compact('expenses', 'expense_categories', 'total_expense'));
    }

    public function acnooFilter(Request $request)
    {
        $expenses = Expense::with('category:id,name')
            ->whereNull('landlord_id');

        if ($request->filled('search')) {
            $expenses->where(function ($query) use ($request) {
                $query->where('amount', 'like', '%'.$request->search.'%')
                    ->orWhere('expense_for', 'like', '%'.$request->search.'%')
                    ->orWhere('payment_type', 'like', '%'.$request->search.'%')
                    ->orWhere('reference_no', 'like', '%'.$request->search.'%')
                    ->orWhere('expense_date', 'like', '%'.$request->search.'%')
                    ->orWhereHas('category', function ($query) use ($request) {
                        $query->where('name', 'like', '%'.$request->search.'%');
                    });
            });
        }

        if ($request->filled('custom_days') && $request->custom_days !== 'custom_date' && $request->custom_days !== 'all') {
            switch ($request->custom_days) {
                case 'today':
                    $expenses->whereDate('expense_date', Carbon::today());
                    break;
                case 'yesterday':
                    $expenses->whereDate('expense_date', Carbon::yesterday());
                    break;
                case 'last_seven_days':
                    $expenses->whereBetween('expense_date', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()]);
                    break;
                case 'last_thirty_days':
                    $expenses->whereBetween('expense_date', [Carbon::now()->subDays(29)->startOfDay(), Carbon::now()->endOfDay()]);
                    break;
                case 'current_month':
                    $expenses->whereMonth('expense_date', Carbon::now()->month)
                        ->whereYear('expense_date', Carbon::now()->year);
                    break;
                case 'last_month':
                    $expenses->whereMonth('expense_date', Carbon::now()->subMonth()->month)
                        ->whereYear('expense_date', Carbon::now()->subMonth()->year);
                    break;
                case 'current_year':
                    $expenses->whereYear('expense_date', Carbon::now()->year);
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
                'data' => view('admin.expenses.datas', compact('expenses'))->render(),
                'total_expense' => currency_format($total_expense),
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
        ]);

        return response()->json([
            'message' => __('Expense created successfully.'),
            'redirect' => route('admin.expenses.index'),
        ]);
    }

    public function update(Request $request, string $id)
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
        ]);

        return response()->json([
            'message' => __('Expense updated successfully.'),
            'redirect' => route('admin.expenses.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted successfully',
            'redirect' => route('admin.expenses.index'),
        ]);
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return Expense::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return Expense::with('category:id,name')
            ->whereNull('landlord_id')
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.expenses.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['expenses' => $rows];
    }

    protected function acnooDeleteMessage(): string
    {
        return 'Selected expense deleted successfully';
    }

    protected function acnooRedirectRoute(): string
    {
        return 'admin.expenses.index';
    }

    protected function acnooExport(Request $request): object
    {
        return new AdminExpensesExport;
    }

    protected function acnooExcelFilename(): string
    {
        return 'admin-expenses';
    }

    protected function acnooCsvFilename(): string
    {
        return 'admin-expenses';
    }
}
