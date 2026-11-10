<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class AcnooCommentController extends Controller
{

    public function acnooFilter(Request $request)
    {
        $comments = Comment::when(request('search'), function ($q) {
            $q->where(function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%')
                ->orWhere('email', 'like', '%' . request('search') . '%')
                ->orWhere('comment', 'like', '%' . request('search') . '%');
            });
        })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.blogs.comments.datas', compact('comments'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function destroy($id)
    {
        Comment::where('id', $id)->delete();
        return response()->json([
            'message' => __('Comment deleted successfully.'),
            'redirect' => route('admin.blogs.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        Comment::whereIn('id', $request->ids)->delete();
        return response()->json([
            'message' => __('Selected Comments deleted successfully'),
            'redirect' => route('admin.blogs.index')
        ]);
    }
}
