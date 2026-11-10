<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\IncomeCategory;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class AcnooIncomeCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:income-categories-read')->only('index');
        $this->middleware('permission:income-categories-create')->only('store');
        $this->middleware('permission:income-categories-update')->only('update', 'status');
        $this->middleware('permission:income-categories-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $income_categories = IncomeCategory::whereNull('landlord_id')->latest()->paginate(10);
        return view('admin.income-categories.index', compact('income_categories'));
    }

    public function acnooFilter(Request $request)
    {
        $income_categories = IncomeCategory::whereNull('landlord_id')
            ->when(request('search'), function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.income-categories.datas', compact('income_categories'))->render()
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
                    return $query->whereNull('landlord_id');
                }),
            ],
            'description' => 'nullable|string'
        ]);

        IncomeCategory::create($request->all());

        return response()->json([
            'message' => __('Income Category saved successfully.'),
            'redirect' => route('admin.income-categories.index')
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
                Rule::unique('income_categories')->where(function ($query) {
                    return $query->whereNull('landlord_id');
                }),
            ],
            'description' => 'nullable|string',
        ]);

        $category->update($request->all());

        return response()->json([
            'message' => __('Income Category updated successfully.'),
            'redirect' => route('admin.income-categories.index')
        ]);
    }

    public function destroy(string $id)
    {
        $category = IncomeCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Income Category Deleted Successfully',
            'redirect' => route('admin.income-categories.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        IncomeCategory::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Selected Income Category Deleted Successfully',
            'redirect' => route('admin.income-categories.index')
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
