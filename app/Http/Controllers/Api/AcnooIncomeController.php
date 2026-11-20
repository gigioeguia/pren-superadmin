<?php

namespace App\Http\Controllers\Api;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Income\StoreIncomeRequest;
use App\Http\Requests\Income\UpdateIncomeRequest;
use App\Models\Income;
use App\Services\IncomeService;
use Illuminate\Http\Request;

class AcnooIncomeController extends Controller
{
    public function __construct(protected IncomeService $service) {}

    public function index(Request $request)
    {
        $actor = $request->user();
        $data = $this->service->listForChannel(Channel::Api, $actor, $request->all());

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $data,
        ]);
    }

    public function store(StoreIncomeRequest $request)
    {
        $actor = $request->user();
        $data = $this->service->store($request->validated(), Channel::Api, $actor);

        return response()->json([
            'message' => __('Income saved successfully.'),
            'data' => $data,
        ]);
    }

    public function update(UpdateIncomeRequest $request, string $id)
    {
        $income = Income::findOrFail($id);
        $actor = $request->user();
        $income = $this->service->update($income, $request->validated(), Channel::Api, $actor);

        return response()->json([
            'message' => __('Income updated successfully.'),
            'data' => $income,
        ]);
    }

    public function destroy(string $id)
    {
        $income = Income::findOrFail($id);
        $this->service->delete($income, Channel::Api);

        return response()->json([
            'message' => __('Income deleted successfully.'),
        ]);
    }
}
