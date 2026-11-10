<?php

namespace App\Console\Commands;

use App\Models\RentPayment;
use Illuminate\Console\Command;
use App\Services\AcnooLateFeeService;

class ApplyLateFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rent:apply-late-fee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply late fees to all unpaid rent payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $unpaidPayments = RentPayment::where('payment_status', 'unpaid')->get();
        $service = new AcnooLateFeeService();

        foreach ($unpaidPayments as $payment) {
            $service->applyLateFee($payment);
        }

        $this->info('Late fees applied successfully.');
    }
}
