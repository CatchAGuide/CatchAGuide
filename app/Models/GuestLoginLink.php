<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One "Find my booking" sign-in link and, once used, the 7-day sign-in it started.
 *
 * @property string $email
 * @property Carbon $expires_at
 * @property Carbon|null $used_at
 * @property Carbon|null $session_expires_at
 * @property Carbon|null $signed_out_at
 */
class GuestLoginLink extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'session_expires_at' => 'datetime',
            'signed_out_at' => 'datetime',
        ];
    }

    public static function hashToken(string $plain): string
    {
        return hash('sha256', $plain);
    }

    /** Not used yet and not expired. */
    public function isUsable(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    /** Used, still within its 7 days, not signed out. */
    public function isSignedIn(): bool
    {
        return $this->used_at !== null
            && $this->signed_out_at === null
            && $this->session_expires_at !== null
            && $this->session_expires_at->isFuture();
    }
}
