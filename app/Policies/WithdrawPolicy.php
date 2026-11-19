<?php

namespace App\Policies;

use App\Models\LandlordWithdraw;
use App\Models\User;
use App\Models\Withdraw;

class WithdrawPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasAnyRole(['admin', 'superadmin']) || in_array($user->role, ['admin', 'superadmin'], true)) {
            return true;
        }

        return null;
    }

    public function view(User $user, Withdraw|LandlordWithdraw $withdraw): bool
    {
        return $user->id === $withdraw->user_id;
    }

    public function update(User $user, Withdraw|LandlordWithdraw $withdraw): bool
    {
        return $user->id === $withdraw->user_id;
    }

    public function delete(User $user, Withdraw|LandlordWithdraw $withdraw): bool
    {
        return $user->id === $withdraw->user_id;
    }

    public function approve(User $user, Withdraw|LandlordWithdraw $withdraw): bool
    {
        return false;
    }

    public function reject(User $user, Withdraw|LandlordWithdraw $withdraw): bool
    {
        return false;
    }

    public function invoice(User $user, Withdraw|LandlordWithdraw $withdraw): bool
    {
        return $user->id === $withdraw->user_id;
    }

    public function summary(User $user, Withdraw|LandlordWithdraw $withdraw): bool
    {
        return $user->id === $withdraw->user_id;
    }
}
