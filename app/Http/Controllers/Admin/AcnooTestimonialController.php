<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AcnooTestimonialController extends Controller
{
    use HasAcnooActions;
    use HasUploader;

    public function index(Request $request)
    {
        $testimonials = Testimonial::latest()->paginate(10);

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        return view('admin.testimonials.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'work_at' => 'nullable|string',
            'review' => 'nullable',
            'star' => 'nullable',
            'client_name' => 'required|string',
            'client_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        Testimonial::create($request->except('client_image') + [
            'client_image' => $request->client_image ? $this->upload($request, 'client_image') : null,
        ]);

        return response()->json([
            'message' => __('Testimonial created successfully'),
            'redirect' => route('admin.testimonials.index'),
        ]);
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $request->validate([
            'work_at' => 'nullable|string',
            'review' => 'nullable|string',
            'star' => 'nullable|integer',
            'client_name' => 'required|string',
            'client_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $testimonial->update($request->except('client_image') + [
            'client_image' => $request->client_image ? $this->upload($request, 'client_image', $testimonial->client_image) : $testimonial->client_image,
        ]);

        return response()->json([
            'message' => __('Testimonial updated successfully'),
            'redirect' => route('admin.testimonials.index'),
        ]);
    }

    public function destroy(Testimonial $testimonial)
    {
        if (file_exists($testimonial->client_image)) {
            Storage::delete($testimonial->client_image);
        }

        $testimonial->delete();

        return response()->json([
            'message' => __('Testimonial Deleted successfully'),
            'redirect' => route('admin.testimonials.index'),
        ]);
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return Testimonial::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return Testimonial::when(request('search'), function ($q) {
            $q->where(function ($q) {
                $q->where('client_name', 'like', '%'.request('search').'%')
                    ->orWhere('work_at', 'like', '%'.request('search').'%')
                    ->orWhere('star', 'like', '%'.request('search').'%');
            });
        })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.testimonials.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['testimonials' => $rows];
    }

    protected function acnooDeletesRowByRow(): bool
    {
        return true;
    }

    protected function deletingAcnooRow($row): void
    {
        if (file_exists($row->client_image)) {
            Storage::delete($row->client_image);
        }
    }

    protected function acnooRedirectRoute(): string
    {
        return 'admin.testimonials.index';
    }

    protected function acnooDeleteMessage(): string
    {
        return __('Selected Testimonial deleted successfully');
    }
}
