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
        return $this->employee?->name ?? __('admin.booking_status_history.system');
    }
}
