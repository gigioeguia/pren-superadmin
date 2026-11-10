<?php

namespace App\Http\Controllers\Api;

use App\Models\UserMethod;
use Illuminate\Http\Request;
use App\Models\WithdrawMethod;
use App\Http\Controllers\Controller;

class AcnooWithdrawMethodController extends Controller
{
    public function index()
    {
        $withdraw_methods = WithdrawMethod::where('status', 1)
            ->latest()
            ->get();

        $data = $withdraw_methods->map(function ($method) {

            $meta = is_array($method->meta) ? $method->meta : json_decode($method->meta, true);

            $transformedMeta = collect($meta['label'] ?? [])->map(function ($label, $index) use ($meta) {
                return [
                    "label" => $label,
                    "input" => $meta['input'][$index] ?? null,
                ];
            });

            return [
                'id' => $method->id,
                'name' => $method->name,
                'instructions' => $method->instructions,
                'status' => $method->status,
                'meta' => $transformedMeta,
            ];
        });


        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'method_id' => 'required|integer',
        ]);

        $userMethod = UserMethod::create($request->except('user_id') + [
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'message' => __('Withdraw method setup successfully.'),
            'data' => $userMethod,
        ]);
    }

    public function show()
    {
        $method = UserMethod::with('withdraw_method')->where('user_id', auth()->user()->id)->get();

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'balance' => auth()->user()->balance,
            'data' => $method
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'method_id' => 'required|integer',
        ]);

        $method = UserMethod::findOrFail($id);
        $method->update($request->except('user_id') + [
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'message' => __('Withdraw method setup successfully.'),
            'data' => $method,
        ]);
    }

    public function destroy(string $id)
    {
        UserMethod::findOrFail($id)->delete();

        return response()->json([
            'message' => __('Withdraw method deleted successfully.')
        ]);
    }

    public function user_withdraw_method()
    {
        $data = UserMethod::with(['withdraw_method:id,name,min_amount,max_amount,charge,charge_type,meta'])
            ->where('user_id', auth()->id())
            ->get();

        $methods  = $data->map(function ($userMethod) {
            $withdrawMethod = $userMethod->withdraw_method;

            if ($withdrawMethod) {
                $meta = is_array($withdrawMethod->meta) ? $withdrawMethod->meta : json_decode($withdrawMethod->meta, true);

                $transformedMeta = collect($meta['label'] ?? [])->map(function ($label, $index) use ($meta) {
                    return [
                        "label" => $label,
                        "input" => $meta['input'][$index] ?? null,
                    ];
                });

                $transformedData = [
                    'name' => $withdrawMethod->name,
                    'charge' => $withdrawMethod->charge ?? null,
                    'charge_type' => $withdrawMethod->charge_type ?? null,
                    'min_amount' => $withdrawMethod->min_amount ?? 0,
                    'max_amount' => $withdrawMethod->max_amount ?? 0,
                    'meta' => $transformedMeta,
                ];
            }

            $usermethodData = [
                'id' => $userMethod->id,
                'user_id' => $userMethod->user_id,
                'method_id' => $userMethod->method_id,
                'account_no' => $userMethod->account_no,
                'infos' => $userMethod->infos ?? null,
                'notes' => $userMethod->notes ?? null,
            ];

            return array_merge($usermethodData, $transformedData);
        });

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => [
                'balance' => auth()->user()->balance,
                'methods' => $methods,
            ],
        ]);
    }
}
