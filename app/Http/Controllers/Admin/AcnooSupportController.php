<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Support;
use App\Helpers\HasUploader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcnooSupportController extends Controller
{
    use HasUploader;

    public function __construct()
    {
        $this->middleware('permission:supports-read')->only(['index']);
        $this->middleware('permission:supports-create')->only('reply');
    }

    public function index()
    {
        $adminId = auth()->id();

        // Get all landlords
        $landlords = User::where('role', 'landlord')
            ->with(['supportMessages' => function ($q) use ($adminId) {
                $q->where(function ($query) use ($adminId) {
                    $query->where('sender_id', $adminId)
                        ->orWhere('receiver_id', $adminId);
                })
                    ->latest()
                    ->limit(1);
            }])
            ->withCount(['supportMessages as unread_count' => function ($q) use ($adminId) {
                $q->where('is_read', false)->where('receiver_id', $adminId);
            }])
            ->get();

        // Manually fetch latest message for each landlord
        foreach ($landlords as $landlord) {
            $landlord->latestMessage = $landlord
                ->allSupportMessagesWith($adminId)
                ->orderByDesc('created_at')
                ->first();
        }

        return view('admin.supports.index', compact('landlords'));
    }

    public function getDetailsMsg(string $id)
    {
        Support::where('receiver_id', auth()->id())
            ->where('sender_id', $id)
            ->update(['is_read' => true]);

        $messages = Support::where('sender_id', $id)
            ->orWhere('receiver_id', $id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_id' => $message->sender_id,
                    'receiver_id' => $message->receiver_id,
                    'time' => $message->created_at->format('g:i A'),
                    'file' =>  $message->file ? asset($message->file) : NULL,
                    'ext' => $message->file_info['ext'] ?? '',
                ];
            });

        return response()->json([
            'messages' => $messages,
            'auth_id' => auth()->id(),
        ]);
    }

    public function reply(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|required_without:file',
            'receiver_id' => 'required|exists:users,id',
        ]);

        $filePath = null;
        $fileExtension = null;
        $fileSize = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $this->upload($request, 'file');
            $fileExtension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
        }

        // Determine conversation root if exists
        $parent = null;
        if ($request->id) {
            $parent = Support::find($request->id);
        } else {
            // Try to find latest message between the two to set as parent
            $parent = Support::where(function ($q) use ($request) {
                $q->where('sender_id', auth()->id())
                    ->where('receiver_id', $request->receiver_id);
            })
                ->orWhere(function ($q) use ($request) {
                    $q->where('sender_id', $request->receiver_id)
                        ->where('receiver_id', auth()->id());
                })
                ->orderBy('created_at', 'asc')
                ->first();
        }

        Support::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'parent_id' => $parent->id ?? null,
            'file' => $filePath,
            'file_info' => [
                'ext' => $fileExtension,
                'size' => $fileSize,
            ],
        ]);

        return response()->json([
            'message' => 'Message sent successfully',
            'redirect' => route('admin.supports.index')
        ]);
    }

    public function acnooFilter(Request $request)
    {
        $adminId = auth()->id();

        // Get all landlords
        $landlords = User::where('role', 'landlord')
            ->with(['supportMessages' => function ($q) use ($adminId) {
                $q->where(function ($query) use ($adminId) {
                    $query->where('sender_id', $adminId)
                        ->orWhere('receiver_id', $adminId);
                })
                    ->latest()
                    ->limit(1);
            }])
            ->withCount(['supportMessages as unread_count' => function ($q) use ($adminId) {
                $q->where('is_read', false)->where('receiver_id', $adminId);
            }])
            ->when(request('search'), function ($q) {
                $q->where(function ($q) {
                    $search = request('search');
                    $q->where('name', 'like', '%' . $search . '%');
                });
            })
            ->latest()->get();

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.supports.users', compact('landlords'))->render()
            ]);
        }

        return redirect(url()->previous());
    }
}
