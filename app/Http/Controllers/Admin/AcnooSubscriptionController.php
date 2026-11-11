<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminSubscriptionExport;
use App\Http\Controllers\Controller;
use App\Models\PlanSubscribe;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class AcnooSubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:subscriptions-read')->only('index');
        $this->middleware('permission:subscriptions-update')->only('reject', 'paid');
    }

    public function index()
    {
        $subscriptions = PlanSubscribe::with(['user:id,name,will_expire', 'plan:id,subscriptionName', 'gateway:id,name'])->latest()->paginate(10);

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function acnooFilter(Request $request)
    {
        $subscriptionQuery = PlanSubscribe::with(['user:id,name', 'plan:id,subscriptionName', 'gateway:id,name']);

        if ($request->filled('search')) {
            $subscriptionQuery->where(function ($q) use ($request) {
                $q->where('payment_status', 'like', '%'.$request->search.'%')
                    ->orWhereHas('user', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('plan', function ($q) use ($request) {
                        $q->where('subscriptionName', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('gateway', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%');
                    });
            });
        }

        $subscriptions = $subscriptionQuery->latest()->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.subscriptions.datas', compact('subscriptions'))->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    public function reject(Request $request, string $id)
    {
        $request->validate([
            'notes' => 'required|string|max:255',
        ]);

        $reject = PlanSubscribe::findOrFail($id);

        if ($reject) {

            $reject->update([
                'payment_status' => 'rejected',
                'notes' => $request->notes,
            ]);

            return response()->json([
                'message' => 'Subscription rejected successfully',
                'redirect' => route('admin.subscriptions.index'),
            ]);
        } else {
            return response()->json(['message' => 'request not found'], 404);
        }
    }

    public function paid(Request $request, string $id)
    {
        $request->validate([
            'notes' => 'required|string|max:255',
        ]);

        $subscribe = PlanSubscribe::findOrFail($id);

        if ($subscribe) {
            DB::beginTransaction();
            try {

                $subscribe->update([
                    'payment_status' => 'paid',
                    'notes' => $request->notes,
                ]);

                $subscribe->user()->update([
                    'subscriptionDate' => now(),
                    'plan_subscribe_id' => $subscribe->id,
                    'will_expire' => now()->addDays($subscribe->plan->duration ?? ''),
                ]);

                Transaction::create([
                    'paid_by' => $subscribe->user_id,
                    'received_by' => auth()->id(),
                    'gateway_id' => $subscribe->gateway_id,
                    'plan_subscribe_id' => $subscribe->id,
                    'date' => today(),
                    'amount' => $subscribe->price,
                    'type' => 'credit',
                    'payment_method' => $subscribe->gateway->name ?? null,
                    'payment_type' => 'subscription',
                ]);

                DB::commit();

                Cache::forget('plan-data-'.$subscribe->user_id);

                sendNotification($subscribe->id, notify_users([$subscribe->user->id]), admin_url: route('admin.subscriptions.index', ['id' => $subscribe->id]), admin_msg: __('A new subscription has been activated.'), landlord_msg: __('Your subscription has been successfully activated. Enjoy the benefits!'));

                return response()->json([
                    'message' => 'Subscription paid successfully',
                    'redirect' => route('admin.subscriptions.index'),
                ]);
            } catch (\Exception) {
                DB::rollBack();

                return response()->json(['message' => 'Something Went wrong!'], 404);
            }
        } else {
            return response()->json(['message' => 'request not found'], 404);
        }
    }

    public function file_download(string $id)
    {
        $subscription = PlanSubscribe::findOrFail($id);

        // Check if attachment exists in notes
        $filePath = $subscription->notes['attachment'] ?? null;

        if (empty($filePath) || ! Storage::exists($filePath)) {
            return redirect()->back()->with('error', 'File not found');
        }

        return Storage::download($filePath);
    }

    public function getInvoice(string $invoice_id)
    {
        $subscriber = PlanSubscribe::with(['plan:id,subscriptionName', 'gateway:id,name', 'user:id,name,email', 'user.user_details:user_id,address_info'])->findOrFail($invoice_id);

        return view('admin.subscriptions.invoice', compact('subscriber'));
    }

    public function exportExcel()
    {
        return Excel::download(new AdminSubscriptionExport, 'subscription-list.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new AdminSubscriptionExport, 'subscription-list.csv');
    }
}
