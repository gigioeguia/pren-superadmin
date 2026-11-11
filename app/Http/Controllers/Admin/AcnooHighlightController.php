<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Highlight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AcnooHighlightController extends Controller
{
    use HasUploader;

    public function index(Request $request)
    {
        $highlights = Highlight::latest()->paginate(20);

        return view('admin.highlights.index', compact('highlights'));
    }

    public function acnooFilter(Request $request)
    {
        $highlights = Highlight::when(request('search'), function ($q) {
            $q->where(function ($q) {
                $q->where('short_title', 'like', '%'.request('search').'%')
                    ->orWhere('title', 'like', '%'.request('search').'%');
            });
        })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.highlights.datas', compact('highlights'))->render(),
            ]);
        }

        return redirect(url()->previous());
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
            'image' => 'required|image|max:1024|mimes:jpeg,png,jpg,gif,svg',
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
            'image' => 'image|max:1024|mimes:jpeg,png,jpg,gif,svg',
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

    public function status(Request $request, $id)
    {
        $highlight_status = Highlight::findOrFail($id);
        $highlight_status->update(['status' => $request->status]);

        return response()->json(['message' => 'Highlight']);
    }

    public function deleteAll(Request $request)
    {
        $highlights = Highlight::whereIn('id', $request->ids)->get();

        foreach ($highlights as $highlight) {
            if (file_exists($highlight->image)) {
                Storage::delete($highlight->image);
            }

            $highlight->delete();
        }

        return response()->json([
            'message' => __('Selected highlight deleted successfully'),
            'redirect' => route('admin.highlights.index'),
        ]);
    }
}
