<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServiceChargeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:service-charge-read')->only('index');
        $this->middleware('permission:service-charge-update')->only('update');
    }

    public function index()
    {
        $service_charge = Option::where('key', 'service-charge')->first();
        return view('admin.settings.service-charge.index', compact('service_charge'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'type' => 'required|string',
        ]);
        $service_charge = Option::findOrFail($id);

        Cache::forget($service_charge->key);

        $service_charge->update([
            'value' => $request->except('_token', '_method') + [
                    'amount' => $request->amount,
                    'type' => $request->type,
                ]
        ]);

        return response()->json([
            'message'   => __('Service Charge updated successfully'),
            'redirect'  => route('admin.service-charges.index')
        ]);
    }
}
