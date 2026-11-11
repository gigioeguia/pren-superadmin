<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AcnooFaqController extends Controller
{
    use HasAcnooActions;

    public function index()
    {
        $faqs = Faq::latest()->paginate(10);

        return view('admin.faq.index', compact('faqs'));
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
            'answer' => 'required|max:500',
        ]);

        Faq::create($request->all());

        return response()->json([
            'message' => __('Faq created successfully'),
            'redirect' => route('admin.faqs.index'),
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
            'answer' => 'required|max:500',
        ]);

        $faq->update($request->all());

        return response()->json([
            'message' => __('Faq updated successfully'),
            'redirect' => route('admin.faqs.index'),
        ]);
    }

    public function destroy($id)
    {
        Faq::where('id', $id)->delete();

        return response()->json([
            'message' => __('Faq deleted successfully'),
            'redirect' => route('admin.faqs.index'),
        ]);
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return Faq::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return Faq::when(request('search'), function ($q) {
            $q->where(function ($q) {
                $q->where('question', 'like', '%'.request('search').'%')
                    ->orWhere('answer', 'like', '%'.request('search').'%');
            });
        })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.faq.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['faqs' => $rows];
    }

    protected function acnooRedirectRoute(): string
    {
        return 'admin.faqs.index';
    }

    protected function acnooStatusMessage(): string
    {
        return 'Faq ';
    }

    protected function acnooDeleteMessage(): string
    {
        return __('Selected Faq deleted successfully');
    }
}
