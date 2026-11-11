<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\User;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AcnooFacilityController extends Controller
{
    use HasAcnooActions;

    public function __construct()
    {
        $this->middleware('permission:facilities-read')->only('index');
        $this->middleware('permission:facilities-create')->only('store');
        $this->middleware('permission:facilities-update')->only('update', 'status');
        $this->middleware('permission:facilities-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $facilities = Facility::with('landlord:id,name')->latest()->paginate(10);
        $landlords = User::whereRole('landlord')->latest()->get();

        return view('admin.facilities.index', compact('facilities', 'landlords'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'landlord_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:0,1',
        ]);

        Facility::create($request->all());

        return response()->json([
            'message' => 'Facility Created Successfully',
            'redirect' => route('admin.facilities.index'),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'landlord_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:0,1',
        ]);

        $facility = Facility::findOrFail($id);

        $facility->update($request->all());

        return response()->json([
            'message' => 'Facility Updated Successfully',
            'redirect' => route('admin.facilities.index'),
        ]);
    }

    public function destroy(string $id)
    {
        Facility::where('id', $id)->delete();

        return response()->json([
            'message' => 'Facility Deleted Successfully',
            'redirect' => route('admin.facilities.index'),
        ]);
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return Facility::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return Facility::with('landlord:id,name')
            ->when(request('search'), function ($q) {
                $search = request('search');
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                    $q->orWhereHas('landlord', function ($landlordQuery) use ($search) {
                        $landlordQuery->where('name', 'like', '%'.$search.'%');
                    });
                });
            })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.facilities.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['facilities' => $rows];
    }

    protected function acnooRedirectRoute(): string
    {
        return 'admin.facilities.index';
    }

    protected function acnooStatusMessage(): string
    {
        return 'Facility';
    }

    protected function acnooDeleteMessage(): string
    {
        return 'Selected Facility Deleted Successfully';
    }
}
