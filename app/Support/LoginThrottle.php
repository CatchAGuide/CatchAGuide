<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Brute force protection for the login endpoints.
 *
 * Attempts are counted per email + IP so a flood of guesses against one account
 * cannot lock out other users behind the same address.
 */
class LoginThrottle
{
    public const MAX_ATTEMPTS = 5;

    public const DECAY_SECONDS = 300;

    public static function key(Request $request, string $guard = 'web'): string
    {
        $email = Str::lower(trim((string) $request->input('email')));

        return 'login:' . $guard . '|' . sha1($email . '|' . $request->ip());
    }

    public static function lockedOut(Request $request, string $guard = 'web'): bool
    {
        return RateLimiter::tooManyAttempts(self::key($request, $guard), self::MAX_ATTEMPTS);
    }

    public static function secondsUntilRetry(Request $request, string $guard = 'web'): int
    {
        return max(1, RateLimiter::availableIn(self::key($request, $guard)));
    }

    public static function recordFailure(Request $request, string $guard = 'web'): void
    {
        RateLimiter::hit(self::key($request, $guard), self::DECAY_SECONDS);
    }

    public static function clear(Request $request, string $guard = 'web'): void
    {
        RateLimiter::clear(self::key($request, $guard));
    }

    public static function message(int $seconds): string
    {
        $seconds = max(1, $seconds);

        return $seconds >= 60
            ? (string) __('auth.throttle_minutes', ['minutes' => (int) ceil($seconds / 60)])
            : (string) __('auth.throttle', ['seconds' => $seconds]);
    }

    /**
     * Lockout response that keeps the reason visible to the user instead of
     * bubbling up as a bare "Too Many Attempts" error page.
     */
    public static function response(Request $request, int $seconds, array $headers = [])
    {
        $message = self::message($seconds);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'retry_after' => max(1, $seconds),
                'errors' => ['email' => [$message]],
            ], Response::HTTP_TOO_MANY_REQUESTS, $headers);
        }

        return back()
            ->withInput($request->except(['password', 'password_confirmation']))
            ->withErrors(['email' => $message])
            ->with('error', $message);
    }
}
