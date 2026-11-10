<?php

namespace App\Http\Controllers\Admin;

use App\Models\Notification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:notifications-read')->only('mtIndex');
    }

    public function mtIndex()
    {
        $notifications = auth()->user()->notifications()
            ->whereDate('created_at', today())
            ->latest()
            ->get();
        return view('admin.notifications.index', compact('notifications'));
    }

    public function acnooFilter(Request $request)
    {
        $notifications = auth()->user()->notifications()->when(request('days') == 'daily', function ($q) {
            $q->whereDate('created_at', now()->format('Y-m-d'));
        })
            ->when(request('days') == 'weekly', function ($q) {
                $q->whereDate('created_at', '>=', now()->subDays(6)->format('Y-m-d'));
            })
            ->when(request('days') == '15_days', function ($q) {
                $q->whereDate('created_at', '>=', now()->subDays(15)->format('Y-m-d'));
            })
            ->when(request('days') == 'monthly', function ($q) {
                $q->whereMonth('created_at', now()->format('m'));
            })
            ->when(request('days') == 'yearly', function ($q) {
                $q->whereYear('created_at', now()->format('Y'));
            })
            ->latest()
            ->get();

        return response()->json([
            'data' => view('admin.notifications.datas', compact('notifications'))->render()
        ]);
    }

   public function mtView(string $id)
        {
            $notify = Notification::find($id);
            if ($notify) {
                $notify->read_at = now();
                $notify->save();

                $user = auth()->user();
                $data = $notify->data;

                if (in_array($user->role, ['superadmin', 'admin'])) {
                    if (!empty($data['admin_url'])) {
                        return redirect($data['admin_url']);
                    } else {
                        return back()->with('error', __('No link available for this notification.'));
                    }
                }
                // fallback
                return redirect('/');

            return back()->with('error', __('Permission denied.'));
        }
    }

    public function mtReadAll()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        return back();
    }
}
