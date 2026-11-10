<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Facility;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcnooFacilityController extends Controller
{
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

    public function acnooFilter(Request $request)
    {
        $facilities = Facility::with('landlord:id,name')
          ->when(request('search'), function ($q) {
              $search = request('search');
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                    $q->orWhereHas('landlord', function($landlordQuery) use ($search) {
                        $landlordQuery->where('name', 'like', '%'. $search. '%');
                    });
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.facilities.datas', compact('facilities'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function store(Request $request)
    {
        $request->validate([
            'landlord_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:0,1'
        ]);

        Facility::create($request->all());

        return response()->json([
            'message' => 'Facility Created Successfully',
            'redirect' => route('admin.facilities.index')
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'landlord_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:0,1'
        ]);

        $facility = Facility::findOrFail($id);

        $facility->update($request->all());

        return response()->json([
            'message' => 'Facility Updated Successfully',
            'redirect' => route('admin.facilities.index')
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
            'redirect' => route('admin.facilities.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        Facility::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Selected Facility Deleted Successfully',
            'redirect' => route('admin.facilities.index')
        ]);
    }
}
