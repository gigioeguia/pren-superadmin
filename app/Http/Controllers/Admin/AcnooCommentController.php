<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AcnooCommentController extends Controller
{
    use HasAcnooActions;

    public function __construct()
    {
        $this->middleware('permission:comments-read')->only('acnooFilter');
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

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return Comment::class;
    }

    protected function acnooQuery(Request $request): Builder
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

    protected function acnooView(): string
    {
        return 'admin.blogs.comments.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['comments' => $rows];
    }

    protected function acnooRedirectRoute(): string
    {
        return 'admin.blogs.index';
    }

    protected function acnooDeleteMessage(): string
    {
        return __('Selected Comments deleted successfully');
    }
}
