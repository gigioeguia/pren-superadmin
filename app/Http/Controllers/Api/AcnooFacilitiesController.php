<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;

class AcnooFacilitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Facility::where('landlord_id', auth()->id())
            ->where('status', 1)
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%');
            })
            ->latest();
        if ($request->has('no_paginate') && $request->no_paginate == true) {
            $facilities = $query->select('id', 'name')->get();
            $responseData = [
                'data' => $facilities,
            ];
        } else {
            $facilities = $query->paginate($request->per_page ?? 10);
            $responseData = $facilities;
        }

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $responseData,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'status' => 'nullable|in:0,1',
        ]);

        $data = Facility::create($request->except('landlord_id') + [
            'landlord_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Data store successfully',
            'data' => $data,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string',
            'status' => 'nullable|in:0,1',
        ]);

        $facility = Facility::findOrFail($id);

        $facility->update($request->except('landlord_id') + [
            'landlord_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Data update successfully',
            'data' => $facility,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Facility::where('id', $id)->delete();

        return response()->json([
            'message' => 'Data deleted successfully',
        ]);
    }
}
