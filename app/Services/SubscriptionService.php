<?php

namespace App\Services;

use App\Enums\Channel;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Dueño único del listing de subscriptions/plans por canal y del
 * reject/paid/invoice/attachment de suscripciones (spec-service-payment-
 * fase5). Agnóstico de HTTP: la notificación post-commit la envía el
 * controlador (Fase 4 / 4.8).
 *
 * Divergencia trío paid vs PaymentService::success (rama subscription),
 * preservada por diseño (spec §5, congelada por U4):
 *  - paid actualiza una fila existente aprobada por admin; success crea
 *    la fila (pago gateway confirmado).
 *  - will_expire: paid usa `plan->duration` crudo (p.ej. +1 día para un
 *    mes); success usa `durationInDays()` (+30 días para un mes).
 *  - received_by: admin autenticado (paid) vs primer superadmin/admin
 *    (success). No unificar a ciegas.
 */
class SubscriptionService
{
    /**
     * Listing por canal: Admin pagina PlanSubscribe; Landlord/Api listan
     * los Plan activos (misma consulta que hoy corre duplicada).
     */
    public function listForChannel(Channel $channel): LengthAwarePaginator|Collection
    {
        return match ($channel) {
            Channel::Admin => PlanSubscribe::with(['user:id,name,will_expire', 'plan:id,subscriptionName', 'gateway:id,name'])
                ->latest()
                ->paginate(10),
            Channel::Landlord, Channel::Api => Plan::where('status', 1)->latest()->get(),
        };
    }

    /**
     * Builder del hook query() del trait HasTableActions (ruta filter,
     * canal admin): búsqueda por payment_status/user/plan/gateway.
     */
    public function adminQuery(?string $search = null): Builder
    {
        $query = PlanSubscribe::with(['user:id,name', 'plan:id,subscriptionName', 'gateway:id,name']);

        if (! empty($search)) {
            $query->where(function (Builder $query) use ($search) {
                $query->where('payment_status', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function (Builder $query) use ($search) {
                        $query->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('plan', function (Builder $query) use ($search) {
                        $query->where('subscriptionName', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('gateway', function (Builder $query) use ($search) {
                        $query->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        return $query->latest();
    }

    public function reject(PlanSubscribe $subscription, string $notes): void
    {
        $subscription->update([
            'payment_status' => 'rejected',
            'notes' => $notes,
        ]);
    }

    /**
     * Aprobación manual (admin): fila existente → paid + trío forceFill
     * (Q14 / spec 5.11: dispara UserObserver → invalidación estructural
     * de plan-data-{user_id}) + Transaction credit. La tx envuelve las
     * tres escrituras (Fase 4 / 4.8).
     */
    public function paid(PlanSubscribe $subscription, string $notes, int $approvedBy): void
    {
        DB::beginTransaction();

        try {
            $subscription->update([
                'payment_status' => 'paid',
                'notes' => $notes,
            ]);

            // Divergencia preservada: `plan->duration` crudo — NO
            // durationInDays(). Quirk congelado por U4.
            User::findOrFail($subscription->user_id)->forceFill([
                'subscriptionDate' => now(),
                'plan_subscribe_id' => $subscription->id,
                'will_expire' => now()->addDays($subscription->plan?->duration ?? 0),
            ])->save();

            Transaction::create([
                'paid_by' => $subscription->user_id,
                'received_by' => $approvedBy,
                'gateway_id' => $subscription->gateway_id,
                'plan_subscribe_id' => $subscription->id,
                'date' => today(),
                'amount' => $subscription->price,
                'type' => 'credit',
                'payment_method' => $subscription->gateway->name ?? null,
                'payment_type' => 'subscription',
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function getInvoice(int $invoiceId): PlanSubscribe
    {
        return PlanSubscribe::with(['plan:id,subscriptionName', 'gateway:id,name', 'user:id,name,email', 'user.user_details:user_id,address_info'])
            ->findOrFail($invoiceId);
    }

    /**
     * Path del attachment manual (null si no existe en disco/notes): el
     * streaming (Storage::download) queda en el controlador.
     */
    public function fileDownload(PlanSubscribe $subscription): ?string
    {
        $filePath = $subscription->notes['attachment'] ?? null;

        if (empty($filePath) || ! Storage::exists($filePath)) {
            return null;
        }

        return $filePath;
    }
}
