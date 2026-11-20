<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\Channel;
use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Support;
use App\Models\User;
use App\Services\SupportService;
use Illuminate\Http\Request;

class AcnooSupportController extends Controller
{
    use HasUploader;

    public function __construct(protected SupportService $service) {}

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
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,txt,zip|max:10240',
        ]);

        $this->service->storeMessage([
            'message' => $request->message,
        ], Channel::Landlord, auth()->user(), $request->file('file'));

        return response()->json([
            'message' => 'Message sent successfully',
            'redirect' => route('landlord.supports.index'),
        ]);
    }
}
