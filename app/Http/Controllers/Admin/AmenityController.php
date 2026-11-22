<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\User;
use App\Traits\HasTableActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    use HasTableActions;

    public function __construct()
    {
        $this->middleware('permission:amenities-read')->only('index', 'filter');
        $this->middleware('permission:amenities-create')->only('store');
        $this->middleware('permission:amenities-update')->only('update', 'status');
        $this->middleware('permission:amenities-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $amenities = Amenity::with('landlord:id,name')->latest()->paginate(10);
        $landlords = User::whereRole('landlord')->latest()->get();

        return view('admin.amenities.index', compact('amenities', 'landlords'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'landlord_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:0,1',
        ]);

        Amenity::create($request->all());

        return response()->json([
            'message' => 'Amenity Created Successfully',
            'redirect' => route('admin.amenities.index'),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'landlord_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:0,1',
        ]);

        $amenity = Amenity::findOrFail($id);

        $amenity->update($request->all());

        return response()->json([
            'message' => 'Amenity Updated Successfully',
            'redirect' => route('admin.amenities.index'),
        ]);
    }

    public function destroy(string $id)
    {
        Amenity::where('id', $id)->delete();

        return response()->json([
            'message' => 'Amenity Deleted Successfully',
            'redirect' => route('admin.amenities.index'),
        ]);
    }

    protected function model(): string
    {
        return Amenity::class;
    }

    protected function query(Request $request): Builder
    {
        return Amenity::with('landlord:id,name')
            ->when($request->input('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                    $q->orWhereHas('landlord', function ($landlordQuery) use ($search) {
                        $landlordQuery->where('name', 'like', '%'.$search.'%');
                    });
                });
            })
            ->latest();
    }

    protected function view(): string
    {
        return 'admin.amenities.datas';
    }

    protected function viewData(LengthAwarePaginator $rows): array
    {
        return ['amenities' => $rows];
    }

    protected function redirectRoute(): string
    {
        return 'admin.amenities.index';
    }

    protected function statusMessage(): string
    {
        return 'Amenity';
    }

    protected function deleteMessage(): string
    {
        return 'Selected Amenity Deleted Successfully';
    }
}
