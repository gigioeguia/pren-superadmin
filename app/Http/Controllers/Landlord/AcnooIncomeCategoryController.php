<?php

namespace App\Http\Controllers\Landlord;

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

    public function index()
    {
        $income_categories = IncomeCategory::with('landlord')->where('landlord_id', auth()->id())->latest()->paginate(10);

        return view('landlord.income-categories.index', compact('income_categories'));
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
            'landlord_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => __('Income Category saved successfully.'),
            'redirect' => route('landlord.income-categories.index'),
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
            'landlord_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => __('Income Category updated successfully.'),
            'redirect' => route('landlord.income-categories.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $category = IncomeCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => __('Income Category Deleted Successfully'),
            'redirect' => route('landlord.income-categories.index'),
        ]);
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return IncomeCategory::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return IncomeCategory::with('landlord')->where('landlord_id', auth()->id())->when(request('search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'landlord.income-categories.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['income_categories' => $rows];
    }

    protected function acnooRedirectRoute(): string
    {
        return 'landlord.income-categories.index';
    }

    protected function acnooStatusMessage(): string
    {
        return 'Income Category';
    }

    protected function acnooDeleteMessage(): string
    {
        return __('Selected Income Category Deleted Successfully');
    }
}
