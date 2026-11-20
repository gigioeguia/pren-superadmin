<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseCategory\StoreExpenseCategoryRequest;
use App\Http\Requests\ExpenseCategory\UpdateExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use App\Services\ExpenseCategoryService;
use Illuminate\Http\Request;

class AcnooExpenseCategoryController extends Controller
{
    public function __construct(protected ExpenseCategoryService $service)
    {
        $this->middleware('permission:expense-categories-read')->only('index', 'acnooFilter');
        $this->middleware('permission:expense-categories-create')->only('store');
        $this->middleware('permission:expense-categories-update')->only('update', 'status');
        $this->middleware('permission:expense-categories-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $expense_categories = $this->service->listForChannel(Channel::Admin, auth()->user(), ['per_page' => 10]);

        return view('admin.expense-categories.index', compact('expense_categories'));
    }

    public function store(StoreExpenseCategoryRequest $request)
    {
        $actor = $request->user();
        $this->service->store($request->validated(), Channel::Admin, $actor);

        return response()->json([
            'message' => __('Expense Category saved successfully.'),
            'redirect' => route('admin.expense-categories.index'),
        ]);
    }

    public function update(UpdateExpenseCategoryRequest $request, string $id)
    {
        $category = ExpenseCategory::findOrFail($id);
        $actor = $request->user();
        $this->service->update($category, $request->validated(), Channel::Admin, $actor);

        return response()->json([
            'message' => __('Expense Category updated successfully.'),
            'redirect' => route('admin.expense-categories.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $category = ExpenseCategory::findOrFail($id);
        $this->service->delete($category, Channel::Admin);

        return response()->json([
            'message' => 'Expense Category Deleted Successfully',
            'redirect' => route('admin.expense-categories.index'),
        ]);
    }

    public function acnooFilter(Request $request)
    {
        $expense_categories = $this->service->listForChannel(Channel::Admin, auth()->user(), array_merge($request->all(), ['per_page' => 20]));

        return view('admin.expense-categories.datas', ['expense_categories' => $expense_categories]);
    }

    public function deleteAll(Request $request)
    {
        $actor = $request->user();
        $this->service->deleteMany($request->ids ?? [], Channel::Admin, $actor);

        return response()->json([
            'message' => __('Selected Expense Category Deleted Successfully'),
            'redirect' => route('admin.expense-categories.index'),
        ]);
    }
}
