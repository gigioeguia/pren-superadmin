<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminWithdrawMethodExport;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\WithdrawMethod;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class AcnooWithdrawMethodController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:withdraw-methods-read')->only('index');
        $this->middleware('permission:withdraw-methods-create')->only('create', 'store');
        $this->middleware('permission:withdraw-methods-update')->only('edit', 'update', 'status');
        $this->middleware('permission:withdraw-methods-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $withdrawMethods = WithdrawMethod::with('currency:id,name')->latest()->paginate(20);
        return view('admin.withdraw-method.index', compact('withdrawMethods'));
    }

    public function acnooFilter(Request $request)
    {
        $withdrawMethods = WithdrawMethod::with('currency:id,name')
            ->when(request('search'), function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%')
                    ->orWhere('min_amount', 'like', '%' . request('search') . '%')
                    ->orWhere('max_amount', 'like', '%' . request('search') . '%')
                    ->orWhere('charge', 'like', '%' . request('search') . '%');

                $q->orWhereHas('currency', function ($q) {
                    $q->where('name', 'like', '%' . request('search') . '%');
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.withdraw-method.datas', compact('withdrawMethods'))->render()
            ]);
        }
        return redirect(url()->previous());
    }

    public function create()
    {
        $currencies = Currency::latest()->get();
        return view('admin.withdraw-method.create', compact('currencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'currency_id' => 'required|exists:currencies,id',
            'min_amount' => 'required|numeric',
            'max_amount' => 'required|numeric',
            'charge' => 'required|numeric',
            'charge_type' => 'required|string',
            'instructions' => 'nullable|string',
            'meta.input.*' => [
                'required',
                'regex:/^[a-zA-Z_ ]+$/',
                'max:255'
            ],
        ]);

        $rawInputs = $request->input('meta.input', []);
        $replace = array_map(function ($input) {
            $input = strtolower($input);
            $input = preg_replace('/\s+/', '_', trim($input)); // replace space with underscore
            return $input;
        }, $rawInputs);

        $request->merge([
            'meta' => [
                'label' => $request->input('meta.label'),
                'input' => $replace,
            ],
        ]);

        WithdrawMethod::create($request->all());

        return response()->json([
            'message'   => __('Payment Method Created successfully'),
            'redirect'  => route('admin.withdraw-methods.index')
        ]);
    }

    public function edit(WithdrawMethod $withdrawMethod)
    {
        $currencies = Currency::latest()->get();
        return view('admin.withdraw-method.edit', compact('withdrawMethod', 'currencies'));
    }

    public function update(Request $request, WithdrawMethod $withdrawMethod)
    {


        $request->validate([
            'name' => 'required|string|max:255',
            'min_amount' => 'required|numeric',
            'max_amount' => 'required|numeric',
            'charge' => 'required|numeric',
            'instructions' => 'nullable|string',
        ]);

        $rawInputs = $request->input('meta.input', []);
        $replace = array_map(function ($input) {
            $input = strtolower($input);
            $input = preg_replace('/\s+/', '_', trim($input)); // replace space with underscore
            return $input;
        }, $rawInputs);

        $request->merge([
            'meta' => [
                'label' => $request->input('meta.label'),
                'input' => $replace,
            ],
        ]);

        $withdrawMethod->update($request->all());

        return response()->json([
            'message'   => __('Payment Method Updated successfully'),
            'redirect'  => route('admin.withdraw-methods.index')
        ]);
    }

    public function destroy(WithdrawMethod $withdrawMethod)
    {
        $withdrawMethod->delete();
        return response()->json([
            'message' => __('Withdraw Method Deleted Successfully'),
            'redirect' => route('admin.withdraw-methods.index')
        ]);
    }

    public function status(Request $request, string $id)
    {
        $withdrawMethod = WithdrawMethod::findOrFail($id);
        $withdrawMethod->update(['status' => $request->status]);
        return response()->json(['message' => 'withdrawMethod']);
    }

    public function deleteAll(Request $request)
    {
        WithdrawMethod::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => __('Selected Items deleted successfully.'),
            'redirect' => route('admin.withdraw-methods.index')
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
