<?php

namespace App\Policies;

use App\Models\RentPayment;
use App\Models\User;

class RentPaymentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasAnyRole(['admin', 'superadmin']) || in_array($user->role, ['admin', 'superadmin'], true)) {
            return true;
        }

        return null;
    }

    protected function owns(User $user, RentPayment $payment): bool
    {
        $rent = $payment->rent()->first();

        if (! $rent) {
            return false;
        }

        return $user->id === $rent->getAttribute('landlord_id') || $user->id === $rent->getAttribute('tenant_id');
    }

    public function view(User $user, RentPayment $payment): bool
    {
        return $this->owns($user, $payment);
    }

    public function update(User $user, RentPayment $payment): bool
    {
        $rent = $payment->rent()->first();

        return $rent && $user->id === $rent->getAttribute('landlord_id');
    }

    public function delete(User $user, RentPayment $payment): bool
    {
        $rent = $payment->rent()->first();

        return $rent && $user->id === $rent->getAttribute('landlord_id');
    }

    public function approve(User $user, RentPayment $payment): bool
    {
        return false;
    }

    public function reject(User $user, RentPayment $payment): bool
    {
        return false;
    }

    public function fileDownload(User $user, RentPayment $payment): bool
    {
        return $this->owns($user, $payment);
    }
}
