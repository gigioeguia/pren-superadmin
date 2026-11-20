<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ExpenseService;
use Illuminate\Http\Request;

class AcnooExpenseController extends Controller
{
    public function __construct(protected ExpenseService $service)
    {
        $this->middleware('permission:expenses-read')->only('index', 'acnooFilter', 'exportExcel', 'exportCsv');
        $this->middleware('permission:expenses-create')->only('store');
        $this->middleware('permission:expenses-update')->only('update', 'status');
        $this->middleware('permission:expenses-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $expense_categories = ExpenseCategory::whereStatus(1)->whereNull('landlord_id')->latest()->get();
        $expenses = $this->service->listForChannel(Channel::Admin, auth()->user(), ['per_page' => 10]);
        $total_expense = $this->service->totalForChannel(Channel::Admin, auth()->user(), []);

        return view('admin.expenses.index', compact('expenses', 'expense_categories', 'total_expense'));
    }

    public function acnooFilter(Request $request)
    {
        $expenses = $this->service->listForChannel(Channel::Admin, auth()->user(), array_merge($request->all(), ['per_page' => $request->per_page ?? 10]));
        $total_expense = $this->service->totalForChannel(Channel::Admin, auth()->user(), $request->all());

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.expenses.datas', compact('expenses'))->render(),
                'total_expense' => currency_format($total_expense),
            ]);
        }

        return redirect(url()->previous());
    }

    public function store(StoreExpenseRequest $request)
    {
        $actor = $request->user();
        $this->service->store($request->validated(), Channel::Admin, $actor);

        return response()->json([
            'message' => __('Expense created successfully.'),
            'redirect' => route('admin.expenses.index'),
        ]);
    }

    public function update(UpdateExpenseRequest $request, string $id)
    {
        $expense = Expense::findOrFail($id);
        $actor = $request->user();
        $this->service->update($expense, $request->validated(), Channel::Admin, $actor);

        return response()->json([
            'message' => __('Expense updated successfully.'),
            'redirect' => route('admin.expenses.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $expense = Expense::findOrFail($id);
        $this->service->delete($expense, Channel::Admin);

        return response()->json([
            'message' => __('Expense deleted successfully.'),
            'redirect' => route('admin.expenses.index'),
        ]);
    }

    public function deleteAll(Request $request)
    {
        $actor = $request->user();
        $this->service->deleteMany($request->ids ?? [], Channel::Admin, $actor);

        return response()->json([
            'message' => __('Selected Expense Deleted Successfully'),
            'redirect' => route('admin.expenses.index'),
        ]);
    }
}
