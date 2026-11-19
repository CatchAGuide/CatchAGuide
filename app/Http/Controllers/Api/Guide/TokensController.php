<?php

namespace App\Http\Controllers\Api\Guide;

use App\Http\Controllers\Controller;
use App\Services\Guide\Area\GuideApiTokenService;
use App\Support\LoginThrottle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * /api/guide/tokens — sign in with email + password to get a bearer token, sign out to drop it.
 */
class TokensController extends Controller
{
    private const THROTTLE_GUARD = 'guide-api';

    public function __construct(private readonly GuideApiTokenService $tokens) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'device_name' => ['required', 'string', 'max:100'],
        ]);

        if (LoginThrottle::lockedOut($request, self::THROTTLE_GUARD)) {
            return $this->throttled($request);
        }

        $user = $this->tokens->userForCredentials($data['email'], $data['password']);

        if ($user === null) {
            LoginThrottle::recordFailure($request, self::THROTTLE_GUARD);
            Log::warning('Failed guide API login attempt', ['ip' => $request->ip()]);

            return LoginThrottle::lockedOut($request, self::THROTTLE_GUARD)
                ? $this->throttled($request)
                : response()->json(['message' => __('auth.failed'), 'errors' => ['email' => [__('auth.failed')]]], 422);
        }

        LoginThrottle::clear($request, self::THROTTLE_GUARD);

        if (! $user->canViewGuideTools()) {
            return response()->json(['message' => __('guide_area.api.not_a_guide')], 403);
        }

        if (! $user->usesGuideArea()) {
            return response()->json(['message' => __('guide_area.api.not_enabled')], 403);
        }

        $token = $this->tokens->issue($user, $data['device_name']);

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
            'user' => ['id' => $user->id, 'firstname' => $user->firstname],
        ], 201);
    }

    public function destroy(Request $request): Response
    {
        $this->tokens->revokeCurrent($request->user());

        return response()->noContent();
    }

    private function throttled(Request $request): JsonResponse
    {
        $seconds = LoginThrottle::secondsUntilRetry($request, self::THROTTLE_GUARD);

        return response()->json([
            'message' => LoginThrottle::message($seconds),
            'retry_after' => $seconds,
        ], 429);
    }
}
