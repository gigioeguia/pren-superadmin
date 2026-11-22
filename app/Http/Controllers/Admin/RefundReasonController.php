<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundReason;
use App\Traits\HasTableActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RefundReasonController extends Controller
{
    use HasTableActions;

    public function __construct()
    {
        $this->middleware('permission:refund_reasons-read')->only('index', 'filter');
        $this->middleware('permission:refund_reasons-create')->only('store');
        $this->middleware('permission:refund_reasons-update')->only('update', 'status');
        $this->middleware('permission:refund_reasons-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $reasons = RefundReason::latest()->paginate(10);

        return view('admin.refund-reason.index', compact('reasons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        RefundReason::create($request->all());

        return response()->json([
            'message' => 'Reason Created successfully',
            'redirect' => route('admin.refund-reason.index'),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $reason = RefundReason::findOrFail($id);

        $reason->update($request->all());

        return response()->json([
            'message' => 'Reason updated successfully',
            'redirect' => route('admin.refund-reason.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $reason = RefundReason::findOrFail($id);
        $reason->delete();

        return response()->json([
            'message' => 'Reason deleted successfully',
            'redirect' => route('admin.refund-reason.index'),
        ]);
    }

    protected function model(): string
    {
        return RefundReason::class;
    }

    protected function query(Request $request): Builder
    {
        return RefundReason::when($request->input('search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $search = $request->input('search');
                $q->where('reason', 'like', '%'.$search.'%');
            });
        })
            ->latest();
    }

    protected function view(): string
    {
        return 'admin.refund-reason.datas';
    }

    protected function viewData(LengthAwarePaginator $rows): array
    {
        return ['reasons' => $rows];
    }

    protected function perPage(): int
    {
        return 20;
    }

    protected function redirectRoute(): string
    {
        return 'admin.refund-reason.index';
    }

    protected function statusMessage(): string
    {
        return 'Refund Reason';
    }

    protected function deleteMessage(): string
    {
        return 'Selected reason deleted successfully';
    }
}
