<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomScheduledTask extends Model
{
    protected $fillable = [
        'label',
        'description',
        'command',
        'is_enabled',
        'frequency',
        'schedule_time',
        'day_of_week',
        'cron_expression',
        'without_overlapping',
        'run_in_background',
        'append_output_to',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'day_of_week' => 'integer',
        'without_overlapping' => 'boolean',
        'run_in_background' => 'boolean',
    ];
}
