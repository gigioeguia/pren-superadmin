<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\IncomeCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcnooIncomeCategoryController extends Controller
{
    public function index()
    {
        $income_categories = IncomeCategory::with('landlord')->where('landlord_id', auth()->id())->latest()->paginate(10);
        return view('landlord.income-categories.index', compact('income_categories'));
    }

    public function acnooFilter(Request $request)
    {
        $income_categories = IncomeCategory::with('landlord')->where('landlord_id', auth()->id())->when(request('search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.income-categories.datas', compact('income_categories'))->render()
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
                Rule::unique('income_categories')->where(function ($query) {
                    return $query->where('landlord_id', auth()->id());
                }),
            ],
            'description' => 'nullable|string',
        ]);

        IncomeCategory::create($request->except('landlord_id') + [
            'landlord_id' => auth()->id()
        ]);

        return response()->json([
            'message' => __('Income Category saved successfully.'),
            'redirect' => route('landlord.income-categories.index')
        ]);
    }

    public function update(Request $request, string $id)
    {
        $category = IncomeCategory::findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('income_categories')->ignore($category->id)->where(function ($query) {
                    return $query->where('landlord_id', auth()->id());
                }),
            ],
            'description' => 'nullable|string',
        ]);

        $category->update($request->except('landlord_id') + [
            'landlord_id' => auth()->id()
        ]);

        return response()->json([
            'message' => __('Income Category updated successfully.'),
            'redirect' => route('landlord.income-categories.index')
        ]);
    }

    public function destroy(string $id)
    {
        $category = IncomeCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => __('Income Category Deleted Successfully'),
            'redirect' => route('landlord.income-categories.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        IncomeCategory::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => __('Selected Income Category Deleted Successfully'),
            'redirect' => route('landlord.income-categories.index')
        ]);
    }

    public function status(Request $request, string $id)
    {
        $category = IncomeCategory::findOrFail($id);
        $category->update(['status' => $request->status]);
        return response()->json([
            'message' => 'Income Category',
        ]);
    }
}
