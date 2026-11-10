<?php

namespace App\Exports;

use App\Models\PlanSubscribe;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AdminSubscriptionReportExport implements FromView
{
    public function view(): View
    {
        $subscriptions = PlanSubscribe::with(['user:id,name,will_expire', 'plan:id,subscriptionName', 'gateway:id,name'])->latest()->get();
        return view('admin.reports.subscriptions.excel-csv', [
            'subscriptions' => $subscriptions
        ]);
    }
}
