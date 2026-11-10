<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ExpenseCategory;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class AcnooExpenseCategoryController extends Controller
{
    public function index(Request $request)
    {
        $expense_categories = ExpenseCategory::where('landlord_id', auth()->id())
            ->when(request('search'), function ($query) {
                $query->where('name', 'like', '%' . request('search') . '%');
            })
            ->latest();
        if ($request->has('no_paginate') && $request->no_paginate == true) {
            $expense_categories = $expense_categories->select('id', 'name')->get();
            $responseData = [
                'data' => $expense_categories,
            ];
        } else {
            $expense_categories = $expense_categories->paginate(10);
            $responseData = $expense_categories;
        }

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $responseData,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique('expense_categories')->where(function ($query) {
                    return $query->where('landlord_id', auth()->id());
                })
            ],
            'description' => 'nullable|string'
        ]);

        $data = ExpenseCategory::create($request->except('landlord_id') + [
            'landlord_id' => auth()->id()
        ]);

        return response()->json([
            'message' => __('Data saved successfully.'),
            'data' => $data,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $category = ExpenseCategory::findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                Rule::unique('expense_categories')
                    ->ignore($category->id)
                    ->where(function ($query) {
                        return $query->where('landlord_id', auth()->id());
                    }),
            ],
            'description' => 'nullable|string'
        ]);

        $category->update($request->except('landlord_id') + [
            'landlord_id' => auth()->id()
        ]);

        return response()->json([
            'message' => __('Data updated successfully.'),
            'data' => $category,
        ]);
    }

    public function destroy(string $id)
    {
        $category = ExpenseCategory::findOrFail($id);
        $category->delete();
        return response()->json([
            'message' => __('Data deleted successfully.'),
        ]);
    }
}
