<?php

namespace App\Services;

use App\Models\RentPayment;
use Carbon\Carbon;

class AcnooLateFeeService
{
    public function applyLateFee(RentPayment $payment)
    {
        $rent = $payment->rent;
        $property = $rent->property;

        if (! $property || $payment->late_fee > 0 || $payment->payment_status === 'paid') {
            return; // Skip if not applicable
        }

        // due date: 1th of each rent month
        $monthNumber = Carbon::parse($payment->rent_month)->month;
        $dueDate = Carbon::create($payment->rent_year, $monthNumber, 1);
        $graceDate = $dueDate->addDays($property->late_fee_after_days);

        if (Carbon::now()->greaterThan($graceDate)) {
            $lateFee = $property->late_fee;

            $payment->update([
                'late_fee' => $lateFee ?? 0,
                'total_amount' => $payment->total_amount + $lateFee,
            ]);
        }
    }
}
