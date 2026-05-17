<?php

namespace App\Support;

final class BookingAssistantVisibility
{
    public const SESSION_PREVIEW_KEY = 'booking_assistant.preview_granted';

    public static function isEnabled(): bool
    {
        return static::configBool('booking_assistant.enabled');
    }

    public static function isWidgetVisibleSiteWide(): bool
    {
        return static::isEnabled() && static::configBool('booking_assistant.widget_visible');
    }

    public static function isPreviewPageRequest(): bool
    {
        return request()->routeIs('booking-assistant.preview');
    }

    public static function hasPreviewSession(): bool
    {
        return (bool) session(static::SESSION_PREVIEW_KEY, false);
    }

    public static function grantPreviewSession(): void
    {
        session([static::SESSION_PREVIEW_KEY => true]);
    }

    public static function shouldRenderWidget(): bool
    {
        if (! static::isEnabled()) {
            return false;
        }

        if (static::isWidgetVisibleSiteWide()) {
            return true;
        }

        return static::hasPreviewSession() && static::isPreviewPageRequest();
    }

    public static function canAccessChatApi(): bool
    {
        if (! static::isEnabled()) {
            return false;
        }

        if (static::isWidgetVisibleSiteWide()) {
            return true;
        }

        return static::hasPreviewSession() && static::isPreviewPageRequest();
    }

    public static function previewToken(): ?string
    {
        $token = config('booking_assistant.preview_token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    public static function matchesPreviewToken(string $candidate): bool
    {
        $token = static::previewToken();

        return $token !== null && hash_equals($token, $candidate);
    }

    public static function previewUrl(): ?string
    {
        $token = static::previewToken();

        if ($token === null) {
            return null;
        }

        $path = trim((string) config('booking_assistant.preview_path', 'hub/cag-ba-preview'), '/');

        return url('/'.$path.'/'.$token);
    }

    private static function configBool(string $key, bool $default = false): bool
    {
        return filter_var(config($key, $default), FILTER_VALIDATE_BOOLEAN);
    }
}
