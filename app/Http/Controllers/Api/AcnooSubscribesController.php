<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanSubscribe;

class AcnooSubscribesController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => __('Unauthorized access.'),
            ], 401);
        }

        $subscribes = PlanSubscribe::where('user_id', auth()->id())->latest()->get();

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $subscribes
        ]);
    }
}
