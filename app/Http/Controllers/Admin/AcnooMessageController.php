<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class AcnooMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:messages-read')->only('index');
        $this->middleware('permission:messages-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $messages = Message::latest()->paginate(10);
        return view('admin.message.index', compact('messages'));
    }

    public function acnooFilter(Request $request)
    {
        $messages = Message::when(request('search'), function ($q) {
            $q->where(function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%')
                    ->orWhere('phone', 'like', '%' . request('search') . '%')
                    ->orWhere('email', 'like', '%' . request('search') . '%')
                    ->orWhere('company_name', 'like', '%' . request('search') . '%')
                    ->orWhere('message', 'like', '%' . request('search') . '%');
            });
        })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.message.datas', compact('messages'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function destroy(string $id)
    {
        Message::where('id', $id)->delete();

        return response()->json([
            'message' => 'Message deleted successfully',
            'redirect' => route('admin.messages.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        Message::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Selected message deleted successfully',
            'redirect' => route('admin.messages.index')
        ]);
    }
}
