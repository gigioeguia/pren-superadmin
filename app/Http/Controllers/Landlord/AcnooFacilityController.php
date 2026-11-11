<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AcnooFacilityController extends Controller
{
    use HasAcnooActions;

    public function index()
    {
        $facilities = Facility::where('landlord_id', auth()->id())->latest()->paginate(10);

        return view('landlord.facilities.index', compact('facilities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:0,1',
        ]);

        Facility::create($request->except('landlord_id') + [
            'landlord_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Facility Created Successfully',
            'redirect' => route('landlord.facilities.index'),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:0,1',
        ]);

        $facility = Facility::findOrFail($id);

        $facility->update($request->except('landlord_id'));

        return response()->json([
            'message' => 'Facility Updated Successfully',
            'redirect' => route('landlord.facilities.index'),
        ]);
    }

    public function destroy(string $id)
    {
        Facility::where('id', $id)->delete();

        return response()->json([
            'message' => 'Facility Deleted Successfully',
            'redirect' => route('landlord.facilities.index'),
        ]);
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return Facility::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return Facility::where('landlord_id', auth()->id())->when(request('search'), function ($q) {
            $q->where(function ($q) {
                $search = request('search');
                $q->where('name', 'like', '%'.$search.'%');
            });
        })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'landlord.facilities.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['facilities' => $rows];
    }

    protected function acnooRedirectRoute(): string
    {
        return 'landlord.facilities.index';
    }

    protected function acnooStatusMessage(): string
    {
        return 'Facility';
    }

    protected function acnooDeleteMessage(): string
    {
        return 'Selected Facility Deleted Successfully';
    }
}
