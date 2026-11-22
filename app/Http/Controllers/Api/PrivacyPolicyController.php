<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Option;

class PrivacyPolicyController extends Controller
{
    public function index()
    {
        $privacy_policy = Option::where('key', 'privacy-policy')->first();

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $privacy_policy,
        ]);
    }
}
