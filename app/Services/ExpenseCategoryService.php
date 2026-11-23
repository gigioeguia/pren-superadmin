<?php

namespace App\Services;

use App\Enums\Channel;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ExpenseCategoryService
{
    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        if ($channel === Channel::Admin) {
            $query = ExpenseCategory::whereNull('landlord_id');
        } else {
            $query = ExpenseCategory::where('landlord_id', $actor?->id);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function store(array $data, Channel $channel, ?User $actor): ExpenseCategory
    {
        $landlordId = $channel === Channel::Admin ? null : $actor?->id;

        DB::beginTransaction();
        try {
            $category = ExpenseCategory::create([
                'landlord_id' => $landlordId,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 1,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $category;
    }

    public function update(ExpenseCategory $category, array $data, Channel $channel, ?User $actor): ExpenseCategory
    {
        DB::beginTransaction();
        try {
            $category->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? $category->status,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $category->fresh();
    }

    public function delete(ExpenseCategory $category, Channel $channel): void
    {
        $category->delete();
    }

    public function deleteMany(array $ids, Channel $channel, ?User $actor): int
    {
        $query = ExpenseCategory::whereIn('id', $ids);
        if ($channel !== Channel::Admin && $actor) {
            $query->where('landlord_id', $actor->id);
        }
        $count = $query->delete();

        return (int) $count;
    }

    // Seg-5 (contrato del trait, spec 5.17 §5): sin scoping — las policies lo cerrarán.
    public function updateStatus(ExpenseCategory $category, int $status): void
    {
        $category->update(['status' => $status]);
    }
}
