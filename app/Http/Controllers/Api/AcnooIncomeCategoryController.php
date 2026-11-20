<?php

namespace App\Http\Controllers\Api;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Http\Requests\IncomeCategory\StoreIncomeCategoryRequest;
use App\Http\Requests\IncomeCategory\UpdateIncomeCategoryRequest;
use App\Models\IncomeCategory;
use App\Services\IncomeCategoryService;
use Illuminate\Http\Request;

class AcnooIncomeCategoryController extends Controller
{
    public function __construct(protected IncomeCategoryService $service) {}

    public function index(Request $request)
    {
        $actor = $request->user();
        $categories = $this->service->listForChannel(Channel::Api, $actor, $request->all());

        if ($request->has('no_paginate') && $request->no_paginate) {
            $responseData = [
                'data' => array_values($categories->items()),
            ];
        } else {
            $responseData = $categories;
        }

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $responseData,
        ]);
    }

    public function store(StoreIncomeCategoryRequest $request)
    {
        $actor = $request->user();
        $category = $this->service->store($request->validated(), Channel::Api, $actor);

        return response()->json([
            'message' => __('Data saved successfully.'),
            'data' => $category,
        ]);
    }

    public function update(UpdateIncomeCategoryRequest $request, string $id)
    {
        $category = IncomeCategory::findOrFail($id);
        $actor = $request->user();
        $category = $this->service->update($category, $request->validated(), Channel::Api, $actor);

        return response()->json([
            'message' => __('Data updated successfully.'),
            'data' => $category,
        ]);
    }

    public function destroy(string $id)
    {
        $category = IncomeCategory::findOrFail($id);
        $this->service->delete($category, Channel::Api);

        return response()->json([
            'message' => __('Data deleted successfully.'),
        ]);
    }
}
