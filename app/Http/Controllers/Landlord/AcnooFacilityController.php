<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;

class AcnooFacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::where('landlord_id', auth()->id())->latest()->paginate(10);
        return view('landlord.facilities.index', compact('facilities'));
    }

    public function acnooFilter(Request $request)
    {
        $facilities = Facility::where('landlord_id', auth()->id())->when(request('search'), function ($q) {
            $q->where(function ($q) {
                $search = request('search');
                $q->where('name', 'like', '%' . $search . '%');
            });
        })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.facilities.datas', compact('facilities'))->render()
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

        Facility::create($request->except('landlord_id') + [
            'landlord_id' => auth()->id()
        ]);

        return response()->json([
            'message' => 'Facility Created Successfully',
            'redirect' => route('landlord.facilities.index')
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:0,1'
        ]);

        $facility = Facility::findOrFail($id);

        $facility->update($request->except('landlord_id'));

        return response()->json([
            'message' => 'Facility Updated Successfully',
            'redirect' => route('landlord.facilities.index')
        ]);
    }

    public function status(Request $request, string $id)
    {
        $facility = Facility::findOrFail($id);
        $facility->update(['status' => $request->status]);
        return response()->json([
            'message' => 'Facility',
        ]);
    }

    public function destroy(string $id)
    {
        Facility::where('id', $id)->delete();

        return response()->json([
            'message' => 'Facility Deleted Successfully',
            'redirect' => route('landlord.facilities.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        Facility::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Selected Facility Deleted Successfully',
            'redirect' => route('landlord.facilities.index')
        ]);
    }
}
