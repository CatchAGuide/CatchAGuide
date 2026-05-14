<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FinanceItemEvent extends Model
{
    protected $table = 'finance_item_events';

    protected $fillable = [
        'finance_item_id',
        'event_type',
        'payload',
        'actor_type',
        'actor_id',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function financeItem(): BelongsTo
    {
        return $this->belongsTo(FinanceItem::class);
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}

