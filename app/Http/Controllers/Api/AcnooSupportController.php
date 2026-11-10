<?php

namespace App\Http\Controllers\Api;

use App\Helpers\HasUploader;
use App\Models\User;
use App\Models\Support;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcnooSupportController extends Controller
{
    use HasUploader;

    public function index()
    {
        $userId = auth()->id();

        $messages = Support::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'asc')->get();

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $messages
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string',
            'file' => 'nullable|file'
        ]);

        $admin = User::where('role', 'superadmin')->first();

        $existingMessage = Support::where('sender_id', auth()->id())
            ->where('receiver_id', $admin->id)
            ->whereNull('parent_id')
            ->first();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $this->upload($request, 'file');
            $fileExtension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
        }

        $data = Support::create($request->except('file', 'parent_id') + [
            'sender_id' => auth()->id(),
            'receiver_id' => $admin->id,
            'parent_id' =>  $existingMessage ? $existingMessage->id : NULL,
            'file' => $filePath ?? NULL,
            'file_info' => [
                'ext' => $fileExtension ?? NULL,
                'size' => $fileSize ?? NULL
            ],
        ]);

        return response()->json([
            'message' => 'Message send successfully',
            'data' => $data
        ]);
    }
}
