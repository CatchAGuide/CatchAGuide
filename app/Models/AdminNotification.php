<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'body',
        'level',
        'link',
        'meta',
        'is_read',
        'created_by',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_read' => 'boolean',
    ];
}

