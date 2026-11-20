<?php

namespace App\Http\Controllers\Api;

use App\Enums\Channel;
use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Support;
use App\Services\SupportService;
use Illuminate\Http\Request;

class AcnooSupportController extends Controller
{
    use HasUploader;

    public function __construct(protected SupportService $service) {}

    public function index()
    {
        $userId = auth()->id();

        $messages = Support::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'asc')->get();

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $messages,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,txt,zip|max:10240',
        ]);

        $data = $this->service->storeMessage([
            'message' => $request->message,
        ], Channel::Api, auth()->user(), $request->file('file'));

        return response()->json([
            'message' => 'Message send successfully',
            'data' => $data,
        ]);
    }
}
