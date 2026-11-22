<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;

class SubscriptionController extends Controller
{
    public function __construct(protected SubscriptionService $subscriptions) {}

    public function index()
    {
        $plans = $this->subscriptions->listForChannel(Channel::Landlord);

        return view('landlord.subscription.index', compact('plans'));
    }
}
