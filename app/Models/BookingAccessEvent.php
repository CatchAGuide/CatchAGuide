<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * What happened to a booking link: created, sent, opened, revoked. Date and time only, no IP
 * address or browser (follow-up F5: no privacy policy change).
 */
class BookingAccessEvent extends Model
{
    public const CREATED = 'created';
    public const SENT = 'sent';
    public const OPENED = 'opened';
    public const REVOKED = 'revoked';

    // "Find my booking" sign-in links (no booking attached).
    public const LOGIN_SENT = 'login_sent';
    public const LOGIN_USED = 'login_used';
    public const SIGNED_OUT = 'signed_out';

    public const UPDATED_AT = null;

    protected $fillable = [
        'booking_access_token_id',
        'email',
        'bookable_type',
        'bookable_id',
        'event',
        'employee_id',
    ];

    public function token(): BelongsTo
    {
        return $this->belongsTo(BookingAccessToken::class, 'booking_access_token_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
