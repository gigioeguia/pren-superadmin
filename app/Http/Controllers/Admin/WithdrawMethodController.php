<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Exports\AdminWithdrawMethodExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\WithdrawMethod\StoreWithdrawMethodRequest;
use App\Http\Requests\WithdrawMethod\UpdateWithdrawMethodRequest;
use App\Models\Currency;
use App\Models\WithdrawMethod;
use App\Services\WithdrawMethodService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class WithdrawMethodController extends Controller
{
    public function __construct(protected WithdrawMethodService $service)
    {
        $this->middleware('permission:withdraw-methods-read')->only('index', 'filter', 'exportExcel', 'exportCsv');
        $this->middleware('permission:withdraw-methods-create')->only('create', 'store');
        $this->middleware('permission:withdraw-methods-update')->only('edit', 'update', 'status');
        $this->middleware('permission:withdraw-methods-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $withdrawMethods = $this->service->listForChannel(Channel::Admin, auth()->user(), ['per_page' => 20]);

        return view('admin.withdraw-method.index', compact('withdrawMethods'));
    }

    public function create()
    {
        $currencies = Currency::latest()->get();

        return view('admin.withdraw-method.create', compact('currencies'));
    }

    public function store(StoreWithdrawMethodRequest $request)
    {
        $this->service->store($request->validated());

        return response()->json([
            'message' => __('Payment Method Created successfully'),
            'redirect' => route('admin.withdraw-methods.index'),
        ]);
    }

    public function edit(WithdrawMethod $withdrawMethod)
    {
        $currencies = Currency::latest()->get();

        return view('admin.withdraw-method.edit', compact('withdrawMethod', 'currencies'));
    }

    public function update(UpdateWithdrawMethodRequest $request, WithdrawMethod $withdrawMethod)
    {
        $this->service->update($withdrawMethod, $request->validated());

        return response()->json([
            'message' => __('Payment Method Updated successfully'),
            'redirect' => route('admin.withdraw-methods.index'),
        ]);
    }

    public function destroy(WithdrawMethod $withdrawMethod)
    {
        $this->service->delete($withdrawMethod);

        return response()->json([
            'message' => __('Withdraw Method Deleted Successfully'),
            'redirect' => route('admin.withdraw-methods.index'),
        ]);
    }

    public function filter(Request $request)
    {
        $withdrawMethods = $this->service->listForChannel(Channel::Admin, auth()->user(), array_merge($request->all(), ['per_page' => 20]));

        return view('admin.withdraw-method.datas', ['withdrawMethods' => $withdrawMethods]);
    }

    public function deleteAll(Request $request)
    {
        $this->service->deleteMany($request->ids ?? []);

        return response()->json([
            'message' => __('Selected Items deleted successfully.'),
            'redirect' => route('admin.withdraw-methods.index'),
        ]);
    }

    public function exportExcel()
    {
        return Excel::download(new AdminWithdrawMethodExport, 'withdraw-methods.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new AdminWithdrawMethodExport, 'withdraw-methods.csv');
    }
}
