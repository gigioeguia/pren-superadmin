<?php

namespace App\Http\Controllers\Landlord;

use App\Models\User;
use App\Models\Support;
use App\Helpers\HasUploader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcnooSupportController extends Controller
{
    use HasUploader;

    public function index()
    {
        $admin = User::where('role', 'superadmin')->first();

        $userId = auth()->id();

        $messages = Support::with('sender:id,name,role,image')->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'asc')->get();


        return view('landlord.supports.index', compact('messages', 'admin'));
    }

    public function getMessage()
    {
        $userId = auth()->id();

        $messages = Support::with('sender:id,name,role,image')->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'asc')->get();

        return view('landlord.supports.message', compact('messages'))->render();
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|required_without:file',
            'file' => 'nullable|file'
        ]);

        $admin = User::where('role', 'superadmin')->first();

        $existingMessage = Support::with('sender:id,name,image')->where('sender_id', auth()->id())
            ->where('receiver_id', $admin->id)
            ->whereNull('parent_id')
            ->first();


        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $this->upload($request, 'file');
            $fileExtension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
        }

        Support::create($request->except('file', 'parent_id') + [
            'sender_id' => auth()->id(),
            'receiver_id' => $admin->id,
            'parent_id' => $existingMessage ? $existingMessage->id : NULL,
            'file' => $filePath ?? NULL,
            'file_info' => [
                'ext' => $fileExtension ?? NULL,
                'size' => $fileSize ?? NULL
            ],
        ]);

        return response()->json([
            'message' => 'Message sent successfully',
            'redirect' => route('landlord.supports.index')
        ]);
    }
}
