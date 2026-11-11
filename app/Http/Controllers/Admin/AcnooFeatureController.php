<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AcnooFeatureController extends Controller
{
    use HasAcnooActions;
    use HasUploader;

    public function index(Request $request)
    {
        $features = Feature::latest()->paginate(10);

        return view('admin.features.index', compact('features'));
    }

    public function create()
    {
        return view('admin.features.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'status' => 'required',
            'title' => 'required',
            'bg_color' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        Feature::create($request->except('image') + [
            'image' => $request->image ? $this->upload($request, 'image') : null,
        ]);

        return response()->json([
            'message' => __('Feature created successfully'),
            'redirect' => route('admin.features.index'),
        ]);
    }

    public function edit(Feature $feature)
    {
        return view('admin.features.edit', compact('feature'));
    }

    public function update(Request $request, Feature $feature)
    {
        $request->validate([
            'status' => 'required',
            'title' => 'required|string',
            'bg_color' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $feature->update($request->except('image') + [
            'image' => $request->image ? $this->upload($request, 'image', $feature->image) : $feature->image,
        ]);

        return response()->json([
            'message' => __('Feature updated successfully'),
            'redirect' => route('admin.features.index'),
        ]);
    }

    public function destroy(Feature $feature)
    {
        if (file_exists($feature->image)) {
            Storage::delete($feature->image);
        }
        $feature->delete();

        return response()->json([
            'message' => __('Feature deleted successfully'),
            'redirect' => route('admin.features.index'),
        ]);
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return Feature::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return Feature::when(request('search'), function ($q) {
            $q->where(function ($q) {
                $q->where('title', 'like', '%'.request('search').'%');
            });
        })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.features.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['features' => $rows];
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
        return 'admin.features.index';
    }

    protected function acnooStatusMessage(): string
    {
        return 'Feature ';
    }

    protected function acnooDeleteMessage(): string
    {
        return __('Selected Feature deleted successfully');
    }
}
