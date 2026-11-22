<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $service)
    {
        $this->middleware('permission:notifications-read')->only('mtIndex', 'mtView', 'mtReadAll', 'filter');
    }

    public function mtIndex()
    {
        $notifications = $this->service->listForChannel(Channel::Admin, auth()->user(), ['today' => true]);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function filter(Request $request)
    {
        $notifications = $this->service->listForChannel(Channel::Admin, auth()->user(), $request->all());

        return response()->json([
            'data' => view('admin.notifications.datas', compact('notifications'))->render(),
        ]);
    }

    public function mtView(string $id)
    {
        $notify = Notification::find($id);
        if ($notify) {
            $this->service->markAsRead($notify);

            $user = auth()->user();
            $data = $notify->data;

            if (in_array($user->role, ['superadmin', 'admin'])) {
                if (! empty($data['admin_url'])) {
                    return redirect($data['admin_url']);
                } else {
                    return back()->with('error', __('No link available for this notification.'));
                }
            }

            return back()->with('error', __('Permission denied.'));
        }

        return back()->with('error', __('Permission denied.'));
    }

    public function mtReadAll()
    {
        $this->service->markAllRead(auth()->user());

        return back();
    }
}
