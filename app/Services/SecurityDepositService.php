<?php

namespace App\Services;

use App\Enums\Channel;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\RentPayment;
use App\Models\SecurityDeposit;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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

    /**
     * Pago offline (manual): dueño de la tx (spec-tx-legadas-services-fase5).
     *
     * Ramas preservadas tal cual: subscription (PlanSubscribe 'unpaid' +
     * notes con attachment), security_deposit y rent_payment (update +
     * gateway). Un payment_type desconocido no escribe nada, como el
     * original. El authorize de la rama deposit lo evalúa el controlador
     * dentro de su try (el 500 'Payment failed' actual es parte del
     * contrato congelado por F13b).
     *
     * @param  array{payment_type: mixed, id: string, gateway_id: int|string, receipt_path: ?string, payment_note: ?string}  $data
     */
    public function offlinePayment(User $tenant, array $data): void
    {
        DB::beginTransaction();

        try {
            $payment_data = [
                'payment_note' => $data['payment_note'],
                'payment_receipt' => $data['receipt_path'],
            ];

            if ($data['payment_type'] == 'subscription') {
                $plan = Plan::findOrFail($data['id']);
                $duration = $plan->durationInDays();

                PlanSubscribe::create([
                    'user_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->subscriptionName,
                    'number_of_property' => $plan->number_of_property,
                    'number_of_tenant' => $plan->number_of_tenant,
                    'number_of_labor' => $plan->number_of_labor,
                    'gateway_id' => $data['gateway_id'],
                    'price' => $plan->offerPrice ?? $plan->subscriptionPrice,
                    'duration' => $duration,
                    'payment_status' => 'unpaid',
                    'notes' => [
                        'manual_data' => $data['payment_note'],
                        'attachment' => $data['receipt_path'],
                    ],
                ]);
            } elseif ($data['payment_type'] == 'security_deposit') {
                SecurityDeposit::findOrFail($data['id'])->update([
                    'payment_date' => today(),
                    'status' => 'pending',
                    'gateway_id' => $data['gateway_id'],
                    'payment_data' => $payment_data,
                ]);
            } elseif ($data['payment_type'] == 'rent_payment') {
                RentPayment::findOrFail($data['id'])->update([
                    'payment_date' => today(),
                    'payment_status' => 'pending',
                    'gateway_id' => $data['gateway_id'],
                    'payment_data' => $payment_data,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
