<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminWithdrawMethodExport;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\WithdrawMethod;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AcnooWithdrawMethodController extends Controller
{
    use HasAcnooActions;

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
                'max:255',
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
            'message' => __('Payment Method Created successfully'),
            'redirect' => route('admin.withdraw-methods.index'),
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
            'message' => __('Payment Method Updated successfully'),
            'redirect' => route('admin.withdraw-methods.index'),
        ]);
    }

    public function destroy(WithdrawMethod $withdrawMethod)
    {
        $withdrawMethod->delete();

        return response()->json([
            'message' => __('Withdraw Method Deleted Successfully'),
            'redirect' => route('admin.withdraw-methods.index'),
        ]);
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return WithdrawMethod::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return WithdrawMethod::with('currency:id,name')
            ->when(request('search'), function ($q) {
                $q->where('name', 'like', '%'.request('search').'%')
                    ->orWhere('min_amount', 'like', '%'.request('search').'%')
                    ->orWhere('max_amount', 'like', '%'.request('search').'%')
                    ->orWhere('charge', 'like', '%'.request('search').'%');

                $q->orWhereHas('currency', function ($q) {
                    $q->where('name', 'like', '%'.request('search').'%');
                });
            })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.withdraw-method.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['withdrawMethods' => $rows];
    }

    protected function acnooPerPage(): int
    {
        return 20;
    }

    protected function acnooDeleteMessage(): string
    {
        return __('Selected Items deleted successfully.');
    }

    protected function acnooRedirectRoute(): string
    {
        return 'admin.withdraw-methods.index';
    }

    protected function acnooStatusMessage(): string
    {
        return 'withdrawMethod';
    }

    protected function acnooExport(Request $request): object
    {
        return new AdminWithdrawMethodExport;
    }

    protected function acnooExcelFilename(): string
    {
        return 'withdraw-methods';
    }

    protected function acnooCsvFilename(): string
    {
        return 'withdraw-methods';
    }
}
