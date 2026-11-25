<?php

namespace App\Services\Guide\Area;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Bearer tokens for /api/guide/* clients (e.g. a future guide app). Only guides who can use
 * the Guide Area get one; each token is named after the device and expires after
 * config('guide_area.api.token_days').
 */
class GuideApiTokenService
{
    public const ABILITY = 'guide-area';

    public const NAME_PREFIX = 'guide-area:';

    /** Bcrypt hash of a random throwaway string, checked against when the email is unknown. */
    private const DUMMY_HASH = '$2y$10$B6TRNSCwomQ6se1svvC18.5IBljVSxjprvb0/5t3WiVhLWEEcNJYm';

    /**
     * The user these credentials belong to, or null. Runs a hash check even for unknown
     * emails so response times don't tell which addresses have an account.
     */
    public function userForCredentials(string $email, string $password): ?User
    {
        $user = User::query()->where('email', trim($email))->first();

        if ($user === null) {
            Hash::check($password, self::DUMMY_HASH);

            return null;
        }

        return Hash::check($password, (string) $user->password) ? $user : null;
    }

    public function issue(User $user, string $deviceName): NewAccessToken
    {
        $days = (int) config('guide_area.api.token_days', 90);

        return $user->createToken(
            self::NAME_PREFIX.mb_substr(trim($deviceName), 0, 100),
            [self::ABILITY],
            $days > 0 ? now()->addDays($days) : null,
        );
    }

    /**
     * Deletes the bearer token the request came in with. Session requests have no token,
     * so there is nothing to delete.
     */
    public function revokeCurrent(User $user): bool
    {
        $token = $user->currentAccessToken();

        if (! $token instanceof PersonalAccessToken) {
            return false;
        }

        $token->delete();

        return true;
    }
}
