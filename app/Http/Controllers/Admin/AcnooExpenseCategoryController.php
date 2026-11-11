<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcnooExpenseCategoryController extends Controller
{
    use HasAcnooActions;

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
            'redirect' => route('admin.expense-categories.index'),
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
            'redirect' => route('admin.expense-categories.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $category = ExpenseCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Expense Category Deleted Successfully',
            'redirect' => route('admin.expense-categories.index'),
        ]);
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return ExpenseCategory::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return ExpenseCategory::whereNull('landlord_id')->when(request('search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.expense-categories.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['expense_categories' => $rows];
    }

    protected function acnooRedirectRoute(): string
    {
        return 'admin.expense-categories.index';
    }

    protected function acnooStatusMessage(): string
    {
        return 'Expense Category';
    }

    protected function acnooDeleteMessage(): string
    {
        return 'Selected Expense Category Deleted Successfully';
    }
}
