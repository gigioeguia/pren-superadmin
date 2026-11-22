<?php

namespace App\Services;

use App\Models\Rent;
use App\Models\RentPayment;
use Carbon\Carbon;

class LateFeeService
{
    public function applyLateFee(RentPayment $payment)
    {
        $rent = $payment->rent()->first();
        $property = ($rent instanceof Rent) ? $rent->property()->first() : null;

        if (! $property || (float) $payment->getAttribute('late_fee') > 0 || $payment->getAttribute('payment_status') === 'paid') {
            return; // Skip if not applicable
        }

        // due date: 1th of each rent month
        if (! $payment->getAttribute('rent_year') || ! $payment->getAttribute('rent_month')) {
            return;
        }

        $monthNumber = Carbon::parse($payment->getAttribute('rent_month'))->month;
        $dueDate = Carbon::create((int) $payment->getAttribute('rent_year'), $monthNumber, 1);
        $graceDate = $dueDate->addDays((int) $property->getAttribute('late_fee_after_days'));

        if (Carbon::now()->greaterThan($graceDate)) {
            $lateFee = $property->getAttribute('late_fee');

            $payment->update([
                'late_fee' => $lateFee ?? 0,
                'total_amount' => $payment->total_amount + $lateFee,
            ]);
        }
    }
}
