<?php

namespace App\Http\Controllers\Landlord;

use App\Exports\LandlordMaintenanceExport;
use App\Models\Labor;
use App\Models\Property;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class AcnooMaintenanceController extends Controller
{
    public function index()
    {
        $property = Property::where('landlord_id', auth()->id())->get('id');
        $maintenances = Maintenance::with(['tenant:id,name,phone', 'labor:id,name', 'property', 'property.propertyDetail:property_id,property_info'])
            ->whereIn('property_id', $property)
            ->latest()->paginate(10);

        return view('landlord.maintenance.index', compact('maintenances'));
    }

    public function acnooFilter(Request $request)
    {
        $property = Property::where('landlord_id', auth()->id())->get('id');

        $maintenances = Maintenance::with(['tenant:id,name,phone', 'labor:id,name', 'property'])->whereIn('property_id', $property)
            ->when(request('search'), function ($q) {
                $q->where(function ($q) {
                    $search = request('search');
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orwhere('description', 'like', '%' . $search . '%')
                        ->orwhere('notes', 'like', '%' . $search . '%')
                        ->orwhere('comments', 'like', '%' . $search . '%')
                        ->orWhereHas('tenant', function ($query) use ($search) {
                            $query->where('name', 'like', '%' . $search . '%')
                                ->orWhere('phone', 'like', '%' . $search . '%');;
                        })

                        ->orWhereHas('labor', function ($query) use ($search) {
                            $query->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('property', function ($query) use ($search) {
                            $query->where('building_name', 'like', '%' . $search . '%');
                        })

                    ;
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.maintenance.datas', compact('maintenances'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function show(string $id)
    {
        $labors = Labor::latest()->get();
        $maintenance = Maintenance::with(['tenant:id,name,phone', 'property:id,building_name,address_info'])->findOrFail($id);
        return view('landlord.maintenance.view', compact('maintenance', 'labors'));
    }

    public function rejected(Request $request, string $id)
    {

        $request->validate([
            'notes' => 'nullable|string'
        ]);

        $maintenance = Maintenance::findOrFail($id);

        $maintenance->update([
            'status' => 'rejected',
            'notes' => $request->notes
        ]);

        return response()->json([
            'message' => 'Maintenance rejected successfully',
            'redirect' => route('landlord.maintenances.index')
        ]);
    }

    public function processing(Request $request, string $id)
    {

        $request->validate([
            'labor_id' => 'required|integer|exists:labors,id',
            'total_costing' => 'required|numeric',
            'comments' => 'nullable',
        ]);

        $maintenance = Maintenance::findOrFail($id);

        $maintenance->update([
            'labor_id' => $request->labor_id,
            'total_costing' => $request->total_costing,
            'comments' => $request->comments,
            'status' => 'processing',
        ]);

        return response()->json([
            'message' => 'Maintenance processing successfully',
            'redirect' => route('landlord.maintenances.show', $maintenance->id)
        ]);
    }

    public function completed(Request $request, string $id)
    {
        $request->validate([
            'comments' => 'required',
        ]);

        $maintenance = Maintenance::findOrFail($id);

        $maintenance->update([
            'comments' => $request->comments,
            'status' => 'completed',
        ]);

        return response()->json([
            'message' => 'Maintenance Completed successfully',
            'redirect' => route('landlord.maintenances.show', $maintenance->id)
        ]);
    }

    public function exportExcel()
    {
        return Excel::download(new LandlordMaintenanceExport, 'landlord-maintenances.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new LandlordMaintenanceExport, 'landlord-maintenances.csv');
    }
}
