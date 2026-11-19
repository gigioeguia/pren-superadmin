<?php

namespace App\Policies;

use App\Models\SecurityDeposit;
use App\Models\User;

class SecurityDepositPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasAnyRole(['admin', 'superadmin']) || in_array($user->role, ['admin', 'superadmin'], true)) {
            return true;
        }

        return null;
    }

    public function view(User $user, SecurityDeposit $deposit): bool
    {
        return $user->id === $deposit->landlord_id || $user->id === $deposit->tenant_id;
    }

    public function update(User $user, SecurityDeposit $deposit): bool
    {
        return $user->id === $deposit->landlord_id || $user->id === $deposit->tenant_id;
    }

    public function delete(User $user, SecurityDeposit $deposit): bool
    {
        return $user->id === $deposit->landlord_id;
    }

    public function approve(User $user, SecurityDeposit $deposit): bool
    {
        return $user->id === $deposit->landlord_id;
    }

    public function reject(User $user, SecurityDeposit $deposit): bool
    {
        return $user->id === $deposit->landlord_id;
    }

    public function fileDownload(User $user, SecurityDeposit $deposit): bool
    {
        return $user->id === $deposit->landlord_id || $user->id === $deposit->tenant_id;
    }

    public function offlinePayment(User $user, SecurityDeposit $deposit): bool
    {
        return $user->id === $deposit->landlord_id || $user->id === $deposit->tenant_id;
    }
}
