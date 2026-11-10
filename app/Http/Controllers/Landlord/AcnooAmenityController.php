<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;

class AcnooAmenityController extends Controller
{
    public function index()
    {
        $amenities = Amenity::where('landlord_id', auth()->id())->latest()->paginate(10);
        return view('landlord.amenities.index', compact('amenities'));
    }

    public function acnooFilter(Request $request)
    {
        $amenities = Amenity::where('landlord_id', auth()->id())->when(request('search'), function ($q) {
            $q->where(function ($q) {
                $search = request('search');
                $q->where('name', 'like', '%' . $search . '%');
            });
        })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.amenities.datas', compact('amenities'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:0,1'
        ]);

        Amenity::create($request->except('landlord_id') + [
            'landlord_id' => auth()->id()
        ]);

        return response()->json([
            'message' => 'Amenity Created Successfully',
            'redirect' => route('landlord.amenities.index')
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:0,1'
        ]);

        $amenity = Amenity::findOrFail($id);

        $amenity->update($request->except('landlord_id'));

        return response()->json([
            'message' => 'Amenity Updated Successfully',
            'redirect' => route('landlord.amenities.index')
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
            'redirect' => route('landlord.amenities.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        Amenity::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Selected Amenity Deleted Successfully',
            'redirect' => route('landlord.amenities.index')
        ]);
    }
}
