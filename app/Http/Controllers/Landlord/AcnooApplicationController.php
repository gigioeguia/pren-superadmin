<?php

namespace App\Http\Controllers\Landlord;

use App\Exports\LandlordApplicationExport;
use App\Models\Property;
use App\Models\Application;
use App\Helpers\HasUploader;
use Illuminate\Http\Request;
use App\Models\SecurityDeposit;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class AcnooApplicationController extends Controller
{
    use HasUploader;

    public function index()
    {
        $property = Property::where('landlord_id', auth()->id())->get('id');
        $applications = Application::with(['tenant:id,name', 'property:id,puid', 'property.propertyDetail:property_id,property_info', 'unit:id,unit_name'])->whereIn('property_id', $property)->latest()->paginate(10);
        return view('landlord.application.index', compact('applications'));
    }

    public function acnooFilter(Request $request)
    {
        $property = Property::where('landlord_id', auth()->id())->get('id');

        $applications = Application::with(['tenant:id,name', 'property:id,puid', 'property.propertyDetail:property_id,property_info', 'unit:id,unit_name'])
            ->whereIn('property_id', $property)
            ->when(request('search'), function ($q) {
                $q->where(function ($q) {
                    $search = request('search');
                    $q->whereHas('tenant', function ($query) use ($search) {
                            $query->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('property', function ($query) use ($search) {
                            $query->where('puid', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('unit', function ($query) use ($search) {
                            $query->where('unit_name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('property.propertyDetail', function ($query) use ($search) {
                            $query->where('property_info->property_title', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.application.datas', compact('applications'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function show(string $id)
    {
        $application = Application::with(['tenant',
        'tenant.user_details',
        'property:id,puid,category_id,address_info,rent_info,utility_deposit,security_deposit,late_fee,late_fee_after_days,landlord_id',
        'property.propertyDetail:property_id,property_type,property_info',
        'property.category:id,name',
        'property.landlord:id,name,phone,email',
        'property.rent:property_id,id',
        'unit:id,monthly_rent,utility_deposit,security_deposit,late_fee,late_fee_after_days'])->findOrFail($id);

        return view('landlord.application.view', compact('application'));
    }

    public function processing(Request $request, string $id)
    {
        $application_process = Application::findOrFail($id);

        if ($application_process) {

            $request->validate([
                'landlord_agreement' => 'required|file',
            ]);

            $application_process->update([
                'landlord_agreement' => $request->landlord_agreement ? $this->upload($request, 'landlord_agreement') : NULL,
                'status' => 1,
            ]);

            return response()->json([
                'message' => 'Application processes successfully',
                'redirect' =>  route('landlord.tenant-applications.show', $application_process->id)
            ]);
        } else {
            return response()->json(['message' => 'Application not found'], 404);
        }
    }

    public function approved(string $id)
    {
        $approve_application = Application::findOrFail($id);
        $security_deposit = SecurityDeposit::where('property_id', $approve_application->property_id)
                                            ->where('unit_id', $approve_application->unit_id)
                                            ->first();

        if (!empty($approve_application->tenant_agreement) && Storage::exists($approve_application->tenant_agreement)) {
            if ($security_deposit->status == 'paid') {
                $approve_application->update([
                    'status' => 2,
                ]);

                $security_deposit->rent()->update([
                    'status' => 'active'
                ]);

                return response()->json([
                    'message' => 'Application approved successfully',
                    'redirect' =>  route('landlord.tenant-applications.show', $approve_application->id)
                ]);
            } else {
                return response()->json(['message' => 'Application Unpaid'], 404);
            }
        } else {
            return response()->json([
                'message' => 'Tenant agreement file does not exist',
            ], 404);
        }
    }

    public function rejected(Request $request, string $id)
    {
        $request->validate([
            'rejected_cause' => 'required|string|max:255',
        ]);

        $application_reject = Application::findOrFail($id);
        if (empty($application_reject->tenant_agreement) || !Storage::exists($application_reject->tenant_agreement)) {
            if ($application_reject) {
                $application_reject->update([
                    'rejected_cause' => $request->rejected_cause,
                    'status' => 3
                ]);

                return response()->json([
                    'message' => 'Application rejected successfully',
                    'redirect' =>  route('landlord.tenant-applications.show', $application_reject->id)
                ]);
            } else {
                return response()->json(['message' => 'Application not found'], 404);
            }
        } else {
            return response()->json([
                'message' => 'Application cannot be rejected because a tenant agreement already exists.',
            ]);
        }
    }

    public function file_download(string $id, string $type)
    {
        $application = Application::findOrFail($id);

        $filePath = $type === 'landlord' ? $application->landlord_agreement : $application->tenant_agreement;

        if (is_null($application->landlord_agreement)) {
            return response()->json([
                'message' => 'File not found',
            ]);
        }

        $filePath = $application->landlord_agreement;

        if (Storage::exists($filePath)) {
            return Storage::download($filePath);
        }
    }

    public function exportExcel()
    {
        return Excel::download(new LandlordApplicationExport, 'landlord-applications.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new LandlordApplicationExport, 'landlord-applications.csv');
    }
}
