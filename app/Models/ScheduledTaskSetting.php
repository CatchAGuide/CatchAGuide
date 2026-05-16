<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledTaskSetting extends Model
{
    protected $fillable = [
        'key',
        'is_enabled',
        'frequency',
        'schedule_time',
        'day_of_week',
        'cron_expression',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'day_of_week' => 'integer',
    ];
}
