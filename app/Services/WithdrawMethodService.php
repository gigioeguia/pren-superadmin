<?php

namespace App\Services;

use App\Enums\Channel;
use App\Models\User;
use App\Models\WithdrawMethod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WithdrawMethodService
{
    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 20);

        $query = WithdrawMethod::with('currency:id,name');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('min_amount', 'like', '%'.$search.'%')
                    ->orWhere('max_amount', 'like', '%'.$search.'%')
                    ->orWhere('charge', 'like', '%'.$search.'%')
                    ->orWhereHas('currency', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function store(array $data): WithdrawMethod
    {
        $rawInputs = $data['meta']['input'] ?? $data['meta_input'] ?? [];
        $replace = array_map(function ($input) {
            $input = strtolower($input);
            $input = preg_replace('/\s+/', '_', trim($input));

            return $input;
        }, is_array($rawInputs) ? $rawInputs : []);

        $meta = [
            'label' => $data['meta']['label'] ?? ($data['meta_label'] ?? null),
            'input' => $replace,
        ];

        DB::beginTransaction();
        try {
            $method = WithdrawMethod::create([
                'name' => $data['name'],
                'currency_id' => $data['currency_id'],
                'min_amount' => $data['min_amount'],
                'max_amount' => $data['max_amount'],
                'charge' => $data['charge'],
                'charge_type' => $data['charge_type'],
                'instructions' => $data['instructions'] ?? null,
                'meta' => $meta,
                'status' => $data['status'] ?? 1,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $method;
    }

    public function update(WithdrawMethod $method, array $data): WithdrawMethod
    {
        $rawInputs = $data['meta']['input'] ?? $data['meta_input'] ?? [];
        $replace = array_map(function ($input) {
            $input = strtolower($input);
            $input = preg_replace('/\s+/', '_', trim($input));

            return $input;
        }, is_array($rawInputs) ? $rawInputs : []);

        $meta = [
            'label' => $data['meta']['label'] ?? ($data['meta_label'] ?? $method->meta['label'] ?? null),
            'input' => $replace ?: ($method->meta['input'] ?? []),
        ];

        DB::beginTransaction();
        try {
            $method->update([
                'name' => $data['name'] ?? $method->name,
                'currency_id' => $data['currency_id'] ?? $method->currency_id,
                'min_amount' => $data['min_amount'] ?? $method->min_amount,
                'max_amount' => $data['max_amount'] ?? $method->max_amount,
                'charge' => $data['charge'] ?? $method->charge,
                'charge_type' => $data['charge_type'] ?? $method->charge_type,
                'instructions' => $data['instructions'] ?? $method->instructions,
                'meta' => $meta,
                'status' => $data['status'] ?? $method->status,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $method->fresh();
    }

    public function delete(WithdrawMethod $method): void
    {
        $method->delete();
    }

    public function deleteMany(array $ids): int
    {
        return (int) WithdrawMethod::whereIn('id', $ids)->delete();
    }

    // Seg-5 (contrato del trait, spec 5.17 §5): sin scoping — las policies lo cerrarán.
    public function updateStatus(WithdrawMethod $method, int $status): void
    {
        $method->update(['status' => $status]);
    }
}
