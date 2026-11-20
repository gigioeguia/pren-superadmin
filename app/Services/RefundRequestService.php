<?php

namespace App\Services;

use App\Enums\Channel;
use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RefundRequestService
{
    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        if ($channel === Channel::Admin) {
            $query = RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'landlord:id,name', 'tenant:id,name', 'deposit:id,total_amount']);
        } elseif ($channel === Channel::Landlord) {
            $query = RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'tenant:id,name', 'deposit:id,deposit_amount,utility_advance,rent_advance,total_amount'])
                ->where('landlord_id', $actor?->id);
        } else {
            $query = RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'landlord:id,name', 'tenant:id,name', 'deposit:id,invoice_no,deposit_amount,utility_advance,rent_advance,total_amount'])
                ->when($actor && $actor->role === 'tenant', function ($q) use ($actor) {
                    $q->where('tenant_id', $actor->id);
                })
                ->when($actor && $actor->role === 'landlord', function ($q) use ($actor) {
                    $q->where('landlord_id', $actor->id);
                });

            if (! empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (! empty($filters['from_date']) && ! empty($filters['to_date'])) {
                $query->whereDate('created_at', '>=', $filters['from_date']);
                $query->whereDate('created_at', '<=', $filters['to_date']);
            }
        }

        return $query->latest()->paginate($perPage);
    }

    public function approve(RefundRequest $request): RefundRequest
    {
        $request->update(['status' => 1]);

        return $request->fresh();
    }

    public function delete(RefundRequest $request, Channel $channel): void
    {
        $request->delete();
    }
}
