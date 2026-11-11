<?php

namespace App\Http\Controllers\Landlord;

use App\Exports\LandlordUserMethodExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserMethod;
use App\Models\WithdrawMethod;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AcnooWithdrawMethodController extends Controller
{
    use HasAcnooActions;

    public function index(Request $request)
    {
        $withdraw_methods = UserMethod::with('withdraw_method:id,name,charge,min_amount,charge_type,meta')->where('user_id', auth()->id())->paginate(10);
        $methods = WithdrawMethod::latest()->get();
        $user = User::where('id', auth()->user()->id)->first();

        return view('landlord.withdraw-method.index', compact('withdraw_methods', 'methods', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'infos' => 'required|array',
        ]);

        $method_exist = UserMethod::where('user_id', auth()->id())->where('method_id', $request->method_id)->first();

        if ($method_exist) {
            return response()->json([
                'message' => 'Withdraw method already exist',
            ], 400);
        }

        UserMethod::create([
            'user_id' => auth()->id(),
            'method_id' => $request->method_id,
            'account_no' => $request->account_no,
            'infos' => $request->input('infos'),
        ]);

        return response()->json([
            'message' => __('Withdraw method setup successfully.'),
            'redirect' => route('landlord.withdraw-method.index'),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'method_id' => 'required|exists:withdraw_methods,id',
            'account_no' => 'required',
            'infos' => 'required|array',
        ]);

        $userId = auth()->id();

        $method = UserMethod::where('user_id', $userId)->findOrFail($id);

        $exists = UserMethod::where('user_id', $userId)
            ->where('method_id', $request->method_id)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => __('Withdraw method already exist.'),
            ], 400);
        }

        $method->update([
            'method_id' => $request->method_id,
            'account_no' => $request->account_no,
            'infos' => $request->infos,
        ]);

        return response()->json([
            'message' => __('Withdraw method updated successfully.'),
            'redirect' => route('landlord.withdraw-method.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $withdraw_method = UserMethod::findOrFail($id);
        $withdraw_method->delete();

        return response()->json([
            'message' => __('Withdraw Method Deleted Successfully'),
            'redirect' => route('landlord.withdraw-method.index'),
        ]);
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return UserMethod::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return UserMethod::with('withdraw_method:id,name')
            ->where('user_id', auth()->id())
            ->when(request('search'), function ($query) {
                $query->where('account_no', 'like', '%'.request('search').'%')
                    ->orWhereHas('withdraw_method', function ($q) {
                        $q->where('name', 'like', '%'.request('search').'%');
                    });
            })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'landlord.withdraw-method.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['withdraw_methods' => $rows];
    }

    protected function acnooRedirectRoute(): string
    {
        return 'landlord.withdraw-method.index';
    }

    protected function acnooDeleteMessage(): string
    {
        return __('Selected Withdraw Method Deleted Successfully');
    }

    protected function acnooExport(Request $request): object
    {
        return new LandlordUserMethodExport;
    }

    protected function acnooExcelFilename(): string
    {
        return 'landlord-user-method';
    }

    protected function acnooCsvFilename(): string
    {
        return 'landlord-user-method';
    }
}
