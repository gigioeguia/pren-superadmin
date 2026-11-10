<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\IncomeCategory;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class AcnooIncomeCategoryController extends Controller
{
    public function index(Request $request)
    {
        $income_categories = IncomeCategory::where('business_id', auth()->user()->business_id)
            ->when(request('search'), function ($query) {
                $query->where('name', 'like', '%' . request('search') . '%');
            })
            ->latest();

        if ($request->has('no_paginate') && $request->no_paginate == true) {
            $income_categories = $income_categories->select('id', 'name')->get();
            $responseData = [
                'data' => $income_categories,
            ];
        } else {
            $income_categories = $income_categories->paginate(10);
            $responseData = $income_categories;
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
                Rule::unique('income_categories')->where(function ($query) {
                    return $query->where('landlord_id', auth()->id());
                })
            ],
            'description' => 'nullable|string'
        ]);

        $data = IncomeCategory::create($request->except('landlord_id') + [
            'landlord_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => __('Data saved successfully.'),
            'data' => $data,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $category = IncomeCategory::findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                Rule::unique('income_categories')->where(function ($query) {
                    return $query->where('landlord_id', auth()->id());
                })
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
        $category = IncomeCategory::findOrFail($id);
        $category->delete();
        return response()->json([
            'message' => __('Data deleted successfully.'),
        ]);
    }
}
