<?php

namespace App\Http\Controllers\Admin;

use App\Models\Gateway;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcnooGatewayController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:payment-gateway-read')->only('index');
        $this->middleware('permission:payment-gateway-update')->only('update');
    }

    public function index()
    {
        $gateways = Gateway::all();
        $currencies = Currency::latest()->get();
        return view('admin.gateways.index', compact('gateways', 'currencies'));
    }

    public function update(Request $request, string $id)
    {
        Gateway::findOrFail($id)->update($request->except('image') + [
            'image' => $request->hasFile('image') ? $this->upload($request, 'image') : NULL
        ]);

        return response()->json([
            'message' => 'Gateway updated successfully',
            'redirect' => route('admin.gateways.index')
        ]);
    }
}
