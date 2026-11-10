<?php

namespace App\Http\Controllers\Api;


use App\Models\Expense;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcnooExpenseController extends Controller
{
    public function index()
    {
        $data = Expense::with('category:id,name')
            ->where('business_id', auth()->user()->business_id)
            ->when(request('expense_category_id'), function ($query) {
                $query->where('expense_category_id', request('expense_category_id'));
            })
            ->when(request('search'), function ($query) {
                $query->where('expanseFor', 'like', '%' . request('search') . '%')
                    ->orWhere('amount', 'like', '%' . request('search') . '%')
                    ->orWhereHas('category', function ($query) {
                        $query->where('name', 'like', '%' . request('search') . '%');
                    });
            })
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'payment_type' => 'required|string',
            'expense_for' => 'nullable|string',
            'reference_no' => 'nullable|string',
            'expense_date' => 'required|date',
            'note' => 'nullable|string',
            'expense_category_id' => 'required|exists:expense_categories,id',
        ]);

        $data = Expense::create($request->except('landlord_id') + [
            'landlord_id' => auth()->id()
        ]);

        return response()->json([
            'message' => __('Expense saved successfully.'),
            'data' => $data,
        ]);
    }

    public function update(Request $request, string $id)
    {

        $request->validate([
            'amount' => 'required|numeric',
            'payment_type' => 'required|string',
            'expense_for' => 'nullable|string',
            'reference_no' => 'nullable|string',
            'expense_date' => 'required|date',
            'note' => 'nullable|string',
            'expense_category_id' => 'required|exists:expense_categories,id',
        ]);

        $expense = Expense::findOrFail($id);

        $expense->update($request->except('landlord_id') + [
            'landlord_id' => auth()->id()
        ]);

        return response()->json([
            'message' => __('Expense updated successfully.'),
            'data' => $expense,
        ]);
    }

    public function destroy(string $id)
    {

        $expense = Expense::findOrFail($id);
        $expense->delete();

        return response()->json([
            'message' => __('Expense deleted successfully.'),
        ]);
    }
}
