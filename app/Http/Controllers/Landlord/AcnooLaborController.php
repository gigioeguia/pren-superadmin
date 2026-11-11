<?php

namespace App\Http\Controllers\Landlord;

use App\Exports\LandlordLaborExport;
use App\Http\Controllers\Controller;
use App\Models\Labor;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AcnooLaborController extends Controller
{
    use HasAcnooActions;

    public function index()
    {
        $labors = Labor::with('landlord')->where('landlord_id', auth()->id())->latest()->paginate(10);
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);

        return view('landlord.labors.index', compact('labors', 'phone_codes'));
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
            'phone' => $request->phone,
        ]);

        sendNotification($labor->landlord->id, notify_users([$labor->landlord->id]), landlord_url: route('landlord.labors.index', ['id' => $labor->id]), landlord_msg: __('You have created a new labor.'));

        return response()->json([
            'message' => 'Labor save successfully',
            'redirect' => route('landlord.labors.index'),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:labors,email,'.$id,
            'phone' => 'required',
            'salary' => 'required|numeric|min:0',
            'gender' => 'nullable|in:Male,Female,other',
        ]);

        $labor = Labor::findOrFail($id);

        $labor->update($request->except('landlord_id', 'phone') + [
            'landlord_id' => auth()->id(),
            'phone' => $request->phone,
        ]);

        return response()->json([
            'message' => 'Labor update successfully',
            'redirect' => route('landlord.labors.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $labor = Labor::findOrFail($id);
        $labor->delete();

        return response()->json([
            'message' => __('Labor deleted successfully'),
            'redirect' => route('landlord.labors.index'),
        ]);
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return Labor::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return Labor::with('landlord')
            ->where('landlord_id', auth()->id())
            ->when(request('search'), function ($q) {
                $q->where(function ($q) {
                    $search = request('search');
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orwhere('email', 'like', '%'.$search.'%')
                        ->orwhere('phone', 'like', '%'.$search.'%')
                        ->orwhere('gender', 'like', '%'.$search.'%')
                        ->orwhere('salary', 'like', '%'.$search.'%');
                });
            })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'landlord.labors.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['labors' => $rows];
    }

    protected function acnooPerPage(): int
    {
        return 20;
    }

    protected function acnooRedirectRoute(): string
    {
        return 'landlord.labors.index';
    }

    protected function acnooDeleteMessage(): string
    {
        return __('Selected labor deleted successfully');
    }

    protected function acnooExport(Request $request): object
    {
        return new LandlordLaborExport;
    }

    protected function acnooExcelFilename(): string
    {
        return 'landlord-labors';
    }

    protected function acnooCsvFilename(): string
    {
        return 'landlord-labors';
    }
}
