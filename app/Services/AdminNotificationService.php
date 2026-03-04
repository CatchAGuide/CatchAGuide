<?php

namespace App\Services;

use App\Models\AdminNotification;
use Illuminate\Support\Facades\Auth;

class AdminNotificationService
{
    public function notify(
        string $type,
        string $title,
        ?string $body = null,
        array $options = []
    ): AdminNotification {
        return AdminNotification::create([
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'level' => $options['level'] ?? 'info',
            'link' => $options['link'] ?? null,
            'meta' => $options['meta'] ?? null,
            'is_read' => $options['is_read'] ?? false,
            'created_by' => $options['created_by'] ?? Auth::id(),
        ]);
    }

    public function unreadCount(): int
    {
        return AdminNotification::where('is_read', false)->count();
    }

    public function latestUnread(int $limit = 8)
    {
        return AdminNotification::where('is_read', false)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function markAllRead(): void
    {
        AdminNotification::where('is_read', false)->update(['is_read' => true]);
    }
}

