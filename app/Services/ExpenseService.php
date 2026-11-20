<?php

namespace App\Services;

use App\Enums\Channel;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        if ($channel === Channel::Admin) {
            $query = Expense::with('category:id,name')->whereNull('landlord_id');
        } else {
            $query = Expense::with('category:id,name')->where('landlord_id', $actor?->id);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('amount', 'like', '%'.$search.'%')
                    ->orWhere('expense_for', 'like', '%'.$search.'%')
                    ->orWhere('payment_type', 'like', '%'.$search.'%')
                    ->orWhere('reference_no', 'like', '%'.$search.'%')
                    ->orWhere('expense_date', 'like', '%'.$search.'%')
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        $customDays = $filters['custom_days'] ?? null;
        if ($customDays && $customDays !== 'custom_date' && $customDays !== 'all') {
            switch ($customDays) {
                case 'today':
                    $query->whereDate('expense_date', Carbon::today());
                    break;
                case 'yesterday':
                    $query->whereDate('expense_date', Carbon::yesterday());
                    break;
                case 'last_seven_days':
                    $query->whereBetween('expense_date', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()]);
                    break;
                case 'last_thirty_days':
                    $query->whereBetween('expense_date', [Carbon::now()->subDays(29)->startOfDay(), Carbon::now()->endOfDay()]);
                    break;
                case 'current_month':
                    $query->whereMonth('expense_date', Carbon::now()->month)
                        ->whereYear('expense_date', Carbon::now()->year);
                    break;
                case 'last_month':
                    $query->whereMonth('expense_date', Carbon::now()->subMonth()->month)
                        ->whereYear('expense_date', Carbon::now()->subMonth()->year);
                    break;
                case 'current_year':
                    $query->whereYear('expense_date', Carbon::now()->year);
                    break;
            }
        }

        if ($customDays === 'custom_date' && ! empty($filters['from_date']) && ! empty($filters['to_date'])) {
            $query->whereBetween('expense_date', [$filters['from_date'], $filters['to_date']]);
        }

        return $query->latest()->paginate($perPage);
    }

    public function totalForChannel(Channel $channel, ?User $actor, array $filters = []): float
    {
        if ($channel === Channel::Admin) {
            $query = Expense::whereNull('landlord_id');
        } else {
            $query = Expense::where('landlord_id', $actor?->id);
        }

        $customDays = $filters['custom_days'] ?? null;
        if ($customDays && $customDays !== 'custom_date' && $customDays !== 'all') {
            switch ($customDays) {
                case 'today':
                    $query->whereDate('expense_date', Carbon::today());
                    break;
                case 'yesterday':
                    $query->whereDate('expense_date', Carbon::yesterday());
                    break;
                case 'last_seven_days':
                    $query->whereBetween('expense_date', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()]);
                    break;
                case 'last_thirty_days':
                    $query->whereBetween('expense_date', [Carbon::now()->subDays(29)->startOfDay(), Carbon::now()->endOfDay()]);
                    break;
                case 'current_month':
                    $query->whereMonth('expense_date', Carbon::now()->month)
                        ->whereYear('expense_date', Carbon::now()->year);
                    break;
                case 'last_month':
                    $query->whereMonth('expense_date', Carbon::now()->subMonth()->month)
                        ->whereYear('expense_date', Carbon::now()->subMonth()->year);
                    break;
                case 'current_year':
                    $query->whereYear('expense_date', Carbon::now()->year);
                    break;
            }
        }

        if ($customDays === 'custom_date' && ! empty($filters['from_date']) && ! empty($filters['to_date'])) {
            $query->whereBetween('expense_date', [$filters['from_date'], $filters['to_date']]);
        }

        return (float) $query->sum('amount');
    }

    public function store(array $data, Channel $channel, ?User $actor): Expense
    {
        $landlordId = $channel === Channel::Admin ? null : $actor?->id;

        DB::beginTransaction();
        try {
            $expense = Expense::create([
                'amount' => $data['amount'],
                'expense_for' => $data['expense_for'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'expense_date' => $data['expense_date'] ?? null,
                'payment_type' => $data['payment_type'],
                'note' => $data['note'] ?? null,
                'expense_category_id' => $data['expense_category_id'],
                'landlord_id' => $landlordId,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $expense;
    }

    public function update(Expense $expense, array $data, Channel $channel, ?User $actor): Expense
    {
        DB::beginTransaction();
        try {
            $expense->update([
                'amount' => $data['amount'] ?? $expense->amount,
                'expense_for' => $data['expense_for'] ?? $expense->expense_for,
                'reference_no' => $data['reference_no'] ?? $expense->reference_no,
                'expense_date' => $data['expense_date'] ?? $expense->expense_date,
                'payment_type' => $data['payment_type'] ?? $expense->payment_type,
                'note' => $data['note'] ?? $expense->note,
                'expense_category_id' => $data['expense_category_id'] ?? $expense->expense_category_id,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $expense->fresh();
    }

    public function delete(Expense $expense, Channel $channel): void
    {
        $expense->delete();
    }

    public function deleteMany(array $ids, Channel $channel, ?User $actor): int
    {
        $query = Expense::whereIn('id', $ids);
        if ($channel !== Channel::Admin && $actor) {
            $query->where('landlord_id', $actor->id);
        }
        $count = $query->delete();

        return (int) $count;
    }
}
