<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\SecurityDeposit;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Exports\AdminSecurityDepositExport;

class AcnooSecurityDepositController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:security-deposit-read')->only('index');
        $this->middleware('permission:security-deposit-update')->only('approve', 'rejected');
    }

    public function index()
    {
        $security_deposits = SecurityDeposit::with([
            'tenant:id,name',
            'property:id',
            'property.propertyDetail:id,property_id,property_info',
            'gateway:id,name',
            'rent:id,tenant_id,property_id',
            'rent.tenant:id,name',
            'rent.property:id,puid',
            'rent.property.propertyDetail:id,property_id,property_info',
        ])
            ->latest()
            ->paginate(10);

        return view('admin.security-deposits.index', compact('security_deposits'));
    }

    public function acnooFilter(Request $request)
    {
        $security_deposits = SecurityDeposit::with([
            'tenant:id,name',
            'property:id',
            'property.propertyDetail:id,property_id,property_info',
            'gateway:id,name',
            'rent:id,tenant_id,property_id',
            'rent.tenant:id,name',
            'rent.property:id,puid',
            'rent.property.propertyDetail:id,property_id,property_info',
        ])->when(request('search'), function ($q) {
            $q->where('invoice_no', 'like', '%' . request('search') . '%')
                ->orWhere('status', 'like', '%' . request('search') . '%')
                ->orWhere('total_amount', 'like', '%' . request('search') . '%')
                ->orWhere('utility_advance', 'like', '%' . request('search') . '%');

            $q->orWhereHas('tenant', function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%');
            });

            $q->orWhereHas('gateway', function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%');
            });

            $q->orWhereHas('property.propertyDetail', function ($q) {
                $q->where('property_info', 'like', '%' . request('search') . '%');
            });
        })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if (request()->ajax()) {
            return response()->json([
                'data' => view('admin.security-deposits.datas', compact('security_deposits'))->render()
            ]);
        }
        return redirect(url()->previous());
    }

    public function file_download(string $id)
    {
        $security_deposit = SecurityDeposit::findOrFail($id);

        // Check if attachment exists in notes
        $filePath = $security_deposit->payment_data['payment_receipt'] ?? null;

        if (empty($filePath) || !Storage::exists($filePath)) {
            return redirect()->back()->with('error', 'File not found');
        }

        return Storage::download($filePath);
    }

    public function approve(string $id)
    {
        $security_deposit = SecurityDeposit::findOrFail($id);
        $landlord = User::where('id', $security_deposit->landlord_id)->first();

        if ($security_deposit->status == 'pending') {
            DB::beginTransaction();
            try {
                Transaction::create([
                    'paid_by' => auth()->id(),
                    'received_by' => $landlord->id,
                    'gateway_id' => $security_deposit->gateway_id,
                    'security_deposit_id' => $security_deposit->id,
                    'date' => today(),
                    'type' => 'debit',
                    'payment_type' => 'security_deposit',
                    'amount' => $security_deposit->landlord_balance ?? 0,
                    'charge' => $security_deposit->charge ?? 0,
                    'notes' => $security_deposit->payment_data['payment_note'] ?? '',
                    'file' => $security_deposit->payment_data['payment_receipt'] ?? '',
                ]);

                $landlord->update([
                    'balance' => $landlord->balance + $security_deposit->landlord_balance,
                ]);

                $security_deposit->update([
                    'status' => 'paid'
                ]);

                DB::commit();

                sendNotification(
                    $security_deposit->id,
                    notify_users([$security_deposit->landlord->id, $security_deposit->tenant->id]),
                    admin_url:route('admin.security-deposits.index', ['id' => $security_deposit->id]),
                    landlord_url: route('landlord.security-deposits.index', ['id' => $security_deposit->id]),
                    admin_msg: __('A security deposit has been approved.'),
                    landlord_msg: __('The tenant’s security deposit has been approved.'),
                    tenant_msg: __('Your security deposit has been approved successfully.')
                );

                return response()->json([
                    'message' => 'Security deposit approved',
                    'redirect' => route('admin.security-deposits.index')
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    'message' => $th->getMessage(),
                ]);
            }
        } else {
            return response()->json([
                'message' => 'The security deposit is still unpaid and cannot be approved at this time.',
                'redirect' => route('admin.security-deposits.index')
            ], 400);
        }
    }

    public function rejected(Request $request, string $id)
    {
        $security_deposit = SecurityDeposit::findOrFail($id);
        $security_deposit->update([
            'note' => $request->note,
            'status' => 'rejected'
        ]);

        sendNotification($security_deposit->id, notify_users([$security_deposit->landlord->id, $security_deposit->tenant->id]), admin_url: route('admin.security-deposits.index', ['id' => $security_deposit->id]), landlord_url: route('landlord.security-deposits.index', ['id' => $security_deposit->id]), admin_msg: __('A security deposit has been rejected.'), landlord_msg: __('The tenant’s security deposit has been rejected.'), tenant_msg: __('Your security deposit has been rejected.'));

        return response()->json([
            'message' => 'Security deposit Rejected',
            'redirect' => route('admin.security-deposits.index')
        ]);
    }

    public function exportExcel()
    {
        return Excel::download(new AdminSecurityDepositExport, 'security-deposits.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new AdminSecurityDepositExport, 'security-deposits.csv');
    }
}
