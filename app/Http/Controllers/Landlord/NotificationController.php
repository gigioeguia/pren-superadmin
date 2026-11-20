<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $service) {}

    public function mtIndex()
    {
        $notifications = $this->service->listForChannel(Channel::Landlord, auth()->user(), ['today' => true]);

        return view('landlord.notifications.index', compact('notifications'));
    }

    public function acnooFilter(Request $request)
    {
        $notifications = $this->service->listForChannel(Channel::Landlord, auth()->user(), $request->all());

        return response()->json([
            'data' => view('landlord.notifications.datas', compact('notifications'))->render(),
        ]);
    }

    public function mtView(string $id)
    {
        $notify = Notification::find($id);
        if ($notify) {
            $this->service->markAsRead($notify);

            $user = auth()->user();
            $data = $notify->data;

            if ($user->role === 'landlord') {
                if (! empty($data['landlord_url'])) {
                    return redirect($data['landlord_url']);
                } else {
                    return back()->with('error', __('No link available for this notification.'));
                }
            }

            return redirect('/');
        }

        return back()->with('error', __('Permission denied.'));
    }

    public function mtReadAll()
    {
        $this->service->markAllRead(auth()->user());

        return back();
    }
}
