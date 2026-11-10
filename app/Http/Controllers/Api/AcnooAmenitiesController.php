<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;

class AcnooAmenitiesController extends Controller
{
    public function index(Request $request)
    {
        $query = Amenity::where('landlord_id', auth()->id())
                        ->where('status', 1)
                        ->when($request->search, function($q) use ($request) {
                            $q->where('name', 'like', '%'. $request->search. '%');
                        })
                        ->latest();
        if ($request->has('no_paginate') && $request->no_paginate == true) {
            $amenities = $query->select('id', 'name')->get();
            $responseData = [
                'data' => $amenities,
            ];
        } else {
            $amenities = $query->paginate($request->per_page ?? 10);
            $responseData = $amenities;
        }

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $responseData
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'status' => 'nullable|in:0,1',
        ]);

        $data = Amenity::create($request->except('landlord_id') + [
            'landlord_id' => auth()->id()
        ]);

        return response()->json([
            'message' => 'Data store successfully',
            'data' => $data
        ]);
    }


    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string',
            'status' => 'nullable|in:0,1',
        ]);

        $amenity = Amenity::findOrFail($id);

        $amenity->update($request->except('landlord_id') + [
            'landlord_id' => auth()->id()
        ]);

        return response()->json([
            'message' => 'Data update successfully',
            'data' => $amenity
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Amenity::where('id', $id)->delete();

        return response()->json([
            'message' => 'Data deleted successfully',
        ]);
    }
}
