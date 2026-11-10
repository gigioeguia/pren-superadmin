<?php

namespace App\Http\Controllers\Admin;

use App\Models\RefundReason;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcnooRefundReasonController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:refund_reasons-read')->only('index');
        $this->middleware('permission:refund_reasons-create')->only('store');
        $this->middleware('permission:refund_reasons-update')->only('update', 'status');
        $this->middleware('permission:refund_reasons-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $reasons = RefundReason::latest()->paginate(10);
        return view('admin.refund-reason.index', compact('reasons'));
    }

    public function acnooFilter(Request $request)
    {
        $reasons = RefundReason::when(request('search'), function ($q) {
                $q->where(function ($q) {
                    $search = request('search');
                    $q->where('reason', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.refund-reason.datas', compact('reasons'))->render()
            ]);
        }

        return redirect(url()->previous());
    }


    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);

        RefundReason::create($request->all());

        return response()->json([
            'message' => 'Reason Created successfully',
            'redirect' => route('admin.refund-reason.index')
        ]);
    }


    public function update(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);

        $reason = RefundReason::findOrFail($id);

        $reason->update($request->all());

        return response()->json([
            'message' => 'Reason updated successfully',
            'redirect' => route('admin.refund-reason.index')
        ]);
    }


    public function status(Request $request, string $id)
    {
        $reason = RefundReason::findOrFail($id);
        $reason->update(['status' => $request->status]);
        return response()->json([
            'message' => 'Refund Reason',
        ]);
    }

    public function destroy(string $id)
    {
        $reason = RefundReason::findOrFail($id);
        $reason->delete();

        return response()->json([
            'message' => 'Reason deleted successfully',
            'redirect' => route('admin.refund-reason.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        RefundReason::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Selected reason deleted successfully',
            'redirect' => route('admin.refund-reason.index')
        ]);
    }
}
