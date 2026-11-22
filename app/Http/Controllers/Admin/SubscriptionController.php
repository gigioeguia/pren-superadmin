<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Exports\AdminSubscriptionExport;
use App\Http\Controllers\Controller;
use App\Models\PlanSubscribe;
use App\Services\SubscriptionService;
use App\Traits\HasTableActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubscriptionController extends Controller
{
    use HasTableActions;

    public function __construct(protected SubscriptionService $subscriptions)
    {
        $this->middleware('permission:subscriptions-read')->only('index', 'filter', 'file_download', 'getInvoice', 'exportExcel', 'exportCsv');
        $this->middleware('permission:subscriptions-update')->only('reject', 'paid');
    }

    public function index()
    {
        $subscriptions = $this->subscriptions->listForChannel(Channel::Admin);

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function reject(Request $request, string $id)
    {
        $request->validate([
            'notes' => 'required|string|max:255',
        ]);

        $this->subscriptions->reject(PlanSubscribe::findOrFail($id), (string) $request->input('notes'));

        return response()->json([
            'message' => 'Subscription rejected successfully',
            'redirect' => route('admin.subscriptions.index'),
        ]);
    }

    public function paid(Request $request, string $id)
    {
        $request->validate([
            'notes' => 'required|string|max:255',
        ]);

        $subscription = PlanSubscribe::findOrFail($id);

        try {
            $this->subscriptions->paid($subscription, (string) $request->input('notes'), (int) auth()->id());
        } catch (\Exception $e) {
            report($e);

            return response()->json(['message' => 'Something Went wrong!'], 404);
        }

        sendNotification(
            $subscription->id,
            notify_users([$subscription->user->id]),
            admin_url: route('admin.subscriptions.index', ['id' => $subscription->id]),
            admin_msg: __('A new subscription has been activated.'),
            landlord_msg: __('Your subscription has been successfully activated. Enjoy the benefits!')
        );

        return response()->json([
            'message' => 'Subscription paid successfully',
            'redirect' => route('admin.subscriptions.index'),
        ]);
    }

    public function file_download(string $id)
    {
        $filePath = $this->subscriptions->fileDownload(PlanSubscribe::findOrFail($id));

        if ($filePath === null) {
            return redirect()->back()->with('error', 'File not found');
        }

        return Storage::download($filePath);
    }

    public function getInvoice(string $invoice_id)
    {
        $subscriber = $this->subscriptions->getInvoice((int) $invoice_id);

        return view('admin.subscriptions.invoice', compact('subscriber'));
    }

    protected function model(): string
    {
        return PlanSubscribe::class;
    }

    protected function query(Request $request): Builder
    {
        $search = $request->input('search');

        return $this->subscriptions->adminQuery(is_string($search) ? $search : null);
    }

    protected function view(): string
    {
        return 'admin.subscriptions.datas';
    }

    protected function viewData(LengthAwarePaginator $rows): array
    {
        return ['subscriptions' => $rows];
    }

    protected function export(Request $request): object
    {
        return new AdminSubscriptionExport;
    }

    protected function excelFilename(): string
    {
        return 'subscription-list';
    }

    protected function csvFilename(): string
    {
        return 'subscription-list';
    }
}
