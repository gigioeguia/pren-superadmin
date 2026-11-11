<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HouseType;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AcnooHouseTypeController extends Controller
{
    use HasAcnooActions;

    public function __construct()
    {
        $this->middleware('permission:house-types-read')->only('index');
        $this->middleware('permission:house-types-create')->only('store');
        $this->middleware('permission:house-types-update')->only('update', 'status');
        $this->middleware('permission:house-types-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $house_types = HouseType::latest()->paginate(10);

        return view('admin.house-types.index', compact('house_types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        HouseType::create($request->all());

        return response()->json([
            'message' => 'House Types Created successfully',
            'redirect' => route('admin.house-types.index'),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $reason = HouseType::findOrFail($id);

        $reason->update($request->all());

        return response()->json([
            'message' => 'House Types Updated successfully',
            'redirect' => route('admin.house-types.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $reason = HouseType::findOrFail($id);
        $reason->delete();

        return response()->json([
            'message' => 'House Types Deleted Successfully',
            'redirect' => route('admin.house-types.index'),
        ]);
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return HouseType::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return HouseType::when(request('search'), function ($q) {
            $q->where(function ($q) {
                $search = request('search');
                $q->where('name', 'like', '%'.$search.'%');
            });
        })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.house-types.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['house_types' => $rows];
    }

    protected function acnooPerPage(): int
    {
        return 20;
    }

    protected function acnooRedirectRoute(): string
    {
        return 'admin.house-types.index';
    }

    protected function acnooStatusMessage(): string
    {
        return 'House Type';
    }

    protected function acnooDeleteMessage(): string
    {
        return 'Selected House Types Deleted Successfully';
    }
}
