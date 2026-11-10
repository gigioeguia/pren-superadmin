<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Plan;

class SubscriptionController extends Controller
{
    public function index()
    {
        $plans = Plan::where('status', 1)->latest()->get();
        return view('landlord.subscription.index', compact('plans'));
    }
}
