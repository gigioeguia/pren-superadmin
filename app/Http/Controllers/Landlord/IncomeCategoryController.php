<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Http\Requests\IncomeCategory\StoreIncomeCategoryRequest;
use App\Http\Requests\IncomeCategory\UpdateIncomeCategoryRequest;
use App\Models\IncomeCategory;
use App\Services\IncomeCategoryService;
use Illuminate\Http\Request;

class IncomeCategoryController extends Controller
{
    public function __construct(protected IncomeCategoryService $service) {}

    public function index()
    {
        $income_categories = $this->service->listForChannel(Channel::Landlord, auth()->user(), ['per_page' => 10]);

        return view('landlord.income-categories.index', compact('income_categories'));
    }

    public function store(StoreIncomeCategoryRequest $request)
    {
        $actor = $request->user();
        $this->service->store($request->validated(), Channel::Landlord, $actor);

        return response()->json([
            'message' => __('Income Category saved successfully.'),
            'redirect' => route('landlord.income-categories.index'),
        ]);
    }

    public function update(UpdateIncomeCategoryRequest $request, string $id)
    {
        $category = IncomeCategory::findOrFail($id);
        $actor = $request->user();
        $this->service->update($category, $request->validated(), Channel::Landlord, $actor);

        return response()->json([
            'message' => __('Income Category updated successfully.'),
            'redirect' => route('landlord.income-categories.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $category = IncomeCategory::findOrFail($id);
        $this->service->delete($category, Channel::Landlord);

        return response()->json([
            'message' => __('Income Category Deleted Successfully'),
            'redirect' => route('landlord.income-categories.index'),
        ]);
    }

    public function filter(Request $request)
    {
        $income_categories = $this->service->listForChannel(Channel::Landlord, auth()->user(), array_merge($request->all(), ['per_page' => 20]));

        return view('landlord.income-categories.datas', ['income_categories' => $income_categories]);
    }

    // Restaurado (spec 5.17): statusMessage verbatim pre-4.2, sin __().
    public function status(Request $request, string $id)
    {
        $category = IncomeCategory::findOrFail($id);
        $this->service->updateStatus($category, (int) $request->status);

        return response()->json(['message' => 'Income Category']);
    }

    // Restaurado (spec 5.17): deleteMany scoped del service — desviación
    // security-positive vs el unscoped verbatim del trait (Q7, §5 del spec).
    public function deleteAll(Request $request)
    {
        $actor = $request->user();
        $this->service->deleteMany($request->ids ?? [], Channel::Landlord, $actor);

        return response()->json([
            'message' => __('Selected Income Category Deleted Successfully'),
            'redirect' => route('landlord.income-categories.index'),
        ]);
    }
}
