<?php

namespace App\Http\Controllers\Api;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;

class SubscriptionsController extends Controller
{
    public function __construct(protected SubscriptionService $subscriptions) {}

    public function index()
    {
        if (! auth()->check()) {
            return response()->json([
                'message' => __('Unauthorized access.'),
            ], 401);
        }

        $plans = $this->subscriptions->listForChannel(Channel::Api);

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $plans,
        ]);
    }
}
