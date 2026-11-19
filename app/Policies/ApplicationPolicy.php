<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasAnyRole(['admin', 'superadmin']) || in_array($user->role, ['admin', 'superadmin'], true)) {
            return true;
        }

        return null;
    }

    public function view(User $user, Application $application): bool
    {
        if ($user->id === $application->tenant_id) {
            return true;
        }

        $property = $application->property()->first();

        return $property && $user->id === $property->landlord_id;
    }

    public function process(User $user, Application $application): bool
    {
        $property = $application->property()->first();

        return $property && $user->id === $property->landlord_id;
    }

    public function approve(User $user, Application $application): bool
    {
        return $this->process($user, $application);
    }

    public function reject(User $user, Application $application): bool
    {
        return $this->process($user, $application);
    }

    public function fileDownload(User $user, Application $application): bool
    {
        if ($user->id === $application->tenant_id) {
            return true;
        }

        $property = $application->property()->first();

        return $property && $user->id === $property->landlord_id;
    }

    public function agreementSign(User $user, Application $application): bool
    {
        return $user->id === $application->tenant_id;
    }
}
