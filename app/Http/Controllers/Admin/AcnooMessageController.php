<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AcnooMessageController extends Controller
{
    use HasAcnooActions;

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

    public function destroy(string $id)
    {
        Message::where('id', $id)->delete();

        return response()->json([
            'message' => 'Message deleted successfully',
            'redirect' => route('admin.messages.index'),
        ]);
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return Message::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return Message::when(request('search'), function ($q) {
            $q->where(function ($q) {
                $q->where('name', 'like', '%'.request('search').'%')
                    ->orWhere('phone', 'like', '%'.request('search').'%')
                    ->orWhere('email', 'like', '%'.request('search').'%')
                    ->orWhere('company_name', 'like', '%'.request('search').'%')
                    ->orWhere('message', 'like', '%'.request('search').'%');
            });
        })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.message.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['messages' => $rows];
    }

    protected function acnooRedirectRoute(): string
    {
        return 'admin.messages.index';
    }

    protected function acnooDeleteMessage(): string
    {
        return 'Selected message deleted successfully';
    }
}
