<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;

class NotifyContrller extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:notifications-read')->only('mtIndex', 'mtView');
    }

    public function mtIndex()
    {
        return view('admin.notifications.index');
    }

    public function mtView(string $id)
    {
        $notify = Notification::find($id);
        $notify->read_at = now();
        $notify->save();
        return redirect($notify->data['url'] ?? '/');
    }

    public function mtReadAll()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        return back();
    }
}
