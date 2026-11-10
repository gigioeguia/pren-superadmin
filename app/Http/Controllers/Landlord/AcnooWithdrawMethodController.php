<?php

namespace App\Http\Controllers\Landlord;

use App\Exports\LandlordUserMethodExport;
use App\Models\User;
use App\Models\UserMethod;
use Illuminate\Http\Request;
use App\Models\WithdrawMethod;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class AcnooWithdrawMethodController extends Controller
{
    public function index(Request $request)
    {
        $withdraw_methods = UserMethod::with('withdraw_method:id,name,charge,min_amount,charge_type,meta')->where('user_id', auth()->id())->paginate(10);
        $methods = WithdrawMethod::latest()->get();
        $user = User::where('id', auth()->user()->id)->first();
        

        return view('landlord.withdraw-method.index', compact('withdraw_methods', 'methods', 'user'));
    }

    public function acnooFilter(Request $request)
    {
        $withdraw_methods = UserMethod::with('withdraw_method:id,name')
            ->where('user_id', auth()->id())
            ->when(request('search'), function ($query) {
                $query->where('account_no', 'like', '%' . request('search') . '%')
                    ->orWhereHas('withdraw_method', function ($q) {
                        $q->where('name', 'like', '%' . request('search') . '%');
                    });
            })
            ->latest()
            ->paginate(request('per_page') ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.withdraw-method.datas', compact('withdraw_methods'))->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    public function store(Request $request)
    {
        $request->validate([
            'infos' => 'required|array',
        ]);

        $method_exist = UserMethod::where('user_id', auth()->id())->where('method_id', $request->method_id)->first();

        if ($method_exist) {
            return response()->json([
                'message' => 'Withdraw method already exist'
            ], 400);
        }

        UserMethod::create([
            'user_id' => auth()->id(),
            'method_id' => $request->method_id,
            'account_no' => $request->account_no,
            'infos' => $request->input('infos')
        ]);

        return response()->json([
            'message' => __('Withdraw method setup successfully.'),
            'redirect' => route('landlord.withdraw-method.index')
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
            'redirect' => route('landlord.withdraw-method.index')
        ]);
    }

    public function destroy(string $id)
    {
        $withdraw_method = UserMethod::findOrFail($id);
        $withdraw_method->delete();
        return response()->json([
            'message'   => __('Withdraw Method Deleted Successfully'),
            'redirect'  => route('landlord.withdraw-method.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        UserMethod::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message'   => __('Selected Withdraw Method Deleted Successfully'),
            'redirect'  => route('landlord.withdraw-method.index')
        ]);
    }

    public function exportExcel()
    {
        return Excel::download(new LandlordUserMethodExport, 'landlord-user-method.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new LandlordUserMethodExport, 'landlord-user-method.csv');
    }
}
