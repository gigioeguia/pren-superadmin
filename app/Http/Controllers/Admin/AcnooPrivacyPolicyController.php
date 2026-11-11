<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AcnooPrivacyPolicyController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:privacy-policy-read')->only('index');
        $this->middleware('permission:privacy-policy-update')->only('store');
    }

    public function index()
    {
        $privacy_policy = Option::where('key', 'privacy-policy')->first();

        return view('admin.privacy-policy.index', compact('privacy_policy'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'privacy_title' => 'required|string',
            'description' => 'required|string',
        ]);

        $privacy_policy = Option::updateOrCreate(
            ['key' => 'privacy-policy'],
            ['value' => [
                'privacy_title' => $request->privacy_title,
                'description' => $request->description,
            ]]
        );

        Cache::forget($privacy_policy->key);

        return response()->json([
            'message' => __('Privacy policy updated successfully'),
            'redirect' => route('admin.privacy-policy.index'),
        ]);
    }
}
