<?php

namespace App\Http\Controllers\Api;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $service) {}

    public function index()
    {
        $notifications = $this->service->listForChannel(Channel::Api, auth()->user(), []);

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $notifications,
        ]);
    }

    public function readAll()
    {
        $this->service->markAllRead(auth()->user());

        return response()->json([
            'message' => 'Mark all read successfully',
        ]);
    }
}
