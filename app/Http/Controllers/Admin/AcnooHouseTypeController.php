<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HouseType;
use Illuminate\Http\Request;

class AcnooHouseTypeController extends Controller
{
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

    public function acnooFilter(Request $request)
    {
        $house_types = HouseType::when(request('search'), function ($q) {
                $q->where(function ($q) {
                    $search = request('search');
                    $q->where('name', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.house-types.datas', compact('house_types'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        HouseType::create($request->all());

        return response()->json([
            'message' => 'House Types Created successfully',
            'redirect' => route('admin.house-types.index')
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $reason = HouseType::findOrFail($id);

        $reason->update($request->all());

        return response()->json([
            'message' => 'House Types Updated successfully',
            'redirect' => route('admin.house-types.index')
        ]);
    }

    public function status(Request $request, string $id)
    {
        $reason = HouseType::findOrFail($id);
        $reason->update(['status' => $request->status]);
        return response()->json([
            'message' => 'House Type',
        ]);
    }

    public function destroy(string $id)
    {
        $reason = HouseType::findOrFail($id);
        $reason->delete();

        return response()->json([
            'message' => 'House Types Deleted Successfully',
            'redirect' => route('admin.house-types.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        HouseType::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Selected House Types Deleted Successfully',
            'redirect' => route('admin.house-types.index')
        ]);
    }
}
