<?php

namespace App\Exports;

use App\Models\Labor;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LandlordLaborExport implements FromView
{
    public function view(): View
    {
        $labors = Labor::with('landlord')->where('landlord_id', auth()->id())->latest()->get();
        return view('landlord.labors.excel-csv', [
            'labors' => $labors
        ]);
    }
}
