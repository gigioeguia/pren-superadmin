<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\User;
use Illuminate\Http\Request;

class AcnooAmenityController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:amenities-read')->only('index');
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

    public function acnooFilter(Request $request)
    {
        $amenities = Amenity::with('landlord:id,name')
            ->when(request('search'), function ($q) {
                $search = request('search');
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                    $q->orWhereHas('landlord', function ($landlordQuery) use ($search) {
                        $landlordQuery->where('name', 'like', '%'.$search.'%');
                    });
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.amenities.datas', compact('amenities'))->render(),
            ]);
        }

        return redirect(url()->previous());
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

    public function status(Request $request, string $id)
    {
        $amenity = Amenity::findOrFail($id);
        $amenity->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Amenity',
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

    public function deleteAll(Request $request)
    {
        Amenity::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Selected Amenity Deleted Successfully',
            'redirect' => route('admin.amenities.index'),
        ]);
    }
}
