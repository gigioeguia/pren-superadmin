<?php

namespace App\Services;

use App\Enums\Channel;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): Collection
    {
        $query = $actor ? $actor->notifications() : null;

        if (! $query) {
            return collect();
        }

        if (! empty($filters['days'])) {
            $days = $filters['days'];
            if ($days === 'daily') {
                $query->whereDate('created_at', now()->format('Y-m-d'));
            } elseif ($days === 'weekly') {
                $query->whereDate('created_at', '>=', now()->subDays(6)->format('Y-m-d'));
            } elseif ($days === '15_days') {
                $query->whereDate('created_at', '>=', now()->subDays(15)->format('Y-m-d'));
            } elseif ($days === 'monthly') {
                $query->whereMonth('created_at', now()->format('m'));
            } elseif ($days === 'yearly') {
                $query->whereYear('created_at', now()->format('Y'));
            }
        }

        if (isset($filters['today']) && $filters['today']) {
            $query->whereDate('created_at', today());
        }

        return $query->latest()->get();
    }

    public function markAsRead(Notification $notification): void
    {
        $notification->read_at = now();
        $notification->save();
    }

    public function markAllRead(?User $actor): void
    {
        if ($actor) {
            $actor->unreadNotifications()->update(['read_at' => now()]);
        }
    }
}
