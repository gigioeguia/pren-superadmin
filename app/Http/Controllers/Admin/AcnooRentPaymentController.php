<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\RentPayment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdminRentPaymentExport;
use Illuminate\Support\Facades\Storage;

class AcnooRentPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:rent-payment-read')->only('index');
        $this->middleware('permission:rent-payment-update')->only('approve', 'rejected');
    }

    public function index()
    {
        $rent_payments = RentPayment::with([
            'rent:id,tenant_id,property_id',
            'rent.tenant:id,name',
            'rent.property:id,puid',
            'rent.property.propertyDetail:id,property_id,property_info',
            'gateway:id,name'
        ])
            ->latest()
            ->paginate(10);
        return view('admin.rent-payment.index', compact('rent_payments'));
    }


    public function acnooFilter(Request $request)
    {
        $rent_payments = RentPayment::with(['rent:id,tenant_id,property_id', 'rent.tenant:id,name', 'rent.property:id,puid', 'rent.property.propertyDetail:id,property_id,property_info', 'gateway:id,name'])
            ->when(request('search'), function ($q) {
                $q->where('invoice_no', 'like', '%' . request('search') . '%')
                    ->orWhere('monthly_rent', 'like', '%' . request('search') . '%')
                    ->orWhere('utility_bill', 'like', '%' . request('search') . '%')
                    ->orWhere('payment_status', 'like', '%' . request('search') . '%');

                $q->orWhereHas('rent.tenant', function ($q) {
                    $q->where('name', 'like', '%' . request('search') . '%');
                });

                $q->orWhereHas('gateway', function ($q) {
                    $q->where('name', 'like', '%' . request('search') . '%');
                });

                $q->orWhereHas('rent.property.propertyDetail', function ($q) {
                    $q->where('property_info', 'like', '%' . request('search') . '%');
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if (request()->ajax()) {
            return response()->json([
                'data' => view('admin.rent-payment.datas', compact('rent_payments'))->render()
            ]);
        }
        return redirect(url()->previous());
    }

    public function file_download(string $id)
    {
        $rent_payment = RentPayment::findOrFail($id);

        // Check if attachment exists in notes
        $filePath = $rent_payment->payment_data['payment_receipt'] ?? null;

        if (empty($filePath) || !Storage::exists($filePath)) {
            return redirect()->back()->with('error', 'File not found');
        }

        return Storage::download($filePath);
    }

    public function approve(string $id)
    {
        $rent_payment = RentPayment::findOrFail($id);
        $landlord = User::where('id', $rent_payment->rent->landlord_id ?? '')->first();

        DB::beginTransaction();
        try {
            Transaction::create([
                'paid_by' => auth()->user()->id,
                'received_by' => $landlord->id,
                'gateway_id' => $rent_payment->gateway_id,
                'rent_payment_id' => $rent_payment->id,
                'date' => today(),
                'type' => 'debit',
                'payment_type' => 'rent_payment',
                'amount' => $rent_payment->subtotal_amount ?? 0,
                'charge' => $rent_payment->charge ?? 0,
                'notes' => $rent_payment->payment_data['payment_note'] ?? '',
                'file' => $rent_payment->payment_data['payment_receipt'] ?? '',
            ]);

            $landlord->update([
                'balance' => $landlord->balance + $rent_payment->total_amount
            ]);

            $rent_payment->update([
                'payment_status' => 'paid'
            ]);

            DB::commit();

            sendNotification(
                $rent_payment->id,
                notify_users([$landlord->id, $rent_payment->rent->tenant->id]),
                admin_url: route('admin.rent-payments.index', ['id' => $rent_payment->id]),
                landlord_url: route('landlord.rent-payments.index', ['id' => $rent_payment->id]),
                admin_msg: __('A rent payment has been approved.'),
                landlord_msg: __('A rent payment has been approved and credited to your account.'),
                tenant_msg: __('Your rent payment has been approved successfully.')
            );

            return response()->json([
                'message' => 'Rent payment approved',
                'redirect' => route('admin.rent-payments.index')
            ]);
        } catch (\Throwable $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function rejected(Request $request, string $id)
    {

        $rent_payment = RentPayment::findOrFail($id);
        $rent_payment->update([
            'note' => $request->note,
            'payment_status' => 'rejected'
        ]);

        sendNotification(
            $rent_payment->id,
            notify_users([$rent_payment->rent->landlord->id, $rent_payment->rent->tenant->id]),
            admin_url: route('admin.rent-payments.index', ['id' => $rent_payment->id]),
            landlord_url: route('landlord.rent-payments.index', ['id' => $rent_payment->id]),
            admin_msg: __('A rent payment has been rejected.'),
            landlord_msg: __('A rent payment has been rejected.'),
            tenant_msg: __('Your rent payment has been rejected.')
        );

        return response()->json([
            'message' => 'Rent payment request Rejected',
            'redirect' => route('admin.rent-payments.index')
        ]);
    }

    public function exportExcel()
    {
        return Excel::download(new AdminRentPaymentExport, 'rent-payments.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new AdminRentPaymentExport, 'rent-payments.csv');
    }
}
