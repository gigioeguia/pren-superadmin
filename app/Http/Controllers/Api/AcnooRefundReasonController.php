<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RefundReason;

class AcnooRefundReasonController extends Controller
{
    public function index()
    {
        $refund_reason = RefundReason::latest()->get();

        return response()->json([
            'message' => 'Refund Reason List',
            'data' => $refund_reason
        ]);
    }
}
