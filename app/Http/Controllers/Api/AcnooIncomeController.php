<?php

namespace App\Http\Controllers\Api;

use App\Models\Income;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcnooIncomeController extends Controller
{
    public function index()
    {
        $data = Income::with('category:id,name')->where('business_id', auth()->user()->business_id)
            ->when(request('income_category_id'), function ($query) {
                $query->where('income_category_id', request('income_category_id'));
            })
            ->when(request('search'), function ($query) {
                $query->where('incomeFor', 'like', '%' . request('search') . '%')
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
            'income_for' => 'nullable|string',
            'reference_no' => 'nullable|string',
            'income_date' => 'required|date',
            'note' => 'nullable|string',
            'income_category_id' => 'required|exists:income_categories,id',
        ]);

        $data = Income::create($request->except('landlord_id') + [
            'landlord_id' => auth()->id()
        ]);

        return response()->json([
            'message' => __('Income saved successfully.'),
            'data' => $data,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'payment_type' => 'required|string',
            'income_for' => 'nullable|string',
            'reference_no' => 'nullable|string',
            'income_date' => 'required|date',
            'note' => 'nullable|string',
            'income_category_id' => 'required|exists:income_categories,id',
        ]);

        $income = Income::findOrFail($id);

        $income->update($request->except('landlord_id') + [
            'landlord_id' => auth()->id()
        ]);

        return response()->json([
            'message' => __('Income updated successfully.'),
            'data' => $income,
        ]);
    }

    public function destroy(string $id)
    {

        $income = Income::findOrFail($id);
        $income->delete();

        return response()->json([
            'message' => __('Income deleted successfully.'),
        ]);
    }
}
