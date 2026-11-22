<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Traits\HasTableActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use HasTableActions;

    public function __construct()
    {
        $this->middleware('permission:comments-read')->only('filter');
        $this->middleware('permission:comments-delete')->only('destroy', 'deleteAll');
    }

    public function destroy($id)
    {
        Comment::where('id', $id)->delete();

        return response()->json([
            'message' => __('Comment deleted successfully.'),
            'redirect' => route('admin.blogs.index'),
        ]);
    }

    protected function model(): string
    {
        return Comment::class;
    }

    protected function query(Request $request): Builder
    {
        return Comment::when($request->input('search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->input('search').'%')
                    ->orWhere('email', 'like', '%'.$request->input('search').'%')
                    ->orWhere('comment', 'like', '%'.$request->input('search').'%');
            });
        })
            ->latest();
    }

    protected function view(): string
    {
        return 'admin.blogs.comments.datas';
    }

    protected function viewData(LengthAwarePaginator $rows): array
    {
        return ['comments' => $rows];
    }

    protected function redirectRoute(): string
    {
        return 'admin.blogs.index';
    }

    protected function deleteMessage(): string
    {
        return __('Selected Comments deleted successfully');
    }
}
