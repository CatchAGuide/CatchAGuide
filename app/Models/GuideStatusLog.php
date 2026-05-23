<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuideStatusLog extends Model
{
    public $timestamps = false;

    protected $table = 'guide_status_log';

    protected $fillable = [
        'user_id',
        'from_status',
        'to_status',
        'changed_by',
        'changed_at',
        'reason',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
