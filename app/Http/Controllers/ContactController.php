<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $page_data = get_option('manage-pages');

        return view('web.contact.index', compact('page_data'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'company_name' => 'nullable|string|max:255',
            'message' => 'required|string|max:255',
        ]);

        Message::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'company_name' => $validated['company_name'] ?? null,
            'message' => $validated['message'],
        ]);

        return response()->json([
            'message' => __('Your Message Submitted successfully'),
            'redirect' => route('contact.index'),
        ]);
    }
}
