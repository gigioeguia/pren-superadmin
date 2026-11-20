<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Income\StoreIncomeRequest;
use App\Http\Requests\Income\UpdateIncomeRequest;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Services\IncomeService;
use Illuminate\Http\Request;

class AcnooIncomeController extends Controller
{
    public function __construct(protected IncomeService $service) {}

    public function index()
    {
        $income_categories = IncomeCategory::whereStatus(1)->where('landlord_id', auth()->id())->latest()->get();
        $incomes = $this->service->listForChannel(Channel::Landlord, auth()->user(), ['per_page' => 10]);
        $total_income = $this->service->totalForChannel(Channel::Landlord, auth()->user(), []);

        return view('landlord.incomes.index', compact('incomes', 'income_categories', 'total_income'));
    }

    public function acnooFilter(Request $request)
    {
        $incomes = $this->service->listForChannel(Channel::Landlord, auth()->user(), array_merge($request->all(), ['per_page' => $request->per_page ?? 10]));
        $total_income = $this->service->totalForChannel(Channel::Landlord, auth()->user(), $request->all());

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.incomes.datas', compact('incomes'))->render(),
                'total_income' => currency_format($total_income),
            ]);
        }

        return redirect(url()->previous());
    }

    public function store(StoreIncomeRequest $request)
    {
        $actor = $request->user();
        $this->service->store($request->validated(), Channel::Landlord, $actor);

        return response()->json([
            'message' => __('Income created successfully.'),
            'redirect' => route('landlord.incomes.index'),
        ]);
    }

    public function update(UpdateIncomeRequest $request, string $id)
    {
        $income = Income::findOrFail($id);
        $actor = $request->user();
        $this->service->update($income, $request->validated(), Channel::Landlord, $actor);

        return response()->json([
            'message' => __('Income updated successfully.'),
            'redirect' => route('landlord.incomes.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $income = Income::findOrFail($id);
        $this->service->delete($income, Channel::Landlord);

        return response()->json([
            'message' => __('Income deleted successfully.'),
            'redirect' => route('landlord.incomes.index'),
        ]);
    }

    public function deleteAll(Request $request)
    {
        $actor = $request->user();
        $this->service->deleteMany($request->ids ?? [], Channel::Landlord, $actor);

        return response()->json([
            'message' => __('Selected Income Deleted Successfully'),
            'redirect' => route('landlord.incomes.index'),
        ]);
    }
}
