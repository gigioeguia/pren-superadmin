<?php

namespace App\Http\Controllers\Landlord;

use Illuminate\Http\Request;
use App\Models\ExpenseCategory;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class AcnooExpenseCategoryController extends Controller
{
    public function index()
    {
        $expense_categories = ExpenseCategory::with('landlord')->where('landlord_id', auth()->id())->latest()->paginate(10);
        return view('landlord.expense-categories.index', compact('expense_categories'));
    }

    public function acnooFilter(Request $request)
    {
        $expense_categories = ExpenseCategory::with('landlord')->where('landlord_id', auth()->id())->when(request('search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.expense-categories.datas', compact('expense_categories'))->render()
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
                    return $query->where('landlord_id', auth()->id());
                }),
            ],
            'description' => 'nullable|string',
        ]);

        ExpenseCategory::create($request->except('landlord_id') + [
            'landlord_id' => auth()->id()
        ]);

        return response()->json([
            'message' => __('Expense Category saved successfully.'),
            'redirect' => route('landlord.expense-categories.index')
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
                    return $query->where('landlord_id', auth()->id());
                }),
            ],
            'description' => 'nullable|string',
        ]);

        $category->update($request->except('landlord_id') + [
            'landlord_id' => auth()->id()
        ]);

        return response()->json([
            'message' => __('Expense Category updated successfully.'),
            'redirect' => route('landlord.expense-categories.index')
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
            'message' => __('Expense Category Deleted Successfully'),
            'redirect' => route('landlord.expense-categories.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        ExpenseCategory::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => __('Selected Expense Category Deleted Successfully'),
            'redirect' => route('landlord.expense-categories.index')
        ]);
    }
}
