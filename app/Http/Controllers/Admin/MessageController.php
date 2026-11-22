<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Traits\HasTableActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    use HasTableActions;

    public function __construct()
    {
        $this->middleware('permission:messages-read')->only('index', 'filter');
        $this->middleware('permission:messages-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $messages = Message::latest()->paginate(10);

        return view('admin.message.index', compact('messages'));
    }

    public function destroy(string $id)
    {
        Message::where('id', $id)->delete();

        return response()->json([
            'message' => 'Message deleted successfully',
            'redirect' => route('admin.messages.index'),
        ]);
    }

    protected function model(): string
    {
        return Message::class;
    }

    protected function query(Request $request): Builder
    {
        return Message::when($request->input('search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->input('search').'%')
                    ->orWhere('phone', 'like', '%'.$request->input('search').'%')
                    ->orWhere('email', 'like', '%'.$request->input('search').'%')
                    ->orWhere('company_name', 'like', '%'.$request->input('search').'%')
                    ->orWhere('message', 'like', '%'.$request->input('search').'%');
            });
        })
            ->latest();
    }

    protected function view(): string
    {
        return 'admin.message.datas';
    }

    protected function viewData(LengthAwarePaginator $rows): array
    {
        return ['messages' => $rows];
    }

    protected function redirectRoute(): string
    {
        return 'admin.messages.index';
    }

    protected function deleteMessage(): string
    {
        return 'Selected message deleted successfully';
    }
}
