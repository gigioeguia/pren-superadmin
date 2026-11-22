<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseCategory\StoreExpenseCategoryRequest;
use App\Http\Requests\ExpenseCategory\UpdateExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use App\Services\ExpenseCategoryService;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function __construct(protected ExpenseCategoryService $service) {}

    public function index()
    {
        $expense_categories = $this->service->listForChannel(Channel::Landlord, auth()->user(), ['per_page' => 10]);

        return view('landlord.expense-categories.index', compact('expense_categories'));
    }

    public function store(StoreExpenseCategoryRequest $request)
    {
        $actor = $request->user();
        $this->service->store($request->validated(), Channel::Landlord, $actor);

        return response()->json([
            'message' => __('Expense Category saved successfully.'),
            'redirect' => route('landlord.expense-categories.index'),
        ]);
    }

    public function update(UpdateExpenseCategoryRequest $request, string $id)
    {
        $category = ExpenseCategory::findOrFail($id);
        $actor = $request->user();
        $this->service->update($category, $request->validated(), Channel::Landlord, $actor);

        return response()->json([
            'message' => __('Expense Category updated successfully.'),
            'redirect' => route('landlord.expense-categories.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $category = ExpenseCategory::findOrFail($id);
        $this->service->delete($category, Channel::Landlord);

        return response()->json([
            'message' => __('Expense Category Deleted Successfully'),
            'redirect' => route('landlord.expense-categories.index'),
        ]);
    }

    public function filter(Request $request)
    {
        $expense_categories = $this->service->listForChannel(Channel::Landlord, auth()->user(), array_merge($request->all(), ['per_page' => 20]));

        return view('landlord.expense-categories.datas', ['expense_categories' => $expense_categories]);
    }
}
