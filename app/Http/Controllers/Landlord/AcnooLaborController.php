<?php

namespace App\Http\Controllers\Landlord;

use App\Exports\LandlordLaborExport;
use App\Models\Labor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class AcnooLaborController extends Controller
{
    public function index()
    {
        $labors = Labor::with('landlord')->where('landlord_id', auth()->id())->latest()->paginate(10);
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);

        return view('landlord.labors.index', compact('labors', 'phone_codes'));
    }

    public function acnooFilter(Request $request)
    {
        $labors = Labor::with('landlord')
            ->where('landlord_id', auth()->id())
            ->when(request('search'), function ($q) {
                $q->where(function ($q) {
                    $search = request('search');
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orwhere('email', 'like', '%' . $search . '%')
                        ->orwhere('phone', 'like', '%' . $search . '%')
                        ->orwhere('gender', 'like', '%' . $search . '%')
                        ->orwhere('salary', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.labors.datas', compact('labors'))->render()
            ]);
        }

        return redirect(url()->previous());
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:labors,email',
            'phone' => 'required',
            'salary' => 'required|numeric|min:0',
            'gender' => 'nullable|in:Male,Female,other',
        ]);

       $labor = Labor::create($request->except('landlord_id', 'phone') + [
            'landlord_id' => auth()->id(),
            'phone' => $request->phone
        ]);

        sendNotification($labor->landlord->id, notify_users([$labor->landlord->id]), landlord_url: route('landlord.labors.index', ['id' => $labor->id]),  landlord_msg: __('You have created a new labor.'));

        return response()->json([
            'message' => 'Labor save successfully',
            'redirect' => route('landlord.labors.index')
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:labors,email,' . $id,
            'phone' => 'required',
            'salary' => 'required|numeric|min:0',
            'gender' => 'nullable|in:Male,Female,other',
        ]);

        $labor = Labor::findOrFail($id);

        $labor->update($request->except('landlord_id', 'phone') + [
            'landlord_id' => auth()->id(),
            'phone' => $request->phone
        ]);

        return response()->json([
            'message' => 'Labor update successfully',
            'redirect' => route('landlord.labors.index')
        ]);
    }

    public function destroy(string $id)
    {
        $labor = Labor::findOrFail($id);
        $labor->delete();
        return response()->json([
            'message'   => __('Labor deleted successfully'),
            'redirect'  => route('landlord.labors.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        Labor::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message'   => __('Selected labor deleted successfully'),
            'redirect'  => route('landlord.labors.index')
        ]);
    }

    public function exportExcel()
    {
        return Excel::download(new LandlordLaborExport, 'landlord-labors.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new LandlordLaborExport, 'landlord-labors.csv');
    }
}
