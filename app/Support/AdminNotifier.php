<?php

namespace App\Support;

use App\Services\AdminNotificationService;

class AdminNotifier
{
    public static function info(string $type, string $title, ?string $body = null, array $options = []): void
    {
        static::notify($type, $title, $body, ['level' => 'info'] + $options);
    }

    public static function success(string $type, string $title, ?string $body = null, array $options = []): void
    {
        static::notify($type, $title, $body, ['level' => 'success'] + $options);
    }

    public static function warning(string $type, string $title, ?string $body = null, array $options = []): void
    {
        static::notify($type, $title, $body, ['level' => 'warning'] + $options);
    }

    public static function error(string $type, string $title, ?string $body = null, array $options = []): void
    {
        static::notify($type, $title, $body, ['level' => 'danger'] + $options);
    }

    public static function notify(string $type, string $title, ?string $body = null, array $options = []): void
    {
        /** @var AdminNotificationService $service */
        $service = app(AdminNotificationService::class);
        $service->notify($type, $title, $body, $options);
    }
}

