<?php

namespace App\Services;

use App\Enums\Channel;
use App\Models\RentPayment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RentPaymentService
{
    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        if ($channel === Channel::Admin) {
            $query = RentPayment::with([
                'rent:id,tenant_id,property_id',
                'rent.tenant:id,name',
                'rent.property:id,puid',
                'rent.property.propertyDetail:id,property_id,property_info',
                'gateway:id,name',
            ]);
        } elseif ($channel === Channel::Landlord) {
            $landlordId = $actor?->id;
            $query = RentPayment::with(['rent:id,tenant_id,property_id,landlord_id', 'rent.tenant:id,name', 'rent.property:id,puid', 'rent.property.propertyDetail:id,property_id,property_info'])
                ->whereHas('rent', function ($q) use ($landlordId) {
                    $q->where('landlord_id', $landlordId);
                });

            if (! empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('payment_date', 'like', '%'.$search.'%')
                        ->orWhere('total_amount', 'like', '%'.$search.'%')
                        ->orWhere('invoice_no', 'like', '%'.$search.'%')
                        ->orWhereHas('rent.tenant', function ($q) use ($search) {
                            $q->where('name', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('rent.property', function ($q) use ($search) {
                            $q->where('puid', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('rent.property.propertyDetail', function ($q) use ($search) {
                            $q->where('property_info->property_title', 'like', '%'.$search.'%');
                        });
                });
            }
        } else {
            $query = RentPayment::select('id', 'rent_id', 'invoice_no', 'charge', 'total_amount', 'subtotal_amount', 'payment_status', 'created_at')
                ->with('rent:id,tenant_id,landlord_id,property_id', 'rent.tenant:id,name', 'rent.landlord:id,name', 'rent.property:id,puid', 'rent.property.propertyDetail:id,property_id,property_info')
                ->when($actor && $actor->role === 'landlord', function ($q) use ($actor) {
                    $q->whereHas('rent', function ($q) use ($actor) {
                        $q->where('landlord_id', $actor->id);
                    });
                })
                ->when($actor && $actor->role === 'tenant', function ($q) use ($actor) {
                    $q->whereHas('rent', function ($q) use ($actor) {
                        $q->where('tenant_id', $actor->id);
                    });
                });

            if (! empty($filters['payment_status'])) {
                $query->where('payment_status', $filters['payment_status']);
            }
            if (! empty($filters['from_date']) && ! empty($filters['to_date'])) {
                $query->whereDate('created_at', '>=', $filters['from_date']);
                $query->whereDate('created_at', '<=', $filters['to_date']);
            }
        }

        return $query->latest()->paginate($perPage);
    }

    public function approve(RentPayment $payment): RentPayment
    {
        $payment->update(['payment_status' => 'paid']);

        return $payment->fresh();
    }

    public function reject(RentPayment $payment, string $reason): RentPayment
    {
        $payment->update(['payment_status' => 'rejected']);

        return $payment->fresh();
    }

    public function delete(RentPayment $payment, Channel $channel): void
    {
        $payment->delete();
    }
}
