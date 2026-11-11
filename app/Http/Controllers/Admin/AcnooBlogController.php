<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Comment;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AcnooBlogController extends Controller
{
    use HasAcnooActions;
    use HasUploader;

    public function index(Request $request)
    {
        $blogs = Blog::latest()->paginate(20);

        return view('admin.blogs.index', compact('blogs'));
    }

    public function create()
    {
        return view('admin.blogs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:blogs,title',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'status' => 'boolean',
            'descriptions' => 'nullable|string',
            'tags' => 'nullable|array',
            'meta.title' => 'nullable|string',
            'meta.description' => 'nullable|string',
        ]);

        Blog::create($request->except('image') + [
            'user_id' => Auth::id(),
            'image' => $request->image ? $this->upload($request, 'image') : null,
        ]);

        return response()->json([
            'message' => __('BLog created successfully'),
            'redirect' => route('admin.blogs.index'),
        ]);
    }

    public function edit(Blog $blog)
    {
        return view('admin.blogs.edit', compact('blog'));
    }

    public function update(Request $request, Blog $blog)
    {

        $request->validate([
            'title' => 'required|unique:blogs,title,'.$blog->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'status' => 'boolean',
            'descriptions' => 'nullable|string',
            'tags' => 'nullable|array',
            'meta.title' => 'nullable|string',
            'meta.description' => 'nullable|string',
        ]);

        $blog->update($request->except('image') + [
            'user_id' => Auth::id(),
            'image' => $request->image ? $this->upload($request, 'image', $blog->image) : $blog->image,
        ]);

        return response()->json([
            'message' => __('BLog updated successfully'),
            'redirect' => route('admin.blogs.index'),
        ]);
    }

    public function destroy(Blog $blog)
    {
        if (file_exists($blog->image)) {
            Storage::delete($blog->image);
        }

        $blog->delete();

        return response()->json([
            'message' => __('Blog deleted successfully'),
            'redirect' => route('admin.blogs.index'),
        ]);
    }

    public function filterComment($id)
    {
        $blog = Blog::findOrFail($id);
        $comments = Comment::with('blog:id')->whereStatus(1)->where('blog_id', $blog->id)->latest()->paginate(10);

        return view('admin.blogs.comments.index', compact('comments'));
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return Blog::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return Blog::when(request('search'), function ($q) {
            $q->where(function ($q) {
                $q->where('title', 'like', '%'.request('search').'%');
            });
        })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.blogs.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['blogs' => $rows];
    }

    protected function acnooDeletesRowByRow(): bool
    {
        return true;
    }

    protected function deletingAcnooRow($row): void
    {
        if (file_exists($row->image)) {
            Storage::delete($row->image);
        }
    }

    protected function acnooRedirectRoute(): string
    {
        return 'admin.blogs.index';
    }

    protected function acnooStatusMessage(): string
    {
        return 'Blog';
    }

    protected function acnooDeleteMessage(): string
    {
        return __('Selected Blog deleted successfully');
    }
}
