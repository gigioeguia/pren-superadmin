<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class AcnooFaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::latest()->paginate(10);
        return view('admin.faq.index', compact('faqs'));
    }


    public function acnooFilter(Request $request)
    {
        $faqs = Faq::when(request('search'), function ($q) {
            $q->where(function ($q) {
                $q->where('question', 'like', '%' . request('search') . '%')
                ->orWhere('answer', 'like', '%' . request('search') . '%');
            });
        })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.faq.datas', compact('faqs'))->render()
            ]);
        }

        return redirect(url()->previous());
    }


    public function create()
    {
        return view('admin.faq.create');
    }


    public function store(Request $request)
        {
            $request->validate([
                'status' => 'required',
                'question' => 'required|max:255',
                'answer' => 'required|max:500'
            ]);

            Faq::create($request->all());

            return response()->json([
                'message' => __('Faq created successfully'),
                'redirect' => route('admin.faqs.index')
            ]);
        }

        public function edit(string $id)
        {
            $faq = Faq::findOrFail($id);
            return view('admin.faq.edit', compact('faq'));
        }

        public function update(Request $request, Faq $faq)
        {
            $request->validate([
                'status' => 'required',
                'question' => 'required|max:255',
                'answer' => 'required|max:500'
            ]);

            $faq->update($request->all());

            return response()->json([
                'message' => __('Faq updated successfully'),
                'redirect' => route('admin.faqs.index')
            ]);
        }

        public function destroy($id)
        {
            Faq::where('id', $id)->delete();

            return response()->json([
                'message'   => __('Faq deleted successfully'),
                'redirect'  => route('admin.faqs.index')
            ]);
        }

        public function status(Request $request, $id)
        {
            $faq = Faq::findOrFail($id);
            $faq->update(['status' => $request->status]);
            return response()->json(['message' => 'Faq ']);
        }


        public function deleteAll(Request $request)
        {
            Faq::whereIn('id', $request->ids)->delete();

            return response()->json([
                'message' => __('Selected Faq deleted successfully'),
                'redirect' => route('admin.faqs.index')
            ]);
        }
}
