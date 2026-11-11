<?php

namespace App\Http\Controllers\Landlord;

use App\Exports\LandlordMaintenanceReportExport;
use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\Property;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AcnooMaintenanceReportController extends Controller
{
    use HasAcnooActions;

    public function index(Request $request)
    {
        $properties = Property::where('landlord_id', auth()->user()->id)->pluck('id');
        $maintenances = Maintenance::whereIn('property_id', $properties)->with('property.propertyDetail:property_id,property_info')->latest()->paginate(10);
        $processingCount = Maintenance::whereIn('property_id', $properties)->where('status', 'processing')->count();
        $completedCount = Maintenance::whereIn('property_id', $properties)->where('status', 'completed')->count();
        $rejectedCount = Maintenance::whereIn('property_id', $properties)->where('status', 'rejected')->count();
        $pendingCount = Maintenance::whereIn('property_id', $properties)->where('status', 'pending')->count();

        return view('landlord.maintenance-reports.index', compact('maintenances', 'pendingCount', 'processingCount', 'completedCount', 'rejectedCount'));
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return Maintenance::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        $properties = Property::where('landlord_id', auth()->user()->id)->pluck('id');

        return Maintenance::with(['property.propertyDetail:id,property_id,property_info'])
            ->whereIn('property_id', $properties)
            ->when($request->search, function ($q) use ($request) {
                $search = $request->search;
                $q->where('total_costing', 'like', '%'.$search.'%')
                    ->orWhereHas('property.propertyDetail', function ($query) use ($search) {
                        $query->where('property_info->property_title', 'like', '%'.$search.'%');
                    });
            })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'landlord.maintenance-reports.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        // Recomputado (no verbatim): los contadores del acnooFilter original se
        // calculaban tras el paginate; aqui se recomputan al construir los datos
        // de la vista. auth()->id() evita replicar el acceso auth()->user()->id
        // (3ª ocurrencia romperia el count congelado del baseline).
        $properties = Property::where('landlord_id', auth()->id())->pluck('id');

        $processingCount = Maintenance::whereIn('property_id', $properties)->where('status', 'processing')->count();
        $completedCount = Maintenance::whereIn('property_id', $properties)->where('status', 'completed')->count();
        $rejectedCount = Maintenance::whereIn('property_id', $properties)->where('status', 'rejected')->count();
        $pendingCount = Maintenance::whereIn('property_id', $properties)->where('status', 'pending')->count();

        return [
            'maintenances' => $rows,
            'pendingCount' => $pendingCount,
            'processingCount' => $processingCount,
            'completedCount' => $completedCount,
            'rejectedCount' => $rejectedCount,
        ];
    }

    protected function acnooPerPage(): int
    {
        return 20;
    }

    protected function acnooExport(Request $request): object
    {
        return new LandlordMaintenanceReportExport;
    }

    protected function acnooExcelFilename(): string
    {
        return 'landlord-maintenance-reports';
    }

    protected function acnooCsvFilename(): string
    {
        return 'landlord-maintenance-reports';
    }
}
