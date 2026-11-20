<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Highlight;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AcnooHighlightController extends Controller
{
    use HasAcnooActions;
    use HasUploader;

    public function __construct()
    {
        $this->middleware('permission:highlights-read')->only('index', 'acnooFilter');
        $this->middleware('permission:highlights-create')->only('create', 'store');
        $this->middleware('permission:highlights-update')->only('edit', 'update', 'status');
        $this->middleware('permission:highlights-delete')->only('destroy', 'deleteAll');
    }

    public function index(Request $request)
    {
        $highlights = Highlight::latest()->paginate(20);

        return view('admin.highlights.index', compact('highlights'));
    }

    public function create()
    {
        return view('admin.highlights.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'short_title' => 'required|max:55',
            'title' => 'required|unique:highlights,title',
            'status' => 'boolean',
            'image' => 'required|image|max:1024|mimes:jpeg,png,jpg,gif',
        ]);

        Highlight::create($request->except('image', 'title', 'slug') + [
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'image' => $request->image ? $this->upload($request, 'image') : null,
        ]);

        return response()->json([
            'message' => __('Highlight created successfully'),
            'redirect' => route('admin.highlights.index'),
        ]);
    }

    public function edit(Highlight $highlight)
    {
        return view('admin.highlights.edit', compact('highlight'));
    }

    public function update(Request $request, Highlight $highlight)
    {

        $request->validate([
            'short_title' => 'required|max:55',
            'title' => 'required|unique:highlights,title,'.$highlight->id,
            'slug' => 'required',
            'status' => 'boolean',
            'image' => 'image|max:1024|mimes:jpeg,png,jpg,gif',
        ]);

        $highlight->update($request->except('image', 'title', 'slug') + [
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'image' => $request->image ? $this->upload($request, 'image', $highlight->image) : $highlight->image,
        ]);

        return response()->json([
            'message' => __('Highlight updated successfully'),
            'redirect' => route('admin.highlights.index'),
        ]);
    }

    public function destroy(Highlight $highlight)
    {
        if (file_exists($highlight->image)) {
            Storage::delete($highlight->image);
        }

        $highlight->delete();

        return response()->json([
            'message' => __('Highlight deleted successfully'),
            'redirect' => route('admin.highlights.index'),
        ]);
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return Highlight::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return Highlight::when($request->input('search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('short_title', 'like', '%'.$request->input('search').'%')
                    ->orWhere('title', 'like', '%'.$request->input('search').'%');
            });
        })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.highlights.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['highlights' => $rows];
    }

    protected function acnooDeletesRowByRow(): bool
    {
        return true;
    }

    protected function deletingAcnooRow($row): array
    {
        $image = $row->image;

        if ($image && file_exists($image)) {
            return [fn () => Storage::delete($image)];
        }

        return [];
    }

    protected function acnooRedirectRoute(): string
    {
        return 'admin.highlights.index';
    }

    protected function acnooStatusMessage(): string
    {
        return 'Highlight';
    }

    protected function acnooDeleteMessage(): string
    {
        return __('Selected highlight deleted successfully');
    }
}
