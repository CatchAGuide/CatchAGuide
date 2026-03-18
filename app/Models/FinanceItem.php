<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FinanceItem extends Model
{
    protected $table = 'finance_items';

    protected $fillable = [
        'invoice_status',
        'invoice_sent_at',
        'invoice_number',
        'paid_status',
        'paid_at',
    ];

    protected $casts = [
        'invoice_sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }
}

