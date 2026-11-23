<?php

namespace App\Services;

use App\Enums\Channel;
use App\Models\IncomeCategory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class IncomeCategoryService
{
    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        if ($channel === Channel::Admin) {
            $query = IncomeCategory::whereNull('landlord_id');
        } else {
            $query = IncomeCategory::where('landlord_id', $actor?->id);
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

    public function store(array $data, Channel $channel, ?User $actor): IncomeCategory
    {
        $landlordId = $channel === Channel::Admin ? null : $actor?->id;

        DB::beginTransaction();
        try {
            $category = IncomeCategory::create([
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

    public function update(IncomeCategory $category, array $data, Channel $channel, ?User $actor): IncomeCategory
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

    public function delete(IncomeCategory $category, Channel $channel): void
    {
        $category->delete();
    }

    public function deleteMany(array $ids, Channel $channel, ?User $actor): int
    {
        $query = IncomeCategory::whereIn('id', $ids);
        if ($channel !== Channel::Admin && $actor) {
            $query->where('landlord_id', $actor->id);
        }
        $count = $query->delete();

        return (int) $count;
    }

    // Seg-5 (contrato del trait, spec 5.17 §5): sin scoping — las policies lo cerrarán.
    public function updateStatus(IncomeCategory $category, int $status): void
    {
        $category->update(['status' => $status]);
    }
}
