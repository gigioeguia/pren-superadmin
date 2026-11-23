<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Exports\AdminExportIncome;
use App\Http\Controllers\Controller;
use App\Http\Requests\Income\StoreIncomeRequest;
use App\Http\Requests\Income\UpdateIncomeRequest;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Services\IncomeService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class IncomeController extends Controller
{
    public function __construct(protected IncomeService $service)
    {
        $this->middleware('permission:incomes-read')->only('index', 'filter', 'exportExcel', 'exportCsv');
        $this->middleware('permission:incomes-create')->only('store');
        $this->middleware('permission:incomes-update')->only('update', 'status');
        $this->middleware('permission:incomes-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $income_categories = IncomeCategory::whereStatus(1)->whereNull('landlord_id')->latest()->get();
        $incomes = $this->service->listForChannel(Channel::Admin, auth()->user(), ['per_page' => 10]);
        $total_income = $this->service->totalForChannel(Channel::Admin, auth()->user(), []);

        return view('admin.incomes.index', compact('incomes', 'income_categories', 'total_income'));
    }

    public function filter(Request $request)
    {
        $filters = $request->all();
        $incomes = $this->service->listForChannel(Channel::Admin, auth()->user(), array_merge($filters, ['per_page' => $request->per_page ?? 10]));
        $total_income = $this->service->totalForChannel(Channel::Admin, auth()->user(), $filters);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.incomes.datas', compact('incomes'))->render(),
                'total_income' => currency_format($total_income),
            ]);
        }

        return redirect(url()->previous());
    }

    public function store(StoreIncomeRequest $request)
    {
        $actor = $request->user();
        $this->service->store($request->validated(), Channel::Admin, $actor);

        return response()->json([
            'message' => __('Income created successfully.'),
            'redirect' => route('admin.incomes.index'),
        ]);
    }

    public function update(UpdateIncomeRequest $request, string $id)
    {
        $income = Income::findOrFail($id);
        $actor = $request->user();
        $this->service->update($income, $request->validated(), Channel::Admin, $actor);

        return response()->json([
            'message' => __('Income updated successfully.'),
            'redirect' => route('admin.incomes.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $income = Income::findOrFail($id);
        $this->service->delete($income, Channel::Admin);

        return response()->json([
            'message' => __('Income deleted successfully.'),
            'redirect' => route('admin.incomes.index'),
        ]);
    }

    public function deleteAll(Request $request)
    {
        $actor = $request->user();
        $this->service->deleteMany($request->ids ?? [], Channel::Admin, $actor);

        return response()->json([
            'message' => __('Selected Income Deleted Successfully'),
            'redirect' => route('admin.incomes.index'),
        ]);
    }

    // Restaurado (spec 5.17): filename verbatim pre-4.2.
    public function exportExcel()
    {
        return Excel::download(new AdminExportIncome, 'admin-incomes.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new AdminExportIncome, 'admin-incomes.csv');
    }
}
