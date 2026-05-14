<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FinanceItem extends Model
{
    protected $table = 'finance_items';

    protected $fillable = [
        'invoice_status',
        'invoice_sent_at',
        'invoice_number',
        'gross_amount',
        'commission_amount',
        'tax_amount',
        'currency',
        'paid_status',
        'paid_at',
        'invoice_due_at',
        'reminder_step',
        'last_reminder_sent_at',
        'next_reminder_at',
    ];

    protected $casts = [
        'invoice_sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'gross_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'invoice_due_at' => 'datetime',
        'last_reminder_sent_at' => 'datetime',
        'next_reminder_at' => 'datetime',
    ];

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    public function events(): HasMany
    {
        return $this->hasMany(FinanceItemEvent::class);
    }
}

