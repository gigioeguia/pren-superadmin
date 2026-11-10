<?php

namespace App\Http\Controllers\Admin;

use App\Models\Option;
use App\Models\Rent;
use App\Models\RentPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcnooRentPaymentGenerateController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:rent-payment-generate-read')->only('index');
        $this->middleware('permission:rent-payment-generate-create')->only('store');
    }

    public function index()
    {
        $rent_payments = RentPayment::with(['rent:id,landlord_id', 'rent.landlord:id,name'])->latest()->paginate(10);
        return view('admin.rent-payment-generate.index', compact('rent_payments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'month' => 'required|string',
            'year' => 'required|integer',
        ]);

        $rent_payment_exists = RentPayment::where('rent_month', $request->month)
            ->where('rent_year', $request->year)
            ->exists();

        if ($rent_payment_exists) {
            // Delete previous data for all unpaid salaries
            RentPayment::where('rent_month', $request->month)
                ->where('rent_year', $request->year)
                ->where('payment_status', 'unpaid')
                ->delete();
        }

        $paid_rents = RentPayment::where('rent_month', $request->month)->where('rent_year', $request->year)->where('payment_status', 'paid')->pluck('rent_id');
        $charge = optional(Option::where('key', 'service-charge')->first())->value['amount'] ?? 0;
        $invoice_no = RentPayment::max('id');

        $rents = Rent::whereNotIn('id', $paid_rents)
            ->when(true, function ($query) {
                $query->where(function ($q) {
                    $q->where('status', '!=', 'expired') // Keep non-expired rents
                        ->orWhere(function ($q) {
                            $q->where('status', 'expired') // For expired rents, add date comparison
                                ->whereDate('start_date', '<=', Carbon::today())
                                ->whereDate('end_date', '>=', Carbon::today());
                        });
                });
            })
            ->get();

        $data = [];
        foreach ($rents as $key => $rent) {
            $data[$key] = [
                'rent_id' => $rent->id,
                'monthly_rent' => $rent->monthly_rent ?? 0,
                'utility_bill' => $rent->utility_bill ?? 0,
                'charge' => $charge ?? 0,
                'subtotal_amount' => $rent->total_amount,
                'total_amount' => $rent->total_amount - $charge ?? 0,
                'rent_month' => $request->month,
                'rent_year' => $request->year,
                'payment_status' => 'unpaid',
                'invoice_no' => "PAYSLIP" . str_pad($key + $invoice_no + 1, 2, '0', STR_PAD_LEFT), // Use $key here
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        RentPayment::insert($data);

        return response()->json([
            'redirect' => route('admin.rent-payment-generate.index'),
            'message' => __('Rent payment generated successfully.'),
        ]);
    }
}
