<?php

namespace App\Http\Controllers\Admin;

use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

class AcnooReminderMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:reminder-message-read')->only('index');
        $this->middleware('permission:reminder-message-create')->only('store');
        $this->middleware('permission:reminder-message-update')->only('update');
        $this->middleware('permission:reminder-message-delete')->only('destroy');
    }

    public function index()
    {
        $messages = Option::where('key', 'reminder-messages')->latest()->paginate(10);
        return view('admin.settings.reminder-message.index', compact('messages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'payment_method' => 'nullable|string',
        ]);

        Option::create([
            'key' => 'reminder-messages',
            'value' => $request->except('_token', '_method'),
        ]);

        return response()->json([
            'message'   => __('Reminder messages create successfully'),
            'redirect'  => route('admin.reminder-messages.index')
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'payment_method' => 'nullable|string',
        ]);

        $message = Option::where('key', 'reminder-messages')->findOrFail($id);
        Cache::forget($message->key);
        $message->update([
            'key' => 'reminder-messages',
            'value' => $request->except('_token', '_method'),
        ]);

        return response()->json([
            'message'   => __('Reminder messages updated successfully'),
            'redirect'  => route('admin.reminder-messages.index')
        ]);
    }

    public function destroy(string $id)
    {
        $option = Option::where('key', 'reminder-messages')->findOrFail($id);
        $option->delete();

        return response()->json([
            'message' => __('Reminder messages deleted successfully'),
            'redirect' => route('admin.reminder-messages.index')
        ]);
    }
}
