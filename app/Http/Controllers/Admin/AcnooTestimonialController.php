<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AcnooTestimonialController extends Controller
{
    use HasUploader;

    public function index(Request $request)
    {
        $testimonials = Testimonial::latest()->paginate(10);
        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function acnooFilter(Request $request)
    {
        $testimonials = Testimonial::when(request('search'), function ($q) {
            $q->where(function ($q) {
                $q->where('client_name', 'like', '%' . request('search') . '%')
                    ->orWhere('work_at', 'like', '%' . request('search') . '%')
                    ->orWhere('star', 'like', '%' . request('search') . '%');
            });
        })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.testimonials.datas', compact('testimonials'))->render()
            ]);
        }

        return redirect(url()->previous());
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
            'client_image' => $request->client_image ? $this->upload($request, 'client_image') : NULL
        ]);

        return response()->json([
            'message' => __('Testimonial created successfully'),
            'redirect' => route('admin.testimonials.index')
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
            'redirect' => route('admin.testimonials.index')
        ]);
    }

    public function destroy(Testimonial $testimonial)
    {
        if (file_exists($testimonial->client_image)) {
            Storage::delete($testimonial->client_image);
        }

        $testimonial->delete();

        return response()->json([
            'message'   => __('Testimonial Deleted successfully'),
            'redirect'  => route('admin.testimonials.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        $testimonials = Testimonial::whereIn('id', $request->ids)->get();

        foreach ($testimonials as $testimonial) {
            if (file_exists($testimonial->client_image)) {
                Storage::delete($testimonial->client_image);
            }

            $testimonial->delete();
        }

        return response()->json([
            'message' => __('Selected Testimonial deleted successfully'),
            'redirect' => route('admin.testimonials.index')
        ]);
    }
}
