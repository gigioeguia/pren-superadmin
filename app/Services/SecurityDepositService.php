<?php

namespace App\Services;

use App\Enums\Channel;
use App\Models\SecurityDeposit;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SecurityDepositService
{
    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        if ($channel === Channel::Admin) {
            $query = SecurityDeposit::with([
                'tenant:id,name',
                'property:id',
                'property.propertyDetail:id,property_id,property_info',
                'gateway:id,name',
                'rent:id,tenant_id,property_id',
                'rent.tenant:id,name',
                'rent.property:id,puid',
                'rent.property.propertyDetail:id,property_id,property_info',
            ]);
        } elseif ($channel === Channel::Landlord) {
            $query = SecurityDeposit::with(['tenant:id,name', 'property:id,puid', 'property.propertyDetail:id,property_id,property_info'])
                ->where('landlord_id', $actor?->id);
        } else {
            $query = SecurityDeposit::with(['property:id,puid', 'property.propertyDetail:property_id,property_info', 'landlord:id,name,phone', 'tenant:id,name'])
                ->when($actor && $actor->role === 'landlord', function ($q) use ($actor) {
                    $q->where('landlord_id', $actor->id);
                })
                ->when($actor && $actor->role === 'tenant', function ($q) use ($actor) {
                    $q->where('tenant_id', $actor->id);
                });

            if (! empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (! empty($filters['from_date']) && ! empty($filters['to_date'])) {
                $query->whereDate('created_at', '>=', $filters['from_date']);
                $query->whereDate('created_at', '<=', $filters['to_date']);
            }
        }

        if ($channel === Channel::Landlord) {
            if (! empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_no', 'like', '%'.$search.'%')
                        ->orWhere('deposit_amount', 'like', '%'.$search.'%')
                        ->orWhereHas('tenant', function ($q) use ($search) {
                            $q->where('name', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('property', function ($q) use ($search) {
                            $q->where('puid', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('property.propertyDetail', function ($q) use ($search) {
                            $q->where('property_info->property_title', 'like', '%'.$search.'%');
                        });
                });
            }
        }

        return $query->latest()->paginate($perPage);
    }

    public function approve(SecurityDeposit $deposit): SecurityDeposit
    {
        $deposit->update(['status' => 1]);

        return $deposit->fresh();
    }

    public function reject(SecurityDeposit $deposit, string $reason): SecurityDeposit
    {
        $meta = $deposit->payment_data ?? [];
        $meta['reason'] = $reason;

        $deposit->update([
            'status' => 3,
            'payment_data' => $meta,
        ]);

        return $deposit->fresh();
    }

    public function delete(SecurityDeposit $deposit, Channel $channel): void
    {
        $deposit->delete();
    }
}
