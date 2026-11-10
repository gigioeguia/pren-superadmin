<?php

namespace App\Http\Controllers;

use App\Models\Plan;

class PlanController extends Controller
{
    public function index()
    {
        $page_data = get_option('manage-pages');
        $plans = Plan::where('status',1)->latest()->get();

        return view('web.plan.index',compact('page_data', 'plans'));
    }
}
