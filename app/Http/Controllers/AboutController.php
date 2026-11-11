<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Models\Plan;

class AboutController extends Controller
{
    public function index()
    {
        $page_data = get_option('manage-pages');
        $features = Feature::whereStatus(1)->latest()->get();
        $plans = Plan::where('status', 1)->latest()->get();

        return view('web.about.index', compact('page_data', 'features', 'plans'));
    }
}
