<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;

class AcnooSubscriptionsController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => __('Unauthorized access.'),
            ], 401);
        }

        $plans = Plan::whereStatus(1)->latest()->get();
        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $plans,
        ]);
    }
}
