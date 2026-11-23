<?php

namespace App\Services;

use App\Enums\Channel;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserMethod;
use App\Models\Withdraw;
use Illuminate\Support\Facades\DB;

/**
 * Dueño único de las tx de withdraw (spec-tx-legadas-services-fase5):
 * store (Api/Landlord unificados), approve y reject (Admin).
 *
 * Divergencias HTTP preservadas (spec §4):
 *  - store: notifica solo en canal Api, DENTRO de la tx (escritura DB
 *    por canal database, orden original); Landlord no notifica.
 *  - approve: la notificación la envía el controlador tras el commit.
 *  - reject (D-FIX-reject): status + refund pasan a ser atómicos; el
 *    happy path observable no cambia.
 */
class WithdrawService
{
    /**
     * Crea el retiro y debita el balance en una sola unidad atómica.
     *
     * El chequeo de balance es una lectura y queda fuera de la tx
     * (Fase 4/4.8): quien llama responde con el 400 verbatim.
     */
    public function store(User $user, UserMethod $userMethod, float|string $amount, float $charge, Channel $channel): Withdraw
    {
        DB::beginTransaction();

        try {
            $withdraw = Withdraw::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'user_method_id' => $userMethod->id,
                'charge' => $charge,
            ]);

            $user->forceFill([
                'balance' => $user->balance - ($amount + $charge),
            ])->save();

            // Divergencia Api: notificación dentro de la tx, como el original
            if ($channel === Channel::Api) {
                sendNotification(
                    $withdraw->id,
                    notify_users([$user->id]),
                    landlord_url: route('landlord.withdraw.index', ['id' => $withdraw->id]),
                    admin_msg: __('A new withdrawal request has been submitted. Please review and process it.'),
                    landlord_msg: __('Your withdrawal request has been submitted successfully. Awaiting approval.')
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        return $withdraw;
    }

    /**
     * Aprueba el retiro: Transaction (ledger) + status en una tx.
     */
    public function approve(User $admin, Withdraw $withdraw, ?string $reason, ?string $notes): Transaction
    {
        // Relaciones explícitas tipadas, leídas fuera de la tx (Fase 4 / 4.8)
        $method = $withdraw->account_info()->first()?->withdraw_method()->first();

        DB::beginTransaction();

        try {
            $transaction = Transaction::create([
                'paid_by' => $admin->id,
                'received_by' => $withdraw->user_id,
                'withdraw_id' => $withdraw->id,
                'date' => today(),
                'type' => 'debit',
                'payment_type' => 'withdraw',
                'amount' => $withdraw->amount,
                'charge' => $withdraw->charge,
                'payment_method' => $method->name ?? '',
                'notes' => $notes,
            ]);

            $withdraw->update([
                'status' => 'approved',
                'reason' => $reason,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        return $transaction;
    }

    /**
     * Rechaza el retiro y reembolsa el balance (amount + charge).
     *
     * D-FIX-reject (spec §5.2): antes eran dos escrituras sin tx; ahora
     * son una sola unidad atómica. El happy path observable no cambia.
     */
    public function reject(Withdraw $withdraw, ?string $reason): void
    {
        DB::transaction(function () use ($withdraw, $reason): void {
            $totalRefund = $withdraw->amount + $withdraw->charge;

            $withdraw->update([
                'status' => 'rejected',
                'reason' => $reason,
            ]);

            User::findOrFail($withdraw->user_id)->increment('balance', $totalRefund);
        });
    }
}
