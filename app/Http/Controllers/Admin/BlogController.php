<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Comment;
use App\Traits\HasTableActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    use HasTableActions;
    use HasUploader;

    public function __construct()
    {
        $this->middleware('permission:blogs-read')->only('index', 'filter', 'filterComment');
        $this->middleware('permission:blogs-create')->only('create', 'store');
        $this->middleware('permission:blogs-update')->only('edit', 'update', 'status');
        $this->middleware('permission:blogs-delete')->only('destroy', 'deleteAll');
    }

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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
        // Seg-30 (spec 5.19): sin filtro de status — el listador debe ver
        // TODO el inventario, incluidos los ocultos, para poder re-aprobarlos.
        $comments = Comment::with('blog:id')->where('blog_id', $blog->id)->latest()->paginate(10);

        return view('admin.blogs.comments.index', compact('comments', 'blog'));
    }

    protected function model(): string
    {
        return Blog::class;
    }

    protected function query(Request $request): Builder
    {
        return Blog::when($request->input('search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->input('search').'%');
            });
        })
            ->latest();
    }

    protected function view(): string
    {
        return 'admin.blogs.datas';
    }

    protected function viewData(LengthAwarePaginator $rows): array
    {
        return ['blogs' => $rows];
    }

    protected function deletesRowByRow(): bool
    {
        return true;
    }

    protected function deletingRow($row): array
    {
        $image = $row->image;

        if ($image && file_exists($image)) {
            return [fn () => Storage::delete($image)];
        }

        return [];
    }

    protected function redirectRoute(): string
    {
        return 'admin.blogs.index';
    }

    protected function statusMessage(): string
    {
        return 'Blog';
    }

    protected function deleteMessage(): string
    {
        return __('Selected Blog deleted successfully');
    }
}
