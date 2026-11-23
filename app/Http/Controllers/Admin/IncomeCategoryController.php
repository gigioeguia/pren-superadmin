<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Http\Requests\IncomeCategory\StoreIncomeCategoryRequest;
use App\Http\Requests\IncomeCategory\UpdateIncomeCategoryRequest;
use App\Models\IncomeCategory;
use App\Services\IncomeCategoryService;
use Illuminate\Http\Request;

class IncomeCategoryController extends Controller
{
    public function __construct(protected IncomeCategoryService $service)
    {
        $this->middleware('permission:income-categories-read')->only('index', 'filter');
        $this->middleware('permission:income-categories-create')->only('store');
        $this->middleware('permission:income-categories-update')->only('update', 'status');
        $this->middleware('permission:income-categories-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $income_categories = $this->service->listForChannel(Channel::Admin, auth()->user(), ['per_page' => 10]);

        return view('admin.income-categories.index', compact('income_categories'));
    }

    public function store(StoreIncomeCategoryRequest $request)
    {
        $actor = $request->user();
        $this->service->store($request->validated(), Channel::Admin, $actor);

        return response()->json([
            'message' => __('Income Category saved successfully.'),
            'redirect' => route('admin.income-categories.index'),
        ]);
    }

    public function update(UpdateIncomeCategoryRequest $request, string $id)
    {
        $category = IncomeCategory::findOrFail($id);
        $actor = $request->user();
        $this->service->update($category, $request->validated(), Channel::Admin, $actor);

        return response()->json([
            'message' => __('Income Category updated successfully.'),
            'redirect' => route('admin.income-categories.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $category = IncomeCategory::findOrFail($id);
        $this->service->delete($category, Channel::Admin);

        return response()->json([
            'message' => 'Income Category Deleted Successfully',
            'redirect' => route('admin.income-categories.index'),
        ]);
    }

    public function filter(Request $request)
    {
        $income_categories = $this->service->listForChannel(Channel::Admin, auth()->user(), array_merge($request->all(), ['per_page' => 20]));

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.income-categories.datas', ['income_categories' => $income_categories])->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    public function deleteAll(Request $request)
    {
        $actor = $request->user();
        $this->service->deleteMany($request->ids ?? [], Channel::Admin, $actor);

        return response()->json([
            'message' => __('Selected Income Category Deleted Successfully'),
            'redirect' => route('admin.income-categories.index'),
        ]);
    }

    // Restaurado (spec 5.17): statusMessage verbatim pre-4.2, sin __().
    public function status(Request $request, string $id)
    {
        $category = IncomeCategory::findOrFail($id);
        $this->service->updateStatus($category, (int) $request->status);

        return response()->json(['message' => 'Income Category']);
    }
}
