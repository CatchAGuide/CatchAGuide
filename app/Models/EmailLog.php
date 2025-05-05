<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'language',
        'subject',
        'type',
        'status',
        'target',
        'additional_info',
    ];

    protected $table = 'email_logs';
    
    public $timestamps = true;
}
