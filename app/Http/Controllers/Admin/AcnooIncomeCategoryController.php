<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IncomeCategory;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcnooIncomeCategoryController extends Controller
{
    use HasAcnooActions;

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
            'description' => 'nullable|string',
        ]);

        IncomeCategory::create($request->all());

        return response()->json([
            'message' => __('Income Category saved successfully.'),
            'redirect' => route('admin.income-categories.index'),
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
            'redirect' => route('admin.income-categories.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $category = IncomeCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Income Category Deleted Successfully',
            'redirect' => route('admin.income-categories.index'),
        ]);
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return IncomeCategory::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return IncomeCategory::whereNull('landlord_id')
            ->when(request('search'), function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('description', 'like', '%'.$request->search.'%');
                });
            })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.income-categories.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['income_categories' => $rows];
    }

    protected function acnooRedirectRoute(): string
    {
        return 'admin.income-categories.index';
    }

    protected function acnooStatusMessage(): string
    {
        return 'Income Category';
    }

    protected function acnooDeleteMessage(): string
    {
        return 'Selected Income Category Deleted Successfully';
    }
}
