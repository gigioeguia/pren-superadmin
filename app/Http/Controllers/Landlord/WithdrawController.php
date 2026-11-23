<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\Channel;
use App\Exports\LandlordWithdrawMethodExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserMethod;
use App\Models\Withdraw;
use App\Models\WithdrawMethod;
use App\Services\WithdrawService;
use App\Traits\HasTableActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
    use HasTableActions;

    public function __construct(protected WithdrawService $service) {}

    public function index()
    {
        $withdraws = Withdraw::with('account_info.withdraw_method:id,name,charge_type')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('landlord.withdraw-request.index', compact('withdraws'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_method_id' => 'required|integer',
            'amount' => 'required|string',
            'status' => 'nullable|string',
        ]);

        // Tipado explícito: auth()->user() es union de guards con modelos
        // fantasma en config (spec 5.11 — writes sobre User tipado).
        $user = User::findOrFail(auth()->id());
        $user_method = UserMethod::findOrFail($request->user_method_id);
        $method = WithdrawMethod::findOrFail($user_method->method_id);

        // Calculate charge
        if ($method->charge_type === 'percentage') {
            $charge = ($request->amount / 100) * $method->charge;
        } elseif ($method->charge_type === 'fixed') {
            $charge = $method->charge;
        } else {
            $charge = 0;
        }

        $total_amount = $request->amount + $charge;

        // Check method min and max amount
        if ($request->amount < $method->min_amount) {
            return response()->json([
                'message' => 'Withdraw amount must be at least '.$method->min_amount,
            ], 400);
        }

        if ($request->amount > $method->max_amount) {
            return response()->json([
                'message' => 'Withdraw amount cannot exceed '.$method->max_amount,
            ], 400);
        }

        // Lectura fuera de la tx (Fase 4/4.8): la unidad atómica vive en el service
        if ($user->balance < $total_amount) {
            return response()->json(
                [
                    'message' => 'Insufficient balance',
                ],
                400
            );
        }

        try {
            $this->service->store($user, $user_method, $request->amount, $charge, Channel::Landlord);

            return response()->json([
                'message' => 'withdraw request has been send',
                'modal' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Transaction failed'], 500);
        }
    }

    public function show(string $id)
    {
        $withdraw = Withdraw::with(['user:id,name,phone,email', 'user.user_details:user_id,address_info', 'account_info:id,method_id', 'account_info.withdraw_method:id,charge_type'])->findOrFail($id);
        $this->authorize('view', $withdraw);

        return view('landlord.withdraw-request.invoice', compact('withdraw'));
    }

    protected function model(): string
    {
        return Withdraw::class;
    }

    protected function query(Request $request): Builder
    {
        return Withdraw::with(['account_info.withdraw_method:id,name'])
            ->where('user_id', auth()->id())
            ->when($request->input('search'), function ($q) use ($request) {
                $search = $request->input('search');

                $q->where('amount', 'like', '%'.$search.'%')
                    ->orWhere('charge', 'like', '%'.$search.'%')
                    ->orWhere('status', 'like', '%'.$search.'%')
                    ->orWhereHas('account_info.withdraw_method', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('account_info', function ($q) use ($search) {
                        $q->where('infos->bank_name', 'like', '%'.$search.'%');
                    });
            })
            ->latest();
    }

    protected function view(): string
    {
        return 'landlord.withdraw-request.datas';
    }

    protected function viewData(LengthAwarePaginator $rows): array
    {
        return ['withdraws' => $rows];
    }

    protected function perPage(): int
    {
        return 20;
    }

    protected function export(Request $request): object
    {
        return new LandlordWithdrawMethodExport;
    }

    protected function excelFilename(): string
    {
        return 'landlord-withdraw-method';
    }

    protected function csvFilename(): string
    {
        return 'landlord-withdraw-method';
    }
}
