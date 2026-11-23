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
        $this->middleware('permission:comments-update')->only('status');
        $this->middleware('permission:comments-delete')->only('destroy', 'deleteAll');
    }

    public function destroy($id)
    {
        // D-FIX-2 (spec 5.19): findOrFail — id inexistente → 404 en vez del
        // 200 success silent no-op del where()->delete() legado.
        Comment::findOrFail($id)->delete();

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
        // D-FIX-1 (spec 5.19): scope opcional por blog — el listador per-blog
        // pasa blog_id en sus forms; sin input, el contrato sin scope se
        // preserva (comportamiento anterior exacto).
        return Comment::when($request->input('blog_id'), function ($q) use ($request) {
            $q->where('blog_id', $request->input('blog_id'));
        })->when($request->input('search'), function ($q) use ($request) {
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

    protected function statusMessage(): string
    {
        // Espejo verbatim del patrón ('Blog', sin __()): el JS del toggle
        // añade " status published/unpublished" (custom-ajax.js).
        return 'Comment';
    }

    protected function deleteMessage(): string
    {
        return __('Selected Comments deleted successfully');
    }
}
