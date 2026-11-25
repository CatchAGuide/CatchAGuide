<?php

namespace App\Models;

use App\Enums\GuestBookingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

/**
 * The link to one booking (/b/{token}). One active row per booking; turning a link off sets
 * revoked_at and the next email gets a new one. See BookingAccessTokenService.
 */
class BookingAccessToken extends Model
{
    protected $fillable = [
        'bookable_type',
        'bookable_id',
        'email',
        'token_hash',
        'token_encrypted',
        'revoked_at',
        'last_used_at',
    ];

    protected $hidden = ['token_hash', 'token_encrypted'];

    protected $casts = [
        'bookable_type' => GuestBookingType::class,
        'revoked_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(BookingAccessEvent::class);
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }

    public function plainToken(): string
    {
        return Crypt::decryptString($this->token_encrypted);
    }

    /** The booking, tour, camp, trip or voucher order; null when it was deleted. */
    public function bookable(): ?Model
    {
        $class = $this->bookable_type->modelClass();

        return $class::query()->find($this->bookable_id);
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
