<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BookingStatusChange extends Model
{
    public const UPDATED_AT = null;

    public const REASON_CREATED = 'created';
    public const REASON_OFFER_CREATED = 'offer_created';

    /** Guiding bookings: who changed the status when no employee did. */
    public const REASON_GUIDE = 'guide';
    public const REASON_GUEST = 'guest';
    public const REASON_EXPIRED = 'expired';

    /** Reasons that name the actor instead of explaining the change (no badge in the history). */
    public const ACTOR_REASONS = [self::REASON_GUIDE, self::REASON_GUEST];

    protected $fillable = [
        'from_status',
        'to_status',
        'employee_id',
        'reason',
    ];

    public function bookable(): MorphTo
    {
        return $this->morphTo();
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function actorName(): string
    {
        if ($this->employee) {
            return $this->employee->name;
        }

        return match ($this->reason) {
            self::REASON_GUIDE => __('admin.booking_status_history.guide'),
            self::REASON_GUEST => __('admin.booking_status_history.guest'),
            default => __('admin.booking_status_history.system'),
        };
    }
}
