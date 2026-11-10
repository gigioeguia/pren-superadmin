<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Rent;
use App\Models\Option;
use App\Models\RentPayment;
use Illuminate\Console\Command;

class RentPaymentGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rent:payment-generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate monthly rent payments for active and valid expired rents';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = strtolower(Carbon::now()->format('F')); 
        $year = Carbon::now()->year;

        $this->info("Generating rent payments for {$month} {$year}...");

        $rent_payment_exists = RentPayment::where('rent_month', $month)
            ->where('rent_year', $year)
            ->exists();

        if ($rent_payment_exists) {
            RentPayment::where('rent_month', $month)
                ->where('rent_year', $year)
                ->where('payment_status', 'unpaid')
                ->delete();
        }

        $paid_rents = RentPayment::where('rent_month', $month)
            ->where('rent_year', $year)
            ->where('payment_status', 'paid')
            ->pluck('rent_id');

        $charge = optional(Option::where('key', 'service-charge')->first())->value['amount'] ?? 0;
        $invoice_no = RentPayment::max('id') ?? 0;

        $rents = Rent::whereNotIn('id', $paid_rents)
            ->where(function ($q) {
                $q->where('status', 'active')
                  ->orWhere(function ($q) {
                      $q->where('status', 'expired')
                        ->whereDate('start_date', '<=', Carbon::today())
                        ->whereDate('end_date', '>=', Carbon::today());
                  });
            })
            ->get();

        if ($rents->isEmpty()) {
            $this->info('No rents found for payment generation.');
            return;
        }

        $data = [];
        foreach ($rents as $key => $rent) {
            $data[$key] = [
                'rent_id' => $rent->id,
                'monthly_rent' => $rent->monthly_rent ?? 0,
                'utility_bill' => $rent->utility_bill ?? 0,
                'charge' => $charge ?? 0,
                'subtotal_amount' => $rent->total_amount,
                'total_amount' => $rent->total_amount - $charge ?? 0,
                'rent_month' => $month,
                'rent_year' => $year,
                'payment_status' => 'unpaid',
                'invoice_no' => "PAYSLIP" . str_pad($key + $invoice_no + 1, 2, '0', STR_PAD_LEFT),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        RentPayment::insert($data);

        $this->info('Rent payments generated successfully!');
    }

}

