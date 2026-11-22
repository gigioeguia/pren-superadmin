<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Traits\HasTableActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    use HasTableActions;

    public function index()
    {
        $amenities = Amenity::where('landlord_id', auth()->id())->latest()->paginate(10);

        return view('landlord.amenities.index', compact('amenities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:0,1',
        ]);

        Amenity::create($request->except('landlord_id') + [
            'landlord_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Amenity Created Successfully',
            'redirect' => route('landlord.amenities.index'),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:0,1',
        ]);

        $amenity = Amenity::findOrFail($id);

        $amenity->update($request->except('landlord_id'));

        return response()->json([
            'message' => 'Amenity Updated Successfully',
            'redirect' => route('landlord.amenities.index'),
        ]);
    }

    public function destroy(string $id)
    {
        Amenity::where('id', $id)->delete();

        return response()->json([
            'message' => 'Amenity Deleted Successfully',
            'redirect' => route('landlord.amenities.index'),
        ]);
    }

    protected function model(): string
    {
        return Amenity::class;
    }

    protected function query(Request $request): Builder
    {
        return Amenity::where('landlord_id', auth()->id())->when($request->input('search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $search = $request->input('search');
                $q->where('name', 'like', '%'.$search.'%');
            });
        })
            ->latest();
    }

    protected function view(): string
    {
        return 'landlord.amenities.datas';
    }

    protected function viewData(LengthAwarePaginator $rows): array
    {
        return ['amenities' => $rows];
    }

    protected function redirectRoute(): string
    {
        return 'landlord.amenities.index';
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
