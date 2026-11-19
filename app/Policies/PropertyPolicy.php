<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasAnyRole(['admin', 'superadmin']) || in_array($user->role, ['admin', 'superadmin'], true)) {
            return true;
        }

        return null;
    }

    public function view(User $user, Property $property): bool
    {
        return $user->id === $property->landlord_id;
    }

    public function update(User $user, Property $property): bool
    {
        return $user->id === $property->landlord_id;
    }

    public function delete(User $user, Property $property): bool
    {
        return $user->id === $property->landlord_id;
    }
}
