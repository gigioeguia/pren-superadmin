<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Traits\HasTableActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use HasTableActions;

    public function __construct()
    {
        $this->middleware('permission:faqs-read')->only('index', 'filter');
        $this->middleware('permission:faqs-create')->only('create', 'store');
        $this->middleware('permission:faqs-update')->only('edit', 'update', 'status');
        $this->middleware('permission:faqs-delete')->only('destroy', 'deleteAll');
    }

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

    protected function model(): string
    {
        return Faq::class;
    }

    protected function query(Request $request): Builder
    {
        return Faq::when($request->input('search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('question', 'like', '%'.$request->input('search').'%')
                    ->orWhere('answer', 'like', '%'.$request->input('search').'%');
            });
        })
            ->latest();
    }

    protected function view(): string
    {
        return 'admin.faq.datas';
    }

    protected function viewData(LengthAwarePaginator $rows): array
    {
        return ['faqs' => $rows];
    }

    protected function redirectRoute(): string
    {
        return 'admin.faqs.index';
    }

    protected function statusMessage(): string
    {
        return 'Faq ';
    }

    protected function deleteMessage(): string
    {
        return __('Selected Faq deleted successfully');
    }
}
