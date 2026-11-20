<?php

namespace App\Services;

use App\Enums\Channel;
use App\Models\Income;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class IncomeService
{
    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        if ($channel === Channel::Admin) {
            $query = Income::with('category:id,name')->whereNull('landlord_id');
        } else {
            $query = Income::with('category:id,name')->where('landlord_id', $actor?->id);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('amount', 'like', '%'.$search.'%')
                    ->orWhere('income_for', 'like', '%'.$search.'%')
                    ->orWhere('payment_type', 'like', '%'.$search.'%')
                    ->orWhere('reference_no', 'like', '%'.$search.'%')
                    ->orWhere('income_date', 'like', '%'.$search.'%')
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        // date filters
        $customDays = $filters['custom_days'] ?? null;
        if ($customDays && $customDays !== 'custom_date' && $customDays !== 'all') {
            switch ($customDays) {
                case 'today':
                    $query->whereDate('income_date', Carbon::today());
                    break;
                case 'yesterday':
                    $query->whereDate('income_date', Carbon::yesterday());
                    break;
                case 'last_seven_days':
                    $query->whereBetween('income_date', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()]);
                    break;
                case 'last_thirty_days':
                    $query->whereBetween('income_date', [Carbon::now()->subDays(29)->startOfDay(), Carbon::now()->endOfDay()]);
                    break;
                case 'current_month':
                    $query->whereMonth('income_date', Carbon::now()->month)
                        ->whereYear('income_date', Carbon::now()->year);
                    break;
                case 'last_month':
                    $query->whereMonth('income_date', Carbon::now()->subMonth()->month)
                        ->whereYear('income_date', Carbon::now()->subMonth()->year);
                    break;
                case 'current_year':
                    $query->whereYear('income_date', Carbon::now()->year);
                    break;
            }
        }

        if ($customDays === 'custom_date' && ! empty($filters['from_date']) && ! empty($filters['to_date'])) {
            $query->whereBetween('income_date', [$filters['from_date'], $filters['to_date']]);
        }

        return $query->latest()->paginate($perPage);
    }

    public function totalForChannel(Channel $channel, ?User $actor, array $filters = []): float
    {
        if ($channel === Channel::Admin) {
            $query = Income::whereNull('landlord_id');
        } else {
            $query = Income::where('landlord_id', $actor?->id);
        }

        // apply same date filters for total
        $customDays = $filters['custom_days'] ?? null;
        if ($customDays && $customDays !== 'custom_date' && $customDays !== 'all') {
            switch ($customDays) {
                case 'today':
                    $query->whereDate('income_date', Carbon::today());
                    break;
                case 'yesterday':
                    $query->whereDate('income_date', Carbon::yesterday());
                    break;
                case 'last_seven_days':
                    $query->whereBetween('income_date', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()]);
                    break;
                case 'last_thirty_days':
                    $query->whereBetween('income_date', [Carbon::now()->subDays(29)->startOfDay(), Carbon::now()->endOfDay()]);
                    break;
                case 'current_month':
                    $query->whereMonth('income_date', Carbon::now()->month)
                        ->whereYear('income_date', Carbon::now()->year);
                    break;
                case 'last_month':
                    $query->whereMonth('income_date', Carbon::now()->subMonth()->month)
                        ->whereYear('income_date', Carbon::now()->subMonth()->year);
                    break;
                case 'current_year':
                    $query->whereYear('income_date', Carbon::now()->year);
                    break;
            }
        }

        if ($customDays === 'custom_date' && ! empty($filters['from_date']) && ! empty($filters['to_date'])) {
            $query->whereBetween('income_date', [$filters['from_date'], $filters['to_date']]);
        }

        return (float) $query->sum('amount');
    }

    public function store(array $data, Channel $channel, ?User $actor): Income
    {
        $landlordId = $channel === Channel::Admin ? null : $actor?->id;

        DB::beginTransaction();
        try {
            $income = Income::create([
                'amount' => $data['amount'],
                'income_for' => $data['income_for'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'income_date' => $data['income_date'] ?? null,
                'payment_type' => $data['payment_type'],
                'note' => $data['note'] ?? null,
                'income_category_id' => $data['income_category_id'],
                'landlord_id' => $landlordId,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $income;
    }

    public function update(Income $income, array $data, Channel $channel, ?User $actor): Income
    {
        DB::beginTransaction();
        try {
            $income->update([
                'amount' => $data['amount'] ?? $income->amount,
                'income_for' => $data['income_for'] ?? $income->income_for,
                'reference_no' => $data['reference_no'] ?? $income->reference_no,
                'income_date' => $data['income_date'] ?? $income->income_date,
                'payment_type' => $data['payment_type'] ?? $income->payment_type,
                'note' => $data['note'] ?? $income->note,
                'income_category_id' => $data['income_category_id'] ?? $income->income_category_id,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $income->fresh();
    }

    public function delete(Income $income, Channel $channel): void
    {
        $income->delete();
    }

    public function deleteMany(array $ids, Channel $channel, ?User $actor): int
    {
        $query = Income::whereIn('id', $ids);
        if ($channel !== Channel::Admin && $actor) {
            $query->where('landlord_id', $actor->id);
        }
        $count = $query->delete();

        return (int) $count;
    }
}
