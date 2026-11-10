<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Client;
use App\Models\Faq;
use App\Models\Plan;
use App\Models\Feature;
use App\Models\Highlight;
use App\Models\Testimonial;

class WebController extends Controller
{
    public function index()
    {
        $page_data = get_option('manage-pages');
        $features = Feature::whereStatus(1)->latest()->get();
        $testimonials = Testimonial::latest()->get();
        $recent_blogs = Blog::whereStatus(1)->latest()->take(3)->get();
        $blogs = Blog::whereStatus(1)->take(2)->get();
        $plans = Plan::where('status',1)->latest()->get();
        $faqs = Faq::where('status', 1)->latest()->take(7)->get();
        $clients = Client::where('status', 1)->latest()->take(6)->get();
        $highlights = Highlight::whereStatus(1)->latest()->take(2)->get();

        return view('web.index', compact('page_data','features','testimonials','recent_blogs','blogs','plans','faqs', 'clients', 'highlights'));
    }
}
