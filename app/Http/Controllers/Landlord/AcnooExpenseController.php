<?php

namespace App\Http\Controllers\Landlord;

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
    public function __construct(protected ExpenseService $service) {}

    public function index()
    {
        $expense_categories = ExpenseCategory::whereStatus(1)->where('landlord_id', auth()->id())->latest()->get();
        $expenses = $this->service->listForChannel(Channel::Landlord, auth()->user(), ['per_page' => 10]);
        $total_expense = $this->service->totalForChannel(Channel::Landlord, auth()->user(), []);

        return view('landlord.expenses.index', compact('expenses', 'expense_categories', 'total_expense'));
    }

    public function acnooFilter(Request $request)
    {
        $expenses = $this->service->listForChannel(Channel::Landlord, auth()->user(), array_merge($request->all(), ['per_page' => $request->per_page ?? 10]));
        $total_expense = $this->service->totalForChannel(Channel::Landlord, auth()->user(), $request->all());

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.expenses.datas', compact('expenses'))->render(),
                'total_expense' => currency_format($total_expense),
            ]);
        }

        return redirect(url()->previous());
    }

    public function store(StoreExpenseRequest $request)
    {
        $actor = $request->user();
        $this->service->store($request->validated(), Channel::Landlord, $actor);

        return response()->json([
            'message' => __('Expense created successfully.'),
            'redirect' => route('landlord.expenses.index'),
        ]);
    }

    public function update(UpdateExpenseRequest $request, string $id)
    {
        $expense = Expense::findOrFail($id);
        $actor = $request->user();
        $this->service->update($expense, $request->validated(), Channel::Landlord, $actor);

        return response()->json([
            'message' => __('Expense updated successfully.'),
            'redirect' => route('landlord.expenses.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $expense = Expense::findOrFail($id);
        $this->service->delete($expense, Channel::Landlord);

        return response()->json([
            'message' => __('Expense deleted successfully.'),
            'redirect' => route('landlord.expenses.index'),
        ]);
    }

    public function deleteAll(Request $request)
    {
        $actor = $request->user();
        $this->service->deleteMany($request->ids ?? [], Channel::Landlord, $actor);

        return response()->json([
            'message' => __('Selected Expense Deleted Successfully'),
            'redirect' => route('landlord.expenses.index'),
        ]);
    }
}
