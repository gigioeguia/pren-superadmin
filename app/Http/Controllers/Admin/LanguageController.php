<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\LanguageService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class LanguageController extends Controller
{
    public function __construct(private LanguageService $service)
    {
        $this->middleware('permission:languages-read')->only('index', 'show', 'filter');
        $this->middleware('permission:languages-create')->only('store');
        $this->middleware('permission:languages-update')->only('update', 'status', 'isDefault');
        $this->middleware('permission:languages-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $languages = $this->service->list();

        return view('admin.languages.index', compact('languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order' => 'required|integer|min:1',
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:languages,code',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $this->service->store([
            'order' => $request->order,
            'name' => $request->name,
            'code' => $request->code,
            'icon' => $request->file('icon'),
        ]);

        return response()->json([
            'message' => __('Laguage created successfully'),
            'redirect' => route('admin.languages.index'),
        ]);
    }

    public function show(Request $request, $code)
    {
        $result = $this->service->translations(
            $code,
            $request->filled('search') ? $request->search : null
        );

        if ($result === null) {
            abort(404, 'Language file not found');
        }

        $lang = $result['language'];
        $entries = $result['entries'];

        $perPage = 20;
        $page = $request->get('page', 1);

        $rows = new LengthAwarePaginator(
            $entries->forPage($page, $perPage),
            $entries->count(),
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => $request->query()]
        );

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.languages.translates.datas', compact('rows', 'lang'))->render(),
            ]);
        }

        return view('admin.languages.translates.translate', compact('rows', 'lang'));
    }

    public function update(Request $request, Language $language)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:languages,code,'.$language->id,
            'order' => 'required|integer|min:1',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $this->service->update($language, [
            'name' => $request->name,
            'code' => $request->code,
            'order' => $request->order,
            'icon' => $request->file('icon'),
        ]);

        return response()->json([
            'message' => __('Language updated successfully'),
            'redirect' => route('admin.languages.index'),
        ]);
    }

    public function destroy(Language $language)
    {
        if ($language->is_default) {
            return response()->json([
                'message' => __('Default language cannot be deleted'),
            ], 403);
        }

        $this->service->delete($language);

        return response()->json([
            'message' => __('Language deleted successfully'),
            'redirect' => route('admin.languages.index'),
        ]);
    }

    public function status(Request $request, $id)
    {
        $language = Language::findOrFail($id);

        if ($language->is_default) {
            return response()->json([
                'message' => __('Default language status cannot be changed'),
            ], 403);
        }

        $this->service->setStatus($language, $request->status);

        return response()->json(['message' => __('Language')]);
    }

    public function isDefault(string $id)
    {
        $this->service->setDefault($id);

        return response()->json([
            'message' => __('Default language updated successfully'),
        ]);
    }

    public function deleteAll(Request $request)
    {
        $this->service->deleteMany($request->ids);

        return response()->json([
            'message' => __('Selected Language deleted successfully'),
            'redirect' => route('admin.languages.index'),
        ]);
    }

    public function filter(Request $request)
    {
        $rows = $this->service->list($request->only(['search', 'per_page']));

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.languages.datas', ['languages' => $rows])->render(),
            ]);
        }

        return redirect(url()->previous());
    }
}
