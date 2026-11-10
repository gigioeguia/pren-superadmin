<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ExpenseCategory;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class AcnooExpenseCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:expense-categories-read')->only('index');
        $this->middleware('permission:expense-categories-create')->only('store');
        $this->middleware('permission:expense-categories-update')->only('update');
        $this->middleware('permission:expense-categories-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $expense_categories = ExpenseCategory::whereNull('landlord_id')->latest()->paginate(10);
        return view('admin.expense-categories.index', compact('expense_categories'));
    }

    public function acnooFilter(Request $request)
    {
        $expense_categories = ExpenseCategory::whereNull('landlord_id')->when(request('search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.expense-categories.datas', compact('expense_categories'))->render()
            ]);
        }
        return redirect(url()->previous());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories')->where(function ($query) {
                    return $query->whereNull('landlord_id');
                }),
            ],
            'description' => 'nullable|string',
        ]);

        ExpenseCategory::create($request->all());

        return response()->json([
            'message' => __('Expense Category saved successfully.'),
            'redirect' => route('admin.expense-categories.index')
        ]);
    }

    public function update(Request $request, string $id)
    {
        $category = ExpenseCategory::findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories')->ignore($category->id)->where(function ($query) {
                    return $query->whereNull('landlord_id');
                }),
            ],
            'description' => 'nullable|string',
        ]);

        $category->update($request->all());

        return response()->json([
            'message' => __('Expense Category updated successfully.'),
            'redirect' => route('admin.expense-categories.index')
        ]);
    }

    public function status(Request $request, string $id)
    {
        $category = ExpenseCategory::findOrFail($id);
        $category->update(['status' => $request->status]);
        return response()->json([
            'message' => 'Expense Category',
        ]);
    }

    public function destroy(string $id)
    {
        $category = ExpenseCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Expense Category Deleted Successfully',
            'redirect' => route('admin.expense-categories.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        ExpenseCategory::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Selected Expense Category Deleted Successfully',
            'redirect' => route('admin.expense-categories.index')
        ]);
    }
}
