<?php

namespace App\Http\Controllers\Api;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Models\Expense;
use App\Services\ExpenseService;
use Illuminate\Http\Request;

class AcnooExpenseController extends Controller
{
    public function __construct(protected ExpenseService $service) {}

    public function index(Request $request)
    {
        $expenses = $this->service->listForChannel(Channel::Api, $request->user(), $request->all());

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $expenses,
        ]);
    }

    public function store(StoreExpenseRequest $request)
    {
        $actor = $request->user();
        $data = $this->service->store($request->validated(), Channel::Api, $actor);

        return response()->json([
            'message' => __('Expense saved successfully.'),
            'data' => $data,
        ]);
    }

    public function update(UpdateExpenseRequest $request, string $id)
    {
        $expense = Expense::findOrFail($id);
        $actor = $request->user();
        $expense = $this->service->update($expense, $request->validated(), Channel::Api, $actor);

        return response()->json([
            'message' => __('Expense updated successfully.'),
            'data' => $expense,
        ]);
    }

    public function destroy(string $id)
    {
        $expense = Expense::findOrFail($id);
        $this->service->delete($expense, Channel::Api);

        return response()->json([
            'message' => __('Expense deleted successfully.'),
        ]);
    }
}
