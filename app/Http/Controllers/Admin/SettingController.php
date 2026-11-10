<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    use HasUploader;

    public function __construct()
    {
        $this->middleware('permission:settings-read')->only('index');
        $this->middleware('permission:settings-update')->only('update');
    }

    public function index()
    {
        $general = Option::where('key', 'general')->first();
        return view('admin.settings.general', compact('general'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'app_name' => 'required|string|max:100',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'app_url' => 'required|string',
            'logo' => 'nullable|image',
            'withdraw_payment_success_title' => 'required|string|max:25',
            'withdraw_payment_success_message' => 'required|string|max:120'
        ]);

        $general = Option::findOrFail($id);
        Cache::forget($general->key);

        $general->update([
            'value' => $request->except('_token', '_method', 'logo', 'favicon', 'login_page_img') + [
                'logo' => $request->logo ? $this->upload($request, 'logo', $general->value['logo'] ?? null) : $general->value['logo'] ?? null,
                'favicon' => $request->favicon ? $this->upload($request, 'favicon', $general->value['favicon'] ?? null) : $general->value['favicon'] ?? null,
                'login_page_img' => $request->login_page_img ? $this->upload($request, 'login_page_img', $general->value['login_page_img'] ?? null) : $general->value['login_page_img'] ?? null
            ]
        ]);

        return response()->json([
            'message'   => __('General Setting updated successfully'),
            'redirect'  => route('admin.settings.index')
        ]);
    }
}
